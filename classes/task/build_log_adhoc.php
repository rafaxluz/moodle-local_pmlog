<?php
namespace local_pmlog\task;

defined('MOODLE_INTERNAL') || die();

use core\task\adhoc_task;
use local_pmlog\local\pipeline_service;
use local_pmlog\local\exporter_csv;

class build_log_adhoc extends adhoc_task {

    public function execute() {
        global $CFG;

        $data = $this->get_custom_data();

        $courseid = (int)($data->courseid ?? 0);
        if ($courseid <= 0) {
            return;
        }

        $options = [
            'clear' => true,
            'studentonly' => !empty($data->studentonly),
            'dedup' => !empty($data->dedup),
            'dedupwindow' => (int)($data->dedupwindow ?? 30),
            'timestart' => (int)($data->timestart ?? 0),
            'timeend' => (int)($data->timeend ?? 0),
            'courseviewwindow' => (int)($data->courseviewwindow ?? 18000000),
            'moduleviewwindow' => (int)($data->moduleviewwindow ?? 18000000),
        ];

        $svc = new pipeline_service();
        $result = $svc->run($courseid, $options);

        set_config('last_run_courseid', $courseid, 'local_pmlog');
        set_config('last_run_time', time(), 'local_pmlog');
        set_config('last_run_raw', (int)$result['raw_count'], 'local_pmlog');
        set_config('last_run_stored', (int)$result['stored_count'], 'local_pmlog');
        set_config('last_run_skipped', (int)$result['skipped_count'], 'local_pmlog');

        if (!empty($data->exportcsv)) {
            require_once($CFG->libdir . '/filelib.php');

            $dir = make_temp_directory('local_pmlog');
            $filename = 'course' . $courseid
                . (!empty($options['studentonly']) ? '_students' : '_all')
                . '_labeled.csv';
            $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

            $exp = new exporter_csv();
            $exp->export_course($courseid, $fullpath);

            set_config('last_csv_course_' . $courseid, $filename, 'local_pmlog');
            set_config('last_csv_time_course_' . $courseid, time(), 'local_pmlog');
        }
    }
}
