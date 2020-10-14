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
 *
 * All Lenovo customized functions associcated with Moodle /mod/assign/locallib.php. Remember to add the following at the top of any
 *          module that requires these functions:
 *              require_once($CFG->dirroot . '/local/swtc/lib/swtc_mod_assign_locallib.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/swtc_mod_assign_locallib.php
 * @copyright  2012 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	PTR2019Q401 - @01 - 03/16/20 - Initial writing; added swtc_filter_list_participants and swtc_count_sumissions_with_status.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// @01 - Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');


/**
 * (change)Look for any of the Lenovo shared resources (defined in /lib/swtc_resources.php) and any courses that have been moved to archive
 *          in the array passed. Returns "true" if any of the courses are found; "false" if none of the courses are found.
 *
 *
 *
 * @param array The list of the users to filter. Note: if an object is passed in, it will be converted to a array.
 * @param option    The user types, either PS ('PremierSupport') or SD ('ServiceDelivery'), to filter on.
 *
 * @return array    Filtered array of users.
 *
 * History:
 *
 * @01 - 03/16/20 - Original version.
 *
 */
function swtc_filter_list_participants($users, $option) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $returnusers = array();
    $test_users = 'test';
    $userprofile = new stdClass();
    $useraccesstype = null;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $access_ps_stud = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_stud;
    $access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
    $access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
    $access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
    $access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

    $access_lenovo_sd_stud = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_stud;
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
        $messages[] = "Entering /local/swtc/lib/swtc_mod_assign_locallib.php===swtc_filter_list_participants.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "About to print users.";
        $messages[] = print_r((array)$users, true);
        $messages[] = "Finished printing users.";
        // print_object($users);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Loop through all the users.
    foreach ($users as $user) {
        // Get the user's access type.
        // @01 - Change "id" to "userid" due to a restriction when swtc_filter_list_participants is called from another function.
        $userprofile = local_swtc_get_user_profile($user->userid);
        $useraccesstype = $userprofile->profile_field_accesstype;
        // print_object($useraccesstype);
        // print_object($user->firstname);
        // Lenovo ********************************************************************************.
        // Search for testing users; remove if found.
        // Lenovo ********************************************************************************.
        if (stripos($user->firstname, $test_users) !== false) {
            if (isset($debug)) {
                debug_logmessage("Found a testing user. Removing.", 'both');
            }

        // Lenovo ********************************************************************************.
        // Search for suspended users; remove if found.
        // Lenovo ********************************************************************************
        } else if ($user->suspended == 1) {
            if (isset($debug)) {
                debug_logmessage("Found a suspended user. Removing.", 'both');
            }

        // Lenovo ********************************************************************************.
        // Search for deleted users; remove if found.
        // Lenovo ********************************************************************************
        } else if ($user->deleted == 1) {
            if (isset($debug)) {
                debug_logmessage("Found a deleted user. Removing.", 'both');
            }

        // Lenovo ********************************************************************************.
        // Search for only the PremierSupport users; add if found.
        // Lenovo ********************************************************************************
        } else if ($option === 'PremierSupport') {
            if ((preg_match($access_ps_stud, $useraccesstype)) || (preg_match($access_ps_mgr, $useraccesstype)) || (preg_match($access_ps_admin, $useraccesstype)) || (preg_match($access_ps_geoadmin, $useraccesstype)) || (preg_match($access_ps_siteadmin, $useraccesstype))) {
                if (isset($debug)) {
                    debug_logmessage("Found a PremierSupport user. Adding.", 'both');
                }

                $returnusers[$user->id] = $user;
            }

        // Lenovo ********************************************************************************.
        // Search for only the ServiceDelivery users; add if found.
        // Lenovo ********************************************************************************
        } else if ($option === 'ServiceDelivery') {
            if ((preg_match($access_lenovo_sd_stud, $useraccesstype)) || (preg_match($access_lenovo_sd_mgr, $useraccesstype)) || (preg_match($access_lenovo_sd_admin, $useraccesstype)) || (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_siteadmin, $useraccesstype))) {
                if (isset($debug)) {
                    debug_logmessage("Found a ServiceDelivery user. Adding.", 'both');
                }

                $returnusers[$user->id] = $user;
            }
        }
    }

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving /local/swtc/lib/swtc_mod_assign_locallib.php===swtc_filter_list_participants.exit.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "returnusers array follows :";
        $messages[] = print_r($returnusers, true);
        $messages[] = "returnusers array ends.";
        // print_object($newcourses);
        // var_dump("old count was " . count($courses));
        // var_dump("new count is " . count($newcourses));
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Remember to return newcourses.
    return $returnusers;
}

