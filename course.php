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

use local_pmlog\local\casebuilder;
use local_pmlog\form\pipeline_form;

/**
 * Convert a stored boolean-ish config value into a human label.
 *
 * @param bool $enabled Whether the option is enabled.
 * @return string
 */
function local_pmlog_yes_no(bool $enabled): string {
    return $enabled ? get_string('yes') : get_string('no');
}

/**
 * Format a stored timestamp range option.
 *
 * @param int $timestamp Unix timestamp or 0.
 * @return string
 */
function local_pmlog_format_optional_time(int $timestamp): string {
    if ($timestamp <= 0) {
        return get_string('notset', 'local_pmlog');
    }

    return userdate($timestamp);
}

/**
 * Convert a case strategy code into a human label.
 *
 * @param string $strategy Strategy code.
 * @return string
 */
function local_pmlog_case_strategy_label(string $strategy): string {
    $labels = [
        casebuilder::STRATEGY_USER_COURSE => get_string('caseidstrategy_user_course', 'local_pmlog'),
        casebuilder::STRATEGY_USER_DAY => get_string('caseidstrategy_user_day', 'local_pmlog'),
        casebuilder::STRATEGY_USER_COURSE_DAY => get_string('caseidstrategy_user_course_day', 'local_pmlog'),
    ];

    return $labels[$strategy] ?? get_string('caseidstrategy_user_course', 'local_pmlog');
}

/**
 * Render a collapsible information card.
 *
 * @param string $title Section title.
 * @param array $lines Content lines.
 * @param bool $open Whether the section should start open.
 * @return string
 */
function local_pmlog_render_info_card(string $title, array $lines, bool $open = true): string {
    if (empty($lines)) {
        return '';
    }

    $items = [];
    foreach ($lines as $line) {
        $items[] = html_writer::tag('li', $line, ['class' => 'mb-1']);
    }

    $summary = html_writer::tag('summary', s($title), ['class' => 'h4 mb-0']);
    $content = html_writer::div(
        html_writer::tag('ul', implode('', $items), ['class' => 'mb-0 pl-3']),
        'pt-3'
    );
    $attributes = ['class' => 'border rounded p-3 mb-3 bg-white'];
    if ($open) {
        $attributes['open'] = 'open';
    }

    return html_writer::tag('details', $summary . $content, $attributes);
}

/**
 * Build a formatted export line.
 *
 * @param string $label Export label.
 * @param int $timestamp Export time.
 * @param string $filename Export file name.
 * @param moodle_url $url Download URL.
 * @param string $downloadlabel Download label.
 * @return string
 */
function local_pmlog_export_line(
    string $label,
    int $timestamp,
    string $filename,
    moodle_url $url,
    string $downloadlabel
): string {
    return s($label) . ': ' . userdate($timestamp) . ' | ' . s($filename) . ' | ' .
        html_writer::link($url, $downloadlabel);
}

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
        'caseidstrategy' => (string)$data->caseidstrategy,
        'dedup_strict_cmid' => !empty($data->dedup_strict_cmid),
        'dedupwindow' => (int)$data->dedupwindow,
        'exportcsv' => !empty($data->exportcsv),
        'exportcsvdetailed' => !empty($data->exportcsvdetailed),
        'exportcsvnamed' => !empty($data->exportcsvnamed),
        'exportxes' => !empty($data->exportxes),
        'exportxesdetailed' => !empty($data->exportxesdetailed),
        'exportxesnamed' => !empty($data->exportxesnamed),
        'timestart' => (int)$data->timestart,
        'timeend' => (int)$data->timeend,
        'courseviewwindow' => (int)$data->courseviewwindow,
        'moduleviewwindow' => (int)$data->moduleviewwindow,
    ]);
    \core\task\manager::queue_adhoc_task($task);

    set_config('build_running_course_' . $courseid, 1, 'local_pmlog');
    set_config('last_run_options_course_' . $courseid, json_encode([
        'studentonly' => !empty($data->studentonly),
        'dedup' => !empty($data->dedup),
        'caseidstrategy' => (string)$data->caseidstrategy,
        'dedup_strict_cmid' => !empty($data->dedup_strict_cmid),
        'dedupwindow' => (int)$data->dedupwindow,
        'exportcsv' => !empty($data->exportcsv),
        'exportcsvdetailed' => !empty($data->exportcsvdetailed),
        'exportcsvnamed' => !empty($data->exportcsvnamed),
        'exportxes' => !empty($data->exportxes),
        'exportxesdetailed' => !empty($data->exportxesdetailed),
        'exportxesnamed' => !empty($data->exportxesnamed),
        'timestart' => (int)$data->timestart,
        'timeend' => (int)$data->timeend,
        'courseviewwindow' => (int)$data->courseviewwindow,
        'moduleviewwindow' => (int)$data->moduleviewwindow,
    ], JSON_UNESCAPED_UNICODE), 'local_pmlog');

    redirect(
        new moodle_url('/local/pmlog/course.php', ['courseid' => $courseid, 'queued' => 1])
    );
}

