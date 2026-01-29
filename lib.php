<?php
defined('MOODLE_INTERNAL') || die();

function local_pmlog_extend_navigation_course($navigation, $course, $context) {
    if (empty($course) || empty($context)) {
        return;
    }

    if (!has_capability('local/pmlog:manage', $context)) {
        return;
    }

    $url = new moodle_url('/local/pmlog/course.php', ['courseid' => $course->id]);

    $navigation->add(
        get_string('pluginname', 'local_pmlog'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'local_pmlog_course_link',
        new pix_icon('i/report', '')
    );
}
