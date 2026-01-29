<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class eventstore {
    public function clear_course(int $courseid): void {
        global $DB;
        $DB->delete_records('local_pmlog_events', ['courseid' => $courseid]);
    }

    public function insert_many(array $rows): void {
        global $DB;
        foreach ($rows as $row) {
            $DB->insert_record('local_pmlog_events', $row);
        }
    }
}
