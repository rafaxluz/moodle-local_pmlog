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
 * CLI script to build logs.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');

require_once($CFG->libdir . '/clilib.php');

use local_pmlog\local\pipeline_service;
use local_pmlog\local\exporter_csv;

list($options, $unrecognized) = cli_get_params([
    'courseid' => null,
    'timestart' => 0,
    'timeend' => 0,
    'clear' => 1,
    'studentonly' => 1,
    'dedup' => 1,
    'dedupwindow' => 30,
    'exportcsv' => '',
    'help' => false,
], [
    'h' => 'help',
]);


if ($options['help'] || empty($options['courseid'])) {
    $help = "Build normalized event log for a course

Options:
  --courseid=INT         Required.
  --timestart=INT        Unix timestamp (optional).
  --timeend=INT          Unix timestamp (optional).
  --clear=0|1            Clear previous built log for course (default 1).
  --studentonly=0|1      Include only students (role shortname/archetype) (default 1).
  --dedup=0|1            Deduplicate sequential repeated events (default 1).
  --dedupwindow=INT      Dedup time window in seconds (default 30).
  --exportcsv=PATH       Export normalized CSV to PATH (optional).
  -h, --help             Print out this help.

Example:
  php public/local/pmlog/cli/build_log.php --courseid=11 --clear=1 --studentonly=1 --dedup=1 --dedupwindow=30 --exportcsv=/tmp/course11.csv
";
    echo $help;
    exit(0);
}

$courseid = (int)$options['courseid'];

$svc = new pipeline_service();
$result = $svc->run($courseid, [
    'timestart' => (int)$options['timestart'],
    'timeend' => (int)$options['timeend'],
    'clear' => (bool)$options['clear'],
    'studentonly' => (bool)$options['studentonly'],
    'dedup' => (bool)$options['dedup'],
    'dedupwindow' => (int)$options['dedupwindow'],
]);


echo "Done.\n";
echo "Raw: {$result['raw_count']} | Stored: {$result['stored_count']} | Skipped: {$result['skipped_count']}\n";

if (!empty($options['exportcsv'])) {
    $path = $options['exportcsv'];
    $exp = new exporter_csv();
    $exp->export_course($courseid, $path);
    echo "CSV exported to: {$path}\n";
}