echo $OUTPUT->header();

$buildrunning = !empty(get_config('local_pmlog', 'build_running_course_' . $courseid));
$storedoptionsjson = (string)get_config('local_pmlog', 'last_run_options_course_' . $courseid);
$storedoptions = [];
if ($storedoptionsjson !== '') {
    $decoded = json_decode($storedoptionsjson, true);
    if (is_array($decoded)) {
        $storedoptions = $decoded;
    }
}

$csvname = (string)get_config('local_pmlog', 'last_csv_course_' . $courseid);
$csvtime = (int)get_config('local_pmlog', 'last_csv_time_course_' . $courseid);
$csvdetailedname = (string)get_config('local_pmlog', 'last_csv_detailed_course_' . $courseid);
$csvdetailedtime = (int)get_config('local_pmlog', 'last_csv_detailed_time_course_' . $courseid);
$csvnamedname = (string)get_config('local_pmlog', 'last_csv_named_course_' . $courseid);
$csvnamedtime = (int)get_config('local_pmlog', 'last_csv_named_time_course_' . $courseid);
$xesname = (string)get_config('local_pmlog', 'last_xes_course_' . $courseid);
$xestime = (int)get_config('local_pmlog', 'last_xes_time_course_' . $courseid);
$xesdetailedname = (string)get_config('local_pmlog', 'last_xes_detailed_course_' . $courseid);
$xesdetailedtime = (int)get_config('local_pmlog', 'last_xes_detailed_time_course_' . $courseid);
$xesnamedname = (string)get_config('local_pmlog', 'last_xes_named_course_' . $courseid);
$xesnamedtime = (int)get_config('local_pmlog', 'last_xes_named_time_course_' . $courseid);

$lastcourse = (int)get_config('local_pmlog', 'last_run_courseid');
$lastrun = 0;
$raw = 0;
$stored = 0;
$skipped = 0;
if ($lastcourse === (int)$courseid) {
    $lastrun = (int)get_config('local_pmlog', 'last_run_time');
    if ($lastrun > 0) {
        $raw = (int)get_config('local_pmlog', 'last_run_raw');
        $stored = (int)get_config('local_pmlog', 'last_run_stored');
        $skipped = (int)get_config('local_pmlog', 'last_run_skipped');
    }
}

if ($buildrunning) {
    echo $OUTPUT->notification(
        get_string('executionrunning', 'local_pmlog'),
        \core\output\notification::NOTIFY_WARNING
    );
}

if ($queued) {
    echo $OUTPUT->notification(
        get_string('executionqueued', 'local_pmlog'),
        \core\output\notification::NOTIFY_INFO
    );
}

$mform->display();

$statuslines = [];
if ($lastcourse === (int)$courseid && !empty($lastrun)) {
    $statuslines[] = get_string('lastrun', 'local_pmlog') . ': ' . userdate($lastrun);
    $statuslines[] = 'Raw: ' . $raw . ' | Stored: ' . $stored . ' | Skipped: ' . $skipped;
}
if ($buildrunning) {
    $statuslines[] = get_string('executionrunning', 'local_pmlog');
}

if (!empty($storedoptions)) {
    $summary = [];
    $summary[] = get_string('caseidstrategy', 'local_pmlog') . ': ' .
        local_pmlog_case_strategy_label((string)($storedoptions['caseidstrategy'] ?? casebuilder::STRATEGY_USER_COURSE));
    $summary[] = get_string('onlystudents', 'local_pmlog') . ': ' .
        local_pmlog_yes_no(!empty($storedoptions['studentonly']));
    $summary[] = get_string('dedup', 'local_pmlog') . ': ' .
        local_pmlog_yes_no(!empty($storedoptions['dedup']));
    $summary[] = get_string('dedup_strict_cmid', 'local_pmlog') . ': ' .
        local_pmlog_yes_no(!empty($storedoptions['dedup_strict_cmid']));
    $summary[] = get_string('dedupwindow', 'local_pmlog') . ': ' . (int)($storedoptions['dedupwindow'] ?? 0);
    $summary[] = get_string('courseviewwindow', 'local_pmlog') . ': ' . (int)($storedoptions['courseviewwindow'] ?? 0);
    $summary[] = get_string('moduleviewwindow', 'local_pmlog') . ': ' . (int)($storedoptions['moduleviewwindow'] ?? 0);
    $summary[] = get_string('exportcsv', 'local_pmlog') . ': ' .
        local_pmlog_yes_no(!empty($storedoptions['exportcsv']));
    $summary[] = get_string('exportcsv', 'local_pmlog') . ' (' . get_string('detailed', 'local_pmlog') . '): ' .
        local_pmlog_yes_no(!empty($storedoptions['exportcsvdetailed']));
    $summary[] = get_string('exportcsv', 'local_pmlog') . ' (' . get_string('named', 'local_pmlog') . '): ' .
        local_pmlog_yes_no(!empty($storedoptions['exportcsvnamed']));
    $summary[] = get_string('exportxes', 'local_pmlog') . ': ' .
        local_pmlog_yes_no(!empty($storedoptions['exportxes']));
    $summary[] = get_string('exportxes', 'local_pmlog') . ' (' . get_string('detailed', 'local_pmlog') . '): ' .
        local_pmlog_yes_no(!empty($storedoptions['exportxesdetailed']));
    $summary[] = get_string('exportxes', 'local_pmlog') . ' (' . get_string('named', 'local_pmlog') . '): ' .
        local_pmlog_yes_no(!empty($storedoptions['exportxesnamed']));
    $summary[] = get_string('startdate', 'local_pmlog') . ': ' .
        local_pmlog_format_optional_time((int)($storedoptions['timestart'] ?? 0));
    $summary[] = get_string('enddate', 'local_pmlog') . ': ' .
        local_pmlog_format_optional_time((int)($storedoptions['timeend'] ?? 0));
}

