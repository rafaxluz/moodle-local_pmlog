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
     * @return array<int, \stdClass> raw events
     */
    public function extract(int $courseid, int $timestart = 0, int $timeend = 0, array $userids = []): array {
        global $DB;

        $params = ['courseid' => $courseid];
        $where = "courseid = :courseid";

        if ($timestart > 0) {
            $where .= " AND timecreated >= :timestart";
            $params['timestart'] = $timestart;
        }
        if ($timeend > 0) {
            $where .= " AND timecreated <= :timeend";
            $params['timeend'] = $timeend;
        }
        if (!empty($userids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'uid');
            $where .= " AND userid $insql";
            $params = array_merge($params, $inparams);
        }

        return $DB->get_records_select('logstore_standard_log', $where, $params, 'timecreated ASC, id ASC');
    }
}
