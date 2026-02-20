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
use local_pmlog\local\pipeline_service;
use local_pmlog\local\exporter_csv;

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

        $options = [
            'clear' => true,
            'studentonly' => !empty($data->studentonly),
            'dedup' => !empty($data->dedup),
            'dedup_strict_cmid' => !empty($data->dedup_strict_cmid),
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
