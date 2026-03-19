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
 * CSV download script.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('courseid', PARAM_INT);
$format = optional_param('format', 'csv', PARAM_ALPHA);

require_login($courseid);
$context = context_course::instance($courseid);
require_capability('local/pmlog:manage', $context);

$supported = [
    'csv' => [
        'filenameconfig' => 'last_csv_course_',
        'notfound' => 'error_csvnotfound',
        'missing' => 'error_csvmissing',
        'openread' => 'error_csvopenread',
        'contenttype' => 'text/csv; charset=utf-8',
    ],
    'csvdetailed' => [
        'filenameconfig' => 'last_csv_detailed_course_',
        'notfound' => 'error_csvdetailednotfound',
        'missing' => 'error_csvdetailedmissing',
        'openread' => 'error_csvopenread',
        'contenttype' => 'text/csv; charset=utf-8',
    ],
    'csvnamed' => [
        'filenameconfig' => 'last_csv_named_course_',
        'notfound' => 'error_csvnamednotfound',
        'missing' => 'error_csvnamedmissing',
        'openread' => 'error_csvopenread',
        'contenttype' => 'text/csv; charset=utf-8',
    ],
    'xes' => [
        'filenameconfig' => 'last_xes_course_',
        'notfound' => 'error_xesnotfound',
        'missing' => 'error_xesmissing',
        'openread' => 'error_xesopenread',
        'contenttype' => 'application/xml; charset=utf-8',
    ],
    'xesdetailed' => [
        'filenameconfig' => 'last_xes_detailed_course_',
        'notfound' => 'error_xesdetailednotfound',
        'missing' => 'error_xesdetailedmissing',
        'openread' => 'error_xesopenread',
        'contenttype' => 'application/xml; charset=utf-8',
    ],
    'xesnamed' => [
        'filenameconfig' => 'last_xes_named_course_',
        'notfound' => 'error_xesnamednotfound',
        'missing' => 'error_xesnamedmissing',
        'openread' => 'error_xesopenread',
        'contenttype' => 'application/xml; charset=utf-8',
    ],
];

if (!isset($supported[$format])) {
    throw new moodle_exception('invalidparameter');
}

$info = $supported[$format];
$filename = (string)get_config('local_pmlog', $info['filenameconfig'] . $courseid);
if ($filename === '') {
    throw new moodle_exception(get_string($info['notfound'], 'local_pmlog'));
}

$dir = $CFG->dataroot . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'local_pmlog';
$fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($fullpath)) {
    throw new moodle_exception(get_string($info['missing'], 'local_pmlog'));
}

if (headers_sent($file, $line)) {
    throw new moodle_exception(get_string('error_headerssent', 'local_pmlog', ['file' => $file, 'line' => $line]));
}

while (ob_get_level() > 0) {
    @ob_end_clean();
}

\core\session\manager::write_close();

header('X-Content-Type-Options: nosniff');
header('Content-Description: File Transfer');
header('Content-Type: ' . $info['contenttype']);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

$size = filesize($fullpath);
if ($size !== false) {
    header('Content-Length: ' . $size);
}

$fh = fopen($fullpath, 'rb');
if ($fh === false) {
    throw new moodle_exception(get_string($info['openread'], 'local_pmlog'));
}
fpassthru($fh);
fclose($fh);
exit;
