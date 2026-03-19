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

/**
 * Activity group mapping.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activitygroupmap {
    /** @var array<string, string>|null Cache of translated group labels. */
    private static ?array $groups = null;

    /**
     * Map a detailed activity label to a coarse activity group.
     *
     * @param string $activity The activity name/label.
     * @return string The mapped group name.
     */
    public static function group(string $activity): string {
        $a = strtolower($activity);
        $groups = self::groups();

        if (str_contains($a, 'quiz')) {
            return $groups['quiz'];
        }

        if (str_contains($a, 'assignment')) {
            return $groups['assignment'];
        }

        if (str_contains($a, 'forum')) {
            return $groups['forum'];
        }

        if (str_contains($a, 'lesson')) {
            return $groups['lesson'];
        }

        if (
            str_contains($a, 'page') ||
            str_contains($a, 'url') ||
            str_contains($a, 'content')
        ) {
            return $groups['content'];
        }

        if (str_contains($a, 'completion')) {
            return $groups['progress'];
        }

        return $groups['other'];
    }

    /**
     * Get translated activity-group labels.
     *
     * @return array<string, string>
     */
    private static function groups(): array {
        if (self::$groups !== null) {
            return self::$groups;
        }

        self::$groups = [
            'quiz' => get_string('group_quiz', 'local_pmlog'),
            'assignment' => get_string('group_assignment', 'local_pmlog'),
            'forum' => get_string('group_forum', 'local_pmlog'),
            'lesson' => get_string('group_lesson', 'local_pmlog'),
            'content' => get_string('group_content', 'local_pmlog'),
            'progress' => get_string('group_progress', 'local_pmlog'),
            'other' => get_string('group_other', 'local_pmlog'),
        ];

        return self::$groups;
    }
}
