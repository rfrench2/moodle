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
 * This file contains the cohort enrolment plugin.
 *
 * Lenovo customized code for Moodle cohort enrolment plugin. Remember to add the following at the top of any module that requires these functions:
 *      use \local_swtc\traits\lenovo_cohort_enrol;
 * And put the following within the class that is being overridden:
 *      use lenovo_cohort_enrol;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_cohort_enrol.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 06/11/18 - Adding ability to send "welcome" email when user is enrolled in a course via cohort sync.
 * 07/10/18 - Return immediately to skip the sending of the "welcome" email until we are ready to go "live".
 * 12/04/18 - Adding check of swtcbatchemail to determine if email should be sent or not.
 * 10/29/19 - Initial writing; moved majority of customized code from enrol/cohort/lib.php to functions defined here.
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

use stdClass;
use context_course;
use core_user;
// use context_module;
// use moodle_url;
// use completion_info;

/**
 * Cohort enrolment plugin implementation.
 * @author Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait lenovo_cohort_enrol {

    /**
     * @param stdClass $instance
     * @param int $userid
     * @param int $roleid optional role id
     * @param int $timestart 0 means unknown
     * @param int $timeend 0 means forever
     * @param int $status default to ENROL_USER_ACTIVE for new enrolments, no change by default in updates
     * @param bool $recovergrades restore grade history
     * @return void
     *
     * Lenovo history:
     *
     * 06/11/18 - Adding ability to send "welcome" email when user is enrolled in a course via cohort sync.
     * 07/10/18 - Return immediately to skip the sending of the "welcome" email until we are ready to go "live".
     * 12/04/18 - Adding check of swtcbatchemail to determine if email should be sent or not.
     * 04/06/19 - Added this header.
     *
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = NULL, $timestart = 0, $timeend = 0, $status = NULL, $recovergrades = null){
        global $DB, $CFG;       // Lenovo

        // 12/04/18 - To make sure send is NOT set at the start.
        $send = 0;

        parent::enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades = null);

        // Lenovo ********************************************************************************
        // 07/10/18 - Return immediately to skip the sending of the "welcome" email until we are ready to go "live".
        // 12/04/18 - Adding check of swtcbatchemail to determine if email should be sent or not.
        // Lenovo ********************************************************************************
        // Get setting of swtcbatchemail (will either be "0" for disabled or don't send email or "1" for enabled or send email).
        $send = get_config('local_swtc', 'swtcbatchemail');

        if ($send) {
            $user = $DB->get_record('user', array('id' => $userid));
            // Send welcome message.
            if ($instance->customint4) {
                $this->email_welcome_message($instance, $user);
            }
        } else {
            return;     // Lenovo - remove when ready to go "live".
        }
    }

    /**
     * Send welcome email to specified user.
     *
     * @param stdClass $instance
     * @param stdClass $user user record
     * @return void
     *
     * Lenovo history:
     *
     * 06/11/18 - Adding ability to send "welcome" email when user is enrolled in a course via cohort sync.
     * 07/10/18 - Return immediately to skip the sending of the "welcome" email until we are ready to go "live".
     * 12/04/18 - Adding check of swtcbatchemail to determine if email should be sent or not.
     * 04/06/19 - Added this header.
     *
     */
    protected function email_welcome_message($instance, $user) {
        global $CFG, $DB;

        $course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
        $context = context_course::instance($course->id);

        $a = new stdClass();
        $a->coursename = format_string($course->fullname, true, array('context'=>$context));
        $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id&course=$course->id";

        if (trim($instance->customtext1) !== '') {
            $message = $instance->customtext1;
            $key = array('{$a->coursename}', '{$a->profileurl}', '{$a->fullname}', '{$a->email}');
            $value = array($a->coursename, $a->profileurl, fullname($user), $user->email);
            $message = str_replace($key, $value, $message);
            if (strpos($message, '<') === false) {
                // Plain text only.
                $messagetext = $message;
                $messagehtml = text_to_html($messagetext, null, false, true);
            } else {
                // This is most probably the tag/newline soup known as FORMAT_MOODLE.
                $messagehtml = format_text($message, FORMAT_MOODLE, array('context'=>$context, 'para'=>false, 'newlines'=>true, 'filter'=>true));
                $messagetext = html_to_text($messagehtml);
            }
        } else {
            $messagetext = get_string('welcometocoursetext', 'enrol_self', $a);
            $messagehtml = text_to_html($messagetext, null, false, true);
        }

        $subject = get_string('welcometocourse', 'enrol_self', format_string($course->fullname, true, array('context'=>$context)));

        // $sendoption = $instance->customint4;
        $sendoption = ENROL_SEND_EMAIL_FROM_NOREPLY;
        $contact = $this->get_welcome_email_contact($sendoption, $context);

        // Directly emailing welcome message rather than using messaging.
        email_to_user($user, $contact, $subject, $messagetext, $messagehtml);
    }

    /**
     * Get the "from" contact which the email will be sent from.
     *
     * @param int $sendoption send email from constant ENROL_SEND_EMAIL_FROM_*
     * @param $context context where the user will be fetched
     * @return mixed|stdClass the contact user object.
     *
     * Lenovo history:
     *
     * 06/11/18 - Adding ability to send "welcome" email when user is enrolled in a course via cohort sync.
     * 07/10/18 - Return immediately to skip the sending of the "welcome" email until we are ready to go "live".
     * 12/04/18 - Adding check of swtcbatchemail to determine if email should be sent or not.
     * 04/06/19 - Added this header.
     *
     */
    public function get_welcome_email_contact($sendoption, $context) {
        global $CFG;

        $contact = null;
        // Send as the first user assigned as the course contact.
        if ($sendoption == ENROL_SEND_EMAIL_FROM_COURSE_CONTACT) {
            $rusers = array();
            if (!empty($CFG->coursecontact)) {
                $croles = explode(',', $CFG->coursecontact);
                list($sort, $sortparams) = users_order_by_sql('u');
                // We only use the first user.
                $i = 0;
                do {
                    $allnames = get_all_user_name_fields(true, 'u');
                    $rusers = get_role_users($croles[$i], $context, true, 'u.id,  u.confirmed, u.username, '. $allnames . ',
                    u.email, r.sortorder, ra.id', 'r.sortorder, ra.id ASC, ' . $sort, null, '', '', '', '', $sortparams);
                    $i++;
                } while (empty($rusers) && !empty($croles[$i]));
            }
            if ($rusers) {
                $contact = array_values($rusers)[0];
            }
        } else if ($sendoption == ENROL_SEND_EMAIL_FROM_KEY_HOLDER) {
            // Send as the first user with enrol/self:holdkey capability assigned in the course.
            list($sort) = users_order_by_sql('u');
            $keyholders = get_users_by_capability($context, 'enrol/self:holdkey', 'u.*', $sort);
            if (!empty($keyholders)) {
                $contact = array_values($keyholders)[0];
            }
        }

        // If send welcome email option is set to no reply or if none of the previous options have
        // returned a contact send welcome message as noreplyuser.
        if ($sendoption == ENROL_SEND_EMAIL_FROM_NOREPLY || empty($contact)) {
            $contact = core_user::get_noreply_user();
        }

        return $contact;
    }

}
