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
 * Module labeler.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Module labeler class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modulelabeler {
    /**
     * Given a cmid, return a specific "X open" label (Quiz open, Page open, ...)
     * or null if unknown.
     *
     * @param int|null $cmid The course module ID.
     * @return string|null The specific label or null.
     */
    public function label_from_cmid(?int $cmid): ?string {
        global $DB;

        if (empty($cmid)) {
            return null;
        }

        $sql = "
            SELECT m.name
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module
             WHERE cm.id = :cmid
        ";
        $modname = $DB->get_field_sql($sql, ['cmid' => $cmid], IGNORE_MISSING);
        if (empty($modname)) {
            return null;
        }

        $modname = strtolower((string)$modname);

        $map = [
            'quiz' => get_string('activity_quiz_open', 'local_pmlog'),
            'page' => get_string('activity_page_open', 'local_pmlog'),
            'url' => get_string('activity_url_open', 'local_pmlog'),
            'assign' => get_string('activity_assign_open', 'local_pmlog'),
            'forum' => get_string('activity_forum_open', 'local_pmlog'),
            'lesson' => get_string('activity_lesson_open', 'local_pmlog'),
            'resource' => get_string('activity_resource_open', 'local_pmlog'),
            'book' => get_string('activity_book_open', 'local_pmlog'),
            'folder' => get_string('activity_folder_open', 'local_pmlog'),
            'label' => get_string('activity_label_open', 'local_pmlog'),
            'feedback' => get_string('activity_feedback_open', 'local_pmlog'),
            'choice' => get_string('activity_choice_open', 'local_pmlog'),
            'survey' => get_string('activity_survey_open', 'local_pmlog'),
            'scorm' => get_string('activity_scorm_open', 'local_pmlog'),
            'h5pactivity' => get_string('activity_h5pactivity_open', 'local_pmlog'),
        ];

        return $map[$modname] ?? get_string('activity_generic_open', 'local_pmlog', ucfirst($modname));
    }
}