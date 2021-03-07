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
 * @subpackage swtc/classes/SwtcUser.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 10/16/20 - Changed to swtc class.
 * 03/03/21 - Changed class name from swtc_user to SwtcUser.
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

use local_swtc\SwtcDebug;
use local_swtc\swtc_counter;

// SWTC ********************************************************************************
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************
// require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');
require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');


/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT!
 *          DO NOT call this class directly. Use $swtc_set_user from /lib/swtc_userlib.php.
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
class SwtcUser {
	/**
	 * Store the user's id.
	 * @var integer
	 */
	private  $userid;

	/**
	 * Store the user's username.
	 * @var string
	 */
	private  $username;

	/**
	 * Store the user's accesstype.
	 * @var string
	 */
	private  $user_access_type;

	/**
	 * The user's main portfolio they have access to.
	 * @var string
	 */
	private  $portfolio;

	/**
	 * The user's role shortname.
	 * @var string
	 */
	private  $roleshortname;

	/**
	 * The user's role id.
	 * @var integer
	 */
	private  $roleid;

	/**
	 * The categories the user has access to.
	 * @var array
	 */
	private  $categoryids;

	/**
	 * The time of this action.
	 * @var DateTime
	 */
	private  $timestamp;

	/**
	 * If an admin is performing an action on behalf of another user,
	 * this is the related user's id.
	 * @var integer
	 */
	private  $relateduser;

	/**
	 * The cohort names the user is a member of (if any).
	 * @var array
	 */
	private  $cohortnames;

	/**
	 * The preg_match string that should be used to
	 * find all the groups the user is a member of.
	 * @var string
	 */
	private  $groupname;

	/**
	 * The user's GEO.
	 * @var string
	 */
	private  $geoname;

	/**
	 * The groups the user is a member of (if any).
	 * @var array
	 */
	private  $groupnames;

	/**
	 * The timezone of the user.
	 * @var DateTimeZone
	 */
	private  $timezone;

	/**
	 * The user's accesstype 2.
	 * @var string
	 */
	private  $user_access_type2;

	/**
	 * Constructor is private, use /locallib/swtc_local_user() to
	 * retrieve SWTC user information.
	 */
	public function __construct($args=[]) {


		// print_object("In SwtcUser __construct');		// 10/18/20 - SWTC
		// print_object("In SwtcUser __construct; about to print backtrace');		// 10/16/20 - SWTC
		// print_object(format_backtrace(debug_backtrace(), true));        // SWTC-debug
		// print_object($user);		// 10/16/20 - SWTC

		// $this->userid = null;
		$this->userid = $args['userid'] ?? null;
		// $this->username = null;
		$this->username = $args['username'] ?? null;
		$this->user_access_type = null;
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
		$this->user_access_type2 = null;

		// SWTC ********************************************************************************
		// Copy this object to $SESSION->SWTC->USER.
		// SWTC ********************************************************************************
		// $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
		// $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
		// print_object("In not set SWTC->USER; about to print this');		// 10/16/20 - SWTC
		// print_object($this);		// 10/16/20 - SWTC
		// print_object("About to leave SwtcUser __construct; about to print SESSION->SWTC');		// 10/20/20 - SWTC
		// print_object($SESSION->SWTC);      // 10/20/20 - SWTC
	}

	/**
	 * All Setter methods for all properties.
	 *
	 * Setter methods:
	 *      @param $value
	 *      @return N/A
	 *
	 * History:
	 *
	 * 03/03/21 - Initial writing.
	 *
	 **/
	public function set_userid($user) {
	 	$this->userid = (isset($user->id)) ? $user->id : null;
	}

	public function set_username($user) {
		$this->username = (isset($user->username)) ? $user->username : null;
	}

	public function set_user_access_type($user_access_type) {
	 	$this->user_access_type = $user_access_type;
	}

	public function set_user_access_type2($user_access_type2) {
	 	$this->user_access_type2 = $user_access_type2;
	}

	/**
	 * Set current date and time for timestamp. Returns value to set $this->timestamp.
	 *
	 * History
	 *
	 * 10/19/20 - Initial writing.
	 * 03/02/21 - Removed return of timezone (moved to set_timezone).
	 *
	 */
	public function set_timestamp() {
		$timezone = core_date::get_user_timezone_object();
		$today = new DateTime("now", $timezone);
		$this->timestamp = $today->format('H:i:s.u');
	}

