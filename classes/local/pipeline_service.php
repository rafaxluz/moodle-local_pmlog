<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Pipeline service for processing event logs.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

/**
 * Pipeline service class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pipeline_service {
    /** @var extractor_standardlog Extractor instance. */
    private extractor_standardlog $extractor;
    /** @var transformer_basic Transformer instance. */
    private transformer_basic $transformer;
    /** @var casebuilder Case builder instance. */
    private casebuilder $casebuilder;
    /** @var eventstore Event store instance. */
    private eventstore $store;
    /** @var userresolver User resolver instance. */
    private userresolver $userresolver;
    /** @var cleaner_dedup Cleaner instance. */
    private cleaner_dedup $cleaner;
    /** @var modulelabeler Module labeler instance. */
    private modulelabeler $modulelabeler;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->extractor = new extractor_standardlog();
        $this->transformer = new transformer_basic();
        $this->casebuilder = new casebuilder();
        $this->store = new eventstore();
        $this->userresolver = new userresolver();
        $this->cleaner = new cleaner_dedup();
        $this->modulelabeler = new modulelabeler();
    }

    /**
     * Run the pipeline processing.
     *
     * @param int $courseid The course ID.
     * @param array $options Options for processing.
     * @return array Result statistics.
     */
    public function run(int $courseid, array $options = []): array {
        $timestart = (int)($options['timestart'] ?? 0);
        $timeend   = (int)($options['timeend'] ?? 0);
        $clear     = (bool)($options['clear'] ?? true);
        $studentonly = (bool)($options['studentonly'] ?? false);
        $dedup = (bool)($options['dedup'] ?? true);
        $dedupstrictcmid = (bool)($options['dedup_strict_cmid'] ?? false);
        $dedupwindow = (int)($options['dedupwindow'] ?? 30);

        if ($clear) {
            $this->store->clear_course($courseid);
        }

        // 1. Extraction
        $raw = $this->extract_data($courseid, $options);

        // 2. Transformation & Enrichment
        [$rows, $skipped] = $this->transform_and_enrich($raw, $studentonly);

        // 3. Deduplication
        $before = count($rows);
        $dedupskipped = 0;
        if ($dedup) {
            $rows = $this->deduplicate_data($rows, $options);
            $dedupskipped = $before - count($rows);
        }

        // 4. Load (Store)
        $this->store->insert_many($rows);

        return [
            'courseid' => $courseid,
            'raw_count' => count($raw),
            'stored_count' => count($rows),
            'skipped_count' => $skipped,
            'dedup_skipped' => $dedupskipped,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'cleared' => $clear,
            'studentonly' => $studentonly,
            'dedup' => $dedup,
            'dedup_strict_cmid' => $dedupstrictcmid,
            'dedupwindow' => $dedupwindow,
        ];
    }

    /**
     * Extract data from the source.
     *
     * @param int $courseid The course ID.
     * @param array $options Options for extraction.
     * @return array Raw data.
     */
    private function extract_data(int $courseid, array $options): array {
        $timestart = (int)($options['timestart'] ?? 0);
        $timeend   = (int)($options['timeend'] ?? 0);
        $resultoptions = [];

        $studentonly = (bool)($options['studentonly'] ?? false);
        if ($studentonly) {
            $resultoptions['studentonly'] = true;
        }

        $userids = [];
        if (!$studentonly) {
            $userids = (array)($options['userids'] ?? []);
        }

        return $this->extractor->extract($courseid, $timestart, $timeend, $userids, $resultoptions);
    }

    /**
     * Transform and enrich raw data.
     *
     * @param array $raw Raw data.
     * @param bool $studentonly Whether to filter for students only.
     * @return array [rows, skipped_count]
     */
    private function transform_and_enrich(array $raw, bool $studentonly): array {
        $rows = [];
        $skipped = 0;

        foreach ($raw as $e) {
            if (empty($e->userid) || empty($e->courseid) || empty($e->timecreated)) {
                $skipped++;
                continue;
            }

            if ($studentonly && isset($e->action) && $e->action === 'graded') {
                $skipped++;
                continue;
            }

            $rows[] = $this->process_single_event($e);
        }

        return [$rows, $skipped];
    }

    /**
     * Process a single event.
     *
     * @param \stdClass $e The raw event object.
     * @return \stdClass The processed event object.
     */
    private function process_single_event($e): \stdClass {
        $caseid = $this->casebuilder->build((int)$e->userid, (int)$e->courseid);
        $activity = $this->transformer->to_activity($e);
        $meta = $this->transformer->meta($e);

        $cmid = null;
        if (!empty($e->contextlevel) && (int)$e->contextlevel === CONTEXT_MODULE) {
            $cmid = !empty($e->contextinstanceid) ? (int)$e->contextinstanceid : null;
        }

        if ($activity === 'open activity/resource' || $activity === 'viewed:course_module') {
            $specific = $this->modulelabeler->label_from_cmid($cmid);
            if (!empty($specific)) {
                $activity = $specific;
            }
        }

        return (object)[
            'courseid' => (int)$e->courseid,
            'userid' => (int)$e->userid,
            'caseid' => $caseid,
            'activity' => $activity,
            'activitygroup' => \local_pmlog\local\activitygroupmap::group($activity),
            'timecreated' => (int)$e->timecreated,
            'cmid' => $cmid,
            'component' => $e->component ?? null,
            'eventname' => $e->eventname ?? null,
            'action' => $e->action ?? null,
            'target' => $e->target ?? null,
            'metajson' => json_encode($meta, JSON_UNESCAPED_UNICODE),
        ];
    }

    /**
     * Deduplicate data.
     *
     * @param array $rows The rows to deduplicate.
     * @param array $options Options for deduplication.
     * @return array Deduplicated rows.
     */
    private function deduplicate_data(array $rows, array $options): array {
        $dedupstrictcmid = (bool)($options['dedup_strict_cmid'] ?? false);
        $dedupwindow = (int)($options['dedupwindow'] ?? 30);
        $cwindow = (int)($options['courseviewwindow'] ?? 18000000);
        $mwindow = (int)($options['moduleviewwindow'] ?? 18000000);

        $rows = $this->cleaner->dedup_sequential($rows, $dedupwindow);

        if ($dedupstrictcmid) {
            $rows = $this->cleaner->dedup_strict_cmid($rows);
        }

        return $this->cleaner->collapse_navigation($rows, $cwindow, $mwindow);
    }
}
