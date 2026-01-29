<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class pipeline_service {
    private extractor_standardlog $extractor;
    private transformer_basic $transformer;
    private casebuilder $casebuilder;
    private eventstore $store;
    private userresolver $userresolver;
    private cleaner_dedup $cleaner;
    private modulelabeler $modulelabeler;

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
     * @return array<string, mixed>
     */
    public function run(int $courseid, array $options = []): array {
        $timestart = (int)($options['timestart'] ?? 0);
        $timeend   = (int)($options['timeend'] ?? 0);
        $clear     = (bool)($options['clear'] ?? true);

        $studentonly = (bool)($options['studentonly'] ?? false);
        $dedup = (bool)($options['dedup'] ?? true);
        $dedupwindow = (int)($options['dedupwindow'] ?? 30);

        $userids = [];
        if ($studentonly) {
            $userids = $this->userresolver->get_student_userids($courseid);
            if (empty($userids)) {
                if ($clear) {
                    $this->store->clear_course($courseid);
                }
                return [
                    'courseid' => $courseid,
                    'raw_count' => 0,
                    'stored_count' => 0,
                    'skipped_count' => 0,
                    'dedup_skipped' => 0,
                    'studentonly' => true,
                    'note' => 'No student users found for course context (role shortname/archetype).',
                ];
            }
        } else {
            $userids = (array)($options['userids'] ?? []);
        }

        if ($clear) {
            $this->store->clear_course($courseid);
        }

        $raw = $this->extractor->extract($courseid, $timestart, $timeend, $userids);

        $rows = [];
        $skipped = 0;

        foreach ($raw as $e) {
            if (empty($e->userid) || empty($e->courseid) || empty($e->timecreated)) {
                $skipped++;
                continue;
            }

            if ($studentonly) {
                if (!$this->userresolver->is_student_in_course($courseid, (int)$e->userid)) {
                    $skipped++;
                    continue;
                }
            }

            if ($studentonly && isset($e->action) && $e->action === 'graded') {
                $skipped++;
                continue;
            }

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

            $row = (object)[
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

            $rows[] = $row;
        }

        $before = count($rows);
        $dedupskipped = 0;

        if ($dedup) {
            $rows = $this->cleaner->dedup_sequential($rows, $dedupwindow);
            $cwindow = (int)($options['courseviewwindow'] ?? 18000000);
            $mwindow = (int)($options['moduleviewwindow'] ?? 18000000);
            $rows = $this->cleaner->collapse_navigation($rows, $cwindow, $mwindow);
            $dedupskipped = $before - count($rows);
        }

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
            'dedupwindow' => $dedupwindow,
        ];
    }
}
