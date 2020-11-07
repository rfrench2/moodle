<?php
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
 * Local library file to include classes and functions used.
 *
 * @package    emailadmin invitation
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 10/30/20 - Initial writing (copied from /enrol/invitation/locallib.php and modified).
 *
 */

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');

/**
 * Invitation manager that handles the handling of invitation information.
 *
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class invitation_manager {
    /**
     * Constant for revoking an active invitation.
     */
    const INVITE_EXPIRE = 1;

    /**
     * Constant for extending the expiration time of an active invitation.
     */
    const INVITE_EXTEND = 2;

	/**
     * Constant for reseting the expiration time of an expired invitation.
     */
    const INVITE_RESET = 3;

    /**
     * Constructor.
     *
     * @param N/A
     */
    public function __construct() {
    }

    /**
     * Send invitation (create a unique token for each of them).
     *
     * @param array $data       data processed from the invite form, or an invite
	 *
	 * SWTC history:
	 *
	 * 10/30/20 - Initial writing.
	 *
     */
    public function send_invitation($data) {
        global $DB, $CFG, $SITE, $USER, $PAGE, $OUTPUT, $SESSION;

        //****************************************************************************************.
        // SWTC LMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
    	$debug = swtc_set_debug();

        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering /auth/emailadmin/locallib.php===send_invitation.enter.";
            $messages[] = "About to print SESSION.";
            $messages[] = print_r($SESSION, true);
            $messages[] = "Finished printing SESSION. About to print debug.";
            $messages[] = print_r($debug, true);
            $messages[] = "Finished printing debug.";
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

		// Create unique token for invitation.
        do {
            $token = uniqid();
            $existingtoken = $DB->get_record('local_swtc_userinvitation', array('token' => $token));
        } while (!empty($existingtoken));

        // Save token information in config (token value).
        $invitation = new stdClass();
        $invitation->email = $data->email;
        $invitation->userid = '';
        $invitation->token = $token;
        $invitation->tokenused = false;

        // Set time.
        $timesent = time();
        $invitation->timesent = $timesent;
        $invitation->timeexpiration = $timesent + get_config('local_swtc', 'inviteexpiration');

        $invitation->subject = get_string('default_subject', 'local_swtc', sprintf('%s', $data->email));

        // Construct message: custom (if any) + template.
        $message = '';
        if (!empty($data->message)) {
            $message .= get_string('instructormsg', 'local_swtc', $data->message);
            $invitation->message = $data->message;
        }

        $message_params = new stdClass();
        $message_params->fullname = $SITE->fullname;
        $message_params->expiration = date('M j, Y g:ia', $invitation->timeexpiration);
        // $inviteurl =  new moodle_url('/auth/emailadmin/signup.php', array('token' => $invitation->token));		// 01/08/19
		$inviteurl =  new moodle_url('/auth/emailadmin/invitation.php', array('token' => $invitation->token));		// 01/08/19
        $inviteurl = $inviteurl->out(false);

        $message_params->inviteurl = $inviteurl;
        $message_params->supportemail = $CFG->supportemail;
        $message .= get_string('emailmsgtxt', 'local_swtc', $message_params);

        // SWTC ********************************************************************************
        if (isset($debug)) {
            $messages[] = "About to print message_params.";
            $messages[] = print_r($message_params, true);
            $messages[] = "Finished printing message_params. About to print invitation.";
            $messages[] = print_r($invitation, true);
            $messages[] = "Finished printing invitation.";
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************
        // Here is where all the magic happens.
        // SWTC ********************************************************************************
        $DB->insert_record('local_swtc_userinvitation', $invitation);

        // Set FROM to be $CFG->supportemail.
        $fromuser = new stdClass();
        $fromuser->id = -1;
        $fromuser->deleted = '';
        $fromuser->auth = 'emailadmin';
        $fromuser->firstnamephonetic = '';
        $fromuser->lastnamephonetic = '';
        $fromuser->middlename = '';
        $fromuser->alternatename = '';

        $fromuser->email = $CFG->supportemail;
        $fromuser->firstname = '';
        $fromuser->lastname = $SITE->fullname;
        // $fromuser->maildisplay = true;

        // Send invitation to the user.
        // SWTC ********************************************************************************
        // Since the user does not have an account on the site yet, we have to "fudge" some parameters to get email_to_user
        //      to work.
        // SWTC ********************************************************************************
        $contactuser = new stdClass();
        $contactuser->id = -1;
        $contactuser->deleted = '';
        $contactuser->auth = 'emailadmin';
        $contactuser->firstnamephonetic = '';
        $contactuser->lastnamephonetic = '';
        $contactuser->middlename = '';
        $contactuser->alternatename = '';

        $contactuser->email = $invitation->email;
        $contactuser->firstname = '';
        $contactuser->lastname = '';
        // $contactuser->maildisplay = true;

        // SWTC ********************************************************************************
        if (isset($debug)) {
            $messages[] = "About to print fromuser.";
            $messages[] = print_r($fromuser, true);
            $messages[] = "Finished printing fromuser. About to print contactuser.";
            $messages[] = print_r($contactuser, true);
            $messages[] = "Finished printing contactuser.";
            $messages[] = "subject is :$invitation->subject.";
            $messages[] = "About to print message.";
            $messages[] = print_r($message, true);
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        email_to_user($contactuser, $fromuser, $invitation->subject, $message);

        // SWTC ********************************************************************************
        // Copied parts of /auth/emailadmin/user_signup to here.
        // SWTC ********************************************************************************
        $invitationsent = get_string('invitationsent', 'local_swtc');
        $PAGE->navbar->add($invitationsent);
        $PAGE->set_title($invitationsent);
        $PAGE->set_heading($PAGE->course->fullname);
        echo $OUTPUT->header();

        notice(get_string('invitationsent_desc1', 'local_swtc', $contactuser->email), "$CFG->wwwroot/index.php");

    }

    /**
     * Has a user already been sent an invitation? If so, what is its status?
     *
     * @param string $email    Email of user
     *
     * @return array           Returns status string and invite array (if the user has
     *                  already been sent an invitation AND it has not expired yet) or
     *                  null (the email was not found OR the email was found but the invitation has expired).
     */
    public function search_invites_for_email($email) {
        global $DB;

        // Get all invites for the email (if any).
        $invites = $DB->get_records('local_swtc_userinvitation', array('email'=>$email));

        // No invitation found for the email entered.
        if (empty($invites)) {
            return array(null, null);
        } else {
            $status_active = get_string('status_invite_active', 'local_swtc');
            $status_used = get_string('status_invite_used', 'local_swtc');
            $status_expired = get_string('status_invite_expired', 'local_swtc');

            // There should only be one (unless an existing invite has expired or there is testing going on).
            foreach ($invites as $invite) {
                // An invitation was found for the user. See if it has expired or not OR been used already.
                $status = $this->get_invite_status($invite);

                switch ($status) {
                    // User has a currently active invitation.
                    case $status_active:
                        return array($status_active, $invite);
                        break;

                    // User has used an invitation.
                    case $status_used:
                        return array($status_used, $invite);
                        break;

                    // User had an invitation, but it has expired.
                    case $status_expired:
                        // However, they may have already created a new invitation. If they have,
                        //      count($invites) would be > 1.
                        //      So, if count($invites) == 1, return null, null. If count($invites) > 1,
                        //          continue (to see what the other invites status are).
                        if (count($invites) == 1) {
                            return array(null, null);
                        } else {
                            continue 2;
                        }
                        break;
                }
            }
        }
    }

    /**
     * Returns status of given invite.
     *
     * @param object $invite    Database record
     *
     * @return string  $status       Returns invite status string.
     *
     * History:
     *
     * 10/30/20 - Initial writing.
     *
     */
    public function get_invite_status($invite) {

        if (empty($invite)) {
            // Invite invalid.
            $status = get_string('status_invite_invalid', 'local_swtc');
        }
        elseif ($invite->tokenused) {
            // Invite was used already.
            $status = get_string('status_invite_used', 'local_swtc');
        } elseif ($invite->timeexpiration < time()) {
            // Invite is expired.
            $status = get_string('status_invite_expired', 'local_swtc');
        } else {
            $status = get_string('status_invite_active', 'local_swtc');
        }
        // TO DO: add status_invite_revoked and status_invite_resent status.
        return $status;
    }

    /**
     * Return all invites.
     *
     * @param N/A
     * @return array
     */
    public function get_invites() {
        global $DB;

        $invites = $DB->get_records('local_swtc_userinvitation', null, 'id DESC');

        return $invites;
    }

    /**
     * Remind the user that either they have an active invitation or they have already used their invitation.
     * @param string $status
     * @param object $invitation
	 *
	 * History:
	 *
	 * 10/30/20 - Initial writing.
	 *
     */
    public function remind_invitee($status, $invitation) {
        global $USER, $PAGE, $OUTPUT, $CFG;

        $message_params = new stdClass();
        $message = '';

        $status_active = get_string('status_invite_active', 'local_swtc');
        $status_used = get_string('status_invite_used', 'local_swtc');

        // An active invitation was found for the user. Remind them of this fact.
        if ($status == $status_active) {
            $navbar = get_string('status_invite_still_active', 'local_swtc');
            $title = get_string('invitationactive', 'local_swtc');

            $message_params->email = $invitation->email;
            $message_params->timesent = date('M j, Y g:ia', $invitation->timesent);
            $message_params->expiration = date('M j, Y g:ia', $invitation->timeexpiration);
            // $url =  new moodle_url('/auth/emailadmin/signup.php', array('token' => $invitation->token));		// 01/08/19
			$url =  new moodle_url('/auth/emailadmin/invitation.php', array('token' => $invitation->token));		// 01/08/19
            $url = $url->out(false);
            $inviteurl = "<a target=_blank href=". $url . "> " . $url . ".</a>";
            $message_params->inviteurl = $inviteurl;

            $message .= get_string('status_invite_active_message', 'local_swtc', $message_params);
            unset($message_params);
        } else {
            // A used invitation was found for the user. Remind them of this fact.
            $navbar = get_string('status_invite_already_used', 'local_swtc');
            $title = get_string('invitationused', 'local_swtc');

            $message_params->email = $invitation->email;
            $message_params->timeused = date('M j, Y g:ia', $invitation->timeused);
            $message_params->supportemail = $CFG->supportemail;

            $message .= get_string('status_invite_used_message', 'local_swtc', $message_params);
            unset($message_params);
        }

        $PAGE->navbar->add($navbar);
        $PAGE->set_title($title);
        $PAGE->set_heading($PAGE->course->fullname);
        echo $OUTPUT->header();
        // notice(get_string('invitationsent_desc1', 'local_swtc', $contactuser->email), "$CFG->wwwroot/index.php");
        notice($message, "$CFG->wwwroot/index.php");

    }

    /**
     * Figures out who used an invite.
     *
     * @param object $invite    Invitation record
     *
     * @return object           Returns an object with following values:
     *                          ['username'] - name of who used invite
     *                          ['useremail'] - email of who used invite
     *                          ['roles'] - roles the user has for course that
     *                                      they were invited
     *                          ['timeused'] - formatted string of time used
     *                          Returns false on error or if invite wasn't used.
     */
    public function who_used_invite($invite) {
        global $DB;
        $ret_val = new stdClass();

        if (empty($invite->userid) || empty($invite->tokenused) || empty($invite->timeused)) {
            return false;
        }

        // Find user.
        $user = $DB->get_record('user', array('id' => $invite->userid));
        if (empty($user)) {
            return false;
        }

        $ret_val->username = sprintf('%s %s', $user->firstname, $user->lastname);
        $ret_val->useremail = $user->email;

        // Format string when invite was used.
        $ret_val->timeused = date('M j, Y g:ia', $invite->timeused);

        return $ret_val;
    }

    /**
     * A user clicked on the invitation hyperlink, but a non-active status has been encountered for the token.
     *      Format message to display to user and exit.
     *
     * @param string $status    Status of invite (possibilities are : 'Expired', 'Used', or 'Invalid')
     * @param object $invite    Database record
     *
     * @return N/A
     */
    public function decline_invitation_from_user($status, $invite) {
        global $CFG, $DB, $PAGE, $OUTPUT;

        $message_params = new stdClass();
        $message = '';

        $status_invalid = get_string('status_invite_invalid', 'local_swtc');
        $status_used = get_string('status_invite_used', 'local_swtc');
        $status_expired = get_string('status_invite_expired', 'local_swtc');

        switch ($status) {
            /// A used invitation was found for the user. Remind them of this fact.
            case $status_used:
                $navbar = get_string('status_invite_already_used', 'local_swtc');
                $title = get_string('invitationused', 'local_swtc');

                $message_params->email = $invite->email;
                $message_params->timeused = date('M j, Y g:ia', $invite->timeused);
                $message_params->supportemail = $CFG->supportemail;

                $message .= get_string('status_invite_used_message', 'local_swtc', $message_params);
                unset($message_params);
                break;

            // User had an invitation, but it has expired.
            case $status_expired:
                $navbar = get_string('status_invite_expired', 'local_swtc');
                $title = get_string('invitationexpired', 'local_swtc');

                $message_params->email = $invite->email;
                $message_params->timesent = date('M j, Y g:ia', $invite->timesent);
                $message_params->expiration = date('M j, Y g:ia', $invite->timeexpiration);
                $message_params->supportemail = $CFG->supportemail;

                $message .= get_string('status_invite_expired_message', 'local_swtc', $message_params);
                unset($message_params);
                break;

            // Invalid invitation (token).
            case $status_invalid:
                $navbar = get_string('status_invite_invalid', 'local_swtc');
                $title = get_string('invitationinvalid', 'local_swtc');

                $message_params->supportemail = $CFG->supportemail;

                $message .= get_string('status_invite_invalid_message', 'local_swtc', $message_params);
                unset($message_params);
                break;
        }

        $PAGE->set_url('/auth/emailadmin/signup.php');
        $PAGE->set_context(context_system::instance());
        $PAGE->navbar->add($navbar);
        $PAGE->set_title($title);
        $PAGE->set_heading($PAGE->course->fullname);
        echo $OUTPUT->header();
        notice($message, "$CFG->wwwroot/index.php");
    }

    /**
     * Extend the invitation by adding the global setting to the current $invite->timeexpiration. Used in invitehistory.php.
     *
     * @param object $invite    Invitation record
     *
     * @return object           Returns an object with timeexpiration updated.
     */
    public function set_invite_extended($invite) {
        global $DB;

        // Get the global invitation expiration time.
        $global_expirationtime = get_config('local_swtc', 'inviteexpiration');

        $newtimeexpiration = $invite->timeexpiration + $global_expirationtime;

        $DB->set_field('local_swtc_userinvitation', 'timeexpiration', $newtimeexpiration, array('id' => $invite->id) );

        return;
    }

    /**
     * Immediately expire the invitation by setting the expiration time to yesterday. Used in invitehistory.php.
     *
     * @param object $invite    Invitation record
     *
     * @return object           Returns an object with timeexpiration updated.
     */
    public function set_invite_expired($invite) {
        global $DB;

        $newtimeexpiration = time()-1;

        $DB->set_field('local_swtc_userinvitation', 'timeexpiration', $newtimeexpiration, array('id' => $invite->id) );

        return;
    }

    /**
     * Set the invitation as used by setting tokenused to true and setting timeused to the current time.
     *      Used in /emailadmin/confirm.php.
     *
     * @param object $invite    Invitation record
     * @param int $userid    Userid of user
     *
     * @return object           Returns an object with tokenused and timeused updated.
     */
    public function set_invite_used($invite, $userid) {
        global $DB;

        $invite->userid = $userid;
        $invite->tokenused = true;
        $invite->timeused = time();
        $DB->update_record('local_swtc_userinvitation', $invite);

        return;
    }

	/**
     * Set all the values required to reset an invite. Used in /local/swtc/lib/invitehistory.php.
     *
     * @param object $invite    Invitation record
     *
     * @return object           Returns the existing record with new fields set.
	 *
	 * History:
	 *
	 * 01/03/19 - New function.
	 *
     */
    public function set_invite_reset($invite) {
        global $DB;

        // Set time.
        $timesent = time();
        $invite->timesent = $timesent;
        $invite->timeexpiration = $timesent + get_config('local_swtc', 'inviteexpiration');
		$DB->update_record('local_swtc_userinvitation', $invite);

        return;
    }

}

/**
 * Reports the approximate distance in time between two times given in seconds
 * or in a valid ISO string like.
 *
 * For example, if the distance is 47 minutes, it'll return
 * "about 1 hour". See the source for the complete wording list.
 *
 *  Integers are interpreted as seconds. So,
 * <tt>$date_helper->distance_of_time_in_words(50)</tt> returns "less than a minute".
 *
 * Set <tt>include_seconds</tt> to true if you want more detailed approximations if distance < 1 minute
 *
 * Code borrowed/inspired from:
 * http://www.8tiny.com/source/akelos/lib/AkActionView/helpers/date_helper.php.source.txt
 *
 * Which was in term inspired by Ruby on Rails' similarly called function.
 *
 * @param int $from_time
 * @param int $to_time
 * @param boolean $include_seconds
 * @return string
 */
function distance_of_time_in_words($from_time, $to_time = 0, $include_seconds = false) {
    $from_time = is_numeric($from_time) ? $from_time : strtotime($from_time);
    $to_time = is_numeric($to_time) ? $to_time : strtotime($to_time);
    $distance_in_minutes = round((abs($to_time - $from_time)) / 60);
    $distance_in_seconds = round(abs($to_time - $from_time));

    if ($distance_in_minutes <= 1) {
        if ($include_seconds) {
            if ($distance_in_seconds < 5) {
                return get_string('less_than_x_seconds', 'local_swtc', 5);
            } else if ($distance_in_seconds < 10) {
                return get_string('less_than_x_seconds', 'local_swtc', 10);
            } else if ($distance_in_seconds < 20) {
                return get_string('less_than_x_seconds', 'local_swtc', 20);
            } else if ($distance_in_seconds < 40) {
                return get_string('half_minute', 'local_swtc');
            } else if ($distance_in_seconds < 60) {
                return get_string('less_minute', 'local_swtc');
            } else {
                return get_string('a_minute', 'local_swtc');
            }
        }
        return ($distance_in_minutes == 0) ? get_string('less_minute', 'local_swtc') : get_string('a_minute', 'local_swtc');
    } else if ($distance_in_minutes <= 45) {
        return get_string('x_minutes', 'local_swtc', $distance_in_minutes);
    } else if ($distance_in_minutes < 90) {
        return get_string('about_hour', 'local_swtc');
    } else if ($distance_in_minutes < 1440) {
        return get_string('about_x_hours', 'local_swtc', round($distance_in_minutes / 60));
    } else if ($distance_in_minutes < 2880) {
        return get_string('a_day', 'local_swtc');
    } else {
        return get_string('x_days', 'local_swtc', round($distance_in_minutes / 1440));
    }
}

/**
 * Setups the object used in the notice strings for when a user is accepting
 * a site invitation.
 *
 * @param object $invitation
 * @return object
 */
function prepare_notice_object($invitation) {
    global $CFG, $course, $DB;

    $noticeobject = new stdClass();
    $noticeobject->email = $invitation->email;
    $noticeobject->coursefullname = $course->fullname;
    $noticeobject->supportemail = $CFG->supportemail;

    // Get role name for use in acceptance message.
    $role = $DB->get_record('role', array('id' => $invitation->roleid));
    $noticeobject->rolename = $role->name;
    $noticeobject->roledescription = strip_tags($role->description);

    return $noticeobject;
}

/**
 * Prints out tabs and highlights the appropiate current tab.
 *
 * @param string $active_tab  Either 'invite' or 'history'
 */
function print_page_tabs($active_tab) {
    global $CFG, $COURSE;

    $tabs[] = new tabobject('history', new moodle_url('/local/swtc/lib/invitehistory.php', array('courseid' => $COURSE->id)),
                            get_string('swtcinvitehistory', 'local_swtc'));
    // $tabs[] = new tabobject('invite',
    //                 new moodle_url('/enrol/invitation/invitation.php',
    //                         array('courseid' => $COURSE->id)),
    //                get_string('inviteusers', 'local_swtc'));

    // Display tabs here.
    print_tabs(array($tabs), $active_tab);
}
