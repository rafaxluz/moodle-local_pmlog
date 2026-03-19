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
 * Scheduled task that queues PM log builds.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\task;

use core\task\manager;
use core\task\scheduled_task;
use local_pmlog\local\casebuilder;

/**
 * Scheduled task that queues PM log builds for configured courses.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class build_log_scheduled extends scheduled_task {
    /**
     * Get task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_build_log_scheduled', 'local_pmlog');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $DB;

        if (empty(get_config('local_pmlog', 'scheduleenabled'))) {
            mtrace('local_pmlog: scheduled build is disabled.');
            return;
        }

        $courseids = $this->parse_course_ids((string)get_config('local_pmlog', 'schedulecourseids'));
        if (empty($courseids)) {
            mtrace('local_pmlog: no scheduled course IDs configured.');
            return;
        }

        foreach ($courseids as $courseid) {
            if (!$DB->record_exists('course', ['id' => $courseid])) {
                mtrace('local_pmlog: skipping missing scheduled course ' . $courseid . '.');
                continue;
            }

            $task = new build_log_adhoc();
            $task->set_custom_data((object)[
                'courseid' => $courseid,
                'studentonly' => !empty(get_config('local_pmlog', 'schedulestudentonly')),
                'dedup' => !empty(get_config('local_pmlog', 'schedulededup')),
                'caseidstrategy' => (string)(get_config('local_pmlog', 'schedulecaseidstrategy')
                    ?: casebuilder::STRATEGY_USER_COURSE),
                'dedup_strict_cmid' => !empty(get_config('local_pmlog', 'schedulededupstrictcmid')),
                'dedupwindow' => max(0, (int)get_config('local_pmlog', 'schedulededupwindow')),
                'exportcsv' => !empty(get_config('local_pmlog', 'scheduleexportcsv')),
                'exportcsvdetailed' => !empty(get_config('local_pmlog', 'scheduleexportcsvdetailed')),
                'exportcsvnamed' => !empty(get_config('local_pmlog', 'scheduleexportcsvnamed')),
                'exportxes' => !empty(get_config('local_pmlog', 'scheduleexportxes')),
                'exportxesdetailed' => !empty(get_config('local_pmlog', 'scheduleexportxesdetailed')),
                'exportxesnamed' => !empty(get_config('local_pmlog', 'scheduleexportxesnamed')),
                'timestart' => 0,
                'timeend' => 0,
                'courseviewwindow' => max(0, (int)get_config('local_pmlog', 'schedulecourseviewwindow')),
                'moduleviewwindow' => max(0, (int)get_config('local_pmlog', 'schedulemoduleviewwindow')),
            ]);
            manager::queue_adhoc_task($task);

            set_config('build_running_course_' . $courseid, 1, 'local_pmlog');
            set_config('last_run_options_course_' . $courseid, json_encode([
                'studentonly' => !empty(get_config('local_pmlog', 'schedulestudentonly')),
                'dedup' => !empty(get_config('local_pmlog', 'schedulededup')),
                'caseidstrategy' => (string)(get_config('local_pmlog', 'schedulecaseidstrategy')
                    ?: casebuilder::STRATEGY_USER_COURSE),
                'dedup_strict_cmid' => !empty(get_config('local_pmlog', 'schedulededupstrictcmid')),
                'dedupwindow' => max(0, (int)get_config('local_pmlog', 'schedulededupwindow')),
                'exportcsv' => !empty(get_config('local_pmlog', 'scheduleexportcsv')),
                'exportcsvdetailed' => !empty(get_config('local_pmlog', 'scheduleexportcsvdetailed')),
                'exportcsvnamed' => !empty(get_config('local_pmlog', 'scheduleexportcsvnamed')),
                'exportxes' => !empty(get_config('local_pmlog', 'scheduleexportxes')),
                'exportxesdetailed' => !empty(get_config('local_pmlog', 'scheduleexportxesdetailed')),
                'exportxesnamed' => !empty(get_config('local_pmlog', 'scheduleexportxesnamed')),
                'timestart' => 0,
                'timeend' => 0,
                'courseviewwindow' => max(0, (int)get_config('local_pmlog', 'schedulecourseviewwindow')),
                'moduleviewwindow' => max(0, (int)get_config('local_pmlog', 'schedulemoduleviewwindow')),
            ], JSON_UNESCAPED_UNICODE), 'local_pmlog');

            mtrace('local_pmlog: queued scheduled build for course ' . $courseid . '.');
        }
    }

    /**
     * Parse a comma-separated course list.
     *
     * @param string $value Raw config value.
     * @return int[]
     */
    private function parse_course_ids(string $value): array {
        $parts = preg_split('/[\s,;]+/', trim($value));
        if (empty($parts)) {
            return [];
        }

        $courseids = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $courseid = (int)$part;
            if ($courseid > 0) {
                $courseids[] = $courseid;
            }
        }

        return array_values(array_unique($courseids));
    }
}
