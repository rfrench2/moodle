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
 *	09/24/18 - Initial writing.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
// require_once($CFG->dirroot . '/user/editlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/dashboardlib.php');                   // Required functions for statistics_form.

class dashboard_advreporting_form extends moodleform {
    function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;

        // Lenovo ********************************************************************************
        // Site course numbers (all time, last year, this year, last month, last week, yesterday, today).
        //          $this->_customdata['today']     "Timestamp is :" .$today->format('H:i:s').".==1.open_logfile===.\n"
        // $date_string = "Site activity (counts) as of " .$today->format('H:i:s')."";
        // $mform->addElement('header', 'siteactivity', $date_string);
        // Lenovo ********************************************************************************
        $header_string = "Advanced reporting dashboard";
        $mform->addElement('header', 'sitecourses', $header_string);

        $mform->addElement('static', 'now', "Time : " .$this->_customdata['now']."");

        $header_string = "Advanced report to run";
        $mform->addElement('header', 'reporttorun', $header_string);
        $mform->setExpanded('reporttorun');

        $text = 'Report to run :';
        $reports = array(
            '332' => '332 Context: All contexts in path'
        );
        $mform->addElement('select', 'reportid', $text, $reports);

        $mform->addElement('text','contextid', 'contextid', 'maxlength="7" size="7"');
        $mform->setType('contextid', PARAM_INT);
        $mform->setDefault('contextid', $this->_customdata['contextid']);


        $header_string = "Output from report";
        $mform->addElement('header', 'outputfromcommand', $header_string);
        $mform->setExpanded('outputfromcommand');

        $contextlevels = "Contextlevels are:</br>
                    CONTEXT_SYSTEM (10)</br>
                    CONTEXT_USER (30)</br>
                    CONTEXT_COURSECAT (40)</br>
                    CONTEXT_COURSE (50)</br>
                    CONTEXT_MODULE (70)</br>
                    CONTEXT_BLOCK (80)";
        format_text($contextlevels, FORMAT_MARKDOWN);
        # $mform->addElement('textarea', 'introduction', $textarea, 'wrap="virtual" rows="20" cols="50"');

        $mform->addElement('static','reportoutput','label',$this->_customdata['reportoutput']);
        $mform->setType('reportoutput', PARAM_TEXT);
        // Set default value by using a passed parameter
        // $mform->setDefault('reportoutput', $this->_customdata['tableoutput']);
        // $mform->addElement('static', 'alltime', 'All time :', $this->_customdata['alltime']);
        // print_object($this->_customdata['reportoutput']);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', 'Run command');
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);


    }

    function definition_after_data(){
        $mform = $this->_form;
        // $mform->applyFilter('username', 'trim');         // Lenovo

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
