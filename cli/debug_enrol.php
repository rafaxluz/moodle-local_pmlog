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
 * CLI script to debug enrollment.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'courseid' => null,
    'help' => false,
], ['h' => 'help']);

if ($options['help'] || empty($options['courseid'])) {
    echo "Usage:\n";
    echo "  php public/local/pmlog/cli/debug_enrol.php --courseid=INT\n";
    exit(0);
}

$courseid = (int)$options['courseid'];

$context = \context_course::instance($courseid, IGNORE_MISSING);
if (!$context) {
    echo "Course context NOT FOUND for courseid={$courseid}\n";
    exit(0);
}

echo "Course context id: {$context->id}\n";

$enrolled = get_enrolled_users($context, '', 0, 'u.id');
echo "Enrolled users count: " . count($enrolled) . "\n";

$ids = [];
foreach ($enrolled as $u) {
    $ids[] = (int)$u->id;
    if (count($ids) >= 10) {
        break;
    }
}
echo "Sample enrolled userids: " . implode(', ', $ids) . "\n";

foreach ($ids as $uid) {
    $canview = has_capability('moodle/course:view', $context, $uid) ? 'yes' : 'no';
    $canupdate = has_capability('moodle/course:update', $context, $uid) ? 'yes' : 'no';
    echo "userid={$uid} view={$canview} update={$canupdate}\n";
}