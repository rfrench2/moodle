<?php
// This file is part of the UCLA Site Invitation Plugin for Moodle - http://moodle.org/
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
 * Viewing invitation history script.
 *
 * @package    local_swtc
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 03/11/21 - Initial writing.
 *
 **/

require_once('../../../config.php');
require_once($CFG->dirroot. '/auth/emailadmin/locallib.php');
require_once($CFG->libdir . '/tablelib.php');

global $USER, $SESSION;

// Lenovo ********************************************************************************.
// Include SWTC LMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot . '/local/swtc/lib/swtc_userlib.php');

// Lenovo ********************************************************************************.
// SWTC swtc_user and debug variables.
$swtc_user = swtc_get_user([
    'userid' => $USER->id,
    'username' => $USER->username]);
$debug = swtc_get_debug();
// Lenovo ********************************************************************************.

if (isset($debug)) {
    $messages[] = "In /local/swtc/lib/invitehistory.php ===1.enter===";
    debug_logmessage($messages, 'both');
    unset($messages);
}

require_login();
$inviteid = optional_param('inviteid', 0, PARAM_INT);
$actionid = optional_param('actionid', 0, PARAM_INT);

// Lenovo ********************************************************************************.
// @01 - 06/05/20 - In /local/swtc/lib/invitehistory.php, restricted list shown to 20 and added paging.
// Lenovo ********************************************************************************.
$page = optional_param('page', 0, PARAM_INT);       // @01
$perpage = optional_param('perpage', 20, PARAM_INT);        // @01

// Set up page.
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
// Lenovo ********************************************************************************.
// @01 - 06/05/20 - In /local/swtc/lib/invitehistory.php, restricted list shown to 20 and added paging.
// $PAGE->set_url(new moodle_url('/local/swtc/lib/invitehistory.php'));       // @01
// Lenovo ********************************************************************************.
$url = new moodle_url('/local/swtc/lib/invitehistory.php', array('perpage' => $perpage, 'page' => $page));      // @01
$PAGE->set_url($url);      // @01
$PAGE->set_pagelayout('admin');
$pagetitle = get_string('invitehistory', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);
// $PAGE->navbar->add($pagetitle);

// OUTPUT form.
echo $OUTPUT->header();

// Print out a heading.
echo $OUTPUT->heading($pagetitle, 2, 'headingblock');

// OUTPUT page tabs.
print_page_tabs('history');

$invitationmanager = new invitation_manager();

// Get invites and display them.
$invites = $invitationmanager->get_invites();

if (isset($debug)) {
    $messages[] = "About to print invites.";
    $messages[] = print_r($invites, true);
    $messages[] = "Finished printing invites.";
    debug_logmessage($messages, 'detailed');
    unset($messages);
}

