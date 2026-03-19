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



$string['activity_assign'] = 'Assignment';
$string['activity_assign_open'] = 'Assignment open';
$string['activity_book_open'] = 'Book open';
$string['activity_chat'] = 'Chat';
$string['activity_choice'] = 'Choice';
$string['activity_choice_open'] = 'Choice open';
$string['activity_feedback'] = 'Feedback';
$string['activity_feedback_open'] = 'Feedback activity open';
$string['activity_folder'] = 'Folder';
$string['activity_folder_open'] = 'Folder open';
$string['activity_forum'] = 'Forum';
$string['activity_forum_open'] = 'Forum open';
$string['activity_generic_open'] = '{$a} open';
$string['activity_glossary'] = 'Glossary';
$string['activity_h5p'] = 'H5P';
$string['activity_h5pactivity_open'] = 'H5P open';
$string['activity_label_open'] = 'Label open';
$string['activity_lesson'] = 'Lesson';
$string['activity_lesson_open'] = 'Lesson open';
$string['activity_lti'] = 'External Tool';
$string['activity_page'] = 'Page';
$string['activity_page_open'] = 'Page open';
$string['activity_quiz'] = 'Quiz';
$string['activity_quiz_open'] = 'Quiz open';
$string['activity_resource'] = 'File';
$string['activity_resource_open'] = 'File/resource open';
$string['activity_scorm'] = 'SCORM';
$string['activity_scorm_open'] = 'SCORM open';
$string['activity_survey'] = 'Survey';
$string['activity_survey_open'] = 'Survey open';
$string['activity_url'] = 'URL';
$string['activity_url_open'] = 'URL open';
$string['activity_wiki'] = 'Wiki';
$string['activity_workshop'] = 'Workshop';
$string['buildnormalizedlog'] = 'Build normalized log';
$string['buildrefreshlog'] = 'Build normalized log for this course';
$string['caseidstrategy'] = 'Case ID strategy';
$string['caseidstrategy_help'] = 'Defines how events are grouped into cases for process mining. Use a course-wide case to follow a learner across the whole course, or split by day for shorter traces.';
$string['caseidstrategy_user_course'] = 'Per user and course';
$string['caseidstrategy_user_course_day'] = 'Per user, course, and day';
$string['caseidstrategy_user_day'] = 'Per user and day';
$string['availableexports'] = 'Available exports';
$string['courseid'] = 'Course ID';
$string['courseviewwindow'] = 'Course view window (seconds)';
$string['dedup'] = 'Deduplicate sequential events';
$string['dedup_strict_cmid'] = 'Remove consecutive duplicates from the same activity';
$string['dedupskipped'] = 'Dedup skipped';
$string['dedupwindow'] = 'Dedup window (seconds)';
$string['detailed'] = 'Detailed';
$string['downloadcsv'] = 'Download CSV';
$string['downloadcsvdetailed'] = 'Download detailed CSV';
$string['downloadcsvnamed'] = 'Download named CSV';
$string['downloadxes'] = 'Download XES';
$string['downloadxesdetailed'] = 'Download detailed XES';
$string['downloadxesnamed'] = 'Download named XES';
$string['enddate'] = 'End Date';
$string['error_csvdetailedmissing'] = 'Detailed CSV file missing on disk. Re-run the export.';
$string['error_csvdetailednotfound'] = 'Detailed CSV not found. Run the pipeline with detailed CSV export enabled first.';
$string['error_csvmissing'] = 'CSV file missing on disk. Re-run the export.';
$string['error_csvnamedmissing'] = 'Named CSV file missing on disk. Re-run the export.';
$string['error_csvnamednotfound'] = 'Named CSV not found. Run the pipeline with named CSV export enabled first.';
$string['error_csvnotfound'] = 'CSV not found. Run the pipeline with CSV export enabled first.';
$string['error_csvopenread'] = 'Could not open CSV for reading.';
$string['error_headerssent'] = 'Headers already sent in {$a->file} on line {$a->line}. Cannot download CSV safely.';
$string['error_xesdetailedmissing'] = 'Detailed XES file missing on disk. Re-run the export.';
$string['error_xesdetailednotfound'] = 'Detailed XES not found. Run the pipeline with detailed XES export enabled first.';
$string['error_xesmissing'] = 'XES file missing on disk. Re-run the export.';
$string['error_xesnamedmissing'] = 'Named XES file missing on disk. Re-run the export.';
$string['error_xesnamednotfound'] = 'Named XES not found. Run the pipeline with named XES export enabled first.';
$string['error_xesnotfound'] = 'XES not found. Run the pipeline with XES export enabled first.';
$string['error_xesopenread'] = 'Could not open XES for reading.';
$string['event_activity_resource_open'] = 'Open activity/resource';
$string['event_assign_confirm_form_view'] = 'Assignment confirm submission view';
$string['event_assign_status_view'] = 'Assignment status view';
$string['event_assign_submission_create'] = 'Assignment submission created';
$string['event_assign_submission_form_view'] = 'Assignment submission form view';
$string['event_assign_submit'] = 'Assignment submit';
$string['event_assign_upload'] = 'Assignment upload';
$string['event_completion_update'] = 'Update completion';
$string['event_course_view'] = 'Course view';
$string['event_forum_discussion_create'] = 'Forum discussion create';
$string['event_forum_discussion_view'] = 'Forum discussion view';
$string['event_forum_post_create'] = 'Forum post create';
$string['event_grade_given'] = 'Grade/feedback given';
$string['event_grade_view'] = 'View grades';
$string['event_page_view'] = 'View page content';
$string['event_quiz_attempt_start'] = 'Quiz attempt start';
$string['event_quiz_attempt_submit'] = 'Quiz attempt submit';
$string['event_quiz_attempt_summary_view'] = 'Quiz attempt summary view';
$string['event_quiz_attempt_view'] = 'Quiz attempt view';
$string['event_quiz_review'] = 'Quiz review';
$string['executionstatus'] = 'Execution status';
$string['executionqueued'] = 'Execution queued. It will run in the background (cron). Refresh this page in a moment to see updated timelines.';
$string['executionqueuedshort'] = 'Execution queued. The log will be built in the background (cron). Refresh this page in a moment.';
$string['executionrunning'] = 'A build for this course is currently running in the background. The last completed result will remain visible until the new run finishes.';
$string['exportcsv'] = 'Export CSV';
$string['exportxes'] = 'Export XES';
$string['group_assignment'] = 'Assignment';
$string['group_content'] = 'Content';
$string['group_forum'] = 'Forum';
$string['group_lesson'] = 'Lesson';
$string['group_other'] = 'Other';
$string['group_progress'] = 'Progress';
$string['group_quiz'] = 'Quiz';
$string['lastcsvexport'] = 'Last CSV export';
$string['lastcsvexportdetailed'] = 'Last detailed CSV export';
$string['lastcsvexportnamed'] = 'Last named CSV export';
$string['lastrun'] = 'Last run';
$string['lastrunsettings'] = 'Last run settings';
$string['lastxesexport'] = 'Last XES export';
$string['lastxesexportdetailed'] = 'Last detailed XES export';
$string['lastxesexportnamed'] = 'Last named XES export';
$string['moduleviewwindow'] = 'Module view window (seconds)';
$string['named'] = 'Named';
$string['noeventsfound'] = 'No normalized events found yet for this course. Run the pipeline above first.';
$string['noeventsstudent'] = 'No events found for this student in this course.';
$string['note'] = 'Note';
$string['notset'] = 'Not set';
$string['onlystudents'] = 'Only students';
$string['opentimeline'] = 'Open timeline';
$string['pluginadministration'] = 'Configure scheduled builds for Process Mining Log Builder, including courses, filters, deduplication, and export formats.';
$string['pluginname'] = 'Process Mining Log Builder';
$string['pmlog:manage'] = 'Manage Process Mining Log Builder';
$string['privacy:metadata:local_pmlog_events'] = 'Stores extracted and normalized event logs for process mining analysis.';
$string['privacy:metadata:local_pmlog_events:courseid'] = 'The ID of the course where the action occurred.';
$string['privacy:metadata:local_pmlog_events:eventname'] = 'The name of the event.';
$string['privacy:metadata:local_pmlog_events:timecreated'] = 'The time when the event occurred.';
$string['privacy:metadata:local_pmlog_events:userid'] = 'The ID of the user who performed the action.';
$string['raw'] = 'Raw';
$string['run'] = 'Run';
$string['runpipeline'] = 'Run';
$string['schedulecaseidstrategy_desc'] = 'Case ID strategy used by scheduled builds.';
$string['schedulecourseids'] = 'Scheduled course IDs';
$string['schedulecourseids_desc'] = 'Comma-separated list of course IDs to process automatically.';
$string['schedulecourses'] = 'Scheduled courses';
$string['schedulecourses_help'] = 'Select one or more courses to be processed automatically by the scheduled task.';
$string['schedulecourseviewwindow_desc'] = 'Course view collapse window in seconds for scheduled builds.';
$string['schedulededup_desc'] = 'Apply sequential deduplication during scheduled builds.';
$string['schedulededupstrictcmid_desc'] = 'Remove sequential events that refer to the same course module during scheduled builds.';
$string['schedulededupwindow_desc'] = 'Deduplication window in seconds for scheduled builds.';
$string['scheduleenabled'] = 'Enable scheduled builds';
$string['scheduleenabled_desc'] = 'If enabled, the scheduled task will queue Process Mining log builds for the configured courses whenever it runs.';
$string['scheduleexportcsv_desc'] = 'Generate CSV files for scheduled builds.';
$string['scheduleexportcsvdetailed_desc'] = 'Generate detailed anonymized CSV files for scheduled builds.';
$string['scheduleexportcsvnamed_desc'] = 'Generate named CSV files for scheduled builds.';
$string['scheduleexportxes_desc'] = 'Generate XES files for scheduled builds.';
$string['scheduleexportxesdetailed_desc'] = 'Generate detailed anonymized XES files for scheduled builds.';
$string['scheduleexportxesnamed_desc'] = 'Generate named XES files for scheduled builds.';
$string['schedulemoduleviewwindow_desc'] = 'Module view collapse window in seconds for scheduled builds.';
$string['schedulestudentonly_desc'] = 'Limit scheduled builds to student actions only.';
$string['skipped'] = 'Skipped';
$string['sort_events_asc'] = 'Events (Low-High)';
$string['sort_events_desc'] = 'Events (High-Low)';
$string['sort_name_asc'] = 'Student Name (A-Z)';
$string['sort_name_desc'] = 'Student Name (Z-A)';
$string['standard'] = 'Standard';
$string['startdate'] = 'Start Date';
$string['stored'] = 'Stored';
$string['studenttimeline'] = 'Student timeline';
$string['studenttimelines'] = 'Student timelines';
$string['task_build_log_scheduled'] = 'Queue scheduled Process Mining log builds';
$string['thevents'] = 'Events';
$string['thstudent'] = 'Student';
$string['thtimeline'] = 'Timeline';
$string['unknown'] = 'Unknown';
$string['user'] = 'User';
$string['verb_accept'] = 'Accept';
$string['verb_attempt'] = 'Attempt';
$string['verb_complete'] = 'Complete';
$string['verb_create'] = 'Create';
$string['verb_delete'] = 'Delete';
$string['verb_do'] = 'Do';
$string['verb_end'] = 'End';
$string['verb_grade'] = 'Grade';
$string['verb_restart'] = 'Restart';
$string['verb_resume'] = 'Resume';
$string['verb_review'] = 'Review';
$string['verb_start'] = 'Start';
$string['verb_submission'] = 'Submission';
$string['verb_submit'] = 'Submit';
$string['verb_update'] = 'Update';
$string['verb_upload'] = 'Upload';
$string['verb_view'] = 'View';
