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
 * User invitation page. Added as part of the /auth/emailadmin plugin. This is part 1 of 2 (part 2 is signup.php).
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	08/23/18 - Initial writing.
 * 11/04/19 - Added correct setting of swtc_user and debug information.
 * 03/02/20 - Removed require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php') from all modules except /local/swtc/lib/swtc_userlib.php.
 *
 */

require('../../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/adminlib.php');

require_once($CFG->dirroot.'/local/swtc/forms/dashboard_premier_form.php');
// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');        // 03/02/20
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/dashboardlib.php');                   // Required functions for statistics_form.
global $USER, $PAGE, $SESSION;        // Lenovo

//****************************************************************************************.
// Lenovo EBGLMS swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_get_debug();

// Other Lenovo variables.
$formdata = array();        // Pass data to form.
//****************************************************************************************.

// Lenovo *******************************************************************************
//
// Lenovo *******************************************************************************
admin_externalpage_setup('dashpremier');

// Setup $PAGE here.
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title('Dashboards');
$PAGE->set_heading('Dashboards');
$PAGE->set_url($CFG->wwwroot.'/local/swtc/forms/dashboard_premier_form.php');

// Setup some things before loading the form.
$today = new DateTime("now", core_date::get_user_timezone_object());
$formdata['now'] = $today->format('H:i:s');

$formdata['alltime']  = number_format(count_records_activity($today, "alltime"));
$formdata['lastyear']  = number_format(count_records_activity($today, "lastyear"));
$formdata['thisyear']  = number_format(count_records_activity($today, "thisyear"));

$mform = new dashboard_premier_form();//name of the form you defined in file above.
$mform->set_data($formdata);
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

} else if ($fromform = $mform->get_data()) {
    // This branch is where you process validated data.
    // Do stuff ...

    // Typically you finish up by redirecting to somewhere where the user
    // can see what they did.
    redirect($nexturl);
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
