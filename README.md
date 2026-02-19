# PM Log Builder (local_pmlog)

## Description

The **PM Log Builder** is a Moodle local plugin designed to extract, normalize, and export event logs for Process Mining analysis. It transforms raw Moodle events into a structured "Activity Log" format suitable for tools like ProM, Disco, or Celonis.

Key features:
- **Normalization**: Maps diverse Moodle events into standardized activities.
- **Deduplication**: Intelligently merges sequential identical events (with a configurable time window) to reduce noise.
- **Ad-hoc Processing**: Builds logs asynchronously using Moodle's task API (cron), ensuring performance is not impacted for large courses.
- **Timeline View**: Provides a per-student timeline visualization within Moodle for quick analysis.
- **CSV Export**: Allows downloading the normalized event log for external analysis.

## Requirements

- **Moodle**: 3.8 or later.
- **PHP**: 7.4 or later recommended.

## Installation

1.  Download the plugin and extract it into your Moodle `local/` directory.
    - Path should be: `your-moodle-site/local/pmlog`
2.  Log in to your Moodle site as an administrator.
3.  Go to **Site administration** > **Notifications** to trigger the plugin installation.
4.  Follow the on-screen prompts to upgrade the database.

## Usage

### Building a Log

1.  Navigate to a course.
2.  Access the plugin via the **Course Menu** > **More** > **PM Log Builder** (allowed for the default roles of manager and teacher).
3.  Configure the extraction parameters:
    - **Time Window**: Start and end timestamps (optional).
    - **Resolution**: Windows for grouping course vs. module views.
    - **Filtering**: Option to include only student actions.
    - **Deduplication**: Enable and set a window (in seconds) to merge repeated hits (e.g., student refreshing a page).
4.  Click **Run**. The task will be queued and processed by the Moodle cron system.

### Viewing Results

- **Status**: The page will show the last run time and stats (raw events vs. stored events).
- **Download**: Once processed, a "Download CSV" link will appear to get the dataset.
- **Timeline**: A list of students with event counts will appear. Click "Open timeline" to see a sequential view of a specific student's actions.

## Privacy

This plugin processes and stores user activity data.
- It extracts data from the standard Moodle `logstore_standard` (or related) tables.
- It stores a normalized copy in `{local_pmlog_events}`.
- Data includes: User ID, Course ID, Event Name, Timestamp, and Context.
- Adheres to Moodle's Privacy API (GDPR compliance).

## Copyright

(c) 2026 rafaxluz
Licensed under the GNU GPL v3 or later.
