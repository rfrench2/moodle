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
 * @copyright  2020 SWTC
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

use \stdClass;
use \cache;

// SWTC ********************************************************************************
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************
// require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');


/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT!
 *          DO NOT call this class directly. Use $swtc_get_user from /lib/swtc_userlib.php.
 *
 * @param N/A
 *
 * @return $SESSION->SWTC->USER.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
 // use cm_info;
 // use core_text;
 // use html_writer;
 // use context_course;
 // use moodle_url;
 // use coursecat_helper;



class swtc_user {
    /**
     * Store the user's id.
     *
     * @private  integer
     */
    private  $userid;

     /**
      * Store the user's username.
      *
      * @private  string
      */
    private  $username;

     /**
      * Store the user's accesstype.
      *
      * @private  string
      */
    private  $user_access_type;

    /**
     * The user's main portfolio they have access to.
     *
     * @private  integer
     */
    private  $portfolio;

    /**
     * The user's role shortname.
     *
     * @private  integer
     */
    private  $roleshortname;

    /**
     * The user's role id.
     *
     * @private  integer
     */
    private  $roleid;

    /**
     * The categories the user has access to.
     *
     * @private  integer
     */
    private  $categoryids;

    /**
     * The user's capabilities.
     *
     * @private  integer
     */
    private  $capabilities;

    /**
     * The time of this action.
     *
     * @private  integer
     */
    private  $timestamp;

    /**
     * If an admin is performing an action on behalf of another user, this is the related user's id.
     *
     * @private  integer
     */
    private  $relateduser;

    /**
     * The cohort names the user is a member of (if any).
     *
     * @private  integer
     */
    private  $cohortnames;

    /**
     * The preg_match string that should be used to find all the groups the user is a member of.
     *
     * @private  integer
     */
    private  $groupname;

    /**
     * The user's GEO.
     *
     * @private  integer
     */
    private  $geoname;

    /**
     * The groups the user is a member of (if any).
     *
     * @private  integer
     */
    private  $groupnames;

    /**
     * The timezone of the user.
     *
     * @private  integer
     */
    private  $timezone;

    /**
     * The user's accesstype 2.
     *
     * @private  integer
     */
    private  $user_access_type2;

    /**
     * Constructor
     *
     * Constructor is private, use local_stwc->get_user() to retrieve SWTC user information.
     *
     * @param class $USER or $user
     *
    */
    public function __construct($user) {
        global $SESSION;

        // print_object("In swtc_user __construct");		// 10/18/20 - SWTC
        // print_object("In swtc_user __construct; about to print backtrace");		// 10/16/20 - SWTC
        // print_object(format_backtrace(debug_backtrace(), true));        // SWTC-debug
        // print_object($user);		// 10/16/20 - SWTC

        // The following should be set in $USER.
        $this->userid = (isset($user->id)) ? $user->id : null;
        $this->username = (isset($user->username)) ? $user->username : null;

        // SWTC ********************************************************************************
        // Load the user's profile data.
        // SWTC ********************************************************************************
        $temp = new stdClass();
        $temp->id = $this->userid;
        profile_load_data($temp);
        // print_object("In swtc_user __construct; about to print profile data");		// 10/16/20 - SWTC
        // print_object($temp);		// 10/16/20 - SWTC
        // $this->user_access_type = $temp->get_string('profile_field_accesstype', 'local_swtc');
        $this->user_access_type = (isset($temp->profile_field_Accesstype)) ? $temp->profile_field_Accesstype : null;
        // $this->user_access_type2 = (null !== $temp->get_string('profile_field_accesstype2', 'local_swtc')) ? $temp->get_string('profile_field_accesstype2', 'local_swtc') : null;
        $this->user_access_type2 = (isset($temp->profile_field_accesstype2)) ? $temp->profile_field_accesstype2 : null;

        // The following we'll set in ***.
        $this->portfolio = 'PORTFOLIO_NONE';
        $this->roleshortname = null;
        $this->roleid = null;
        $this->categoryids = null;
        $this->capabilities = null;
        $this->relateduser = null;
        $this->cohortnames = null;
        $this->groupname = null;
        $this->geoname = null;
        $this->groupnames = null;

        // SWTC ********************************************************************************
        // Get the additional swtc_user properties; user's timestamp and timezone.
        // SWTC ********************************************************************************
        list($this->timestamp, $this->timezone) = $this->set_timestamp();

        // SWTC ********************************************************************************
        // Copy this object to $SESSION->SWTC->USER.
        // SWTC ********************************************************************************
        // $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
        // $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
        // print_object("In not set SWTC->USER; about to print this");		// 10/16/20 - SWTC
        // print_object($this);		// 10/16/20 - SWTC
        // print_object("About to leave swtc_user __construct; about to print SESSION->SWTC");		// 10/20/20 - SWTC
        // print_object($SESSION->SWTC);      // 10/20/20 - SWTC
    }

    /**
     * All Setter and Getter methods for all properties.
     *
     * Setter methods:
     *      @param $value
     *      @return N/A
     *
     * Getter methods:
     *      @param N/A
     *      @return value
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     **/
    public function get_user($user) {
        // $swtc_user = new swtc_user;      // 10/24/20
        $swtc_user = new swtc_user($user);
        // print_object("In swtc_user.get_user; about to print swtc_user");
        // print_object($swtc_user);
        return $swtc_user;
    }