 	/**
 	 * Set current timezone.
 	 *
 	 * History
 	 *
 	 * 03/02/21 - Initial writing.
 	 *
 	 */
	public function set_timezone() {
		$this->timezone = core_date::get_user_timezone_object();
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
	 *				have access too.
	 */
	function set_user_role($eventdata) {
		global $USER, $DB, $COURSE;

		/** @var $USER Gets the current user information. */
		// $this = swtc_get_user($USER);
		/** @var SwtcDebug Get the current debug information. */
		$debug = swtc_get_debug();
		// $counter = new swtc_counter;        // Debug - 02/26/21

		// Other SWTC variables.
		/** @var array Only set IF working with a related user (i.e. swtc_get_relateduser is called). */
		$user_related = null;
		$tmp_user = new stdClass();    // Hold return values from /local/swtc/classes/SwtcUser.php get_user_access.
		// SWTC ********************************************************************************

		if (isset($debug)) {
			// SWTC ********************************************************************************
			// Always output standard header information.
			// SWTC ********************************************************************************
			$messages[] = "SWTC ********************************************************************************";
			$messages[] = "Entering /local/swtc/classes/SwtcUser.php.===set_user_role.enter.";
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "swtc_user array follows :";
			$messages[] = print_r($this, true);
			$messages[] = "swtc_user array ends.";
			$messages[] = "eventname follows :";
			$messages[] = print_r($eventdata->eventname, true);
			// print_object("eventname is $eventdata->eventname");     // Debug - 02/25/21
			$messages[] = "eventname ends.";
			$messages[] = "SWTC ********************************************************************************";
			// $phplog = debug_enable_phplog($debug, "In get_user_access.');
			$debug->logmessage($messages, 'both');
			unset($messages);
		   }

		//****************************************************************************************
		// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
		//****************************************************************************************
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
					// purge_all_caches();      // Debug - 02/25/21
				} else {
					$debug->logmessage("User is viewing a course. About to return.", 'logfile');
					$debug->logmessage("Leaving set_user_role.exit===11===.", 'logfile');
					// purge_all_caches();      // Debug - 02/25/21
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
		// Check to see if the administrator is working on behalf of a user, or the actual user is doing something.
		//		Important! If an administrator is working on behalf of a user (for example, updating the user's profile or creating a new user),
		//			$eventdata->relateduserid will be the userid of the user and the userid the rest of the plug-in should work with.
		//			If a "regular" user is doing something, $eventdata->relateduserid will be empty.
		//
		// Sets variables:
		//			$this->userid								The userid of the "actual" user (not the administrator).
		//			$this->username							The username of the "actual" user (not the administrator).
		//			$this->user_access_type			The most important variable; triggers all the rest that follows.
		//          $this->timestamp
		//			$this->user_access_type2     // @02
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
			// 07/18/18 - Set $user_related to $this->relateduser (otherwise $user_related is NULL).
			$user_related = $this->get_relateduser($eventdata->relateduserid);   // 12/04/18
			$this->relateduser = $user_related;       // 12/04/18

			// 07/18/18 - Set $user_related to $this->relateduser (otherwise $user_related is NULL).	// 01/10/19 - Moved above.
			// $user_related = $this->relateduser;	// 01/10/19

			if (isset($debug)) {
				$messages[] = "In top of set_user_role (relateduserid). Setting swtc_user->relateduser information of $eventdata->relateduserid. ===11===.";
				$messages[] = "swtc_get_relateduser follow:";
				$messages[] = print_r($this->relateduser, true);
				$messages[] = "swtc_get_relateduser end.";
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
		} else {
			// SWTC ********************************************************************************
			// 04/23/18: Since $this->userid and $this->user_access_type should already be set by now,
			//                  removing this section.
			// SWTC ********************************************************************************
			// Set the users userid and access_type.
			//  if (!isset($this->userid)) {
			//      if (isset($debug)) {
			//          $debug->logmessage("In top of local_swtc_set_user_role. assigning userid of $eventdata->userid. ===11===.", 'logfile');
			//      }
			//      $this->userid = $eventdata->userid;
			//      // $this->userid = $eventdata['other']['username'];      // TODO
			//
			//      // Get the customized user profile field "accesstype".
			//      $this->user_access_type = $USER->profile['accesstype'];
			//      $this->timestamp = $this->set_timestamp();
			//  }
		}

		if (isset($debug)) {
			$messages[] = "The userid that will be used throughout this plugin is :<strong>$this->userid</strong>.";
			$messages[] = "The username that will be used throughout this plugin is :<strong>$this->username</strong>.";
			$messages[] = "The user_access_type is :<strong>$this->user_access_type</strong>.";
			$messages[] = "The timestamp is :<strong>$this->timestamp</strong>.";
			$messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid, admin is working with this userid).";
			$debug->logmessage($messages, 'both');
			unset($messages);
		}

		// SWTC ********************************************************************************
		// For each of the messages being captured, get the user access type, role, and category id they SHOULD have access to (function below).
		//
		//			The $this is returned. It is a multidimensional array that has the following format (Note: roleid will be loaded later):
		//			$access = array(
		//					'portfolio'=>'',
		//					'roleshortname'=>'',
		//					'roleid'=>'',
		//					'categoryid's = array(
		//                      )
		//			);
		//
		// SWTC ********************************************************************************
		// No need to return access (values set directly in SWTC) nor allroles (called again in swtc_load_roleids).
		//  No need to send $this->user_access_type as that is part of SWTC.
		//      Also update timestamp.
		// SWTC ********************************************************************************
		// list($catlist, $tmp_user) = $this->get_user_access();
		$tmp_user = $this->get_user_access();

		// SWTC ********************************************************************************
		// Finished. Set $this to all the appropriate values.
		//      And set new timestamp.
		// SWTC ********************************************************************************
		// 07/12/18 - Added check if $user_related is set. If so, load that access information. Otherwise, load $this.
		if (isset($user_related)) {
			$user_related->portfolio = $tmp_user->portfolio;
			$user_related->roleshortname = $tmp_user->roleshortname;
			$user_related->roleid = $tmp_user->roleid;
			$user_related->categoryids = $tmp_user->categoryids;
			$user_related->set_timestamp();
			$user_related->set_timezone();
		} else {
			$this->portfolio = $tmp_user->portfolio;
			$this->roleshortname = $tmp_user->roleshortname;
			$this->roleid = $tmp_user->roleid;
			$this->categoryids = $tmp_user->categoryids;
			$this->set_timestamp();
			$this->set_timezone();
		}
		// print_r("after first call to isset - user_related.\n');   // 11/30/18 - RF - testing...
		// print_object($user_related);
		// print_r("after first call to isset - swtc_user.\n');   // 11/30/18 - RF - testing...
		// print_object($this);     // At this point, swtc_user is messed up.

		// SWTC ********************************************************************************
		// Note: At this point the $catids array should be fully created...
		// SWTC ********************************************************************************
		if (isset($debug)) {
			// $messages[] = "catlist array follows: ";
			// $messages[] = print_r($catlist, true);
			// $messages[] = "catlist array ends.";
			$messages[] = "swtc_user array follows: ";
			$messages[] = print_r($this, true);
			$messages[] = "swtc_user array ends.";
			$debug->logmessage($messages, 'detailed');
			unset($messages);
		//	die();
		}

		// SWTC ********************************************************************************
		// Debug: get the list of all courses the user has access to. In other words, if they don't have access to any, count will be 0.
		// 		Note: This returns a 'course_in_list' array. Take a look in ./course/classes/management/helper.php for functions.
		// 11/27/19 - Removed call to core_course_category::get(0)->get_courses(array('recursive' => true)) as it was creating possible
		//                      side effect with other functions.
		// SWTC ********************************************************************************
		// if (isset($debug)) {
			// $debug->logmessage("In user_loggedin message.", 'logfile');
			// $allcourses = core_course_category::get(0)->get_courses(array('recursive' => true));        // Moodle 3.6
			// $allcourses = core_course_category::get(0)->get_courses(array('recursive' => true));
			// $totalcount = count($allcourses);
			// $debug->logmessage("Total number of courses the user has access to is :<strong>$totalcount.</strong>", 'logfile');
		//	print_object($allcourses, true);
		// }

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
		//		In other words, if the user's role in the course is different than what is in their user profile ("Accesstype'), assign correct
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

			$sharedres_courseid = $DB->get_field('course', 'id', array('shortname' => get_string('sharedresources_coursename', 'local_swtc')));
			$lensharedsimulators_courseid = $DB->get_field('course', 'id', array('shortname' => get_string('lensharedsimulators_shortname', 'local_swtc')));

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
				// SWTC *******************************************************************************.
				// 10/08/19 - metacourse could be either of two courses.
				// SWTC *******************************************************************************.
				$messages[] = "metacourse1 courseid :$swtc_resources->sharedres_courseid.";
				$messages[] = "metacourse2 courseid :$swtc_resources->lensharedsimulators_courseid.";
				$debug->logmessage($messages, 'logfile');
				unset($messages);
			}