if (empty($invites)) {
    echo $OUTPUT->notification(get_string('noinvitehistory', 'local_swtc'), 'notifymessage');
} else {

    // Update invitation if the user decided to revoke/extend/resend an invite.
    if ($inviteid && $actionid) {
        if (!$curr_invite = $invites[$inviteid]) {
            print_error('invalidinviteid');
        }
        if ($actionid == invitation_manager::INVITE_EXPIRE) {
            // Set the invite to be expired.
            // $DB->set_field('local_swtc_userinvitation', 'timeexpiration', time()-1, array('id' => $curr_invite->id) );
            $invitationmanager->set_invite_expired($curr_invite);

            echo $OUTPUT->notification(get_string('expire_invite_sucess', 'local_swtc'), 'notifysuccess');

        } elseif ($actionid == invitation_manager::INVITE_EXTEND) {
            // Set the invite to be extended.
            $invitationmanager->set_invite_extended($curr_invite);

            echo $OUTPUT->notification(get_string('extend_invite_sucess', 'local_swtc'), 'notifysuccess');

        } elseif ($actionid == invitation_manager::INVITE_RESET) {
			// Set the invite to be like new.
            $invitationmanager->set_invite_reset($curr_invite);

            echo $OUTPUT->notification(get_string('extend_invite_sucess', 'local_swtc'), 'notifysuccess');

		} else {
            print_error('invalidactionid');
        }

        // Get the updated invites.
        $invites = $invitationmanager->get_invites();
    }

    // Columns to display.
    $columns = array(
            'id'           => get_string('historyid', 'local_swtc'),
            'invitee'           => get_string('historyinvitee', 'local_swtc'),
            'userid'           => get_string('historyuserid', 'local_swtc'),
            'status'            => get_string('historystatus', 'local_swtc'),
            'datesent'          => get_string('historydatesent', 'local_swtc'),
            // 'dateused'    => get_string('historydateused', 'local_swtc'),
            'dateexpiration'    => get_string('historydateexpiration', 'local_swtc'),
            'actions'           => get_string('historyactions', 'local_swtc')
    );

    $table = new flexible_table('invitehistory');
    // Lenovo ********************************************************************************.
    // @01 - 06/05/20 - In /local/swtc/lib/invitehistory.php, restricted list shown to 20 and added paging.
    // Lenovo ********************************************************************************.
    $table->pagesize($perpage, count($invites));      // @01
    $table->pageable(true);      // @01
    $table->define_columns(array_keys($columns));
    $table->define_headers(array_values($columns));
    $table->define_baseurl($PAGE->url);
    $table->set_attribute('class', 'generaltable');

    $table->setup();

    // Lenovo ********************************************************************************.
    // @01 - 06/05/20 - In /local/swtc/lib/invitehistory.php, restricted list shown to 20 and added paging.
    // Lenovo ********************************************************************************.
    $start = $page * $perpage;      // @01
    if ($start > count($invites)) {     // @01
        $page = 0;      // @01
        $start = 0;     // @01
    }       // @01

    $results = array_slice($invites, $start, $perpage, true);       // @01

    foreach ($results as $invite) {     // @01
        /* Build display row:
         * [0] - id
         * [1] - invitee
         * [2] - userid
         * [3] - status
         * [4] - dates sent
         * [5] - used date      // Lenovo - 08/10/18 Skipping for now (Status will have user information in there if invitation was accepted).
         * [5] - expiration date
         * [6] - actions
         */

         // Display id.
        $row[0] = $invite->id;

        // Display invitee.
        $row[1] = $invite->email;

        // Display userid (if status is used).
        $row[2] = $invite->userid;

        // What is the status of the invite?
        $status = $invitationmanager->get_invite_status($invite);
        $row[3] = $status;

        // If status was used, figure out who used the invite.
        $result = $invitationmanager->who_used_invite($invite);
        if (!empty($result)) {
            $row[3] .= get_string('used_by', 'local_swtc', $result);
        }

        // When was the invite sent?
        $row[4] = date('M j, Y g:ia', $invite->timesent);

        // If status is used, then state when it was used.
        //  if ($status == get_string('status_invite_used', 'local_swtc')) {
        //      $row[5] = date('M j, Y g:ia', $invite->timeused);
        //  } else {
        //      $row[5] = '';
        //  }

        // When does the invite expire?
        $row[5] = date('M j, Y g:ia', $invite->timeexpiration);

        // If status is active, then state how many days/minutes left.
        if ($status == get_string('status_invite_active', 'local_swtc')) {
            $expires_text = sprintf('%s %s',
                    get_string('historyexpires_in', 'local_swtc'),
                    distance_of_time_in_words(time(), $invite->timeexpiration, true));
            $row[5] .= ' ' . html_writer::tag('span', '(' . $expires_text . ')', array('expires-text'));
        }

        // Are there any actions user can do?
        $row[6] = '';
        $url = new moodle_url('/local/swtc/lib/invitehistory.php', array('inviteid' => $invite->id));
        // Same if statement as above, separated for clarity.
        if ($status == get_string('status_invite_active', 'local_swtc')) {
            // Create link to revoke an invite.
            $url->param('actionid', invitation_manager::INVITE_EXPIRE);
            $row[6] .= html_writer::link($url, get_string('action_expire_invite', 'local_swtc'));
            $row[6] .= html_writer::start_tag('br');
            // Create link to extend an invite.
            $url->param('actionid', invitation_manager::INVITE_EXTEND);
            $row[6] .= html_writer::link($url, get_string('action_extend_invite', 'local_swtc'));
        } elseif ($status == get_string('status_invite_expired', 'local_swtc')) {
			// Create link to reset an invite just like new.
            $url->param('actionid', invitation_manager::INVITE_RESET);
            $row[6] .= html_writer::link($url, get_string('action_reset_invite', 'local_swtc'));
		}

        $table->add_data($row);
    }

    $table->finish_output();
}

echo $OUTPUT->footer();
