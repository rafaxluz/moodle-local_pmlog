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
 * Event cleaner and deduplicator.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class cleaner_dedup {

    /**
     * Generic sequential dedup: same caseid + same activity + same cmid within window.
     *
     * @param \stdClass[] $rows
     * @return \stdClass[]
     */
	/**
	 * Key-window dedup:
	 * For each caseid, skip events with same (activity + cmid) that occur within the window,
	 * even if they are not consecutive.
	 *
	 * @param \stdClass[] $rows
	 * @return \stdClass[]
	 */
	public function dedup_sequential(array $rows, int $windowseconds = 30): array {
		$out = [];

		$lastseen = [];

		foreach ($rows as $row) {
			$caseid = (string)$row->caseid;
			$activity = (string)$row->activity;
			$cmid = isset($row->cmid) ? (int)$row->cmid : 0;
			$t = (int)$row->timecreated;

			if (!isset($lastseen[$caseid])) {
				$lastseen[$caseid] = [];
			}

			$key = $activity . '|' . $cmid;

			if (isset($lastseen[$caseid][$key])) {
				$lastt = (int)$lastseen[$caseid][$key];
				if (($t - $lastt) <= $windowseconds) {
					continue;
				}
			}

			$lastseen[$caseid][$key] = $t;
			$out[] = $row;
		}

		return $out;
	}

    /**
     * Strict CMID Deduplication:
     * Removes sequential events that refer to the same CMID (Course Module ID) within a case,
     * ignoring the action (e.g. 'Quiz start' followed by 'Quiz view').
     * Keeps only the FIRST event of the sequence.
     *
     * @param \stdClass[] $rows
     * @return \stdClass[]
     */
    public function dedup_strict_cmid(array $rows): array {
        $out = [];
        $lastcmidv = []; // caseid => last_cmid

        foreach ($rows as $row) {
            $caseid = (string)$row->caseid;
            $cmid = isset($row->cmid) ? (int)$row->cmid : 0;

            // If it's not a module event, keep it and reset memory for this case
            if ($cmid <= 0) {
                $lastcmidv[$caseid] = 0;
                $out[] = $row;
                continue;
            }

            // If it matches the immediately preceding CMID for this case, skip it
            if (isset($lastcmidv[$caseid]) && $lastcmidv[$caseid] === $cmid) {
                continue;
            }

            // New CMID, keep it
            $lastcmidv[$caseid] = $cmid;
            $out[] = $row;
        }

        return $out;
    }


    /**
     * Navigation collapse:
     * - "Course view": keep at most 1 per session window per case
     * - "View course module": keep at most 1 per (cmid) per window per case
     *
     * @param \stdClass[] $rows
     * @return \stdClass[]
     */
    public function collapse_navigation(array $rows, int $courseviewwindow = 1800, int $moduleviewwindow = 600): array {
        $out = [];

        $lastcourseview = [];

        $lastmoduleview = [];

        foreach ($rows as $row) {
            $caseid = (string)$row->caseid;
            $activity = (string)$row->activity;
            $t = (int)$row->timecreated;
            $cmid = isset($row->cmid) ? (int)$row->cmid : 0;

            if ($activity === 'Course view') {
                $lastt = $lastcourseview[$caseid] ?? null;
                if ($lastt !== null && ($t - (int)$lastt) <= $courseviewwindow) {
                    continue;
                }
                $lastcourseview[$caseid] = $t;
                $out[] = $row;
                continue;
            }

            if ($activity === 'View course module') {
                if (!isset($lastmoduleview[$caseid])) {
                    $lastmoduleview[$caseid] = [];
                }

                $key = $cmid > 0 ? (string)$cmid : '_nocmid';
                $lastt = $lastmoduleview[$caseid][$key] ?? null;

                if ($lastt !== null && ($t - (int)$lastt) <= $moduleviewwindow) {
                    continue;
                }

                $lastmoduleview[$caseid][$key] = $t;
                $out[] = $row;
                continue;
            }

            $out[] = $row;
        }

        return $out;
    }
}
