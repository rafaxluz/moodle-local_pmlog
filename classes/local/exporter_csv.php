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
 * CSV Exporter.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class exporter_csv {
    public function export_course(int $courseid, string $filepath): void {
        global $DB;

        $fh = fopen($filepath, 'w');
        if ($fh === false) {
            throw new \moodle_exception('Could not open file for writing: ' . $filepath);
        }

        fputcsv($fh, ['caseid', 'activity', 'timestamp', 'userid', 'courseid', 'cmid', 'component', 'eventname', 'action', 'target']);

        $rs = $DB->get_recordset('local_pmlog_events', ['courseid' => $courseid], 'caseid ASC, timecreated ASC',
            'caseid, activity, timecreated, userid, courseid, cmid, component, eventname, action, target');

        foreach ($rs as $rec) {
            fputcsv($fh, [
                $rec->caseid,
                $rec->activity,
                gmdate('c', (int)$rec->timecreated), // ISO 8601
                (int)$rec->userid,
                (int)$rec->courseid,
                $rec->cmid ?? '',
                $rec->component ?? '',
                $rec->eventname ?? '',
                $rec->action ?? '',
                $rec->target ?? '',
            ]);
        }
        $rs->close();
        fclose($fh);
    }
}
