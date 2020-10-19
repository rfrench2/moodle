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
 * @subpackage swtc/lib/swtclib.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 *
 *****************************************************************************/
defined('MOODLE_INTERNAL') || die();

use stdClass;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');

require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

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
 * 10/14/20 - Initial writing.
 *
 **/
function swtc_loadcatids($roles) {
	global $CFG, $USER, $SESSION;

    // SWTC ********************************************************************************.
    // SWTC LMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    print_object("swtc_loadcatids");       // SWTC-debug
    print_object($SESSION->SWTC);       // SWTC-debug

    // Other Lenovo variables.
    $cats = array();        // A list of all the top-level category information defined (this is returned).
    // SWTC ********************************************************************************.

	if (isset($debug)) {
        // SWTC ********************************************************************************
        // Always output standard header information.
        // SWTC ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_swtclib.php. ===swtc_loadcatids.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
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
    $catids = get_tree(0);				// '0' means just the top-level categories are returned.
    print_object($catids);      // SWTC-debug

	if (isset($debug)) {
        // debug_enable_phplog($debug, "2 - In swtc_loadcatids.");
		$messages[] = "catids array follows:";
        $messages[] = print_r($catids, true);
        $messages[] = "catids array ends.";
		// print_object($catids);
	//	debug_logmessage("roles array follows: <br />", 'detailed');
	//	print_object($roles);
	//	die();
        debug_logmessage($messages, 'detailed');
        unset($messages);
	}

    // SWTC ********************************************************************************
	// Next, load a multi-dimension array for each of the top-level categories (this array will be searched by name for the id below):
    //              'catid'             - the id of the top-level category (returned from the get_tree(0) call above).
    //              'catname'       - the name of the top-level category (ex: "GTP Portfolio").
    //              'context'       - create a context of context_coursecat.
    //              'capability'    - the capability associated with this top-level category (ex: local/swtc:swtc_access_gtp_portfolio).
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
	//					[capability] => local/swtc:swtc_access_gtp_portfolio
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
	// Build the main $cats array (to be passed back to local_swtc_assign_user_role).
    // SWTC ********************************************************************************
	foreach ($catids as $key => $catid) {
		$cats[$key]['catid'] = $catid;
		// $cats[$key]['catname'] = coursecat::get($catid, MUST_EXIST, true)->name;     // Moodle 3.6
        $cats[$key]['catname'] = \core_course_category::get($catid, MUST_EXIST, true)->name;
		$cats[$key]['context'] = \context_coursecat::instance($catid);

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
			// 'One Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // SWTC ********************************************************************************
            case get_string('one_portfolio', 'local_swtc'):
                $cats[$key]['capability']  = get_string('cap_swtc_access_one_portfolio', 'local_swtc');

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == get_string('role_swtc_administrator', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_administrator', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_instructor', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_instructor', 'local_swtc')] = $role->id;
                    }else if ($role->shortname == get_string('role_swtc_student', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_student', 'local_swtc')] = $role->id;
                    }
                }
                break;

            // SWTC ********************************************************************************
			// 'Two Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // SWTC ********************************************************************************
            case get_string('two_portfolio', 'local_swtc'):
                $cats[$key]['capability']  = get_string('cap_swtc_access_two_portfolio', 'local_swtc');

                // Load all the roleids. Remember that ALL roles have access to this portfolio.
                foreach ($roles as $role) {
                    if ($role->shortname == get_string('role_swtc_administrator', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_administrator', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_instructor', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_instructor', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_student', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_student', 'local_swtc')] = $role->id;
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
                    if ($role->shortname == get_string('role_swtc_administrator', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_administrator', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_instructor', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_instructor', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_student', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_student', 'local_swtc')] = $role->id;
                    }
                }
                break;

			// SWTC ********************************************************************************
			// 'Curriculums Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // SWTC ********************************************************************************
            case get_string('curriculums_portfolio', 'local_swtc'):
                $cats[$key]['capability']  = get_string('cap_swtc_access_curriculums_portfolio', 'local_swtc');

                // Load all the roleids. Remember that ALL roles have access to this portfolio.
                foreach ($roles as $role) {
                    if ($role->shortname == get_string('role_swtc_administrator', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_administrator', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_instructor', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_instructor', 'local_swtc')] = $role->id;
                    } else if ($role->shortname == get_string('role_swtc_student', 'local_swtc')) {
                        $cats[$key]['roles'][get_string('role_swtc_student', 'local_swtc')] = $role->id;
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
        debug_logmessage($messages, 'detailed');
        unset($messages);
    //	die();
    }

	return $cats;
}

/**
 * Get current date and time for timestamp. Returns value to set $SESSION->SWTC->USER->timestamp.
 *
 * History:
 *
 * @01 - Added user timezone to improve performance.
 *
 */
function swtc_timestamp() {
    global $CFG, $USER, $SESSION;

    // SWTC ********************************************************************************.
    // SWTC LMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // SWTC ********************************************************************************.

    // SWTC ********************************************************************************
    // Make all the times these variables were set the same.
    // Make all the functions these variables were set the same.
    // SWTC ********************************************************************************
    $today = new DateTime("now", $swtc_user->timezone);
    $time = $today->format('H:i:s.u');

    return $time;

}

/**
 * Determine if user should have access to category. Either a top-level or child category can be sent. If child is sent,
 *          the parent (top-level) category is determined and access to that is returned (not the specific access to the child).
 *      Remember that "navigaion nodes" and "category" are different types.
 *
 * @param catid          category id of category to check.
 *
 * @return bool         true if user should have access; false if not.
 */
 /**
 * Version details
 *
 * History:
 *
 * 06/07/18 - Initial writing.
 * 11/06/18 - Updated the user categories check with the new values.
 *	10/16/19 - Changed to new Lenovo SWTC classes and methods to load swtc_user and debug.
 * 12/19/19 - In swtc_user_access_category, added exception for SelfSupport students (because they do not have access to any top
 *                      level category).
 * @01 - 03/01/20 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
 *                      does not neet to call get() again; changed get() on each category id to core_course_category::make_categories_list
 *                      with the user's main capability; moved core_course_category::make_categories_list higher in the stack for
 *                      better performance; this function will not be needed once we move to core_course_category::can_view_category.
 *
 **/
function swtc_user_access_category($cats, $catid) {
    global $CFG, $USER, $SESSION;

    // SWTC ********************************************************************************.
    // SWTC LMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $user_categoryids = $swtc_user->categoryids;

    // SWTC ********************************************************************************
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again; changed get() on each category id to core_course_category::make_categories_list
    //                  with the user's main capability (category information is cached for 10 minutes by Moodle).
    // $category = coursecat::get($catid, MUST_EXIST, true);        // Moodle 3.6
    // $category = \core_course_category::get($catid, MUST_EXIST, true);
    // SWTC ********************************************************************************
    // $categories = core_course_category::make_categories_list($capability);

    // SWTC ********************************************************************************.

	if (isset($debug)) {
        // SWTC ********************************************************************************
        // Always output standard header information.
        // SWTC ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_swtclib.php. ===swtc_user_access_category.enter.";
        $messages[] = "About to print catid.";
        $messages[] = print_r($catid, true);
        $messages[] = "Finished printing catid. About to print user_categoryids.";
        $messages[] = print_r($user_categoryids, true);
        $messages[] = "Finished printing user_categoryids.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // SWTC ********************************************************************************
    // Get the top-level category for this catid.
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again.
    // $toplevelcat = swtc_toplevel_category($catid);
    // SWTC ********************************************************************************
    // $toplevelcat = swtc_toplevel_category($category);

    // if (isset($debug)) {
    //     $messages[] = print_r("toplevelcat category to search for is :$toplevelcat ===swtc_user_access_category.", true);
    //     debug_logmessage($messages, 'detailed');
    //     unset($messages);
    // }

    // SWTC ********************************************************************************
    // If toplevelcat appears in $swtc_user->categoryids, return true. If not, return false.
    // 12/19/19 - Added exception for SelfSupport students (because they do not have access to any top level category).
    // SWTC ********************************************************************************
    // if (array_search($toplevelcat, $user_categoryids) !== false) {       // 11/08/18
    // if (array_search($toplevelcat, array_column($user_categoryids, 'catid')) !== false) {
    // @01 - Change to array_keys search.
    if (in_array($catid, array_keys($cats))) {
        if (isset($debug)) {
            $messages[] = "category $catid found in user_categoryids. Returning true.===swtc_user_access_category.exit";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        return true;

    } else {
        if (isset($debug)) {
            $messages[] = "category $catid NOT found in user_categoryids. Returning false.===swtc_user_access_category.exit";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        return false;

    }
}

/**
 * Determine the top-level category. Either a top-level or child category can be sent. If child is sent,
 *          the parent (top-level) category is determined.
 *      Remember that "navigaion nodes" and "category" are different types.
 *
 *  Note: Called only from local_swtc_assign_user_role in /local/swtc/lib/locallib.php.
 *
 * @param catid          category id of category to check.
 *
 * @return topcatid         top-level category id.
 */
 /**
 * Version details
 *
 * History:
 *
 * 07/13/18 - Initial writing.
 * @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
 *                      does not neet to call get() again.
 *
 **/
function swtc_toplevel_category($catid) {
    global $CFG, $USER, $SESSION;

	//****************************************************************************************.
    // SWTC LMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // SWTC ********************************************************************************.

    // SWTC ********************************************************************************
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again.
    // $category = coursecat::get($catid, MUST_EXIST, true);        // Moodle 3.6
    // SWTC ********************************************************************************
    $category = \core_course_category::get($catid, MUST_EXIST, true);

    // SWTC ********************************************************************************
    // Get the parents of this category (if any).
    // SWTC ********************************************************************************
    $parents = $category->get_parents();

    if (empty($parents)) {
        // If no parents, the categoryid passed IS a top-level category.
        $toplevelcat = $category->id;
    } else {
        // If it does have parents, the top-level category will be in index 0.
        $toplevelcat = $parents[0];
    }

    return $toplevelcat;
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
	global $DB, $SESSION;
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
 * Saves all the ids and names of all the groups the user has access to in $user->groupnames.
 *
 * $user The $swtc_user variable.
 * $groups The groups array to use to save.
 *
 * @return N/A.
 */
 /**
 * Version details
 *
 * History:
 *
 * 03/11/19 - Initial writing.
 *
 **/
function swtc_save_user_groups($user, $groups) {
    global $CFG, $SESSION;

	//****************************************************************************************
    // Local temporary SWTC variables.
    // $usergroups = new stdClass();
    $usergroups = array();
    // Local variables end...
	//****************************************************************************************

	// SWTC ********************************************************************************
	// Loop through the groups passed in and save the information in the swtc_user->groupnames.
	// SWTC ********************************************************************************
    // print_object($groups);
	foreach ($groups as $group) {
        $usergroups[$group->id]->id = $group->id;
        $usergroups[$group->id]->name = $group->name;
    }

    // Save the groups to swtc_user.
    $user->groupnames = $usergroups;

    return;

}

/**
 * Gets all the ids, and optionally the names, of all the groups the user has access to in $user->groupnames.
 *
 * $user The $swtc_user variable.
 * $option Either:
*           "idsonly": Returns the id's of all the users groups found.
*           "both": Returns the id's, and the names, of all the users groups found.
*           "firstid": Returns the first group id found.
 *
 * @return N/A.
 */
 /**
 * Version details
 *
 * History:
 *
 * 03/10/20 - Initial writing.
 *
 **/
function swtc_get_user_groupnames($user, $option) {
    global $CFG, $SESSION;

	//****************************************************************************************
    // Local temporary SWTC variables.
    // $usergroups = new stdClass();
    $grouplist = array();
    $groupnames = array();
    $usergroups = $user->groupnames;
    $firstid = (stripos($option, 'firstid') !== false) ? 1 : null;      // @01

    // Local variables end...
	//****************************************************************************************

	// SWTC ********************************************************************************
	// Loop through the groups passed in and save the information in the swtc_user->groupnames.
	// SWTC ********************************************************************************
	foreach($usergroups as $key1 => $value1) {
        if (is_array($value1)) {
            foreach($value1 as $key2 => $value2) {
                if (is_array($value2)) {
                    foreach($value2 as $key3 => $value3) {
                        if ($key3 === 'groups') {
                            // $grouplist .= $value3;
                            $temp = explode(', ', $value3);

                            if (isset($firstid)) {      // @01
                                return $temp[0];     // @01
                            }       // @01

                            $grouplist = array_merge($grouplist, $temp);

                            // Loop through $value3 to find the actual group name.
                            foreach ($temp as $groupid) {
                                // $groupnames[] = groups_get_group_name($groupid);     // 03/13/20
                                $groupnames[] = groups_get_group_name($groupid);
                            }
                        }
                    }
                }
            }
        }
    }

    // print_object($grouplist);
    if ($option === 'idsonly') {
        return $grouplist;
    } else {
        return array($groupnames, $grouplist);
    }
}

/**
 * Gets the uuid of the "xxx_menu" group from the user's groupnames data.
 *
 * $user The $swtc_user variable.
 * $option Either:
*           "studs_menu": Returns the uuid of the students menu.
*           "mgrs_menu": Returns the uuid of the manager menu.
*           "admins_menu": Returns the uuid of the admins menu.
*           "geoadmins_menu": Returns the uuid of the students menu.
*           "siteadmins_menu": Returns the uuid of the students menu.
 *
 * @return N/A.
 */
 /**
 * Version details
 *
 * History:
 *
 * @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers; added
 *                      swtc_get_user_groupnames_menuid.
 *
 **/
function swtc_get_user_groupnames_menuid($user, $option) {
    global $CFG, $SESSION;

	//****************************************************************************************
    // Local temporary SWTC variables.
    $key = null;
    $usergroups = $user->groupnames;
    // Local variables end...
	//****************************************************************************************
    // $user_groupnames{$found[0]}{$found[1]}['groups'];
    // $uuid = $usergroups{$option};
    // $uuid2 = key($usergroups{$option});
    // $uuid2 = current($usergroups{$option});
    // $uuid3 = key($usergroups{$option}[0]);
    // 10/13/20 - Array and string offset access syntax with curly braces is deprecated.
    // foreach($usergroups{$option} as $key => $value) {
    foreach($usergroups[$option] as $key => $value) {
        return $key;
    }
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
 * 03/12/19 - Initial writing.
 *
 **/
function swtc_array_find_deep($array, $search, $keys = array())
{
    foreach($array as $key => $value) {
        if (is_array($value)) {
            $sub = swtc_array_find_deep($value, $search, array_merge($keys, array($key)));
            if (count($sub)) {
                return $sub;
            }
        } elseif ($value === $search) {
            return array_merge($keys, array($key));
        }
    }

    return array();
}
