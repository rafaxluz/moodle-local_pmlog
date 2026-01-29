<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_pmlog_index',
        get_string('pluginname', 'local_pmlog'),
        new moodle_url('/local/pmlog/index.php'),
        'local/pmlog:manage'
    ));
}
