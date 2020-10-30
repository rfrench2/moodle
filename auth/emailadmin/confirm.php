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
 * Confirm self registered user.
 * NOTE: based on original 'login/config.php' by Martin Dougiamas.
 *
 * @package    core
 * @subpackage auth
 * @copyright  2012 Felipe Carasso http://carassonet.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 10/30/20 - Initial writing.
 *
 */

require('../../config.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('locallib.php');      // SWTC
global $SESSION;        // SWTC

// SWTC ********************************************************************************
// 08/09/18 - Now requiring login to confirm accounts.
// SWTC ********************************************************************************
require_login();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');

// SWTC ********************************************************************************.
// SWTC LMS swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_set_debug();

// Other SWTC variables.
$access_ibm_email_domain = 'ibm.com';							// SWTC - IBM email domain.
$access_lenovo_email_domain = 'lenovo.com';					// SWTC - Lenovo email domain.
$access_type = '';																// SWTC - To hold the 'Access type' value based on email domain.
// SWTC - Checking for the 'Access type' of 'IBM-stud'.
$access_ibm_student = $SESSION->EBGLMS->STRINGS->ibm->access_ibm_stud;
// SWTC - Checking for the 'Access type' of 'Lenovo-stud'.
$access_lenovo_student = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_stud;
//****************************************************************************************.

// SWTC
//			Important! Until a method is found, must hard-code the id of the custom sql report to run.
//			In the .244 sandbox, the NON-Confirmed user report is id 27; on the production site the id for the report is 32.
$reportid = 32;
$url_nonconfirmed_user_report = ("$CFG->wwwroot/report/customsql/view.php?id=" . $reportid);

// SWTC
if (isset($debug)) {
	$messages[] = "In auth/emailadmin/auth/confirm.php ===1.enter===";
	$messages[] = "access_lenovo_student is :<strong>$access_lenovo_student</strong>.";
	$messages[] = "access_lenovo_email_domain is :<strong>$access_lenovo_email_domain</strong>.";
	$messages[] = "access_ibm_student is :<strong>$access_ibm_student</strong>.";
	$messages[] = "access_ibm_email_domain is :<strong>$access_ibm_email_domain</strong>.";
    $debug->logmessage($messages, 'both');
    unset($messages);
}

require_capability('moodle/user:update', context_system::instance());

$data = optional_param('data', '', PARAM_RAW);  // Formatted as:  secret/username.

$p = optional_param('p', '', PARAM_ALPHANUM);   // Old parameter:  secret.
$s = optional_param('s', '', PARAM_RAW);        // Old parameter:  username.

$PAGE->set_url('/auth/emailadmin/confirm.php');
$PAGE->set_context(context_system::instance());

if (empty($CFG->registerauth)) {
    print_error('cannotusepage2');
}
$authplugin = get_auth_plugin($CFG->registerauth);

if (!$authplugin->can_confirm()) {
    print_error('cannotusepage2');
}

if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        // SWTC ********************************************************************************
        // Sample URL string is similar to the following:
        //      https://lenovoedu.lenovo.com/auth/emailadmin/confirm.php?data=zFfFfUaDUpwGrS2/jgalindo
        //      if the user selected a typical username.
        //      If the user used their email address as their username, the URL will look like the following:
        //      https://lenovoedu.lenovo.com/auth/emailadmin/confirm.php?data=SeECjsmmMxG72ZT/caomin1%40lenovo%2Ecom.
        // SWTC ********************************************************************************
        $dataelements = explode('/', $data, 2); // Stop after 1st slash. Rest is username. MDL-7647.
        $usersecret = $dataelements[0];
        $username   = $dataelements[1];
    } else {
        $usersecret = $p;
        $username   = $s;
    }

    // SWTC ********************************************************************************
    // Note: user_confirm now sends email; no need to send one later.
    // SWTC ********************************************************************************
    $confirmed = $authplugin->user_confirm($username, $usersecret);

    if ($confirmed == AUTH_CONFIRM_ALREADY) {
        $user = get_complete_user_data('username', $username);
        $PAGE->navbar->add(get_string("alreadyconfirmed"));
        $PAGE->set_title(get_string("alreadyconfirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        // echo "<h3>".get_string("thanks").", ". fullname($user) . "</h3>\n";      // SWTC
        // echo "<p>".get_string("alreadyconfirmed")."</p>\n";      // SWTC
        // echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));     // SWTC
        echo "<h3>The registration for user </h3>\n";
		echo "<h3>". fullname($user) . "</h3>\n";
		echo "<h3>has already been processed.</h3>\n";
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;

    } else if ($confirmed == AUTH_CONFIRM_OK) {

        // The admin confirmed the account.

        if (!$user = get_complete_user_data('username', $username)) {
            print_error('cannotfinduser', '', '', s($username));
        }

        // SWTC
		// Load and then set the customized user profile field "Accesstype" to a default value based on email domain.
		//
		if ( stripos($user->email, $access_lenovo_email_domain) !== false) {
			$access_type = $access_lenovo_student;
		} else {
			$access_type = $access_ibm_student;
		}

        $user->profile_field_Accesstype = $access_type;
		// $user->profile_field_Accesstype = 'This is a test';

		if (isset($debug)) {
			$messages[] = "In auth/emailadmin/auth/confirm.php ===1.1===";
			$messages[] = "user->email is :<strong>$user->email</strong>.";
			$messages[] = "inital access type for user is :<strong>$access_type</strong>.";
			$messages[] = "user->profile_field_Accesstype is :<strong>$user->profile_field_Accesstype</strong>.";
		//	print_r($PAGE);
            $debug->logmessage($messages, 'both');
            unset($messages);
		}

		// Save any custom profile field information
		profile_save_data($user);

		// SWTC
		//		Build the URL to the users profile if it is needed...
		$url_update_user_profile = ("$CFG->wwwroot/user/editadvanced.php?id=" . $user->id . "&course=1");

		if (isset($debug)) {
			$messages[] = "In auth/emailadmin/auth/confirm.php; after calling <strong>profile_save_data</strong> ===1.2===";
			$messages[] = "URL to edit users profile is :<strong>$url_update_user_profile</strong>.";
			$messages[] = "user->email is :<strong>$user->email</strong>.";
			$messages[] = "user follows:";
			$messages[] = print_r($user, true);
            $debug->logmessage($messages, 'both');
            unset($messages);
		}

        // SWTC ********************************************************************************
        // Note: user_confirm now sends email; no need to send one here.
        // SWTC ********************************************************************************
        // send_confirmation_email_user($user);

		// SWTC ********************************************************************************
        // 08/09/18 - Since user is confirmed, changed invitation status to 'Accepted' and put userid in table.
        // SWTC ********************************************************************************
        $invitationmanager = new invitation_manager();

        // See if user has an active invitation already.
        list($status, $invite) = $invitationmanager->search_invites_for_email($user->email);

        // Set users invitation to 'Accepted' and set userid.
        if (isset($invite)) {
            $invitationmanager->set_invite_used($invite, $user->id);
            // $invite->userid = $user->id;
            // $invite->tokenused = true;
            // $invite->timeused = time();
            // $DB->update_record('local_swtc_userinvitation', $invite);
        } else {
            // TODO: Did NOT find invitation; some type of catastrophic error happened.
        }

        $PAGE->navbar->add(get_string("confirmed"));
        $PAGE->set_title(get_string("confirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        // echo "<h3>".get_string("thanks").", ". fullname($USER) . "</h3>\n";      // SWTC
        // echo "<p>".get_string("confirmed")."</p>\n";     // SWTC
        // echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));     // SWTC
		echo "<h3>The registration for user </h3>\n";       // SWTC
		echo "<h3>". fullname($user) . "</h3>\n";       // SWTC
		echo "<h3>was sucessful.</h3>\n";       // SWTC
        echo $OUTPUT->single_button($url_update_user_profile, get_string('swtc_update_user_profile', 'auth_emailadmin'));     // SWTC
		echo $OUTPUT->single_button($url_nonconfirmed_user_report, get_string('swtc_run_nonconfirmed_report', 'auth_emailadmin'));    // SWTC
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();

        // SWTC
		if (isset($debug)) {
			$messages[] = "In auth/emailadmin/auth/confirm.php ===1.leave===";
            $debug->logmessage($messages, 'both');
            unset($messages);
		}

        exit;
    } else {
        mtrace("Confirm returned: ". $confirmed);
        print_error('invalidconfirmdata');
    }
} else {
    print_error("errorwhenconfirming");
}

// SWTC
if (isset($debug)) {
	$messages[] = "In auth/emailadmin/auth/confirm.php ===1.leave===";
    $debug->logmessage($messages, 'both');
    unset($messages);
}

redirect("$CFG->wwwroot/");
