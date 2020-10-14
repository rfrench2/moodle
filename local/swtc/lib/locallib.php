<?php
// declare(strict_types=1); // For debugging.
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

/*****************************************************************************
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/locallib.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 *	03/14/16 - Added 'PORTFOLIO_LENOVOINTERNAL' constant; changed value of 'PORTFOLIO_NONE.
 *	03/29/16 - Added 'PORTFOLIO_LENOVOSHAREDRESOURCES' constant.
 *	05/11/16 - Added code to support Maintech portfolio.
 *	05/21/16 - v10 - In Moodle GUI, removed Lenovo-administrator from System and User contexts (no access to anything); change processing the
 *									user type "Lenovo-admin" to any other user type (i.e. remove Lenovo-admin shortcuts).
 * 06/21/16 - v11 - Added check of $USER->id before doing much of anything (i.e. if empty, the user has not logged on yet).
 * 06/25/16 - v11 - Added back user_loggedinas so admins can test access for users.
 * 08/11/16 - v12 - Removed 'Arrow' and 'Global Knowledge".
 *	08/25/16 - Changed "Lenovo and IBM Portfolio" values to just "IBM Portfolio" so that values will be the same (i.e. will help in transition).
 *	08/28/16 - Adding capturing of debug information to file (rather than print on screen). Using $CFG->dirroot. '/local/swtc as output folder location (for now).
 *	08/31/16 - Adding Lenovo-stud and Lenovo-inst to Maintech Portfolio.
 *	09/27/16 - Added "gtp-siteadmin", "gtp-siteadministrator", "AV-GTP-siteadmin", "LQ-GTP-siteadmin", and "IM-GTP-siteadmin".
 *	10/05/16 - Added hybrid role of ASP / Maintech student.
 * 10/05/17 - Adding support for ASP Portfolio.
 * 11/22/17 - Verifying "Lenovo-student" access to ASP Portfolio.
 * 03/03/18 - Added PremierSupport portfolio and all associated information.
 * 03/23/18 - Changing how local_swtc_get_user_access is called (in preparation for calling from theme-adaptable).
 * 04/16/18 - Added new $SESSION->EBGLMS global variable and all its required changes.
 * 05/03/18 - Changed the "require_once($CFG->dirroot.'/local/swtc/lib/swtc_debug.php');" to be dependent on the setting
 *                          of a local variable that must be named $debug: "$debug = new stdClass();" = debugging on;
 *                          null = debugging off (since we are using isset() for the check).
 * 05/16/18 - For testing, added PremierSupport-mgr1, PremierSupport-mgr2, and PremierSupport-mgr3 roles.
 * 06/03/18 - Added check for new swtcdebug setting.
 * 07/10/18 - Changed some messages for clarity; changed entire if ($eventdata->relateduserid) in local_swtc_assign_user_role.
 * 07/11-13/18 - In local_swtc_assign_user_role, changed PremierSupport roles to only have PremierSupport-student roles
 *                          outside the PremierSupport portfolio (even administrators and managers). This is to prevent PremierSupport admins
 *                          and mgrs from having more access to a course when they are enrolled in a course outside the PremierSupport portfolio.
 * 08/14/18 - Added SiteHelp portfolio.
 * 11/07/18 - Added additional access type strings for all PremierSupport user types.
 * 11/15/18 - Added check for ServiceDelivery managers and administrators.
 * 11/30/18 - Changed access type names for ServiceDelivery and added access for the appropriate user types; modified
 *                      access for PremierSupport.
 * 12/11/18 - If a user has more than one role in a particular category (after a bug that allowed it), the existing code is "assuming" only one
 *						role exists for the user (depending on order they are checked, it could be correct or incorrect). To fix, if multiple roles are found,
 *						loop through looking for the correct one and remove all the incorrect ones.
 * 01/10/19 - Changing all occurrences of "if (something)" to "if (!empty(something))"; for checking access to top level category, replaced
 *						checking of multiple access types with the appropriate has_capability(local/swtc:ebg-access-***-portfolio) check; changed
 *						how local_swtc_change_user_access works and how it is called; has_capability checking did not work as expected - going
 *						back to previous check.
 * 01/17/19 - In local_swtc_change_user_access, replaced checking of multiple access types with switch statement to multiple stripos
 *						checks (similar to local_swtc_change_user_access in locallib.php).
 *  01/24/19 - In local_swtc_change_user_access, due to the updated PremierSupport and ServiceDelivery user access types,
 *						using preg_match to search for access types.
 * 02/07/19 - Moved get_tree to swtclib.php.
 * 03/03/19 - In local_swtc_get_user_access and local_swtc_change_user_access, added PS/AD site administrator user access types.
 * 03/08/19 - In local_swtc_get_user_access and local_swtc_change_user_access, added PS/AD GEO site administrator user access types.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 06/13/19 - Added array_key_first (until we update to PHP 7.3).
 * 08/22/19 - Added local_swtc_get_all_accesstypes; added local_swtc_get_all_courses.
 * 08/26/19 - Moved relatedcourses_capture_enrollment to /local/swtc/lib/locallib.php as local_swtc_capture_enrollment.
 * 10/08/19 - Added code to remove shared simulator course (lensharedsimulators_shortname); added more comments to existing Lenovo code.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/21/19 - Added local_swtc_find_and_remove_shared_resources (for example, "LenInternalSharedResources" or "ES10000");
 *                      subtle technical issue with using array_search and array_column (returning the incorrect array key).
 * 10/31/19 - Added correct setting of swtc_user information.
 * 11/01/19 - Modified how the new Lenovo EBGLMS classes and methods are called throughout all customized code.
 * 11/03/19 - Moved local_swtc_find_and_remove_shared_resources to /local/swtc/lib/swtc_lib_enrollib.php.
 * 11/12/19 - IMPORTANT! If event is user_enrolment_created and enrol_user (in /lib/enrollib.php) is called from lib.php (in /enrol/autoenrol),
 *                       the userid will be passed in $event->relateduserid, NOT in $event->id (i.e. $event->id will be 0).
 * 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
 *                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
 * 11/27/19 - In local_swtc_assign_user_role, removed call to \core_course_category::get(0)->get_courses(array('recursive' => true))
 *                      as it was creating possible side effect with other functions.
 * 12/12/19 - In local_swtc_capture_click, updated to add record in "local_swtc_rc" if the record doesn't exist (because we are
 *                      dynamically determining the related courses, this will be a common occurrence).
 * 12/17/19 - Added Curriculum portfolio and all associated information.
 * 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after inserting (needed
 *                      for log event functions); added several log event functions to write events and data to mdl_logstore_standard_log.
 * PTR2019Q401 - @01 - 03/12/20 - Added local_swtc_get_all_pssd_roleids to /local/swtc/locallib.php for easier access
 *                  checking; added local_swtc_get_user_profile to /local/swtc/locallib.php to load the user profile of the user.
 * PTR2020Q109 - @02 - 05/13/20 - Newer versions of the local_cohortrole plugin do not include the local_cohortrole_exists function;
 *                  added local_swtc_cohortrole_exists function (called from Moosh scripts); added access to PremierSupport Portfolio
 *                  (local_swtc_get_user_access) for SD users (so they can access PSC0003 and all courses that is in the PSC0003 curriculum).
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS classes.
// Lenovo ********************************************************************************.
use \format_swtccustom\output\htmlpage;

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');   // Include constants.

//require_once ($CFG->dirroot.'/course/format/lib.php');
//require_once($CFG->dirroot.'/course/format/swtccustom/lib.php');
require_once ($CFG->dirroot. '/config.php');
// require_once($CFG->libdir. '/coursecatlib.php');     // Removed for Moodle 3.6
require_once($CFG->libdir. '/accesslib.php');
require_once($CFG->dirroot. '/user/profile/lib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot. '/user/lib.php');

// Lenovo ********************************************************************************
// Note: top_level_categories, capabilities, and portfolio values are now located in $SESSION->EBGLMS.
//     To use: $swtc = $SESSION->EBGLMS;
//                 $portfolios = $SESSION->EBGLMS->PORTFOLIOS;
//                 $top_level_categories = $SESSION->EBGLMS->STRINGS->top_level_categories;
//                 $capabilities = $SESSION->EBGLMS->STRINGS->capabilities;
//                 $swtc_user = $SESSION->EBGLMS->USER;
// Lenovo ********************************************************************************

/**
 * Checks for the following:
 *		Checks to see if the user has been assigned a role at login (message '\core\event\user_loggedin'). If not, assign them the appropriate role
 *		based on the customized user profile setting 'Accesstype'.
 *		When creating a user (message '\core\event\user_created'),  assign them the appropriate role based on the customized user profile
 *      setting 'Accesstype'. At user update (message '\core\event\user_updated'),  assign them the appropriate role based on the customized
 *      user profile setting 'Accesstype'.
 *		When viewing the frontpage (message '\core\event\user_updated') where courseid = 1, reload the page (to refresh the users new roles
 *        if necesary).
 *		When a user self-enrolls in a course (message '\core\event\role_assigned'), a default role is given to the user (different default roles
 *			 depending on the portfolio). If the course that the user is enrolling in has a metalink to the 'Shared Resources (Master)' (for example,
 *          a simulator), two enrollments are performed. When each '\core\event\role_assigned' message is processed, remove the default role
 *            for the user and assign them the appropriate role based on the customized user profile setting 'Accesstype'.
 *
 * For example,
 *		If the user is a GTP-stud user, assign them the GTP-stud role.
 *
 * Important! Prerequiste is all users have the user profile setting 'Accesstype' set. Another prerequisite is that all the courses defined in the Lenovo
 *		Server Education LMS system using the new swtccustom course format (it saves a course portfolio flag in the course settings). Flag is:
 * 	$COURSE->coursetype
 *
 * History:
 *
 * 10/08/19 - Added code to remove shared simulator course (lensharedsimulators_shortname).
 *	10/16/19 - Added this header; changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/31/19 - Added correct setting of swtc_user information.
 * 11/12/19 - IMPORTANT! If event is user_enrolment_created and enrol_user (in /lib/enrollib.php) is called from lib.php (in /enrol/autoenrol),
 *                       the userid will be passed in $event->relateduserid, NOT in $event->id (i.e. $event->id will be 0).
 * 11/27/19 - In local_swtc_assign_user_role, removed call to \core_course_category::get(0)->get_courses(array('recursive' => true))
 *                      as it was creating possible side effect with other functions.
 *
 */

