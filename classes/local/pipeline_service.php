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
    /** @var int Batch size for incremental inserts. */
    private const INSERT_BATCH_SIZE = 1000;

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
        global $DB;

        $timestart = (int)($options['timestart'] ?? 0);
        $timeend   = (int)($options['timeend'] ?? 0);
        $clear     = (bool)($options['clear'] ?? true);
        $studentonly = (bool)($options['studentonly'] ?? false);
        $dedup = (bool)($options['dedup'] ?? true);
        $dedupstrictcmid = (bool)($options['dedup_strict_cmid'] ?? false);
        $dedupwindow = (int)($options['dedupwindow'] ?? 30);
        $casestrategy = (string)($options['caseidstrategy'] ?? casebuilder::STRATEGY_USER_COURSE);
        $rawcount = 0;
        $storedcount = 0;
        $skipped = 0;
        $dedupskipped = 0;
        $batch = [];
        $dedupstate = $this->cleaner->create_state();
        $recordset = null;
        $transaction = $DB->start_delegated_transaction();

        try {
            if ($clear) {
                $this->store->clear_course($courseid);
            }

            $recordset = $this->extract_data($courseid, $options);

            foreach ($recordset as $event) {
                $rawcount++;

                if (!$this->should_process_event($event, $studentonly)) {
                    $skipped++;
                    continue;
                }

                $row = $this->process_single_event($event, $options);

                if ($dedup && !$this->cleaner->keep_row($row, $options, $dedupstate)) {
                    $dedupskipped++;
                    continue;
                }

                $batch[] = $row;

                if (count($batch) >= self::INSERT_BATCH_SIZE) {
                    $this->store->insert_many($batch);
                    $storedcount += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $this->store->insert_many($batch);
                $storedcount += count($batch);
            }

            $transaction->allow_commit();
        } finally {
            if ($recordset) {
                $recordset->close();
            }
        }

        return [
            'courseid' => $courseid,
            'raw_count' => $rawcount,
            'stored_count' => $storedcount,
            'skipped_count' => $skipped,
            'dedup_skipped' => $dedupskipped,
            'timestart' => $timestart,
            'timeend' => $timeend,
            'cleared' => $clear,
            'studentonly' => $studentonly,
            'dedup' => $dedup,
            'dedup_strict_cmid' => $dedupstrictcmid,
            'dedupwindow' => $dedupwindow,
            'caseidstrategy' => $casestrategy,
        ];
    }

    /**
     * Extract data from the source.
     *
     * @param int $courseid The course ID.
     * @param array $options Options for extraction.
     * @return \moodle_recordset Raw data.
     */
    private function extract_data(int $courseid, array $options): \moodle_recordset {
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
     * Check whether the raw event should be processed.
     *
     * @param \stdClass $event Raw event.
     * @param bool $studentonly Whether to filter for students only.
     * @return bool
     */
    private function should_process_event(\stdClass $event, bool $studentonly): bool {
        if (empty($event->userid) || empty($event->courseid) || empty($event->timecreated)) {
            return false;
        }

        if ($studentonly && isset($event->action) && $event->action === 'graded') {
            return false;
        }

        return true;
    }

    /**
     * Process a single event.
     *
     * @param \stdClass $e The raw event object.
     * @param array $options Processing options.
     * @return \stdClass The processed event object.
     */
    private function process_single_event(\stdClass $e, array $options = []): \stdClass {
        $casestrategy = (string)($options['caseidstrategy'] ?? casebuilder::STRATEGY_USER_COURSE);
        $caseid = $this->casebuilder->build(
            (int)$e->userid,
            (int)$e->courseid,
            (int)$e->timecreated,
            $casestrategy
        );
        $activity = $this->transformer->to_activity($e);
        $meta = $this->transformer->meta($e);

        $cmid = null;
        if (!empty($e->contextlevel) && (int)$e->contextlevel === CONTEXT_MODULE) {
            $cmid = !empty($e->contextinstanceid) ? (int)$e->contextinstanceid : null;
        }

        if ($this->is_module_open_event($e)) {
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
     * Check whether the raw event represents opening a course module.
     *
     * @param \stdClass $event Raw event data.
     * @return bool
     */
    private function is_module_open_event(\stdClass $event): bool {
        return (($event->action ?? '') === 'viewed')
            && (($event->target ?? '') === 'course_module');
    }
}
