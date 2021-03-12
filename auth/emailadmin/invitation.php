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
 * User invitation page. Added as part of the /auth/emailadmin plugin. This is part 2 of 2 (part 1 is signup.php).
 *
 * @package    auth
 * @subpackage emailadmin
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/30/20 - Initial writing.
 *
 */

require('../../config.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->libdir . '/authlib.php');
// require_once('lib.php');
require_once($CFG->dirroot . '/login/lib.php');
require_once('locallib.php');

// Check if user is passing a token they were sent.
$signupinvitationtoken = required_param('token', PARAM_ALPHANUM);

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

global $SESSION, $USER;

//****************************************************************************************.
// SWTC LMS swtc_user and debug variables.
// $swtc_user = swtc_get_user($USER);
// $debug = swtc_set_debug();

// Other SWTC variables.
// Invitation status strings.
$status_active = get_string('status_invite_active', 'local_swtc');
$status_used = get_string('status_invite_used', 'local_swtc');
$status_expired = get_string('status_invite_expired', 'local_swtc');
$status_invalid = get_string('status_invite_invalid', 'local_swtc');
//****************************************************************************************.

// If the user passes a token, attempt to retrieve it.
if (isset($signupinvitationtoken)) {
    $invitation = $DB->get_record('local_swtc_userinvitation', array('token' => $signupinvitationtoken));

    // print_object($invitation);
    $invitationmanager = new invitation_manager();

     // Get the status of the invitation (possibilities are : 'Active', 'Expired', 'Used', or 'Invalid').
    $status = $invitationmanager->get_invite_status($invitation);

    // If token used is NOT valid (active), error message and exit.
    if ($status != $status_active) {
        $invitationmanager->decline_invitation_from_user($status, $invitation);
        exit; //never reached
    }
}

if (!$authplugin = signup_is_enabled()) {
    print_error('notlocalisederrormessage', 'error', '', 'Sorry, you may not use this page.');
}

// $PAGE->set_url('/login/signup.php');                             // SWTC
// $PAGE->set_url('/auth/emailadmin/signup.php');          // SWTC - works first time through, but not second.
// $PAGE->set_url('/auth/emailadmin/signup.php', array('token' => $signupinvitationtoken));          // 01/08/19
$PAGE->set_url('/auth/emailadmin/invitation.php', array('token' => $signupinvitationtoken));        // 01/08/19
$PAGE->set_context(context_system::instance());

// If wantsurl is empty or /login/signup.php, override wanted URL.
// We do not want to end up here again if user clicks "Login".
if (empty($SESSION->wantsurl)) {
    $SESSION->wantsurl = $CFG->wwwroot . '/';
} else {
    $wantsurl = new moodle_url($SESSION->wantsurl);
    if ($PAGE->url->compare($wantsurl, URL_MATCH_BASE)) {
        $SESSION->wantsurl = $CFG->wwwroot . '/';
    }
}

if (isloggedin() and !isguestuser()) {
    // Prevent signing up when already logged in.
    echo $OUTPUT->header();
    echo $OUTPUT->box_start();
    $logout = new single_button(new moodle_url('/login/logout.php',
        array('sesskey' => sesskey(), 'loginpage' => 1)), get_string('logout'), 'post');
    $continue = new single_button(new moodle_url('/'), get_string('cancel'), 'get');
    echo $OUTPUT->confirm(get_string('cannotsignup', 'error', fullname($USER)), $logout, $continue);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

// If verification of age and location (digital minor check) is enabled.
if (\core_auth\digital_consent::is_age_digital_consent_verification_enabled()) {
    $cache = cache::make('core', 'presignup');
    $isminor = $cache->get('isminor');
    if ($isminor === false) {
        // The verification of age and location (minor) has not been done.
        redirect(new moodle_url('/login/verify_age_location.php'));
    } else if ($isminor === 'yes') {
        // The user that attempts to sign up is a digital minor.
        redirect(new moodle_url('/login/digital_minor.php'));
    }
}

// Plugins can create pre sign up requests.
// Can be used to force additional actions before sign up such as acceptance of policies, validations, etc.
core_login_pre_signup_requests();

// SWTC *******************************************************************************
// The following signup_form call loads /auth/emailadmin/signup_form (if all goes well).
// 01/08/19 - Changed to invitation_form.
// SWTC *******************************************************************************
// $mform_signup = $authplugin->signup_form();		// 01/08/19
$mform_invitation = $authplugin->invitation_form();	// 01/08/19
$mform_invitation->set_data(array('token' => $invitation->token, 'email' => $invitation->email, 'email2' => $invitation->email));

if ($mform_invitation->is_cancelled()) {
    redirect(get_login_url());

} else if ($user = $mform_invitation->get_data()) {
    // Add missing required fields.
    $user = signup_setup_new_user($user);

    $authplugin->user_signup($user, true); // prints notice and link to login/index.php

    exit; //never reached
}

// SWTC ********************************************************************************
// Change section header.
// SWTC ********************************************************************************
$newaccount = get_string('newaccount');
$login      = get_string('login');

$PAGE->navbar->add($login);
$PAGE->navbar->add($newaccount);

$PAGE->set_pagelayout('login');
$PAGE->set_title($newaccount);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

if ($mform_invitation instanceof renderable) {
    // Try and use the renderer from the auth plugin if it exists.
    try {
        $renderer = $PAGE->get_renderer('auth_' . $authplugin->authtype);
    } catch (coding_exception $ce) {
        // Fall back on the general renderer.
        $renderer = $OUTPUT;
    }
    echo $renderer->render($mform_invitation);
} else {
    // Fall back for auth plugins not using renderables.
    $mform_invitation->display();
}
echo $OUTPUT->footer();