function local_swtc_assign_user_role($eventdata) {
	global $CFG, $USER, $DB, $PAGE, $COURSE, $OUTPUT, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER, $eventdata->relateduserid);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $swtc_resources = $SESSION->EBGLMS->RESOURCES;

    $user_related = null;           // Only set IF working with a related user (i.e. swtc_get_relateduser is called).
    $eventname = '';						// The event / message that has been triggered.
	$admins = '';							// The array of all the defined admins (Note: Some of the role methods will not work correctly if the userid is an admin).
	$catlist = array();					// A list of all the top-level categories defined (returned from local_swtc_get_user_access).
    $tmp_user = new stdClass();    // Hold return values from local_swtc_get_user_access.
	$allcourses = '';							// A list of all courses user has access to.
    // Lenovo ********************************************************************************.

	if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering /local/swtc/lib/locallib.php===local_swtc_assign_user_role.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "swtc_user array follows :";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends.";
        $messages[] = "eventname follows :";
        $messages[] = print_r($eventdata->eventname, true);
        $messages[] = "eventname ends.";
        // debug_logmessage(print_r($swtc, true), 'detailed');
        debug_logmessage($messages, 'both');
        unset($messages);

		// Lenovo ********************************************************************************
        // Additional diagnostic information.
        // Lenovo ********************************************************************************
        // $messages[] = "About to print strings.";
        // $messages[] = print_r($SESSION->EBGLMS->STRINGS, true);
        // $messages[] = "Finished printing strings.";
        // debug_logmessage($messages, 'detailed');
        // unset($messages);
	}

	//****************************************************************************************
	// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
	//****************************************************************************************
	if ( empty($USER->id)) {
		if (isset($debug)) {
			debug_logmessage("User has not logged on yet; local_swtc_assign_user_role.exit===2.1===.", 'logfile');
		}
		return;
	}

    // Lenovo ********************************************************************************
    // Removed a section of code, comments, or both. See archived versions of module for information.
    // Lenovo ********************************************************************************

	// Load the event name (if it's needed later).
	$eventname = $eventdata->eventname;

	if (isset($debug)) {
		$messages[] = "eventname is :<strong>$eventname</strong>.";
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // Lenovo ********************************************************************************
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
    // Lenovo ********************************************************************************
    if ($eventname == '\core\event\course_viewed') {
        if (isset($debug)) {
            if ($eventdata->courseid == 1) {
                debug_logmessage("User is viewing the front page (courseid = 1). Continuing...", 'logfile');
                //debug_logmessage("eventdata properties follow...", 'logfile');
                //print_object($eventdata, true);
                //debug_logmessage("Finished printing eventdata ===11===.", 'logfile');
                purge_all_caches();         // 04/26/18 - RF - testing of refreshing front page so student can see new course they are enrolled in.
            } else {
                debug_logmessage("User is viewing a course. About to return.", 'logfile');
                debug_logmessage("Leaving local_swtc_assign_user_role.exit===11===.", 'logfile');
                purge_all_caches();
                return;
            }
        }
    }

    // Lenovo ********************************************************************************
	// Important! Properties passed via $eventdata defined in https://docs.moodle.org/dev/Event_2#Information_contained_in_events
	//	Note: <strong> and </strong> begins and ends bold printing;   adds CRLF to end of print statement.
    // Lenovo ********************************************************************************
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
        debug_logmessage($messages, 'both');
        unset($messages);

        $messages[] = "all eventdata properties follow :";
		$messages[] = print_r($eventdata, true);
		$messages[] = "all eventdata properties end.";
        debug_logmessage($messages, 'detailed');
        unset($messages);
	}

    // Lenovo ********************************************************************************
    // Removed a section of code, comments, or both. See archived versions of module for information.
    // Lenovo ********************************************************************************

    // Lenovo ********************************************************************************
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
	// Lenovo ********************************************************************************
	if (!empty($eventdata->relateduserid)) {		// 01/10/19

        if (isset($debug)) {
			// Lenovo ********************************************************************************
            // 07/10/18 - Changed some messages for clarity.
            // Lenovo ********************************************************************************
            switch ($eventname) {

                // Lenovo ********************************************************************************
                // Event \core\event\user_loggedinas
                // Lenovo ********************************************************************************
                case '\core\event\user_loggedinas':
                    debug_logmessage("Admin has logged on as user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event \core\event\user_updated
                // Lenovo ********************************************************************************
                case '\core\event\user_updated':
                    debug_logmessage("Admin has updated a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event \core\event\user_created
                // Lenovo ********************************************************************************
                case '\core\event\user_created':
                    debug_logmessage("Admin has created a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event \core\event\role_assigned
                // Lenovo ********************************************************************************
                case '\core\event\role_assigned':
                    debug_logmessage("Admin has triggered a role assignment on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event \core\event\user_enrolment_deleted
                // Lenovo ********************************************************************************
                case '\core\event\user_enrolment_deleted':
                    debug_logmessage("Admin has triggered an unenrollment from a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event \core\event\user_enrolment_updated
                // Lenovo ********************************************************************************
                case '\core\event\user_enrolment_updated':
                    debug_logmessage("Admin has triggered an updated enrollment in a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
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
                // Lenovo ********************************************************************************
                case '\core\event\user_enrolment_created':
                    debug_logmessage("Admin has triggered an enrollment in a course on behalf of a user (eventname is <strong>$eventname</strong>).", 'both');
                    break;

                // Lenovo ********************************************************************************
                // Event - all others
                // Lenovo ********************************************************************************
                default:
                    debug_logmessage("Something happened. Log it. (eventname is <strong>$eventname</strong>).", 'both');
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
            $messages[] = "In top of local_swtc_assign_user_role (relateduserid). Setting swtc_user->relateduser information of $eventdata->relateduserid. ===11===.";
            $messages[] = "swtc_get_relateduser follow:";
            $messages[] = print_r($swtc_user->relateduser, true);
            $messages[] = "swtc_get_relateduser end.";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        if (isset($debug)) {
            $messages[] = "In top of local_swtc_assign_user_role (relateduserid). After setting swtc_user to new values.";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }
    } else {
        // Lenovo ********************************************************************************
        // 04/23/18: Since $swtc_user->userid and $swtc_user->user_access_type should already be set by now,
        //                  removing this section.
        // Lenovo ********************************************************************************
        // Set the users userid and access_type.
        //  if (!isset($swtc_user->userid)) {
        //      if (isset($debug)) {
        //          debug_logmessage("In top of local_swtc_assign_user_role. assigning userid of $eventdata->userid. ===11===.", 'logfile');
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
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // Lenovo ********************************************************************************
    // Removed a section of code, comments, or both. See archived versions of module for information.
    // Lenovo ********************************************************************************


	// Lenovo ********************************************************************************
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
    // Lenovo ********************************************************************************
    // No need to return access (values set directly in EBGLMS) nor allroles (called again in swtc_load_roleids).
    //  No need to send $swtc_user->user_access_type as that is part of EBGLMS.
    //      Also update timestamp.
    // Lenovo ********************************************************************************
    list($catlist, $tmp_user) = local_swtc_get_user_access();

    // Lenovo ********************************************************************************
    // Finished. Set $swtc_user to all the appropriate values.
    //      And set new timestamp.
    // Lenovo ********************************************************************************
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

    // Lenovo ********************************************************************************
	// Note: At this point the $catids array should be fully created...
    // Lenovo ********************************************************************************
	if (isset($debug)) {
		// $messages[] = "catlist array follows: ";
        // $messages[] = print_r($catlist, true);
        // $messages[] = "catlist array ends.";
        $messages[] = "swtc_user array follows: ";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends.";
        debug_logmessage($messages, 'detailed');
        unset($messages);
	//	die();
	}

    // Lenovo ********************************************************************************
	// Debug: get the list of all courses the user has access to. In other words, if they don't have access to any, count will be 0.
	// 		Note: This returns a 'course_in_list' array. Take a look in ./course/classes/management/helper.php for functions.
    // 11/27/19 - Removed call to \core_course_category::get(0)->get_courses(array('recursive' => true)) as it was creating possible
    //                      side effect with other functions.
    // Lenovo ********************************************************************************
	// if (isset($debug)) {
		// debug_logmessage("In user_loggedin message.", 'logfile');
		// $allcourses = coursecat::get(0)->get_courses(array('recursive' => true));        // Moodle 3.6
        // $allcourses = \core_course_category::get(0)->get_courses(array('recursive' => true));
		// $totalcount = count($allcourses);
		// debug_logmessage("Total number of courses the user has access to is :<strong>$totalcount.</strong>", 'logfile');
	//	print_object($allcourses, true);
	// }

    // Lenovo ********************************************************************************
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
    // Lenovo ********************************************************************************
	if ($eventname == '\core\event\role_assigned') {

		$manager = new course_enrolment_manager($PAGE, $COURSE);
		$is_meta = false;						// Important! If this variable is still false later, then get_records on course; if true, get_records on metacourse.

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
            // Lenovo *******************************************************************************.
            // 10/08/19 - metacourse could be either of two courses.
            // Lenovo *******************************************************************************.
            $messages[] = "metacourse1 courseid :$swtc_resources->sharedres_courseid.";
            $messages[] = "metacourse2 courseid :$swtc_resources->lensharedsimulators_courseid.";
            debug_logmessage($messages, 'logfile');
            unset($messages);
		}

		// Get the courseid that the user has enrolled in.
		$courseid = $eventdata->courseid;

        // Lenovo ********************************************************************************
		// If the student self-enrolled in a course that has a metacourse, two '\core\event\role_assigned' messages are generated.
		//		Is $eventdata->courseid the course or the metacourse? If it's the metacourse, return. If not, continue.
        // 10/08/19 - metacourse could be either of two courses.
        // Lenovo ********************************************************************************
        // if ($courseid == $swtc_resources->sharedres_courseid) {            // 10/08/19
        if (($courseid == $swtc_resources->sharedres_courseid) || ($courseid == $swtc_resources->lensharedsimulators_courseid)) {
			if (isset($debug)) {
				debug_logmessage("eventdata->courseid is :<strong>$courseid</strong>; the metacourse id is either :", 'logfile');        // 10/08/19
                debug_logmessage("<strong>$swtc_resources->sharedres_courseid</strong>.", 'logfile');     // 10/08/19
                debug_logmessage("<strong>$swtc_resources->lensharedsimulators_courseid</strong>.", 'logfile');     // 10/08/19
				debug_logmessage("Returning.", 'logfile');
			}
			return;
		}

		// Create a context to the course.
		$context = context_course::instance($courseid, MUST_EXIST);

		if (isset($debug)) {
			debug_logmessage("User has enrolled in course (courseid :<strong>$courseid</strong>).", 'logfile');
			debug_logmessage("About to print courseid $courseid context.", 'logfile');
            debug_logmessage(print_r($context, true), 'logfile');
			// print_object($context, true);
			// $DB->set_debug(true);
		}

		// Get the role assignments of the user in the course (get_records on course or metacourse depending on is_meta value).
		$ras = $DB->get_records('role_assignments', array('userid'=>$swtc_user->userid, 'contextid'=>$context->id));

		// Lenovo ********************************************************************************
		// The default role for a student self-enrolling in a course depends on what portfolio the course is in. In any event, delete whatever it is
        //      and add what the user's 'real' role should be.
		//
		//		Note: $access is set for each user in code above.
        //          And update timestamp.
        // Lenovo ********************************************************************************
        // Lenovo ********************************************************************************
        // Get the top-level category for this catid.
        // Lenovo ********************************************************************************
        $topcatid = swtc_toplevel_category($COURSE->category);
        // Look for key in catlist array.
        $key = array_search($topcatid, array_column($catlist, 'catid'));
        // $topcatname = $catlist[$key]['catname'];		// 01/10/19
		$topcat = $catlist[$key];			// 01/10/19

        // Lenovo ********************************************************************************
        // 07/18/18 - Added check if user_related is set.
        //                  If so, use that user information to determine access; to make sure all changes are made, changing swtc_user to temp_user
        //                  (since no information is saved in this section); remember to unset SESSION->EBGLMS->USER->relateduser at the end.
        // Lenovo ********************************************************************************
        if (isset($user_related)) {
            $temp_user = $user_related;
        } else {
            $temp_user = $swtc_user;
        }

        // print_r("after second call to isset - user_related.\n");   // 11/30/18 - RF - testing...
        // print_object($temp_user);

        // Lenovo ********************************************************************************
        // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
        // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
        // Lenovo ********************************************************************************
		// print_object("about to call 1");
        // $temp_user = local_swtc_change_user_access($topcatname, $temp_user);		// 01/10/19
		local_swtc_change_user_access($topcat, $temp_user);			// 01/10/19
		// print_object($temp_user);
		if (isset($debug)) {
			// $DB->set_debug(false);
			debug_logmessage("Correct roleid is :<strong>$temp_user->roleid</strong>.", 'logfile');
			debug_logmessage("Printing ras before deleting incorrect role.", 'logfile');
            debug_logmessage(print_r($ras, true), 'logfile');
			// print_object($ras, true);
			debug_logmessage("Finished printing ras before deleting incorrect role.", 'logfile');
		}

        // Lenovo ********************************************************************************
		// Loop through each role assignment record the user has in the course and, if $ra->roleid is different than $temp_user->roleid,
        //      delete the enrollment record.
        // Lenovo ********************************************************************************
		foreach($ras as $ra) {
			//if (isset($debug)) {
			//	debug_logmessage("Printing enrollment record:", 'logfile');
			//	print_object($ra, true);
			//	debug_logmessage("Finished printing enrollment record.", 'logfile');
			//}

			// If the roleid assigned is different than what it should be, delete it.
			if (($ra->userid == $temp_user->userid) && ($ra->roleid != $temp_user->roleid)){
				$DB->delete_records('role_assignments', array('id'=>$ra->id));
			}
		}

		if (isset($debug)) {
			$tmp = $DB->get_records('role_assignments', array('userid'=>$temp_user->userid, 'contextid'=>$context->id));
			debug_logmessage("Printing ras after deleting incorrect role.", 'logfile');
            debug_logmessage(print_r($tmp, true), 'logfile');
			// print_object($tmp, true);
			debug_logmessage("Finished printing ras after deleting incorrect role.", 'logfile');
		}

		// Lenovo ********************************************************************************
		// Finally, assign the user to the correct role in the course.
		// Lenovo ********************************************************************************
		//if ( !$manager->assign_role_to_user($temp_user->roleid, $swtc_user->userid)) {
		if ( !$ra = role_assign($temp_user->roleid, $temp_user->userid, $context->id)) {
			if (isset($debug)) {
				$newshortname = $temp_user->roleshortname;
				debug_logmessage("Error assigning <strong>$newroleid ($newshortname)</strong> in course <strong>$courseid</strong>. Returning.", 'logfile');
				return;
			} else {
				// debug_logmessage("Successful assigning <strong>$newroleid ($newshortname)</strong> in course <strong>$courseid</strong>.", 'logfile');
			}
		}

		if (isset($debug)) {
			$tmp = $DB->get_records('role_assignments', array('userid'=>$temp_user->userid, 'contextid'=>$context->id));
			debug_logmessage("Printing ras after assigning new role.", 'logfile');
            debug_logmessage(print_r($tmp, true), 'logfile');
			// print_object($tmp, true);
		}

		// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
		$context->mark_dirty();
		purge_all_caches();

		if (isset($debug)) {
			debug_logmessage("Leaving role_assigned message ===11.5===.", 'logfile');
		}

		return;
	}

    // Lenovo ********************************************************************************
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
	//	Remember! $catlist array format is below (defined in function local_swtc_get_user_access):
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
    //                  (since no information is saved in this section); remember to unset SESSION->EBGLMS->USER->relateduser at the end.
    // Lenovo ********************************************************************************
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
                debug_logmessage($messages, 'detailed');
                unset($messages);
			} else {
				debug_logmessage("Userid <strong>$temp_user->userid</strong> does <strong>NOT</strong> have any roles in <strong>$temp</strong>.", 'both');
			}
		}

		$catid = $catlist[$key]['catid'];

        // SHOULD the user have access to this category?" (If the category id's match, enter).
        // Lenovo ********************************************************************************
        // Removed a section of code, comments, or both. See archived versions of module for information.
        // Lenovo ********************************************************************************
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
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        // 11/08/18 - Fix after trying to put into production.
		// 01/10/19 - Changing to has_capability call; testing...not working...going back...
        if (array_search($catid, array_column($temp_user->categoryids, 'catid')) !== false) {		// 01/10/19
		// if (has_capability($catlist[$key]['capability'], $context)) {			// 01/10/19
		// if (has_capability($catlist[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['capability'],
        //        $cats[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['context']))

            // $temp = $catlist[$key]['catname'];      // 11/27/18 - Moved here from three lines below.		// 01/10/19 - Changed (see line below).
			$temp = $catlist[$key];		// 01/10/19

			if (isset($debug)) {
				// $temp = $catlist[$key]['catname'];   // 11/27/18 - Moved above.
				$catname = $catlist[$key]['catname'];
				debug_logmessage("User <strong>$temp_user->userid SHOULD</strong> have access to category <strong>$catname</strong>.", 'logfile');
			}

            // Lenovo ********************************************************************************
			// Does the current user have any roles assigned in this category? If so, check to make sure it's the CORRECT role.
			//		What does CORRECT role mean? The CORRECT role would be the one that the user should have been assigned
			//			(based on the 'Access type' flag).
			//		Note: The only way for a user to have more than one role assigned to them in a top-level category is if an administrator
			//			purposely did it (since 'Access type' is a single-select, it is impossible to get more than one role from it). For example, a user
			//			was given the GTP-student AND GTP-instructor role in the 'GTP Portfolio' top-level category.
            // Lenovo ********************************************************************************
			if ($countroles != 0) {

                // $temp = $catlist[$key]['catname'];		// 01/10/19 - Not needed; set above.

				if (isset($debug)) {
					debug_logmessage("And <strong>DOES</strong>. Next is to check if it is the CORRECT access.", 'logfile');
				//	print_object($userroles, true);
				}
				// Does the current user have the CORRECT role assigned in this category?
				// For each of the roles, if $role->id == $swtc_user->roleid, the user has the correct access. If they don't match,
                //      remove the user from the role.
				foreach ($userroles as $role) {

                    // Lenovo ********************************************************************************
                    // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                    // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                    // Lenovo ********************************************************************************
					// print_object("about to call 2");
                    local_swtc_change_user_access($temp, $temp_user);		// 01/10/19

                    if ($role->roleid != $temp_user->roleid) {

						if (isset($debug)) {
							$tempid = $temp_user->roleid;
							$tempname = $temp_user->roleshortname;
							$messages[] = "However, user <strong>$temp_user->userid</strong> has been given an incorrect role. It should be <strong>$tempid ($tempname)</strong> but is <strong>$role->roleid ($role->shortname)</strong>.";
							$messages[] = "Action: will remove user from role <strong>$role->roleid</strong>; will add user to role <strong>$tempid</strong>.";
                            $messages[] = "parameters to role_unassign are :role->roleid is $role->roleid; temp_user->userid is $temp_user->userid; catlist[$key]['context']->id is :";
                            $messages[] = print_r($catlist[$key]['context']->id, true);
                            debug_logmessage($messages, 'both');
                            unset($messages);
						}

						// Unassign the user from the incorrect role...
                        role_unassign($role->roleid, $temp_user->userid, $catlist[$key]['context']->id);

						// Assign the user to the correct role...
                        $id = role_assign($temp_user->roleid, $temp_user->userid, $catlist[$key]['context']->id);

						// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
						$catlist[$key]['context']->mark_dirty();

                        // 07/13/18 - If the above role_unassign followed by role_assign worked, or didn't work, the user would STILL have roles
                        //                  in this category. So, there is not much gain in checking access again. So, just continue.
					} else {
                        if (isset($debug)) {
                            debug_logmessage("It is the correct access.", 'logfile');
                        }
                    }
				}
			} else {
				// The user SHOULD have access; the user does NOT have a role assigned in the category. Add the user to the role.
                // Lenovo ********************************************************************************
                // 07/12/18 - Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
                // 11/15/18 - Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
                // Lenovo ********************************************************************************
				// print_object("about to call 3");
                local_swtc_change_user_access($temp, $temp_user);		// 01/10/19

				if (isset($debug)) {
					$temp = $catlist[$key]['catname'];
					$messages[] = "User <strong>$temp_user->userid</strong> does <strong>NOT</strong> have role <strong>$temp_user->roleshortname</strong> in category <strong>$temp</strong>.";
					$messages[] = "Action: will add user role to category.";
                    $messages[] = "temp_user follows:";
                    $messages[] = print_r($temp_user, true);
                    $messages[] = "Finished printing temp_user.";
                    debug_logmessage($messages, 'both');
                    unset($messages);
				}

				$id = role_assign($temp_user->roleid, $temp_user->userid, $catlist[$key]['context']->id);

				// Not sure what the following does, but assign_capability says to call it after (and it only works with using it)...
				$catlist[$key]['context']->mark_dirty();
			}
		} else {
			// The user should NOT have access to this category. Do they? If so, remove them from the role.
			if (isset($debug)) {
				debug_logmessage("countroles is :<strong>$countroles</strong>.", 'logfile');
				$temp = $catlist[$key]['catname'];
				debug_logmessage("User <strong>$temp_user->userid</strong> should <strong>NOT</strong> have access to category <strong>$temp</strong>. ", 'logfile');
			}

			// If the user has roles in this category...
			if ($countroles != 0) {

				if (isset($debug)) {
					$temp = $catlist[$key]['catname'];
					debug_logmessage("However, user <strong>$temp_user->userid DOES</strong> have access to category <strong>$temp</strong>.", 'logfile');
					debug_logmessage("Action: will remove the user from the role.", 'logfile');
					debug_logmessage("userroles array follows (before removing):", 'logfile');
                    debug_logmessage(print_r($userroles, true), 'logfile');
					// print_object($userroles, true);
					debug_logmessage("temp_user->roleid is:", 'logfile');
                    debug_logmessage(print_r($temp_user->roleid, true), 'logfile');
					// print_r($swtc_user->roleid);
					debug_logmessage("", 'logfile');
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
                        debug_logmessage($messages, 'both');
                        unset($messages);
                    }
                } else {
                    // The user now has no roles in this category.
                    if (isset($debug)) {
                        $messages[] = "User <strong>$temp_user->userid successfully removed access to category <strong>$temp</strong>.";
                        $messages[] = "updated_userroles array follows (after removing):";
                        $messages[] = print_r($updated_userroles, true);
                        debug_logmessage($messages, 'both');
                        unset($messages);
                    }
                }
			} else {
				// The user has no roles in this category.
				if (isset($debug)) {
					debug_logmessage("And does <strong>NOT</strong>.", 'logfile');
				}
			}
		}
	}

    // print_r("about to return from assign_user_role.\n");
    // print_r("printing SESSION->EBGLMS->USER.\n");   // 11/30/18 - RF - testing...
    // print_object($SESSION->EBGLMS->USER);       // At this point, USER is still messed up.

    // 07/12/18 - Remember to unset SESSION->EBGLMS->USER->relateduser at the end.
    // 11/30/18 - RF - testing...tried commenting out; didnt change anything; putting back in.
    if (isset($SESSION->EBGLMS->USER->relateduser)) {
        unset($SESSION->EBGLMS->USER->relateduser);
        unset($user_related);
    }

    // print_r("printing SESSION->EBGLMS->USER - again.\n");   // 11/30/18 - RF - testing...
    // print_object($SESSION->EBGLMS->USER);


	// Invalidate the data so that the user does not need to logoff and log back in to see changed roles...
	purge_all_caches();
	// print_object($swtc_user);     // 11/08/19 - Lenovo debugging...
	if (isset($debug)) {
		debug_logmessage("Leaving local_swtc_assign_user_role.exit===11===.", 'logfile');
	}

}

// Lenovo ********************************************************************************
// Get the logged in user customized user profile value 'Accesstype'. Accesstype is  used to determine which portfolio of classes
// the user should have access to (in other words, which top-level category they should have access to). Note that this function returns the
//	information the user 'should' have access to. What the user actually has access to (and whether they need more or less access) is
//  determined above.
// 			Important! Case of Accesstype is important. It must match the case defined in Moodle.
// 			Returns array: first element portfolio value; second element the user's role shortname (i.e. 'ibm-student' or 'gtp-administrator');
//				third element is the top-level category id the user 'should' have access to (checked above).
//
// Lenovo ********************************************************************************
/**
 * Used get the users access.
 *
 * @param N/A
 *
 * @return $array   The catlist array.
 * @return $array   An array of values used to set $SESSION->EBGLMS->USER.
 *
 *
 * History:
 *
 *  04/16/18 - Added new $SESSION->EBGLMS global variables and all its required changes.
 *  04/28/18 - Added Self support student type.
 *  06/04/18 - Added capabilities array (to hold all the capabilities of the user).
 * 11/07/18 - Added additional access type strings for all PremierSupport user types; added local_swtc_find_portfolio_name function.
 * 11/30/18 - Changed access type names for ServiceDelivery and added access for the appropriate user types; modified
 *                      access for PremierSupport.
 * 01/17/19 - In local_swtc_change_user_access, replaced checking of multiple access types with switch statement to multiple stripos
 *						checks (similar to local_swtc_change_user_access in locallib.php).
 * 01/24/19 - Due to the updated user access types, using preg_match to search for access types.
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/08/19 - Added PS/SD GEO site administrator user access types.
 *	10/16/19 - Added this header; changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/31/19 - Added correct setting of swtc_user information.
 * 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
 *                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
 * 12/17/19 - Added Curriculum portfolio and all associated information.
 * @02 - 05/13/20 - Newer versions of the local_cohortrole plugin do not include the local_cohortrole_exists function;
 *                  added local_swtc_cohortrole_exists function (called from Moosh scripts); added access to PremierSupport Portfolio
 *                  (local_swtc_get_user_access) for SD users (so they can access PSC0003 and all courses that is in the PSC0003 curriculum).
 *
 */
function local_swtc_get_user_access() {
	global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $top_level_categories = $SESSION->EBGLMS->STRINGS->top_level_categories;
    $portfolios = $SESSION->EBGLMS->PORTFOLIOS;
    $strings = $SESSION->EBGLMS->STRINGS;

    $cats = array();						        // A list of all the top-level category information defined (returned to local_swtc_assign_user_role).
    $temp_user = new stdClass();    // Returned to calling function.

    // Temporary variables. Use these during the function and return values.
    $roleshortname = null;
    $portfolio = null;
    $categoryids = array();		    // A list of all the categories the user should have access to (set in $swtc_user->categoryids).
    $capabilities = array();		    // A list of all the capabilities the user should have (set in $swtc_user->capabilities).
    $roleid = null;

    // Lenovo ********************************************************************************
    // 07/12/18 - Added check if swtc_user->relateduser is set. If so, use that user information to determine access.
    //                  Note that no switching of users below should be necessary.
    // Lenovo ********************************************************************************
    if (isset($SESSION->EBGLMS->USER->relateduser)) {
        $swtc_user = $SESSION->EBGLMS->USER->relateduser;
        $user_access_type = $SESSION->EBGLMS->USER->relateduser->user_access_type;
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_locallib.php. ===3.local_swtc_get_user_access.enter.";
        $messages[] = "swtc_user->relateduser is set; the userid that will be used throughout local_swtc_get_user_access is :<strong>$swtc_user->userid</strong>.";
        $messages[] = "swtc_user->relateduser is set; the username that will be used throughout local_swtc_get_user_access is :<strong>$swtc_user->username</strong>.";
		$messages[] = "swtc_user->relateduser is set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
    } else {
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_locallib.php. ===3.local_swtc_get_user_access.enter.";
        $messages[] = "swtc_user->relateduser is NOT set; the userid that will be used throughout local_swtc_get_user_access is :<strong>$swtc_user->userid</strong>.";
        $messages[] = "swtc_user->relateduser is NOT set; the username that will be used throughout local_swtc_get_user_access is :<strong>$swtc_user->username</strong>.";
		$messages[] = "swtc_user->relateduser is NOT set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
        $swtc_user = $SESSION->EBGLMS->USER;
        $user_access_type = $SESSION->EBGLMS->USER->user_access_type;
    }
    // Lenovo ********************************************************************************.

	if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        // $messages[] = "Lenovo ********************************************************************************.";
        // $messages[] = "Entering swtc_lib_locallib.php. ===3.local_swtc_get_user_access.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        // $phplog = debug_enable_phplog($debug, "In local_swtc_get_user_access.");
        debug_logmessage($messages, 'both');
        unset($messages);

		// Lenovo ********************************************************************************
        // Additional diagnostic information.
        // Lenovo ********************************************************************************
        // $messages[] = "About to print strings.";
        // $messages[] = print_r($strings, true);
        // $messages[] = "Finished printing strings.";
        // debug_logmessage($messages, 'detailed');
        // unset($messages);
	}

    // Lenovo ********************************************************************************
	// We already know what the user's role should be (either they have it or need it assigned to them from above).
    //      And we've loaded all the roles defined in the system (Part 1). However, we don't know the specific roleid ($role->id) assigned
    //      to that role name. In the $roles array, search for the role ($access[roleshortname]) that the user should have. Once found,
    //      save the $role->id ($userroleid) in $swtc_user->roleid. Later, we will use this array to list all the other roles the user
    //      should NOT have and remove them.
    // Lenovo ********************************************************************************
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
    // Lenovo ********************************************************************************
	$roles = get_all_roles();

    // Lenovo ********************************************************************************
    $cats = swtc_loadcatids($roles);

    // Lenovo ********************************************************************************
	// Note: At this point the $cats array should be fully created...
    // Lenovo ********************************************************************************
    //if (isset($debug)) {
    //    $messages[] = "cats array follows:";
    //    // $messages[] = print_object($cats, true);
    //    $messages[] = print_r($cats, true);
    //    $messages[] = "cats array ends.";
    //    debug_logmessage($messages, 'detailed');
    //    unset($messages);
    ////  //	die();
    //}

    // Lenovo ********************************************************************************
	// Determine what portfolio the user should be able to view based on value in access_type
	// Important! Since the switch statement is using the EXACT $access_xxx_yyy stirngs for comparison to the Accesstype flag,
	//		they must be defined that way in the /lang/en/local_swtc.php file...
    //
	// 03/29/16 - Even though all users might use a shared resource, no users should have direct access to
    //                  'Lenovo Shared Resources (Master)' except Lenovo-admins.
	//	08/31/16 - Adding Lenovo-stud and Lenovo-inst to Maintech Portfolio.
    // Lenovo ********************************************************************************
	if (isset($debug)) {
		$messages[] = "swtc_user array follows: ";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends. user_access_type to check is:  $user_access_type";
        debug_logmessage($messages, 'detailed');
        unset($messages);
		// print_object($swtc_user, true);
        // die();
	}

    // Lenovo ********************************************************************************
    // Switch on the users access type.
    //
    // Sets variables:
	//			$swtc_user->roleshortname								The actual name of the role the user has.
    //			$swtc_user->portfolio								        The name of the portfolio the user has access to.
    //			$swtc_user->categoryids								    An array of category ids the user has access to.
    //			$swtc_user->capabilities								    An array of capabilities the user has.
    //
    // Lenovo ********************************************************************************

	// Lenovo ********************************************************************************
	// Check for Lenovo-admin, Lenovo-inst, or Lenovo-stud user
	// Lenovo ********************************************************************************
	if ((stripos($user_access_type, $strings->lenovo->access_lenovo_admin) !== false) || (stripos($user_access_type, $strings->lenovo->access_lenovo_inst) !== false) || (stripos($user_access_type, $strings->lenovo->access_lenovo_stud) !== false)) {

		if (stripos($user_access_type, $strings->lenovo->role_lenovo_admin) !== false) {
			$roleshortname = $strings->lenovo->role_lenovo_administrator;
			$portfolio = $portfolios->PORTFOLIO_LENOVO;

			list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->lenovointernal_portfolio, $cats);

			list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->lenovosharedresources_portfolio, $cats);

			list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->gtp_portfolio, $cats);

            list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->curriculums_portfolio, $cats);

		} else if (stripos($user_access_type, $strings->lenovo->role_lenovo_inst) !== false) {
				$roleshortname = $strings->lenovo->role_lenovo_instructor;
				$portfolio = $portfolios->PORTFOLIO_LENOVO;
		} else if (stripos($user_access_type, $strings->lenovo->role_lenovo_stud) !== false) {
				$roleshortname = $strings->lenovo->role_lenovo_student;
				$portfolio = $portfolios->PORTFOLIO_LENOVO;
		}

		// Search for category name in cats array. When found, load the category id values.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->lenovo_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->ibm_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->maintech_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->asp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

		// 07/23/18 - Added access to PremierSupport portfolio for Lenovo-administrators until GA.
		// 11/30/18 - Changed access type names for ServiceDelivery and added access for the appropriate user types; modified
		//                      access for PremierSupport.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->premiersupport_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->servicedelivery_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->av_gtp->access_av_gtp) !== false) {

		$portfolio = $portfolios->PORTFOLIO_GTP;

		if (stripos($user_access_type, $strings->generic_role->role_gtp_siteadmin) !== false) {
			$roleshortname = $strings->generic_role->role_gtp_siteadministrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_admin) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_administrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_inst) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_instructor;
		} else if  (stripos($user_access_type, $strings->generic_role->role_gtp_stud) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_student;
		}

		// Search for category name in cats array. When found, load the category id value.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->gtp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->im_gtp->access_im_gtp) !== false) {

		$portfolio = $portfolios->PORTFOLIO_GTP;

		if (stripos($user_access_type, $strings->generic_role->role_gtp_siteadmin) !== false) {
			$roleshortname = $strings->generic_role->role_gtp_siteadministrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_admin) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_administrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_inst) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_instructor;
		} else if  (stripos($user_access_type, $strings->generic_role->role_gtp_stud) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_student;
		}

		// Search for category name in cats array. When found, load the category id value.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->gtp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->lq_gtp->access_lq_gtp) !== false) {

		$portfolio = $portfolios->PORTFOLIO_GTP;

		if (stripos($user_access_type, $strings->generic_role->role_gtp_siteadmin) !== false) {
			$roleshortname = $strings->generic_role->role_gtp_siteadministrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_admin) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_administrator;
		} else if (stripos($user_access_type, $strings->generic_role->role_gtp_inst) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_instructor;
		} else if  (stripos($user_access_type, $strings->generic_role->role_gtp_stud) !== false) {
				$roleshortname = $strings->generic_role->role_gtp_student;
		}

		// Search for category name in cats array. When found, load the category id value.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->gtp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for IBM-stud user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->ibm->access_ibm_stud) !== false) {

		$portfolio = $portfolios->PORTFOLIO_IBM;
		$roleshortname = $strings->ibm->role_ibm_student;

		// Search for category name in cats array. When found, load the category id value.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->ibm_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for ServiceProvider-stud user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->serviceprovider->access_serviceprovider_stud) !== false) {

		$portfolio = $portfolios->PORTFOLIO_SERVICEPROVIDER;
		$roleshortname = $strings->serviceprovider->role_serviceprovider_student;

		// Search for category name in cats array. When found, load the category id values.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->asp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for Maintech-stud user
    // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
    //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
	// Lenovo ********************************************************************************
	// } elseif (stripos($user_access_type, $strings->maintech->access_maintech_stud) !== false) {      // 11/25/19
    } elseif (strncasecmp($user_access_type, $strings->maintech->access_maintech_stud, strlen($user_access_type)) == 0) {   // 11/25/19

		$portfolio = $portfolios->PORTFOLIO_MAINTECH;
		$roleshortname = $strings->maintech->role_maintech_student;

		// Search for category name in cats array. When found, load the category id values.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->maintech_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for ASP-Maintech-stud user
    // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
    //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
	// Lenovo ********************************************************************************
	// } elseif (stripos($user_access_type, $strings->asp_maintech->access_asp_maintech_stud) !== false) {      // 11/25/19
    } elseif (strncasecmp($user_access_type, $strings->asp_maintech->access_asp_maintech_stud, strlen($user_access_type)) == 0) {   // 11/25/19

		$portfolio = $portfolios->PORTFOLIO_SERVICEPROVIDER;
		$roleshortname = $strings->asp_maintech->role_asp_maintech_student;

		// Search for category name in cats array. When found, load the category id values.
		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->maintech_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->asp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

	// Lenovo ********************************************************************************
	// Check for PremierSupport users
	// 05/16/18 - For testing, added PremierSupport-mgr1, PremierSupport-mgr2, and PremierSupport-mgr3 roles.
	// 11/07/18 - Added additional access type strings for all PremierSupport user types.
	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
	// Lenovo ********************************************************************************
	} else if ((preg_match($strings->premiersupport->access_premiersupport_pregmatch_stud, $user_access_type)) || (preg_match($strings->premiersupport->access_premiersupport_pregmatch_mgr, $user_access_type)) || (preg_match($strings->premiersupport->access_premiersupport_pregmatch_admin, $user_access_type)) || (preg_match($strings->premiersupport->access_premiersupport_pregmatch_geoadmin, $user_access_type)) || (preg_match($strings->premiersupport->access_premiersupport_pregmatch_siteadmin, $user_access_type))) {

		$portfolio = $portfolios->PORTFOLIO_PREMIERSUPPORT;

		if (preg_match($strings->premiersupport->access_premiersupport_pregmatch_admin, $user_access_type)) {
			$roleshortname = $strings->premiersupport->role_premiersupport_administrator;
		} else if (preg_match($strings->premiersupport->access_premiersupport_pregmatch_stud, $user_access_type)) {
				$roleshortname = $strings->premiersupport->role_premiersupport_student;
		} else if (preg_match($strings->premiersupport->access_premiersupport_pregmatch_mgr, $user_access_type)) {
				$roleshortname = $strings->premiersupport->role_premiersupport_manager;
		} else if (preg_match($strings->premiersupport->access_premiersupport_pregmatch_siteadmin, $user_access_type)) {
				$roleshortname = $strings->premiersupport->role_premiersupport_siteadministrator;
		} else if (preg_match($strings->premiersupport->access_premiersupport_pregmatch_geoadmin, $user_access_type)) {
				$roleshortname = $strings->premiersupport->role_premiersupport_geoadministrator;
		}

		// Search for category name in cats array. When found, load the category id values.
		if (has_capability($cats[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['capability'],
			$cats[array_search($top_level_categories->premiersupport_portfolio, array_column($cats, 'catname'))]['context'])) {
			// debug_logmessage("===Yes===", 'detailed');
		} else {
			// debug_logmessage("===No===", 'detailed');
		}

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->premiersupport_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->ibm_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->lenovo_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->maintech_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->asp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);
		// print_object("did I get here??");       // 11/20/19 - Lenovo debugging...
        // print_object($categoryids);
	// Lenovo ********************************************************************************
	// Check for ServiceDelivery users
	// 11/15/18 - Added additional access type strings for all ServiceDelivery user types.
	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // @02 - 05/13/20 - Added access to PremierSupport Portfolio (local_swtc_get_user_access) for SD users (so they
    //              can access PSC0003 and all courses that is in the PSC0003 curriculum).
	// Lenovo ********************************************************************************
	} else if ((preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_stud, $user_access_type)) || (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr, $user_access_type)) || (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin, $user_access_type)) || (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin, $user_access_type)) || (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin, $user_access_type))) {
		$portfolio = $portfolios->PORTFOLIO_SERVICEDELIVERY;

		if (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin, $user_access_type)) {
			$roleshortname = $strings->servicedelivery->role_servicedelivery_administrator;
		} else if (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_stud, $user_access_type)) {
				$roleshortname = $strings->servicedelivery->role_servicedelivery_student;
		} else if (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr, $user_access_type)) {
				$roleshortname = $strings->servicedelivery->role_servicedelivery_manager;
		} else if (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin, $user_access_type)) {
				$roleshortname = $strings->servicedelivery->role_servicedelivery_siteadministrator;
		} else if (preg_match($strings->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin, $user_access_type)) {
				$roleshortname = $strings->servicedelivery->role_servicedelivery_geoadministrator;
		}

		// Search for category name in cats array. When found, load the category id values.
		if (has_capability($cats[array_search($top_level_categories->servicedelivery_portfolio, array_column($cats, 'catname'))]['capability'],
			$cats[array_search($top_level_categories->servicedelivery_portfolio, array_column($cats, 'catname'))]['context'])) {
			// debug_logmessage("===Yes===", 'detailed');
		} else {
			// debug_logmessage("===No===", 'detailed');
		}

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->servicedelivery_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->ibm_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->lenovo_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->maintech_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->serviceprovider_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->asp_portfolio, $cats);

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);

        list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->premiersupport_portfolio, $cats);       // @02

	// Lenovo ********************************************************************************
	// Check for Self support user
	// Lenovo ********************************************************************************
	} elseif (stripos($user_access_type, $strings->selfsupport->access_selfsupport_stud) !== false) {

		$portfolio = $portfolios->PORTFOLIO_NONE;       // 05/01/18 - RF
		$roleshortname = $strings->selfsupport->role_selfsupport_student;

		list($categoryids[], $capabilities[]) = local_swtc_find_portfolio_name($top_level_categories->sitehelp_portfolio, $cats);
		// $categoryids[] = 'none';
		// $capabilities[] = 'none';

	// Lenovo ********************************************************************************
	// Check for Special access user
	// Lenovo ********************************************************************************
	// case $strings->specialaccess->access_special_user:
	// case $strings->specialaccess->access_specialaccess_stud:
	//
	//     $roleshortname = $strings->specialaccess->role_specialaccess_student;
	//
	// 	break;

	// Lenovo ********************************************************************************
	// Accesstype is not recognized
	// Lenovo ********************************************************************************
	} else {
		$portfolio = $portfolios->PORTFOLIO_NONE;
		$roleshortname = 'none';
		$categoryids[] = 'none';
		$capabilities[] = 'none';
	}

    // Lenovo ********************************************************************************
    // Loop through all the roles defined. When the shortname is found, load the role's id value.
    //
    // Sets variables:
	//			$swtc_user->roleid								The id of the role the user has.
    // Lenovo ********************************************************************************
	foreach ($roles as $role) {
		if ($role->shortname == $roleshortname) {
			$roleid = $role->id;
			break;
		}
	}

    // Lenovo ********************************************************************************
    // Finished. Set $temp_user to all the appropriate values so it can be returned.
    // Lenovo ********************************************************************************
    $temp_user->portfolio = $portfolio;
    $temp_user->roleshortname = $roleshortname;
    $temp_user->categoryids = $categoryids;
    $temp_user->capabilities = $capabilities;
    $temp_user->roleid = $roleid;

	if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving swtc_lib_locallib.php. ===3.local_swtc_get_user_access.exit.";
        $messages[] = "Lenovo ********************************************************************************.";
		$messages[] = "temp_user array follows: ";
        $messages[] = print_r($temp_user, true);
        $messages[] = "After printing temp_user";
        debug_logmessage($messages, 'detailed');
        unset($messages);
	}

    return array($cats, $temp_user);
}

