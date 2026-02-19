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
 * Label mapping utilities.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Label mapping utilities.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class labelmap {

    /**
     * Return a human-friendly activity label for a raw log row.
     * If no mapping matches, fallback to "action:target".
     *
     * @param \stdClass $raw The raw event object.
     * @return string The human-friendly label.
     */
    public static function map(\stdClass $raw): string {
        $component = $raw->component ?? '';
        $action = $raw->action ?? '';
        $target = $raw->target ?? '';

        $key = "{$component}|{$action}|{$target}";

        $rules = [
            'core|viewed|course' => get_string('event_course_view', 'local_pmlog'),
            'core|viewed|course_module' => get_string('event_activity_resource_open', 'local_pmlog'),
            'core|viewed|content_page' => get_string('event_page_view', 'local_pmlog'),
            'core|viewed|grade_report' => get_string('event_grade_view', 'local_pmlog'),
            'core|updated|course_module_completion' => get_string('event_completion_update', 'local_pmlog'),

            'core|graded|user' => get_string('event_grade_given', 'local_pmlog'),

            'mod_assign|viewed|course_module' => get_string('event_assign_status_view', 'local_pmlog'), // "Assignment open" usually maps to status view if not specific.
            'mod_assign|viewed|submission_status' => get_string('event_assign_status_view', 'local_pmlog'),
            'mod_assign|viewed|submission_form' => get_string('event_assign_submission_form_view', 'local_pmlog'),
            'mod_assign|viewed|submission_confirmation_form' => get_string('event_assign_confirm_form_view', 'local_pmlog'),
            'mod_assign|created|submission' => get_string('event_assign_submission_create', 'local_pmlog'),
            'mod_assign|submitted|submission' => get_string('event_assign_submit', 'local_pmlog'),
            'mod_assign|uploaded|submission' => get_string('event_assign_upload', 'local_pmlog'),

            'mod_quiz|viewed|course_module' => get_string('activity_quiz_open', 'local_pmlog'),
            'mod_quiz|started|attempt' => get_string('event_quiz_attempt_start', 'local_pmlog'),
            'mod_quiz|viewed|attempt' => get_string('event_quiz_attempt_view', 'local_pmlog'),
            'mod_quiz|viewed|attempt_summary' => get_string('event_quiz_attempt_summary_view', 'local_pmlog'),
            'mod_quiz|submitted|attempt' => get_string('event_quiz_attempt_submit', 'local_pmlog'),
            'mod_quiz|reviewed|attempt' => get_string('event_quiz_review', 'local_pmlog'),

            'mod_forum|viewed|course_module' => get_string('activity_forum_open', 'local_pmlog'),
            'mod_forum|viewed|discussion' => get_string('event_forum_discussion_view', 'local_pmlog'),
            'mod_forum|created|discussion' => get_string('event_forum_discussion_create', 'local_pmlog'),
            'mod_forum|created|post' => get_string('event_forum_post_create', 'local_pmlog'),

            'mod_page|viewed|course_module' => get_string('activity_page_open', 'local_pmlog'),
            'mod_url|viewed|course_module' => get_string('activity_url_open', 'local_pmlog'),
        ];

        if (isset($rules[$key])) {
            return $rules[$key];
        }

        $key2 = "{$component}|{$action}|*";
        if (isset($rules[$key2])) {
            return $rules[$key2];
        }

        $action = strtolower(trim((string)$action));
        $target = strtolower(trim((string)$target));

        $verbmap = [
            'viewed' => get_string('verb_view', 'local_pmlog'),
            'started' => get_string('verb_start', 'local_pmlog'),
            'resumed' => get_string('verb_resume', 'local_pmlog'),
            'restarted' => get_string('verb_restart', 'local_pmlog'),
            'ended' => get_string('verb_end', 'local_pmlog'),
            'attempted' => get_string('verb_attempt', 'local_pmlog'),
            'submitted' => get_string('verb_submit', 'local_pmlog'),
            'uploaded' => get_string('verb_upload', 'local_pmlog'),
            'created' => get_string('verb_create', 'local_pmlog'),
            'updated' => get_string('verb_update', 'local_pmlog'),
            'deleted' => get_string('verb_delete', 'local_pmlog'),
            'accepted' => get_string('verb_accept', 'local_pmlog'),
            'reviewed' => get_string('verb_review', 'local_pmlog'),
            'graded' => get_string('verb_grade', 'local_pmlog'),
            'completed' => get_string('verb_complete', 'local_pmlog'),
        ];

        $verb = $verbmap[$action] ?? ($action !== '' ? ucfirst($action) : get_string('verb_do', 'local_pmlog'));

        if ($target !== '') {
            $nice = str_replace('_', ' ', $target);
            return trim($verb . ' ' . $nice);
        }

        if (!empty($raw->eventname)) {
            return (string)$raw->eventname;
        }

        return get_string('unknown', 'local_pmlog');
    }
}