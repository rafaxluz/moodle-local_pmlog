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
 * Scheduled settings page.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_pmlog\form\scheduled_settings_form;
use local_pmlog\local\casebuilder;

admin_externalpage_setup('local_pmlog_schedule');

$courseoptions = $DB->get_records_select_menu(
    'course',
    'id <> :siteid',
    ['siteid' => SITEID],
    'fullname ASC',
    "id, " . $DB->sql_concat('fullname', $DB->sql_concat("' ('", $DB->sql_concat('shortname', "')'"))) . ' AS courselabel'
);

$mform = new scheduled_settings_form(null, ['courseoptions' => $courseoptions]);

$currentcourseids = preg_split('/[\s,;]+/', (string)get_config('local_pmlog', 'schedulecourseids'), -1, PREG_SPLIT_NO_EMPTY);
$currentcourseids = array_map('intval', $currentcourseids ?: []);

$defaults = [
    'scheduleenabled' => (int)!empty(get_config('local_pmlog', 'scheduleenabled')),
    'schedulecourseids' => $currentcourseids,
    'schedulecaseidstrategy' => (string)(get_config('local_pmlog', 'schedulecaseidstrategy') ?: casebuilder::STRATEGY_USER_COURSE),
    'schedulestudentonly' => (int)!empty(get_config('local_pmlog', 'schedulestudentonly')),
    'schedulededup' => (int)!empty(get_config('local_pmlog', 'schedulededup')),
    'schedulededupstrictcmid' => (int)!empty(get_config('local_pmlog', 'schedulededupstrictcmid')),
    'schedulededupwindow' => (int)(get_config('local_pmlog', 'schedulededupwindow') ?: 600),
    'schedulecourseviewwindow' => (int)(get_config('local_pmlog', 'schedulecourseviewwindow') ?: 1800),
    'schedulemoduleviewwindow' => (int)(get_config('local_pmlog', 'schedulemoduleviewwindow') ?: 600),
    'scheduleexportcsv' => (int)!empty(get_config('local_pmlog', 'scheduleexportcsv')),
    'scheduleexportcsvdetailed' => (int)!empty(get_config('local_pmlog', 'scheduleexportcsvdetailed')),
    'scheduleexportcsvnamed' => (int)!empty(get_config('local_pmlog', 'scheduleexportcsvnamed')),
    'scheduleexportxes' => (int)!empty(get_config('local_pmlog', 'scheduleexportxes')),
    'scheduleexportxesdetailed' => (int)!empty(get_config('local_pmlog', 'scheduleexportxesdetailed')),
    'scheduleexportxesnamed' => (int)!empty(get_config('local_pmlog', 'scheduleexportxesnamed')),
];
$mform->set_data($defaults);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/admin/category.php', ['category' => 'localplugins']));
} else if ($data = $mform->get_data()) {
    set_config('scheduleenabled', !empty($data->scheduleenabled), 'local_pmlog');
    set_config('schedulecourseids', implode(',', array_map('intval', $data->schedulecourseids ?? [])), 'local_pmlog');
    set_config('schedulecaseidstrategy', (string)$data->schedulecaseidstrategy, 'local_pmlog');
    set_config('schedulestudentonly', !empty($data->schedulestudentonly), 'local_pmlog');
    set_config('schedulededup', !empty($data->schedulededup), 'local_pmlog');
    set_config('schedulededupstrictcmid', !empty($data->schedulededupstrictcmid), 'local_pmlog');
    set_config('schedulededupwindow', max(0, (int)$data->schedulededupwindow), 'local_pmlog');
    set_config('schedulecourseviewwindow', max(0, (int)$data->schedulecourseviewwindow), 'local_pmlog');
    set_config('schedulemoduleviewwindow', max(0, (int)$data->schedulemoduleviewwindow), 'local_pmlog');
    set_config('scheduleexportcsv', !empty($data->scheduleexportcsv), 'local_pmlog');
    set_config('scheduleexportcsvdetailed', !empty($data->scheduleexportcsvdetailed), 'local_pmlog');
    set_config('scheduleexportcsvnamed', !empty($data->scheduleexportcsvnamed), 'local_pmlog');
    set_config('scheduleexportxes', !empty($data->scheduleexportxes), 'local_pmlog');
    set_config('scheduleexportxesdetailed', !empty($data->scheduleexportxesdetailed), 'local_pmlog');
    set_config('scheduleexportxesnamed', !empty($data->scheduleexportxesnamed), 'local_pmlog');

    redirect(
        new moodle_url('/local/pmlog/schedule.php'),
        get_string('changessaved'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_pmlog'));
echo $OUTPUT->notification(get_string('pluginadministration', 'local_pmlog'), \core\output\notification::NOTIFY_INFO);
$mform->display();
echo $OUTPUT->footer();
