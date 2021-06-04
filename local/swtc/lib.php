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
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 02/11/21 - Since we're using moodle/category:viewcourselist, testing of removing
 * each navigation function.
 *
 **/

defined('MOODLE_INTERNAL') || die();

use \local_swtc\swtc_user;
use \local_swtc\swtc_debug;

require_once($CFG->libdir. '/navigationlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->libdir . '/grouplib.php');

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

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

    // SWTC - Debug 10/30/20.
    return;

    // SWTC ********************************************************************************.
    // 10/14/20 - If we're not logged in, return.
    // SWTC ********************************************************************************.
    if (!isloggedin()) {
        return;
    }

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    // SWTC ********************************************************************************.
    $swtcuser = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $useraccesstype = $swtcuser->get_useraccesstype();
    $mycourses = 'mycourses';        // The key for 'My courses'.
    $mycurriculums = get_string('mycurriculums', 'local_swtc');       // The title for 'My Curriculums'.
    $site = 'site';        // The key for 'Site' (i.e. Navigation > Home > swtc).
    $participants = 'participants';        // The key for 'Participants' (i.e. Navigation > Home > swtc > Participants).

    $capability = $swtcuser->get_capabilities()[0];
    $accessselfsupportstud = get_string('accessselfsupportstud', 'local_swtc');

    if (isset($debug)) {
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering /local/swtc/lib.php.===local_swtc_extend_navigation.enter.";
        $messages[] = "About to print swtcuser.";
        $messages[] = print_r($swtcuser, true);
        $messages[] = "Finished printing swtcuser.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
    // SWTC ********************************************************************************.
    if ( empty($USER->id)) {
        if (isset($debug)) {
            $debug->logmessage("User has not logged on yet; leaving local_swtc_extend_navigation ===1.exit===.", 'both');
        }
        return;
    }

    // SWTC ********************************************************************************.
    // Quick check...if user is a siteadmin, skip all this and return...
    // SWTC ********************************************************************************.
    if (is_siteadmin($USER->id)) {
        if (isset($debug)) {
            $debug->logmessage("Leaving local_swtc_extend_navigation ===1.exit===.", 'both');
        }
        return;
    }

    // SWTC ********************************************************************************.
    // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
    // of access (before moving to core_course_category::can_view_category).
    // @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/swtc_course_renderer.php
    // (changed core_course_category to \core_course_category).
    // SWTC ********************************************************************************.
    $categories = \core_course_category::make_categories_list($capability);

    // SWTC ********************************************************************************.
    // Determine if user should have access to category. If not, remove it.
    // SWTC ********************************************************************************.
    $courses = $nav->find('courses', null);             // Find the courses node.
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

    // SWTC ********************************************************************************.
    // Main loop. See if children is in $SESSION->SWTC->USER->categoryids. If it is found, the user has access, so
    // leave the course in the list. If the user doesn't, remove it from the list.
    // SWTC ********************************************************************************.
    foreach ($children as $key => $catid) {
        if ((in_array($catid, array_keys($categories))) || (stripos($swtcuser->useraccesstype, $accessselfsupportstud) !== false)) {
            if (isset($debug)) {
                $messages[] = "Child category $catid found in SESSION->SWTC->USER->categoryids.
                    Keeping category in list. ===1.1===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
        } else {
            if (isset($debug)) {
                $messages[] = "Child category catid NOT found in SESSION->SWTC->USER->categoryids.
                    Removing category from list. ===1.1===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            $courses->children->remove($children[$key]);
        }
    }

    // SWTC ********************************************************************************.
    // 11/14/19 - In local_swtc_extend_navigation, added fix to remove "swtc > Participants" navigation node if not a SWTC-admin
    // or SWTC-siteadmin.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $useraccesstype))
        && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
        $node = $nav->find($site, null);
        $participantnode = $node->children->find($participants, null);

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
        } else {
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
    }

    // SWTC ********************************************************************************.
    // Since the "My overview" (course overview) plugin has been sunset and removed from all our sites, this code has
    // been removed. This header remains as a reminder.
    //
    // Attempting to find the "My overview" block. It is located on the user's "My Courses" page.
    // Addressability is via $PAGE->blocks; the class returned is an object of class
    // "block_manager" (/lib/blocklib.php).
    //
    // SWTC ********************************************************************************.
    // Removed a section of code, comments, or both. See archived versions of module for information.
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        $debug->logmessage("Leaving local_swtc_extend_navigation ===1.exit===.", 'both');
    }
}

/* Navigation is available through the page object $PAGE, against which you set the heading for the page, the title, any JavaScript
 * requirements, etc. The navigation structure uses the information $PAGE contains to generate a navigation structure for the site.
 * The navigation or settings blocks are interpretations of the navigation structure Moodle creates.
 *
 * This navigation structure is available through three variables:
 * $PAGE->navigation: This is the main navigation structure, it will contain items that will allow the user to browse to the other
 *          available pages. See local_swtc_extend_navigation.
 * $PAGE->settingsnav: This is the settings navigation structure contains items that will allow the user to edit settings.
 * 		See local_swtc_extend_settings_navigation.
 * $PAGE->navbar: The navbar is a special structure for page breadcrumbs.
 *			Not implemented in this file.
 *
 * The navigation is NOT the navigation block or the settings block! These two blocks were created to
 * display the navigation structure. The navigation block looks at $PAGE->navigation, and the settings
 * block looks at $PAGE->settingsnav. Both blocks interpret their data into an HTML structure and render it.
 *
 * Any plugin implementing the following callback in lib.php can extend the course settings navigation.
 * Prior to 3.0 only reports and admin tools could extend the course settings navigation. See
 * local_swtc_extend_navigation_course.
 *
 * Any plugin implementing the following callback in lib.php can extend the user settings navigation. Prior to 3.0 only admin tools
 * could extend the user settings navigation.
 * 		See local_swtc_extend_navigation_user_settings.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
function local_swtc_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    global $USER;

    // SWTC - Debug 10/30/20.
    return;

    // SWTC ********************************************************************************.
    // If we're not logged in, return.
    // SWTC ********************************************************************************.
    if (!isloggedin()) {
        return;
    }

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $swtcuser = swtc_get_user([
    'userid' => $USER->id,
    'username' => $USER->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $useraccesstype = $swtcuser->get_useraccesstype();

    // SWTC ********************************************************************************.
    // Array of top-level settingsnav menu 'Front page settings' (frontpage) nodes (keys) to remove. Main front page
    // settings key is :frontpage.
    //
    // Notes:
    // $settingsnav->children->get_key_list() should be the following:
    // Array ( [0] => frontpage [1] => dashboard [2] => siteadministration )
    //
    // Front page (frontpage).
    $frontpage = 'frontpage';

    // SWTC ********************************************************************************.
    // Array of top-level menu 'Site administration nodes (keys) to remove. Main site administration key is :siteadministration.
    // Most of these will be removed by the definition of the role.
    //
    // Typically, the Site administration node consists of (keys in parenthesis):
    // Site administration (siteadministration)
    // $siteadministration = 'siteadministration';
    // Notifications (adminnotifications).
    $adminnotifications = 'adminnotifications';
    // Registration (registrationmoodleorg).
    $regmoodleorg = 'registrationmoodleorg';
    // Advanced features (optionalsubsystems).
    $advancedfeatures = 'optionalsubsystems';
    // Users (users). Note: Remember SWTC administrators still need access to this.
    // $users = 'users'.    // The 'Course administration > Users' node (key).
    // Courses (courses)
    // $courses = 'courses';
    // Grades (grades).
    $grades = 'grades';
    // Analytics (analytics).
    $analytics = 'analytics';
    // Competencies (competencies).
    $competencies = 'competencies';
    // Badge settings (badges).
    // Note: Badges are not enabled.
    // Location (location).
    $location = 'location';
    // Language (language).
    // $language = 'language';
    // Plugins (modules).
    $modules = 'modules';
    // Security (security).
    $security = 'security';
    // Appearance (appearance).
    $appearance = 'appearance';
    // Front page (frontpage).
    // $frontpage = 'frontpage';
    // Server (server).
    $server = 'server';
    // Mnet (mnet)
    // Reports (reports)
    // $reports = 'reports';
    // Mobile app (mobileapp).
    $mobileapp = 'mobileapp';
    // Development (development).
    $development = 'development';
    // Search (search)
    // $search = 'search';
    // Assignment upgrade helper (assignmentupgrade).
    $assignmentupgrade = 'assignmentupgrade';
    // Multilang upgrade (toolmultilangupgrade).
    $toolmultilangupgrade = 'toolmultilangupgrade';
    // SWTC (swtcadmin).
    // $swtcadmin = 'swtcadmin';
    // Moodle services (Moodle 3.6.3).
    $moodleservices = 'moodleservices';
    $admintopremove = array($adminnotifications, $regmoodleorg, $advancedfeatures, $grades, $analytics, $competencies,
        $location, $modules, $security, $appearance, $frontpage, $server, $mobileapp, $development, $assignmentupgrade,
        $toolmultilangupgrade, $moodleservices);

    // SWTC ********************************************************************************.
    // Array of lower-level menu 'Site administration nodes (keys) to remove. Main site administration key is :siteadministration.
    // Most of these will be removed by the definition of the role.
    //
    // Typically, the Site administration node consists of (keys in parenthesis):
    // Users (users)                               Note: Remember SWTC administrators still need access to this.
    // $users = 'users';                                    // The 'Course administration > Users' node (key).
    // $accounts = 'accounts'.
    $roles = 'roles';
    $privacy = 'privacy';
    $userdefpreferences = 'userdefaultpreferences';
    $profilefields = 'profilefields';
    $tooluploaduserpics = 'tooluploaduserpictures';
    // Courses (courses).
    // $courses = 'courses'.
    $addcategory = 'addcategory';
    $restorecourse = 'restorecourse';
    $coursesettings = 'coursesettings';
    $courserequest = 'courserequest';
    $backups = 'backups';
    $tooluploadcourse = 'tooluploadcourse';
    $addnewcourse = 'addnewcourse';
    // Grades (grades).
    // $grades = 'grades';
    // Analytics (analytics).
    // $analytics = 'analytics';
    // Competencies (competencies).
    // $competencies = 'competencies';
    // Badge settings (badges).
    // Note: Badges are not enabled.
    // Location (location).
    // $location = 'location';
    // Language (language).
    // $language = 'language';
    // Plugins (modules).
    // $modules = 'modules';
    // Security (security).
    // $security = 'security';
    // Appearance (appearance).
    // $appearance = 'appearance';
    // Front page (frontpage).
    // Note: Removed for everyone separately below.
    // $frontpage = 'frontpage';
    // Server (server).
    // $server = 'server';
    // Mnet (mnet).
    // Reports (reports).
    // $reports = 'reports'.
    $comments = 'comments';
    $reportbackups = 'reportbackups';
    $reportconfiglog = 'reportconfiglog';
    $reporteventlists = 'reporteventlists';
    $reportperformance = 'reportperformance';
    $rptquestioninstances = 'reportquestioninstances';
    $reportsecurity = 'reportsecurity';
    $reportstats = 'reportstats';
    $toolmonitorrules = 'toolmonitorrules';
    $toolspamcleaner = 'toolspamcleaner';
    // Mobile app (mobileapp).
    // $mobileapp = 'mobileapp';
    // Development (development).
    // $development = 'development';
    // Unsupported
    // Search (search).
    // $search = 'search';
    // Assignment upgrade helper (assignmentupgrade).
    // $assignmentupgrade = 'assignmentupgrade';
    // Multilang upgrade (toolmultilangupgrade).
    // SWTC (swtcadmin)
    // $swtcadmin = 'swtcadmin'.
    $localswtcsettings = 'localswtcsettings';
    $invitehistory = 'invitehistory';
    $servicebench = 'servicebench';
    $adminsecondremove = array($roles, $privacy, $userdefpreferences, $profilefields, $tooluploaduserpics, $addcategory,
        $restorecourse, $coursesettings, $courserequest, $backups, $tooluploadcourse, $comments, $reportbackups, $reportconfiglog,
        $reporteventlists, $reportperformance, $rptquestioninstances, $reportsecurity, $reportstats, $toolmonitorrules,
        $toolspamcleaner, $localswtcsettings, $invitehistory, $servicebench, $addnewcourse);
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering /local/swtc/lib.php===local_swtc_extend_settings_navigation.enter===.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $debug->logmessage($messages, 'both');
        unset($messages);

        // SWTC ********************************************************************************.
        // Detailed debugging information.
        //
        // Notes:
        // $settingsnav->children->get_key_list() should be the following:
        // Array ( [0] => frontpage [1] => dashboard [2] => siteadministration )
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "About to print settings_navigation (settingsnav) node.";
        $messages[] = print_r(array_keys((array)$settingsnav), true);
        $messages[] = "Finished printing settings_navigation (settingsnav) node.";
        $messages[] = "About to print children get_key_list :";
        $allnodes = $settingsnav->children->get_key_list();
        $messages[] = print_r($allnodes, true);
        $messages[] = "Finished printing children get_key_list.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
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
    // SWTC ********************************************************************************.
    // Remove 'Front page' for all users.
    //
    // Attempt to find the 'frontpage' node in the Administration (settings) node...and remove it.
    // The 'frontpage' node would only be available IF the user is viewing the frontpage of the site
    // (which is course id 1) AND the user has the appropriate capabilities. However, this should be
    // done for user's that are not students (students do not have access to edit the frontpage).
    // Editing the frontpage should only be available for SWTC site administrators.
    //
    // Notes:
    // When using "->find" in the navigation_node object, you're searching for the key value:
    // [id] = frontpagesettings ("Front page settings")
    // [key] = frontpage ("Front page")
    // [text] = Front page settings
    // context will be 50 (CONTEXT_COURSE) AND the course id (context->instanceid) will be 1.
    // SWTC ********************************************************************************.
    if ($context->contextlevel == CONTEXT_COURSE && $context->instanceid == SITEID) {
        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Detailed debugging information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering local_swtc_extend_settings_navigation.removing_frontpage ===2.0.5.enter.";
            $messages[] = "About to print context :";
            $messages[] = print_r($context, true);
            $messages[] = "Finished printing context. As a reminder, context levels follow :";
            $messages[] = "CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50);
                CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
            $messages[] = "As an additional reminder, navigation_node namedtypes are as follows :";
            $messages[] = "[0] => system [10] => category [20] => course [30] => structure [40] => activity [50]
                => resource [60] => custom [70] => setting [71] => siteadmin [80] => user [90] => container";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // See if the user is viewing the frontpage of the site.
        $frontpageroot = $settingsnav->find($frontpage, navigation_node::TYPE_SETTING);

        // SWTC ********************************************************************************.
        // Fixed access for Lenovo-admin and Lenovo-siteadmin user types.
        // SWTC ********************************************************************************.
        if (!empty($frontpageroot)) {
            if (isset($debug)) {
                $debug->logmessage("I found the frontpage node ===2.0.5===.", 'both');
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
        } else {
            if (isset($debug)) {
                $debug->logmessage("I DIDN'T find the frontpage node. Continuing ===2.0.5===...", 'both');
            }
        }
    } else if ($context->contextlevel == CONTEXT_SYSTEM) {
        // SWTC ********************************************************************************.
        //
        //
        // Notes:
        // CONTEXT_SYSTEM is associated with any interaction with the "Site administration" menu. It's key is "modulesettings".
        // Some examples are:
        // When on the site frontpage (with the "Site administration" menu collapsed), the user clicks the tie to expand the menu.
        //
        //
        // SWTC ********************************************************************************.
        // Do something interesting.
    } else if ($context->contextlevel == CONTEXT_COURSE) {
        // Need to remove the Filters, Backup, Restore, Import, Question bank, and Repositories nodes; done in
        // local_swtc_extend_navigation_course.
    } else if ($context->contextlevel == CONTEXT_MODULE) {
        // SWTC ********************************************************************************.
        // Remove 'Forum administration' for all users. Main "Forum administration" key is :modulesettings.
        // Most of these will be removed by the definition of the role.
        //
        // Typically, the Forum administration node consists of (keys in parenthesis):
        // Forum administration (modulesettings).
        $modulesettings = 'modulesettings';
        // Notes:
        // CONTEXT_MODULE is associated with any interaction with the "Forum administration" menu. It's key is "modulesettings".
        // Some examples are:
        // When on the site frontpage, clicking on the "Older topics..." hyperlink just below the Site announcements forum.
        //
        // If in CONTEXT_MODULE, if it is a Quiz, keep the menu.
        // SWTC ********************************************************************************.
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
        // SWTC ********************************************************************************.
        // Remove 'Category settings' for all users.
        //
        // Attempt to find the 'categorysettings' node in the Administration (settings) node...and remove it.
        // SWTC ********************************************************************************.
        $categorysettings = $settingsnav->find('categorysettings', null);

        if (!empty($categorysettings)) {
            if (isset($debug)) {
                $debug->logmessage("I found a Category settings node. ===2.6===.", 'both');
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

    // SWTC ********************************************************************************.
    // Fixed access for Lenovo-admin and Lenovo-siteadmin user types.
    // SWTC ********************************************************************************.
    if ($adminroot = $settingsnav->find('siteadministration', \navigation_node::TYPE_SITE_ADMIN)) {
        // TODO - 02/20/19 - Look for a better way to do this...
        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Detailed debugging information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "I found the <strong>Site administration</strong> menu.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $useraccesstype))
            && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
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
            }
        } else {
            if (isset($debug)) {
                $debug->logmessage("User type is either PremierSupport-admin, PremierSupport-mgr,
                    ServiceDelivery-admin, ServiceDelivery-mgr,  Lenovo-admin, or Lenovo-siteadmin -
                    keeping the Site administration menu ===2.2===.", 'both');
                $debug->logmessage("Most sub-menu items should be handled using role definitions.
                    The rest will be handled here.===2.2===.", 'both');
            }

            // SWTC ********************************************************************************.
            // Next, remove all the nodes in the $admintopremove array (see above).
            // SWTC ********************************************************************************.
            foreach ($admintopremove as $node) {
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
                        $debug->logmessage("I DIDN'T find the <strong>Site administration >
                            $node</strong> node ===2.2.5===.", 'both');
                    }
                }
            }

            // SWTC ********************************************************************************.
            // Next, remove all the nodes in the $adminsecondremove array (see above).
            // SWTC ********************************************************************************.
            foreach ($adminsecondremove as $node) {
                if (isset($debug)) {
                    $debug->logmessage("Searching for the <strong>Site administration >$node</strong> node. ===2.2.5===.", 'both');
                }

                // Can we find it?
                $found = $settingsnav->find($node, null);

                if ( !empty($found)) {
                    if (isset($debug)) {
                        $debug->logmessage("I found the <strong>Site administration >$node</strong> node ===2.2.5===.", 'both');
                    }

                    // Remove it.
                    if ( !$found->remove()) {
                        if (isset($debug)) {
                            $debug->logmessage("Error removing the <strong>Site administration >
                                $node</strong> node ===2.2.5===.", 'both');
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("Successfully removed the <strong>Site administration >
                                $node</strong> node ===2.2.5===.", 'both');
                        }
                    }
                } else {
                    if (isset($debug)) {
                        $debug->logmessage("I DIDN'T find the <strong>Site administration >
                            $node</strong> node ===2.2.5===.", 'both');
                    }
                }
            }
        }
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
 * requirements, etc. The navigation structure uses the information $PAGE contains to generate a navigation structure for the site.
 * The navigation or settings blocks are interpretations of the navigation structure Moodle creates.
 *
 * This navigation structure is available through three variables:
 * $PAGE->navigation: This is the main navigation structure, it will contain items that will allow the user to browse to the other
 *          available pages. See local_swtc_extend_navigation.
 * $PAGE->settingsnav: This is the settings navigation structure contains items that will allow the user to edit settings.
 * 		See local_swtc_extend_settings_navigation.
 * $PAGE->navbar: The navbar is a special structure for page breadcrumbs.
 *			Not implemented in this file.
 *
 * The navigation is NOT the navigation block or the settings block! These two blocks were created to display the navigation
 * structure. The navigation block looks at $PAGE->navigation, and the settings block looks at $PAGE->settingsnav. Both blocks
 * interpret their data into an HTML structure and render it.
 *
 * Any plugin implementing the following callback in lib.php can extend the course settings navigation. Prior to 3.0 only reports
 * and admin tools could extend the course settings navigation. See local_swtc_extend_navigation_course.
 *
 * Any plugin implementing the following callback in lib.php can extend the user settings navigation. Prior to 3.0 only admin tools
 * could extend the user settings navigation.
 * 		See local_swtc_extend_navigation_user_settings.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/
function local_swtc_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    global $USER;

    // SWTC ********************************************************************************.
    // 10/14/20 - If we're not logged in, return.
    // SWTC ********************************************************************************.
    if (!isloggedin()) {
        return;
    }

    // SWTC - Debug 10/30/20.
    return;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $swtcuser = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $useraccesstype = $swtcuser->get_useraccesstype();

    // Array of 'Course administration nodes (keys) to remove. Main course administration key is :courseadmin.
    // Most of these will be removed by the definition of the role. Note that when this function is called,
    // $parentnode IS $courseadmin.
    //
    // Typically, the Course administration node consists of (keys in parenthesis):
    // Course administration (courseadmin)
    // $courseadmin = 'courseadmin';
    // Edit settings (editsettings)
    // $editsettings = 'editsettings';
    // Turn editing on (turneditingonoff)
    // $turneditingonoff = 'turneditingonoff';
    // Course completion (not sure what key is; search for text 'Course completion')
    // Users (users)                               Note: Remember SWTC administrators still need access to this.
    $users = 'users';                                        // The 'Course administration > Users' node (key).
    // Unenroll me from ***
    // $unenrolself = 'unenrolself';
    // Filters (not sure what key is; search for text 'Filters')
    // Note: Removed via role definition.
    // $filters = '';
    // Reports (coursereports).
    $coursereports = 'coursereports';
    // Statistics (statistics)
    // Gradebook setup (gradebooksetup)
    // Backup (backup).
    $backup = 'backup';
    // Restore (restore).
    $restore = 'restore';
    // Import (import).
    $import = 'import';
    // Reset (reset).
    // Question bank (questionbank).
    $questionbank = 'questionbank';
    // Repositories (not sure what key is; search for text 'Repositories').
    $repositories = 'repositories';
    $adminremove = array($backup, $restore, $import, $questionbank, $repositories);

    // Array of 'Course administration nodes (keys) to change if certain conditions are met.
    // Note: Adding of review node is defined in /lib/enrollib.php in function enrol_add_course_navigation. It is called twice in
    // lib/navigationlib.php.
    //
    $review = 'review'; // The 'Course administration > Users > Enrolled users' node (key).
    $coursecompletionnode = 'coursecompletion';
    $coursecompletion = get_string('coursecompletion');        // The actual "Course completion" string itself.

    $coursepartnode = 'courseparticipation';
    $courseparticipation = 'Course participation';                    // The actual "Course participation" string itself.

    $activitycompnode = 'activitycompletion';
    $activitycompletion = 'Activity completion';                    // The actual "Activity completion" string itself.

    $adminchange = array($review, $coursecompletionnode, $coursepartnode, $activitycompnode);
    $curriculumid = null;

    // Array of 'Course administration > Users' nodes (keys) to remove for "regular" users.
    //
    $review = 'review'; // The 'Course administration > Users > Enrolled users' node (key).
    $usersremove = array('override', 'manageinstances');

    // Local variables end...
    // SWTC ********************************************************************************.
    // If debugging, output header information.
    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering local_swtc_extend_navigation_course ===4.enter===.";
        $messages[] = "About to print swtcuser.";
        $messages[] = print_r($swtcuser, true);
        $messages[] = "Finished printing swtcuser. About to print course node:";
        $messages[] = print_r($course, true);
        $messages[] = "Finished printing course node. About to print context:";
        $messages[] = print_r($context, true);
        $messages[] = "Finished printing context. As a reminder, context levels follow :";
        $messages[] = "CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40); CONTEXT_COURSE (50);
            CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
        $messages[] = "As an additional reminder, navigation_node namedtypes are as follows :";
        $messages[] = "[0] => system [10] => category [20] => course [30] => structure [40] => activity [50]
            => resource [60] => custom [70] => setting [71] => siteadmin [80] => user [90] => container";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
    // SWTC ********************************************************************************.
    if ( empty($USER->id)) {
        if (isset($debug)) {
            $debug->logmessage("User has not logged on yet; local_swtc_extend_navigation_course ===4.exit===.", 'both');
        }
        return;
    }

    // SWTC ********************************************************************************.
    // Quick check...if user is a siteadmin, skip all this and return...
    // SWTC ********************************************************************************.
    if ( is_siteadmin($USER->id)) {
        if (isset($debug)) {
            $debug->logmessage("Leaving local_swtc_extend_navigation_course ===4.exit===.", 'both');
        }
        return;
    }

    // SWTC ********************************************************************************.
    // Remove 'Course administration > Users' node. Typically, the Users node consists of:
    // Enrolled users
    // Enrollment methods
    // Groups
    // Permissions
    // Other users
    //
    // Attempt to find the 'Course administration > Users' node in the Course Administration (settings) node...and remove it...
    //
    // 06/13/16 - If role is Lenovo-admin, keep the 'Course administration > Users' node (i.e. skip this section).
    // 05/18/18 - If role is PremierSupport-manager or PremierSupport-admin, keep it also (with some modifications).
    // 05/21/18 - Manging menu settings using role definitions.
    // 06/21/18 - If role is Lenovo-admin, keep it also.
    // 12/14/18 - If role is PremierSupport-mgr, PremierSupport-admin, ServiceDelivery-mgr, or ServiceDelivery-admin keep the
    // Users node ONLY IF the course is in a curriculum they are enrolled in. In other words, if they are NOT enrolled
    // in the course as a PremierSupport or ServiceDelivery manager or admin, do NOT show the Users and Reports
    // nodes.
    // The 'Course administration > Users' keys (using $usersnode->children->get_key_list()) are the following (in Moodle 3.7):
    //
    // Array
    // (
    // [0] => review
    // [1] => manageinstances
    // [2] => groups
    // [3] => override
    // [4] => otherusers
    // )
    //
    // SWTC ********************************************************************************.
    $usersnode = $parentnode->find($users, null);
    $coursereportsnode = $parentnode->find($coursereports, null);

    // SWTC ********************************************************************************.
    // If role is Lenovo-admin, keep the 'Course administration > Users' node (i.e. skip this
    // section).
    // Manging menu settings using role definitions.
    // If role is Lenovo-admin, keep it also.
    // SWTC ********************************************************************************.
    if ( !empty($usersnode)) {
        if (isset($debug)) {
            $debug->logmessage("I found the <strong>Course administration >
                Users</strong> node ===4.1===.", 'both');
        }

        // SWTC ********************************************************************************.
        if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $useraccesstype))
            && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
            if (isset($debug)) {
                $debug->logmessage("About to remove the <strong>Course administration > Users</strong> node ===4.2===.", 'both');
            }
            if ( !$usersnode->remove()) {
                if (isset($debug)) {
                    $debug->logmessage("Error removing the <strong>Course administration > Users</
                        strong> node ===4.2.1===.", 'both');
                }
            } else {
                if (isset($debug)) {
                    $debug->logmessage("Successfully removed the <strong>Course administration >
                        Users</strong> node ===4.2.2===.", 'both');
                }
            }
        } else {
            if (isset($debug)) {
                $debug->logmessage("Most sub-menu items should be handled using role definitions.
                    The rest will be handled here.===4.2.3===.", 'both');
            }
            // SWTC ********************************************************************************.
            // Determine if we need to remove all the nodes in the $adminremove array (see above).
            // SWTC ********************************************************************************.
            // First, see if the user is enrolled in THIS course.
            $userisenrolled = is_enrolled($context) ? true : false;

            // Next, see if this course is part of ANY curriculum. If so, which one(s).
            // Note: curriculums will have list of course id's.
            $crspartofcurric = curriculum_courses_find_course($course->id);

            // Using temp boolean value for now.
            $userisenrolledincurriculumcourse = false;

            // If this course it is part of ANY curriculum, see if the user is enrolled in ANY of them.
            if (isset($crspartofcurric)) {
                $curriculums = explode(', ', $crspartofcurric->curriculums);

                foreach ($curriculums as $curriculum) {
                    if (curriculum_is_user_enrolled($USER->id, $curriculum)) {
                            $userisenrolledincurriculumcourse = true;
                            // Curriculumid will be used later when modifying the "Enrolled users" node.
                            $curriculumid = $curriculum;
                            continue;
                    }
                }
            }

            if (isset($debug)) {
                $messages[] = "userisenrolled is :$userisenrolled.===4.2.4===.";
                $messages[] = "crspartofcurric follows :";
                $messages[] = print_r($crspartofcurric, true);
                $messages[] = "userisenrolledincurriculumcourse is :$userisenrolledincurriculumcourse.===4.2.4===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            // SWTC ********************************************************************************.
            // MAJOR CHECK (make sure it's correct!).
            // IF the course is NOT part of ANY curriculum OR
            // IF the course IS part of a curriculum, BUT the user is NOT enrolled in the curriculum course
            //
            // THEN remove the admin nodes ($adminremove).
            //
            // SWTC ********************************************************************************.
            if (!isset($crspartofcurric) || !$userisenrolledincurriculumcourse || !$userisenrolled) {
                // SWTC ********************************************************************************.
                // Remove the entire Course Administration > Users node.
                // 10/22/19 - Skip this for Lenovo-admins and Lenovo-siteadmins.
                // SWTC ********************************************************************************.
                if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $useraccesstype))
                    && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
                    // SWTC ********************************************************************************.
                    // Remove the entire Course Administration > Users node.
                    // SWTC ********************************************************************************.
                    if (isset($debug)) {
                        $debug->logmessage("About to remove the <strong>Course administration >
                            Users </strong> node ===4.3===.", 'both');
                    }

                    if ( !$usersnode->remove()) {
                        if (isset($debug)) {
                            $debug->logmessage("Error removing the <strong>Course administration >
                                Users </strong> node ===4.3.1===.", 'both');
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("Successfully removed the <strong>Course
                                administration > Users </strong> node ===4.3.2===.", 'both');
                        }
                    }
                }

                // SWTC ********************************************************************************.
                // Next, remove all the nodes in the $adminremove array (see above).
                // SWTC ********************************************************************************.
                foreach ($adminremove as $node) {
                    if (isset($debug)) {
                            $debug->logmessage("Searching for the <strong>Course administration >
                                Users > $node</strong> node ===4.4===.", 'both');
                    }
                    $found = $parentnode->find($node, null);

                    if ( !empty($found)) {
                        if (isset($debug)) {
                            $debug->logmessage("I found the <strong>Course administration > Users >
                                $node</strong> node ===4.4.1===.", 'both');
                        }
                        if ( !$found->remove()) {
                            if (isset($debug)) {
                                $debug->logmessage("Error removing the <strong>Course administration > Users >
                                    $node</strong> node ===4.4.2===.", 'both');
                            }
                        } else {
                            if (isset($debug)) {
                                $debug->logmessage("Successfully removed the <strong>Course administration > Users >
                                    $node</strong> node ===4.4.3===.", 'both');
                            }
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("I DIDN'T find the <strong>Course administration >
                                Users > $node</strong> node ===4.4.4===.", 'both');
                        }
                    }
                }
            } else {
                // The course IS part of a curriculum. BUT the user might not be enrolled in the
                // curriculum (maybe they just self-enrolled in just this one course).
                // Check to see if if the user is enrolled in the curriculum course. If NOT,
                // remove the admin nodes ($adminremove).
                // SWTC ********************************************************************************.
                // Next, modify all the nodes in the $adminchange array (see above).
                // Note: Need to remove the existing node and add the new one.
                //
                // 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery
                // managers and administrators.
                // SWTC ********************************************************************************.
                // $adminchange = array($review, $coursecompletionnode);
                // For each node, first, remove them. Then add them.
                // SWTC ********************************************************************************.
                // Added 'group' parameter to URL that is built for each tab for PremierSupport
                // and ServiceDelivery managers and administrators.
                // SWTC ********************************************************************************.
                $groups = swtc_groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
                // Note: Should only be one returned.
                foreach ($groups as $group) {
                    $groupid = $group->id;
                }

                foreach ($adminchange as $node) {
                    switch ($node) {
                        case $review:
                            // Searching for and removing.
                            if (isset($debug)) {
                                $debug->logmessage("Searching for the <strong>Course administration > Users >
                                    $node</strong> node ===4.5===.", 'both');
                            }

                            $found = $usersnode->find($node, null);

                            if ( !empty($found)) {
                                if (isset($debug)) {
                                    $debug->logmessage("I found the <strong>Course administration > Users >
                                        $node</strong> node ===4.5.1===.", 'both');
                                }
                                if ( !$found->remove()) {
                                    if (isset($debug)) {
                                        $debug->logmessage("Error removing the <strong>Course administration > Users >
                                            $node</strong> node ===4.5.2===.", 'both');
                                    }
                                } else {
                                    if (isset($debug)) {
                                        $debug->logmessage("Successfully removed the <strong>Course administration > Users >
                                            $node</strong> node ===4.5.3===.", 'both');
                                        $debug->logmessage("About to add modified <strong>Course administration > Users >
                                            $node</strong> node ===4.5.5===.", 'both');
                                    }
                                    // Add the new one (no error checking if it didn't work).
                                    $url = new moodle_url('/local/swtc/lib/curriculums.php',
                                        array('curriculumid' => $curriculumid));
                                    $usersnode->add(get_string('enrolledusers', 'enrol'), $url,
                                        navigation_node::TYPE_SETTING, null, 'review', new pix_icon('i/enrolusers', ''));
                                }
                            } else {
                                if (isset($debug)) {
                                    $debug->logmessage("I DIDN'T find the <strong>Course administration > Users >
                                        $node</strong> node ===4.5.3===.", 'both');
                                }
                            }
                            break;

                        case $coursecompletionnode:
                            // Searching for and removing.
                            if (isset($debug)) {
                                $debug->logmessage("Searching for the <strong>Course administration > Reports >
                                    $node</strong> node ===4.6===.", 'both');
                            }

                            // SWTC ********************************************************************************.
                            // Handle case if groupid is empty.
                            // SWTC ********************************************************************************.
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
                            foreach ($children as $child) {
                                // Loop through the key list looking for the $coursecompletion string.
                                if (strpos($child->text, $coursecompletion) !== false) {
                                    if (isset($debug)) {
                                        $messages[] = "I found the <strong>Course administration > Reports >
                                            $node</strong> node ===4.6.1===.";
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
                                    $debug->logmessage("I DIDN'T find the <strong>Course administration > Reports >
                                        $node</strong> node ===4.6.2===.", 'both');
                                }
                            }
                            break;

                        case $coursepartnode:
                            // Searching for and removing.
                            if (isset($debug)) {
                                $debug->logmessage("Searching for the <strong>Course administration > Reports >
                                    $node</strong> node ===4.7===.", 'both');
                            }

                            // SWTC ********************************************************************************.
                            // Handle case if groupid is empty.
                            // Updated to correct the course participation hyperlink (changed "course" to "id").
                            // SWTC ********************************************************************************.
                            if (!empty($groupid)) {
                                $params = array('id' => $course->id, 'group' => $groupid);
                            } else {
                                $params = array('id' => $course->id);
                            }

                            // Remember - replacing link(s) in $coursereportsnode (found above).
                            // debug_navigation($coursereportsnode);
                            // Get all the children of the coursereportsnode.
                            $children = $coursereportsnode->children;
                            $found = null;
                            foreach ($children as $child) {
                                // Loop through the key list looking for the $courseparticipation string.
                                if (strpos($child->text, $courseparticipation) !== false) {
                                    if (isset($debug)) {
                                        $messages[] = "I found the <strong>Course administration > Reports >
                                            $node</strong> node ===4.7.1===.";
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
                                    $debug->logmessage("I DIDN'T find the <strong>Course administration > Reports >
                                        $node</strong> node ===4.7.2===.", 'both');
                                }
                            }
                            break;

                        case $activitycompnode:
                            // Searching for and removing.
                            if (isset($debug)) {
                                $debug->logmessage("Searching for the <strong>Course administration > Reports >
                                    $node</strong> node ===4.8===.", 'both');
                            }

                            // SWTC ********************************************************************************.
                            // Handle case if groupid is empty.
                            // SWTC ********************************************************************************.
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
                            foreach ($children as $child) {
                                // Loop through the key list looking for the $activitycompletion string.
                                if (strpos($child->text, $activitycompletion) !== false) {
                                    if (isset($debug)) {
                                        $messages[] = "I found the <strong>Course administration > Reports >
                                            $node</strong> node ===4.8.1===.";
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
                                    $debug->logmessage("I DIDN'T find the <strong>Course administration > Reports >
                                        $node</strong> node ===4.8.2===.", 'both');
                                }
                            }
                            break;

                        default:
                    }
                }
            }

            // SWTC ********************************************************************************.
            // Skip this for Lenovo-admins and Lenovo-siteadmins.
            // SWTC ********************************************************************************.
            if ((!preg_match(get_string('access_swtc_pregmatch_admin', 'local_swtc'), $useraccesstype))
                && (!preg_match(get_string('access_swtc_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
                foreach ($usersremove as $node) {
                    if (isset($debug)) {
                        $debug->logmessage("Searching for the <strong>Course administration > Users >
                            $node</strong> node ===4.6===.", 'both');
                    }
                    $found = $parentnode->find($node, null);

                    if ( !empty($found)) {
                        if (isset($debug)) {
                            $debug->logmessage("I found the <strong>Course administration > Users >
                                $node</strong> node ===4.6.1===.", 'both');
                        }
                        if ( !$found->remove()) {
                            if (isset($debug)) {
                                $debug->logmessage("Error removing the <strong>Course administration > Users >
                                    $node</strong> node ===4.6.2===.", 'both');
                            }
                        } else {
                            if (isset($debug)) {
                                $debug->logmessage("Successfully removed the <strong>Course administration > Users >
                                    $node</strong> node ===4.6.3===.", 'both');
                            }
                        }
                    } else {
                        if (isset($debug)) {
                            $debug->logmessage("I DIDN'T find the <strong>Course administration > Users >
                                $node</strong> node ===4.6.4===.", 'both');
                        }
                    }
                }
            }
        }
    } else {
        if (isset($debug)) {
            $debug->logmessage("I DIDN'T find the <strong>Course administration >
                Users</strong> node. Continuing ===4.9.9===...", 'both');
        }
    }

    if (isset($debug)) {
        $debug->logmessage("Leaving local_swtc_extend_navigation_course ===4.exit===.", 'both');
    }
}

function local_swtc_extend_navigation_category_settings(navigation_node $parentnode, context_coursecat $context) {
    global $USER;

    // SWTC ********************************************************************************.
    // If we're not logged in, return.
    // SWTC ********************************************************************************.
    if (!isloggedin()) {
        return;
    }

    // SWTC - Debug 10/30/20.
    return;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $swtcuser = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_set_debug();

    // SWTC ********************************************************************************.
    // If debugging, output header information.
    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering local_swtc_extend_navigation_category_settings ===2.enter===.";
        $messages[] = "About to print navigation_node (parentnode) node";
        $messages[] = print_r(array_keys((array)$parentnode), true);
        $messages[] = "Finished printing navigation_node (parentnode) node";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

}
