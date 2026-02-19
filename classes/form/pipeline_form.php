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
 * Pipeline configuration form.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Pipeline form class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pipeline_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $courseid = $this->_customdata['courseid'] ?? 0;
        $isadmin  = !empty($this->_customdata['isadmin']);

        $mform->addElement('html', \html_writer::tag('h4', get_string('buildnormalizedlog', 'local_pmlog')));

        if ($isadmin) {
            $mform->addElement('text', 'courseid', get_string('courseid', 'local_pmlog'));
            $mform->setType('courseid', PARAM_INT);
            $mform->setDefault('courseid', $courseid > 0 ? $courseid : '');
            $mform->addRule('courseid', null, 'required', null, 'client');
            $mform->addRule('courseid', null, 'numeric', null, 'client');
        } else {
            $mform->addElement('hidden', 'courseid', $courseid);
            $mform->setType('courseid', PARAM_INT);
        }

        $mform->addElement('date_selector', 'timestart', get_string('startdate', 'local_pmlog'), ['optional' => true]);
        
        $mform->addElement('date_selector', 'timeend', get_string('enddate', 'local_pmlog'), ['optional' => true]);

        $mform->addElement('checkbox', 'studentonly', get_string('onlystudents', 'local_pmlog'));
        $mform->setDefault('studentonly', 1);

        $mform->addElement('checkbox', 'dedup', get_string('dedup', 'local_pmlog'));
        $mform->setDefault('dedup', 1);

        $mform->addElement('checkbox', 'dedup_strict_cmid', get_string('dedup_strict_cmid', 'local_pmlog'));
        $mform->setDefault('dedup_strict_cmid', 0);

        $mform->addElement('text', 'dedupwindow', get_string('dedupwindow', 'local_pmlog'));
        $mform->setType('dedupwindow', PARAM_INT);
        $mform->setDefault('dedupwindow', 600);
        $mform->addRule('dedupwindow', null, 'numeric', null, 'client');

        $mform->addElement('text', 'courseviewwindow', get_string('courseviewwindow', 'local_pmlog'));
        $mform->setType('courseviewwindow', PARAM_INT);
        $mform->setDefault('courseviewwindow', 18000000);
        $mform->addRule('courseviewwindow', null, 'numeric', null, 'client');

        $mform->addElement('text', 'moduleviewwindow', get_string('moduleviewwindow', 'local_pmlog'));
        $mform->setType('moduleviewwindow', PARAM_INT);
        $mform->setDefault('moduleviewwindow', 18000000);
        $mform->addRule('moduleviewwindow', null, 'numeric', null, 'client');

        $mform->addElement('checkbox', 'exportcsv', get_string('exportcsv', 'local_pmlog'));
        $mform->setDefault('exportcsv', 1);

        $this->add_action_buttons(false, get_string('runpipeline', 'local_pmlog'));
    }

    /**
     * Validate the form data.
     *
     * @param array $data The submitted data.
     * @param array $files The submitted files.
     * @return array Errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['timestart']) && !empty($data['timeend'])) {
            if ($data['timeend'] < $data['timestart']) {
                $errors['timeend'] = get_string('error_endbeforestart', 'error');
            }
        }

        return $errors;
    }
}