    public function get_userid() {
        return $this->userid;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    public function get_user_access_type() {
        return $this->user_access_type;
    }

    public function get_capabilities() {
        return $this->capabilities;
    }

    public function get_portfolio() {
        return $this->portfolio;
    }

    /**
     * Set current date and time for timestamp. Returns value to set $SESSION->SWTC->USER->timestamp.
     *
     * History:
     *
     * 10/19/20 - Initial writing.
     *
     **/
    public function set_timestamp() {
        $timezone = \core_date::get_user_timezone_object();
        $today = new \DateTime("now", $timezone);
        $time = $today->format('H:i:s.u');
        return array($time, $timezone);
    }

    /**
     * Checks for the following:
     *		Checks to see if the user has been assigned a role at login (message '\core\event\user_loggedin').
     *		    If not, assign them the appropriate role based on the customized user profile setting 'Accesstype'.
     *		When creating a user (message '\core\event\user_created'), assign them the appropriate role based
     *		    on the customized user profile setting 'Accesstype'. At user update (message
     *		    '\core\event\user_updated'),  assign them the appropriate role based on the customized
     *          user profile setting 'Accesstype'.
     *		When viewing the frontpage (message '\core\event\user_updated') where courseid = 1, reload the page
     *		    (to refresh the users new roles if necesary).
     *		When a user self-enrolls in a course (message '\core\event\role_assigned'), a default role is given
     *		    to the user (different default roles depending on the portfolio). If the course that the user
     *		    is enrolling in has a metalink to the 'Shared Resources (Master)' (for example,
     *          a simulator), two enrollments are performed. When each '\core\event\role_assigned' message is
     *          processed, remove the default role for the user and assign them the appropriate role based on the
     *          customized user profile setting 'Accesstype'.
     *
     * For example,
     *		If the user is a GTP-stud user, assign them the GTP-stud role.
     *
     * Important! Prerequiste is all users have the user profile setting 'Accesstype' set. Another prerequisite
     *      is that all the courses defined in the Lenovo Server Education LMS system using the new swtccustom
     *      course format (it saves a course portfolio flag in the course settings). Flag is:
     * 	        $COURSE->coursetype
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */

    function assign_user_role($eventdata) {
    	global $USER, $DB, $COURSE, $SESSION;

      	// SWTC ******************************************************************************
      	// SWTC LMS swtc_user and debug variables.
      	$swtc_user = swtc_get_user($USER, $eventdata->relateduserid);
      	$debug = swtc_set_debug();

    	// Other Lenovo variables.
      	$user_related = null;           // Only set IF working with a related user (i.e. swtc_get_relateduser is called).
      	$eventname = '';						// The event / message that has been triggered.
    	$catlist = array();					// A list of all the top-level categories defined (returned from get_user_access).
      	$tmp_user = new stdClass();    // Hold return values from get_user_access.
        // SWTC ********************************************************************************

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************";
            $messages[] = "Entering /local/swtc/lib/locallib.php===assign_user_role.enter.";
            $messages[] = "Lenovo ********************************************************************************";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends.";
            $messages[] = "eventname follows :";
            $messages[] = print_r($eventdata->eventname, true);
            $messages[] = "eventname ends.";
            // $debug->logmessage(print_r($swtc, true), 'detailed');
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

    	//****************************************************************************************
    	// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
    	//****************************************************************************************
    	if ( empty($USER->id)) {
    		if (isset($debug)) {
    			$debug->logmessage("User has not logged on yet; assign_user_role.exit===2.1===.", 'logfile');
    		}
    		return;
    	}

        // SWTC ********************************************************************************
        // Removed a section of code, comments, or both. See archived versions of module for information.
        // SWTC ********************************************************************************

    	// Load the event name (if it's needed later).
    	$eventname = $eventdata->eventname;

    	if (isset($debug)) {
    		$messages[] = "eventname is :<strong>$eventname</strong>.";
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

        // SWTC ********************************************************************************
    	// Trick to refresh the users roles without logging out and logging in again.
    	//		If the user is already logged OUT and their role changes, they get an updated view next time they login.
    	//		However, if the user is already logged IN and their role changes, we must reload a web page for the new role assignments to take affect.
    	//			This means capturing the course_viewed message, calling purge_all_caches, and immediately returning.
    	//
    	// Will tell user to click on the home page link to refresh their access, but viewing any course will work.
    	//		In fact, just clicking refresh in the web browser should work (have not testing will all browsers in all circumstances).
    	// 09/26/16 - Since we are now putting activities on the front page, we cannot just return anymore (so that access restrictions can be set).
    	//							If the user is viewing any course other than the front page, purge_all_caches and return.
    	// if (($eventname == '\core\event\course_viewed') and ($eventdata->courseid != 1) ) {
        // SWTC ********************************************************************************
        if ($eventname == '\core\event\course_viewed') {
            if (isset($debug)) {
                if ($eventdata->courseid == 1) {
                    $debug->logmessage("User is viewing the front page (courseid = 1). Continuing...", 'logfile');
                    //$debug->logmessage("eventdata properties follow...", 'logfile');
                    //print_object($eventdata, true);
                    //$debug->logmessage("Finished printing eventdata ===11===.", 'logfile');
                    purge_all_caches();         // 04/26/18 - RF - testing of refreshing front page so student can see new course they are enrolled in.
                } else {
                    $debug->logmessage("User is viewing a course. About to return.", 'logfile');
                    $debug->logmessage("Leaving assign_user_role.exit===11===.", 'logfile');
                    purge_all_caches();
                    return;
                }
            }
        }

        // SWTC ********************************************************************************
    	// Important! Properties passed via $eventdata defined in https://docs.moodle.org/dev/Event_2#Information_contained_in_events
    	//	Note: <strong> and </strong> begins and ends bold printing;   adds CRLF to end of print statement.
        // SWTC ********************************************************************************
    	if (isset($debug)) {
    		$messages[] = "==========1.2===========";
    		$messages[] = "eventdata properties follow...";
    		$messages[] = "event message :<strong>$eventdata->eventname.</strong>";
    		$messages[] = "contextid is : <strong>$eventdata->contextid.</strong>";
    		$messages[] = "possible contextlevel values are: CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
    		$messages[] = "contextlevel is :<strong>$eventdata->contextlevel.</strong>";
    		$messages[] = "courseid is :<strong>$eventdata->courseid.</strong>";
    		$messages[] = "contextinstanceid is :<strong>$eventdata->contextinstanceid.</strong>";
    		$messages[] = "userid is :<strong>$eventdata->userid</strong> (either userid, 0 when not logged in, or -1 when other).";
    		$messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid, admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "all eventdata properties follow :";
    		$messages[] = print_r($eventdata, true);
    		$messages[] = "all eventdata properties end.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	}

        // SWTC ********************************************************************************
        // Removed a section of code, comments, or both. See archived versions of module for information.
        // SWTC ********************************************************************************

        // SWTC ********************************************************************************
    	// Check to see if the administrator is working on behalf of a user, or the actual user is doing something.
    	//		Important! If an administrator is working on behalf of a user (for example, updating the user's profile or creating a new user),
    	//			$eventdata->relateduserid will be the userid of the user and the userid the rest of the plug-in should work with.
    	//			If a "regular" user is doing something, $eventdata->relateduserid will be empty.
    	//
    	// Sets variables:
    	//			$swtc_user->userid								The userid of the "actual" user (not the administrator).
        //			$swtc_user->username							The username of the "actual" user (not the administrator).
    	//			$swtc_user->user_access_type			The most important variable; triggers all the rest that follows.
        //          $swtc_user->timestamp
        //			$swtc_user->user_access_type2     // @02
        //
        // 07/12/18 - Added call to swtc_get_relateduser.
    	// SWTC ********************************************************************************
    	if (!empty($eventdata->relateduserid)) {		// 01/10/19

            if (isset($debug)) {
    			// SWTC ********************************************************************************
                // 07/10/18 - Changed some messages for clarity.
                // SWTC ********************************************************************************
                switch ($eventname) {

                    // SWTC ********************************************************************************
                    // Event \core\event\user_loggedinas
                    // SWTC ********************************************************************************
                    case '\core\event\user_loggedinas':
                        $debug->logmessage("Admin has logged on as user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\user_updated
                    // SWTC ********************************************************************************
                    case '\core\event\user_updated':
                        $debug->logmessage("Admin has updated a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\user_created
                    // SWTC ********************************************************************************
                    case '\core\event\user_created':
                        $debug->logmessage("Admin has created a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\role_assigned
                    // SWTC ********************************************************************************
                    case '\core\event\role_assigned':
                        $debug->logmessage("Admin has triggered a role assignment on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\user_enrolment_deleted
                    // SWTC ********************************************************************************
                    case '\core\event\user_enrolment_deleted':
                        $debug->logmessage("Admin has triggered an unenrollment from a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\user_enrolment_updated
                    // SWTC ********************************************************************************
                    case '\core\event\user_enrolment_updated':
                        $debug->logmessage("Admin has triggered an updated enrollment in a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event \core\event\user_enrolment_created
    				//
    				// 		If user_enrolment_created was done by a cohort, eventdata will look like the following (Notes are embedded):
    				//
    				// core\event\user_enrolment_created Object
    				// 	(
    				// 		[data:protected] => Array
    				// 			(
    				// 				[eventname] => \core\event\user_enrolment_created
    				// 				[component] => core
    				// 				[action] => created
    				// 				[target] => user_enrolment
    				// 				[objecttable] => user_enrolments
    				// 				[objectid] => 139952
    				// 				[crud] => c
    				// 				[edulevel] => 0
    				// 				[contextid] => 3819
    				// 				[contextlevel] => 50				(CONTEXT_COURSE)
    				// 				[contextinstanceid] => 159		(courseid 159 = ES11611)
    				// 				[userid] => 4							(4 = rfrench)
    				// 				[courseid] => 159					(courseid 159 = ES11611)
    				// 				[relateduserid] => 12983		(userid of user dropped in cohort)
    				// 				[anonymous] => 0
    				// 				[other] => Array
    				// 					(
    				// 						[enrol] => cohort
    				// 					)
                    //
    				// 				[timecreated] => 1547760579
    				// 			)
                    //
    				// 		[logextra:protected] =>
    				// 		[context:protected] => context_course Object
    				// 			(
    				// 				[_id:protected] => 3819
    				// 				[_contextlevel:protected] => 50
    				// 				[_instanceid:protected] => 159			(courseid 159 = ES11611)
    				// 				[_path:protected] => /1/511/513/514/3819
    				// 				[_depth:protected] => 5
    				// 			)
                    //
    				// 		[triggered:core\event\base:private] => 1
    				// 		[dispatched:core\event\base:private] => 1
    				// 		[restored:core\event\base:private] =>
    				// 		[recordsnapshots:core\event\base:private] => Array
    				// 			(
    				// 				[user_enrolments] => Array
    				// 					(
    				// 						[139952] => stdClass Object
    				// 							(
    				// 								[id] => 139952
    				// 								[status] => 0
    				// 								[enrolid] => 4887
    				// 								[userid] => 12983
    				// 								[timestart] => 0
    				// 								[timeend] => 0
    				// 								[modifierid] => 4
    				// 								[timecreated] => 1547760579
    				// 								[timemodified] => 1547760579
    				// 								[enrol] => cohort
    				// 								[courseid] => 159
    				// 							)
    				// 					)
    				// 			)
    				// 	)
                    // SWTC ********************************************************************************
                    case '\core\event\user_enrolment_created':
                        $debug->logmessage("Admin has triggered an enrollment in a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************
                    // Event - all others
                    // SWTC ********************************************************************************
                    default:
                        $debug->logmessage("Something happened. Log it. (eventname is <strong>$eventname</strong>).", 'both');
                        break;
                }
    		}

            // Set the users userid and access_type.
            // 07/12/18 - Added call to swtc_get_relateduser.
    		// 07/18/18 - Set $user_related to $swtc_user->relateduser (otherwise $user_related is NULL).
            $user_related = swtc_user_get_relateduser($eventdata->relateduserid);   // 12/04/18
            $swtc_user->relateduser = $user_related;       // 12/04/18

            // 07/18/18 - Set $user_related to $swtc_user->relateduser (otherwise $user_related is NULL).	// 01/10/19 - Moved above.
            // $user_related = $swtc_user->relateduser;	// 01/10/19

            if (isset($debug)) {
                $messages[] = "In top of assign_user_role (relateduserid). Setting swtc_user->relateduser information of $eventdata->relateduserid. ===11===.";
                $messages[] = "swtc_get_relateduser follow:";
                $messages[] = print_r($swtc_user->relateduser, true);
                $messages[] = "swtc_get_relateduser end.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            if (isset($debug)) {
                $messages[] = "In top of assign_user_role (relateduserid). After setting swtc_user to new values.";
                $messages[] = "swtc_user array follows :";
                $messages[] = print_r($swtc_user, true);
                $messages[] = "swtc_user array ends.";
                $debug->logmessage($messages, 'both');
                unset($messages);
            }
        } else {
            // SWTC ********************************************************************************
            // 04/23/18: Since $swtc_user->userid and $swtc_user->user_access_type should already be set by now,
            //                  removing this section.
            // SWTC ********************************************************************************
            // Set the users userid and access_type.
            //  if (!isset($swtc_user->userid)) {
            //      if (isset($debug)) {
            //          $debug->logmessage("In top of assign_user_role. assigning userid of $eventdata->userid. ===11===.", 'logfile');
            //      }
            //      $swtc_user->userid = $eventdata->userid;
            //      // $swtc_user->userid = $eventdata['other']['username'];      // TODO
            //
            //      // Get the customized user profile field "Accesstype".
            //      $swtc_user->user_access_type = $USER->profile['Accesstype'];
            //      $swtc_user->timestamp = swtc_timestamp();
            //  }
    	}

    	if (isset($debug)) {
    		$messages[] = "The userid that will be used throughout this plugin is :<strong>$swtc_user->userid</strong>.";
            $messages[] = "The username that will be used throughout this plugin is :<strong>$swtc_user->username</strong>.";
    		$messages[] = "The user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
            $messages[] = "The timestamp is :<strong>$swtc_user->timestamp</strong>.";
            $messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid, admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

        // SWTC ********************************************************************************
        // Removed a section of code, comments, or both. See archived versions of module for information.
        // SWTC ********************************************************************************


    	// SWTC ********************************************************************************
        // For each of the messages being captured, get the user access type, role,  and category id they SHOULD have access to (function below).
    	//
    	//			The $swtc_user is returned. It is a multidimensional array that has the following format (Note: roleid will be loaded later):
    	//			$access = array(
    	//					'portfolio'=>'',
    	//					'roleshortname'=>'',
    	//					'roleid'=>'',
        //					'categoryid's = array(
        //                      )
    	//			);
    	//
        // SWTC ********************************************************************************
        // No need to return access (values set directly in EBGLMS) nor allroles (called again in swtc_load_roleids).
        //  No need to send $swtc_user->user_access_type as that is part of EBGLMS.
        //      Also update timestamp.
        // SWTC ********************************************************************************
        list($catlist, $tmp_user) = $this->get_user_access();
    	// print_object("assign_user_role; about to print catlist");       // SWTC-debug
    	// print_object($catlist);		// SWTC-debug

        // SWTC ********************************************************************************
        // Finished. Set $swtc_user to all the appropriate values.
        //      And set new timestamp.
        // SWTC ********************************************************************************
        // 07/12/18 - Added check if $user_related is set. If so, load that access information. Otherwise, load $swtc_user.
        if (isset($user_related)) {
            $user_related->portfolio = $tmp_user->portfolio;
            $user_related->roleshortname = $tmp_user->roleshortname;
            $user_related->categoryids = $tmp_user->categoryids;
            $user_related->capabilities = $tmp_user->capabilities;
            $user_related->roleid = $tmp_user->roleid;
            $user_related->timestamp = swtc_timestamp();
        } else {
            $swtc_user->portfolio = $tmp_user->portfolio;
            $swtc_user->roleshortname = $tmp_user->roleshortname;
            $swtc_user->categoryids = $tmp_user->categoryids;
            $swtc_user->capabilities = $tmp_user->capabilities;
            $swtc_user->roleid = $tmp_user->roleid;
            $swtc_user->timestamp = swtc_timestamp();
        }
        // print_r("after first call to isset - user_related.\n");   // 11/30/18 - RF - testing...
        // print_object($user_related);
        // print_r("after first call to isset - swtc_user.\n");   // 11/30/18 - RF - testing...
        // print_object($swtc_user);     // At this point, swtc_user is messed up.

        // SWTC ********************************************************************************
    	// Note: At this point the $catids array should be fully created...
        // SWTC ********************************************************************************
    	if (isset($debug)) {
    		// $messages[] = "catlist array follows: ";
            // $messages[] = print_r($catlist, true);
            // $messages[] = "catlist array ends.";
            $messages[] = "swtc_user array follows: ";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	//	die();
    	}

        // SWTC ********************************************************************************
    	// Special case check - For each course that has self-enrollment enabled, the Administrator defines a default role for each
        //      self-enrollment instance.
    	//		In most cases, the default role is fine (students enrolling as students). However, in some cases, the user's role in course must be changed
    	//		(for example, student changing to instructor).
    	//
    	//	If the user is enrolling in a course (self-enrollment):
    	//		Check to see if the current user is student (and not an Administrator acting on behalf of the student). In other words, if an Administrator is
    	//					enrolling the user, whatever role the Administrator gives the user is fine).
    	//		Check to see if they are enrolled as the correct type of user (for example, for a course in the IBM portfolio, the self-enrollment default role
    	//					is 'IBM-student'). However, a user with a role of 'Lenovo-instructor' can enroll in the course. So, the 'Lenovo-instructor' role must be
    	//					assigned and the 'IBM-student' role must be unassigned.
    	//		In other words, if the user's role in the course is different than what is in their user profile ("Accesstype"), assign correct
        //          role / unassign incorrect role.
    	//
    	//	Important - Any roles unassigned or assigned in the 'main' course flow to any metacourses linked to the 'main' course. In other words,
    	//			any roles that need to be unassigned or assigned must be done in the 'main' course only (not in the metacourse) - no need to do anything
    	//			in any metacourses.
    	//
    	//		Notes:
    	//			Remember to check if the current user is the student (and not an Administrator acting on behalf of the student).
    	//			Remember that once the student self-enrolls in a course, if a metalink exists to that course, an automatic enrollment is processed
        //              for each metacourse (in other words, the student does NOT enroll in any metacourses). Because two classes are enrolled, two
        //              '\core\event\role_assigned' messages are generated - one for each course.
    	//			Check to see which course the '\core\event\role_assigned' message is associated with. If it is for the 'main' course, continue. If it is for any
    	//				metacourses, ignore them.
    	//			Remember that the user cannot enroll themself in the 'Shared resources (Master)' course (because enrollment is via a course metalink).
        //              Therefore, after checking, if the user is not enrolled in course, we must return without doing anything and the user will get an
        //              error.***not sure of this***
    	//
        // SWTC ********************************************************************************
    	if ($eventname == '\core\event\role_assigned') {

    		// Special case - If $courseid is 0, return.
    		if ($eventdata->courseid == 0) {
    			return;
    		}

    		if (isset($debug)) {
    			$messages[] = "=====================";
    			$messages[] = "In role_assigned message ===11.5===.";
    			$messages[] = "About to print all of eventdata ===11.5===.";
                $messages[] = print_r($eventdata, true);
    			// print_object($eventdata, true);
    			$messages[] = "Finished printing eventdata ===11.5===.";
                // $messages[] = "About to print all of COURSE ===11.5===.";
                // $messages[] = print_r($COURSE, true);
    			// $messages[] = "Finished printing COURSE ===11.5===.";
                $messages[] = "About to print COURSE->category ===11.5===.";
                $messages[] = print_r($COURSE->category, true);
    			$messages[] = "Finished printing COURSE->category ===11.5===.";
    			$messages[] = "eventname is :<strong>$eventname</strong>.";
    			$messages[] = "eventdata->courseid is :$eventdata->courseid.";
    			$messages[] = "eventdata->userid is :$eventdata->userid.";
                $messages[] = "eventdata->relateduserid is :$eventdata->relateduserid.";
                $debug->logmessage($messages, 'logfile');
                unset($messages);
    		}

    		// Get the courseid that the user has enrolled in.
    		$courseid = $eventdata->courseid;

    		// Create a context to the course.
    		$context = context_course::instance($courseid, MUST_EXIST);

    		if (isset($debug)) {
    			$debug->logmessage("User has enrolled in course (courseid :<strong>$courseid</strong>).", 'logfile');
    			$debug->logmessage("About to print courseid $courseid context.", 'logfile');
                $debug->logmessage(print_r($context, true), 'logfile');
    			// print_object($context, true);
    			// $DB->set_debug(true);
    		}

    		// Get the role assignments of the user in the course (get_records on course or metacourse depending on is_meta value).
    		$ras = $DB->get_records('role_assignments', array('userid'=>$swtc_user->userid, 'contextid'=>$context->id));

    		// SWTC ********************************************************************************
    		// The default role for a student self-enrolling in a course depends on what portfolio the course is in. In any event, delete whatever it is
            //      and add what the user's 'real' role should be.
    		//
    		//		Note: $access is set for each user in code above.
            //          And update timestamp.
            // SWTC ********************************************************************************
            // SWTC ********************************************************************************
            // Get the top-level category for this catid.
            // SWTC ********************************************************************************
            $topcatid = swtc_toplevel_category($COURSE->category);
            // Look for key in catlist array.
            $key = array_search($topcatid, array_column($catlist, 'catid'));
            // $topcatname = $catlist[$key]['catname'];		// 01/10/19
    		$topcat = $catlist[$key];			// 01/10/19

            // SWTC ********************************************************************************
            // 07/18/18 - Added check if user_related is set.
            //                  If so, use that user information to determine access; to make sure all changes are made, changing swtc_user to temp_user
            //                  (since no information is saved in this section); remember to unset SESSION->SWTC->USER->relateduser at the end.
            // SWTC ********************************************************************************
            if (isset($user_related)) {
                $temp_user = $user_related;
            } else {
                $temp_user = $swtc_user;
            }

            // print_r("after second call to isset - user_related.\n");   // 11/30/18 - RF - testing...
            // print_object($temp_user);

            // SWTC ********************************************************************************
            // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
            // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
            // SWTC ********************************************************************************
    		// print_object("about to call 1");
            // $temp_user = local_swtc_change_user_access($topcatname, $temp_user);		// 01/10/19
    		local_swtc_change_user_access($topcat, $temp_user);			// 01/10/19
    		// print_object($temp_user);
    		if (isset($debug)) {
    			// $DB->set_debug(false);
    			$debug->logmessage("Correct roleid is :<strong>$temp_user->roleid</strong>.", 'logfile');
    			$debug->logmessage("Printing ras before deleting incorrect role.", 'logfile');
                $debug->logmessage(print_r($ras, true), 'logfile');
    			// print_object($ras, true);
    			$debug->logmessage("Finished printing ras before deleting incorrect role.", 'logfile');
    		}

            // SWTC ********************************************************************************
    		// Loop through each role assignment record the user has in the course and, if $ra->roleid is different than $temp_user->roleid,
            //      delete the enrollment record.
            // SWTC ********************************************************************************
    		foreach($ras as $ra) {
    			//if (isset($debug)) {
    			//	$debug->logmessage("Printing enrollment record:", 'logfile');
    			//	print_object($ra, true);
    			//	$debug->logmessage("Finished printing enrollment record.", 'logfile');
    			//}

    			// If the roleid assigned is different than what it should be, delete it.
    			if (($ra->userid == $temp_user->userid) && ($ra->roleid != $temp_user->roleid)){
    				$DB->delete_records('role_assignments', array('id'=>$ra->id));
    			}
    		}

    		if (isset($debug)) {
    			$tmp = $DB->get_records('role_assignments', array('userid'=>$temp_user->userid, 'contextid'=>$context->id));
    			$debug->logmessage("Printing ras after deleting incorrect role.", 'logfile');
                $debug->logmessage(print_r($tmp, true), 'logfile');
    			// print_object($tmp, true);
    			$debug->logmessage("Finished printing ras after deleting incorrect role.", 'logfile');
    		}

    		// SWTC ********************************************************************************
    		// Finally, assign the user to the correct role in the course.
    		// SWTC ********************************************************************************
    		if ( !$ra = role_assign($temp_user->roleid, $temp_user->userid, $context->id)) {
    			if (isset($debug)) {
    				$newshortname = $temp_user->roleshortname;
    				$debug->logmessage("Error assigning <strong>$newshortname</strong> in course <strong>$courseid</strong>. Returning.", 'logfile');
    				return;
    			}
    		}

    		if (isset($debug)) {
    			$tmp = $DB->get_records('role_assignments', array('userid'=>$temp_user->userid, 'contextid'=>$context->id));
    			$debug->logmessage("Printing ras after assigning new role.", 'logfile');
                $debug->logmessage(print_r($tmp, true), 'logfile');
    			// print_object($tmp, true);
    		}

    		// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
    		$context->mark_dirty();
    		purge_all_caches();

    		if (isset($debug)) {
    			$debug->logmessage("Leaving role_assigned message ===11.5===.", 'logfile');
    		}

    		return;
    	}

        // SWTC ********************************************************************************
    	//	At this point, we've determined all the roles defined in the system (above), all the categories in the system (above), and (most importantly)
    	// 			the role the user 'should ' have in the category.
    	//
    	// Part 4 of 4 - At this point, we've determined all the roles defined in the system, all the categories in the system, and (most importantly)
    	// 			the role the user 'should' have in the category. Nothing left to do, but check to see if the user really does have that capability
        //              in that category.
    	//			If not, assign it to them. Search for name of capability and set the master capability variable.
    	//			If they have a role in a category they shouldn't, remove them from the role.
    	//
    	//			Logic flow is the following:
    	//				For each of the top-level categories (all top-level categories are checked each time)
    	//					Should user have access? (if 'catname' == access['catname or if 'catname' == ALL)
    	//						If yes
    	//							Add the capability
    	//						If no
    	//							Remove the capability
    	//
    	//	Remember! $catlist array format is below (defined in function get_user_access):
    	//			Array
    	//		(
    	//			[0] => Array
    	//				(
    	//					[catid] => 14
    	//					[catname] => GTP Portfolio
    	//					[context] => context_coursecat Object
    	//						(
    	//							[_id:protected] => 345
    	//							[_contextlevel:protected] => 40
    	//							[_instanceid:protected] => 14
    	//							[_path:protected] => /1/345
    	//							[_depth:protected] => 2
    	//						)
    	//					[capability] => local/swtc:ebg_access_gtp_portfolio
    	//					[roles] => Array
    	//						(
    	//							[gtp-instructor] => 13
    	//							[gtp-student] => 14
    	//							[gtp-administrator] => 16
    	//						)
    	//				)
    	//
    	// Main loop ("For each of the top-level categories defined on the site...").
        //
        //  07/11/18 - Changed PremierSupport roles to only have PremierSupport-student roles outside the PremierSupport portfolio
        //                      (even administrators and managers). This is to prevent PremierSupport admins and mgrs from having more access
        //                      to a course when they are enrolled in a course outside the PremierSupport portfolio.
        // 07/12/18 - Remember to skip roles returned with contextid = 1 (which is System context); added check if user_related is set.
        //                  If so, use that user information to determine access; to make sure all changes are made, changing swtc_user to temp_user
        //                  (since no information is saved in this section); remember to unset SESSION->SWTC->USER->relateduser at the end.
        // SWTC ********************************************************************************
        if (isset($user_related)) {
            $temp_user = $user_related;
        } else {
            $temp_user = $swtc_user;
        }

        // print_r("after third call to isset - user_related.\n");   // 11/30/18 - RF - testing...
        // print_object($user_related);

    	foreach ($catlist as $key => $catlist['catid']) {

    		// Check to see if the user has any roles assigned to this top-level category. Save for several checks later...
            // 07/12/18 - Added checkparentcontexts as false (to remove System contextid).
    		// $userroles = get_user_roles($catlist[$key]['context'], $swtc_user->userid);
    		// print_object("key is :$key");		// 01/10/19
            $userroles = get_user_roles($catlist[$key]['context'], $temp_user->userid, false);
    		$countroles = count($userroles);

    		if (isset($debug)) {
    			$temp = $catlist[$key]['catname'];
    			if ($countroles != 0) {
    				$messages[] = "Userid <strong>$temp_user->userid DOES</strong> have userroles in <strong>$temp</strong>.";	// 12/11/18
    				$messages[] = "The number of roles userid <strong>$temp_user->userid has assigned in <strong>$temp</strong> is <strong>==>$countroles<==</strong>.";		// 12/11/18
    				$messages[] = "Next is to check if they SHOULD have access.";		// 12/11/18
    				$messages[] = "About to print userroles.";
                    $messages[] = print_r($userroles, true);
    				// print_object($userroles, true);
                    $messages[] = "Finished printing userroles. About to print context.";
                    $messages[] = print_r($catlist[$key]['context'], true);
                    $messages[] = "Finished printing context.";
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
    			} else {
    				$debug->logmessage("Userid <strong>$temp_user->userid</strong> does <strong>NOT</strong> have any roles in <strong>$temp</strong>.", 'both');
    			}
    		}

    		$catid = $catlist[$key]['catid'];

            // SHOULD the user have access to this category?" (If the category id's match, enter).
            // SWTC ********************************************************************************
            // Removed a section of code, comments, or both. See archived versions of module for information.
            // SWTC ********************************************************************************
    		// $context = context_coursecat::instance($catid);		// 01/10/19
            if (isset($debug)) {
                $messages[] = "catid to search for is $catid.";
                // $cat = $temp_user->categoryids[array_search($catid, array_column($temp_user->categoryids, 'catid'))]['catid'];
                // print_object(array_column($temp_user->categoryids, 'catid'));
                if (array_search($catid, array_column($temp_user->categoryids, 'catid')) !== false) {
                    $messages[] = "I found cat $catid.";
                } else {
                    $messages[] = "I did NOT find cat $catid.";
                }
    			// $messages[] = "About to call has_capability to determine access. 01/10/19";		// 01/10/19
    			// $messages[] = "capability to check follows :";		// 01/10/19
    			// $messages[] = print_r($catlist[$key]['capability'], true);
    			// $messages[] = "capability finished. context to check follows :";		// 01/10/19
    			// $messages[] = print_r($context, true);
    			// $messages[] = "context finished.";		// 01/10/19
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            // 11/08/18 - Fix after trying to put into production.
    		// 01/10/19 - Changing to has_capability call; testing...not working...going back...
            if (array_search($catid, array_column($temp_user->categoryids, 'catid')) !== false) {
    			$temp = $catlist[$key];		// 01/10/19

    			if (isset($debug)) {
    				// $temp = $catlist[$key]['catname'];   // 11/27/18 - Moved above.
    				$catname = $catlist[$key]['catname'];
    				$debug->logmessage("User <strong>$temp_user->userid SHOULD</strong> have access to category <strong>$catname</strong>.", 'logfile');
    			}

                // SWTC ********************************************************************************
    			// Does the current user have any roles assigned in this category? If so, check to make sure it's the CORRECT role.
    			//		What does CORRECT role mean? The CORRECT role would be the one that the user should have been assigned
    			//			(based on the 'Access type' flag).
    			//		Note: The only way for a user to have more than one role assigned to them in a top-level category is if an administrator
    			//			purposely did it (since 'Access type' is a single-select, it is impossible to get more than one role from it). For example, a user
    			//			was given the GTP-student AND GTP-instructor role in the 'GTP Portfolio' top-level category.
                // SWTC ********************************************************************************
    			if ($countroles != 0) {

                    // $temp = $catlist[$key]['catname'];		// 01/10/19 - Not needed; set above.

    				if (isset($debug)) {
    					$debug->logmessage("And <strong>DOES</strong>. Next is to check if it is the CORRECT access.", 'logfile');
    				//	print_object($userroles, true);
    				}
    				// Does the current user have the CORRECT role assigned in this category?
    				// For each of the roles, if $role->id == $swtc_user->roleid, the user has the correct access. If they don't match,
                    //      remove the user from the role.
    				foreach ($userroles as $role) {

                        // SWTC ********************************************************************************
                        // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                        // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                        // SWTC ********************************************************************************
    					// print_object("about to call 2");
                        $swtc_user->change_user_access($temp, $temp_user);

                        if ($role->roleid != $temp_user->roleid) {

    						if (isset($debug)) {
    							$tempid = $temp_user->roleid;
    							$tempname = $temp_user->roleshortname;
    							$messages[] = "However, user <strong>$temp_user->userid</strong> has been given an incorrect role. It should be <strong>$tempid ($tempname)</strong> but is <strong>$role->roleid ($role->shortname)</strong>.";
    							$messages[] = "Action: will remove user from role <strong>$role->roleid</strong>; will add user to role <strong>$tempid</strong>.";
                                $messages[] = "parameters to role_unassign are :role->roleid is $role->roleid; temp_user->userid is $temp_user->userid; catlist[$key]['context']->id is :";
                                $messages[] = print_r($catlist[$key]['context']->id, true);
                                $debug->logmessage($messages, 'both');
                                unset($messages);
    						}

    						// Unassign the user from the incorrect role...
                            role_unassign($role->roleid, $temp_user->userid, $catlist[$key]['context']->id);

    						// Assign the user to the correct role...
                            role_assign($temp_user->roleid, $temp_user->userid, $catlist[$key]['context']->id);

    						// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
    						$catlist[$key]['context']->mark_dirty();

                            // 07/13/18 - If the above role_unassign followed by role_assign worked, or didn't work, the user would STILL have roles
                            //                  in this category. So, there is not much gain in checking access again. So, just continue.
    					} else {
                            if (isset($debug)) {
                                $debug->logmessage("It is the correct access.", 'logfile');
                            }
                        }
    				}
    			} else {
    				// The user SHOULD have access; the user does NOT have a role assigned in the category. Add the user to the role.
                    // SWTC ********************************************************************************
                    // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                    // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                    // SWTC ********************************************************************************
    				// print_object("about to call 3");
                    local_swtc_change_user_access($temp, $temp_user);		// 01/10/19

    				if (isset($debug)) {
    					$temp = $catlist[$key]['catname'];
    					$messages[] = "User <strong>$temp_user->userid</strong> does <strong>NOT</strong> have role <strong>$temp_user->roleshortname</strong> in category <strong>$temp</strong>.";
    					$messages[] = "Action: will add user role to category.";
                        $messages[] = "temp_user follows:";
                        $messages[] = print_r($temp_user, true);
                        $messages[] = "Finished printing temp_user.";
                        $debug->logmessage($messages, 'both');
                        unset($messages);
    				}

    				$id = role_assign($temp_user->roleid, $temp_user->userid, $catlist[$key]['context']->id);

    				// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
    				$catlist[$key]['context']->mark_dirty();
    			}
    		} else {
    			// The user should NOT have access to this category. Do they? If so, remove them from the role.
    			if (isset($debug)) {
    				$debug->logmessage("countroles is :<strong>$countroles</strong>.", 'logfile');
    				$temp = $catlist[$key]['catname'];
    				$debug->logmessage("User <strong>$temp_user->userid</strong> should <strong>NOT</strong> have access to category <strong>$temp</strong>. ", 'logfile');
    			}

    			// If the user has roles in this category...
    			if ($countroles != 0) {

    				if (isset($debug)) {
    					$temp = $catlist[$key]['catname'];
    					$debug->logmessage("However, user <strong>$temp_user->userid DOES</strong> have access to category <strong>$temp</strong>.", 'logfile');
    					$debug->logmessage("Action: will remove the user from the role.", 'logfile');
    					$debug->logmessage("userroles array follows (before removing):", 'logfile');
                        $debug->logmessage(print_r($userroles, true), 'logfile');
    					// print_object($userroles, true);
    					$debug->logmessage("temp_user->roleid is:", 'logfile');
                        $debug->logmessage(print_r($temp_user->roleid, true), 'logfile');
    					// print_r($swtc_user->roleid);
    					$debug->logmessage("", 'logfile');
    				}

    				// For each of the roles, remove the user from it.
    				foreach ($userroles as $role) {
    					// 08/27/16 - The user may have the correct role (for example, IBM-student), but might have been accidentally given
                        //      access to an "off limits" portfolio  (for example, GTP-Portfolio). In this case, if the current portfolio
                        //      is not one of the one's in the list, remove the user from it.
                        //
                        //      Removing "if ($role->roleid != $swtc_user->roleid)" condition.
    					role_unassign($role->roleid, $temp_user->userid, $catlist[$key]['context']->id);

    					// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
    					$catlist[$key]['context']->mark_dirty();
    				}

                    // Get a new count of the roles assigned to this top-level category.
                    // 07/12/18 - Added checkparentcontexts as false (to remove System contextid).
                    $updated_userroles = get_user_roles($catlist[$key]['context'], $temp_user->userid, false);
                    $updated_countroles = count($updated_userroles);

                    // If the user STILL has roles in this category...
                    if ($updated_countroles != 0) {
                        if (isset($debug)) {
                            $messages[] = "Unable to remove <strong>$temp_user->userid access to category <strong>$temp</strong>.";
                            $messages[] = "updated_userroles array follows (after failed removal):";
                            $messages[] = print_r($updated_userroles, true);
                            $debug->logmessage($messages, 'both');
                            unset($messages);
                        }
                    } else {
                        // The user now has no roles in this category.
                        if (isset($debug)) {
                            $messages[] = "User <strong>$temp_user->userid successfully removed access to category <strong>$temp</strong>.";
                            $messages[] = "updated_userroles array follows (after removing):";
                            $messages[] = print_r($updated_userroles, true);
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

        // print_r("about to return from assign_user_role.\n");
        // print_r("printing SESSION->SWTC->USER.\n");   // 11/30/18 - RF - testing...
        // print_object($SESSION->SWTC->USER);       // At this point, USER is still messed up.

        // 07/12/18 - Remember to unset SESSION->SWTC->USER->relateduser at the end.
        // 11/30/18 - RF - testing...tried commenting out; didnt change anything; putting back in.
        if (isset($SESSION->SWTC->USER->relateduser)) {
            unset($SESSION->SWTC->USER->relateduser);
            unset($user_related);
        }

        // print_r("printing SESSION->SWTC->USER - again.\n");   // 11/30/18 - RF - testing...
        // print_object($SESSION->SWTC->USER);


    	// Invalidate the data so that the user does not need to logoff and log back in to see changed roles...
    	purge_all_caches();
    	// print_object($swtc_user);     // 11/08/19 - Lenovo debugging...
    	if (isset($debug)) {
    		$debug->logmessage("Leaving assign_user_role.exit===11===.", 'logfile');
    	}

    }

    // SWTC ********************************************************************************
    // Get the logged in user customized user profile value 'Accesstype'. Accesstype is  used to determine which portfolio of classes
    // the user should have access to (in other words, which top-level category they should have access to). Note that this function returns the
    //	information the user 'should' have access to. What the user actually has access to (and whether they need more or less access) is
    //  determined above.
    // 			Important! Case of Accesstype is important. It must match the case defined in Moodle.
    // 			Returns array: first element portfolio value; second element the user's role shortname (i.e. 'ibm-student' or 'gtp-administrator');
    //				third element is the top-level category id the user 'should' have access to (checked above).
    //
    // SWTC ********************************************************************************
    /**
     * Used get the users access.
     *
     * @param N/A
     *
     * @return $array   The catlist array.
     * @return $array   An array of values used to set $SESSION->SWTC->USER.
     *
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    function get_user_access() {
    	global $USER, $SESSION;

        // SWTC ********************************************************************************.
        // SWTC LMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_set_debug();

        // Other Lenovo variables.
        $cats = array();						        // A list of all the top-level category information defined (returned to assign_user_role).
        $temp_user = new stdClass();    // Returned to calling function.

        // Temporary variables. Use these during the function and return values.
        $roleshortname = null;
        $portfolio = null;
        $categoryids = array();		    // A list of all the categories the user should have access to (set in $swtc_user->categoryids).
        $capabilities = array();		    // A list of all the capabilities the user should have (set in $swtc_user->capabilities).
        $roleid = null;

        // SWTC ********************************************************************************
        // 07/12/18 - Added check if swtc_user->relateduser is set. If so, use that user information to determine access.
        //                  Note that no switching of users below should be necessary.
        // SWTC ********************************************************************************
        if (isset($SESSION->SWTC->USER->relateduser)) {
            $swtc_user = $SESSION->SWTC->USER->relateduser;
            $user_access_type = $SESSION->SWTC->USER->relateduser->user_access_type;
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "swtc_user->relateduser is set; the userid that will be used throughout get_user_access is :<strong>$swtc_user->userid</strong>.";
            $messages[] = "swtc_user->relateduser is set; the username that will be used throughout get_user_access is :<strong>$swtc_user->username</strong>.";
    		$messages[] = "swtc_user->relateduser is set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
        } else {
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "swtc_user->relateduser is NOT set; the userid that will be used throughout get_user_access is :<strong>$swtc_user->userid</strong>.";
            $messages[] = "swtc_user->relateduser is NOT set; the username that will be used throughout get_user_access is :<strong>$swtc_user->username</strong>.";
    		$messages[] = "swtc_user->relateduser is NOT set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
            $swtc_user = $SESSION->SWTC->USER;
            $user_access_type = $SESSION->SWTC->USER->user_access_type;
        }
        // SWTC ********************************************************************************.

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            // $messages[] = "Lenovo ********************************************************************************.";
            // $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            // $phplog = debug_enable_phplog($debug, "In get_user_access.");
            $debug->logmessage($messages, 'both');
            unset($messages);

    		// SWTC ********************************************************************************
            // Additional diagnostic information.
            // SWTC ********************************************************************************
            // $messages[] = "About to print strings.";
            // $messages[] = print_r($strings, true);
            // $messages[] = "Finished printing strings.";
            // $debug->logmessage($messages, 'detailed');
            // unset($messages);
    	}

        // SWTC ********************************************************************************
    	// We already know what the user's role should be (either they have it or need it assigned to them from above).
        //      And we've loaded all the roles defined in the system (Part 1). However, we don't know the specific roleid ($role->id) assigned
        //      to that role name. In the $roles array, search for the role ($access[roleshortname]) that the user should have. Once found,
        //      save the $role->id ($userroleid) in $swtc_user->roleid. Later, we will use this array to list all the other roles the user
        //      should NOT have and remove them.
        // SWTC ********************************************************************************
        // Load all the roles (context is not needed (see below)). The returned value, $roles, is an array that has the following format:
    	// 			[11] => stdClass Object
    	//		    (
    	//			        [id] => 11
    	//			        [name] => Lenovo-instructor
    	//			        [shortname] => lenovo-instructor
    	//			        [description] => A Lenovo instructor.
    	//			        [sortorder] => 12
    	//			        [archetype] => teacher
    	//			        ***[localname] => Lenovo-instructor - field not returned using get_all_roles()
    	//			    )
    	//		 01/23/16 - Don't think the instance is needed: $context = context_coursecat::instance(CONTEXT_COURSECAT);
    	//		 		Changing role_get_names() to get_all_roles().
    	//		 			get_all_roles() defined in /lib/accesslib.php. Returns array of all the defined roles (just like role_get_names),
        //                      except it does not contain the role localname field (that field is added by role_fix_names()). It DOES contain
        //                      the role shortname field (that we use later). Note: get_all_roles() does NOT need a context to be passed to return
        //                      all the defined roles (also doesn't matter what type of user that runs it).
        //
    	//		09/27/16 - Important! Hidden dependency is that the role name and the role shortname must match!
        // SWTC ********************************************************************************
    	$roles = get_all_roles();

        // SWTC ********************************************************************************
        $cats = $this->loadcatids($roles);

        // SWTC ********************************************************************************
    	// Note: At this point the $cats array should be fully created...
        // SWTC ********************************************************************************
        //if (isset($debug)) {
        //    $messages[] = "cats array follows:";
        //    // $messages[] = print_object($cats, true);
        //    $messages[] = print_r($cats, true);
        //    $messages[] = "cats array ends.";
        //    $debug->logmessage($messages, 'detailed');
        //    unset($messages);
        ////  //	die();
        //}

        // SWTC ********************************************************************************
    	// Determine what portfolio the user should be able to view based on value in access_type
    	// Important! Since the switch statement is using the EXACT $access_xxx_yyy stirngs for comparison to the Accesstype flag,
    	//		they must be defined that way in the /lang/en/local_swtc.php file...
        //
    	// 03/29/16 - Even though all users might use a shared resource, no users should have direct access to
        //                  'Lenovo Shared Resources (Master)' except Lenovo-admins.
    	//	08/31/16 - Adding Lenovo-stud and Lenovo-inst to Maintech Portfolio.
        // SWTC ********************************************************************************
    	if (isset($debug)) {
    		$messages[] = "swtc_user array follows: ";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends. user_access_type to check is:  $user_access_type";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    		// print_object($swtc_user, true);
            // die();
    	}

        // SWTC ********************************************************************************
        // Switch on the users access type.
        //
        // Sets variables:
    	//			$swtc_user->roleshortname     The actual name of the role the user has.
        //			$swtc_user->portfolio      The name of the portfolio the user has access to.
        //			$swtc_user->categoryids    An array of category ids the user has access to.
        //			$swtc_user->capabilities   An array of capabilities the user has.
        //
        // SWTC ********************************************************************************

    	// SWTC ********************************************************************************
    	// Check for Lenovo-admin, Lenovo-inst, or Lenovo-stud user
    	// SWTC ********************************************************************************
    	if ((stripos($user_access_type, get_string('access_lenovo_admin', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_inst', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_stud', 'local_swtc')) !== false)) {

    		if (stripos($user_access_type, get_string('role_lenovo_admin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_lenovo_administrator', 'local_swtc');
    			$portfolio = get_string('lenovo_portfolio', 'local_swtc');

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovointernal_portfolio', 'local_swtc'), $cats);

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovosharedresources_portfolio', 'local_swtc'), $cats);

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

                list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('curriculums_portfolio', 'local_swtc'), $cats);

    		} else if (stripos($user_access_type, get_string('role_lenovo_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_lenovo_instructor', 'local_swtc');
    				$portfolio = get_string('lenovo_portfolio', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_lenovo_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_lenovo_student', 'local_swtc');
    				$portfolio = get_string('lenovo_portfolio', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    		// 07/23/18 - Added access to PremierSupport portfolio for Lenovo-administrators until GA.
    		// 11/30/18 - Changed access type names for ServiceDelivery and added access for the appropriate user types; modified
    		//                      access for PremierSupport.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('premiersupport_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('servicedelivery_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_av_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_im_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_lq_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for IBM-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_ibm_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('ibm_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_ibm_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for ServiceProvider-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_serviceprovider_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_serviceprovider_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for Maintech-stud user
        // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/get_user_access to fix
        //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
    	// SWTC ********************************************************************************
    	// } elseif (stripos($user_access_type, get_string('access_maintech_stud', 'local_swtc')) !== false) {      // 11/25/19
        } elseif (strncasecmp($user_access_type, get_string('access_maintech_stud', 'local_swtc'), strlen($user_access_type)) == 0) {   // 11/25/19

    		$portfolio = get_string('maintech_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_maintech_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for ASP-Maintech-stud user
        // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/get_user_access to fix
        //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
    	// SWTC ********************************************************************************
    	// } elseif (stripos($user_access_type, get_string('access_asp_maintech_stud', 'local_swtc')) !== false) {      // 11/25/19
        } elseif (strncasecmp($user_access_type, get_string('access_asp_maintech_stud', 'local_swtc'), strlen($user_access_type)) == 0) {   // 11/25/19

    		$portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_asp_maintech_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for PremierSupport users
    	// 05/16/18 - For testing, added PremierSupport-mgr1, PremierSupport-mgr2, and PremierSupport-mgr3 roles.
    	// 11/07/18 - Added additional access type strings for all PremierSupport user types.
    	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
    	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
    	// SWTC ********************************************************************************
    	} else if ((preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {

    		$portfolio = get_string('premiersupport_portfolio', 'local_swtc');

    		if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    			$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		if (has_capability($cats[array_search(get_string('premiersupport_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['capability'],
    			$cats[array_search(get_string('premiersupport_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['context'])) {
    			// $debug->logmessage("===Yes===", 'detailed');
    		} else {
    			// $debug->logmessage("===No===", 'detailed');
    		}

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('premiersupport_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);
    		// print_object("did I get here??");       // 11/20/19 - Lenovo debugging...
            // print_object($categoryids);
    	// SWTC ********************************************************************************
    	// Check for ServiceDelivery users
    	// 11/15/18 - Added additional access type strings for all ServiceDelivery user types.
    	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
    	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
    	// SWTC ********************************************************************************
    	} else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
    		$portfolio = get_string('servicedelivery_portfolio', 'local_swtc');

    		if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_madmin', 'local_swtc'), $user_access_type)) {
    			$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		if (has_capability($cats[array_search(get_string('servicedelivery_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['capability'],
    			$cats[array_search(get_string('servicedelivery_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['context'])) {
    			// $debug->logmessage("===Yes===", 'detailed');
    		} else {
    			// $debug->logmessage("===No===", 'detailed');
    		}

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('servicedelivery_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for Self support user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_selfsupport_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('none_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_selfsupport_student', 'local_swtc');

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);
    		// $categoryids[] = 'none';
    		// $capabilities[] = 'none';

    	// SWTC ********************************************************************************
    	// Check for Special access user
    	// SWTC ********************************************************************************
    	// case $access_special_user:
    	// case $access_specialaccess_stud:
    	//
    	//     $roleshortname = $role_specialaccess_student;
    	//
    	// 	break;

    	// SWTC ********************************************************************************
    	// Accesstype is not recognized
    	// SWTC ********************************************************************************
    	} else {
    		$portfolio = get_string('PORTFOLIO_NONE', 'local_swtc');
    		$roleshortname = 'none';
    		$categoryids[] = 'none';
    		$capabilities[] = 'none';
    	}

        // SWTC ********************************************************************************
        // Loop through all the roles defined. When the shortname is found, load the role's id value.
        //
        // Sets variables:
    	//			$swtc_user->roleid								The id of the role the user has.
        // SWTC ********************************************************************************
    	foreach ($roles as $role) {
    		if ($role->shortname == $roleshortname) {
    			$roleid = $role->id;
    			break;
    		}
    	}

        // SWTC ********************************************************************************
        // Finished. Set $temp_user to all the appropriate values so it can be returned.
        // SWTC ********************************************************************************
        $temp_user->portfolio = $portfolio;
        $temp_user->roleshortname = $roleshortname;
        $temp_user->categoryids = $categoryids;
        $temp_user->capabilities = $capabilities;
        $temp_user->roleid = $roleid;

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Leaving swtc_lib_locallib.php. ===3.get_user_access.exit.";
            $messages[] = "Lenovo ********************************************************************************.";
    		$messages[] = "temp_user array follows: ";
            $messages[] = print_r($temp_user, true);
            $messages[] = "After printing temp_user";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	}

        return array($cats, $temp_user);
    }

    /**
     * Load all the category (portfolio) ids and information about each of them.
     *
     * @param N/A
     *
     * @return $array   All category information.
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     **/
    function loadcatids($roles) {

        // SWTC ********************************************************************************.
        // SWTC LMS swtc_user and debug variables.
        $debug = swtc_set_debug();

        // A list of all the top-level category information defined (this is returned).
        $cats = array();

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_swtclib.php. ===swtc_loadcatids.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

        // SWTC ********************************************************************************
        // Get a list of all top-level categories defined in the system (whether the user can view them or not) using get_tree.
    	//		Note: The following array is returned; the number in the listing is the top-level category id number ($catids->id). Example:
    	//			array (					At the time of this writing, the top-level category names are:
    	//				[0] => 14			'GTP Portfolio'
    	//				[1] => 36			'IBM Portfolio'
    	//				[2] => 47			'Lenovo Portfolio'
    	//				[3] => 60			'Lenovo Internal Portfolio'
    	//				[4] => 73			'Lenovo Shared Resources (Master)'
    	//				[5] => 74			'Maintech Portfolio'
    	//				[6] => 25			'Service Provider'
        //				[7] => 97			'ASP Portfolio'
        //				[8] => 110		'Premier Support Portfolio'
        //				[9] => 137		'Service Delivery Portfolio'
        //				[10] => 136		'Site Help Portfolio'
    	//				[11] => 141		'Curriculums Portfolio'
    	//			)
    	//			Important! The category id's returned are NOT guaranteed to be the numbers shown (although they should be). However,
    	//					the category NAMES ARE guaranteed to be strings shown (unless specifically changed on the Lenovo EBG LMS site).
    	//			Important! To access context for each category: $context = $cats[0-8]['context'];
        // SWTC ********************************************************************************
        $catids = $this->get_tree(0);				// '0' means just the top-level categories are returned.

    	if (isset($debug)) {
            // debug_enable_phplog($debug, "2 - In swtc_loadcatids.");
    		$messages[] = "catids array follows:";
            $messages[] = print_r($catids, true);
            $messages[] = "catids array ends.";
    		// print_object($catids);
    	//	$debug->logmessage("roles array follows: <br />", 'detailed');
    	//	print_object($roles);
    	//	die();
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	}

        // SWTC ********************************************************************************
    	// Next, load a multi-dimension array for each of the top-level categories (this array will be searched by name for the id below):
        //              'catid'             - the id of the top-level category (returned from the get_tree(0) call above).
        //              'catname'       - the name of the top-level category (ex: "GTP Portfolio").
        //              'context'       - create a context of context_coursecat.
        //              'capability'    - the capability associated with this top-level category (ex: local/swtc:ebg_access_gtp_portfolio).
        //              'roles'             - array of all roles and roleids associated with this top-level category (see below for example).
        //
    	//			An example array (filled-in below) has the following format (as of 08/28/16 taken from .244 sandbox):
    	//
    	//			[0] => Array
    	//				(
    	//					[catid] => 14
    	//					[catname] => GTP Portfolio
    	//					[context] => context_coursecat Object
    	//						(
    	//							[_id:protected] => 511
    	//							[_contextlevel:protected] => 40
    	//							[_instanceid:protected] => 14
    	//							[_path:protected] => /1/511
    	//							[_depth:protected] => 2
    	//						)
    	//					[capability] => local/swtc:ebg_access_gtp_portfolio
    	//					[roles] => Array
    	//						(
    	//							[gtp-instructor] => 15
    	//							[gtp-student] => 16
    	//							[gtp-administrator] => 10
    	//							[gtp-siteadministrator] => 23
    	//						)
    	//				)
    	//
        // SWTC ********************************************************************************

        // SWTC ********************************************************************************
    	// Build the main $cats array (to be passed back to assign_user_role).
        // SWTC ********************************************************************************
    	foreach ($catids as $key => $catid) {
    		$cats[$key]['catid'] = $catid;
    		// $cats[$key]['catname'] = coursecat::get($catid, MUST_EXIST, true)->name;     // Moodle 3.6
            $cats[$key]['catname'] = \core_course_category::get($catid, MUST_EXIST, true)->name;
    		$cats[$key]['context'] = \context_coursecat::instance($catid);

            // SWTC ********************************************************************************
            // Switch on the 'catname'.
            //      Note: If adding a new portfolio, add a new case to this switch.
            // SWTC ********************************************************************************
            switch ($cats[$key]['catname'] ) {
                // SWTC ********************************************************************************
                // 'GTP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('gtp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_gtp_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        //if (isset($debug)) {
                        //	$debug->logmessage("role follows: <br />", 'logfile');
                        //	print_object($role);
                        //	$debug->logmessage("role->shortname to search for is <strong>$role->shortname</strong>.<br />", 'logfile');
                        //}
                        if ($role->shortname == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_gtp_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovo_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovo_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_lenovo_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'IBM Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
    			// 08/25/16 - Changed "Lenovo and IBM Portfolio" values to just "IBM Portfolio" so that values
                //                  will be the same (i.e. will help in transition).
                // SWTC ********************************************************************************
                case get_string('ibm_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_ibm_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ServiceProvider Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('serviceprovider_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_serviceprovider_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Internal Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovointernal_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovointernal_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Maintech Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('maintech_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_maintech_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_maintech_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Shared Resources (Master)' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovosharedresources_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovosharedresources', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ASP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('asp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_asp_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_asp_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'PremierSupport Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('premiersupport_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_premiersupport_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ServiceDelivery Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('servicedelivery_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_servicedelivery_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Site Help Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('sitehelp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_sitehelp_portfolio', 'local_swtc');

                    // Load all the roleids. Remember that ALL roles have access to this portfolio.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_lenovo_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_asp_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
    					} else if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
    					}
                    }
                    break;

    			// SWTC ********************************************************************************
    			// 'Curriculums Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('curriculums_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_curriculums_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                default:
                    // unknown type
            }
        }

        // SWTC ********************************************************************************
    	// Note: At this point the $cats array should be fully created...
        // SWTC ********************************************************************************
        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Exiting swtc_lib_swtclib.php. ===swtc_loadcatids.exit.";
            $messages[] = "Lenovo ********************************************************************************.";
            // debug_enable_phplog($debug);
            // $messages[] =  "cats array follows:";
            // $messages[] = print_object($cats);
            // $messages[] = print_r($cats, true);
            // $messages[] = "cats array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        //	die();
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
    function get_tree($id) {
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
    		$all[$record->id. 'i']= array();
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
    function get_portfolio_name($portfolio_name, $cats) {
        $tmp = array();

        $cat = $cats[array_search($portfolio_name, array_column($cats, 'catname'))];

        $tmp['catid'] = $cat['catid'];
        $tmp['catname'] = $cat['catname'];
        $tmp['context'] = $cat['context'];
        $tmp['capability'] = $cat['capability'];

        return array($tmp, $cat['capability']);

    }

    /**
     * If PremierSupport or ServiceDelivery manager or administrator ventures outside their own portfolio,
     *          they are no longer considered a manager or administrator. Substitute either
     *          PremierSupport-student or ServiceDelivery-student as role.
     *
     * @param $cat		A catlist class variable.
     * @param $user		A user class variable.
     *
     * @return $temp_user	$user (passed in) with the rolename and roleid changed if required.
     *
     *
     * History:
     *
     * 10/24/20 - Initial writing.
     *
     */
    function change_user_access($cat, &$user) {
        global $DB;

        // SWTC ********************************************************************************.
        // SWTC SWTC swtc_user and debug variables.
        $swtc_user = swtc_get_user($user);
        $debug = swtc_set_debug();

        // Other Lenovo variables.
        $user_access_type = $swtc_user->user_access_type;
        $roleshortname = null;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_change_user_access.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends.";
            // $debug->logmessage(print_r($swtc, true), 'detailed');
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

    	// $topcat = $cat['catname'];
    	// print_object("In local_swtc_change_user_access. catname to check is :$topcat.");

    	// 01/10/19 - Just a test...
    	// if (has_capability($cat['capability'], $cat['context'])) {
    	// 	print_object("User has access to category $topcat");
    	// } else {
    	// 	print_object("User does NOT have access to category $topcat");
    	// }

        // SWTC ********************************************************************************
        // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
        // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
        // SWTC ********************************************************************************
    	// print_object($user->user_access_type);
    	// if (stripos($user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc')) !== false) {
    	// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), stripos was true");
    	// } else {
    	// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), stripos was false");
    	// }

    	// SWTC ********************************************************************************
    	// PremierSupport access type.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/08/19 - Added PS/SD GEO site administrator user access types.
    	// SWTC ********************************************************************************
        //****************************************************************************************.
        // PremierSupport managers
        //****************************************************************************************.
    	if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the mgr access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		}
        //****************************************************************************************.
        // PremierSupport administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
        //****************************************************************************************.
        // PremierSupport GEO administrators
        //****************************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the GEO admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
        //****************************************************************************************.
        // PremierSupport site administrators
        //****************************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the site admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
    	// SWTC ********************************************************************************
    	// ServiceDelivery access type.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/08/19 - Added PS/SD GEO site administrator user access types.
    	// SWTC ********************************************************************************
        //****************************************************************************************.
        // ServiceDelivery managers
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the mgr access.
    		// print_object("I found a servicedelivery-mgr.");
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery GEO administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery site administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        }
        // SWTC ********************************************************************************
        // Remember to set the roleid.
    	// 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
    	//						if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
    	//						it was when this was called.
        // SWTC ********************************************************************************
    	if (!empty($roleshortname)) {
    		$user->roleshortname = $roleshortname;
    		$role = $DB->get_record('role', array('shortname' => $user->roleshortname), '*', MUST_EXIST);
    		$user->roleid = $role->id;
    	} else {

    	}

    	// return $tmp_user;
    	return;
    }
}
