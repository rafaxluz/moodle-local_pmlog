<?php
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
