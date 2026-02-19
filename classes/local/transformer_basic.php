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
 * Basic transformer.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