			// Get the courseid that the user has enrolled in.
			$courseid = $eventdata->courseid;

			// SWTC ********************************************************************************
			// If the student self-enrolled in a course that has a metacourse, two '\core\event\role_assigned' messages are generated.
			//		Is $eventdata->courseid the course or the metacourse? If it's the metacourse, return. If not, continue.
			// 10/08/19 - metacourse could be either of two courses.
			// SWTC ********************************************************************************
			if (($courseid == $sharedres_courseid) || ($courseid == $lensharedsimulators_courseid)) {
				if (isset($debug)) {
					$debug->logmessage("eventdata->courseid is :<strong>$courseid</strong>; the metacourse id is either :", 'logfile');        // 10/08/19
					$debug->logmessage("<strong>$swtc_resources->sharedres_courseid</strong>.", 'logfile');     // 10/08/19
					$debug->logmessage("<strong>$swtc_resources->lensharedsimulators_courseid</strong>.", 'logfile');     // 10/08/19
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
				// print_object($context, true);
				// $DB->set_debug(true);
			}

			// Get the role assignments of the user in the course (get_records on course or metacourse depending on is_meta value).
			$ras = $DB->get_records('role_assignments', array('userid'=>$this->userid, 'contextid'=>$context->id));

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
				$temp_user = clone $user_related;
			} else {
				$temp_user = clone $this;
			}

			print_r('after second call to isset - user_related.\n');   // 11/30/18 - RF - testing..
			print_object($temp_user);

