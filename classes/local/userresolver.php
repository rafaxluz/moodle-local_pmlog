<?php
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
