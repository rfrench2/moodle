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
 * @copyright  2020 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	02/24/20 - Initial writing.
 *
 */

require('../../../config.php');
// require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

global $USER, $SESSION;        // Lenovo

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/tableaulib.php');

// Lenovo ********************************************************************************.
// Lenovo EBGLMS swtc_user and debug variables.
// Lenovo EBGLMS swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_get_debug();

// Other Lenovo variables.
$messages = array();
// Lenovo ********************************************************************************.

// Lenovo *******************************************************************************
//
// Lenovo *******************************************************************************
admin_externalpage_setup('tableauecut');

// Setup URL.
// $id = optional_param('id', null, PARAM_INT);
// if (!empty($id)) {
//     $baseurl = new moodle_url('/local/swtc/lib/tableau.php', array ('id'=>$id));
// } else {
//     $baseurl = new moodle_url('/local/swtc/lib/tableau.php');
// }
$baseurl = new moodle_url('/local/swtc/lib/tableau.php');

// Setup $PAGE here.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
// $PAGE->set_url(new moodle_url('/local/swtc/lib/tableauenrollmentsbyusertype.php'), array('id' => $id));
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');
$pagetitle = 'Enrollment / Complition Dashboard';
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add($pagetitle);

// OUTPUT form.
echo $OUTPUT->header();

// Print out a heading.
echo $OUTPUT->heading($pagetitle, 2, 'headingblock');

// Columns to display; all (tracked) user types.
$columns = array(
    'Course shortname',
    'IBM',
    'Lenovo',
    'Lenovo-ServiceDelivery',
    'PremierSupport',
    'SelfSupport',
    'ServiceProvider',
    'Maintech',
    'ASP-Maintech'
);



// All (tracked) user types.
$usertypes = array(
    'ASP-Maintech%',
    'IBM%',
    'Lenovo%',
    'Lenovo-ServiceDelivery%',
    'Maintech%',
    'PremierSupport%',
    'SelfSupport%',
    'ServiceProvider%'
);

// Get all course shortnames.
// $sql = "SELECT c.shortname
//             FROM {course} AS c
//             LEFT OUTER JOIN {course_categories} AS cc ON (cc.id = c.category)
//             WHERE (c.shortname NOT IN ('LenInternalSharedResources', 'ES10000')) AND ((cc.path NOT LIKE '/60/%') AND (cc.path NOT LIKE '%/60') AND (cc.path NOT LIKE '/73/%') AND (cc.path NOT LIKE '%/73'))";
//
// $shortnames = $DB->get_records_sql($sql);
$shortnames = ('ES41758', 'PA056');



// Lenovo ********************************************************************************
// Change section header.
// Lenovo ********************************************************************************

echo $OUTPUT->footer();
