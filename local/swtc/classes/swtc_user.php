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
 * @subpackage swtc/classes/swtc_user.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 10/16/20 - Changed to swtc class.
 *
 **/

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use cache;
use core_course_category;
use context_coursecat;
use core_date;
use DateTime;
use core_user;

use local_swtc\swtc_debug;
use local_swtc\swtc_counter;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');

/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT!
 *          DO NOT call this directly. Use $swtc_set_user from /lib/swtc_userlib.php.
 *
 * @param N/A
 *
 * @return $SESSION->SWTC->USER.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 */
class swtc_user
{
    /**
     * Store the user's id.
     * @var integer
     */
    private $userid;

    /**
     * Store the user's username.
     * @var string
     */
    private $username;

    /**
     * Store the user's accesstype.
     * @var string
     */
    private $accesstype;

    /**
     * The user's main portfolio they have access to.
     * @var string
     */
    private $portfolio;

    /**
     * The user's role shortname.
     * @var string
     */
    private $roleshortname;

    /**
     * The user's role id.
     * @var integer
     */
    private $roleid;

    /**
     * The categories the user has access to.
     * @var array
     */
    private $categoryids;

    /**
     * The time of this action.
     * @var DateTime
     */
    private $timestamp;

    /**
     * If an admin is performing an action on behalf of another user,
     * this is the related user's id.
     * @var integer
     */
    private $relateduser;

    /**
     * The cohort names the user is a member of (if any).
     * @var array
     */
    private $cohortnames;

    /**
     * The preg_match string that should be used to
     * find all the groups the user is a member of.
     * @var string
     */
    private $groupname;

    /**
     * The user's GEO.
     * @var string
     */
    private $geoname;

    /**
     * The groups the user is a member of (if any).
     * @var array
     */
    private $groupnames;

    /**
     * The timezone of the user.
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * The user's accesstype 2.
     * @var string
     *
     */
    private $accesstype2;

    /**
     * Constructor is private, use /locallib/swtc_local_user() to
     * retrieve SWTC user information.
     */
    public function __construct($args=array()) {
        $this->userid = $args['userid'] ?? null;
        $this->username = $args['username'] ?? null;
        $this->accesstype = null;
        $this->portfolio = get_string('none_portfolio', 'local_swtc');
        $this->roleshortname = null;
        $this->roleid = null;
        $this->categoryids = null;
        $this->timestamp = null;
        $this->relateduser = null;
        $this->cohortnames = null;
        $this->groupname = null;
        $this->geoname = null;
        $this->groupnames = null;
        $this->timezone = null;
        $this->accesstype2 = null;

        // SWTC ********************************************************************************.
        // Copy this object to $SESSION->SWTC->USER.
        // SWTC ********************************************************************************.
        // $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
        // $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
        // print_object("In not set SWTC->USER; about to print this');        // 10/16/20 - SWTC
        // print_object($this);        // 10/16/20 - SWTC
        // print_object("About to leave swtc_user __construct; about to print SESSION->SWTC');        // 10/20/20 - SWTC
        // print_object($SESSION->SWTC).
    }

    /**
     * All Setter methods for all properties.
     *
     * Setter methods:
     * @param $value
     * @return N/A
     *
     * History:
     *
     * 03/03/21 - Initial writing.
     *
     **/
    public function set_userid($userid) {
        $this->userid = (isset($userid)) ? $userid : null;
    }

    public function set_username($username) {
        $this->username = (isset($username)) ? $username : null;
    }

    public function set_user_access_type($accesstype) {
        $this->accesstype = $accesstype;
    }

    public function set_user_access_type2($accesstype2) {
        $this->accesstype2 = $accesstype2;
    }

    public function set_timestamp() {
        $timezone = core_date::get_user_timezone_object();
        $today = new DateTime("now", $timezone);
        $this->timestamp = $today->format('H:i:s.u');
        return $this->timestamp;
    }

    public function set_timezone() {
        $this->timezone = core_date::get_user_timezone_object();
        return $this->timezone;
    }

