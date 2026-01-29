<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);
$context = context_course::instance($courseid);
require_capability('local/pmlog:manage', $context);

$filename = (string)get_config('local_pmlog', 'last_csv_course_' . $courseid);
if ($filename === '') {
    throw new moodle_exception('CSV not found. Run the pipeline with CSV export enabled first.');
}

$dir = $CFG->dataroot . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'local_pmlog';
$fullpath = $dir . DIRECTORY_SEPARATOR . $filename;

if (!file_exists($fullpath)) {
    throw new moodle_exception('CSV file missing on disk. Re-run the export.');
}

if (headers_sent($file, $line)) {
    throw new moodle_exception("Headers already sent in {$file} on line {$line}. Cannot download CSV safely.");
}

while (ob_get_level() > 0) {
    @ob_end_clean();
}

\core\session\manager::write_close();

header('X-Content-Type-Options: nosniff');
header('Content-Description: File Transfer');
header('Content-Type: text/csv; charset=utf-8');
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
    throw new moodle_exception('Could not open CSV for reading.');
}
fpassthru($fh);
fclose($fh);
exit;
