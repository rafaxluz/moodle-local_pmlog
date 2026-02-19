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
 * Activity group mapping.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class activitygroupmap {

    /**
     * Map a detailed activity label to a coarse activity group.
     */
    public static function group(string $activity): string {
        $a = strtolower($activity);

        if (str_contains($a, 'quiz')) {
            return get_string('group_quiz', 'local_pmlog');
        }

        if (str_contains($a, 'assignment')) {
            return get_string('group_assignment', 'local_pmlog');
        }

        if (str_contains($a, 'forum')) {
            return get_string('group_forum', 'local_pmlog');
        }

        if (str_contains($a, 'lesson')) {
            return get_string('group_lesson', 'local_pmlog');
        }

        if (
            str_contains($a, 'page') ||
            str_contains($a, 'url') ||
            str_contains($a, 'content')
        ) {
            return get_string('group_content', 'local_pmlog');
        }

        if (str_contains($a, 'completion')) {
            return get_string('group_progress', 'local_pmlog');
        }

        return get_string('group_other', 'local_pmlog');
    }
}
