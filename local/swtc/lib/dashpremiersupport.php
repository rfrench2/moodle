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

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');
require_once($CFG->dirroot.'/local/swtc/lib/dashboardlib.php');                   // Required functions for statistics_form.

global $USER, $SESSION;        // Lenovo

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
admin_externalpage_setup('dashpremiersupport');

// Setup $PAGE here.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/swtc/lib/dashpremier.php'));
$PAGE->set_pagelayout('admin');
$pagetitle = 'Users';
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add($pagetitle);

// OUTPUT form.
echo $OUTPUT->header();

// Print out a heading.
echo $OUTPUT->heading($pagetitle, 2, 'headingblock');

// Columns to display.
$columns = array(
        'geo'           => 'Geography',
        'studs'           => 'Students',
        'mgrs'           => 'Managers',
        'admins'           => 'Adminstrators',
        'geoadmins'           => 'GEO</br>Administrators'
);

// Geographies.
$geos = array(
        'US'           => 'US',
        'LA'           => 'LA',
        'CA'           => 'CA',
        'EMEA'           => 'EM',
        'AP'           => 'AP'
);

// All PS cohort names.
$names = array(
    'PS-siteadmins',
    'PS-AP-geoadmins',
    'PS-AP%-admins',
    'PS-AP%-mgrs',
    'PS-AP%-studs',
    'PS-CA-geoadmins',
    'PS-CA%-admins',
    'PS-CA%-mgrs',
    'PS-CA%-studs',
    'PS-EM-geoadmins',
    'PS-EM%-admins',
    'PS-EM%-mgrs',
    'PS-EM%-studs',
    'PS-LA-geoadmins',
    'PS-LA%-admins',
    'PS-LA%-mgrs',
    'PS-LA%-studs',
    'PS-US-geoadmins',
    'PS-US%-admins',
    'PS-US%-mgrs',
    'PS-US%-studs'

);

// Get all of the cohorts (named the same as the PS geos).
//      @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
//      SELECT * FROM `mdl_cohort_members` WHERE cohortid = 6
$sql = "SELECT COUNT(cm.id) AS membercnt FROM {cohort} c, {cohort_members} cm
            WHERE (c.name LIKE :name) AND (cm.cohortid = c.id)";

// $table = new flexible_table('invitehistory');
$table = new flexible_table('dashpremier');
$table->define_columns(array_keys($columns));
$table->define_headers(array_values($columns));
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable');
$table->setup();


foreach ($geos as $geo) {
    /* Build display row:
     * [0] - geo
     * [1] - studs
     * [2] - mgrs
     * [3] - admins
     */

     // Display Geography.
    $row[0] = $geo;

    // Display studs.
    $studs = 'PS-'. $geo .'%-studs';
    $records = $DB->get_records_sql($sql, array('name'=>$studs));
    // print_object($studs);
    // print_object($records);
    $row[1] = key($records);

    // Display mgrs.
    $mgrs = 'PS-'. $geo .'%-mgrs';
    $records = $DB->get_records_sql($sql, array('name'=>$mgrs));
    $row[2] = key($records);

    // Display admins.
    $admins = 'PS-'. $geo .'%-admins';
    $records = $DB->get_records_sql($sql, array('name'=>$admins));
    $row[3] = key($records);

    // Display GEO admins.
    $geoadmins = 'PS-'. $geo .'-geoadmins';
    $records = $DB->get_records_sql($sql, array('name'=>$geoadmins));
    $row[4] = key($records);

    $table->add_data($row);
}

$table->finish_output();


// Lenovo ********************************************************************************
// Change section header.
// Lenovo ********************************************************************************

echo $OUTPUT->footer();
