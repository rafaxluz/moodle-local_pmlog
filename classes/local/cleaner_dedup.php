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

/**
 * Cleaner deduplicator class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleaner_dedup {
    /**
     * Create the mutable state bag used by streaming deduplication.
     *
     * @return array
     */
    public function create_state(): array {
        return [
            'lastseen' => [],
            'lastcmidv' => [],
            'lastcourseview' => [],
            'lastmoduleview' => [],
        ];
    }

    /**
     * Decide whether a normalized row should be kept in streaming mode.
     *
     * @param \stdClass $row Normalized row.
     * @param array $options Deduplication options.
     * @param array $state Mutable state bag.
     * @return bool
     */
    public function keep_row(\stdClass $row, array $options, array &$state): bool {
        $dedupwindow = (int)($options['dedupwindow'] ?? 30);
        $dedupstrictcmid = !empty($options['dedup_strict_cmid']);
        $courseviewwindow = (int)($options['courseviewwindow'] ?? 1800);
        $moduleviewwindow = (int)($options['moduleviewwindow'] ?? 600);

        if (!$this->passes_sequential_dedup($row, $dedupwindow, $state['lastseen'])) {
            return false;
        }

        if ($dedupstrictcmid && !$this->passes_strict_cmid_dedup($row, $state['lastcmidv'])) {
            return false;
        }

        if (!$this->passes_course_view_collapse($row, $courseviewwindow, $state['lastcourseview'])) {
            return false;
        }

        if (!$this->passes_module_view_collapse($row, $moduleviewwindow, $state['lastmoduleview'])) {
            return false;
        }

        return true;
    }

    /**
     * Generic sequential dedup: same caseid + same activity + same cmid within window.
     *
     * @param \stdClass[] $rows
     * @param int $windowseconds
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
     * Check sequential deduplication for a single row.
     *
     * @param \stdClass $row Event row.
     * @param int $windowseconds Deduplication window.
     * @param array $lastseen Mutable last-seen bag.
     * @return bool
     */
    private function passes_sequential_dedup(\stdClass $row, int $windowseconds, array &$lastseen): bool {
        $caseid = (string)$row->caseid;
        $activity = (string)$row->activity;
        $cmid = isset($row->cmid) ? (int)$row->cmid : 0;
        $t = (int)$row->timecreated;

        if (!isset($lastseen[$caseid])) {
            $lastseen[$caseid] = [];
        }

        $key = $activity . '|' . $cmid;
        $lastt = $lastseen[$caseid][$key] ?? null;
        if ($lastt !== null && ($t - (int)$lastt) <= $windowseconds) {
            return false;
        }

        $lastseen[$caseid][$key] = $t;
        return true;
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
        $lastcmidv = [];

        foreach ($rows as $row) {
            $caseid = (string)$row->caseid;
            $cmid = isset($row->cmid) ? (int)$row->cmid : 0;

            // If it's not a module event, keep it and reset memory for this case.
            if ($cmid <= 0) {
                $lastcmidv[$caseid] = 0;
                $out[] = $row;
                continue;
            }

            // If it matches the immediately preceding CMID for this case, skip it.
            if (isset($lastcmidv[$caseid]) && $lastcmidv[$caseid] === $cmid) {
                continue;
            }

            // New CMID, keep it.
            $lastcmidv[$caseid] = $cmid;
            $out[] = $row;
        }

        return $out;
    }

    /**
     * Check strict CMID deduplication for a single row.
     *
     * @param \stdClass $row Event row.
     * @param array $lastcmidv Mutable last-cmid bag.
     * @return bool
     */
    private function passes_strict_cmid_dedup(\stdClass $row, array &$lastcmidv): bool {
        $caseid = (string)$row->caseid;
        $cmid = isset($row->cmid) ? (int)$row->cmid : 0;

        if ($cmid <= 0) {
            $lastcmidv[$caseid] = 0;
            return true;
        }

        if (isset($lastcmidv[$caseid]) && $lastcmidv[$caseid] === $cmid) {
            return false;
        }

        $lastcmidv[$caseid] = $cmid;
        return true;
    }


    /**
     * Navigation collapse:
     * - "Course view": keep at most 1 per session window per case.
     * - "View course module": keep at most 1 per (cmid) per window per case.
     *
     * @param \stdClass[] $rows
     * @param int $courseviewwindow Seconds for course view window.
     * @param int $moduleviewwindow Seconds for module view window.
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

            if ($this->should_collapse_course_view($row, $caseid, $t, $courseviewwindow, $lastcourseview)) {
                continue;
            }

            if ($this->should_collapse_module_view($row, $caseid, $t, $cmid, $moduleviewwindow, $lastmoduleview)) {
                continue;
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * Check course-view collapse for a single row.
     *
     * @param \stdClass $row Event row.
     * @param int $window Window size in seconds.
     * @param array $lastcourseview Mutable last course-view bag.
     * @return bool
     */
    private function passes_course_view_collapse(\stdClass $row, int $window, array &$lastcourseview): bool {
        if (!$this->is_course_view_event($row)) {
            return true;
        }

        $caseid = (string)$row->caseid;
        $t = (int)$row->timecreated;
        $lastt = $lastcourseview[$caseid] ?? null;
        if ($lastt !== null && ($t - (int)$lastt) <= $window) {
            return false;
        }

        $lastcourseview[$caseid] = $t;
        return true;
    }

    /**
     * Check module-view collapse for a single row.
     *
     * @param \stdClass $row Event row.
     * @param int $window Window size in seconds.
     * @param array $lastmoduleview Mutable last module-view bag.
     * @return bool
     */
    private function passes_module_view_collapse(\stdClass $row, int $window, array &$lastmoduleview): bool {
        if (!$this->is_module_view_event($row)) {
            return true;
        }

        $caseid = (string)$row->caseid;
        $t = (int)$row->timecreated;
        $cmid = isset($row->cmid) ? (int)$row->cmid : 0;

        if (!isset($lastmoduleview[$caseid])) {
            $lastmoduleview[$caseid] = [];
        }

        $key = $cmid > 0 ? (string)$cmid : '_nocmid';
        $lastt = $lastmoduleview[$caseid][$key] ?? null;
        if ($lastt !== null && ($t - (int)$lastt) <= $window) {
            return false;
        }

        $lastmoduleview[$caseid][$key] = $t;
        return true;
    }

    /**
     * Check if a course view event should be collapsed.
     *
     * @param \stdClass $row Event row.
     * @param string $caseid Case ID.
     * @param int $t Timestamp.
     * @param int $window Window size in seconds.
     * @param array $lastcourseview Reference to last course view array.
     * @return bool True if should be collapsed.
     */
    private function should_collapse_course_view(
        \stdClass $row,
        string $caseid,
        int $t,
        int $window,
        array &$lastcourseview
    ): bool {
        if (!$this->is_course_view_event($row)) {
            return false;
        }

        $lastt = $lastcourseview[$caseid] ?? null;
        if ($lastt !== null && ($t - (int)$lastt) <= $window) {
            return true;
        }

        $lastcourseview[$caseid] = $t;
        return false;
    }

    /**
     * Check if a module view event should be collapsed.
     *
     * @param \stdClass $row Event row.
     * @param string $caseid Case ID.
     * @param int $t Timestamp.
     * @param int $cmid Course module ID.
     * @param int $window Window size in seconds.
     * @param array $lastmoduleview Reference to last module view array.
     * @return bool True if should be collapsed.
     */
    private function should_collapse_module_view(
        \stdClass $row,
        string $caseid,
        int $t,
        int $cmid,
        int $window,
        array &$lastmoduleview
    ): bool {
        if (!$this->is_module_view_event($row)) {
            return false;
        }

        if (!isset($lastmoduleview[$caseid])) {
            $lastmoduleview[$caseid] = [];
        }

        $key = $cmid > 0 ? (string)$cmid : '_nocmid';
        $lastt = $lastmoduleview[$caseid][$key] ?? null;

        if ($lastt !== null && ($t - (int)$lastt) <= $window) {
            return true;
        }

        $lastmoduleview[$caseid][$key] = $t;
        return false;
    }

    /**
     * Check whether the row is a raw course view event.
     *
     * @param \stdClass $row Event row.
     * @return bool
     */
    private function is_course_view_event(\stdClass $row): bool {
        return (($row->action ?? '') === 'viewed') && (($row->target ?? '') === 'course');
    }

    /**
     * Check whether the row is a raw module view event.
     *
     * @param \stdClass $row Event row.
     * @return bool
     */
    private function is_module_view_event(\stdClass $row): bool {
        return (($row->action ?? '') === 'viewed') && (($row->target ?? '') === 'course_module');
    }
}
