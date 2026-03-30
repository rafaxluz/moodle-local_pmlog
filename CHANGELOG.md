# Changelog

All notable changes to this project are documented in this file.

## [1.0.1] - 2026-03-30

### Changed

- Simplified the case model to a single fixed definition of one learner per course.
- Removed the remaining case-ID selection from manual and scheduled configuration forms.
- Updated the documentation to reflect the fixed trace definition used by the plugin.

## [1.0.0] - 2026-03-19

### Changed

- Promoted the plugin to its first stable release after consolidating the manual flow, scheduled flow, export modes, documentation, and administrative surface.
- Removed legacy administrative artifacts and aligned the project structure with the current navigation model.
- Simplified case handling by fixing the exported trace definition to one user per course and removing extra case-ID options from the user-facing configuration.

## [0.3.0-beta] - 2026-03-19

### Added

- Case handling based on one user per course.
- XES export support alongside CSV export.
- Detailed export modes for CSV and XES using anonymized `cmid` markers.
- Named export modes for CSV and XES using real course and activity names.
- Scheduled execution via Moodle scheduled tasks.
- A dedicated administration page for scheduled processing with course autocomplete.

### Changed

- Standardized the public plugin name to `Process Mining Log Builder`.
- Reworked the pipeline for streaming extraction, batched inserts, safer rebuilds, and per-course locking.
- Expanded manual and scheduled execution flows to support standard, detailed, and named CSV/XES generation.
- Simplified the administration menu to a single plugin entry pointing to scheduled configuration.
- Reworked the course page to keep the build form at the top and group status, exports, settings, and student timelines into collapsible sections.
- Updated the main documentation set to match the current implementation.

### Fixed

- Corrected timeline permission handling to use course context.
- Corrected relabeling and deduplication logic to rely on raw Moodle event attributes.
- Corrected default navigation window values.
- Corrected the scheduled settings submit label to use a valid Moodle core string.
- Corrected export-related references and wording across the documentation.

## [0.1.6] - 2026-02-19

### Changed

- Flattened the "Run pipeline" form by replacing the collapsible header with a simple HTML heading.
- Refactored `classes/local/pipeline_service.php` to reduce method complexity.
- Refactored `classes/local/cleaner_dedup.php` to reduce method complexity.
- Refined the flexible `$options` array in `extractor_standardlog.php`.
- Replaced hard-coded strings in key files with `get_string`.
- Fixed multiple Moodle CodeSniffer standard warnings and errors across the codebase.

## [0.1.5] - 2026-02-19

### Added

- Pagination on both the student timeline and the course main page.
- Sorting options on the course main page by event count and student name.
- A per-page selector for course-level listings.
- Standard GNU GPL v3 license headers and PHPDoc blocks across PHP and Mustache files.
- Missing language strings for improved internationalization support.

### Changed

- Refactored log extraction and course queries to use joins instead of large `IN` lists.
- Optimized student filtering logic to use a subquery instead of large PHP arrays.
- Replaced hard-coded UI text with language-string lookups.
