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
 * Event store interface.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Event store class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventstore {
    /**
     * Clear all events for a specific course.
     *
     * @param int $courseid The course ID.
     */
    public function clear_course(int $courseid) {
        global $DB;
        $DB->delete_records('local_pmlog_events', ['courseid' => $courseid]);
    }

    /**
     * Insert multiple event records.
     *
     * @param array $rows Array of event objects.
     */
    public function insert_many(array $rows) {
        global $DB;
        $DB->insert_records('local_pmlog_events', $rows);
    }
}
