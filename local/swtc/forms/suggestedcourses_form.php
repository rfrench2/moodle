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
 * Set suggested courses.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo EBG Services Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	06/04/19 - Initial writing.
 * 07/01/19 - For Moodle 3.7 (and all previous), need to pass more fields to get_courses (since coursecatlib::get_courses
 *                   eventually calls get_course which requires "visible" and "category" fields).
 * 07/18/19 - In local_swtc_courses, changed field "courseids" to "courseid".
 * 07/31/19 - Added maximum of 12 suggested courses per section string.
 * 08/22/19 - Added call to /local/swtc/lib/locallib.php:local_swtc_get_all_courses; always expand "sitewide" section and
 *                      any other sections that have selections.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
// Lenovo ********************************************************************************.

class suggestedcourses_form extends moodleform {
    function definition() {
        global $USER, $CFG, $DB;

        $trunclength = 100;
        $coursefullname = null;
        $courses = array();
        $sections = array('sitewide', 'ibm', 'lenovo', 'serviceprovider', 'premiersupport', 'servicedelivery', 'maintech', 'asp');

        $mform = $this->_form;
        $data    = $this->_customdata['data'];      // What courses are already selected in all the sections.
        // print_object($data);

        $mform->addElement('header', null, get_string('suggestedcourses', 'local_swtc'));

        // Lenovo ********************************************************************************.
        // 05/30/19 - Get a list of all the courses.
        // 07/01/19 - For Moodle 3.7 (and all previous), need to pass more fields to get_courses (since coursecatlib::get_courses
        //                  eventually calls get_course which requires "visible" and "category" fields).
        // 08/22/19 - Added call to /local/swtc/lib/locallib.php:local_swtc_get_all_courses.
        // Lenovo ********************************************************************************.
        // $records = get_courses('all', 'c.sortorder ASC', 'c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category');
        $records = local_swtc_get_all_courses();
        foreach ($records as $record) {
            $fullnamelength = core_text::strlen($record->fullname);

            // If for some reason the length requested is equal to or is greater than the current length of the course fullname, return
            //      the entire course fullname. If not, return only what was requested.
            if ($trunclength >= $fullnamelength) {
                $coursefullname = $record->fullname;
            } else {
                // shorten_text is in /lib/moodlelib.php.
                $coursefullname = shorten_text($record->fullname, $trunclength);
            }

            // print_object($coursefullname);
            $courses[$record->id] = $record->shortname .' '. $coursefullname;
        }

        asort($courses);

        // Lenovo ********************************************************************************.
        // The course listbox data.
        // IMPORTANT! To align the listbox at the left margin, use the following CSS code (Appearance > Themes >
        //          Adaptable > Custom CSS & JS):
        //
        //          .form-item .form-setting, .form-item .form-description, .mform .fitem .felement, #page-mod-forum-search .c1, .mform
        //          .fdescription.required, .userprofile dl.list dd, .form-horizontal .controls
        //          {
        //              margin-left: 0;
        //          }
        //
        // Lenovo ********************************************************************************.
        $attributes = array( 'multiple' => 'multiple', 'size' => 10);

        // Write all the header text.
        $mform->addElement('html', html_writer::tag('p', get_string('suggestedcourses_header1', 'local_swtc')));
        $mform->addElement('html', html_writer::empty_tag('br'));
        $mform->addElement('html', html_writer::tag('p', get_string('suggestedcourses_header2', 'local_swtc')));
        $mform->addElement('html', html_writer::empty_tag('br'));
        $mform->addElement('html', html_writer::tag('p', get_string('suggestedcourses_header3', 'local_swtc')));
        $mform->addElement('html', html_writer::empty_tag('br'));
        $mform->addElement('html', html_writer::tag('p', get_string('suggestedcourses_header4', 'local_swtc')));
        $mform->addElement('html', html_writer::empty_tag('br'));

        // Lenovo ********************************************************************************.
        // Loop on each type of user (including sitewide) and add all the courses.
        //
        // $defaults['unenrol_users'] = array_keys($studentrole);
        // $mform->setDefault($element, $default);
        //
        // Lenovo ********************************************************************************.
        foreach ($sections as $section) {
            $suggestedcourses = $section . 'suggestedcourses';
            $mform->addElement('header', $section, get_string($suggestedcourses, 'local_swtc'));
            // $mform->addElement('static', 'currentcourses', $currentcourses_string);
            $mform->addElement('html', html_writer::tag('p', get_string($section . 'suggestedcourses_header', 'local_swtc')));
            $mform->addElement('select', $suggestedcourses, '', $courses, $attributes);
            // $mform->setType($section . 'suggestedcourses', PARAM_TEXT);

            // Lenovo ********************************************************************************.
            // Set the default to the courses that were loaded from the database.
            // Lenovo ********************************************************************************.
            foreach ($data['courses'] as $suggested) {
                // print_object($suggested);
                $idx = array_key_first($suggested);

                if (stripos($idx, $suggestedcourses) !== false) {
                    $value = $suggested[$idx];
                    // print_object($value);
                    if (!empty($value)) {
                        // $value = explode(', ', $value);      // Lenvo 07/18/19
                        $tmp = array();
                        // The following two lines:
                        //      Get the id of the multiple select listbox.
                        //      Selects (sets) the defaults in the listbox.
                        $element = $mform->getElement($suggestedcourses);
                        $element->setSelected($value);
                        // print_object($value);
                        // Set the header to the current values.
                        // Get the courseids of each course to display.
                        // print_object($courses);
                        foreach ($value as $key => $courseid) {
                            // print_object($courses[$key]);
                            // $tmp .= $courses[$key]->shortname;
                            // print_object($key);
                            // print_r("course id is $courseid");
                            if ($courseid) {
                                list($firstword) = explode(' ', $courses[$courseid], 2);
                                // print_object($firstword);
                                $tmp[] = $firstword;
                            }
                        }
                        // Lenovo ********************************************************************************.
                        // For debugging purposes only.
                        $currentcourses_string = get_string('suggestedcourses_courses', 'local_swtc', array('courses' => implode(', ', $tmp)));
                        $mform->addElement('html', html_writer::tag('p', $currentcourses_string));
                        $mform->addElement('html', html_writer::tag('p', implode(', ', $value)));
                        // Lenovo ********************************************************************************.
                    } else {
                        $currentcourses_string = get_string('suggestedcourses_none', 'local_swtc');
                        $mform->addElement('html', html_writer::tag('p', $currentcourses_string));
                    }
                    // Lenovo ********************************************************************************.
                    // Set the section opened.
                    // Lenovo ********************************************************************************.
                    $mform->setExpanded($section);
                }
            }
        }

        // Lenovo ********************************************************************************.
        // Always set the site suggested courses section opened.
        // Lenovo ********************************************************************************.
        $mform->setExpanded('sitewide');

        $this->add_action_buttons();

    }

    function definition_after_data(){
        $mform = $this->_form;
        // $mform->applyFilter('username', 'trim');         // Lenovo

    }

    /**
     * Validate user supplied data on the suggestedcourses mform.
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

    /**
     * Return submitted data if properly submitted or returns NULL if validation fails or
     * if there is no submitted data.
     *
     * @return object submitted data; NULL if not valid or not submitted or cancelled
     */
    public function get_data() {
        $data = parent::get_data();

        if (!empty($data)) {

            $sections = array('sitewide', 'ibm', 'lenovo', 'serviceprovider', 'premiersupport', 'servicedelivery', 'maintech', 'asp');
            $newdata = null;

            foreach ($sections as $section) {
                // Create the string that will be checked in data.
                $key = $section . 'suggestedcourses';

                // See if the key is contained in the data. If not, add it.
                if (!array_key_exists($key, $data)) {
                    $data->{$key} = array();
                }
            }
        }

        // print_object($data);

        return $data;
    }

}
