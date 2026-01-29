<?php
namespace local_pmlog\local;

defined('MOODLE_INTERNAL') || die();

class modulelabeler {
    /**
     * Given a cmid, return a specific "X open" label (Quiz open, Page open, ...)
     * or null if unknown.
     */
    public function label_from_cmid(?int $cmid): ?string {
        global $DB;

        if (empty($cmid)) {
            return null;
        }

        $sql = "
            SELECT m.name
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module
             WHERE cm.id = :cmid
        ";
        $modname = $DB->get_field_sql($sql, ['cmid' => $cmid], IGNORE_MISSING);
        if (empty($modname)) {
            return null;
        }

        $modname = strtolower((string)$modname);

        $map = [
            'quiz' => 'Quiz open',
            'page' => 'Page open',
            'url' => 'URL open',
            'assign' => 'Assignment open',
            'forum' => 'Forum open',
            'lesson' => 'Lesson open',
            'resource' => 'File/resource open',
            'book' => 'Book open',
            'folder' => 'Folder open',
            'label' => 'Label open',
            'feedback' => 'Feedback activity open',
            'choice' => 'Choice open',
            'survey' => 'Survey open',
            'scorm' => 'SCORM open',
            'h5pactivity' => 'H5P open',
        ];

        return $map[$modname] ?? (ucfirst($modname) . ' open');
    }
}
