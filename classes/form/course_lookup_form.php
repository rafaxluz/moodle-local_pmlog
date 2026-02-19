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
 * Course lookup form.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pmlog\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Course lookup form class.
 *
 * @package    local_pmlog
 * @copyright  2026 rafaxluz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_lookup_form extends \moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'lookup', get_string('courseid', 'local_pmlog'));

        $mform->addElement('text', 'courseid', get_string('courseid', 'local_pmlog'));
        $mform->setType('courseid', PARAM_INT);
        $mform->addRule('courseid', null, 'required', null, 'client');
        $mform->addRule('courseid', null, 'numeric', null, 'client');

        $this->add_action_buttons(false, get_string('run', 'local_pmlog'));
    }
}