/**
 * Load a count of submissions with a specified status.
 *
 * Called from: count_submissions_with_status
 *  Location: /mod/locallib.php
 *  To call: this_function_name
 *
 * @param mixed $currentgroup int|null the group for counting (if null the function will determine it)
 * @return $enrolledsql, $params
 *
 * Lenovo history:
 *
 * @01- 03/21/20 - Initial writing; in each function where 'mod/assign:submit' is used, added check for PS/SD manager
 *                   or admin user types.
 *
 *
 */
function swtc_count_submissions_with_status($groupid = 0) {
    global $DB, $CFG, $SESSION, $USER, $COURSE;       // Lenovo.

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $assigncap = null;
    $user_access_type = $swtc_user->user_access_type;
    $groups = null;
    $user_groupnames = $SESSION->EBGLMS->USER->groupnames;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    if (isset($swtc_user->psmanagement)) {
        $assigncap = $SESSION->EBGLMS->STRINGS->capabilities->cap_ebg_mod_assign_submit_premiersupport;
    } else if (isset($swtc_user->sdmanagement)) {
        $assigncap = $SESSION->EBGLMS->STRINGS->capabilities->cap_ebg_mod_assign_submit_servicedelivery;
    }

    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************.
        // Always output standard header information.
        // Lenovo ********************************************************************************.
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering /local/swtc/lib/swtc_mod_assign_locallib.php. ===lenovo_count_submissions_with_status.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************
    // 07/25/18 - Added capability to get_enrolled_sql call if user is PremierSupport or ServiceDelivery managers and admins.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
    //                          the capabilities for Students are applied in the category context.
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
    //                          to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the
    //                      other groups to use).
    // Lenovo ********************************************************************************
    // print_object("In swtc_count_submissions_with_status. About to print user_groupnames:");       // @01
    // print_object($user_groupnames);     // @01
    // Loop through $user_groupnames looking for the "virtual" group (if set).
    if (!empty($user_groupnames)) {
        // Remember that an array will be located that looks like the following:
        //      Array
        //      (
        //          [0] => mgrs_menu
        //          [1] => 168690638
        //          [2] => uuid
        //      )
        if (empty($groupid)) {
            // $groupid = swtc_get_user_groupnames($swtc_user, 'firstid');        // @01
            $groupid = swtc_get_user_groupnames_menuid($swtc_user, 'studs_menu');        // @01
        }

        $found = swtc_array_find_deep($user_groupnames, $groupid);
        if (!empty($found)) {
            $groups = $user_groupnames{$found[0]}{$found[1]}['groups'];
            $groups = explode(', ', $groups);

            // Lenovo ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print groups ==lenovo_count_submissions_with_status===.\n";
                $messages[] = print_r($groups, true);
                // print_object("==/lib/completionlib.php:get_tracked_users=== - groups follows :\n");
                // print_object($groups);      // @01
                $messages[] = "Finished printing groups ==lenovo_count_submissions_with_status===.\n";
                debug_logmessage($messages, 'both');
                unset($messages);
            }
        }
        // Lenovo ********************************************************************************.
    }

    if (isset($swtc_user->psmanagement)) {
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $params) = get_enrolled_sql(
        context_course::instance($COURSE->id), $assigncap, $groupid, true);
    } else if (isset($swtc_user->sdmanagement)) {
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $params) = get_enrolled_sql(
            context_course::instance($COURSE->id), $assigncap, $groupid, true);
    }

    // Lenovo ********************************************************************************
    if (isset($debug)) {
        $messages[] = "enrolledsql is :$enrolledsql. ==get_tracked_users===.\n";
        $messages[] = "params follows :.\n";
        $messages[] = print_r($params, true);
        // print_object("==/lib/completionlib.php:get_tracked_users===.\n");
        // print_object($enrolledsql);
        // print_object($params);
        debug_logmessage($messages, 'detailed');
        unset($messages);
    }
    // Lenovo ********************************************************************************

    return array($enrolledsql, $params);

}
