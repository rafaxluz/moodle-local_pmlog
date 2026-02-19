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
 * Student timeline page.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('local/pmlog:manage', context_system::instance());

$courseid = required_param('courseid', PARAM_INT);
$userid   = required_param('userid', PARAM_INT);
$page     = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 100, PARAM_INT);

$url = new moodle_url('/local/pmlog/timeline.php', ['courseid' => $courseid, 'userid' => $userid]);
if ($perpage !== 100) {
    $url->param('perpage', $perpage);
}

$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('studenttimeline', 'local_pmlog'));
$PAGE->set_heading(get_string('studenttimeline', 'local_pmlog'));

echo $OUTPUT->header();

global $DB;

$user = $DB->get_record('user', ['id' => $userid], 'id, firstname, lastname', MUST_EXIST);
$course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, shortname', MUST_EXIST);

$modinfo = get_fast_modinfo($courseid);

$options = [
    20 => 20,
    50 => 50,
    100 => 100,
    5000 => get_string('all', 'core'),
];
$selector = new single_select($url, 'perpage', $options, $perpage, null);
$selector->set_label(get_string('perpage', 'moodle'));

echo html_writer::div($OUTPUT->render($selector), 'mb-3');

$totalcount = $DB->count_records('local_pmlog_events', ['courseid' => $courseid, 'userid' => $userid]);

$pagingbar = new paging_bar($totalcount, $page, $perpage, $url, 'page');
echo $OUTPUT->render($pagingbar);

$sql = "
    SELECT id, activity, timecreated, cmid
      FROM {local_pmlog_events}
     WHERE courseid = :courseid
       AND userid = :userid
     ORDER BY timecreated ASC, id ASC
";
$params = ['courseid' => $courseid, 'userid' => $userid];

$records = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

$timelinewindow = 600;
$lastshown = [];

$events = [];

foreach ($records as $e) {
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
        'suffix' => $suffix,
    ];
}

echo $OUTPUT->render_from_template('local_pmlog/timeline_page', [
    'userfullname' => fullname($user),
    'coursename' => format_string($course->fullname),
    'events' => $events,
]);

echo $OUTPUT->render($pagingbar);

echo $OUTPUT->footer();
