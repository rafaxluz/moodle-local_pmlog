<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Basic transformer: converts raw Moodle log events
 * into human-friendly activity labels and extracts minimal metadata.
 */
class transformer_basic {

    /**
     * Convert a raw log event into an activity label.
     *
     * @param \stdClass $raw
     * @return string
     */
    public function to_activity(\stdClass $raw): string {
        return \local_pmlog\local\labelmap::map($raw);
    }

    /**
     * Extract minimal metadata from a raw log event.
     * Keep this intentionally small to avoid noise and privacy issues.
     *
     * @param \stdClass $raw
     * @return array
     */
    public function meta(\stdClass $raw): array {
        return [
            'objectid'     => $raw->objectid ?? null,
            'contextid'    => $raw->contextid ?? null,
            'contextlevel' => $raw->contextlevel ?? null,
            'edulevel'     => $raw->edulevel ?? null,
        ];
    }
}
