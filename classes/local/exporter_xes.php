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
 * XES Exporter.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\local;

/**
 * XES Exporter class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exporter_xes {
    /** @var array<int, array<int, string|null>> Cache of course module names by course and CMID. */
    private array $cmnamecache = [];

    /**
     * Export course logs to XES.
     *
     * @param int $courseid The course ID.
     * @param string $filepath The path to save the XES file.
     * @param string $mode Export mode.
     * @throws \moodle_exception If the file cannot be opened.
     */
    public function export_course(
        int $courseid,
        string $filepath,
        string $mode = export_mode::STANDARD
    ): void {
        global $DB;

        $writer = new \XMLWriter();
        if (!$writer->openURI($filepath)) {
            throw new \moodle_exception('Could not open file for writing: ' . $filepath);
        }

        $writer->startDocument('1.0', 'UTF-8');
        $writer->setIndent(true);
        $writer->startElement('log');
        $writer->writeAttribute('xes.version', '1.0');
        $writer->writeAttribute('xes.features', 'nested-attributes');
        $writer->writeAttribute('openxes.version', '1.0RC7');
        $writer->writeAttribute('xmlns', 'http://www.xes-standard.org/');

        $this->write_extension($writer, 'Concept', 'concept', 'http://www.xes-standard.org/concept.xesext');
        $this->write_extension($writer, 'Time', 'time', 'http://www.xes-standard.org/time.xesext');
        $this->write_extension($writer, 'Organizational', 'org', 'http://www.xes-standard.org/org.xesext');

        $this->write_global_trace($writer);
        $this->write_global_event($writer);
        $this->write_classifier($writer, 'Activity', 'concept:name');
        $courserecord = null;
        if ($mode === export_mode::NAMED) {
            $courserecord = get_course($courseid);
            $this->write_string($writer, 'concept:name', format_string($courserecord->fullname));
        } else {
            $this->write_string($writer, 'concept:name', 'course:' . $courseid);
        }

        $sql = "SELECT caseid, activity, activitygroup, timecreated, userid, courseid, cmid, component, eventname, action, target
                  FROM {local_pmlog_events}
                 WHERE courseid = :courseid
                 ORDER BY caseid ASC, timecreated ASC, id ASC";
        $recordset = $DB->get_recordset_sql($sql, ['courseid' => $courseid]);

        $currentcaseid = null;

        foreach ($recordset as $rec) {
            if ($currentcaseid !== $rec->caseid) {
                if ($currentcaseid !== null) {
                    $writer->endElement();
                }

                $currentcaseid = $rec->caseid;
                $writer->startElement('trace');
                $this->write_string($writer, 'concept:name', (string)$rec->caseid);
                $this->write_int($writer, 'courseid', (int)$rec->courseid);
                if ($mode === export_mode::NAMED && $courserecord !== null) {
                    $this->write_string($writer, 'course:name', format_string($courserecord->fullname));
                    $this->write_string($writer, 'course:shortname', format_string($courserecord->shortname));
                }
            }

            $writer->startElement('event');
            $this->write_string(
                $writer,
                'concept:name',
                $this->format_activity($courseid, (string)$rec->activity, $rec->cmid ?? null, $mode)
            );
            $this->write_date($writer, 'time:timestamp', (int)$rec->timecreated);
            $this->write_string($writer, 'org:resource', 'user:' . (int)$rec->userid);
            $this->write_int($writer, 'userid', (int)$rec->userid);
            $this->write_int($writer, 'courseid', (int)$rec->courseid);

            if (!empty($rec->activitygroup)) {
                $this->write_string($writer, 'activitygroup', (string)$rec->activitygroup);
            }
            if (!empty($rec->cmid)) {
                $this->write_int($writer, 'cmid', (int)$rec->cmid);
            }
            if (!empty($rec->component)) {
                $this->write_string($writer, 'component', (string)$rec->component);
            }
            if (!empty($rec->eventname)) {
                $this->write_string($writer, 'eventname', (string)$rec->eventname);
            }
            if (!empty($rec->action)) {
                $this->write_string($writer, 'action', (string)$rec->action);
            }
            if (!empty($rec->target)) {
                $this->write_string($writer, 'target', (string)$rec->target);
            }
            $writer->endElement();
        }

        $recordset->close();

        if ($currentcaseid !== null) {
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
    }

    /**
     * Format the exported activity label.
     *
     * @param int $courseid Course ID.
     * @param string $activity Base activity label.
     * @param int|null $cmid Course module ID.
     * @param string $mode Export mode.
     * @return string
     */
    private function format_activity(int $courseid, string $activity, ?int $cmid, string $mode): string {
        if (empty($cmid) || $mode === export_mode::STANDARD) {
            return $activity;
        }

        if ($mode === export_mode::DETAILED) {
            return $activity . ' [cmid:' . (int)$cmid . ']';
        }

        $cmname = $this->get_cm_name($courseid, (int)$cmid);
        if ($mode === export_mode::NAMED && $cmname !== null && $cmname !== '') {
            return $activity . ': ' . $cmname;
        }

        return $activity;
    }

    /**
     * Resolve the real course module name for an export.
     *
     * @param int $courseid Course ID.
     * @param int $cmid Course module ID.
     * @return string|null
     */
    private function get_cm_name(int $courseid, int $cmid): ?string {
        if (!isset($this->cmnamecache[$courseid])) {
            $this->cmnamecache[$courseid] = [];
        }

        if (array_key_exists($cmid, $this->cmnamecache[$courseid])) {
            return $this->cmnamecache[$courseid][$cmid];
        }

        $modinfo = get_fast_modinfo($courseid);
        if (empty($modinfo->cms[$cmid])) {
            $this->cmnamecache[$courseid][$cmid] = null;
            return null;
        }

        $cmname = format_string($modinfo->cms[$cmid]->name);
        $this->cmnamecache[$courseid][$cmid] = $cmname;

        return $cmname;
    }

    /**
     * Write an XES extension element.
     *
     * @param \XMLWriter $writer XML writer.
     * @param string $name Extension name.
     * @param string $prefix Extension prefix.
     * @param string $uri Extension URI.
     */
    private function write_extension(\XMLWriter $writer, string $name, string $prefix, string $uri): void {
        $writer->startElement('extension');
        $writer->writeAttribute('name', $name);
        $writer->writeAttribute('prefix', $prefix);
        $writer->writeAttribute('uri', $uri);
        $writer->endElement();
    }

    /**
     * Write the global trace declaration.
     *
     * @param \XMLWriter $writer XML writer.
     */
    private function write_global_trace(\XMLWriter $writer): void {
        $writer->startElement('global');
        $writer->writeAttribute('scope', 'trace');
        $this->write_string($writer, 'concept:name', '');
        $writer->endElement();
    }

    /**
     * Write the global event declaration.
     *
     * @param \XMLWriter $writer XML writer.
     */
    private function write_global_event(\XMLWriter $writer): void {
        $writer->startElement('global');
        $writer->writeAttribute('scope', 'event');
        $this->write_string($writer, 'concept:name', '');
        $this->write_date($writer, 'time:timestamp', 0);
        $writer->endElement();
    }

    /**
     * Write a classifier.
     *
     * @param \XMLWriter $writer XML writer.
     * @param string $name Classifier name.
     * @param string $keys Classifier keys.
     */
    private function write_classifier(\XMLWriter $writer, string $name, string $keys): void {
        $writer->startElement('classifier');
        $writer->writeAttribute('name', $name);
        $writer->writeAttribute('keys', $keys);
        $writer->endElement();
    }

    /**
     * Write a string attribute.
     *
     * @param \XMLWriter $writer XML writer.
     * @param string $key Attribute key.
     * @param string $value Attribute value.
     */
    private function write_string(\XMLWriter $writer, string $key, string $value): void {
        $writer->startElement('string');
        $writer->writeAttribute('key', $key);
        $writer->writeAttribute('value', $value);
        $writer->endElement();
    }

    /**
     * Write an int attribute.
     *
     * @param \XMLWriter $writer XML writer.
     * @param string $key Attribute key.
     * @param int $value Attribute value.
     */
    private function write_int(\XMLWriter $writer, string $key, int $value): void {
        $writer->startElement('int');
        $writer->writeAttribute('key', $key);
        $writer->writeAttribute('value', (string)$value);
        $writer->endElement();
    }

    /**
     * Write a date attribute.
     *
     * @param \XMLWriter $writer XML writer.
     * @param string $key Attribute key.
     * @param int $timestamp Unix timestamp.
     */
    private function write_date(\XMLWriter $writer, string $key, int $timestamp): void {
        $writer->startElement('date');
        $writer->writeAttribute('key', $key);
        $writer->writeAttribute('value', gmdate('Y-m-d\TH:i:s\Z', $timestamp));
        $writer->endElement();
    }
}
