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
 * Scheduled settings form.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

use local_pmlog\local\casebuilder;

/**
 * Form for scheduled build settings.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class scheduled_settings_form extends \moodleform {
    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;
        $courseoptions = $this->_customdata['courseoptions'] ?? [];

        $mform->addElement('advcheckbox', 'scheduleenabled', get_string('scheduleenabled', 'local_pmlog'));

        $mform->addElement(
            'autocomplete',
            'schedulecourseids',
            get_string('schedulecourses', 'local_pmlog'),
            $courseoptions,
            ['multiple' => true]
        );
        $mform->addHelpButton('schedulecourseids', 'schedulecourses', 'local_pmlog');

        $caseidoptions = [
            casebuilder::STRATEGY_USER_COURSE => get_string('caseidstrategy_user_course', 'local_pmlog'),
            casebuilder::STRATEGY_USER_DAY => get_string('caseidstrategy_user_day', 'local_pmlog'),
            casebuilder::STRATEGY_USER_COURSE_DAY => get_string('caseidstrategy_user_course_day', 'local_pmlog'),
        ];
        $mform->addElement('select', 'schedulecaseidstrategy', get_string('caseidstrategy', 'local_pmlog'), $caseidoptions);

        $mform->addElement('advcheckbox', 'schedulestudentonly', get_string('onlystudents', 'local_pmlog'));
        $mform->addElement('advcheckbox', 'schedulededup', get_string('dedup', 'local_pmlog'));
        $mform->addElement('advcheckbox', 'schedulededupstrictcmid', get_string('dedup_strict_cmid', 'local_pmlog'));

        $mform->addElement('text', 'schedulededupwindow', get_string('dedupwindow', 'local_pmlog'));
        $mform->setType('schedulededupwindow', PARAM_INT);

        $mform->addElement('text', 'schedulecourseviewwindow', get_string('courseviewwindow', 'local_pmlog'));
        $mform->setType('schedulecourseviewwindow', PARAM_INT);

        $mform->addElement('text', 'schedulemoduleviewwindow', get_string('moduleviewwindow', 'local_pmlog'));
        $mform->setType('schedulemoduleviewwindow', PARAM_INT);

        $csvgroup = [];
        $csvgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportcsv',
            '',
            get_string('standard', 'local_pmlog')
        );
        $csvgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportcsvdetailed',
            '',
            get_string('detailed', 'local_pmlog')
        );
        $csvgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportcsvnamed',
            '',
            get_string('named', 'local_pmlog')
        );
        $mform->addGroup(
            $csvgroup,
            'scheduleexportcsvgroup',
            get_string('exportcsv', 'local_pmlog'),
            [' '],
            false
        );

        $xesgroup = [];
        $xesgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportxes',
            '',
            get_string('standard', 'local_pmlog')
        );
        $xesgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportxesdetailed',
            '',
            get_string('detailed', 'local_pmlog')
        );
        $xesgroup[] = &$mform->createElement(
            'advcheckbox',
            'scheduleexportxesnamed',
            '',
            get_string('named', 'local_pmlog')
        );
        $mform->addGroup(
            $xesgroup,
            'scheduleexportxesgroup',
            get_string('exportxes', 'local_pmlog'),
            [' '],
            false
        );

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * Validate submitted data.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        foreach (['schedulededupwindow', 'schedulecourseviewwindow', 'schedulemoduleviewwindow'] as $field) {
            if (isset($data[$field]) && (int)$data[$field] < 0) {
                $errors[$field] = get_string('err_numeric', 'form');
            }
        }

        if (
            isset($data['schedulecaseidstrategy']) &&
            !in_array($data['schedulecaseidstrategy'], casebuilder::valid_strategies(), true)
        ) {
            $errors['schedulecaseidstrategy'] = get_string('invaliddata', 'error');
        }

        return $errors;
    }
}
