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
 * Lenovo customized code for Moodle activity completion. Remember to add the following at the top of any module that requires these functions:
 *      require_once($CFG->dirroot.'/local/swtc/lib/lenovo_completion_functions.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_completion_functions.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// SWTC customized code for Moodle core completion.
// SWTC ********************************************************************************.
use \local_swtc\traits\lenovo_completionlib;
// use \local_swtc\traits\lenovo_completion_criteria;

// SWTC ********************************************************************************.
// Include Lenovo SWTC user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

/**
 * Course completion progress report
 *
 * Called from: index.php
 *  Location: /report/completion/
 *  To call: this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * @package    report
 * @subpackage completion
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 * 10/14/20 - Initial writing.
 *
 */
 function lenovo_report_completion($completion, $where, $whereparams, $group) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // SWTC.

    // SWTC ********************************************************************************.
    // SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_get_debug();
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering lenovo_completion_functions.php. ===lenovo_report_completion.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************
    // Add PremierSupport roles to where_params.
    // Add additional WHERE condition if $USER role is PremierSupport-manager or PremierSupport-admin.
    // SWTC ********************************************************************************
    if (isset($debug)) {
        $messages[] = "About to print where ==lenovo_report_completion===.\n";
        $messages[] = print_r($where, true);
        // print_object($where);
        $messages[] = "Finished printing where ==lenovo_report_completion===.\n";
        // print_object($parentnode);
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************
    // 11/15/18 - PremierSupport and ServiceDelivery managers and admins have special access.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
    //                          the capabilities for Students are applied in the category context.
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types,
    // using preg_match to search for access types.
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 02/26/19 - Create a customized $grandtotal command for PremierSupport and ServiceDelivery user access types and
    //                      Lenovo-siteadmin and
    //                     Lenovo-admin user access types (so that Lenovo-*** will see all enrolled users).
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the
    //                      other groups to use).
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // SWTC ********************************************************************************
    // SWTC ********************************************************************************.
    // IMPORTANT! The following code assumes the following:
    //          For PS/SD manager access types (ex: PS-US1-manager):
    //                  Should only see enrollments in "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
    //
    //          For PS/SD administrator access types (ex: PS-US1-administrator):
    //                  $groupsmenu[0] will be set to "All PremierSupport US enrollments".
    //
    // SWTC ********************************************************************************.
    // 11/11/19 - In lenovo_set_where_conditions_by_accesstype, added $swtc_user to parameters to function.
    // 12/30/19 - If $where or $whereparams was set when this function was called, preserve them.
    // SWTC ********************************************************************************.
    if ((has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {

        list($where, $where_params, $grandtotal) = lenovo_set_where_conditions_by_accesstype($swtc_user, $completion, $where, $whereparams, $group);

    }

    // SWTC ********************************************************************************.
    // 12/30/19 - If $where or $whereparams was set when this function was called, preserve them.
    // SWTC ********************************************************************************.
    if (!empty($whereparams)) {
        $where_params = array_merge($where_params, $whereparams);
    }

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "Leaving lenovo_completion_functions.php. ===lenovo_report_completion.exit.";
        $messages[] = "About to print where_params.\n";
        $messages[] = print_r($where_params, true);
        // print_object($where_params);
        $messages[] = "Finished printing where_params.\n";
        $messages[] = "About to print where (again).\n";
        // print_object($where);
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).\n";
        $messages[] = "grandtotal is :$grandtotal.\n";
        // print_object($grandtotal);
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $where_params, $grandtotal);
 }

 /**
 * Set additional SQL WHERE conditions based on user acces type.
 *
 * Called from: N/A
 *  Location: /report/completion/index.php
 *  To call: this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * @package    local
 * @subpackage /swtc/lib/lenovo_completion_functions.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 * 10/14/20 - Initial writing.
 *
 */
 function lenovo_set_where_conditions_by_accesstype($swtc_user, $completion, $where_passed, $whereparams, $group) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // SWTC.

    // SWTC ********************************************************************************.
    // SWTC swtc_user and debug variables.
    // 11/11/19 - In lenovo_set_where_conditions_by_accesstype, added $swtc_user to parameters to function.
    // $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $where_params = array();     // 12/30/19

    $useraccesstype = $swtc_user->user_access_type;
    $user_groupname = $swtc_user->groupname;
    $user_geoname = $SESSION->SWTC->USER->geoname;
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Entering lenovo_completion_functions.php ===lenovo_set_where_conditions_by_accesstype.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************
    // 11/15/18 - PremierSupport and ServiceDelivery managers and admins have special access.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
    //                          the capabilities for Students are applied in the category context.
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types,
    // using preg_match to search for access types.
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 02/26/19 - Create a customized $grandtotal command for PremierSupport and ServiceDelivery user access types and
    //                      Lenovo-siteadmin and
    //                     Lenovo-admin user access types (so that Lenovo-*** will see all enrolled users).
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the
    //                      other groups to use).
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // SWTC ********************************************************************************
    // SWTC ********************************************************************************.
    // IMPORTANT! The following code assumes the following:
    //          For PS/SD manager access types (ex: PS-US1-manager):
    //                  Should only see enrollments in "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
    //
    //          For PS/SD administrator access types (ex: PS-US1-administrator):
    //                  $groupsmenu[0] will be set to "All PremierSupport US enrollments".
    //
    // SWTC ********************************************************************************.
    // 10/22/19 - IMPORTANT! Must have the has_capability checks BEFORE calling this function.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
    // SWTC ********************************************************************************.
    if ((preg_match($access_ps_mgr, $useraccesstype)) || (preg_match($access_ps_admin, $useraccesstype)) || (preg_match($access_ps_geoadmin, $useraccesstype)) || (preg_match($access_ps_siteadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_mgr, $useraccesstype)) || (preg_match($access_lenovo_sd_admin, $useraccesstype)) || (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_siteadmin, $useraccesstype))) {
        // One common where clause.
        // @02 - 05/11/20 - If debug is enabled, list all users including any testing users (%test%) in case cohort has not
        //                              been populated with actual users.
        if (isset($debug)) {
            $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.suspended != 1) AND (u.deleted != 1)";
        } else {
            $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
        }
        // SWTC ********************************************************************************.
        // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
        // SWTC ********************************************************************************.
        if (!empty($where_passed)) {
            $where = array_merge($where, $where_passed);
        }

        // SWTC ********************************************************************************.
        // PremierSupport site administrators
        // SWTC ********************************************************************************.
        if (preg_match($access_ps_siteadmin, $useraccesstype)) {
            $where_params['accesstype1'] = "%PremierSupport-%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // PremierSupport GEO administrators
        // SWTC ********************************************************************************.
        } else if (preg_match($access_ps_geoadmin, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%PremierSupport-" .$user_geoname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // PremierSupport administrators
        // SWTC ********************************************************************************.
        } else if (preg_match($access_ps_admin, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%PremierSupport-" .$user_groupname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // PremierSupport managers
        // SWTC ********************************************************************************.
        } else if (preg_match($access_ps_mgr, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%PremierSupport-" .$user_groupname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // ServiceDelivery site administrators
        // SWTC ********************************************************************************.
        } else if (preg_match($access_lenovo_sd_siteadmin, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%ServiceDelivery-%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // ServiceDelivery GEO administrators
        // SWTC ********************************************************************************.
        } else if (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            // print_object("in index.php - in else if : group is :$group.\n");     // 03/12/19
            $where_params['accesstype1'] = "%ServiceDelivery-" .$user_geoname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            // print_object("%ServiceDelivery-" .$user_geoname. "%");
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
            // print_object("in /report/completion/index.php. grandtotal is :$grandtotal\n");
        // SWTC ********************************************************************************.
        // ServiceDelivery administrators
        // SWTC ********************************************************************************.
        } else if (preg_match($access_lenovo_sd_admin, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%ServiceDelivery-" .$user_groupname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        // SWTC ********************************************************************************.
        // ServiceDelivery managers
        // SWTC ********************************************************************************.
        } else if (preg_match($access_lenovo_sd_mgr, $useraccesstype)) {
            // $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
            $where_params['accesstype1'] = "%ServiceDelivery-" .$user_groupname. "%";
            // SWTC ********************************************************************************.
            // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $where_params = array_merge($where_params, $whereparams);
            }
            $grandtotal = $completion->lenovo_get_num_tracked_users(implode(' AND ', $where), $where_params, $group);
        }
    // SWTC ********************************************************************************.
    // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // SWTC ********************************************************************************.
    } else {
        $where = array();
        // $grandtotal = $completion->lenovo_get_num_tracked_users('', array(), $group);        // 12/30/19
        // SWTC ********************************************************************************.
        // 12/30/19 - If $where_passed or $whereparams was set when this function was called, preserve them.
        // SWTC ********************************************************************************.
        if (!empty($where_passed)) {
            $where = array_merge($where, $where_passed);
        }

        if (!empty($whereparams)) {
            $where_params = array_merge($where_params, $whereparams);
        }

        $grandtotal = $completion->lenovo_get_num_tracked_users('', $where_params, $group);           // 12/30/19
        // $where_params = array();     // 12/30/19
    }

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Leaving lenovo_completion_functions.php ===lenovo_set_where_conditions_by_accesstype.exit===";
        $messages[] = "About to print where_params.";
        $messages[] = print_r($where_params, true);
        // print_object($where_params);
        $messages[] = "Finished printing where_params.";
        $messages[] = "About to print where (again).";
        // print_object($where);
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).";
        $messages[] = "grandtotal is :$grandtotal.";
        // print_object($grandtotal);
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $where_params, $grandtotal);
 }

 /**
 * Get enrolled users based on user acces type.
 *
 * Called from: lenovo_get_num_tracked_users
 *  Location: /local/swtc/classes/traits/lenovo_completionlib.php
 *  To call: this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * @package    local
 * @subpackage /swtc/lib/lenovo_completion_functions.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 * 11/15/18 - Added check for accessreports flag instead of user_access_type (if the user should have access to reporting features).
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 11/29/18 - Removed test users from appearing in reports (users with firstname like %test%); also removed suspended and deleted
 *                          users from appearing.
 * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
 *                      for Students are applied in the category context.
 * 12/20/18 - Added hyperlinks and tooltips to the column headers.
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
 *                      to use this new value.
 * 02/26/19 - Create a customized $grandtotal command for PremierSupport and ServiceDelivery user access types and
 *                      Lenovo-siteadmin and
 *                      Lenovo-admin user access types (so that Lenovo-*** will see all enrolled users).
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
 *                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the other groups
 *                      to use).
 * 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
 * 03/20/19 - Changing tooltips hyperlink from viewing course to viewing individual course completion report; changing way course
 *                      completion data is presented if course completion is based on other courses being complete.
 * 06/04/19 - In all reports, to work around Moodle keeping ifirst and ilast in user_preferences (and causing confusion as to the actual
 *                      of users that should be shown), skip err_nousers code in all reports (so that the initals_first and
 *                      initials_last controls are always shown).
 *	10/17/19 - Changed to new Lenovo SWTC classes and methods to load swtc_user and debug.
 * 10/22/19 - Initial writing; added this function.
 * 10/24/19 - For Moodle 3.7+, changing all Lenovo capability strings from dashes (ebg-access-gtp-portfolio) to
 *                  underscores (ebg_access_gtp_portfolio).
 * 11/11/19 - In lenovo_get_enrolled_users_by_accesstype, added $swtc_user as parameter to function.
 * 12/30/19 - In lenovo_get_enrolled_users_by_accesstype, add $whereparams parameter; if $whereparams was set when this
 *                      function was called, pass it along and preserve it.
 * @01 - 03/21/20 - In lenovo_get_enrolled_users_by_accesstype, added global flags for PS / SD management flag
 *                          (if user is manager or above) for easier access checking.
 *
 */
 function lenovo_get_enrolled_users_by_accesstype($swtc_user, $whereparams, $groupid) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // SWTC.

    // SWTC ********************************************************************************.
    // SWTC SWTC swtc_user and debug variables.
    // 11/11/19 - In lenovo_get_enrolled_users_by_accesstype, added $swtc_user as parameter to function.
    // $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $useraccesstype = $swtc_user->user_access_type;
    $groups = null;
    $user_groupnames = $SESSION->SWTC->USER->groupnames;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $psmanagement = isset($swtc_user->psmanagement) ? $swtc_user->psmanagement : null;      // @01
    $sdmanagement = isset($swtc_user->sdmanagement) ? $swtc_user->sdmanagement : null;      // @01
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Entering lenovo_completion_functions.php ===lenovo_set_where_conditions_on_accesstype.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************
    // 11/15/18 - PremierSupport and ServiceDelivery managers and admins have special access.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
    //                          the capabilities for Students are applied in the category context.
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types,
    // using preg_match to search for access types.
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 02/26/19 - Create a customized $grandtotal command for PremierSupport and ServiceDelivery user access types and
    //                      Lenovo-siteadmin and
    //                     Lenovo-admin user access types (so that Lenovo-*** will see all enrolled users).
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the
    //                      other groups to use).
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // SWTC ********************************************************************************
    // SWTC ********************************************************************************.
    // IMPORTANT! The following code assumes the following:
    //          For PS/SD manager access types (ex: PS-US1-manager):
    //                  Should only see enrollments in "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
    //
    //          For PS/SD administrator access types (ex: PS-US1-administrator):
    //                  $groupsmenu[0] will be set to "All PremierSupport US enrollments".
    //
    // SWTC ********************************************************************************.
    // 10/22/19 - IMPORTANT! Must have the has_capability checks BEFORE calling this function.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
    // SWTC ********************************************************************************.
    // Loop through $user_groupnames looking for the "virtual" group (if set).
    // print_object($user_groupnames);      // 10/23/19
    // print_object($swtc);      // 10/23/19
    if (!empty($user_groupnames)) {
        // Remember that an array will be located that looks like the following:
        //      Array
        //      (
        //          [0] => mgrs_menu
        //          [1] => 168690638
        //          [2] => uuid
        //      )
        $found = swtc_array_find_deep($user_groupnames, $groupid);
        if (!empty($found)) {
            // print_object("About to print found ==/lib/completionlib.php:get_num_tracked_users===.\n");
            // print_object($found);
            // print_object($user_groupnames{$found[0]});
            // print_object($user_groupnames{$found[0]}{$found[1]});
            // print_object($user_groupnames{$found[0]}{$found[1]}['groups']);
            // print_object($user_groupnames{$found[0]}{$found[1]}{'groups'});
            // print_object($user_groupnames[$found[1]]);
            // print_object($found[0]);
            // print_object($found[0][1]);
            // 03/12/19 - Send to sql as an array
            // $groups = $user_groupnames{$found[0]}{$found[1]}{'groups'};  This works also.
            // 10/13/20 - Array and string offset access syntax with curly braces is deprecated; changed {} to []
            // $groups = $user_groupnames{$found[0]}{$found[1]}['groups'];
            $groups = $user_groupnames[$found[0]][$found[1]]['groups'];
            $groups = explode(', ', $groups);

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print groups.\n";
                $messages[] = print_r($groups, true);
                // print_object("==/lib/completionlib.php:get_num_tracked_users=== - groups follows :\n");
                // print_object($groups);
                $messages[] = "Finished printing groups.\n";
                $debug->logmessage($messages, 'both');
                unset($messages);
            }
        }
        // SWTC ********************************************************************************.
    }

    if (isset($psmanagement)) {     // @01
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
        context_course::instance($COURSE->id), 'local/swtc:ebg_access_premiersupport_portfolio', $groupid, true);
    } else if (isset($sdmanagement)) {      // @01
        $groupid = isset($groups) ? $groups : $groupid;
        // print_object("in completionlib.php - groupid is :");
        // print_object($groupid);
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
            context_course::instance($COURSE->id), 'local/swtc:ebg_access_servicedelivery_portfolio', $groupid, true);
    }  else {
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                context_course::instance($COURSE->id), 'moodle/course:isincompletionreports', $groupid, true);
    }

    // SWTC ********************************************************************************.
    // 12/30/19 - If $whereparams was set when this function was called, preserve it.
    // SWTC ********************************************************************************.
    $params = array_merge($enrolledparams, $whereparams);

    // SWTC ********************************************************************************
    if (isset($debug)) {
        $messages[] = "Leaving lenovo_completion_functions.php ===lenovo_get_num_tracked_users.exit===";
        $messages[] = "enrolledsql is :$enrolledsql. ==get_num_tracked_users.exit===.\n";
        $messages[] = "params follows :.\n";
        $messages[] = print_r($params, true);
        // print_object("==/lib/completionlib.php:get_num_tracked_users===.\n");
        // print_object($enrolledsql);
        // print_object($params);
        $debug->logmessage($messages, 'detailed');
        unset($messages);
    }
    // SWTC ********************************************************************************

    return array($enrolledsql, $params);
 }
