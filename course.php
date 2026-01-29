<?php
require_once(__DIR__ . '/../../config.php');

use local_pmlog\local\pipeline_service;
use local_pmlog\form\pipeline_form;

$courseid = required_param('courseid', PARAM_INT);
$queued = optional_param('queued', 0, PARAM_BOOL);

require_login($courseid);

$context = context_course::instance($courseid);
require_capability('local/pmlog:manage', $context);

$course = get_course($courseid);

$PAGE->set_url(new moodle_url('/local/pmlog/course.php', ['courseid' => $courseid]));
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
    echo $OUTPUT->notification(
        get_string('lastcsvexport', 'local_pmlog') . ': ' . userdate($csvtime) . ' — ' . s($csvname) . ' — ' . html_writer::link($durl, get_string('downloadcsv', 'local_pmlog')),
        \core\output\notification::NOTIFY_INFO
    );
}

$lastcourse = (int)get_config('local_pmlog', 'last_run_courseid');
if ($lastcourse === (int)$courseid) {
    $lastrun = (int)get_config('local_pmlog', 'last_run_time');
    if ($lastrun > 0) {
        $raw = (int)get_config('local_pmlog', 'last_run_raw');
        $stored = (int)get_config('local_pmlog', 'last_run_stored');
        $skipped = (int)get_config('local_pmlog', 'last_run_skipped');
        echo $OUTPUT->notification(
            get_string('lastrun', 'local_pmlog') . ': ' . userdate($lastrun) . " | Raw: {$raw} | Stored: {$stored} | Skipped: {$skipped}",
            \core\output\notification::NOTIFY_SUCCESS
        );
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

$sql = "
    SELECT e.userid, COUNT(*) AS n
      FROM {local_pmlog_events} e
     WHERE e.courseid = :courseid
     GROUP BY e.userid
     ORDER BY n DESC
";
$rows = $DB->get_records_sql($sql, ['courseid' => $courseid]);

if (!empty($rows)) {
    $userids = array_map(fn($r) => (int)$r->userid, $rows);
    list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid');
    $users = $DB->get_records_select('user', "id $insql", $inparams, '', 'id, firstname, lastname');
    $usersbyid = $users;

    $list = [];
    foreach ($rows as $r) {
        $uid = (int)$r->userid;
        $count = (int)$r->n;

        $uname = isset($usersbyid[$uid])
            ? fullname($usersbyid[$uid])
            : get_string('user', 'local_pmlog') . " {$uid}";

        $turl = new moodle_url('/local/pmlog/timeline.php', ['courseid' => $courseid, 'userid' => $uid]);

        $list[] = [
            'name' => $uname,
            'count' => $count,
            'timelineurl' => $turl->out(false),
        ];
    }
    
    echo $OUTPUT->render_from_template('local_pmlog/course_page', ['students' => ['list' => $list]]);
} else {
    echo $OUTPUT->notification(get_string('noeventsfound', 'local_pmlog'), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();