$exportlines = [];
if ($csvname !== '' && $csvtime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastcsvexport', 'local_pmlog'),
        $csvtime,
        $csvname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'csv']),
        get_string('downloadcsv', 'local_pmlog')
    );
}
if ($csvdetailedname !== '' && $csvdetailedtime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastcsvexportdetailed', 'local_pmlog'),
        $csvdetailedtime,
        $csvdetailedname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'csvdetailed']),
        get_string('downloadcsvdetailed', 'local_pmlog')
    );
}
if ($csvnamedname !== '' && $csvnamedtime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastcsvexportnamed', 'local_pmlog'),
        $csvnamedtime,
        $csvnamedname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'csvnamed']),
        get_string('downloadcsvnamed', 'local_pmlog')
    );
}
if ($xesname !== '' && $xestime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastxesexport', 'local_pmlog'),
        $xestime,
        $xesname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'xes']),
        get_string('downloadxes', 'local_pmlog')
    );
}
if ($xesdetailedname !== '' && $xesdetailedtime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastxesexportdetailed', 'local_pmlog'),
        $xesdetailedtime,
        $xesdetailedname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'xesdetailed']),
        get_string('downloadxesdetailed', 'local_pmlog')
    );
}
if ($xesnamedname !== '' && $xesnamedtime > 0) {
    $exportlines[] = local_pmlog_export_line(
        get_string('lastxesexportnamed', 'local_pmlog'),
        $xesnamedtime,
        $xesnamedname,
        new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid, 'format' => 'xesnamed']),
        get_string('downloadxesnamed', 'local_pmlog')
    );
}

echo local_pmlog_render_info_card(get_string('executionstatus', 'local_pmlog'), $statuslines);
echo local_pmlog_render_info_card(get_string('availableexports', 'local_pmlog'), $exportlines, false);
echo local_pmlog_render_info_card(get_string('lastrunsettings', 'local_pmlog'), $summary ?? [], false);

$studenttimelinecontent = '';

global $DB;

$studenttimelinecontent .= html_writer::start_div('d-flex mb-3 align-items-center gap-3');

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

$studenttimelinecontent .= $OUTPUT->render($sortselector);
$studenttimelinecontent .= $OUTPUT->render($pageselector);

$studenttimelinecontent .= html_writer::end_div();

$totalcount = $DB->count_records_sql("SELECT COUNT(DISTINCT userid) FROM {local_pmlog_events} WHERE courseid = ?", [$courseid]);

$pagingbar = new paging_bar($totalcount, $page, $perpage, $url, 'page');
$studenttimelinecontent .= $OUTPUT->render($pagingbar);

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

    $studenttimelinecontent .= $OUTPUT->render_from_template(
        'local_pmlog/course_page',
        ['students' => ['list' => $list]]
    );
    $studenttimelinecontent .= $OUTPUT->render($pagingbar);
} else {
    $studenttimelinecontent .= $OUTPUT->notification(
        get_string('noeventsfound', 'local_pmlog'),
        \core\output\notification::NOTIFY_INFO
    );
}

echo html_writer::tag(
    'details',
    html_writer::tag('summary', get_string('studenttimelines', 'local_pmlog'), ['class' => 'h4 mb-0']) .
    html_writer::div($studenttimelinecontent, 'pt-3'),
    ['class' => 'border rounded p-3 mb-3 bg-white']
);

echo $OUTPUT->footer();
