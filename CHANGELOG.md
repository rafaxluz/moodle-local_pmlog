# Changelog

All notable changes to this project will be documented in this file.

## [0.1.5] - 2026-02-19

### Added
- **Pagination**: Implemented pagination on both the Student Timeline (`timeline.php`) and Course Main Page (`course.php`) to improve performance with large datasets.
- **Sorting**: Added sorting options to the Course Main Page, allowing users to order students by:
    - Events Count (Descending/Ascending)
    - Name (A-Z/Z-A)
- **Per-Page Selector**: Added a dropdown menu to select the number of items per page (20, 50, 100, All).
- **Moodle Headers**: Applied standard GNU GPL v3 license headers and PHPDoc blocks to all PHP and Mustache files.
- **Language Strings**: Added missing language strings to `lang/en/local_pmlog.php` for better internationalization support.

### Changed
- **SQL Optimization**: Refactored `extractor_standardlog.php` and `course.php` to use `JOIN`s instead of the `IN` operator for user lookups. This prevents database errors with large user counts and improves query efficiency.
- **User Filtering**: Optimised the student filtering logic to use a subquery (`EXISTS`) instead of processing large arrays of user IDs in PHP.
- **UI Improvements**: Replaced hard-coded text in templates and classes with `get_string` calls.