/**
 * Look for the portfolio name in the $categoryids array. When found, save the values we want and return the newly created array.
 *
 * @param The portfolio name to look for and the list of all portfolios.
 *
 * @return $tmp   The catlist array used to set $SESSION->EBGLMS->USER.
 * @return string   The capability.
 *
 *
 * History:
 *
 *  11/08/18 - Original version.
 *
 */
function local_swtc_find_portfolio_name($portfolio_name, $cats) {
    $tmp = array();

    $cat = $cats[array_search($portfolio_name, array_column($cats, 'catname'))];

    $tmp['catid'] = $cat['catid'];
    $tmp['catname'] = $cat['catname'];
    $tmp['context'] = $cat['context'];
    $tmp['capability'] = $cat['capability'];

    return array($tmp, $cat['capability']);

}

/**
 * Look for the portfolio name in the $categoryids array. When found, only return the contextid (instanceid).
 *
 * @param The portfolio name to look for and the list of all portfolios.
 *
 * @return $int   The context of the portfolio name.
 *
 *
 * History:
 *
 *  11/28/18 - Original version.
 *
 */
function local_swtc_find_context_from_name($portfolio_name, $cats) {
    $instanceid = null;

    // print_object(format_backtrace(debug_backtrace(), false));
    // print_object($portfolio_name);
    // print_object($cats);
    $cat = $cats[array_search($portfolio_name, array_column($cats, 'catname'))];

    return $cat['context'];

}

