<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class casebuilder {
    public function build(int $userid, int $courseid): string {
        return "u{$userid}-c{$courseid}";
    }
}
