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
 * Functions used by suggested courses.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	06/04/19 - Initial writing.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 */

require('../../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/adminlib.php');

require_once($CFG->dirroot.'/local/swtc/forms/suggestedcourses_form.php');
require_once($CFG->dirroot.'/local/swtc/lib/suggestedcourseslib.php');
// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.

global $USER, $PAGE, $SESSION;        // Lenovo

// Lenovo ********************************************************************************.
// Lenovo EBGLMS swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_get_debug();

// Other Lenovo variables.
$access_ps = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch;
$access_lenovo_sd = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch;
$access_ibm = $SESSION->EBGLMS->STRINGS->ibm->access_ibm_pregmatch;
$access_lenovo_stud = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_stud;
$access_lenovo_admin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
$access_lenovo_siteadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
$access_serviceprovider = $SESSION->EBGLMS->STRINGS->serviceprovider->access_serviceprovider_pregmatch_stud;
$access_maintech = $SESSION->EBGLMS->STRINGS->maintech->access_maintech_pregmatch_stud;
$access_asp_maintech = $SESSION->EBGLMS->STRINGS->asp_maintech->access_asp_maintech_pregmatch_stud;

$data = array();
$formdata = array();
$access_sitewide = 'sitewide';
$suggestedcourses = array();                 // Main suggestedcourses array.
// Lenovo ********************************************************************************.

if (isset($debug)) {
    $messages[] = "In /local/swtc/forms/suggestedcourses.php ===suggestedcourses.enter===";
    // $messages[] = "About to print this->config.";
    // $messages[] = print_r($this->config, true);
    // $messages[] = "Finished printing this->config.";
    // print_object($this->config);
    debug_logmessage($messages, 'both');
    unset($messages);
}

// Set alltypes with all the types to look for.
$types = array('sitewide', $access_ps, $access_lenovo_sd, $access_ibm, $access_lenovo_stud, $access_serviceprovider, $access_maintech, $access_asp_maintech);

admin_externalpage_setup('suggestedcourses');

// Setup $PAGE here.
$PAGE->set_context(context_system::instance());
$returnurl = new moodle_url('/local/swtc/lib/suggestedcourses.php');
$PAGE->set_url($returnurl);
$PAGE->set_pagelayout('admin');
$pagetitle = get_string('suggestedcourses', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

// Setup some things before loading the form.
// For example, get all the courses are already selected in all the sections.
foreach($types as $type) {
    $courses[] = suggestedcourses_get_courses_by_type($type);
}

if (isset($debug)) {
    $messages[] = "In /local/swtc/lib/suggestedcourses.php ===enter===";
    // $messages[] = "About to print courses.";
    // $messages[] = print_r($courses, true);
    // print_object($courses);
    // $messages[] = "Finished printing courses.";
    // print_object($courses);
    debug_logmessage($messages, 'both');
    unset($messages);
}

$formdata = array('courses' => $courses);
// print_object($formdata);

$mform = new suggestedcourses_form(null, array('data' => $formdata));        //name of the form you defined in file above.
// $mform = new suggestedcourses_form(null, $formdata);//name of the form you defined in file above.
// $mform->set_data($formdata);
// Default 'action' for form is strip_querystring(qualified_me()).

// Set the initial values, for example the existing data loaded from the database.
// (an array of name/value pairs that match the names of data elements in the form.
// You can also use an object)
// $mform->set_data($toform);
// $mform->set_data();

if ($mform->is_cancelled()) {
    // You need this section if you have a cancel button on your form
    // here you tell php what to do if your user presses cancel
    // probably a redirect is called for!
    // PLEASE NOTE: is_cancelled() should be called before get_data().
    redirect($returnurl);

} else if ($data = $mform->get_data()) {
    // This branch is where you process validated data.
    // Do stuff ...
    // print_object("in get_data\n");
    // print_object($data);
    $keys = suggestedcourses_process_formdata($data);
    // print_object($keys);
    // Typically you finish up by redirecting to somewhere where the user
    // can see what they did.
    // if (!empty($keys)) {
        redirect($returnurl, 'Suggested courses have been updated.');
    // } else {
    //     redirect($returnurl, 'Suggested courses have been updated.');
    // }
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
}

// Lenovo ********************************************************************************
// Change section header.
// Lenovo ********************************************************************************
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