			// SWTC ********************************************************************************
			// 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
			// 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
			// SWTC ********************************************************************************
			// print_object("about to call 1');
			// $temp_user = change_user_access($topcatname, $temp_user);		// 01/10/19
			$temp_user->change_user_access($topcat);			// 01/10/19
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
			//if ( !$manager->assign_role_to_user($temp_user->roleid, $this->userid)) {
			if ( !$ra = role_assign($temp_user->roleid, $temp_user->userid, $context->id)) {
				if (isset($debug)) {
					$newshortname = $temp_user->roleshortname;
					$debug->logmessage("Error assigning <strong>$temp_user->roleid ($newshortname)</strong> in course <strong>$courseid</strong>. Returning.", 'logfile');
					return;
				} else {
					// $debug->logmessage("Successful assigning <strong>$temp_user->roleid ($newshortname)</strong> in course <strong>$courseid</strong>.", 'logfile');
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
		//					[roles] => Array
		//						(
		//							[gtp-instructor] => 13
		//							[gtp-student] => 14
		//							[gtp-administrator] => 16
		//						)
		//				)
		//
		// Main loop ("For each of the top-level categories defined on the site...').
		//
		//  07/11/18 - Changed PremierSupport roles to only have PremierSupport-student roles outside the PremierSupport portfolio
		//                      (even administrators and managers). This is to prevent PremierSupport admins and mgrs from having more access
		//                      to a course when they are enrolled in a course outside the PremierSupport portfolio.
		// 07/12/18 - Remember to skip roles returned with contextid = 1 (which is System context); added check if user_related is set.
		//                  If so, use that user information to determine access; to make sure all changes are made, changing swtc_user to temp_user
		//                  (since no information is saved in this section); remember to unset SESSION->SWTC->USER->relateduser at the end.
		// SWTC ********************************************************************************
		if (isset($user_related)) {
			$temp_user = clone $user_related;
		} else {
			$temp_user = clone $this;
		}
		// print_r("after third call to isset - user_related.\n');   // 11/30/18 - RF - testing...
		// here!!!
		$catlist = $this->loadallcatsaccess();

		foreach ($catlist as $key => $catlist['catid']) {
			// Check to see if the user has any roles assigned to this top-level category. Save for several checks later...
			// 07/12/18 - Added checkparentcontexts as false (to remove System contextid).
			// $userroles = get_user_roles($context, $this->userid);
			// print_object("key is :$key');		// 01/10/19
			$context = context_coursecat::instance($catlist[$key]['catid']);
			$userroles = get_user_roles($context, $temp_user->userid, false);
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
					$messages[] = print_r($context, true);
					$messages[] = "Finished printing context.";
					$debug->logmessage($messages, 'detailed');
					unset($messages);
				} else {
					$debug->logmessage("Userid <strong>$temp_user->userid</strong> does <strong>NOT</strong> have any roles in <strong>$temp</strong>.", 'both');
				}
			}

			$catid = $catlist[$key]['catid'];
			// SWTC ********************************************************************************
			// $context = core_course_category::instance($catid);		// 01/10/19
			if (isset($debug)) {
				$messages[] = "catid to search for is $catid.";
				// $cat = $temp_user->categoryids[array_search($catid, array_column($temp_user->categoryids, 'catid'))]['catid'];
				// print_object(array_column($temp_user->categoryids, 'catid'));
				// if (array_search($catid, array_column($temp_user->categoryids, 'catid')) !== false) {
				if (array_key_exists($catid, $temp_user->categoryids) !== false) {   // 02/22/21
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
			if (array_key_exists($catid, $temp_user->categoryids) !== false) {   // 02/22/21
			// if (has_capability($catlist[$key]['capability'], $context)) {			// 01/10/19
			// if (has_capability($catlist[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['capability'],
			//        $cats[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['context']))

				// $temp = $catlist[$key]['catname'];      // 11/27/18 - Moved here from three lines below.		// 01/10/19 - Changed (see line below).
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
					// For each of the roles, if $role['id'] == $this->roleid, the user has the correct access. If they don't match,
					//      remove the user from the role.
					foreach ($userroles as $role) {

						// SWTC ********************************************************************************
						// 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
						// 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
						// SWTC ********************************************************************************
						// print_object('about to call 2');       // Debug - 02/25/21
						// print_object($temp_user);       // Debug - 02/25/21
						// print_object("eventname is $eventdata->eventname");     // Debug - 02/25/21
						// $counter->incrementValue();
						// print_r("==> Counter value is :" . $counter->getValue());
						$temp_user->change_user_access($catid);		// 01/10/19

						if ($role->roleid != $temp_user->roleid) {

							if (isset($debug)) {
								$tempid = $temp_user->roleid;
								$tempname = $temp_user->roleshortname;
								$messages[] = "However, user <strong>$temp_user->userid</strong> has been given an incorrect role. It should be <strong>$tempid ($tempname)</strong> but is <strong>$role->roleid ($role->shortname)</strong>.";
								$messages[] = "Action: will remove user from role <strong>$role->roleid</strong>; will add user to role <strong>$tempid</strong>.";
								$messages[] = "parameters to role_unassign are :role->roleid is $role->roleid; temp_user->userid is $temp_user->userid; $context->id is :";
								$messages[] = print_r($context->id, true);
								$debug->logmessage($messages, 'both');
								unset($messages);
							}

							// Unassign the user from the incorrect role...
							role_unassign($role->roleid, $temp_user->userid, $context->id);

							// Assign the user to the correct role...
							role_assign($temp_user->roleid, $temp_user->userid, $context->id);

							// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
							$context->mark_dirty();

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
					// print_object("about to call 3');
					$temp_user->change_user_access($catid);		// 01/10/19

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

					role_assign($temp_user->roleid, $temp_user->userid, $context->id);

					// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
					$context->mark_dirty();
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
						// print_r($this->roleid);
						$debug->logmessage("", 'logfile');
					}

					// For each of the roles, remove the user from it.
					foreach ($userroles as $role) {
						// 08/27/16 - The user may have the correct role (for example, IBM-student), but might have been accidentally given
						//      access to an "off limits" portfolio  (for example, GTP-Portfolio). In this case, if the current portfolio
						//      is not one of the one's in the list, remove the user from it.
						//
						//      Removing "if ($role->roleid != $this->roleid)" condition.
						role_unassign($role->roleid, $temp_user->userid, $context->id);

						// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
						$context->mark_dirty();
					}

					// Get a new count of the roles assigned to this top-level category.
					// 07/12/18 - Added checkparentcontexts as false (to remove System contextid).
					$updated_userroles = get_user_roles($context, $temp_user->userid, false);
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

		// print_r("about to return from set_user_role.\n');
		// print_r("printing SESSION->SWTC->USER.\n');   // 11/30/18 - RF - testing...
		// print_object($SESSION->SWTC->USER);       // At this point, USER is still messed up.

		// 07/12/18 - Remember to unset SESSION->SWTC->USER->relateduser at the end.
		// 11/30/18 - RF - testing...tried commenting out; didnt change anything; putting back in.
		if (isset($this->relateduser)) {
			unset($this->relateduser);
			unset($user_related);
		}

		// print_r("printing SESSION->SWTC->USER - again.\n');   // 11/30/18 - RF - testing...
		// print_object($SESSION->SWTC->USER);


		// Invalidate the data so that the user does not need to logoff and log back in to see changed roles...
		purge_all_caches();
		// print_object($this);     // 11/08/19 - Lenovo debugging...
		if (isset($debug)) {
			$debug->logmessage("Leaving /local/swtc/classes/set_user_role.exit===11===.", 'logfile');
		}
		return;

	}

	/**
	 * All Getter methods for all properties.
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
 		// $this = new swtc_user;      // 10/24/20
 		$swtc_user = new SwtcUser($user);
 		// print_object('In SwtcUser.get_user; about to print swtc_user');
 		// print_object($swtc_user);
 		return $swtc_user;
 	}

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
 		return $this->user_access_type;
 	}

 	public function get_portfolio() {
 		return $this->portfolio;
 	}

	// SWTC ********************************************************************************
	// Get the logged in user customized user profile value 'accesstype'. accesstype is used to determine
	//      which portfolio of classes the user should have access to (in other words, which top-level
	//      category they should have access to). Note that this function returns the information the
	//      user 'should' have access to. What the user actually has access to (and whether they need
	//      more or less access) is determined above.
	//
	//  Important! Case of accesstype is important. It must match the case defined in Moodle.
	//
	//  Returns array: first element portfolio value; second element the user's role shortname
	//      (i.e. 'ibm-student' or 'gtp-administrator'); third element is the top-level category id
	//      the user 'should' have access to (checked above).
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
	 * History:
	 *
	 * 10/23/20 - Initial writing.
	 * 03/04/21 - Changing $swtc_user to $this.
	 *
	 */
	function get_user_access() {
		global $DB, $USER;

		/** @var SwtcDebug Get the current debug information. */
		$debug = swtc_get_debug();

		// Temporary variables. Use these during the function.
		// $temp_user = new stdClass();    // Returned to calling function.
		$temp_user = clone $this;      // Create a SwtcUser object.
		print_object("entering get_user_access; about to print temp_user");      // Debug
		print_object($temp_user);       // Debug
		$roleshortname = null;
		$portfolio = null;
		$categoryids = array(); // A list of all the categories the user should have access to (set in $this->categoryids).

		// SWTC ********************************************************************************
		// 07/12/18 - Added check if swtc_user->relateduser is set. If so, use that user information to determine access.
		//                  Note that no switching of users below should be necessary.
		// SWTC ********************************************************************************
		if (isset($this->relateduser)) {
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Entering /local/swtc/classes/SwtcUser.php. ===3.get_user_access.enter.";
			$messages[] = "swtc_user->relateduser is set; the userid that will be used throughout get_user_access is :<strong>$this->userid</strong>.";
			$messages[] = "swtc_user->relateduser is set; the username that will be used throughout get_user_access is :<strong>$this->username</strong>.";
			$messages[] = "swtc_user->relateduser is set; the user_access_type is :<strong>$this->user_access_type</strong>.";
			// $this = $this->relateduser;
			$user_access_type = $this->relateduser->user_access_type;
		} else {
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Entering /local/swtc/classes/SwtcUser.php. ===3.get_user_access.enter.";
			$messages[] = "swtc_user->relateduser is NOT set; the userid that will be used throughout get_user_access is :<strong>$this->userid</strong>.";
			$messages[] = "swtc_user->relateduser is NOT set; the username that will be used throughout get_user_access is :<strong>$this->username</strong>.";
			$messages[] = "swtc_user->relateduser is NOT set; the user_access_type is :<strong>$this->user_access_type</strong>.";
			$user_access_type = $this->user_access_type;
		}
		// SWTC ********************************************************************************.
		if (isset($debug)) {
			// SWTC ********************************************************************************
			// Always output standard header information.
			// SWTC ********************************************************************************
			// $messages[] = "SWTC ********************************************************************************.";
			// $messages[] = "Entering /local/swtc/classes/SwtcUser.php. ===3.get_user_access.enter.";
			$messages[] = "SWTC ********************************************************************************.";
			// $phplog = debug_enable_phplog($debug, "In /local/swtc/classes/SwtcUser.php. ===3.get_user_access.enter.');
			$debug->logmessage($messages, 'both');
			unset($messages);
		}

		// SWTC ********************************************************************************
		// Switch on the users access type.
		//
		// Sets variables:
		//			$this->roleshortname	The actual name of the role the user has.
		//			$this->portfolio		The name of the portfolio the user has access to.
		//			$this->categoryids		An array of category ids the user has access to.
		//
		// SWTC ********************************************************************************

		// SWTC ********************************************************************************
		// Check for Lenovo-admin, Lenovo-inst, or Lenovo-stud user
		// SWTC ********************************************************************************
		if ((stripos($user_access_type, get_string('access_lenovo_administrator', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_instructor', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_student', 'local_swtc')) !== false)) {

			if (stripos($user_access_type, get_string('role_lenovo_administrator', 'local_swtc')) !== false) {
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
			} else if (stripos($user_access_type, get_string('role_lenovo_instructor', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_lenovo_instructor', 'local_swtc');
				$portfolio = get_string('lenovo_portfolio', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_lenovo_student', 'local_swtc')) !== false) {
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
		// SWTC ********************************************************************************
		// Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
		// SWTC ********************************************************************************
	} else if (stripos($user_access_type, get_string('access_av_gtp', 'local_swtc')) !== false) {

			$portfolio = get_string('gtp_portfolio', 'local_swtc');

			if (stripos($user_access_type, get_string('role_gtp_siteadministrator', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
			} else if  (stripos($user_access_type, get_string('role_gtp_student', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_student', 'local_swtc');
			}

			$catnames[] = get_string('gtp_portfolio', 'local_swtc');
			$catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

			// Loop through the temp array of category names to load the rest of the fields that are needed.
			foreach ($catnames as $catname) {
				$category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
				$categoryids[$category->id] = $catname;
			}
		// SWTC ********************************************************************************
		// Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
		// SWTC ********************************************************************************
	} else if (stripos($user_access_type, get_string('access_im_gtp', 'local_swtc')) !== false) {

			$portfolio = get_string('gtp_portfolio', 'local_swtc');

			if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_gtp_siteadmin', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
			} else if  (stripos($user_access_type, get_string('role_gtp_student', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_student', 'local_swtc');
			}

			$catnames[] = get_string('gtp_portfolio', 'local_swtc');
			$catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

			// Loop through the temp array of category names to load the rest of the fields that are needed.
			foreach ($catnames as $catname) {
				$category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
				$categoryids[$category->id] = $catname;
			}
		// SWTC ********************************************************************************
		// Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
		// SWTC ********************************************************************************
	} else if (stripos($user_access_type, get_string('access_lq_gtp', 'local_swtc')) !== false) {

			$portfolio = get_string('gtp_portfolio', 'local_swtc');

			if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_gtp_siteadmin', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_administrator', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
			} else if (stripos($user_access_type, get_string('role_gtp_instructor', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
			} else if  (stripos($user_access_type, get_string('role_gtp_student', 'local_swtc')) !== false) {
					$roleshortname = get_string('role_gtp_student', 'local_swtc');
			}

			$catnames[] = get_string('gtp_portfolio', 'local_swtc');
			$catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

			// Loop through the temp array of category names to load the rest of the fields that are needed.
			foreach ($catnames as $catname) {
				$category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
				$categoryids[$category->id] = $catname;
			}
		// SWTC ********************************************************************************
		// Check for IBM-stud user
		// SWTC ********************************************************************************
	} else if (stripos($user_access_type, get_string('access_ibm_student', 'local_swtc')) !== false) {

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
		// SWTC ********************************************************************************
		// Check for ServiceProvider-stud user
		// SWTC ********************************************************************************
		} else if (stripos($user_access_type, get_string('access_serviceprovider_student', 'local_swtc')) !== false) {

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
		// SWTC ********************************************************************************
		// Check for Maintech-stud user
		// SWTC ********************************************************************************
		} else if (strncasecmp($user_access_type, get_string('access_maintech_student', 'local_swtc'), strlen($user_access_type)) == 0) {   // 11/25/19

			$portfolio = get_string('maintech_portfolio', 'local_swtc');
			$roleshortname = get_string('role_maintech_student', 'local_swtc');

			$catnames[] = get_string('maintech_portfolio', 'local_swtc');
			$catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

			// Loop through the temp array of category names to load the rest of the fields that are needed.
			foreach ($catnames as $catname) {
				$category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
				$categoryids[$category->id] = $catname;
			}
		// SWTC ********************************************************************************
		// Check for ASP-Maintech-stud user
		// SWTC ********************************************************************************
		} else if (strncasecmp($user_access_type, get_string('access_asp_maintech_student', 'local_swtc'), strlen($user_access_type)) == 0) {

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
		// SWTC ********************************************************************************
		// Check for PremierSupport users
		// SWTC ********************************************************************************
		} else if ((preg_match(get_string('access_premiersupport_pregmatch_student', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_geoadministrator', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $user_access_type))) {

			$portfolio = get_string('premiersupport_portfolio', 'local_swtc');

			if (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $user_access_type)) {
				$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
			} else if (preg_match(get_string('access_premiersupport_pregmatch_student', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
			} else if (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
			} else if (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
			} else if (preg_match(get_string('access_premiersupport_pregmatch_geoadministrator', 'local_swtc'), $user_access_type)) {
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
		// SWTC ********************************************************************************
		// Check for ServiceDelivery users
		// SWTC ********************************************************************************
		} else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_student', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadministrator', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'), $user_access_type))) {
			$portfolio = get_string('servicedelivery_portfolio', 'local_swtc');

			if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $user_access_type)) {
				$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
			} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_student', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
			} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
			} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'), $user_access_type)) {
					$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
			} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadministrator', 'local_swtc'), $user_access_type)) {
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
		// SWTC ********************************************************************************
		// Check for Self support user
		// SWTC ********************************************************************************
		} else if (stripos($user_access_type, get_string('access_selfsupport_student', 'local_swtc')) !== false) {

			$portfolio = get_string('none_portfolio', 'local_swtc');       // 05/01/18 - RF
			$roleshortname = get_string('role_selfsupport_student', 'local_swtc');

			$catnames[] = get_string('sitehelp_portfolio', 'local_swtc');

			// Loop through the temp array of category names to load the rest of the fields that are needed.
			foreach ($catnames as $catname) {
				$category = $DB->get_record('course_categories', array('name' => $catname, 'parent' => 0), 'id');
				$categoryids[$category->id] = $catname;
			}
		// SWTC ********************************************************************************
		// accesstype is not recognized
		// SWTC ********************************************************************************
		} else {
			$portfolio = get_string('none_portfolio', 'local_swtc');
			$roleshortname = 'none';
			$categoryids[] = 'none';
		}

		// SWTC ********************************************************************************
		// Loop through all the roles defined. When the shortname is found, load the role's id value.
		//
		// Sets variables:
		//			$this->roleid The id of the role the user has.
		// SWTC ********************************************************************************
		// foreach ($roles as $role) {
		// 	if ($role['shortname'] == $roleshortname) {
		// 		$roleid = $role['id'];
		// 		break;
		// 	}
		// }

		// SWTC ********************************************************************************
		// Finished. Set $temp_user to all the appropriate values so it can be returned.
		// SWTC ********************************************************************************
		$temp_user->portfolio = $portfolio;
		$temp_user->roleshortname = $roleshortname;
		$role = $DB->get_record('role', array('name' => $roleshortname), 'id');
		$temp_user->roleid = $role->id;
		$temp_user->categoryids = $categoryids;

		print_object("exiting get_user_access; about to print temp_user");      // Debug
		print_object($temp_user);       // Debug
		// print_object($user_access_type);     // Debug

		if (isset($debug)) {
			// SWTC ********************************************************************************
			// Always output standard header information.
			// SWTC ********************************************************************************
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Leaving /local/swtc/classes/SwtcUser.php. ===3.get_user_access.exit.";
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "temp_user array follows: ";
			$messages[] = print_r($temp_user, true);
			$messages[] = "After printing temp_user";
			$debug->logmessage($messages, 'detailed');
			unset($messages);
		}

		return $temp_user;
	}

	/**
	 *
	 *
	 * @param N/A
	 *
	 * @return None
	 *
	 * History:
	 *
	 * 02/22/21 - Initial writing.
	 * 02/23/21 - TO TO: function also in swtc_userlib.php.
	 *
	 **/
	/**
	 * Setup most, but not all, the characteristics of  SESSION->SWTC->USER->relateduser.
	 * @param  [type] $userid [description]
	 * @return [type]         [description]
	 * History
	 *
	 * 3/4/2021 -
	 *
	 */
	function get_relateduser($userid) {
	  global $USER;

	  // SWTC ********************************************************************************.
	  // SWTC SWTC swtc_user and debug variables.
	  // $this = swtc_get_user($USER);
	  // $debug = swtc_get_debug();

	  $relateduser = new stdClass();     // Local temporary relateduserid variables.
	  // SWTC ********************************************************************************
		// Set some of the SWTC->relateduser variables that will be used IF a relateduserid is found.
		// SWTC ********************************************************************************
		// Get all the user information based on the userid passed in.
		// Note: '*' returns all fields (normally not needed).
		$relateduser = core_user::get_user($userid);
		profile_load_data($relateduser);

		// SWTC ********************************************************************************
		// Since we are using get_user and profile_load_data, there is no need to copy any other fields.
		// SWTC ********************************************************************************
		// $relateduser->username = $relateduser->username;

		// SWTC ********************************************************************************
		// The following fields MUST be added to $relateduser (as they normally do not exist).
		// SWTC ********************************************************************************
		$relateduser->userid = $userid;
		$relateduser->user_access_type = $relateduser->profile_field_accesstype;
		// $relateduser->portfolio = 'PORTFOLIO_NONE';      // 11/30/18 - RF - not sure if this is correct.
		// 01/17/19 - Since we are working with a related user, assigning the portfolio as the same as the administrator is not a good idea.
		$relateduser->portfolio = $this->portfolio;      // 11/30/18

	  // @01 - 03/01/20 - Added user timezone to improve performance.
	  $relateduser->timezone = $this->set_timezone();
	  $relateduser->timestamp = $this->set_timestamp();

		// Important! roleshortname and roleid are what the roles SHOULD be, not necessarily what the roles are.
		$relateduser->roleshortname = null;
		$relateduser->roleid = null;

		// print_object($relateduser);

		// Last step. Note that this sets $SESSION->SWTC->USER->relateduser.
		// $this->relateduser = $relateduser;		// 01/10/19

		// print_object($relateduser);
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
	function loadallcatsaccess() {
		global $DB;

		// SWTC ********************************************************************************.
		// SWTC SWTC swtc_user and debug variables.
		$debug = swtc_get_debug();

		// Other SWTC variables.
		$cats = array();    // A list of all the top-level category information defined (this is returned).
		// $roles = get_all_roles();
		$roles = $DB->get_records('role', array(), 'id ASC', 'id, name, shortname');
		// Put the secondary objects into array format so that multidimensional searching will work.
		$roles = json_decode(json_encode($roles), true);

		// SWTC ********************************************************************************.

		if (isset($debug)) {
			// SWTC ********************************************************************************
			// Always output standard header information.
			// SWTC ********************************************************************************
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Entering /local/swtc/classes/SwtcUser.php. ===loadallcatsaccess.enter.";
			$messages[] = "SWTC ********************************************************************************.";
			$debug->logmessage($messages, 'both');
			unset($messages);
		}

		// SWTC ********************************************************************************
		// Get a list of all top-level categories defined in the system (whether the user can view them or not) using get_tree.
		//		Note: The following array is returned; the number in the listing is the top-level category id number ($catids->id). Example:
		//			array (					At the time of this writing, the top-level category names are:
		//				[0] => 14			'GTP Portfolio'
		//				[1] => 36			'IBM Portfolio'
		//				[2] => 47			'SWTC Portfolio'
		//				[3] => 60			'SWTC Internal Portfolio'
		//				[4] => 73			'SWTC Shared Resources (Master)'
		//				[5] => 74			'Maintech Portfolio'
		//				[6] => 25			'Service Provider'
		//				[7] => 97			'ASP Portfolio'
		//				[8] => 110		'Premier Support Portfolio'
		//				[9] => 137		'Service Delivery Portfolio'
		//				[10] => 136		'Site Help Portfolio'
		//				[11] => 141		'Curriculums Portfolio'
		//			)
		//			Important! The category id's returned are NOT guaranteed to be the numbers shown (although they should be). However,
		//					the category NAMES ARE guaranteed to be strings shown (unless specifically changed on the SWTC EBG LMS site).
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
		//              'roles'             - array of all roles and roleids associated with this top-level category (see below for example).
		//
		//			An example array (filled-in below) has the following format (as of 08/28/16 taken from .244 sandbox):
		//
		//			[0] => Array
		//				(
		//					[catid] => 14
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
		// Build the main $cats array (to be passed back to local_swtc_set_user_role).
		// SWTC ********************************************************************************
		foreach ($catids as $key => $catid) {
			$cats[$key]['catid'] = $catid;
			$cats[$key]['catname'] = core_course_category::get($catid, MUST_EXIST, true)->name;

			// SWTC ********************************************************************************
			// Remember: top-level categories are accessed by $top_level_categories->xxx; capabilities are accessed by $capabilities->xxx.
			// 		For each top-level category, add a two-dimentional array consisting of the roleshortnames and roleids of the roles that have access
			//		to the top-level category.
			// SWTC ********************************************************************************

			// SWTC ********************************************************************************
			// Switch on the 'catname'.
			//      Note: If adding a new portfolio, add a new case to this switch.
			// SWTC ********************************************************************************
			switch ($cats[$key]['catname'] ) {
				// SWTC ********************************************************************************
				// 'GTP Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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

				// SWTC ********************************************************************************
				// 'Lenovo Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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

				// SWTC ********************************************************************************
				// 'IBM Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('ibm_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'ibm-', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_ibm_student', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'ServiceProvider Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('serviceprovider_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'serviceprovider-', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_serviceprovider_student', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'SWTC Internal Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('lenovointernal_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'lenovo-administrator', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'Maintech Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('maintech_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'maintech-', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_maintech_student', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_maintech_student', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'SWTC Shared Resources (Master)' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('lenovosharedresources_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'lenovo-administrator', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_lenovo_administrator', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'ASP Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
				case get_string('asp_portfolio', 'local_swtc'):
					$temp = array();
					$this->array_find_deep($roles, 'shortname', 'asp-maintech-', $temp);

					foreach ($temp as $role) {
						if ($role['shortname'] == get_string('role_asp_maintech_student', 'local_swtc')) {
							$cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role['id'];
						}
					}
					break;

				// SWTC ********************************************************************************
				// 'PremierSupport Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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

				// SWTC ********************************************************************************
				// 'ServiceDelivery Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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

				// SWTC ********************************************************************************
				// 'Site Help Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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

				// SWTC ********************************************************************************
				// 'Curriculums Portfolio' - add the roleids that have access to the top-level category.
				// SWTC ********************************************************************************
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
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Exiting /local/swtc/classes/SwtcUser.php. ===loadallcatsaccess.exit.";
			$messages[] = "SWTC ********************************************************************************.";
			// debug_enable_phplog($debug);
			$messages[] =  "cats array follows:";
			// $messages[] = print_object($cats);
			$messages[] = print_r($cats, true);
			$messages[] = "cats array ends.";
			// print_object($cats);        // Debug - 02/25/21
			$debug->logmessage($messages, 'detailed');
			unset($messages);
			// die();
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

		return array($tmp);

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
	function change_user_access($cat) {
		global $DB;

		// SWTC ********************************************************************************.
		// SWTC SWTC swtc_user and debug variables.
		// $this = swtc_get_user($user);
		$debug = swtc_get_debug();
		// print_object($this);

		// Other SWTC variables.
		$user_access_type = $this->user_access_type;
		$roleshortname = null;
		// SWTC ********************************************************************************.

		if (isset($debug)) {
			// SWTC ********************************************************************************
			// Always output standard header information.
			// SWTC ********************************************************************************
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "Entering /local/swtc/classes/SwtcUser.php. === change_user_access.enter.";
			$messages[] = "SWTC ********************************************************************************.";
			$messages[] = "swtc_user array follows :";
			$messages[] = print_r($this, true);
			$messages[] = "swtc_user array ends.";
			// $debug->logmessage(print_r($swtc, true), 'detailed');
			$debug->logmessage($messages, 'both');
			unset($messages);
		}

		// $topcat = $cat['catname'];
		// print_object("In local_swtc_change_user_access. catname to check is :$topcat.');

		// 01/10/19 - Just a test...
		// if (has_capability($cat['capability'], $cat['context'])) {
		// 	print_object("User has access to category $topcat');
		// } else {
		// 	print_object("User does NOT have access to category $topcat');
		// }

		// SWTC ********************************************************************************
		// Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
		// Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
		// SWTC ********************************************************************************
		// print_object($user->user_access_type);
		// if (stripos($user->user_access_type, get_string('access_premiersupport_pregmatch_manager', 'local_swtc')) !== false) {
		// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), stripos was true');
		// } else {
		// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), stripos was false');
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
		if (preg_match(get_string('access_premiersupport_pregmatch_manager', 'local_swtc'), $user_access_type)) {
			// If the portfolio is PremierSupport, continue with the mgr access.
			if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
				// $debug->logmessage("In found premiersupport_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
				// $debug->logmessage("In NOT found premiersupport_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// PremierSupport administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_premiersupport_pregmatch_administrator', 'local_swtc'), $user_access_type)) {
			// If the portfolio is PremierSupport, continue with the admin access.
			if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
				// $debug->logmessage("In found premiersupport_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
				// $debug->logmessage("In NOT found premiersupport_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// PremierSupport GEO administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
			// If the portfolio is PremierSupport, continue with the GEO admin access.
			if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
				// $debug->logmessage("In found premiersupport_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
				// $debug->logmessage("In NOT found premiersupport_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// PremierSupport site administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_premiersupport_pregmatch_siteadministrator', 'local_swtc'), $user_access_type)) {
			// If the portfolio is PremierSupport, continue with the site admin access.
			if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
				// $debug->logmessage("In found premiersupport_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
				// $debug->logmessage("In NOT found premiersupport_portfolio.", 'detailed');
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
		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_manager', 'local_swtc'), $user_access_type)) {
			// If the portfolio is ServiceDelivery, continue with the mgr access.
			// print_object("I found a servicedelivery-mgr.');
			if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
				// $debug->logmessage("In found servicedelivery_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
				// $debug->logmessage("In NOT found servicedelivery_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// ServiceDelivery administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_administrator', 'local_swtc'), $user_access_type)) {
			// If the portfolio is ServiceDelivery, continue with the admin access.
			if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
				// $debug->logmessage("In found servicedelivery_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
				// $debug->logmessage("In NOT found servicedelivery_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// ServiceDelivery GEO administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
			// If the portfolio is ServiceDelivery, continue with the admin access.
			if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
				// $debug->logmessage("In found servicedelivery_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
				// $debug->logmessage("In NOT found servicedelivery_portfolio.", 'detailed');
			}
		//****************************************************************************************.
		// ServiceDelivery site administrators
		//****************************************************************************************.
		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadministrator', 'local_swtc'), $user_access_type)) {
			// If the portfolio is ServiceDelivery, continue with the admin access.
			if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
				$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
				// $debug->logmessage("In found servicedelivery_portfolio.", 'detailed');
			} else {
				// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
				// $debug->logmessage("In NOT found servicedelivery_portfolio.", 'detailed');
			}
		}
		// SWTC ********************************************************************************
		// Remember to set the roleid.
		// 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
		//						if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
		//						it was when this was called.
		// SWTC ********************************************************************************
		if (!empty($roleshortname)) {
			$this->roleshortname = $roleshortname;
			$role = $DB->get_record('role', array('shortname' => $this->roleshortname), '*', MUST_EXIST);
			$this->roleid = $role['id'];
		} else {

		}

		// return $tmp_user;
		return;
	}



	// Function to recursively search for a given value.
	//      For example, if this is the multi-dimensional array:
	//      Array
	//      (
	//          [studs_menu] => Array
	//              (
	//                  [1478973742] => Array
	//                      (
	//                          [uuid] => 1478973742
	//                          [groups] => 18421, 18422, 18423, 18424, 18425
	//                      )
	//
	//              )
	//
	//          [mgrs_menu] => Array
	//              (
	//                  [168690638] => Array
	//                      (
	//                          [uuid] => 168690638
	//                          [groups] => 18426, 18427, 18428, 18429, 18430
	//                      )
	//
	//              )
	//
	//          [admins_menu] => Array
	//              (
	//                  [630459861] => Array
	//                      (
	//                          [uuid] => 630459861
	//                          [groups] => 18431, 18432, 18433, 18434, 18435
	//                      )
	//
	//              )
	//
	//      )
	//
	//      If you are searching for "168690638", the following will be returned:
	//      Array
	//      (
	//          [0] => mgrs_menu
	//          [1] => 168690638
	//          [2] => uuid
	//      )
	/**
	 * Version details
	 *
	 * History:
	 *
	 * 02/24/21 - Initial writing.
	 *
	 **/
	function array_find_deep($array, $key, $value, array &$results = [])
	{
		if (!is_array($array)) {
			return;
		}

		$key = str_replace('/', '\\/', $key);

		foreach ($array as $arrayKey => $arrayValue) {
			// print_object("key is $arrayKey\n");
			// print_object("value is \n");
			// print_object($arrayValue);
			if ((preg_match("/$key/i", (string)$arrayKey)) && (preg_match("/$value/i", (string)$arrayValue))) {
				// add array if we have a match
				// print_r("Did I get here??\n");
				$results[] = $array;
			}

			if (is_array($arrayValue)) {
				// only do recursion on arrays
				$this->array_find_deep($arrayValue, $key, $value, $results);
			}
		}
	}
}
