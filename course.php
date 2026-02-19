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
 * Course main page.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_pmlog\local\pipeline_service;
use local_pmlog\form\pipeline_form;

$courseid = required_param('courseid', PARAM_INT);
$queued = optional_param('queued', 0, PARAM_BOOL);

require_login($courseid);

$context = context_course::instance($courseid);
require_capability('local/pmlog:manage', $context);

$course = get_course($courseid);

$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);
$sort    = optional_param('sort', 'events_desc', PARAM_ALPHANUMEXT);

$url = new moodle_url('/local/pmlog/course.php', ['courseid' => $courseid]);
if ($perpage !== 100) {
    $url->param('perpage', $perpage);
}
if ($sort !== 'events_desc') {
    $url->param('sort', $sort);
}
if ($queued) {
    $url->param('queued', 1);
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_pmlog'));
$PAGE->set_heading(format_string($course->fullname));

$mform = new pipeline_form(null, ['courseid' => $courseid, 'isadmin' => false]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/pmlog/course.php', ['courseid' => $courseid]));
} else if ($data = $mform->get_data()) {
    require_sesskey();

    $task = new \local_pmlog\task\build_log_adhoc();
    $task->set_custom_data((object)[
        'courseid' => $courseid,
        'studentonly' => !empty($data->studentonly),
        'dedup' => !empty($data->dedup),
        'dedup_strict_cmid' => !empty($data->dedup_strict_cmid),
        'dedupwindow' => (int)$data->dedupwindow,
        'exportcsv' => !empty($data->exportcsv),
        'timestart' => (int)$data->timestart,
        'timeend' => (int)$data->timeend,
        'courseviewwindow' => (int)$data->courseviewwindow,
        'moduleviewwindow' => (int)$data->moduleviewwindow,
    ]);
    \core\task\manager::queue_adhoc_task($task);

    redirect(
        new moodle_url('/local/pmlog/course.php', ['courseid' => $courseid, 'queued' => 1])
    );
}

echo $OUTPUT->header();

$csvname = (string)get_config('local_pmlog', 'last_csv_course_' . $courseid);
$csvtime = (int)get_config('local_pmlog', 'last_csv_time_course_' . $courseid);

    if ($csvname !== '' && $csvtime > 0) {
    $durl = new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid]);
    $msg = get_string('lastcsvexport', 'local_pmlog') . ': ' . userdate($csvtime) . ' — ' . s($csvname) .
           ' — ' . html_writer::link($durl, get_string('downloadcsv', 'local_pmlog'));
    echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_INFO);
}

$lastcourse = (int)get_config('local_pmlog', 'last_run_courseid');
if ($lastcourse === (int)$courseid) {
    $lastrun = (int)get_config('local_pmlog', 'last_run_time');
    if ($lastrun > 0) {
        $raw = (int)get_config('local_pmlog', 'last_run_raw');
        $stored = (int)get_config('local_pmlog', 'last_run_stored');
        $skipped = (int)get_config('local_pmlog', 'last_run_skipped');
        $msg = get_string('lastrun', 'local_pmlog') . ': ' . userdate($lastrun) .
               " | Raw: {$raw} | Stored: {$stored} | Skipped: {$skipped}";
        echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_SUCCESS);
    }
}

if ($queued) {
    echo $OUTPUT->notification(
        get_string('executionqueued', 'local_pmlog'),
        \core\output\notification::NOTIFY_INFO
    );
}

$mform->display();

echo html_writer::tag('h4', get_string('studenttimelines', 'local_pmlog'));

global $DB;

echo html_writer::start_div('d-flex mb-3 align-items-center gap-3');

$sortoptions = [
    'events_desc' => get_string('sort_events_desc', 'local_pmlog'),
    'events_asc'  => get_string('sort_events_asc', 'local_pmlog'),
    'name_asc'    => get_string('sort_name_asc', 'local_pmlog'),
    'name_desc'   => get_string('sort_name_desc', 'local_pmlog'),
];

$sortselector = new single_select($url, 'sort', $sortoptions, $sort, null);
$sortselector->set_label(get_string('sortby', 'core'));

$options = [
    20 => 20,
    50 => 50,
    100 => 100,
    5000 => get_string('all', 'core'),
];
$pageselector = new single_select($url, 'perpage', $options, $perpage, null);
$pageselector->set_label(get_string('perpage', 'moodle'));

echo $OUTPUT->render($sortselector);
echo $OUTPUT->render($pageselector);

echo html_writer::end_div();

$totalcount = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {local_pmlog_events} WHERE courseid = ?", [$courseid]);

$pagingbar = new paging_bar($totalcount, $page, $perpage, $url, 'page');
echo $OUTPUT->render($pagingbar);

switch ($sort) {
    case 'events_asc':
        $orderby = "n ASC, u.firstname ASC, u.lastname ASC";
        break;
    case 'name_asc':
        $orderby = "u.firstname ASC, u.lastname ASC";
        break;
    case 'name_desc':
        $orderby = "u.firstname DESC, u.lastname DESC";
        break;
    case 'events_desc':
    default:
        $orderby = "n DESC, u.firstname ASC, u.lastname ASC";
        break;
}

$sql = "
    SELECT e.userid, u.firstname, u.lastname, COUNT(e.id) AS n
      FROM {local_pmlog_events} e
      JOIN {user} u ON u.id = e.userid
     WHERE e.courseid = :courseid
     GROUP BY e.userid, u.firstname, u.lastname
     ORDER BY $orderby
";
$rows = $DB->get_records_sql($sql, ['courseid' => $courseid], $page * $perpage, $perpage);

if (!empty($rows)) {
    $list = [];
    foreach ($rows as $r) {
        $uid = (int)$r->userid;
        $count = (int)$r->n;
        $uname = fullname($r);

        $turl = new moodle_url('/local/pmlog/timeline.php', ['courseid' => $courseid, 'userid' => $uid]);

        $list[] = [
            'name' => $uname,
            'count' => $count,
            'timelineurl' => $turl->out(false),
        ];
    }
    
    echo $OUTPUT->render_from_template('local_pmlog/course_page', ['students' => ['list' => $list]]);
    
    echo $OUTPUT->render($pagingbar);
} else {
    echo $OUTPUT->notification(get_string('noeventsfound', 'local_pmlog'), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();