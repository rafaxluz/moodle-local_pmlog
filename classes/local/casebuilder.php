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
 * Case ID builder.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

/**
 * Case builder class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class casebuilder {
    /** @var string One case per user within the course. */
    public const STRATEGY_USER_COURSE = 'user_course';

    /**
     * Build a case ID from event context.
     *
     * @param int $userid The user ID.
     * @param int $courseid The course ID.
     * @param int $timecreated Event timestamp.
     * @param string $strategy Case grouping strategy.
     * @return string The case ID.
     */
    public function build(
        int $userid,
        int $courseid,
        int $timecreated,
        string $strategy = self::STRATEGY_USER_COURSE
    ): string {
        return 'u' . $userid . '-c' . $courseid;
    }

    /**
     * Get the list of valid strategies.
     *
     * @return string[]
     */
    public static function valid_strategies(): array {
        return [self::STRATEGY_USER_COURSE];
    }
}
