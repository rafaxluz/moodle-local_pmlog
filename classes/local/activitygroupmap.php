<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class activitygroupmap {

    /**
     * Map a detailed activity label to a coarse activity group.
     */
    public static function group(string $activity): string {
        $a = strtolower($activity);

        if (str_contains($a, 'quiz')) {
            return 'Quiz';
        }

        if (str_contains($a, 'assignment')) {
            return 'Assignment';
        }

        if (str_contains($a, 'forum')) {
            return 'Forum';
        }

        if (str_contains($a, 'lesson')) {
            return 'Lesson';
        }

        if (
            str_contains($a, 'page') ||
            str_contains($a, 'url') ||
            str_contains($a, 'content')
        ) {
            return 'Content';
        }

        if (str_contains($a, 'completion')) {
            return 'Progress';
        }

        return 'Other';
    }
}
