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
 * Strings for component 'local_pmlog'.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'PM Log Builder';
$string['pmlog:manage'] = 'Manage PM Log Builder';
$string['settings'] = 'PM Log Builder settings';
$string['buildnormalizedlog'] = 'Build normalized log';
$string['courseid'] = 'Course ID';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['onlystudents'] = 'Only students';
$string['dedup'] = 'Deduplicate sequential events';
$string['dedupwindow'] = 'Dedup window (seconds)';
$string['run'] = 'Run';
$string['note'] = 'Note';
$string['dedupskipped'] = 'Dedup skipped';
$string['lastcsvexport'] = 'Last CSV export';
$string['downloadcsv'] = 'Download CSV';
$string['lastrun'] = 'Last run';
$string['executionqueued'] = 'Execution queued. It will run in the background (cron). Refresh this page in a moment to see updated timelines.';
$string['executionqueuedshort'] = 'Execution queued. The log will be built in the background (cron). Refresh this page in a moment.';
$string['buildrefreshlog'] = 'Build normalized log for this course';
$string['exportcsv'] = 'Export CSV';
$string['runpipeline'] = 'Run';
$string['studenttimelines'] = 'Student timelines';

$string['dedup_strict_cmid'] = 'Remove sequential CMID duplicates (Strict)';
$string['sort_events_desc'] = 'Events (High-Low)';
$string['sort_events_asc'] = 'Events (Low-High)';
$string['sort_name_asc'] = 'Student Name (A-Z)';
$string['sort_name_desc'] = 'Student Name (Z-A)';

// Activity Types
$string['activity_url'] = 'URL';
$string['activity_page'] = 'Page';
$string['activity_forum'] = 'Forum';
$string['activity_assign'] = 'Assignment';
$string['activity_quiz'] = 'Quiz';
$string['activity_resource'] = 'File';
$string['activity_folder'] = 'Folder';
$string['activity_wiki'] = 'Wiki';
$string['activity_chat'] = 'Chat';
$string['activity_glossary'] = 'Glossary';
$string['activity_lesson'] = 'Lesson';
$string['activity_scorm'] = 'SCORM';
$string['activity_workshop'] = 'Workshop';
$string['activity_feedback'] = 'Feedback';
$string['activity_choice'] = 'Choice';
$string['activity_survey'] = 'Survey';
$string['activity_lti'] = 'External Tool';
$string['activity_h5p'] = 'H5P';

// Verbs
$string['verb_view'] = 'View';
$string['verb_submission'] = 'Submission';
$string['verb_grade'] = 'Grade';
$string['verb_attempt'] = 'Attempt';
$string['verb_update'] = 'Update';
$string['verb_create'] = 'Create';
$string['verb_delete'] = 'Delete';
$string['verb_complete'] = 'Complete';
$string['noeventsfound'] = 'No normalized events found yet for this course. Run the pipeline above first.';
$string['thstudent'] = 'Student';
$string['thevents'] = 'Events';
$string['thtimeline'] = 'Timeline';
$string['opentimeline'] = 'Open timeline';
$string['user'] = 'User';
$string['courseviewwindow'] = 'Course view window (seconds)';
$string['moduleviewwindow'] = 'Module view window (seconds)';
$string['privacy:metadata:local_pmlog_events'] = 'Stores extracted and normalized event logs for process mining analysis.';
$string['privacy:metadata:local_pmlog_events:userid'] = 'The ID of the user who performed the action.';
$string['privacy:metadata:local_pmlog_events:courseid'] = 'The ID of the course where the action occurred.';
$string['privacy:metadata:local_pmlog_events:eventname'] = 'The name of the event.';
$string['privacy:metadata:local_pmlog_events:timecreated'] = 'The time when the event occurred.';

// Index Page
$string['raw'] = 'Raw';
$string['stored'] = 'Stored';
$string['skipped'] = 'Skipped';

// Activity Groups
$string['group_quiz'] = 'Quiz';
$string['group_assignment'] = 'Assignment';
$string['group_forum'] = 'Forum';
$string['group_lesson'] = 'Lesson';
$string['group_content'] = 'Content';
$string['group_progress'] = 'Progress';
$string['group_other'] = 'Other';

// Timeline
$string['noeventsstudent'] = 'No events found for this student in this course.';

// Module Labels
$string['activity_quiz_open'] = 'Quiz open';
$string['activity_page_open'] = 'Page open';
$string['activity_url_open'] = 'URL open';
$string['activity_assign_open'] = 'Assignment open';
$string['activity_forum_open'] = 'Forum open';
$string['activity_lesson_open'] = 'Lesson open';
$string['activity_resource_open'] = 'File/resource open';
$string['activity_book_open'] = 'Book open';
$string['activity_folder_open'] = 'Folder open';
$string['activity_label_open'] = 'Label open';
$string['activity_feedback_open'] = 'Feedback activity open';
$string['activity_choice_open'] = 'Choice open';
$string['activity_survey_open'] = 'Survey open';
$string['activity_scorm_open'] = 'SCORM open';
$string['activity_h5pactivity_open'] = 'H5P open';
$string['activity_generic_open'] = '{$a} open';

// Label Map (Specific events)
$string['event_course_view'] = 'Course view';
$string['event_activity_resource_open'] = 'Open activity/resource';
$string['event_page_view'] = 'View page content';
$string['event_grade_view'] = 'View grades';
$string['event_completion_update'] = 'Update completion';
$string['event_grade_given'] = 'Grade/feedback given';
$string['event_assign_status_view'] = 'Assignment status view';
$string['event_assign_submission_form_view'] = 'Assignment submission form view';
$string['event_assign_confirm_form_view'] = 'Assignment confirm submission view';
$string['event_assign_submission_create'] = 'Assignment submission created';
$string['event_assign_submit'] = 'Assignment submit';
$string['event_assign_upload'] = 'Assignment upload';
$string['event_quiz_attempt_start'] = 'Quiz attempt start';
$string['event_quiz_attempt_view'] = 'Quiz attempt view';
$string['event_quiz_attempt_summary_view'] = 'Quiz attempt summary view';
$string['event_quiz_attempt_submit'] = 'Quiz attempt submit';
$string['event_quiz_review'] = 'Quiz review';
$string['event_forum_discussion_view'] = 'Forum discussion view';
$string['event_forum_discussion_create'] = 'Forum discussion create';
$string['event_forum_post_create'] = 'Forum post create';
$string['unknown'] = 'Unknown';

// Verbs for dynamic mapping
$string['verb_view'] = 'View';
$string['verb_start'] = 'Start';
$string['verb_resume'] = 'Resume';
$string['verb_restart'] = 'Restart';
$string['verb_end'] = 'End';
$string['verb_attempt'] = 'Attempt';
$string['verb_submit'] = 'Submit';
$string['verb_upload'] = 'Upload';
$string['verb_create'] = 'Create';
$string['verb_update'] = 'Update';
$string['verb_delete'] = 'Delete';
$string['verb_accept'] = 'Accept';
$string['verb_review'] = 'Review';
$string['verb_grade'] = 'Grade';
$string['verb_complete'] = 'Complete';
$string['verb_do'] = 'Do';

// Download Errors
$string['error_csvnotfound'] = 'CSV not found. Run the pipeline with CSV export enabled first.';
$string['error_csvmissing'] = 'CSV file missing on disk. Re-run the export.';
$string['error_headerssent'] = 'Headers already sent in {$a->file} on line {$a->line}. Cannot download CSV safely.';
$string['error_csvopenread'] = 'Could not open CSV for reading.';