    /**
     * Set (assign) user role.
     * @param array $eventdata The event data.
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     * 11/08/20 - Not needed anymore since using moodle/category:viewcourselist.
     * 03/04/21 - It is needed to assign user's role to each top level category they
     *                have access too.
     * 03/07/21 - Note that in some cases, for example event user_updated (when changing the default editor);
     *             a relateduser IS set. However, it's value is the same as the user. Therefore adding a check.
     */
    public function set_user_role($eventdata) {
        global $USER, $DB, $COURSE;

        // Get the current debug information.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        // userrelated Only set IF working with a related user (i.e. get_relateduser is called).
        $userrelated = null;
        // Hold return values from /local/swtc/classes/swtc_user.php get_user_access.
        $tmpuser = new swtc_user();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "********************************************************************************.";
            $messages[] = "Entering /local/swtc/classes/swtc_user.php.===set_user_role.enter.";
            $messages[] = "********************************************************************************.";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($this, true);
            $messages[] = "swtc_user array ends.";
            $messages[] = "eventname follows :";
            $messages[] = print_r($eventdata->eventname, true);
            $messages[] = "eventname ends.";
            $messages[] = "********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ***********************************************************************************.
        // Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
        // SWTC ***********************************************************************************.
        if (empty($USER->id)) {
            if (isset($debug)) {
                $debug->logmessage("User has not logged on yet; set_user_role.exit===2.1===.", 'logfile');
            }
            return;
        }

        // Load the event name (if it's needed later).
        $eventname = $eventdata->eventname;

        if (isset($debug)) {
            $messages[] = "eventname is :<strong>$eventname</strong>.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Trick to refresh the users roles without logging out and logging in again.
        // If the user is already logged OUT and their role changes, they get an updated view next
        // time they login. However, if the user is already logged IN and their role changes, we must
        // reload a web page for the new role assignments to take affect. This means capturing the
        // course_viewed message, calling purge_all_caches, and immediately returning.
        //
        // Will tell user to click on the home page link to refresh their access, but viewing any
        // course will work. In fact, just clicking refresh in the web browser should work (have
        // not testing will all browsers in all circumstances).
        // SWTC ********************************************************************************.
        if ($eventname == '\core\event\course_viewed') {
            if (isset($debug)) {
                if ($eventdata->courseid == 1) {
                    $debug->logmessage("User is viewing the front page (courseid = 1). Continuing...", 'logfile');
                    purge_all_caches();
                } else {
                    $debug->logmessage("User is viewing a course. About to return.", 'logfile');
                    $debug->logmessage("Leaving set_user_role.exit===11===.", 'logfile');
                    purge_all_caches();
                    return;
                }
            }
        }

        // SWTC ********************************************************************************.
        // Important! Properties passed via $eventdata defined in
        // https://docs.moodle.org/dev/Event_2#Information_contained_in_events
        // Note: <strong> and </strong> begins and ends bold printing.
        // Also adds CRLF to end of print statement.
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "==========1.2===========";
            $messages[] = "eventdata properties follow...";
            $messages[] = "event message :<strong>$eventdata->eventname.</strong>";
            $messages[] = "contextid is : <strong>$eventdata->contextid.</strong>";
            $messages[] = "possible contextlevel values are: CONTEXT_SYSTEM (10); CONTEXT_USER (30);
            CONTEXT_COURSECAT (40); CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
            $messages[] = "contextlevel is :<strong>$eventdata->contextlevel.</strong>";
            $messages[] = "courseid is :<strong>$eventdata->courseid.</strong>";
            $messages[] = "contextinstanceid is :<strong>$eventdata->contextinstanceid.</strong>";
            $messages[] = "userid is :<strong>$eventdata->userid</strong> (either userid, 0 when not logged in, or -1 when other).";
            $messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid,
            admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "all eventdata properties follow :";
            $messages[] = print_r($eventdata, true);
            $messages[] = "all eventdata properties end.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Check to see if the administrator is working on behalf of a user, or the actual user is doing something.
        // Important! If an administrator is working on behalf of a user (for example, updating the user's profile
        // or creating a new user), $eventdata->relateduserid will be the userid of the user and the userid the rest
        // of the plug-in should work with. If a "regular" user is doing something, $eventdata->relateduserid will
        // be empty.
        //
        // Sets variables:
        // $this->userid    The userid of the "actual" user (not the administrator).
        // $this->username  The username of the "actual" user (not the administrator).
        // $this->accesstype  The most important variable; triggers all the rest that follows.
        // $this->timestamp
        // $this->accesstype2
        // SWTC ********************************************************************************.
        if (!empty($eventdata->relateduserid) && ($eventdata->objectid !== $eventdata->relateduserid)) {
            if (isset($debug)) {
                switch ($eventname) {

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_loggedinas
                    // SWTC ********************************************************************************.
                    case '\core\event\user_loggedinas':
                        $debug->logmessage("Admin has logged on as user (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_updated
                    // SWTC ********************************************************************************.
                    case '\core\event\user_updated':
                        $debug->logmessage("Admin has updated a user (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_created
                    // SWTC ********************************************************************************.
                    case '\core\event\user_created':
                        $debug->logmessage("Admin has created a user (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\role_assigned
                    // SWTC ********************************************************************************.
                    case '\core\event\role_assigned':
                        $debug->logmessage("Admin has triggered a role assignment on behalf of a user
                        (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_deleted
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_deleted':
                        $debug->logmessage("Admin has triggered an unenrollment from a course on behalf of a user
                        (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_updated
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_updated':
                        $debug->logmessage("Admin has triggered an updated enrollment in a course on behalf of a user
                        (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_created
                    //
                    // If user_enrolment_created was done by a cohort, eventdata will look like the following (Notes are embedded):
                    //
                    // core\event\user_enrolment_created Object
                    // (
                    // [data:protected] => Array
                    // (
                    // [eventname] => \core\event\user_enrolment_created
                    // [component] => core
                    // [action] => created
                    // [target] => user_enrolment
                    // [objecttable] => user_enrolments
                    // [objectid] => 139952
                    // [crud] => c
                    // [edulevel] => 0
                    // [contextid] => 3819
                    // [contextlevel] => 50                (CONTEXT_COURSE)
                    // [contextinstanceid] => 159        (courseid 159 = ES11611)
                    // [userid] => 4                            (4 = rfrench)
                    // [courseid] => 159                    (courseid 159 = ES11611)
                    // [relateduserid] => 12983        (userid of user dropped in cohort)
                    // [anonymous] => 0
                    // [other] => Array
                    // (
                    // [enrol] => cohort
                    // )
                    //
                    // [timecreated] => 1547760579
                    // )
                    //
                    // [logextra:protected] =>
                    // [context:protected] => context_course Object
                    // (
                    // [_id:protected] => 3819
                    // [_contextlevel:protected] => 50
                    // [_instanceid:protected] => 159            (courseid 159 = ES11611)
                    // [_path:protected] => /1/511/513/514/3819
                    // [_depth:protected] => 5
                    // )
                    //
                    // [triggered:core\event\base:private] => 1
                    // [dispatched:core\event\base:private] => 1
                    // [restored:core\event\base:private] =>
                    // [recordsnapshots:core\event\base:private] => Array
                    // (
                    // [user_enrolments] => Array
                    // (
                    // [139952] => stdClass Object
                    // (
                    // [id] => 139952
                    // [status] => 0
                    // [enrolid] => 4887
                    // [userid] => 12983
                    // [timestart] => 0
                    // [timeend] => 0
                    // [modifierid] => 4
                    // [timecreated] => 1547760579
                    // [timemodified] => 1547760579
                    // [enrol] => cohort
                    // [courseid] => 159
                    // )
                    // )
                    // )
                    // )
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_created':
                        $debug->logmessage("Admin has triggered an enrollment in a course on behalf of a user
                        (eventname is <strong>$eventname</strong>).", 'both');
                     break;

                    // SWTC ********************************************************************************.
                    // Event - all others
                    // SWTC ********************************************************************************.
                    default:
                        $debug->logmessage("Something happened. Log it. (eventname is <strong>$eventname</strong>).", 'both');
                     break;
                }
            }

            // Set the users userid and access_type.
            // 07/12/18 - Added call to get_relateduser.
            // 07/18/18 - Set $userrelated to $this->relateduser (otherwise $userrelated is NULL).
            $userrelated = ($eventdata->objectid !== $eventdata->relateduserid) ?
                $this->get_relateduser($eventdata->relateduserid) : null;
            $this->relateduser = $userrelated;

            if (isset($debug)) {
                $messages[] = "In top of set_user_role (relateduserid). Setting this->relateduser
                information of $eventdata->relateduserid. ===11===.";
                $messages[] = "get_relateduser follow:";
                $messages[] = print_r($this->relateduser, true);
                $messages[] = "get_relateduser end.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            if (isset($debug)) {
                $messages[] = "In top of local_swtc_set_user_role (relateduserid). After setting swtc_user to new values.";
                $messages[] = "swtc_user array follows :";
                $messages[] = print_r($this, true);
                $messages[] = "swtc_user array ends.";
                $debug->logmessage($messages, 'both');
                unset($messages);
            }
        }

        if (isset($debug)) {
            $messages[] = "The userid that will be used throughout this plugin is :<strong>$this->userid</strong>.";
            $messages[] = "The username that will be used throughout this plugin is :<strong>$this->username</strong>.";
            $messages[] = "The accesstype is :<strong>$this->accesstype</strong>.";
            $messages[] = "The timestamp is :<strong>$this->timestamp</strong>.";
            $messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid,
            admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // For each of the messages being captured, get the user access type, role, and category id
        // they SHOULD have access to (function below).
        //
        // The $this is returned. It is a multidimensional array that has the following format
        // (Note: roleid will be loaded later):
        // $access = array(
        // 'portfolio'=>'',
        // 'roleshortname'=>'',
        // 'roleid'=>'',
        // 'categoryid's = array(
        // )
        // );
        //
        // SWTC ********************************************************************************.
        // No need to return access (values set directly in SWTC) nor allroles (called again in swtc_load_roleids).
        // No need to send $this->accesstype as that is part of SWTC.
        // Also update timestamp.
        // SWTC ********************************************************************************.
        // list($catlist, $tmpuser) = $this->get_user_access().
        $tmpuser = $this->get_user_access();

        // SWTC ********************************************************************************.
        // Finished. Set $this to all the appropriate values.
        // And set new timestamp.
        // SWTC ********************************************************************************.
        // 07/12/18 - Added check if $userrelated is set. If so, load that access information. Otherwise, load $this.
        if (isset($userrelated)) {
            $userrelated->portfolio = $tmpuser->portfolio;
            $userrelated->roleshortname = $tmpuser->roleshortname;
            $userrelated->roleid = $tmpuser->roleid;
            $userrelated->categoryids = $tmpuser->categoryids;
            $userrelated->set_timestamp();
            $userrelated->set_timezone();
        } else {
            $this->portfolio = $tmpuser->portfolio;
            $this->roleshortname = $tmpuser->roleshortname;
            $this->roleid = $tmpuser->roleid;
            $this->categoryids = $tmpuser->categoryids;
            $this->set_timestamp();
            $this->set_timezone();
        }

        // SWTC ********************************************************************************.
        // Note: At this point the $catids array should be fully created...
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "swtc_user array follows: ";
            $messages[] = print_r($this, true);
            $messages[] = "swtc_user array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Special case check - For each course that has self-enrollment enabled, the Administrator
        // defines a default role for each self-enrollment instance. In most cases, the default role
        // is fine (students enrolling as students). However, in some cases, the user's role in course
        // must be changed (for example, student changing to instructor).
        //
        // If the user is enrolling in a course (self-enrollment):
        // Check to see if the current user is student (and not an Administrator acting on behalf of
        // the student). In other words, if an Administrator is enrolling the user, whatever role the
        // Administrator gives the user is fine). Check to see if they are enrolled as the correct
        // type of user (for example, for a course in the IBM portfolio, the self-enrollment default role
        // is 'IBM-student'). However, a user with a role of 'Lenovo-instructor' can enroll in the course.
        // So, the 'Lenovo-instructor' role must be assigned and the 'IBM-student' role must be unassigned.
        // In other words, if the user's role in the course is different than what is in their user profile
        // ("accesstype'), assign correct role / unassign incorrect role.
        //
        // Important - Any roles unassigned or assigned in the 'main' course flow to any metacourses linked
        // to the 'main' course. In other words, any roles that need to be unassigned or assigned must be
        // done in the 'main' course only (not in the metacourse) - no need to do anything in any metacourses.
        //
        // Notes:
        // Remember to check if the current user is the student (and not an Administrator acting on behalf
        // of the student).
        // Remember that once the student self-enrolls in a course, if a metalink exists to that course, an automatic
        // enrollment is processed for each metacourse (in other words, the student does NOT enroll in any metacourses).
        // Because two classes are enrolled, two '\core\event\role_assigned' messages are generated - one for each course.
        // Check to see which course the '\core\event\role_assigned' message is associated with. If it is for the 'main'
        // course, continue. If it is for any metacourses, ignore them.
        // Remember that the user cannot enroll themself in the 'Shared resources (Master)' course (because enrollment is
        // via a course metalink). Therefore, after checking, if the user is not enrolled in course, we must return without
        // doing anything and the user will get an error.***not sure of this***
        //
        // SWTC ********************************************************************************.
        if ($eventname == '\core\event\role_assigned') {
            $sharedrescourseid = $DB->get_field('course', 'id', array('shortname' => get_string('sharedresources_coursename',
            'local_swtc')));
            $lensharedsimulatorscourseid = $DB->get_field('course', 'id',
            array('shortname' => get_string('lensharedsimulators_shortname', 'local_swtc')));

            // Special case - If $courseid is 0, return.
            if ($eventdata->courseid == 0) {
                return;
            }

            if (isset($debug)) {
                $messages[] = "=====================";
                $messages[] = "In role_assigned message ===11.5===.";
                $messages[] = "About to print all of eventdata ===11.5===.";
                $messages[] = print_r($eventdata, true);
                $messages[] = "Finished printing eventdata ===11.5===.";
                $messages[] = "About to print COURSE->category ===11.5===.";
                $messages[] = print_r($COURSE->category, true);
                $messages[] = "Finished printing COURSE->category ===11.5===.";
                $messages[] = "eventname is :<strong>$eventname</strong>.";
                $messages[] = "eventdata->courseid is :$eventdata->courseid.";
                $messages[] = "eventdata->userid is :$eventdata->userid.";
                $messages[] = "eventdata->relateduserid is :$eventdata->relateduserid.";
                // SWTC *******************************************************************************.
                // Metacourse could be either of two courses.
                // SWTC *******************************************************************************.
                $messages[] = "metacourse1 courseid :$sharedrescourseid.";
                $messages[] = "metacourse2 courseid :$lensharedsimulatorscourseid.";
                $debug->logmessage($messages, 'logfile');
                unset($messages);
            }

            // Get the courseid that the user has enrolled in.
            $courseid = $eventdata->courseid;

            // SWTC ********************************************************************************.
            // If the student self-enrolled in a course that has a metacourse, two '\core\event\role_assigned'
            // messages are generated. Is $eventdata->courseid the course or the metacourse? If it's the metacourse,
            // return. If not, continue.
            // SWTC ********************************************************************************.
            if (($courseid == $sharedrescourseid) || ($courseid == $lensharedsimulatorscourseid)) {
                if (isset($debug)) {
                    $debug->logmessage("eventdata->courseid is :<strong>$courseid</strong>;
                    the metacourse id is either :", 'logfile');
                    $debug->logmessage("<strong>$sharedrescourseid</strong>.", 'logfile');
                    $debug->logmessage("<strong>$lensharedsimulatorscourseid</strong>.", 'logfile');
                    $debug->logmessage("Returning.", 'logfile');
                }
                return;
            }

            // Create a context to the course.
            $context = context_course::instance($courseid, MUST_EXIST);

            if (isset($debug)) {
                $debug->logmessage("User has enrolled in course (courseid :<strong>$courseid</strong>).", 'logfile');
                $debug->logmessage("About to print courseid $courseid context.", 'logfile');
                $debug->logmessage(print_r($context, true), 'logfile');
            }

            // Get the role assignments of the user in the course (get_records on course or metacourse depending on is_meta value).
            $ras = $DB->get_records('role_assignments', array('userid' => $this->userid, 'contextid' => $context->id));

            // SWTC ********************************************************************************.
            // The default role for a student self-enrolling in a course depends on what portfolio the course is in.
            // In any event, delete whatever it is and add what the user's 'real' role should be.
            //
            // Note: $access is set for each user in code above.
            // And update timestamp.
            // SWTC ********************************************************************************.
            // SWTC ********************************************************************************.
            // Get the top-level category for this catid.
            // SWTC ********************************************************************************.
            $topcatid = swtc_toplevel_category($COURSE->category);
            // Look for key in catlist array.
            $key = array_search($topcatid, array_column($catlist, 'catid'));
            $topcat = $catlist[$key];

            // SWTC ********************************************************************************.
            // Added check if userrelated is set. If so, use that user information to determine access;
            // to make sure all changes are made, changing swtc_user to tempuser (since no information
            // is saved in this section); remember to unset SESSION->SWTC->USER->relateduser at the end.
            // SWTC ********************************************************************************.
            if (isset($userrelated)) {
                $tempuser = clone $userrelated;
            } else {
                $tempuser = clone $this;
            }

            // SWTC ********************************************************************************.
            // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
            // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
            // SWTC ********************************************************************************.
            $tempuser->change_user_access($topcat);
            if (isset($debug)) {
                $debug->logmessage("Correct roleid is :<strong>$tempuser->roleid</strong>.", 'logfile');
                $debug->logmessage("Printing ras before deleting incorrect role.", 'logfile');
                $debug->logmessage(print_r($ras, true), 'logfile');
                $debug->logmessage("Finished printing ras before deleting incorrect role.", 'logfile');
            }

            // SWTC ********************************************************************************.
            // Loop through each role assignment record the user has in the course and, if $ra->roleid
            // is different than $tempuser->roleid, delete the enrollment record.
            // SWTC ********************************************************************************.
            foreach ($ras as $ra) {
                // If the roleid assigned is different than what it should be, delete it.
                if (($ra->userid == $tempuser->userid) && ($ra->roleid != $tempuser->roleid)) {
                    $DB->delete_records('role_assignments', array('id' => $ra->id));
                }
            }

            if (isset($debug)) {
                $tmp = $DB->get_records('role_assignments', array('userid' => $tempuser->userid, 'contextid' => $context->id));
                $debug->logmessage("Printing ras after deleting incorrect role.", 'logfile');
                $debug->logmessage(print_r($tmp, true), 'logfile');
                $debug->logmessage("Finished printing ras after deleting incorrect role.", 'logfile');
            }

            // SWTC ********************************************************************************.
            // Finally, assign the user to the correct role in the course.
            // SWTC ********************************************************************************.
            if (!$ra = role_assign($tempuser->roleid, $tempuser->userid, $context->id)) {
                if (isset($debug)) {
                    $newshortname = $tempuser->roleshortname;
                    $debug->logmessage("Error assigning <strong>$tempuser->roleid ($newshortname)</strong>
                    in course <strong>$courseid</strong>. Returning.", 'logfile');
                    return;
                }
            }

            if (isset($debug)) {
                $tmp = $DB->get_records('role_assignments', array('userid' => $tempuser->userid, 'contextid' => $context->id));
                $debug->logmessage("Printing ras after assigning new role.", 'logfile');
                $debug->logmessage(print_r($tmp, true), 'logfile');
            }

            // Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
            $context->mark_dirty();
            purge_all_caches();

            if (isset($debug)) {
                $debug->logmessage("Leaving role_assigned message ===11.5===.", 'logfile');
            }

            return;
        }

        // SWTC ********************************************************************************.
        // At this point, we've determined all the roles defined in the system (above), all the
        // categories in the system (above), and (most importantly) the role the user 'should '
        // have in the category.
        //
        // Part 4 of 4 - At this point, we've determined all the roles defined in the system,
        // all the categories in the system, and (most importantly) the role the user 'should'
        // have in the category. Nothing left to do, but check to see if the user really does
        // have that capability in that category.
        // If not, assign it to them. Search for name of capability and set the master capability
        // variable. If they have a role in a category they shouldn't, remove them from the role.
        //
        // Logic flow is the following:
        // For each of the top-level categories (all top-level categories are checked each time)
        // Should user have access? (if 'catname' == access['catname or if 'catname' == ALL)
        // If yes
        // Add the capability
        // If no
        // Remove the capability
        //
        // Remember! $catlist array format is below (defined in get_user_access):
        // Array
        // (
        // [0] => Array
        // (
        // [catid] => 14
        // [catname] => GTP Portfolio
        // [roles] => Array
        // (
        // [gtp-instructor] => 13
        // [gtp-student] => 14
        // [gtp-administrator] => 16
        // )
        // )
        //
        // Main loop ("For each of the top-level categories defined on the site...').
        //
        // Changed PremierSupport roles to only have PremierSupport-student roles outside
        // the PremierSupport portfolio (even administrators and managers). This is to
        // prevent PremierSupport admins and mgrs from having more access to a course when
        // they are enrolled in a course outside the PremierSupport portfolio. Remember to
        // skip roles returned with contextid = 1 (which is System context); added check if
        // userrelated is set. If so, use that user information to determine access; to make
        // sure all changes are made, changing swtc_user to tempuser (since no information is
        // saved in this section); remember to unset SESSION->SWTC->USER->relateduser at the end.
        // SWTC ********************************************************************************.
        if (isset($userrelated)) {
            $tempuser = clone $userrelated;
        } else {
            $tempuser = clone $this;
        }

        $catlist = $this->loadallcatsaccess();

        foreach ($catlist as $key => $catlist['catid']) {
            // Check to see if the user has any roles assigned to this top-level category. Save for several checks later...
            // Added checkparentcontexts as false (to remove System contextid).
            $context = context_coursecat::instance($catlist[$key]['catid']);
            $userroles = get_user_roles($context, $tempuser->userid, false);
            $countroles = count($userroles);

            if (isset($debug)) {
                $temp = $catlist[$key]['catname'];
                if ($countroles != 0) {
                    $messages[] = "Userid <strong>$tempuser->userid DOES</strong> have userroles in
                    <strong>$temp</strong>.";
                    $messages[] = "The number of roles userid <strong>$tempuser->userid has assigned in
                    <strong>$temp</strong> is <strong>==>$countroles<==</strong>.";
                    $messages[] = "Next is to check if they SHOULD have access.";
                    $messages[] = "About to print userroles.";
                    $messages[] = print_r($userroles, true);
                    $messages[] = "Finished printing userroles. About to print context.";
                    $messages[] = print_r($context, true);
                    $messages[] = "Finished printing context.";
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
                } else {
                    $debug->logmessage("Userid <strong>$tempuser->userid</strong> does
                    <strong>NOT</strong> have any roles in <strong>$temp</strong>.", 'both');
                }
            }

            $catid = $catlist[$key]['catid'];
            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "catid to search for is $catid.";
                if (array_key_exists($catid, $tempuser->categoryids) !== false) {
                    $messages[] = "I found cat $catid.";
                } else {
                    $messages[] = "I did NOT find cat $catid.";
                }
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            if (array_key_exists($catid, $tempuser->categoryids) !== false) {
                $temp = $catlist[$key];

                if (isset($debug)) {
                    $catname = $catlist[$key]['catname'];
                    $debug->logmessage("User <strong>$tempuser->userid SHOULD</strong>
                    have access to category <strong>$catname</strong>.", 'logfile');
                }

                // SWTC ********************************************************************************.
                // Does the current user have any roles assigned in this category? If so, check to make sure
                // it's the CORRECT role. What does CORRECT role mean? The CORRECT role would be the one that
                // the user should have been assigned (based on the 'Access type' flag).
                // Note: The only way for a user to have more than one role assigned to them in a top-level
                // category is if an administrator purposely did it (since 'Access type' is a single-select,
                // it is impossible to get more than one role from it). For example, a user was given the
                // GTP-student AND GTP-instructor role in the 'GTP Portfolio' top-level category.
                // SWTC ********************************************************************************.
                if ($countroles != 0) {

                    if (isset($debug)) {
                        $debug->logmessage("And <strong>DOES</strong>. Next is to check if it is the CORRECT access.", 'logfile');
                    }
                    // Does the current user have the CORRECT role assigned in this category?
                    // For each of the roles, if $role['id'] == $this->roleid, the user has the correct access. If they don't match,
                    // remove the user from the role.
                    foreach ($userroles as $role) {

                        // SWTC ********************************************************************************.
                        // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                        // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                        // SWTC ********************************************************************************.
                        $tempuser->change_user_access($catid);

                        if ($role->roleid != $tempuser->roleid) {
                            if (isset($debug)) {
                                $tempid = $tempuser->roleid;
                                $tempname = $tempuser->roleshortname;
                                $messages[] = "However, user <strong>$tempuser->userid</strong> has been given an incorrect role.
                                It should be <strong>$tempid ($tempname)</strong> but is
                                <strong>$role->roleid ($role->shortname)</strong>.";
                                $messages[] = "Action: will remove user from role <strong>$role->roleid</strong>;
                                will add user to role <strong>$tempid</strong>.";
                                $messages[] = "parameters to role_unassign are :role->roleid is $role->roleid;
                                tempuser->userid is $tempuser->userid; $context->id is :";
                                $messages[] = print_r($context->id, true);
                                $debug->logmessage($messages, 'both');
                                unset($messages);
                            }

                            // Unassign the user from the incorrect role...
                            role_unassign($role->roleid, $tempuser->userid, $context->id);

                            // Assign the user to the correct role...
                            role_assign($tempuser->roleid, $tempuser->userid, $context->id);

                            // Not sure what the following does, but assign_capability says to
                            // call it after (and it only works with using it)...
                            $context->mark_dirty();

                            // If the above role_unassign followed by role_assign worked,
                            // or didn't work, the user would STILL have roles in this
                            // category. So, there is not much gain in checking access
                            // again. So, just continue.
                        } else {
                            if (isset($debug)) {
                                $debug->logmessage("It is the correct access.", 'logfile');
                            }
                        }
                    }
                } else {
                    // The user SHOULD have access; the user does NOT have a role assigned in the category.
                    // Add the user to the role.
                    // SWTC ********************************************************************************.
                    // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                    // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                    // SWTC ********************************************************************************.
                    $tempuser->change_user_access($catid);

                    if (isset($debug)) {
                        $temp = $catlist[$key]['catname'];
                        $messages[] = "User <strong>$tempuser->userid</strong> does <strong>NOT</strong> have role
                        <strong>$tempuser->roleshortname</strong> in category <strong>$temp</strong>.";
                        $messages[] = "Action: will add user role to category.";
                        $messages[] = "tempuser follows:";
                        $messages[] = print_r($tempuser, true);
                        $messages[] = "Finished printing tempuser.";
                        $debug->logmessage($messages, 'both');
                        unset($messages);
                    }

                    role_assign($tempuser->roleid, $tempuser->userid, $context->id);

                    // Not sure what the following does, but assign_capability says to
                    // call it after (and it only works with using it)...
                    $context->mark_dirty();
                }
            } else {
                // The user should NOT have access to this category. Do they? If so, remove them from the role.
                if (isset($debug)) {
                    $debug->logmessage("countroles is :<strong>$countroles</strong>.", 'logfile');
                    $temp = $catlist[$key]['catname'];
                    $debug->logmessage("User <strong>$tempuser->userid</strong> should
                    <strong>NOT</strong> have access to category <strong>$temp</strong>. ", 'logfile');
                }

                // If the user has roles in this category...
                if ($countroles != 0) {
                    if (isset($debug)) {
                        $temp = $catlist[$key]['catname'];
                        $debug->logmessage("However, user <strong>$tempuser->userid DOES</strong>
                        have access to category <strong>$temp</strong>.", 'logfile');
                        $debug->logmessage("Action: will remove the user from the role.", 'logfile');
                        $debug->logmessage("userroles array follows (before removing):", 'logfile');
                        $debug->logmessage(print_r($userroles, true), 'logfile');
                        $debug->logmessage("tempuser->roleid is:", 'logfile');
                        $debug->logmessage(print_r($tempuser->roleid, true), 'logfile');
                        $debug->logmessage("", 'logfile');
                    }

                    // For each of the roles, remove the user from it.
                    foreach ($userroles as $role) {
                        // The user may have the correct role (for example, IBM-student),
                        // but might have been accidentally given access to an "off limits" portfolio
                        // (for example, GTP-Portfolio). In this case, if the current portfolio
                        // is not one of the one's in the list, remove the user from it.
                        role_unassign($role->roleid, $tempuser->userid, $context->id);

                        // Not sure what the following does, but assign_capability says to call it after
                        // (and it only works with using it)...
                        $context->mark_dirty();
                    }

                    // Get a new count of the roles assigned to this top-level category.
                    // Added checkparentcontexts as false (to remove System contextid).
                    $updateduserroles = get_user_roles($context, $tempuser->userid, false);
                    $updatedcountroles = count($updateduserroles);

                    // If the user STILL has roles in this category...
                    if ($updatedcountroles != 0) {
                        if (isset($debug)) {
                            $messages[] = "Unable to remove <strong>$tempuser->userid access to category <strong>$temp</strong>.";
                            $messages[] = "updateduserroles array follows (after failed removal):";
                            $messages[] = print_r($updateduserroles, true);
                            $debug->logmessage($messages, 'both');
                            unset($messages);
                        }
                    } else {
                        // The user now has no roles in this category.
                        if (isset($debug)) {
                            $messages[] = "User <strong>$tempuser->userid successfully removed
                            access to category <strong>$temp</strong>.";
                            $messages[] = "updateduserroles array follows (after removing):";
                            $messages[] = print_r($updateduserroles, true);
                            $debug->logmessage($messages, 'both');
                            unset($messages);
                        }
                    }
                } else {
                    // The user has no roles in this category.
                    if (isset($debug)) {
                        $debug->logmessage("And does <strong>NOT</strong>.", 'logfile');
                    }
                }
            }
        }

        // Remember to unset SESSION->SWTC->USER->relateduser at the end.
        if (isset($this->relateduser)) {
            unset($this->relateduser);
            unset($userrelated);
        }

        // Invalidate the data so that the user does not need to logoff and log back in to see changed roles...
        purge_all_caches();

        if (isset($debug)) {
            $debug->logmessage("Leaving /local/swtc/classes/set_user_role.exit===11===.", 'logfile');
        }
        return;
    }

    /**
     * All Getter methods for all properties.
     *
     * Getter methods:
     * @param N/A
     * @return value
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     **/
    public function get_userid() {
        return $this->userid;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    public function get_timestamp() {
        return $this->timestamp;
    }

    public function get_user_access_type() {
        return $this->accesstype;
    }

    public function get_portfolio() {
        return $this->portfolio;
    }

    // SWTC ********************************************************************************.
    // Get the logged in user customized user profile value 'accesstype'. accesstype is used to determine
    // which portfolio of classes the user should have access to (in other words, which top-level
    // category they should have access to). Note that get_user_access returns the information the
    // user 'should' have access to. What the user actually has access to (and whether they need
    // more or less access) is determined above.
    //
    // Important! Case of accesstype is important. It must match the case defined in Moodle.
    //
    // Returns array: first element portfolio value; second element the user's role shortname
    // (i.e. 'ibm-student' or 'gtp-administrator'); third element is the top-level category id
    // the user 'should' have access to (checked above).
    //
    // SWTC ********************************************************************************.
    /**
     * Used get the users access.
     *
     * @param N/A
     *
     * @return $array   The catlist array.
     * @return $array   An array of values used to set $SESSION->SWTC->USER.
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     * 03/04/21 - Changing $swtc_user to $this.
     *
     */
    public function get_user_access() {
        global $DB, $USER;

        // Get the current debug information.
        $debug = swtc_get_debug();

        // Temporary variables. Use these during the function.
        // $tempuser = new stdClass();    // Returned to calling function.
        $tempuser = clone $this;      // Create a swtc_user object.

        $roleshortname = null;
        $portfolio = null;
        $categoryids = array(); // A list of all the categories the user should have access to (set in $this->categoryids).

        // SWTC ********************************************************************************.
        // 07/12/18 - Added check if this->relateduser is set. If so, use that user information to determine access.
        // Note that no switching of users below should be necessary.
        // SWTC ********************************************************************************.
        if (isset($this->relateduser)) {
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering /local/swtc/classes/swtc_user.php. ===3.get_user_access.enter.";
            $messages[] = "this->relateduser is set; the userid that will be used throughout get_user_access
            is :<strong>$this->userid</strong>.";
            $messages[] = "this->relateduser is set; the username that will be used throughout get_user_access
            is :<strong>$this->username</strong>.";
            $messages[] = "this->relateduser is set; the accesstype is :<strong>$this->accesstype</strong>.";
            $accesstype = $this->relateduser->accesstype;
        } else {
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering /local/swtc/classes/swtc_user.php. ===3.get_user_access.enter.";
            $messages[] = "this->relateduser is NOT set; the userid that will be used throughout get_user_access
            is :<strong>$this->userid</strong>.";
            $messages[] = "this->relateduser is NOT set; the username that will be used throughout get_user_access
            is :<strong>$this->username</strong>.";
            $messages[] = "this->relateduser is NOT set; the accesstype is :<strong>$this->accesstype</strong>.";
            $accesstype = $this->accesstype;
        }
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            // $messages[] = "SWTC ********************************************************************************.";
            // $messages[] = "Entering /local/swtc/classes/swtc_user.php. ===3.get_user_access.enter.".
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Switch on the users access type.
        //
        // Sets variables:
        // $this->roleshortname    The actual name of the role the user has.
        // $this->portfolio        The name of the portfolio the user has access to.
        // $this->categoryids        An array of category ids the user has access to.
        //
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        // Check for Lenovo-admin, Lenovo-inst, or Lenovo-stud user
        // SWTC ********************************************************************************.
        if ((stripos($accesstype, get_string('access_lenovo_administrator', 'local_swtc')) !== false) ||
        (stripos($accesstype, get_string('access_lenovo_instructor', 'local_swtc')) !== false) ||
        (stripos($accesstype, get_string('access_lenovo_student', 'local_swtc')) !== false)) {
            if (stripos($accesstype, get_string('role_lenovo_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_administrator', 'local_swtc');
                $portfolio = get_string('lenovo_portfolio', 'local_swtc');

                // Create temp array of category names.
                $catnames[] = get_string('lenovointernal_portfolio', 'local_swtc');
                $catnames[] = get_string('lenovosharedresources_portfolio', 'local_swtc');
                $catnames[] = get_string('gtp_portfolio', 'local_swtc');
                $catnames[] = get_string('curriculums_portfolio', 'local_swtc');

                // Loop through the temp array of category names to load the rest of the fields that are needed.
                foreach ($catnames as $catname) {
                    $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                    $categoryids[$category->id] = $catname;
                }
            } else if (stripos($accesstype, get_string('role_lenovo_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_instructor', 'local_swtc');
                $portfolio = get_string('lenovo_portfolio', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_lenovo_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_student', 'local_swtc');
                $portfolio = get_string('lenovo_portfolio', 'local_swtc');
            }

            // Search for category name in cats array. When found, load the category id values.
            $catnames[] = get_string('lenovo_portfolio', 'local_swtc');
            $catnames[] = get_string('ibm_portfolio', 'local_swtc');
            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('maintech_portfolio', 'local_swtc');
            $catnames[] = get_string('asp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');
            $catnames[] = get_string('premiersupport_portfolio', 'local_swtc');
            $catnames[] = get_string('servicedelivery_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_av_gtp', 'local_swtc')) !== false) {
            $portfolio = get_string('gtp_portfolio', 'local_swtc');

            if (stripos($accesstype, get_string('role_gtp_siteadministrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }

            $catnames[] = get_string('gtp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_im_gtp', 'local_swtc')) !== false) {
            $portfolio = get_string('gtp_portfolio', 'local_swtc');

            if (stripos($accesstype, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadmin', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }

            $catnames[] = get_string('gtp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_lq_gtp', 'local_swtc')) !== false) {
            $portfolio = get_string('gtp_portfolio', 'local_swtc');

            if (stripos($accesstype, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadmin', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_gtp_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }

            $catnames[] = get_string('gtp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for IBM-stud user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_ibm_student', 'local_swtc')) !== false) {
            $portfolio = get_string('ibm_portfolio', 'local_swtc');
            $roleshortname = get_string('role_ibm_student', 'local_swtc');

            // Create temp array of category names.
            $catnames[] = get_string('ibm_portfolio', 'local_swtc');
            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for ServiceProvider-stud user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_serviceprovider_student', 'local_swtc')) !== false) {
            $portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
            $roleshortname = get_string('role_serviceprovider_student', 'local_swtc');

            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('asp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for Maintech-stud user
            // SWTC ********************************************************************************.
        } else if (strncasecmp($accesstype, get_string('access_maintech_student', 'local_swtc'), strlen($accesstype)) == 0) {

            $portfolio = get_string('maintech_portfolio', 'local_swtc');
            $roleshortname = get_string('role_maintech_student', 'local_swtc');

            $catnames[] = get_string('maintech_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for ASP-Maintech-stud user
            // SWTC ********************************************************************************.
        } else if (strncasecmp($accesstype, get_string('access_asp_maintech_student', 'local_swtc'), strlen($accesstype)) == 0) {
            $portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
            $roleshortname = get_string('role_asp_maintech_student', 'local_swtc');

            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('maintech_portfolio', 'local_swtc');
            $catnames[] = get_string('asp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for PremierSupport users
            // SWTC ********************************************************************************.
        } else if ((preg_match(get_string('access_premiersupport_pregmatch_student', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_geoadministrator', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $accesstype))) {
            $portfolio = get_string('premiersupport_portfolio', 'local_swtc');

            if (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_student', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadministrator', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
            }

            $catnames[] = get_string('premiersupport_portfolio', 'local_swtc');
            $catnames[] = get_string('ibm_portfolio', 'local_swtc');
            $catnames[] = get_string('lenovo_portfolio', 'local_swtc');
            $catnames[] = get_string('maintech_portfolio', 'local_swtc');
            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('asp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for ServiceDelivery users
            // SWTC ********************************************************************************.
        } else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_student', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadministrator', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'), $accesstype))) {
            $portfolio = get_string('servicedelivery_portfolio', 'local_swtc');

            if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_student', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'),
                $accesstype)) {
                    $roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadministrator', 'local_swtc'),
                $accesstype)) {
                    $roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
            }

            $catnames[] = get_string('servicedelivery_portfolio', 'local_swtc');
            $catnames[] = get_string('ibm_portfolio', 'local_swtc');
            $catnames[] = get_string('lenovo_portfolio', 'local_swtc');
            $catnames[] = get_string('maintech_portfolio', 'local_swtc');
            $catnames[] = get_string('serviceprovider_portfolio', 'local_swtc');
            $catnames[] = get_string('asp_portfolio', 'local_swtc');
            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // Check for Self support user
            // SWTC ********************************************************************************.
        } else if (stripos($accesstype, get_string('access_selfsupport_student', 'local_swtc')) !== false) {
            $portfolio = get_string('none_portfolio', 'local_swtc');       // 05/01/18 - RF
            $roleshortname = get_string('role_selfsupport_student', 'local_swtc');

            $catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

            // Loop through the temp array of category names to load the rest of the fields that are needed.
            foreach ($catnames as $catname) {
                $category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
                $categoryids[$category->id] = $catname;
            }
            // SWTC ********************************************************************************.
            // accesstype is not recognized
            // SWTC ********************************************************************************.
        } else {
            $portfolio = get_string('none_portfolio', 'local_swtc');
            $roleshortname = 'none';
            $categoryids[] = 'none';
        }

        // SWTC ********************************************************************************.
        // Finished. Set $tempuser to all the appropriate values so it can be returned.
        // SWTC ********************************************************************************.
        $tempuser->portfolio = $portfolio;
        $tempuser->roleshortname = $roleshortname;
        $role = $DB->get_record('role', array('name' => $roleshortname), 'id');
        $tempuser->roleid = $role->id;
        $tempuser->categoryids = $categoryids;

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Leaving /local/swtc/classes/swtc_user.php. ===3.get_user_access.exit.";
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "tempuser array follows: ";
            $messages[] = print_r($tempuser, true);
            $messages[] = "After printing tempuser";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        return $tempuser;
    }

    /**
     * Setup most, but not all, the characteristics of  SESSION->SWTC->USER->relateduser.
     *
     * @param  integer $userid The userid of the user.
     * @return swtc_user         The related user's information.
     *
     * History
     *
     * 02/22/21 - Initial writing.
     *
     */
    public function get_relateduser($userid) {

        // Temporary variable to hold related userid information.
        $relateduser = new stdClass();
        // SWTC ********************************************************************************.
        // Set some of the SWTC->relateduser variables that will be used IF a relateduserid is found.
        // SWTC ********************************************************************************.
        // Get all the user information based on the userid passed in.
        // Note: '*' returns all fields (normally not needed).
        $relateduser = core_user::get_user($userid);
        profile_load_data($relateduser);

        // SWTC ********************************************************************************.
        // Since we are using get_user and profile_load_data, there is no need to copy any other fields.
        // SWTC ********************************************************************************.
        // $relateduser->username = $relateduser->username.

        // SWTC ********************************************************************************.
        // The following fields MUST be added to $relateduser (as they normally do not exist).
        // SWTC ********************************************************************************.
        $relateduser->userid = $userid;
        $relateduser->accesstype = $relateduser->profile_field_accesstype;

        $relateduser->portfolio = $this->portfolio;

        // Add user timezone to improve performance.
        $relateduser->timezone = $this->set_timezone();
        $relateduser->timestamp = $this->set_timestamp();

        // Important! roleshortname and roleid are what the roles SHOULD be, not necessarily what the roles are.
        $relateduser->roleshortname = null;
        $relateduser->roleid = null;

        return $relateduser;
    }

    /**
     * Load all the category (portfolio) ids and information about each of them.
     *
     * @param N/A
     *
     * @return $array   All category information.
     */
    /**
     * Version details
     *
     * History:
     *
     * 02/16/21 - Initial writing.
     *
     **/
    public function loadallcatsaccess() {
        global $DB;

        // SWTC ********************************************************************************.
        // SWTC SWTC swtc_user and debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        // A list of all the top-level category information defined (this is returned).
        $cats = array();
        $roles = $DB->get_records('role', array(), 'id ASC', 'id, name, shortname');
        // Put the secondary objects into array format so that multidimensional searching will work.
        $roles = convert_to_array($roles);
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering /local/swtc/classes/swtc_user.php. ===loadallcatsaccess.enter.";
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Get a list of all top-level categories defined in the system (whether the user can view them or not) using get_tree.
        // Note: The following array is returned; the number in the listing is the top-level category id number
        // ($catids->id). Example:
        // array (              At the time of this writing, the top-level category names are:
        // [0] => 14            'GTP Portfolio'
        // [1] => 36            'IBM Portfolio'
        // [2] => 47            'SWTC Portfolio'
        // [3] => 60            'SWTC Internal Portfolio'
        // [4] => 73            'SWTC Shared Resources (Master)'
        // [5] => 74            'Maintech Portfolio'
        // [6] => 25            'Service Provider'
        // [7] => 97            'ASP Portfolio'
        // [8] => 110        'Premier Support Portfolio'
        // [9] => 137        'Service Delivery Portfolio'
        // [10] => 136        'Site Help Portfolio'
        // [11] => 141        'Curriculums Portfolio'
        // )
        // Important! The category id's returned are NOT guaranteed to be the numbers shown (although they should be). However,
        // the category NAMES ARE guaranteed to be strings shown (unless specifically changed on the SWTC EBG LMS site).
        // Important! To access context for each category: $context = $cats[0-8]['context'];
        // SWTC ********************************************************************************.
        // '0' means just the top-level categories are returned.
        $catids = $this->get_tree(0);

        if (isset($debug)) {
            $messages[] = "catids array follows:";
            $messages[] = print_r($catids, true);
            $messages[] = "catids array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Next, load a multi-dimension array for each of the top-level categories (this array will be
        // searched by name for the id below):
        // 'catid' - the id of the top-level category (returned from the get_tree(0) call above).
        // 'roles' - array of all roles and roleids associated with this top-level category
        // (see below for example).
        //
        // An example array (filled-in below) has the following format
        // (as of 08/28/16 taken from .244 sandbox):
        //
        // [0] => Array
        // (
        // [catid] => 14
        // [roles] => Array
        // (
        // [gtp-instructor] => 15
        // [gtp-student] => 16
        // [gtp-administrator] => 10
        // [gtp-siteadministrator] => 23
        // )
        // )
        //
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        // Build the main $cats array (to be passed back to local_swtc_set_user_role).
        // SWTC ********************************************************************************.
        foreach ($catids as $key => $catid) {
            $cats[$key]['catid'] = $catid;
            $cats[$key]['catname'] = core_course_category::get($catid, MUST_EXIST, true)->name;

            // SWTC ********************************************************************************.
            // Remember: top-level categories are accessed by $top_level_categories->xxx; capabilities are
            // accessed by $capabilities->xxx.
            // For each top-level category, add a two-dimentional array consisting of the roleshortnames and
            // roleids of the roles that have access to the top-level category.
            // SWTC ********************************************************************************.

            // SWTC ********************************************************************************.
            // Switch on the 'catname'.
            // Note: If adding a new portfolio, add a new case to this switch.
            // SWTC ********************************************************************************.
            switch ($cats[$key]['catname']) {
                // SWTC ********************************************************************************.
                // 'GTP Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('gtp_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'gtp-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_instructor', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'Lenovo Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('lenovo_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'lenovo-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_instructor', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'IBM Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('ibm_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'ibm-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'ServiceProvider Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('serviceprovider_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'serviceprovider-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'SWTC Internal Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('lenovointernal_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'lenovo-administrator', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'Maintech Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('maintech_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'maintech-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_maintech_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_maintech_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'SWTC Shared Resources (Master)' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('lenovosharedresources_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'lenovo-administrator', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'ASP Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('asp_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'asp-maintech-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_asp_maintech_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'PremierSupport Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('premiersupport_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'premiersupport-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_premiersupport_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_siteadministrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'ServiceDelivery Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('servicedelivery_portfolio', 'local_swtc'):
                    $temp = array();
                    $this->array_find_deep($roles, 'shortname', 'servicedelivery-', $temp);

                    foreach ($temp as $role) {
                        if ($role['shortname'] == get_string('role_servicedelivery_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_siteadministrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'Site Help Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('sitehelp_portfolio', 'local_swtc'):
                    foreach ($roles as $role) {
                        if ($role['shortname'] == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_instructor', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_gtp_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_instructor', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_asp_maintech_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_siteadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_siteadministrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                // SWTC ********************************************************************************.
                // 'Curriculums Portfolio' - add the roleids that have access to the top-level category.
                // SWTC ********************************************************************************.
                case get_string('curriculums_portfolio', 'local_swtc'):
                    foreach ($roles as $role) {
                        if ($role['shortname'] == get_string('role_servicedelivery_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_servicedelivery_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_servicedelivery_siteadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_manager', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_geoadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_geoadministrator', 'local_swtc')] = $role['id'];
                        } else if ($role['shortname'] == get_string('role_premiersupport_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_premiersupport_siteadministrator', 'local_swtc')] = $role['id'];
                        }
                    }
            break;

                default:
            }
        }

        // SWTC ********************************************************************************.
        // Note: At this point the $cats array should be fully created...
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Exiting /local/swtc/classes/swtc_user.php. ===loadallcatsaccess.exit.";
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "cats array follows:";
            $messages[] = print_r($cats, true);
            $messages[] = "cats array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        return $cats;
    }

    /**
     * Returns the entry from categories tree and makes sure the application-level tree cache is built
     *
     * The following keys can be requested:
     *
     * 'countall' - total number of categories in the system (always present)
     * 0 - array of ids of top-level categories (always present)
     * '0i' - array of ids of top-level categories that have visible=0 (always present but may be empty array)
     * $id (int) - array of ids of categories that are direct children of category with id $id. If
     *   category with id $id does not exist returns false. If category has no children returns empty array
     * $id.'i' - array of ids of children categories that have visible=0
     *
     * @param int|string $id
     * @return mixed
     */
    public function get_tree($id) {
        global $DB;
        $coursecattreecache = cache::make('core', 'coursecattree');
        $rv = $coursecattreecache->get($id);
        if ($rv !== false) {
            return $rv;
        }
        // Re-build the tree.
        $sql = "SELECT cc.id, cc.parent, cc.visible
    FROM {course_categories} cc
    ORDER BY cc.sortorder";
        $rs = $DB->get_recordset_sql($sql, array());
        $all = array(0 => array(), '0i' => array());
        $count = 0;
        foreach ($rs as $record) {
            $all[$record->id] = array();
            $all[$record->id. 'i'] = array();
            if (array_key_exists($record->parent, $all)) {
                $all[$record->parent][] = $record->id;
                if (!$record->visible) {
                    $all[$record->parent. 'i'][] = $record->id;
                }
            } else {
                // Parent not found. This is data consistency error but next fix_course_sortorder() should fix it.
                $all[0][] = $record->id;
                if (!$record->visible) {
                    $all['0i'][] = $record->id;
                }
            }
            $count++;
        }
        $rs->close();
        if (!$count) {
            // No categories found.
            // This may happen after upgrade of a very old moodle version.
            // In new versions the default category is created on install.
            $defcoursecat = $self::create(array('name' => get_string('miscellaneous')));
            set_config('defaultrequestcategory', $defcoursecat->id);
            $all[0] = array($defcoursecat->id);
            $all[$defcoursecat->id] = array();
            $count++;
        }
        // We must add countall to all in case it was the requested ID.
        $all['countall'] = $count;
        foreach ($all as $key => $children) {
            $coursecattreecache->set($key, $children);
        }
        if (array_key_exists($id, $all)) {
            return $all[$id];
        }
        // Requested non-existing category.
        return array();
    }

    /**
     * Look for the portfolio name in the $categoryids array. When found, save the values we want and
     *      return the newly created array.
     *
     * @param The portfolio name to look for and the list of all portfolios.
     *
     * @return $tmp   The catlist array used to set $SESSION->SWTC->USER.
     * @return string   The capability.
     *
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    public function get_portfolioname($portfolioname, $cats) {
        $tmp = array();

        $cat = $cats[array_search($portfolioname, array_column($cats, 'catname'))];

        $tmp['catid'] = $cat['catid'];
        $tmp['catname'] = $cat['catname'];

        return array($tmp);
    }

    /**
     * If PremierSupport or ServiceDelivery manager or administrator ventures outside their own portfolio,
     *          they are no longer considered a manager or administrator. Substitute either
     *          PremierSupport-student or ServiceDelivery-student as role.
     *
     * @param $cat        A catlist variable.
     * @param $user        A user variable.
     *
     * @return $tempuser    $user (passed in) with the rolename and roleid changed if required.
     *
     *
     * History:
     *
     * 10/24/20 - Initial writing.
     *
     */
    public function change_user_access($cat) {
        global $DB;

        // SWTC ********************************************************************************.
        // SWTC SWTC debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $accesstype = $this->accesstype;
        $roleshortname = null;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering /local/swtc/classes/swtc_user.php. === change_user_access.enter.";
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($this, true);
            $messages[] = "swtc_user array ends.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // PremierSupport access type.
        // Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        // to search for access types.
        // SWTC ********************************************************************************.
        // PremierSupport managers
        // SWTC ***********************************************************************************.
        if (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $accesstype)) {
            // If the portfolio is PremierSupport, continue with the mgr access.
            if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
            } else {
                // If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // PremierSupport administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $accesstype)) {
            // If the portfolio is PremierSupport, continue with the admin access.
            if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
            } else {
                // If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // PremierSupport GEO administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
            // If the portfolio is PremierSupport, continue with the GEO admin access.
            if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
            } else {
                // If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // PremierSupport site administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $accesstype)) {
            // If the portfolio is PremierSupport, continue with the site admin access.
            if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
            } else {
                // If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            }
            // SWTC ********************************************************************************.
            // ServiceDelivery access type.
            // Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
            // to search for access types.
            // SWTC ********************************************************************************.
            // SWTC ***********************************************************************************.
            // ServiceDelivery managers
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $accesstype)) {
            // If the portfolio is ServiceDelivery, continue with the mgr access.
            if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
            } else {
                // If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // ServiceDelivery administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $accesstype)) {
            // If the portfolio is ServiceDelivery, continue with the admin access.
            if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
            } else {
                // If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // ServiceDelivery GEO administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
            // If the portfolio is ServiceDelivery, continue with the admin access.
            if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
            } else {
                // If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            }
            // SWTC ***********************************************************************************.
            // ServiceDelivery site administrators
            // SWTC ***********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'), $accesstype)) {
            // If the portfolio is ServiceDelivery, continue with the admin access.
            if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
            } else {
                // If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id
                // (not a string; hard-code for now).
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            }
        }
        // SWTC ********************************************************************************.
        // Remember to set the roleid.
        // 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
        // if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
        // it was when this was called.
        // SWTC ********************************************************************************.
        if (!empty($roleshortname)) {
            $this->roleshortname = $roleshortname;
            $role = $DB->get_record('role', array('shortname' => $this->roleshortname), '*', MUST_EXIST);
            $this->roleid = $role['id'];
        }

        return;
    }



    // Function to recursively search for a given value.
    // For example, if this is the multi-dimensional array:
    // Array
    // (
    // [studs_menu] => Array
    // (
    // [1478973742] => Array
    // (
    // [uuid] => 1478973742
    // [groups] => 18421, 18422, 18423, 18424, 18425
    // )
    //
    // )
    //
    // [mgrs_menu] => Array
    // (
    // [168690638] => Array
    // (
    // [uuid] => 168690638
    // [groups] => 18426, 18427, 18428, 18429, 18430
    // )
    //
    // )
    //
    // [admins_menu] => Array
    // (
    // [630459861] => Array
    // (
    // [uuid] => 630459861
    // [groups] => 18431, 18432, 18433, 18434, 18435
    // )
    //
    // )
    //
    // )
    //
    // If you are searching for "168690638", the following will be returned:
    // Array
    // (
    // [0] => mgrs_menu
    // [1] => 168690638
    // [2] => uuid
    // ).
    /**
     * Version details
     *
     * History:
     *
     * 02/24/21 - Initial writing.
     *
     **/
    public function array_find_deep($array, $key, $value, array &$results = []) {
        if (!is_array($array)) {
            return;
        }

        $key = str_replace('/', '\\/', $key);

        foreach ($array as $arraykey => $arrayvalue) {
            if ((preg_match("/$key/i", (string)$arraykey)) && (preg_match("/$value/i", (string)$arrayvalue))) {
                // Add array if we have a match.
                $results[] = $array;
            }

            if (is_array($arrayvalue)) {
                // Only do recursion on arrays.
                $this->array_find_deep($arrayvalue, $key, $value, $results);
            }
        }
    }
}
