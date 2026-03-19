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
 * Adhoc task for log building.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\task;

use core\task\adhoc_task;
use local_pmlog\local\casebuilder;
use local_pmlog\local\exporter_csv;
use local_pmlog\local\export_mode;
use local_pmlog\local\exporter_xes;
use local_pmlog\local\pipeline_service;

/**
 * Adhoc task for log building.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class build_log_adhoc extends adhoc_task {
    /**
     * Execute the task.
     */
    public function execute() {
        global $CFG;

        $data = $this->get_custom_data();

        $courseid = (int)($data->courseid ?? 0);
        if ($courseid <= 0) {
            return;
        }

        $lockfactory = \core\lock\lock_config::get_lock_factory('local_pmlog_build');
        $lock = $lockfactory->get_lock('course:' . $courseid, 0);
        if (!$lock) {
            mtrace('local_pmlog: build already running for course ' . $courseid . ', skipping duplicate task.');
            return;
        }

        try {
            $options = [
                'clear' => true,
                'studentonly' => !empty($data->studentonly),
                'dedup' => !empty($data->dedup),
                'caseidstrategy' => (string)($data->caseidstrategy ?? casebuilder::STRATEGY_USER_COURSE),
                'dedup_strict_cmid' => !empty($data->dedup_strict_cmid),
                'dedupwindow' => max(0, (int)($data->dedupwindow ?? 30)),
                'timestart' => (int)($data->timestart ?? 0),
                'timeend' => (int)($data->timeend ?? 0),
                'courseviewwindow' => max(0, (int)($data->courseviewwindow ?? 1800)),
                'moduleviewwindow' => max(0, (int)($data->moduleviewwindow ?? 600)),
                'exportxes' => !empty($data->exportxes),
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
                    . '.csv';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_csv();
                $exp->export_course($courseid, $fullpath, export_mode::STANDARD);

                set_config('last_csv_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_csv_time_course_' . $courseid, time(), 'local_pmlog');
            }

            if (!empty($data->exportcsvdetailed)) {
                require_once($CFG->libdir . '/filelib.php');

                $dir = make_temp_directory('local_pmlog');
                $filename = 'course' . $courseid
                    . (!empty($options['studentonly']) ? '_students' : '_all')
                    . '_detailed.csv';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_csv();
                $exp->export_course($courseid, $fullpath, export_mode::DETAILED);

                set_config('last_csv_detailed_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_csv_detailed_time_course_' . $courseid, time(), 'local_pmlog');
            }

            if (!empty($data->exportcsvnamed)) {
                require_once($CFG->libdir . '/filelib.php');

                $dir = make_temp_directory('local_pmlog');
                $filename = 'course' . $courseid
                    . (!empty($options['studentonly']) ? '_students' : '_all')
                    . '_named.csv';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_csv();
                $exp->export_course($courseid, $fullpath, export_mode::NAMED);

                set_config('last_csv_named_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_csv_named_time_course_' . $courseid, time(), 'local_pmlog');
            }

            if (!empty($options['exportxes'])) {
                require_once($CFG->libdir . '/filelib.php');

                $dir = make_temp_directory('local_pmlog');
                $filename = 'course' . $courseid
                    . (!empty($options['studentonly']) ? '_students' : '_all')
                    . '.xes';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_xes();
                $exp->export_course($courseid, $fullpath, export_mode::STANDARD);

                set_config('last_xes_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_xes_time_course_' . $courseid, time(), 'local_pmlog');
            }

            if (!empty($data->exportxesdetailed)) {
                require_once($CFG->libdir . '/filelib.php');

                $dir = make_temp_directory('local_pmlog');
                $filename = 'course' . $courseid
                    . (!empty($options['studentonly']) ? '_students' : '_all')
                    . '_detailed.xes';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_xes();
                $exp->export_course($courseid, $fullpath, export_mode::DETAILED);

                set_config('last_xes_detailed_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_xes_detailed_time_course_' . $courseid, time(), 'local_pmlog');
            }

            if (!empty($data->exportxesnamed)) {
                require_once($CFG->libdir . '/filelib.php');

                $dir = make_temp_directory('local_pmlog');
                $filename = 'course' . $courseid
                    . (!empty($options['studentonly']) ? '_students' : '_all')
                    . '_named.xes';
                $fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

                $exp = new exporter_xes();
                $exp->export_course($courseid, $fullpath, export_mode::NAMED);

                set_config('last_xes_named_course_' . $courseid, $filename, 'local_pmlog');
                set_config('last_xes_named_time_course_' . $courseid, time(), 'local_pmlog');
            }
        } finally {
            set_config('build_running_course_' . $courseid, 0, 'local_pmlog');
            $lock->release();
        }
    }
}
