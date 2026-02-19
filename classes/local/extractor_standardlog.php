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
 * Standard log extractor.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class extractor_standardlog {
    /**
     * Extract raw event data from the standard log.
     *
     * @param int $courseid The course ID.
     * @param int $timestart Optional start timestamp.
     * @param int $timeend Optional end timestamp.
     * @param array $userids Optional list of user IDs to filter by.
     * @param array $options Additional options (e.g. 'studentonly').
     * @return array<int, \stdClass> Raw events.
     */
    public function extract(int $courseid, int $timestart = 0, int $timeend = 0, array $userids = [], array $options = []): array {
        global $DB;

        $studentonly = !empty($options['studentonly']);

        // JOIN with user table to get user details and avoid separate lookups.
        $sql = "SELECT l.*, u.firstname, u.lastname, u.email, u.idnumber
                  FROM {logstore_standard_log} l
                  JOIN {user} u ON l.userid = u.id
                 WHERE l.courseid = :courseid";
        
        $params = ['courseid' => $courseid];

        if ($timestart > 0) {
            $sql .= " AND l.timecreated >= :timestart";
            $params['timestart'] = $timestart;
        }
        if ($timeend > 0) {
            $sql .= " AND l.timecreated <= :timeend";
            $params['timeend'] = $timeend;
        }

        if (!empty($userids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid');
            $sql .= " AND l.userid $insql";
            $params = array_merge($params, $inparams);
        }

        if ($studentonly) {
            $context = \context_course::instance($courseid, IGNORE_MISSING);
            if (!$context) {
                return [];
            }
            
            $studentroles = $DB->get_records_select_menu(
                'role',
                "shortname = ? OR archetype = ?",
                ['student', 'student'],
                '',
                'id, id'
            );
            if (empty($studentroles)) {
                 return [];
            }
            list($rinsql, $rinparams) = $DB->get_in_or_equal(array_keys($studentroles), SQL_PARAMS_NAMED, 'rid');

            $sql .= " AND EXISTS (
                        SELECT 1
                          FROM {role_assignments} ra
                         WHERE ra.userid = l.userid
                           AND ra.contextid = :contextid
                           AND ra.roleid $rinsql
                      )";
            $params['contextid'] = $context->id;
            $params = array_merge($params, $rinparams);
        }

        $sql .= " ORDER BY l.timecreated ASC, l.id ASC";

        return $DB->get_records_sql($sql, $params);
    }
}
