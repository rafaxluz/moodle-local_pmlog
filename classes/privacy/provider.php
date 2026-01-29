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

namespace local_pmlog\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\transform;

/**
 * Privacy provider for local_pmlog.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns metadata about the data handled by this plugin.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_pmlog_events',
            [
                'userid' => 'privacy:metadata:local_pmlog_events:userid',
                'courseid' => 'privacy:metadata:local_pmlog_events:courseid',
                'eventname' => 'privacy:metadata:local_pmlog_events:eventname',
                'timecreated' => 'privacy:metadata:local_pmlog_events:timecreated',
            ],
            'privacy:metadata:local_pmlog_events'
        );
        return $collection;
    }

    /**
     * Get the list of contexts where a user has data.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        // The events are associated with a course, so we link to the course context.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {local_pmlog_events} e ON e.courseid = c.instanceid
                 WHERE c.contextlevel = :contextlevel
                   AND e.userid = :userid";
        
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_COURSE,
            'userid' => $userid
        ]);
        
        return $contextlist;
    }

    /**
     * Export all user data for the specified approved contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();
        
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $currentdata = $DB->get_records('local_pmlog_events', [
                'courseid' => $context->instanceid,
                'userid' => $user->id
            ]);

            if (empty($currentdata)) {
                continue;
            }

            $data = [];
            foreach ($currentdata as $row) {
                $data[] = (object)[
                    'eventname' => $row->eventname,
                    'action' => $row->action,
                    'target' => $row->target,
                    'timecreated' => transform::datetime($row->timecreated),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_pmlog'), 'Events'],
                (object)['events' => $data]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records('local_pmlog_events', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_COURSE) {
                continue;
            }

            $DB->delete_records('local_pmlog_events', [
                'courseid' => $context->instanceid,
                'userid' => $userid
            ]);
        }
    }
}
