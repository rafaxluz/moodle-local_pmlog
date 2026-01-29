<?php
require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('local/pmlog:manage', context_system::instance());

$courseid = required_param('courseid', PARAM_INT);
$userid   = required_param('userid', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/pmlog/timeline.php', ['courseid' => $courseid, 'userid' => $userid]));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Student timeline');
$PAGE->set_heading('Student timeline');

echo $OUTPUT->header();

global $DB;

$user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, shortname', MUST_EXIST);

$modinfo = get_fast_modinfo($courseid);

$sql = "
    SELECT id, activity, timecreated, cmid
      FROM {local_pmlog_events}
     WHERE courseid = :courseid
       AND userid = :userid
     ORDER BY timecreated ASC, id ASC
";
$params = ['courseid' => $courseid, 'userid' => $userid];

$rs = $DB->get_recordset_sql($sql, $params);

$timelinewindow = 600;
$lastshown = [];

$events = [];

foreach ($rs as $e) {
    $t = userdate((int)$e->timecreated);
    $label = s($e->activity);

    $suffix = '';
    if (!empty($e->cmid)) {
        $cmid = (int)$e->cmid;

        if (!empty($modinfo->cms[$cmid])) {
            $cm = $modinfo->cms[$cmid];

            $cmname = format_string($cm->name);
            $modname = s($cm->modname);

            $suffix = " — {$cmname} ({$modname})";
        }
    }

    $displaykey = $label . '|' . strip_tags($suffix);

    $tt = (int)$e->timecreated;
    if (isset($lastshown[$displaykey]) && ($tt - (int)$lastshown[$displaykey]) <= $timelinewindow) {
        continue;
    }
    $lastshown[$displaykey] = $tt;

    $events[] = [
        'time' => $t,
        'label' => $label,
        'suffix' => $suffix
    ];
}
$rs->close();

$data = [
    'userfullname' => fullname($user),
    'coursename' => format_string($course->fullname),
    'events' => $events
];

echo $OUTPUT->render_from_template('local_pmlog/timeline_page', $data);

echo $OUTPUT->footer();
