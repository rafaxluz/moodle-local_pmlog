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
 * Local library specific to this plugin.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add a link to the course navigation menu.
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course object
 * @param context_course $context The context of the course
 */
function local_pmlog_extend_navigation_course($navigation, $course, $context) {
    if (empty($course) || empty($context)) {
        return;
    }

    if (!has_capability('local/pmlog:manage', $context)) {
        return;
    }

    $url = new moodle_url('/local/pmlog/course.php', ['courseid' => $course->id]);

    $navigation->add(
        get_string('pluginname', 'local_pmlog'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_pmlog_course_link',
        new pix_icon('i/report', '')
    );
}