/**
 * If PremierSupport or ServiceDelivery manager or administrator ventures outside their own portfolio, they are no longer
 *          considered a manager or administrator. Substitute either PremierSupport-student or ServiceDelivery-student as role.
 *
 * @param $cat		A catlist class variable.
 * @param $user		A user class variable.
 *
 * @return $temp_user	$user (passed in) with the rolename and roleid changed if required.
 *
 *
 * History:
 *
 *  11/15/18 - Original version.
 * 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function, if it is set, then
 *						change $user->roleshortname. If not changing role, remember to set it to whatever it was when this was called.
 * 01/10/19 - Changing all occurrences of "if (something)" to "if (!empty(something))"; for checking access to top level category, replaced
 *						checking of multiple access types with switch statement to multiple stripos checks; added return from function.
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
 *                      to search for access types.
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 */
function local_swtc_change_user_access($cat, &$user) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($user);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $user_access_type = $swtc_user->user_access_type;
    $top_level_categories = $SESSION->EBGLMS->STRINGS->top_level_categories;
    $roleshortname = null;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
    $access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
    $access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
    $access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

    $access_lenovo_sd_mgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;
    $access_lenovo_sd_admin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
    $access_lenovo_sd_geoadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin;
    $access_lenovo_sd_siteadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin;
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_change_user_access.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "swtc_user array follows :";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends.";
        // debug_logmessage(print_r($swtc, true), 'detailed');
        debug_logmessage($messages, 'both');
        unset($messages);
	}

	$topcat = $cat['catname'];
	// print_object("In local_swtc_change_user_access. catname to check is :$topcat.");

	// 01/10/19 - Just a test...
	// if (has_capability($cat['capability'], $cat['context'])) {
	// 	print_object("User has access to category $topcat");
	// } else {
	// 	print_object("User does NOT have access to category $topcat");
	// }

    // Lenovo ********************************************************************************
    // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
    // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
    // Lenovo ********************************************************************************
	// print_object($user->user_access_type);
	// if (stripos($user->user_access_type, $access_ps_mgr) !== false) {
	// 	print_object("$user->user_access_type, $access_ps_mgr, stripos was true");
	// } else {
	// 	print_object("$user->user_access_type, $access_ps_mgr, stripos was false");
	// }

	// Lenovo ********************************************************************************
	// PremierSupport access type.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
    //          to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/08/19 - Added PS/SD GEO site administrator user access types.
	// Lenovo ********************************************************************************
    //****************************************************************************************.
    // PremierSupport managers
    //****************************************************************************************.
	if (preg_match($access_ps_mgr, $user_access_type)) {
		// If the portfolio is PremierSupport, continue with the mgr access.
		if (stripos($cat['catname'], $top_level_categories->premiersupport_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_manager;
			// debug_logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
		} else {
			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
			// debug_logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
		}
    //****************************************************************************************.
    // PremierSupport administrators
    //****************************************************************************************.
	} else if (preg_match($access_ps_admin, $user_access_type)) {
		// If the portfolio is PremierSupport, continue with the admin access.
		if (stripos($cat['catname'], $top_level_categories->premiersupport_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_administrator;
			// debug_logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
		} else {
			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
			// debug_logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
        }
    //****************************************************************************************.
    // PremierSupport GEO administrators
    //****************************************************************************************.
    } else if (preg_match($access_ps_geoadmin, $user_access_type)) {
		// If the portfolio is PremierSupport, continue with the GEO admin access.
		if (stripos($cat['catname'], $top_level_categories->premiersupport_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_geoadministrator;
			// debug_logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
		} else {
			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
			// debug_logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
        }
    //****************************************************************************************.
    // PremierSupport site administrators
    //****************************************************************************************.
    } else if (preg_match($access_ps_siteadmin, $user_access_type)) {
		// If the portfolio is PremierSupport, continue with the site admin access.
		if (stripos($cat['catname'], $top_level_categories->premiersupport_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_siteadministrator;
			// debug_logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
		} else {
			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
			// debug_logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
        }
	// Lenovo ********************************************************************************
	// ServiceDelivery access type.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
    //          to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/08/19 - Added PS/SD GEO site administrator user access types.
	// Lenovo ********************************************************************************
    //****************************************************************************************.
    // ServiceDelivery managers
    //****************************************************************************************.
	} else if (preg_match($access_lenovo_sd_mgr, $user_access_type)) {
		// If the portfolio is ServiceDelivery, continue with the mgr access.
		// print_object("I found a servicedelivery-mgr.");
		if (stripos($cat['catname'], $top_level_categories->servicedelivery_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_manager;
			// debug_logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		} else {
			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
			// debug_logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		}
    //****************************************************************************************.
    // ServiceDelivery administrators
    //****************************************************************************************.
	} else if (preg_match($access_lenovo_sd_admin, $user_access_type)) {
		// If the portfolio is ServiceDelivery, continue with the admin access.
		if (stripos($cat['catname'], $top_level_categories->servicedelivery_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_administrator;
			// debug_logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		} else {
			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
			// debug_logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		}
    //****************************************************************************************.
    // ServiceDelivery GEO administrators
    //****************************************************************************************.
	} else if (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) {
		// If the portfolio is ServiceDelivery, continue with the admin access.
		if (stripos($cat['catname'], $top_level_categories->servicedelivery_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_geoadministrator;
			// debug_logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		} else {
			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
			// debug_logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		}
    //****************************************************************************************.
    // ServiceDelivery site administrators
    //****************************************************************************************.
	} else if (preg_match($access_lenovo_sd_siteadmin, $user_access_type)) {
		// If the portfolio is ServiceDelivery, continue with the admin access.
		if (stripos($cat['catname'], $top_level_categories->servicedelivery_portfolio) !== false) {
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_siteadministrator;
			// debug_logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		} else {
			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
			$roleshortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
			// debug_logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
		}
    }
    // Lenovo ********************************************************************************
    // Remember to set the roleid.
	// 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
	//						if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
	//						it was when this was called.
    // Lenovo ********************************************************************************
	if (!empty($roleshortname)) {
		$user->roleshortname = $roleshortname;
		$role = $DB->get_record('role', array('shortname' => $user->roleshortname), '*', MUST_EXIST);
		$user->roleid = $role->id;
	} else {

	}

	// return $tmp_user;
	return;
}

/**
 * Returns an array of all the configured user access types.
 *
 * Notes:
 *          In the mdl_user_info_field table, param1 contains all the possible user access types that are defined.
 *
 * @param N/A
 *
 * @return $array  All the configured user access types.
 *
 * History:
 *
 * 06/11/19 - Initial writing.
 *
 **/
function local_swtc_get_all_accesstypes() {
    global $CFG, $DB, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...
    $returntypes = array();

    // Variables end...
    // Lenovo ********************************************************************************

    // Get ALL the possible user access types.
    $types = $DB->get_record('user_info_field', array('shortname' => 'Accesstype'), 'id, param1');

    // Explode alltypes into an array using a delimiter of a new line character.
    $alltypes = explode("\n", $types->param1);

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/locallib.php ===local_swtc_get_all_accesstypes.exit===";
        $messages[] = "About to print alltypes.";
        $messages[] = print_r($alltypes, true);
        $messages[] = "Finished printing alltypes.";
        // print_object($alltypes);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

	return $alltypes;

}

/**
 * Returns all courses NOT in any Lenovo internal portfolios (for listing in related courses and suggested courses listboxes).
 *
 * @param N/A
 *
 * @return $array   The courses array.
 *
 * Version details
 *
 * History:
 *
 * 08/19/19 - Initial writing.
 *
 **/
function local_swtc_get_all_courses() {
    global $CFG, $DB, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...
    // SQL variables.
    $DCG_CUSTOMSQL_MAX_RECORDS = 40000;

    // Lenovo ********************************************************************************.
    // Only list courses NOT in top level categories 60 (Lenovo Internal) and 73 (resource).
    // Lenovo ********************************************************************************.
    $where = "WHERE ((cc.path NOT LIKE '/60/%') AND (cc.path NOT LIKE '%/60')) AND ((cc.path NOT LIKE '/73/%') AND (cc.path NOT LIKE '%/73'))";

    $sql = "SELECT c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category
                FROM {course} AS c
                LEFT OUTER JOIN {course_categories} AS cc ON (c.category = cc.id)
                $where
                ORDER BY c.shortname ASC";

    // Variables end...
    // Lenovo ********************************************************************************
    $records = $DB->get_recordset_sql($sql, null, 0, $DCG_CUSTOMSQL_MAX_RECORDS);

    // print_object("about to print records");
    // print_object($records);      // 08/19/19

    return $records;

}

/**
 * Capture (record) the click.
 *
 * @param $object  stdClass object in the following structure:
 *              action ("click")
 *              type ("related" or "suggested")
 *              parentcourseid  (only if "related")
 *              clickedcourseid
 *              clickeduserid
 *
 * @return N/A
 *
 * History:
 *
 * 08/01/19 - Initial writing.
 * 08/22/19 - Changed table names to "local_swtc_sc" and "local_swtc_rc".
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 12/12/19 - In local_swtc_capture_click, updated to add record in "local_swtc_rc" if the record doesn't exist (because we are
 *                      dynamically determining the related courses, this will be a common occurrence).
 * 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after inserting (needed
 *                      for log event functions); added several log event functions to write events and data to mdl_logstore_standard_log.
 *
 **/
function local_swtc_capture_click($data) {
    global $CFG, $DB, $SESSION, $USER;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $user_access_type = $swtc_user->user_access_type;
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_capture_click.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "swtc_user array follows :";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends.";
        // debug_logmessage(print_r($swtc, true), 'detailed');
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // Lenovo ********************************************************************************.
    // Switch on each type of "click".
    // Lenovo ********************************************************************************.
    switch ($data->type) {
        // Lenovo ********************************************************************************
        // User clicked on a "related" course.
        // Lenovo ********************************************************************************
        case 'related':
            // Lenovo ********************************************************************************.
            // See if the user has clicked on the related course, from the parent course, in the past.
            // $data->parentcourseid      Parent course id.
            // $data->clickedcourseid     The course id that was clicked on.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // Lenovo ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['relatedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_rc_details', $params)) {
                // User has clicked this course before.
                // 08/19/19 - TODO: This section; user clicked on related course, from the parent course, in the past.
                // 08/27/19 - Should I update "dateclicked" with the new date?
            } else {
                // User has NOT clicked this course before.
                // Save the click by this user.
                // Lenovo ********************************************************************************.
                // User has NOT clicked this course before; create the record.
                // Lenovo ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['accesstype'] = $user_access_type;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;
                $params['dateclicked'] = time();
                $params['dateenrolled'] = 0;
                // print_object("about to print params");
                // print_object($params);

                if ($DB->insert_record('local_swtc_rc_details', $params, false)) {
                    // The record was successfully created.
                    // Update the "clicks" counter in local_swtc_rc.
                    $params = array('active' => COURSE_ACTIVE, 'parentcourseid' => $data->parentcourseid, 'relatedcourseid' => $data->clickedcourseid);
                    if ($record = $DB->get_record('local_swtc_rc', $params)) {
                        // Increment "clicks".
                        $record->clicks ++;
                        // print_object("--here--1");
                        // Update just the "clicks" field.
                        $DB->set_field('local_swtc_rc', 'clicks', $record->clicks, array('active' => COURSE_ACTIVE, 'parentcourseid' => $data->parentcourseid, 'relatedcourseid' => $data->clickedcourseid));
                        // 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                        //          inserting (needed for log events function).
                        $recordid = $record->id;
                    } else {
                        // 12/12/19 - Updated to add record in "local_swtc_rc" if the record doesn't exist.
                        // print_object("--here--2");
                        // Lenovo ********************************************************************************.
                        // Create the record if it doesn't exist yet.
                        // Lenovo ********************************************************************************.
                        // Update the timecreated, get the USER->id of the user, and set "active" to active.
                        $params['active'] = COURSE_ACTIVE;
                        $params['usercreated'] = $USER->id;
                        $params['timecreated'] = time();
                        $params['usermodified'] = 0;
                        $params['timemodified'] = 0;
                        $params['parentcourseid'] = $data->parentcourseid;
                        $params['relatedcourseid'] = $data->clickedcourseid;
                        $params['clicks'] = 1;
                        $params['enrollments'] = 0;
                        // print_object("about to print params");
                        // print_object($params);

                        $DB->insert_record('local_swtc_rc', $params, true);

                        // 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                        //          inserting (needed for log events function).
                        $record = $DB->get_record('local_swtc_rc', $params);
                        $recordid = $record->id;
                    }
                } else {
                    // The record was NOT successfully created.
                    // Update the "clicks" counter in local_swtc_rc.
                    $params = array('active' => COURSE_ACTIVE, 'parentcourseid' => $data->parentcourseid, 'relatedcourseid' => $data->clickedcourseid);
                }

                // 01/31/20 - Added several log event functions to write events and data to mdl_logstore_standard_log.
                local_swtc_log_related_clicked($recordid, $data->clickedcourseid);
            }
            break;

        // Lenovo ********************************************************************************
        // User clicked on a "suggested" course.
        // Lenovo ********************************************************************************
        case 'suggested':
            // Lenovo ********************************************************************************.
            // See if the user has clicked on the suggested course, from the parent course, in the past.
            // $data->parentcourseid      Parent course id.
            // $data->clickedcourseid     The course id that was clicked on.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // Lenovo ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['suggestedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_sc_details', $params)) {
                // User has clicked this course before.
                // 08/19/19 - TODO: This section; user clicked on suggested course, from the parent course, in the past.
            } else {
                // User has NOT clicked this course before.
                // Save the click by this user.
                // Lenovo ********************************************************************************.
                // User has NOT clicked this course before; create the record.
                // Lenovo ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['suggestedcourseid'] = $data->clickedcourseid;
                $params['dateclicked'] = time();
                $params['dateenrolled'] = 0;
                // print_object("about to print params");
                // print_object($params);

                if ($DB->insert_record('local_swtc_sc_details', $params, false)) {
                    // The record was successfully created.
                    // Update the "clicks" counter in local_swtc_sc.
                    $params = array('active' => COURSE_ACTIVE, 'suggestedcourseid' => $data->clickedcourseid);
                    if ($record = $DB->get_record('local_swtc_sc', $params)) {
                        // Increment "clicks".
                        $record->clicks ++;
                        // print_object("--here--1");
                        // Update just the "clicks" field.
                        $DB->set_field('local_swtc_sc', 'clicks', $record->clicks, array('active' => COURSE_ACTIVE, 'suggestedcourseid' => $data->clickedcourseid));
                     } else {
                         // 08/19/19 - TODO: Not sure what to do here.
                         // print_object("--here--2");
                     }
                } else {
                    // The record was NOT successfully created.
                    // 08/19/19 - TODO: Not sure what to do here.
                }
            }
            break;

        // Lenovo ********************************************************************************
        // Event - all others
        // Lenovo ********************************************************************************
        default:
            break;
    }
}

/**
 * Capture (record) the enrollment.
 *
 * @param $object  stdClass object in the following structure:
 *              action ("enroll")
 *              type ("related" or "suggested")
 *              parentcourseid  (only if "related")
 *              clickedcourseid
 *              clickeduserid
 *
 * @return N/A
 *
 * History:
 *
 * 08/20/19 - Initial writing.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after inserting (needed
 *                      for log event functions); added several log event functions to write events and data to mdl_logstore_standard_log.
 *
 **/
function local_swtc_capture_enrollment($data) {
    global $CFG, $DB, $SESSION, $USER;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_capture_enrollment.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "swtc_user array follows :";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "swtc_user array ends.";
        // debug_logmessage(print_r($swtc, true), 'detailed');
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // 08/23/19 - For debugging.
    // print_object($data);
    // return;
    // Lenovo ********************************************************************************.
    // Switch on each type of "click".
    // Lenovo ********************************************************************************.
    switch ($data->type) {
        // Lenovo ********************************************************************************
        // User clicked on a "related" course.
        // Lenovo ********************************************************************************
        case 'related':
            // Lenovo ********************************************************************************.
            // See if the user has previously clicked on the course they just enrolled in.
            // $data->parentcourseid           Parent course id.
            // $data->clickedcourseid          The course they just enrolled in.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // Lenovo ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['relatedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_rc_details', $params)) {
                // 01/31/20 - In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                //          inserting (needed for log events function).
                $recordid = $record->id;

                // User has clicked this course before. So, capture enrollment.
                // 08/20/19 - TODO: This section.
                // Lenovo ********************************************************************************.
                // User has clicked this course before; update the record with the time they enrolled.
                // Lenovo ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;
                $dateenrolled = time();

                // Update just the "dateenrolled" field.
                $DB->set_field('local_swtc_rc_details', 'dateenrolled', $dateenrolled, $params);

                // Note: Have to remove "userid".
                $params = array();
                $params['active'] = COURSE_ACTIVE;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;

                // Update the "enrollments" counter in local_swtc_rc.
                if ($record = $DB->get_record('local_swtc_rc', $params)) {
                    // Increment "enrollments".
                    $record->enrollments ++;
                    // print_object("--here--1");
                    // Update just the "enrollments" field.
                    $DB->set_field('local_swtc_rc', 'enrollments', $record->enrollments, $params);
                 } else {
                     // 08/19/19 - TODO: Not sure what to do here.
                     // print_object("--here--2");
                 }

                 // 01/31/20 - Added several log event functions to write events and data to mdl_logstore_standard_log.
                local_swtc_log_related_enrolled($recordid, $data->clickedcourseid);
            } else {
                // User has NOT clicked this course before. So, nothing to do but return.
                // print_object("user has NOT previously clicked on this course. returning.");
            }
            break;

        // Lenovo ********************************************************************************
        // User clicked on a "suggested" course.
        // Lenovo ********************************************************************************
        case 'suggested':
            // Lenovo ********************************************************************************.
            // See if the user has previously clicked on the course they just enrolled in.
            //      $data['clickedcourseid']          The coure they just enrolled in.
            //      $data['userid']              The userid of the user.
            // Lenovo ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['suggestedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_sc_details', $params)) {
                // User has clicked this course before. So, capture enrollment.
                // 08/20/19 - TODO: This section.
                // Lenovo ********************************************************************************.
                // User has clicked this course before; update the record with the time they enrolled.
                // Lenovo ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['suggestedcourseid'] = $data->clickedcourseid;
                $dateenrolled = time();

                // Update just the "dateenrolled" field.
                $DB->set_field('local_swtc_sc_details', 'dateenrolled', $dateenrolled, $params);

                // Note: Have to remove "userid".
                $params = array();
                $params['active'] = COURSE_ACTIVE;
                $params['suggestedcourseid'] = $data->clickedcourseid;

                // Update the "enrollments" counter in local_swtc_rc.
                if ($record = $DB->get_record('local_swtc_sc', $params)) {
                    // Increment "enrollments".
                    $record->enrollments ++;
                    // print_object("--here--1");
                    // Update just the "enrollments" field.
                    $DB->set_field('local_swtc_sc', 'enrollments', $record->enrollments, $params);
                 } else {
                     // 08/19/19 - TODO: Not sure what to do here.
                     // print_object("--here--2");
                 }
            } else {
                // User has NOT clicked this course before. So, nothing to do but return.
                // print_object("user has NOT previously clicked on this course. returning.");
            }
            break;

            // Lenovo ********************************************************************************
            // Event - all others
            // Lenovo ********************************************************************************
            default:
                break;
    }

    return;

}

/**
 * Log event functions to write events and data to mdl_logstore_standard_log. The functions are:
 *      local_swtc_log_related_clicked
 *      local_swtc_log_related_enrolled
 *      local_swtc_log_suggested_clicked
 *      local_swtc_log_suggested_enrolled
 *
 * Modeled after /report/customsql:
 *
 *           function report_customsql_log_delete($id) {
 *              $event = \report_customsql\event\query_deleted::create(
 *                      array('objectid' => $id, 'context' => context_system::instance()));
 *              $event->trigger();
 *          }
 *
 * @param $id  integer The record id from the table being modified.
 *
 * @return N/A
 *
 * History:
 *
 * 01/31/20 - Initial writing.
 *
 **/
function local_swtc_log_related_clicked($id, $courseid) {
    // Lenovo ********************************************************************************.
    // The objectid (id) is the record id from the table being modified.
    // Lenovo ********************************************************************************.
    $event = \local_swtc\event\related_clicked::create(
                        array('objectid' => $id, 'context' => context_course::instance($courseid)));
    $event->trigger();
}

function local_swtc_log_related_enrolled($id, $courseid) {
    // Lenovo ********************************************************************************.
    // The objectid (id) is the record id from the table being modified.
    // Lenovo ********************************************************************************.
    $event = \local_swtc\event\related_enrolled::create(
                        array('objectid' => $id, 'context' => context_course::instance($courseid)));
    $event->trigger();
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

/**
 * Returns all roleids for all PremierSupport and ServiceDelivery roles.
 *
 * @param $option   Either 'premier' or 'service'
 *
 * @return $array   The courses array.
 *
 * Version details
 *
 * History:
 *
 * @01 - 03/12/20 - Initial writing; added local_swtc_get_all_pssd_roleids to /local/swtc/locallib.php for easier access checking;
 *                  added local_swtc_get_user_profile to /local/swtc/locallib.php to load the user profile of the user.
 *
 **/
function local_swtc_get_all_roleids($type) {
    global $CFG, $DB, $USER, $SESSION;

    $roleids = array();

    switch ($type) {
        case 'premier':
            // Remember - PremierSupport roles. First, student role.
            $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, manager role.
            $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_manager;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_administrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, site administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_siteadministrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, GEO administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_geoadministrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            break;

        case 'service':
            // ServiceDelivery roles. First, student role.
            $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, manager role.
            $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_manager;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_administrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, site administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_siteadministrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            // Next, GEO administrator role.
            $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_geoadministrator;
            $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
            $roleids[] = $role->id;

            break;
    }


    // return implode(', ', $roleids);
    return $roleids;

}

/**
 * Returns all the user profile information for the userid passed.
 *
 * @param $userid   The userid of the user to check.
 *
 * @return $array   Array of all the user profile information.
 *
 * Version details
 *
 * History:
 *
 * @01 - 03/16/20 - Initial writing.
 *
 **/
function local_swtc_get_user_profile($userid) {
    global $CFG, $DB, $USER, $SESSION;

    $temp = new stdClass();

    $temp->id = $userid;
    profile_load_data($temp);

    return $temp;

}

/**
 * Test whether a given cohortid+roleid has been defined
 *
 * @param integer $cohortid the id of a cohort
 * @param integer|null $roleid the id of a role, or null to just test cohort
 * @return boolean
 *
 * Version details
 *
 * History:
 *
 * @02 - 05/05/20 - Newer versions of the local_cohortrole plugin do not include the local_cohortrole_exists function;
 *                  added local_swtc_cohortrole_exists function (called from Moosh scripts).
 *
 */
function local_swtc_cohortrole_exists($cohortid, $roleid = null) {
    global $DB;

    $params = array('cohortid' => $cohortid);
    if ($roleid !== null) {
        $params['roleid'] = $roleid;
    }

    return $DB->record_exists('local_cohortrole', $params);
}
