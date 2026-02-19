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
 * User resolver.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class userresolver {
    /**
     * Get enrolled users in the course that have the "student" role (shortname) in the course context
     * (including roles inherited from parent contexts).
     *
     * @return int[] userids
     */
    public function get_student_userids(int $courseid): array {
        global $DB;

        $context = \context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            return [];
        }

        $studentroleid = (int)$DB->get_field('role', 'id', ['shortname' => 'student'], IGNORE_MISSING);
        if (empty($studentroleid)) {
            $studentroleid = (int)$DB->get_field('role', 'id', ['archetype' => 'student'], IGNORE_MISSING);
        }
        if (empty($studentroleid)) {
            return [];
        }

        $enrolled = get_enrolled_users($context, '', 0, 'u.id');
        if (empty($enrolled)) {
            return [];
        }

        $ids = [];
        foreach ($enrolled as $u) {
            $userid = (int)$u->id;

            $roles = get_user_roles($context, $userid, true);
            foreach ($roles as $r) {
                if ((int)$r->roleid === $studentroleid) {
                    $ids[] = $userid;
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    public function is_student_in_course(int $courseid, int $userid): bool {
        global $DB;

        $context = \context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            return false;
        }

        if (!is_enrolled($context, $userid, '', true)) {
            return false;
        }

        $studentroleid = (int)$DB->get_field('role', 'id', ['shortname' => 'student'], IGNORE_MISSING);
        if (empty($studentroleid)) {
            $studentroleid = (int)$DB->get_field('role', 'id', ['archetype' => 'student'], IGNORE_MISSING);
        }
        if (empty($studentroleid)) {
            return false;
        }

        $roles = get_user_roles($context, $userid, true);
        foreach ($roles as $r) {
            if ((int)$r->roleid === $studentroleid) {
                return true;
            }
        }
        return false;
    }

}
