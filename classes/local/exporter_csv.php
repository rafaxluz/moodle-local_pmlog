<?php
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
