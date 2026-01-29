<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class labelmap {
    /**
     * Return a human-friendly activity label for a raw log row.
     * If no mapping matches, fallback to "action:target".
     */
    public static function map(\stdClass $raw): string {
        $component = $raw->component ?? '';
        $action = $raw->action ?? '';
        $target = $raw->target ?? '';

        $key = "{$component}|{$action}|{$target}";

        $rules = [
            'core|viewed|course' => 'Course view',
            'core|viewed|course_module' => 'Open activity/resource',
            'core|viewed|content_page' => 'View page content',
            'core|viewed|grade_report' => 'View grades',
            'core|updated|course_module_completion' => 'Update completion',

            'core|graded|user' => 'Grade/feedback given',

            'mod_assign|viewed|course_module' => 'Assignment open',
            'mod_assign|viewed|submission_status' => 'Assignment status view',
            'mod_assign|viewed|submission_form' => 'Assignment submission form view',
            'mod_assign|viewed|submission_confirmation_form' => 'Assignment confirm submission view',
            'mod_assign|created|submission' => 'Assignment submission created',
            'mod_assign|submitted|submission' => 'Assignment submit',
            'mod_assign|uploaded|submission' => 'Assignment upload',

            'mod_quiz|viewed|course_module' => 'Quiz open',
            'mod_quiz|started|attempt' => 'Quiz attempt start',
            'mod_quiz|viewed|attempt' => 'Quiz attempt view',
            'mod_quiz|viewed|attempt_summary' => 'Quiz attempt summary view',
            'mod_quiz|submitted|attempt' => 'Quiz attempt submit',
            'mod_quiz|reviewed|attempt' => 'Quiz review',

            'mod_forum|viewed|course_module' => 'Forum open',
            'mod_forum|viewed|discussion' => 'Forum discussion view',
            'mod_forum|created|discussion' => 'Forum discussion create',
            'mod_forum|created|post' => 'Forum post create',

            'mod_page|viewed|course_module' => 'Page open',
            'mod_url|viewed|course_module' => 'URL open',
        ];

        if (isset($rules[$key])) {
            return $rules[$key];
        }

        $key2 = "{$component}|{$action}|*";
        if (isset($rules[$key2])) {
            return $rules[$key2];
        }

        $action = strtolower(trim((string)$action));
        $target = strtolower(trim((string)$target));

        $verbmap = [
            'viewed' => 'View',
            'started' => 'Start',
            'resumed' => 'Resume',
            'restarted' => 'Restart',
            'ended' => 'End',
            'attempted' => 'Attempt',
            'submitted' => 'Submit',
            'uploaded' => 'Upload',
            'created' => 'Create',
            'updated' => 'Update',
            'deleted' => 'Delete',
            'accepted' => 'Accept',
            'reviewed' => 'Review',
            'graded' => 'Grade',
            'completed' => 'Complete',
        ];

        $verb = $verbmap[$action] ?? ($action !== '' ? ucfirst($action) : 'Do');

        if ($target !== '') {
            $nice = str_replace('_', ' ', $target);
            return trim($verb . ' ' . $nice);
        }

        if (!empty($raw->eventname)) {
            return (string)$raw->eventname;
        }

        return 'Unknown';

    }
}
