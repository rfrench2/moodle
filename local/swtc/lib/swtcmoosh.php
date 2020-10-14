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
 *	09/24/18 - Initial writing.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 */

require('../../../config.php');
require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

// Lenovo ********************************************************************************
// Include globals (sets $EBGLMS).
// Lenovo ********************************************************************************
require_once($CFG->dirroot.'/local/swtc/lib/swtc.php');                     // All EBGLMS global information.
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
// require_once($CFG->dirroot.'/local/swtc/lib/dashboardlib.php');                   // Required functions for statistics_form.

global $SESSION;        // Lenovo

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS classes.
// Lenovo ********************************************************************************.
use \local_swtc\core\local_swtc_user;
use \local_swtc\core\local_swtc_debug;

// Lenovo ********************************************************************************.
// Lenovo EBGLMS swtc_user and debug variables.
$swtc = new \local_swtc\core\local_swtc_user();
$debug = new \local_swtc\core\local_swtc_debug($swtc->swtc_user);

// Other Lenovo variables.
$messages = array();
$swtc_user = $swtc->swtc_user;
// Lenovo ********************************************************************************.

// Lenovo *******************************************************************************
//
// Lenovo *******************************************************************************
admin_externalpage_setup('dashmoosh');

// Setup $PAGE here.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/swtc/lib/runmoosh.php'));
$PAGE->set_pagelayout('admin');
$pagetitle = 'Run Moosh command';
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add($pagetitle);

// OUTPUT form.
echo $OUTPUT->header();

// Print out a heading.
$heading = 'Run Moosh command. Contextlevels are:</br>
CONTEXT_SYSTEM (10)</br>
CONTEXT_USER (30)</br>
CONTEXT_COURSECAT (40)</br>
CONTEXT_COURSE (50)</br>
CONTEXT_MODULE (70)</br>
CONTEXT_BLOCK (80)';
// echo $OUTPUT->heading($pagetitle, 2, 'headingblock');
echo $OUTPUT->heading($heading, 2, 'headingblock');


// Columns to display.
$columns = array(
        'contextid' => 'contextid',
        'instanceid' => 'instanceid',
        'contextlevel' => 'contextlevel'
);

// Get all of the cohorts (named the same as the PS geos).
//      @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
//      SELECT * FROM `mdl_cohort_members` WHERE cohortid = 6
// $sql = "SELECT COUNT(cm.id) AS membercnt FROM {cohort} c, {cohort_members} cm
//            WHERE (c.name = :name) AND (cm.cohortid = c.id)";

// $table = new flexible_table('invitehistory');
$table = new flexible_table('contextinfo');
$table->define_columns(array_keys($columns));
$table->define_headers(array_values($columns));
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable');
$table->setup();

$contextid = 38;

$context = $DB->get_record('context', array('id'=>$contextid));

// Loop on each element of the context path.
$exploded_path = explode('/', $context->path);
// print_object($exploded_path);
// Throw away the first element of the array (always 0).
array_shift($exploded_path);
// print_object($exploded_path);
// die;

// Print header. Contextlevels are:
//      CONTEXT_SYSTEM (10);
//      CONTEXT_USER (30);
//      CONTEXT_COURSECAT (40);
//      CONTEXT_COURSE (50);
//      CONTEXT_MODULE (70);
//      CONTEXT_BLOCK (80)
// echo "contextid\t instanceid\t contextlevel\t\n";

foreach($exploded_path as $key => $id) {
    /* Build display row:
     * [0] - contextid
     * [1] - instanceid
     * [2] - contextlevel
     * [3] - status
     * [4] - dates sent
     * [5] - used date      // Lenovo - 08/10/18 Skipping for now (Status will have user information in there if invitation was accepted).
     * [5] - expiration date
     * [6] - actions
    */

    $temp = $DB->get_record('context', array('id'=>$id));

    // Display contextid.
    $row[0] = $temp->id;

    // Display instanceid.
    $row[1] = $temp->instanceid;

    // Display contextlevel.
    $row[2] = $temp->contextlevel;

    $table->add_data($row);
}
// print_object($table);
// die;
$table->finish_output();

// Lenovo ********************************************************************************
// Change section header.
// Lenovo ********************************************************************************

echo $OUTPUT->footer();
