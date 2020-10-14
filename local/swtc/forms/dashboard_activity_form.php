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
 * New user account invitation form.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo EBG Services Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 *	08/02/18 - Initial writing.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/dashboardlib.php');                   // Required functions for statistics_form.

class dashboard_activity_form extends moodleform {
    function definition() {
        global $USER, $CFG, $DB;

        $mform = $this->_form;

        // Lenovo ********************************************************************************
        // Site activity numbers (all time, last year, this year, last month, last week, yesterday, today).
        //          $this->_customdata['today']     "Timestamp is :" .$today->format('H:i:s').".==1.open_logfile===.\n"
        // $date_string = "Site activity (counts) as of " .$today->format('H:i:s')."";
        // $mform->addElement('header', 'siteactivity', $date_string);
        // Lenovo ********************************************************************************
        $header_string = "Activity dashboard";
        $mform->addElement('header', 'siteactivity', $header_string);

        $mform->addElement('static', 'now', "Time : " .$this->_customdata['now']."");

        // $myCourseIds = getMyCourses($userid);
        // $mform->addElement('select', 'courseid', get_string('courseid', 'local_data'), $myCourseIds);

        $mform->addElement('static', 'alltime', 'All time :', $this->_customdata['alltime']);
        $mform->addElement('static', 'alltime1', '', "This is the total number of unique course activities that have occurred since the site began.");

        $mform->addElement('static', 'lastyear', 'Last year :', $this->_customdata['lastyear']);
        $mform->addElement('static', 'lastyear1', '', "This is the total number of unique course activities that occurred last year.");

        $mform->addElement('static', 'thisyear', 'So far this year :', $this->_customdata['thisyear']);
        $mform->addElement('static', 'thisyear1', '', "This is the total number of unique course activities that occurred this year.");

    }

    function definition_after_data(){
        $mform = $this->_form;
        // $mform->applyFilter('username', 'trim');         // Lenovo

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        global $CFG, $DB;             // Lenovo
        $errors = parent::validation($data, $files);

        // $errors += signup_validate_data($data, $files);              // Lenovo

        return $errors;
    }

}
