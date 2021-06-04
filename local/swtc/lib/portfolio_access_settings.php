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
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/portfolio_access_settings.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/02/20 - Initial writing.
 * 03/12/21 - Only need to check for moodle/category:viewcourselist for each
 *          role for each top level category.
 *
 **/

use \local_swtc\swtc_user;

require_once('../../../config.php');
require_once($CFG->dirroot . '/user/editlib.php');

global $USER, $DB;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once('../forms/portfolio_access_settings_form.php');

// SWTC ********************************************************************************.
// SWTC swtcuser and debug variables.
$swtcuser = swtc_get_user([
    'userid' => $USER->id,
    'username' => $USER->username]);
$debug = swtc_get_debug();
$tablename = 'local_swtc_port_access';
$baseurl = new moodle_url('/local/swtc/lib/portfolio_access_settings.php');
// SWTC ********************************************************************************.

if (isset($debug)) {
    $messages[] = "In /local/swtc/lib/portfolio_access_settings.php ===enter===";
    $debug->logmessage($messages, 'both');
    unset($messages);
}

require_login();

// See if the user clicked on a rolename header.
$roleid = optional_param('roleid', 0, PARAM_INT);

// See if the user clicked on a portfolio header.
$catid = optional_param('catid', 0, PARAM_INT);

// Set up page.
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

$PAGE->set_url($baseurl);

$pagetitle = get_string('portfolio_access_settings', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

// First create the form.
$args = array(
    'user' => $swtcuser,
    'tablename' => $tablename,
    'roleid' => $roleid,
    'catid' => $catid
);

$settingsform = new portfolio_access_settings_form(null, $args);

if (optional_param('submit', false, PARAM_BOOL) && data_submitted() && confirm_sesskey()) {
    $settingsform->process_submission();
    redirect($baseurl, 'Portfolio access settings have been saved.');
}

if (optional_param('portapply', false, PARAM_BOOL) && data_submitted() && confirm_sesskey()) {
    $settingsform->process_submission('portapply');
    redirect($baseurl, 'Portfolio access settings have been saved. Access to all top-level portfolios have been updated.');
}

$settingsform->load_current_settings();

// OUTPUT form.
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

$table = $settingsform->get_portfolio_access_table();

echo $OUTPUT->box($settingsform->get_intro_text());

echo '<form action="' . $baseurl . '" method="post">';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
echo html_writer::table($table);
echo '<div class="buttons">';
echo '<input type="submit" class="btn btn-primary" name="submit" value="' . get_string('savechanges') . '"/>';
echo '&nbsp;&nbsp;<input type="submit" class="btn btn-primary" name="portapply" value="' .
    get_string('portfolio_access_button', 'local_swtc') . '"/>';
echo '</div></form>';

echo $OUTPUT->footer();
