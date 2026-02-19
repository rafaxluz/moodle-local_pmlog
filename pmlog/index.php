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
 * Plugin index page.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_pmlog_index');

use local_pmlog\form\course_lookup_form;

$courseid = optional_param('courseid', 0, PARAM_INT);

$mform = new course_lookup_form(null, null, 'get');

$csvname = '';
$csvtime = 0;
$coursename = '';

if ($formdata = $mform->get_data()) {
    $courseid = (int)$formdata->courseid;
}

if ($courseid > 0) {
    $mform->set_data(['courseid' => $courseid]);
    
    $course = $DB->get_record('course', ['id' => $courseid]);
    if ($course) {
        $coursename = format_string($course->fullname);
        
        $csvname = (string)get_config('local_pmlog', 'last_csv_course_' . $courseid);
        $csvtime = (int)get_config('local_pmlog', 'last_csv_time_course_' . $courseid);
    }
}

echo $OUTPUT->header();

$mform->display();

if ($courseid > 0) {
    echo html_writer::tag('h3', get_string('course', 'core') . ': ' . $coursename);
    
    if ($csvname !== '' && $csvtime > 0) {
        $durl = new moodle_url('/local/pmlog/download.php', ['courseid' => $courseid]);
        echo $OUTPUT->notification(
            get_string('lastcsvexport', 'local_pmlog') . ': ' . userdate($csvtime) . ' — ' . s($csvname) . ' — ' . html_writer::link($durl, get_string('downloadcsv', 'local_pmlog')),
            \core\output\notification::NOTIFY_INFO
        );
    } else if ($coursename) {
         echo $OUTPUT->notification(get_string('noeventsfound', 'local_pmlog'), \core\output\notification::NOTIFY_WARNING);
    } else {
        echo $OUTPUT->notification(get_string('invalidcourseid', 'error'), \core\output\notification::NOTIFY_ERROR);
    }
}

echo $OUTPUT->footer();
