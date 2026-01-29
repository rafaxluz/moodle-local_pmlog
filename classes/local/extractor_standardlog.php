<?php
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
