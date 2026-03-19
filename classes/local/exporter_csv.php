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

/**
 * CSV Exporter class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exporter_csv {
    /** @var array<int, array<int, string|null>> Cache of course module names by course and CMID. */
    private array $cmnamecache = [];

    /**
     * Export course logs to CSV.
     *
     * @param int $courseid The course ID.
     * @param string $filepath The path to save the CSV file.
     * @param string $mode Export mode.
     * @throws \moodle_exception If the file cannot be opened.
     */
    public function export_course(
        int $courseid,
        string $filepath,
        string $mode = export_mode::STANDARD
    ): void {
        global $DB;

        $fh = fopen($filepath, 'w');
        if ($fh === false) {
            throw new \moodle_exception('Could not open file for writing: ' . $filepath);
        }

        fputcsv($fh, [
            'caseid',
            'activity',
            'timestamp',
            'userid',
            'courseid',
            'cmid',
            'component',
            'eventname',
            'action',
            'target',
        ]);

        $rs = $DB->get_recordset(
            'local_pmlog_events',
            ['courseid' => $courseid],
            'caseid ASC, timecreated ASC',
            'caseid, activity, timecreated, userid, courseid, cmid, component, eventname, action, target'
        );

        foreach ($rs as $rec) {
            fputcsv($fh, [
                $rec->caseid,
                $this->format_activity($courseid, (string)$rec->activity, $rec->cmid ?? null, $mode),
                gmdate('c', (int)$rec->timecreated), // ISO 8601.
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

    /**
     * Format the exported activity label.
     *
     * @param int $courseid Course ID.
     * @param string $activity Base activity label.
     * @param int|null $cmid Course module ID.
     * @param string $mode Export mode.
     * @return string
     */
    private function format_activity(int $courseid, string $activity, ?int $cmid, string $mode): string {
        if (empty($cmid) || $mode === export_mode::STANDARD) {
            return $activity;
        }

        if ($mode === export_mode::DETAILED) {
            return $activity . ' [cmid:' . (int)$cmid . ']';
        }

        $cmname = $this->get_cm_name($courseid, (int)$cmid);
        if ($mode === export_mode::NAMED && $cmname !== null && $cmname !== '') {
            return $activity . ': ' . $cmname;
        }

        return $activity;
    }

    /**
     * Resolve the real course module name for an export.
     *
     * @param int $courseid Course ID.
     * @param int $cmid Course module ID.
     * @return string|null
     */
    private function get_cm_name(int $courseid, int $cmid): ?string {
        if (!isset($this->cmnamecache[$courseid])) {
            $this->cmnamecache[$courseid] = [];
        }

        if (array_key_exists($cmid, $this->cmnamecache[$courseid])) {
            return $this->cmnamecache[$courseid][$cmid];
        }

        $modinfo = get_fast_modinfo($courseid);
        if (empty($modinfo->cms[$cmid])) {
            $this->cmnamecache[$courseid][$cmid] = null;
            return null;
        }

        $cmname = format_string($modinfo->cms[$cmid]->name);
        $this->cmnamecache[$courseid][$cmid] = $cmname;

        return $cmname;
    }
}
