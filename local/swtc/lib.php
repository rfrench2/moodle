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
 * @subpackage swtc
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 02/11/21 - Since we're using moodle/category:viewcourselist, testing of removing
 *		each navigation function.
 *
 **/

defined('MOODLE_INTERNAL') || die();

use local_swtc\swtc_user;
use local_swtc\swtc_debug;
// use \stdClass;

require_once($CFG->dirroot. '/config.php');
require_once($CFG->libdir. '/navigationlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->libdir . '/grouplib.php');

// SWTC ********************************************************************************
// Include SWTC LMS globals (sets $SWTC).
// SWTC ********************************************************************************
// 10/16/20 - SWTC
// require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

/* Navigation is available through the page object $PAGE, against which you set the heading for the page,
 *	the title, any JavaScript requirements, etc. The navigation structure uses the information $PAGE contains
 *	to generate a navigation structure for the site. The navigation or settings blocks are interpretations
 *	of the navigation structure Moodle creates.
 *
 * This navigation structure is available through three variables:
 * 		$PAGE->navigation: This is the main navigation structure, it will contain items that will allow the user
 * 			to browse to the other available pages. See local_swtc_extend_navigation.
 * 		$PAGE->settingsnav: This is the settings navigation structure contains items that will allow the user to
 * 			edit settings. See local_swtc_extend_settings_navigation.
 * 		$PAGE->navbar: The navbar is a special structure for page breadcrumbs. Not implemented in this file.
 *
 * The navigation is NOT the navigation block or the settings block! These two blocks were created to display the
 * 	navigation structure. The navigation block looks at $PAGE->navigation, and the settings block looks at
 * 	$PAGE->settingsnav. Both blocks interpret their data into an HTML structure and render it.
 *
 * Any plugin implementing the following callback in lib.php can extend the course settings navigation. Prior to
 * 	3.0 only reports and admin tools could extend the course settings navigation. See
 * 	local_swtc_extend_navigation_course.
 *
 * Any plugin implementing the following callback in lib.php can extend the user settings navigation. Prior to
 * 	3.0 only admin tools could extend the user settings navigation. See local_swtc_extend_navigation_user_settings.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
function local_swtc_extend_navigation(global_navigation $nav) {
	global $USER;

	// SWTC - Debug 10/30/20
	return;

	// SWTC ******************************************************************************
	// 10/14/20 - If we're not logged in, return.
	// SWTC ******************************************************************************
	if (!isloggedin()) {
		return;
	}

    //****************************************************************************************
	// SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user([
		'userid' => $USER->id,
		'username' => $USER->username]);
	$debug = swtc_set_debug();

    // Other SWTC variables.
    $user_access_type = $swtc_user->get_user_access_type();
    $mycourses = 'mycourses';		// The key for 'My courses'.
	$mycurriculums = get_string('mycurriculums', 'local_swtc');       // The title for 'My Curriculums'.
    $site = 'site';		// The key for 'Site' (i.e. Navigation > Home > swtc).
    $participants = 'participants';		// The key for 'Participants' (i.e. Navigation > Home > swtc > Participants).

    $capability = $swtc_user->get_capabilities()[0];
	$access_selfsupport_stud = get_string('access_selfsupport_stud', 'local_swtc');
	//****************************************************************************************

    if (isset($debug)) {
        $messages[] = "SWTC ********************************************************************************";
        $messages[] = "Entering /local/swtc/lib.php.===local_swtc_extend_navigation.enter.";
        $messages[] = "About to print swtc_user.";
        $messages[] = print_r($swtc_user, true);
		// print_object("Entering /local/swtc/lib.php.===local_swtc_extend_navigation.enter; about to print swtc_user");
		// print_object($swtc_user);
        $messages[] = "Finished printing swtc_user.";
        // $messages[] = "About to print courses.";
        // $tmp = $nav->find('courses', null);
        // $messages[] = print_r($tmp, true);
        // $messages[] = "Finished printing courses.";
		// $messages[] = "About to print nav.";
        // $messages[] = print_r($nav, true);
        // $messages[] = "Finished printing nav.";
        $messages[] = "SWTC ********************************************************************************";
        $debug->logmessage($messages, 'both');
        unset($messages);
	}

	// SWTC ********************************************************************************
	// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
	// SWTC ********************************************************************************
	if ( empty($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("User has not logged on yet; leaving local_swtc_extend_navigation ===1.exit===.", 'both');
		}
		return;
	}

    // SWTC ********************************************************************************
	// Quick check...if user is a siteadmin, skip all this and return...
	// SWTC ********************************************************************************
	if (is_siteadmin($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("Leaving local_swtc_extend_navigation ===1.exit===.", 'both');
		}
		return;
	}

    // SWTC ********************************************************************************
    // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
    //                  of access (before moving to core_course_category::can_view_category).
    // @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/swtc_course_renderer.php
    //                      (changed core_course_category to \core_course_category).
    // SWTC ********************************************************************************
    $categories = \core_course_category::make_categories_list($capability);
    // print_object($categories);     // 03/02/20 - SWTC debugging...

    // SWTC ********************************************************************************
    // Determine if user should have access to category. If not, remove it.
    // SWTC ********************************************************************************
    $courses = $nav->find('courses', null);             // Find the courses node.
    // print_object($courses);     // 11/20/19 - SWTC debugging...
    $children = $courses->children->get_key_list();               // Get a list of all children of the courses node.

    if (isset($debug)) {
		if (!empty($children)) {
			$messages[] = "About to print children. ===1.1===.";
			$messages[] = print_r($children, true);
			$messages[] = "Finished printing children. ===1.1===.";
		} else {
			$messages[] = "I did NOT find any children. ===1.1===.";
		}
        $debug->logmessage($messages, 'detailed');
        unset($messages);
    }

    // SWTC ********************************************************************************
    // Main loop. See if children is in $SESSION->SWTC->USER->categoryids. If it is found, the user has access, so
    //                  leave the course in the list. If the user doesn't, remove it from the list.
    // SWTC ********************************************************************************
    foreach ($children as $key => $catid) {
        if ((in_array($catid, array_keys($categories))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
            if (isset($debug)) {
                $messages[] = "Child category $catid found in SESSION->SWTC->USER->categoryids. Keeping category in list. ===1.1===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
        } else {
            if (isset($debug)) {
                $messages[] = "Child category catid NOT found in SESSION->SWTC->USER->categoryids. Removing category from list. ===1.1===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            $courses->children->remove($children[$key]);
        }
    }

    // SWTC ********************************************************************************
    // 11/14/19 - In local_swtc_extend_navigation, added fix to remove "swtc > Participants" navigation node if not a SWTC-admin
    //                      or SWTC-siteadmin.
    // SWTC ********************************************************************************
    // SWTC ********************************************************************************
    if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $user_access_type)) && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
        $node = $nav->find($site, null);
        // $allnodes = $node->children->get_key_list();
        // print_object($allnodes);
        $participantnode = $node->children->find($participants, null);
        // print_object($participantnode);

        if ( !empty($participantnode)) {
            if (isset($debug)) {
                $messages[] = "I found the Site > Participants node ===1.1.5===.";
                $debug->logmessage($messages, 'logfile');
                unset($messages);
            }
            if ( !$participantnode->remove()) {
                if (isset($debug)) {
                    $debug->logmessage("Error removing Site > Participants node ===1.1.5===.", 'logfile');
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Successfully removed Site > Participants node ===1.1.5===.", 'logfile');
                }
            }
        }else {
            if (isset($debug)) {
                $debug->logmessage("I DIDN'T find Site > Participants node ===1.1.5===.", 'logfile');
            }
        }
    }

    if (isset($debug)) {
		if (!empty($node)) {
			$messages[] = "Found $mycourses ===1.1.5===.";
			$messages[] = "About to print mycourses node ==1.1.5===.";
			$messages[] = print_r($mycourses, true);
			$messages[] = "Finished printing mycourses node ==1.1.5===.";
		} else {
			$messages[] = "I did NOT find $mycourses ===1.1.5===.";
		}
        $debug->logmessage($messages, 'logfile');
        unset($messages);
        // print_object($node);
    }

    // SWTC ********************************************************************************
    // 05/16/18 - Since the "My overview" (course overview) plugin has been sunset and removed from all our sites, this code has
    //      been removed. This header remains as a reminder.
    //
	// Attempting to find the "My overview" block. It is located on the user's "My Courses" page. Addressability is via $PAGE->blocks;
    //          the class returned is an object of class "block_manager" (/lib/blocklib.php).
    //
    // SWTC ********************************************************************************
    // Removed a section of code, comments, or both. See archived versions of module for information.
    // SWTC ********************************************************************************

	if (isset($debug)) {
		$debug->logmessage("Leaving local_swtc_extend_navigation ===1.exit===.", 'both');
	}
}

/* Navigation is available through the page object $PAGE, against which you set the heading for the page, the title, any JavaScript
 * requirements, etc. The navigation structure uses the information $PAGE contains to generate a navigation structure for the site. The
 * navigation or settings blocks are interpretations of the navigation structure Moodle creates.
 *
 * This navigation structure is available through three variables:
 * $PAGE->navigation: This is the main navigation structure, it will contain items that will allow the user to browse to the other
 *          available pages. See local_swtc_extend_navigation.
 * $PAGE->settingsnav: This is the settings navigation structure contains items that will allow the user to edit settings.
 * 		See local_swtc_extend_settings_navigation.
 * $PAGE->navbar: The navbar is a special structure for page breadcrumbs.
 *			Not implemented in this file.
 *
 * The navigation is NOT the navigation block or the settings block! These two blocks were created to display the navigation structure.
 * The navigation block looks at $PAGE->navigation, and the settings block looks at $PAGE->settingsnav. Both blocks interpret their
 * data into an HTML structure and render it.
 *
 * Any plugin implementing the following callback in lib.php can extend the course settings navigation. Prior to 3.0 only reports and
 * admin tools could extend the course settings navigation. See local_swtc_extend_navigation_course.
 *
 * Any plugin implementing the following callback in lib.php can extend the user settings navigation. Prior to 3.0 only admin tools
 * could extend the user settings navigation.
 * 		See local_swtc_extend_navigation_user_settings.
*/
/**
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
function local_swtc_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
	global $USER;

	// SWTC - Debug 10/30/20
	return;

	// SWTC ******************************************************************************
	// 10/14/20 - If we're not logged in, return.
	// SWTC ******************************************************************************
	if (!isloggedin()) {
		return;
	}
	return;		// 10/17/20 - SWTC
    //****************************************************************************************
	// SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user([
		'userid' => $USER->id,
		'username' => $USER->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $user_access_type = $swtc_user->get_user_access_type();

    //****************************************************************************************
    // Array of top-level settingsnav menu 'Front page settings' (frontpage) nodes (keys) to remove. Main front page
    //      settings key is :frontpage.
    //
    //      Notes:
    //          $settingsnav->children->get_key_list() should be the following:
    //          Array ( [0] => frontpage [1] => dashboard [2] => siteadministration )
    //
    //          Front page (frontpage)
    $frontpage = 'frontpage';

    //****************************************************************************************
    // Array of top-level menu 'Site administration nodes (keys) to remove. Main site administration key is :siteadministration.
    //      Most of these will be removed by the definition of the role.
    //
    //          Typically, the Site administration node consists of (keys in parenthesis):
    //          Site administration (siteadministration)
    // $siteadministration = 'siteadministration';
    //          Notifications (adminnotifications)
    $adminnotifications = 'adminnotifications';
    //          Registration (registrationmoodleorg)
    $regmoodleorg = 'registrationmoodleorg';
    //          Advanced features (optionalsubsystems)
    $advancedfeatures = 'optionalsubsystems';
    //          Users (users)                               Note: Remember SWTC administrators still need access to this.
	// $users = 'users';																// The 'Course administration > Users' node (key).
    //          Courses (courses)
    // $courses = 'courses';
    //          Grades (grades)
	$grades = 'grades';
	//				Analytics (analytics)
    $analytics = 'analytics';
    //          Competencies (competencies)
    $competencies = 'competencies';
    //          Badge settings (badges)
    //              Note: Badges are not enabled.
    //          Location (location)
    $location = 'location';
    //          Language (language)
    // $language = 'language';
    //          Plugins (modules)
    $modules = 'modules';
    //          Security (security)
    $security = 'security';
    //          Appearance (appearance)
    $appearance = 'appearance';
    //          Front page (frontpage)
    // $frontpage = 'frontpage';
    //          Server (server)
    $server = 'server';
    //          Mnet (mnet)
    //          Reports (reports)
    // $reports = 'reports';
    //          Mobile app (mobileapp)
    $mobileapp = 'mobileapp';
    //          Development (development)
    $development = 'development';
    //          Search (search)
    // $search = 'search';
    //          Assignment upgrade helper (assignmentupgrade )
    $assignmentupgrade = 'assignmentupgrade';
    //          Multilang upgrade (toolmultilangupgrade)
    $toolmultilangupgrade = 'toolmultilangupgrade';
    //          Lenovo Services Education (ebgadmin)
    // $swtcadmin = 'swtcadmin';
    // Moodle services (Moodle 3.6.3)
    $moodleservices = 'moodleservices';
    // $admin_remove = array('editsettings', 'turneditingonoff', 'coursereports', 'gradebooksetup', 'backup', 'restore', 'import', 'reset', 'questionbank');
	// $admin_remove = array($users, $reports);
	// $admin_top_remove = array($adminnotifications, $regmoodleorg, $advancedfeatures, $grades, $analytics, $competencies, $location, $language, $modules, $security, $appearance, $frontpage, $server, $reports, $mobileapp, $development, $assignmentupgrade, $toolmultilangupgrade, $moodleservices);
    $admin_top_remove = array($adminnotifications, $regmoodleorg, $advancedfeatures, $grades, $analytics, $competencies, $location, $modules, $security, $appearance, $frontpage, $server, $mobileapp, $development, $assignmentupgrade, $toolmultilangupgrade, $moodleservices);

    //****************************************************************************************
    // Array of lower-level menu 'Site administration nodes (keys) to remove. Main site administration key is :siteadministration.
    //      Most of these will be removed by the definition of the role.
    //
    //          Typically, the Site administration node consists of (keys in parenthesis):
    //          Users (users)                               Note: Remember SWTC administrators still need access to this.
	// $users = 'users';									// The 'Course administration > Users' node (key).
    // $accounts = 'accounts';
    $roles = 'roles';
    $privacy = 'privacy';
    $userdefpreferences = 'userdefaultpreferences';
    $profilefields = 'profilefields';
    $tooluploaduserpics = 'tooluploaduserpictures';
    //          Courses (courses)
    // $courses = 'courses';
    $addcategory = 'addcategory';
    $restorecourse = 'restorecourse';
    $coursesettings = 'coursesettings';
    $courserequest = 'courserequest';
    $backups = 'backups';
    $tooluploadcourse = 'tooluploadcourse';
    $addnewcourse = 'addnewcourse';
    //          Grades (grades)
	// $grades = 'grades';
	//				Analytics (analytics)
    // $analytics = 'analytics';
    //          Competencies (competencies)
    // $competencies = 'competencies';
    //          Badge settings (badges)
    //              Note: Badges are not enabled.
    //          Location (location)
    // $location = 'location';
    //          Language (language)
    // $language = 'language';
    //          Plugins (modules)
    // $modules = 'modules';
    //          Security (security)
    // $security = 'security';
    //          Appearance (appearance)
    // $appearance = 'appearance';
    //          Front page (frontpage)
    //              Note: Removed for everyone separately below.
    // $frontpage = 'frontpage';
    //          Server (server)
    // $server = 'server';
    //          Mnet (mnet)
    //          Reports (reports)
    // $reports = 'reports';
    $comments = 'comments';
    $reportbackups = 'reportbackups';
    $reportconfiglog = 'reportconfiglog';
    // $reportcourseoverview = 'reportcourseoverview';
    // $report_customsql = 'report_customsql';
    $reporteventlists = 'reporteventlists';
    // $reportlog = 'reportlog';
    // $reportloglive = 'reportloglive';
    $reportperformance = 'reportperformance';
    $rptquestioninstances = 'reportquestioninstances';
    $reportsecurity = 'reportsecurity';
    $reportstats = 'reportstats';
    $toolmonitorrules = 'toolmonitorrules';
    $toolspamcleaner = 'toolspamcleaner';
    //          Mobile app (mobileapp)
    // $mobileapp = 'mobileapp';
    //          Development (development)
    // $development = 'development';
    //          Unsupported
    //          Search (search)
    // $search = 'search';
    //          Assignment upgrade helper (assignmentupgrade )
    // $assignmentupgrade = 'assignmentupgrade';
    //          Multilang upgrade (toolmultilangupgrade)
    //          Lenovo Services Education (ebgadmin)
    // $swtcadmin = 'swtcadmin';
    $local_swtc_settings = 'local_swtc_settings';
    $invitehistory = 'invitehistory';
    // $dashboards = 'dashboards';
    $servicebench = 'servicebench';
    // $admin_remove = array('editsettings', 'turneditingonoff', 'coursereports', 'gradebooksetup', 'backup', 'restore', 'import', 'reset', 'questionbank');
	// $admin_remove = array($users, $reports);
	$admin_second_remove = array($roles, $privacy, $userdefpreferences, $profilefields, $tooluploaduserpics, $addcategory, $restorecourse, $coursesettings, $courserequest, $backups, $tooluploadcourse, $comments, $reportbackups, $reportconfiglog, $reporteventlists, $reportperformance, $rptquestioninstances, $reportsecurity, $reportstats, $toolmonitorrules, $toolspamcleaner, $local_swtc_settings, $invitehistory, $servicebench, $addnewcourse);
	//****************************************************************************************

    if (isset($debug)) {
        // SWTC ********************************************************************************
        // 04/23/18 - Testing of logging to display.
        // SWTC ********************************************************************************
        // $messages[] = "SWTC ********************************************************************************";
        // $messages[] = "Entering local_swtc_extend_settings_navigation ===2.enter.";
        // $messages[] = "About to print SESSION->SWTC->USER.";
        // $messages[] = print_r($SESSION->SWTC->USER, true);
        // $messages[] = "Finished printing SESSION->SWTC->USER.";
        // $messages[] = "SWTC ********************************************************************************";
        //
        // $debug->logmessage($messages, 'display');
        // unset($messages);

        // SWTC ********************************************************************************
        // Always output standard header information.
        // SWTC ********************************************************************************
        $messages[] = "SWTC ********************************************************************************";
        $messages[] = "Entering /local/swtc/lib.php===local_swtc_extend_settings_navigation.enter===.";
        $messages[] = "SWTC ********************************************************************************";
        $debug->logmessage($messages, 'both');
        unset($messages);

        // SWTC ********************************************************************************
        // Detailed debugging information.
        //
        //      Notes:
        //          $settingsnav->children->get_key_list() should be the following:
        //          Array ( [0] => frontpage [1] => dashboard [2] => siteadministration )
        // SWTC ********************************************************************************
        $messages[] = "SWTC ********************************************************************************";
        $messages[] = "About to print settings_navigation (settingsnav) node.";
        // print_object($settingsnav);
        $messages[] = print_r(array_keys((array)$settingsnav), true);
        // $messages[] = print_r($settingsnav, true);
        $messages[] = "Finished printing settings_navigation (settingsnav) node.";
        // $messages[] = "About to print context :";
        // $messages[] = print_r($context, true);
        // print_object($context);
        // $messages[] = "Finished printing context. As a reminder, context levels follow :";
        // $messages[] = "CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
        // $messages[] = "As an additional reminder, navigation_node namedtypes are as follows :";
        // $messages[] =  "[0] => system [10] => category [20] => course [30] => structure [40] => activity [50] => resource [60] => custom [70] => setting [71] => siteadmin [80] => user [90] => container";
        $messages[] = "About to print children get_key_list :";
        $allnodes = $settingsnav->children->get_key_list();
        $messages[] = print_r($allnodes, true);
        $messages[] = "Finished printing children get_key_list.";
        $messages[] = "SWTC ********************************************************************************";
        $debug->logmessage($messages, 'detailed');
        unset($messages);
	}

	//
	// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
	//
	if ( empty($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("User has not logged on yet; local_swtc_extend_settings_navigation ===2.exit===.", 'both');
		}
		return;
	}

	//
	// Quick check...if user is a siteadmin, skip all this and return...
	//
	if ( is_siteadmin($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("Leaving local_swtc_extend_settings_navigation ===2.exit===.", 'both');
		}
		return;
	}

    //
    // SWTC ********************************************************************************
    //		Remove 'Front page' for all users.
    //
    //      Attempt to find the 'frontpage' node in the Administration (settings) node...and remove it. The 'frontpage' node would only be
    //              available IF the user is viewing the frontpage of the site (which is course id 1) AND the user has the appropriate capabilities.
    //		        However, this should be done for user's that are not students (students do not have access to edit the frontpage).
    //		        Editing the frontpage should only be available for SWTC site administrators.
    //
    //      Notes:
    //          When using "->find" in the navigation_node object, you're searching for the key value:
    //                  [id] = frontpagesettings ("Front page settings")
    //                  [key] = frontpage ("Front page")
    //                  [text] = Front page settings
    //          context will be 50 (CONTEXT_COURSE) AND the course id (context->instanceid) will be 1.
    // SWTC ********************************************************************************
    if ($context->contextlevel == CONTEXT_COURSE && $context->instanceid == SITEID) {
        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Detailed debugging information.
            // SWTC ********************************************************************************
            $messages[] = "SWTC ********************************************************************************";
            $messages[] = "Entering local_swtc_extend_settings_navigation.removing_frontpage ===2.0.5.enter.";
            $messages[] = "About to print context :";
            $messages[] = print_r($context, true);
            // print_object($context);
            $messages[] = "Finished printing context. As a reminder, context levels follow :";
            $messages[] = "CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
            $messages[] = "As an additional reminder, navigation_node namedtypes are as follows :";
            $messages[] =  "[0] => system [10] => category [20] => course [30] => structure [40] => activity [50] => resource [60] => custom [70] => setting [71] => siteadmin [80] => user [90] => container";
            $messages[] = "SWTC ********************************************************************************";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // See if the user is viewing the frontpage of the site.
        $frontpageroot = $settingsnav->find($frontpage, navigation_node::TYPE_SETTING);

        // SWTC ********************************************************************************
        // 02/13/19 - Fixed access for Lenovo-admin and Lenovo-siteadmin user types.
        // SWTC ********************************************************************************
        if (!empty($frontpageroot)) {
            if (isset($debug)) {
                $debug->logmessage("I found the frontpage node ===2.0.5===.", 'both');
            //	print_r($frontpageroot);
            }
            if ( !$frontpageroot->remove()) {
                if (isset($debug)) {
                    $debug->logmessage("Error removing the frontpage node ===2.0.5===.", 'both');
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Successfully removed the frontpage node ===2.0.5===.", 'both');
                }
            }
        }else {
            if (isset($debug)) {
                $debug->logmessage("I DIDN'T find the frontpage node. Continuing ===2.0.5===...", 'both');
            }
        }
    } else if ($context->contextlevel == CONTEXT_SYSTEM) {
        // SWTC ********************************************************************************
        //
        //
        //      Notes:
        //          CONTEXT_SYSTEM is associated with any interaction with the "Site administration" menu. It's key is "modulesettings".
        //                  Some examples are:
        //                      When on the site frontpage (with the "Site administration" menu collapsed), the user clicks the tie to expand the menu.
        //
        //
        // SWTC ********************************************************************************
        // Do something interesting.
    } else if ($context->contextlevel == CONTEXT_COURSE) {
        // 02/25/19 - Need to remove the Filters, Backup, Restore, Import, Question bank, and Repositories nodes; done in
        //      local_swtc_extend_navigation_course.
    } else if ($context->contextlevel == CONTEXT_MODULE) {
        // SWTC ********************************************************************************
        //      Remove 'Forum administration' for all users. Main "Forum administration" key is :modulesettings.
        //          Most of these will be removed by the definition of the role.
        //
        // Typically, the Forum administration node consists of (keys in parenthesis):
        //      Forum administration (modulesettings)
        $modulesettings = 'modulesettings';
        //      Edit settings (modedit)
        // $modedit = 'modedit';
        //      Locally assigned roles (roleassign)
        // $roleassign = 'roleassign';
        //      Permissions (roleoverride)
        // $roleoverride = 'roleoverride';
        //      Check permissions (rolecheck)
        // $rolecheck = 'rolecheck';
        //      Filters (filtermanage)
        // $filtermanage = 'filtermanage';
        //      Logs (logreport)
        // $logreport = 'logreport';
        //      Backup (backup)
        // $backup = 'backup';
        //      Restore (restore)
        // $restore = 'restore';
        //      Optional subscription (8)
        //      Subscribe to this forum (9)
        //      Show/edit current subscribers (10)
        //
        //      Notes:
        //          CONTEXT_MODULE is associated with any interaction with the "Forum administration" menu. It's key is "modulesettings".
        //                  Some examples are:
        //                      When on the site frontpage, clicking on the "Older topics..." hyperlink just below the Site announcements forum.
        //
        // 03/05/19 - In local_swtc_extend_settings_navigation, if in CONTEXT_MODULE, if it is a Quiz, keep the menu.
        // SWTC ********************************************************************************
        // $module_remove = array();

        // Attempt to find the 'modulesettings' node in the Administration (settings) node...and remove it.
        $modulesettings = $settingsnav->find($modulesettings, null);

        if (!empty($modulesettings)) {
            if (isset($debug)) {
                $debug->logmessage("I found a Module settings node. ===2.6===.", 'both');
            }
            if (stripos($modulesettings->text, 'Quiz') === false) {
                if ( !$modulesettings->remove()) {
                    if (isset($debug)) {
                        $debug->logmessage("Error removing the Module settings node. ===2.6===.", 'both');
                    }
                } else {
                    if (isset($debug)) {
                        $debug->logmessage("Successfully removed the Module settings node. ===2.6===.", 'both');
                    }
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Found a Quiz module. Keeping. Continuing. ===2.6===.", 'both');
                }
            }
        } else {
            if (isset($debug)) {
                $debug->logmessage("I DIDN'T find a Module settings node. Continuing ===2.6===.", 'both');
            }
        }
    } else if ($context->contextlevel == CONTEXT_COURSECAT) {
        // SWTC ********************************************************************************
        //		Remove 'Category settings' for all users.
        //
        //Attempt to find the 'categorysettings' node in the Administration (settings) node...and remove it.
        // SWTC ********************************************************************************
        $categorysettings = $settingsnav->find('categorysettings', null);

        if (!empty($categorysettings)) {
            if (isset($debug)) {
                $debug->logmessage("I found a Category settings node. ===2.6===.", 'both');
            //	print_r($permissionsnode);
            }
            if ( !$categorysettings->remove()) {
                if (isset($debug)) {
                    $debug->logmessage("Error removing the Category settings node. ===2.6===.", 'both');
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Successfully removed the Category settings node. ===2.6===.", 'both');
                }
            }
        } else {
            if (isset($debug)) {
                $debug->logmessage("I DIDN'T find a Category settings node. Continuing ===2.6===.", 'both');
            }
        }
    }

    // SWTC ********************************************************************************
    // 02/13/19 - Fixed access for Lenovo-admin and Lenovo-siteadmin user types.
    // SWTC ********************************************************************************
    // if ( !empty($adminroot)) {
    if ($adminroot = $settingsnav->find('siteadministration', \navigation_node::TYPE_SITE_ADMIN)) {
        // print_object($adminroot);
        // 02/24/19 - Attempt to force open.
        // $adminroot->force_open();
        // $adminroot->make_active();

        // TODO - 02/20/19 - Look for a better way to do this...
        // $children = $adminroot->children->get_key_list();

        // if ( !empty($children)) {

            if (isset($debug)) {
                // SWTC ********************************************************************************
                // Detailed debugging information.
                // SWTC ********************************************************************************
                $messages[] = "SWTC ********************************************************************************";
                $messages[] = "I found the <strong>Site administration</strong> menu.";
                // $messages[] = "About to print adminroot :";
                // $messages[] = print_r($adminroot, true);
                // $messages[] = "Finished printing the adminroot.";
                // $messages[] = "About to print all the adminroot children keys :";
                // $messages[] = print_r(array_keys((array)$adminroot), true);
                // $children = $adminroot->children->get_key_list();
                // $messages[] = print_r($children, true);
                // $messages[] = "Finished printing all the adminroot children keys.";
                $messages[] = "SWTC ********************************************************************************";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            // SWTC ********************************************************************************
            if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $user_access_type)) && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
                if (isset($debug)) {
                    $debug->logmessage("Attempting to remove the Site administration menu ===2.1===.", 'both');
                }

                if (!$adminroot->remove()) {     // 02/20/19 - TODO - won't work...
                    if (isset($debug)) {
                        $debug->logmessage("Error removing the Site administration menu ===2.1===.", 'both');
                    }
                } else {
                    if (isset($debug)) {
                        $debug->logmessage("Successfully removed the Site administration menu ===2.1===.", 'both');
                    }
                    // 06/02/18 - RF - It worked! With role assigned to System. Will attempt to add with role NOT assigned to System.
                    // 02/15/19 - Does NOT work with role NOT assigned to System.
                    // $adminsettings = $settingsnav->load_administration_settings();
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("User type is either PremierSupport-admin, PremierSupport-mgr, ServiceDelivery-admin, ServiceDelivery-mgr,  Lenovo-admin, or Lenovo-siteadmin - keeping the Site administration menu ===2.2===.", 'both');
                    $debug->logmessage("Most sub-menu items should be handled using role definitions. The rest will be handled here.===2.2===.", 'both');
                }

                // SWTC ********************************************************************************
                // Next, remove all the nodes in the $admin_top_remove array (see above).
                // SWTC ********************************************************************************
                foreach($admin_top_remove as $node) {
                    if (isset($debug)) {
                        $debug->logmessage("Searching for the <strong>Site administration >$node</strong> node. ===2.2.5===.", 'both');
                    }

                    // Can we find it?
                    $found = $adminroot->children->find($node);

                    if ( !empty($found)) {
                        if (isset($debug)) {
                            $debug->logmessage("I found the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                        }

                        // Remove it.
                        $adminroot->children->remove($node, $found->type);

                        if (isset($debug)) {
                            $debug->logmessage("Removed the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                        }

                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("I DIDN'T find the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                        }
                    }
                }

                // SWTC ********************************************************************************
                // Next, remove all the nodes in the $admin_second_remove array (see above).
                // SWTC ********************************************************************************
                foreach($admin_second_remove as $node) {
                    if (isset($debug)) {
                        $debug->logmessage("Searching for the <strong>Site administration >$node</strong> node. ===2.2.5===.", 'both');
                    }

                    // Can we find it?
                    // $found = $adminroot->children->find($node);
                    $found = $settingsnav->find($node, null);
                    // print_r($found, true);

                    if ( !empty($found)) {
                        if (isset($debug)) {
                            $debug->logmessage("I found the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                        }

                        // Remove it.
                        // $adminroot->children->remove($node, $found->type);
                        if ( !$found->remove()) {
                            if (isset($debug)) {
                                $debug->logmessage("Error removing the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                            }
                        } else {
                            if (isset($debug)) {
                                $debug->logmessage("Successfully removed the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                            }
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("I DIDN'T find the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                        }
                    }
                }
            }
        // }
    } else {
        if (isset($debug)) {
            $debug->logmessage("Did NOT find Site administration menu. Continuing ===2.1===.", 'both');
        }
    }

	if (isset($debug)) {
		$debug->logmessage("Leaving local_swtc_extend_settings_navigation ===2.exit===.", 'both');
	}
}

/* Navigation is available through the page object $PAGE, against which you set the heading for the page, the title, any JavaScript
 * requirements, etc. The navigation structure uses the information $PAGE contains to generate a navigation structure for the site. The
 * navigation or settings blocks are interpretations of the navigation structure Moodle creates.
 *
 * This navigation structure is available through three variables:
 * $PAGE->navigation: This is the main navigation structure, it will contain items that will allow the user to browse to the other
 *          available pages. See local_swtc_extend_navigation.
 * $PAGE->settingsnav: This is the settings navigation structure contains items that will allow the user to edit settings.
 * 		See local_swtc_extend_settings_navigation.
 * $PAGE->navbar: The navbar is a special structure for page breadcrumbs.
 *			Not implemented in this file.
 *
 * The navigation is NOT the navigation block or the settings block! These two blocks were created to display the navigation structure.
 * The navigation block looks at $PAGE->navigation, and the settings block looks at $PAGE->settingsnav. Both blocks interpret their
 * data into an HTML structure and render it.
 *
 * Any plugin implementing the following callback in lib.php can extend the course settings navigation. Prior to 3.0 only reports and
 * admin tools could extend the course settings navigation. See local_swtc_extend_navigation_course.
 *
 * Any plugin implementing the following callback in lib.php can extend the user settings navigation. Prior to 3.0 only admin tools
 * could extend the user settings navigation.
 * 		See local_swtc_extend_navigation_user_settings.
*/
/**
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
function local_swtc_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
	global $USER;

	// SWTC ******************************************************************************
	// 10/14/20 - If we're not logged in, return.
	// SWTC ******************************************************************************
	if (!isloggedin()) {
		return;
	}

	// SWTC - Debug 10/30/20
	return;

    //****************************************************************************************
	// SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user([
		'userid' => $USER->id,
		'username' => $USER->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $user_access_type = $swtc_user->get_user_access_type();

    // Array of 'Course administration nodes (keys) to remove. Main course administration key is :courseadmin. Most of these will be removed
    //          by the definition of the role. Note that when this function is called, $parentnode IS $courseadmin.
    //
    //          Typically, the Course administration node consists of (keys in parenthesis):
    //          Course administration (courseadmin)
    // $courseadmin = 'courseadmin';
    //          Edit settings (editsettings)
    // $editsettings = 'editsettings';
    //          Turn editing on (turneditingonoff)
    // $turneditingonoff = 'turneditingonoff';
    //          Course completion (not sure what key is; search for text 'Course completion')
    //          Users (users)                               Note: Remember SWTC administrators still need access to this.
	$users = 'users';										// The 'Course administration > Users' node (key).
    //          Unenroll me from ***
    // $unenrolself = 'unenrolself';
    //          Filters (not sure what key is; search for text 'Filters')
    //          Note: Removed via role definition.
    // $filters = '';
    //          Reports (coursereports)
	$coursereports = 'coursereports';
	//				Statistics (statistics)
    //          Gradebook setup (gradebooksetup)
    //          Backup (backup)
    $backup = 'backup';
    //          Restore (restore)
    $restore = 'restore';
    //          Import (import)
    $import = 'import';
    //          Reset (reset)
    //          Question bank (questionbank)
    $questionbank = 'questionbank';
    //          Repositories (not sure what key is; search for text 'Repositories')
    $repositories = 'repositories';
    // $admin_remove = array('editsettings', 'turneditingonoff', 'coursereports', 'gradebooksetup', 'backup', 'restore', 'import', 'reset', 'questionbank');
	// $admin_remove = array($users, $reports);
	$admin_remove = array($backup, $restore, $import, $questionbank, $repositories);

	// Array of 'Course administration nodes (keys) to change if certain conditions are met.
	//		Note: Adding of review node is defined in /lib/enrollib.php in function enrol_add_course_navigation. It is called twice in
	//						/lib/navigationlib.php.
    //
    $review = 'review';															// The 'Course administration > Users > Enrolled users' node (key).
	// $admin_change = array($review);
	$coursecompletionnode = 'coursecompletion';
	$coursecompletion = get_string('coursecompletion');		// The actual "Course completion" string itself.

	$coursepartnode = 'courseparticipation';
	$courseparticipation = 'Course participation';					// The actual "Course participation" string itself.

	$activitycompnode = 'activitycompletion';
	$activitycompletion = 'Activity completion';					// The actual "Activity completion" string itself.

	$admin_change = array($review, $coursecompletionnode, $coursepartnode, $activitycompnode);
	$curriculumid = null;
	// The following is the call from enrol_add_course_navigation.
	// $usersnode->add(get_string('enrolledusers', 'enrol'), $url, navigation_node::TYPE_SETTING, null, 'review', new pix_icon('i/enrolusers', ''));

    // Array of 'Course administration > Users' nodes (keys) to remove for "regular" users.
	//
    $review = 'review';															// The 'Course administration > Users > Enrolled users' node (key).
    // $manageinstances = 'manageinstances';                         // The 'Course administration > Users > Enrollment methods' node (key).
    // $groups = 'groups';															// The 'Course administration > Users > Groups' node (key).
    // $override = 'override';														    // The 'Course administration > Users > Permissions' node (key).
    // $otherusers = 'otherusers';												// The 'Course administration > Users > Other users' node (key).
    // $users_remove = array('review', 'manageinstances', 'groups', 'override', 'otherusers');
    $users_remove = array('override', 'manageinstances');

    // Local variables end...
	//****************************************************************************************

    // SWTC ********************************************************************************
    // If debugging, output header information.
    // SWTC ********************************************************************************
	if (isset($debug)) {
        $messages[] = "SWTC ********************************************************************************";
        $messages[] = "Entering local_swtc_extend_navigation_course ===4.enter===.";
        $messages[] = "About to print swtc_user.";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "Finished printing swtc_user.";
        // $messages[] = "Finished printing swtc_user. About to print navigation_node (parentnode) node";
		// $messages[] = print_r($parentnode, true);
        // $messages[] = "Finished printing navigation_node (parentnode) node";
		$messages[] = "Finished printing swtc_user. About to print course node:";
		$messages[] = print_r($course, true);
        $messages[] = "Finished printing course node. About to print context:";
		$messages[] = print_r($context, true);
		$messages[] = "Finished printing context. As a reminder, context levels follow :";
        $messages[] = "CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
        $messages[] = "As an additional reminder, navigation_node namedtypes are as follows :";
        $messages[] =  "[0] => system [10] => category [20] => course [30] => structure [40] => activity [50] => resource [60] => custom [70] => setting [71] => siteadmin [80] => user [90] => container";
        $messages[] = "SWTC ********************************************************************************";
        $debug->logmessage($messages, 'both');
        unset($messages);
        // debug_navigation($parentnode);
    }

	// SWTC ********************************************************************************
	// Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
	// SWTC ********************************************************************************
	if ( empty($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("User has not logged on yet; local_swtc_extend_navigation_course ===4.exit===.", 'both');
		}
		return;
	}

	// SWTC ********************************************************************************
	// Quick check...if user is a siteadmin, skip all this and return...
	// SWTC ********************************************************************************
	if ( is_siteadmin($USER->id)) {
		if (isset($debug)) {
			$debug->logmessage("Leaving local_swtc_extend_navigation_course ===4.exit===.", 'both');
		}
		return;
	}

	// SWTC ********************************************************************************
	//	Remove 'Course administration > Users' node. Typically, the Users node consists of:
    //          Enrolled users
    //          Enrollment methods
    //          Groups
    //          Permissions
    //          Other users
	//
	//		Attempt to find the 'Course administration > Users' node in the Course Administration (settings) node...and remove it...
	//
	// 06/13/16 - If role is Lenovo-admin, keep the 'Course administration > Users' node (i.e. skip this section).
    // 05/18/18 - If role is PremierSupport-manager or PremierSupport-admin, keep it also (with some modifications).
    // 05/21/18 - Manging menu settings using role definitions.
    // 06/21/18 - If role is Lenovo-admin, keep it also.
	// 12/14/18 - If role is PremierSupport-mgr, PremierSupport-admin, ServiceDelivery-mgr, or ServiceDelivery-admin keep the
	//						Users node ONLY IF the course is in a curriculum they are enrolled in. In other words, if they are NOT enrolled
	//						in the course as a PremierSupport or ServiceDelivery manager or admin, do NOT show the Users and Reports
	//						nodes.
    // 10/22/19 - The 'Course administration > Users' keys (using $usersnode->children->get_key_list()) are the following (in Moodle 3.7):
    //
    //                      Array
    //                      (
    //                          [0] => review
    //                          [1] => manageinstances
    //                          [2] => groups
    //                          [3] => override
    //                          [4] => otherusers
    //                      )
    //
	// SWTC ********************************************************************************
	$usersnode = $parentnode->find($users, null);
	$coursereportsnode = $parentnode->find($coursereports, null);

    if (isset($debug)) {
        //  print_r("About to print usersnode ==1===.");
        //  print_object($usersnode);
        //  print_r("Finished printing usersnode ==1===.");
        // return;
    }

    // SWTC ********************************************************************************
    // 06/13/16 - If role is Lenovo-admin, keep the 'Course administration > Users' node (i.e. skip this section).
    // 05/21/18 - Manging menu settings using role definitions.
    // 06/21/18 - If role is Lenovo-admin, keep it also.
    // SWTC ********************************************************************************
    if ( !empty($usersnode)) {
        if (isset($debug)) {
            $debug->logmessage("I found the <strong>Course administration > Users</strong> node ===4.1===.", 'both');
        //	print_r($usersnode);
        }

		// SWTC ********************************************************************************
        if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $user_access_type)) && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
			if (isset($debug)) {
				$debug->logmessage("About to remove the <strong>Course administration > Users</strong> node ===4.2===.", 'both');
			}
            if ( !$usersnode->remove()) {
                if (isset($debug)) {
                    $debug->logmessage("Error removing the <strong>Course administration > Users</strong> node ===4.2.1===.", 'both');
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Successfully removed the <strong>Course administration > Users</strong> node ===4.2.2===.", 'both');
                }
            }
        } else {
            if (isset($debug)) {
                $debug->logmessage("Most sub-menu items should be handled using role definitions. The rest will be handled here.===4.2.3===.", 'both');
            }
            // SWTC ********************************************************************************
            // Determine if we need to remove all the nodes in the $admin_remove array (see above).
            // SWTC ********************************************************************************
			// First, see if the user is enrolled in THIS course.
			$user_is_enrolled = is_enrolled($context) ? true : false;

			// Next, see if this course is part of ANY curriculum. If so, which one(s).
			//		Note: curriculums will have list of course id's.
			$crs_partof_curric = curriculum_courses_find_course($course->id);

			// Using temp boolean value for now.
			$user_is_enrolled_in_curriculum_course = false;

			// If this course it is part of ANY curriculum, see if the user is enrolled in ANY of them.
			if (isset($crs_partof_curric)) {
				$curriculums = explode(', ', $crs_partof_curric->curriculums);

				foreach($curriculums as $curriculum) {
					// print_object($curriculum);
					if (curriculum_is_user_enrolled($USER->id, $curriculum)) {
							$user_is_enrolled_in_curriculum_course = true;
							// $curriculumid will be used later when modifying the "Enrolled users" node.
							$curriculumid = $curriculum;
							continue;
					}
				}
			}

			if (isset($debug)) {
				$messages[] = "user_is_enrolled_this_course is :$user_is_enrolled.===4.2.4===.";
				$messages[] = "course_partof_curriculum follows :";
				$messages[] = print_r($crs_partof_curric, true);
				$messages[] = "user_is_enrolled_in_curriculum_course is :$user_is_enrolled_in_curriculum_course.===4.2.4===.";
				$debug->logmessage($messages, 'detailed');
				unset($messages);
			}


			// SWTC ********************************************************************************
			// MAJOR CHECK (make sure it's correct!).
			// 		IF the course is NOT part of ANY curriculum OR
			//		IF the course IS part of a curriculum, BUT the user is NOT enrolled in the curriculum course
			//
			//		THEN remove the admin nodes ($admin_remove).
			//
			// SWTC ********************************************************************************
			if (!isset($crs_partof_curric) || !$user_is_enrolled_in_curriculum_course || !$user_is_enrolled) {
                // SWTC ********************************************************************************
				// Remove the entire Course Administration > Users node.
                // 10/22/19 - Skip this for Lenovo-admins and Lenovo-siteadmins.
				// SWTC ********************************************************************************
                if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $user_access_type)) && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
                    // SWTC ********************************************************************************
                    // Remove the entire Course Administration > Users node.
                    // SWTC ********************************************************************************
                    if (isset($debug)) {
                        $debug->logmessage("About to remove the <strong>Course administration > Users </strong> node ===4.3===.", 'both');
                    }

                    if ( !$usersnode->remove()) {
                        if (isset($debug)) {
                            $debug->logmessage("Error removing the <strong>Course administration > Users </strong> node ===4.3.1===.", 'both');
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("Successfully removed the <strong>Course administration > Users </strong> node ===4.3.2===.", 'both');
                        }
                    }
                }

				// SWTC ********************************************************************************
				// Next, remove all the nodes in the $admin_remove array (see above).
				// SWTC ********************************************************************************
				// $admin_remove = array($reports);
				foreach($admin_remove as $node) {
					if (isset($debug)) {
						$debug->logmessage("Searching for the <strong>Course administration > Users > $node</strong> node ===4.4===.", 'both');
					}
					$found = $parentnode->find($node, null);

					if ( !empty($found)) {
						if (isset($debug)) {
							$debug->logmessage("I found the <strong>Course administration > Users > $node</strong> node ===4.4.1===.", 'both');
						}
						if ( !$found->remove()) {
							if (isset($debug)) {
								$debug->logmessage("Error removing the <strong>Course administration > Users > $node</strong> node ===4.4.2===.", 'both');
							}
						} else {
							if (isset($debug)) {
								$debug->logmessage("Successfully removed the <strong>Course administration > Users > $node</strong> node ===4.4.3===.", 'both');
							}
						}
					} else {
						if (isset($debug)) {
							$debug->logmessage("I DIDN'T find the <strong>Course administration > Users > $node</strong> node ===4.4.4===.", 'both');
						}
					}
				}
			} else {
				// The course IS part of a curriculum. BUT the user might not be enrolled in the curriculum (maybe they just self-enrolled in
				//		just this one course). Check to see if if the user is enrolled in the curriculum course. If NOT, remove the admin nodes
				//		($admin_remove).
				//
				// SWTC ********************************************************************************
				// Next, modify all the nodes in the $admin_change array (see above).
				//		Note: Need to remove the existing node and add the new one.
				//
				// 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery
				//							managers and administrators.
				// SWTC ********************************************************************************
				// $admin_change = array($review, $coursecompletionnode);
				// For each node, first, remove them. Then add them.
				// SWTC ********************************************************************************
				// 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery managers
				//						and administrators.
				// SWTC ********************************************************************************
                // print_object($course->defaultgroupingid);      // 05/09/19
				$groups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
                // print_object($groups);      // 05/09/19
				// Note: Should only be one returned.
				foreach($groups as $group) {
					$groupid = $group->id;
				}

				foreach($admin_change as $node) {
					switch ($node) {
						case $review:
							// Searching for and removing.
							if (isset($debug)) {
								$debug->logmessage("Searching for the <strong>Course administration > Users > $node</strong> node ===4.5===.", 'both');
							}
							// $found = $parentnode->find($node, null);		// 12/15/18 - Shouldn't this be usersnode?
							$found = $usersnode->find($node, null);

							if ( !empty($found)) {
								if (isset($debug)) {
									$debug->logmessage("I found the <strong>Course administration > Users > $node</strong> node ===4.5.1===.", 'both');
								}
								if ( !$found->remove()) {
									if (isset($debug)) {
										$debug->logmessage("Error removing the <strong>Course administration > Users > $node</strong> node ===4.5.2===.", 'both');
									}
								} else {
									if (isset($debug)) {
										$debug->logmessage("Successfully removed the <strong>Course administration > Users > $node</strong> node ===4.5.3===.", 'both');
										$debug->logmessage("About to add modified <strong>Course administration > Users > $node</strong> node ===4.5.5===.", 'both');
									}
									// Add the new one (no error checking if it didn't work).
									$url = new moodle_url('/local/swtc/lib/curriculums.php', array('curriculumid'=>$curriculumid));
									$usersnode->add(get_string('enrolledusers', 'enrol'), $url, navigation_node::TYPE_SETTING, null, 'review', new pix_icon('i/enrolusers', ''));
								}
							} else {
								if (isset($debug)) {
									$debug->logmessage("I DIDN'T find the <strong>Course administration > Users > $node</strong> node ===4.5.3===.", 'both');
								}
							}
							break;

						case $coursecompletionnode:
							// Searching for and removing.
							if (isset($debug)) {
								$debug->logmessage("Searching for the <strong>Course administration > Reports > $node</strong> node ===4.6===.", 'both');
							}

                            // SWTC ********************************************************************************
                            // 05/09/19 - Handle case if groupid is empty.
                            // SWTC ********************************************************************************
							if (!empty($groupid)) {
                                $params = array('course' => $course->id, 'group' => $groupid);
                            } else {
                                $params = array('course' => $course->id);
                            }

							// Remember - replacing link(s) in $coursereportsnode (found above).
							// debug_navigation($coursereportsnode);
							// Get all the children of the coursereportsnode.
							$children = $coursereportsnode->children;
							$found = null;
							// print_object($children);
							foreach($children as $child) {
								// Loop through the key list looking for the $coursecompletion string.
								if (strpos($child->text, $coursecompletion) !== false) {
									if (isset($debug)) {
										$messages[] = "I found the <strong>Course administration > Reports > $node</strong> node ===4.6.1===.";
										$messages[] = "About to change value of <strong> node->action</strong> setting.";
										$messages[] = "Original value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}

									$url = new moodle_url('/report/completion/index.php', $params);
									$child->action = $url;

									if (isset($debug)) {
										$messages[] = "NEW value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}
									$found = $child;
								}
							}

							if ( empty($found)) {
								if (isset($debug)) {
									$debug->logmessage("I DIDN'T find the <strong>Course administration > Reports > $node</strong> node ===4.6.2===.", 'both');
								}
							}
							break;

						case $coursepartnode:
							// Searching for and removing.
							if (isset($debug)) {
								$debug->logmessage("Searching for the <strong>Course administration > Reports > $node</strong> node ===4.7===.", 'both');
							}

							// SWTC ********************************************************************************
                            // 05/09/19 - Handle case if groupid is empty.
                            // 05/13/19 - Updated to correct the course participation hyperlink (changed "course" to "id").
                            // SWTC ********************************************************************************
							if (!empty($groupid)) {
                                $params = array('id' => $course->id, 'group' => $groupid);      // 05/13/19
                            } else {
                                $params = array('id' => $course->id);           // 05/13/19
                            }

							// Remember - replacing link(s) in $coursereportsnode (found above).
							// debug_navigation($coursereportsnode);
							// Get all the children of the coursereportsnode.
							$children = $coursereportsnode->children;
							$found = null;
							// print_object($children);
							foreach($children as $child) {
								// Loop through the key list looking for the $courseparticipation string.
								if (strpos($child->text, $courseparticipation) !== false) {
									if (isset($debug)) {
										$messages[] = "I found the <strong>Course administration > Reports > $node</strong> node ===4.7.1===.";
										$messages[] = "About to change value of <strong> node->action</strong> setting.";
										$messages[] = "Original value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}

									$url = new moodle_url('/report/participation/index.php', $params);
									$child->action = $url;

									if (isset($debug)) {
										$messages[] = "NEW value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}
									$found = $child;
								}
							}

							if ( empty($found)) {
								if (isset($debug)) {
									$debug->logmessage("I DIDN'T find the <strong>Course administration > Reports > $node</strong> node ===4.7.2===.", 'both');
								}
							}
							break;

						case $activitycompnode:
							// Searching for and removing.
							if (isset($debug)) {
								$debug->logmessage("Searching for the <strong>Course administration > Reports > $node</strong> node ===4.8===.", 'both');
							}

							// SWTC ********************************************************************************
                            // 05/09/19 - Handle case if groupid is empty.
                            // SWTC ********************************************************************************
							if (!empty($groupid)) {
                                $params = array('course' => $course->id, 'group' => $groupid);
                            } else {
                                $params = array('course' => $course->id);
                            }

							// Remember - replacing link(s) in $coursereportsnode (found above).
							// debug_navigation($coursereportsnode);
							// Get all the children of the coursereportsnode.
							$children = $coursereportsnode->children;
							$found = null;
							// print_object($children);
							foreach($children as $child) {
								// Loop through the key list looking for the $activitycompletion string.
								if (strpos($child->text, $activitycompletion) !== false) {
									if (isset($debug)) {
										$messages[] = "I found the <strong>Course administration > Reports > $node</strong> node ===4.8.1===.";
										$messages[] = "About to change value of <strong> node->action</strong> setting.";
										$messages[] = "Original value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}

									$url = new moodle_url('/report/progress/index.php', $params);
									$child->action = $url;

									if (isset($debug)) {
										$messages[] = "NEW value of action follows :";
										$messages[] = print_r($child->action, true);
										$debug->logmessage($messages, 'both');
										unset($messages);
									}
									$found = $child;
								}
							}

							if ( empty($found)) {
								if (isset($debug)) {
									$debug->logmessage("I DIDN'T find the <strong>Course administration > Reports > $node</strong> node ===4.8.2===.", 'both');
								}
							}
							break;

						default:
							// unknown type
					}
				}
			}

			// SWTC ********************************************************************************
			// Next, remove all the nodes in the $users_remove array (see above) for ALL users.
			// SWTC ********************************************************************************
			// $review = 'review';															// The 'Course administration > Users > Enrolled users' node (key).
			// $manageinstances = 'manageinstances';                         // The 'Course administration > Users > Enrollment methods' node (key).
			// $override = 'override';														    // The 'Course administration > Users > Permissions' node (key).
			// $users_remove = array($review, $manageinstances, $override);
            // SWTC ********************************************************************************
            // 10/22/19 - Skip this for Lenovo-admins and Lenovo-siteadmins.
            // SWTC ********************************************************************************
            if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $user_access_type)) && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
                foreach($users_remove as $node) {
                    if (isset($debug)) {
                        $debug->logmessage("Searching for the <strong>Course administration > Users > $node</strong> node ===4.6===.", 'both');
                    }
                    $found = $parentnode->find($node, null);

                    if ( !empty($found)) {
                        if (isset($debug)) {
                            $debug->logmessage("I found the <strong>Course administration > Users > $node</strong> node ===4.6.1===.", 'both');
                        }
                        if ( !$found->remove()) {
                            if (isset($debug)) {
                                $debug->logmessage("Error removing the <strong>Course administration > Users > $node</strong> node ===4.6.2===.", 'both');
                            }
                        } else {
                            if (isset($debug)) {
                                $debug->logmessage("Successfully removed the <strong>Course administration > Users > $node</strong> node ===4.6.3===.", 'both');
                            }
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("I DIDN'T find the <strong>Course administration > Users > $node</strong> node ===4.6.4===.", 'both');
                        }
                    }
                }
            }
        }
    } else {
        if (isset($debug)) {
            $debug->logmessage("I DIDN'T find the <strong>Course administration > Users</strong> node. Continuing ===4.9.9===...", 'both');
        }
    }

    // SWTC ********************************************************************************
	//	Remove the 'Course administration > Course completion' node (text).
	// SWTC ********************************************************************************
    // $completionnode = $parentnode->find($completion, null);
    //
    // if (isset($debug)) {
        //  print_r("About to print parentnode ==1===.");
        //  print_object($parentnode);
        //  print_r("Finished printing parentnode ==1===.");
        //  print_r("About to print children ==1===.");
        //  print_r($parentnode['children']);
        //  print_r("Finished printing children ==1===.");
        // return;
    // }

	if (isset($debug)) {
		$debug->logmessage("Leaving local_swtc_extend_navigation_course ===4.exit===.", 'both');
	}
}

function local_swtc_extend_navigation_category_settings(navigation_node $parentnode, context_coursecat $context) {
    global $USER;

	// SWTC ******************************************************************************
	// 10/14/20 - If we're not logged in, return.
	// SWTC ******************************************************************************
	if (!isloggedin()) {
		return;
	}

	// SWTC - Debug 10/30/20
	return;

	//****************************************************************************************
	// SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user([
		'userid' => $USER->id,
		'username' => $USER->username]);
	$debug = swtc_set_debug();

    // SWTC ********************************************************************************
    // If debugging, output header information.
    // SWTC ********************************************************************************
	if (isset($debug)) {
        $messages[] = "SWTC ********************************************************************************";
        $messages[] = "Entering local_swtc_extend_navigation_category_settings ===2.enter===.";
        // $messages[] = "About to print swtc_user.";
        // $messages[] = print_r($swtc_user, true);
        $messages[] = "About to print navigation_node (parentnode) node";
        $messages[] = print_r(array_keys((array)$parentnode), true);
        // $messages[] = print_r($parentnode->courses, true);
        $messages[] = "Finished printing navigation_node (parentnode) node";
        $messages[] = "SWTC ********************************************************************************";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }



}
