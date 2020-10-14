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
 * Lenovo customized code for Moodle participation. Remember to add the following at the top of any module that requires these functions:
 *      require_once($CFG->dirroot.'/local/swtc/lib/swtc_participation_functions.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc_participation_functions.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	10/24/19 - Initial writing; moved majority of customized code from /report/participation/index.php to functions defined here;
 *                      added utility functions; changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * PTR2020Q108 - @01 - 05/11/20 - If debug is enabled, list all users including any testing users (%test%) in case cohort has not
 *                  been populated with actual users.
 *
 **/

defined('MOODLE_INTERNAL') || die();


// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

/**
 * Course participation report
 *
 * Called from: index.php
 *  Location: /report/participation/
 *  To call: this_function_name
 *
 * @package    report
 * @subpackage participation
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 * 12/03/18 - Added this header; Remember that the capabilities for Managers and Administrators are applied in the system context;
 *						the capabilities for Students are applied in the category context.
 * 12/19/18 - Removed test users from appearing in reports (users with firstname like %test%);
 *							also removed suspended and deleted users from appearing.
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
 *                      to use this new value.
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
 *                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the other groups
 *                      to use).
 * 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
 *	10/17/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *	10/24/19 - Moved majority of Lenovo customized code from /report/participation/index.php to here (including history).
 * @01 - 05/11/20 - If debug is enabled, list all users including any testing users (%test%) in case cohort has not
 *                  been populated with actual users.
 *
 */
 function lenovo_report_participation($roleid) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // Lenovo.

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

	$ebgwhere = null;
    $ebgsort = null;
	// Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************.
        // Always output standard header information.
        // Lenovo ********************************************************************************.
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_participation_functions.php. ===lenovo_report_participation.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************
    // 11/15/18 - If user is PremierSupport or ServiceDelivery manager or admin, only list courses in appropriate portfolio.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
    //                      for Students are applied in the category context (using $systemcontext that is already defined).
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types,
    // using preg_match to search for access types.
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the other groups
    //                      to use).
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // Lenovo ********************************************************************************
    if ((has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {
        // print_object($roleid);      // 11/19/19 - Lenovo debugging...
        list($where, $where_params) = lenovo_set_where_conditions_by_roleid($roleid);

    }

    // Lenovo ********************************************************************************
    if (isset($debug)) {
        $messages[] = "Leaving swtc_participation_functions.php. ===lenovo_report_participation.exit.";
        $messages[] = "About to print where_params.\n";
        $messages[] = print_r($where_params, true);
        // print_object($where_params);
        $messages[] = "Finished printing where_params.\n";
        $messages[] = "About to print where (again).\n";
        // print_object($where);
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).\n";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $where_params);
 }

 /**
 * Set additional SQL WHERE conditions based on roleid.
 *
 * Called from: lenovo_report_participation
 *  Location: /local/swtc/lib/swtc_participation_functions.php
 *  To call: this_function_name
 *
 *      Note: $roles is set here, but not passed back which is the same in current production code. In other words, not sure why it is set here.
 *                  It may be a copy of some code from another function.
 *
 * @package    local
 * @subpackage /swtc/lib/swtc_participation_functions.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 *	10/17/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/24/19 - Initial writing; added this function.
 *
 */
 function lenovo_set_where_conditions_by_roleid($roleid) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // Lenovo.

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $where_params = '';
    $useraccesstype = $swtc_user->user_access_type;
    $user_groupname = $swtc_user->groupname;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
    $access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
    $access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
    $access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

    $access_lenovo_sd_mgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;
    $access_lenovo_sd_admin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
    $access_lenovo_sd_geoadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin;
    $access_lenovo_sd_siteadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin;

    $roleids = $SESSION->EBGLMS->ROLEIDS;

    // Remember - PremierSupport managers and admins have special access. First, manager role.
    $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_manager;
    $roleid_ps_mgr = $roleids->$shortname;

    // Next, administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_administrator;
    $roleid_ps_admin = $roleids->$shortname;

    // Next, GEO administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_geoadministrator;
    $roleid_ps_geoadmin = $roleids->$shortname;

    // Next, site administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_siteadministrator;
    $roleid_ps_siteadmin = $roleids->$shortname;

    // Finally, student role.
    $shortname = $SESSION->EBGLMS->STRINGS->premiersupport->role_premiersupport_student;
    $roleid_ps_stud = $roleids->$shortname;

    // ServiceDelivery managers and admins have special access. First, manager role.
    $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_manager;
    $roleid_sd_mgr = $roleids->$shortname;

    // Next, administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_administrator;
    $roleid_sd_admin = $roleids->$shortname;

    // Next, GEO administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_geoadministrator;
    $roleid_sd_geoadmin = $roleids->$shortname;

    // Next, site administrator role.
    $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_siteadministrator;
    $roleid_sd_siteadmin = $roleids->$shortname;

    // Finally, student role.
    $shortname = $SESSION->EBGLMS->STRINGS->servicedelivery->role_servicedelivery_student;
    $roleid_sd_stud = $roleids->$shortname;
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************.
        // Always output standard header information.
        // Lenovo ********************************************************************************.
        $messages[] = "Entering swtc_participation_functions.php ===lenovo_set_where_conditions_by_roleid.enter===";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************
    // 11/15/18 - If user is PremierSupport or ServiceDelivery manager or admin, only list courses in appropriate portfolio.
    // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
    //                      for Students are applied in the category context (using $systemcontext that is already defined).
    // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
    //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
    //							PS / SD student user types.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types,
    // using preg_match to search for access types.
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
    //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the other groups
    //                      to use).
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // Lenovo ********************************************************************************
    if ((preg_match($access_ps_mgr, $useraccesstype)) || (preg_match($access_ps_admin, $useraccesstype)) || (preg_match($access_ps_geoadmin, $useraccesstype)) || (preg_match($access_ps_siteadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_mgr, $useraccesstype)) || (preg_match($access_lenovo_sd_admin, $useraccesstype)) || (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_siteadmin, $useraccesstype))) {
        // 12/20/18 - Customized WHERE to skip listing test users, etc. Taken from /report/completion/index.php.
        // @01 - 05/11/20 - If debug is enabled, list all users including any testing users (%test%) in case cohort has not
        //                              been populated with actual users.
        if (isset($debug)) {
            $where = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.suspended != 1) AND (u.deleted != 1)";
        } else {
            $where = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1)) AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
        }
        // Add the following to $params:
        //		$params['accesstype1'] = "%Premier%";

        // IMPORTANT! If roleid is passed to this method, respect it (i.e. don't overwrite it).

        if ($roleid == 0) {
            // Lenovo ********************************************************************************.
            // PremierSupport access type.
            // Lenovo ********************************************************************************.
            if ((preg_match($access_ps_siteadmin, $useraccesstype)) || (preg_match($access_ps_geoadmin, $useraccesstype)) || (preg_match($access_ps_admin, $useraccesstype)) || (preg_match($access_ps_mgr, $useraccesstype))) {
                //****************************************************************************************.
                // Common settings for all PremierSupport access types.
                //****************************************************************************************.
                $roleid = $roleid_ps_mgr;       // 03/17/19 - Not sure of all the roles using "manager".

               // Lenovo ********************************************************************************.
                // PremierSupport site administrators
                // Lenovo ********************************************************************************.
                if (preg_match($access_ps_siteadmin, $useraccesstype)) {
                    $roles = 'IN (' . $roleid_ps_siteadmin . ',' . $roleid_ps_geoadmin . ',' . $roleid_ps_admin . ',' . $roleid_ps_mgr . ',' . $roleid_ps_stud . ')';
                    $where_params = 'PS-';
                // Lenovo ********************************************************************************.
                // PremierSupport GEO administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_geoadmin, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_ps_geoadmin . ',' . $roleid_ps_admin . ',' . $roleid_ps_mgr . ',' . $roleid_ps_stud . ')';
                    $where_params = 'PS-' . $user_groupname;
                // Lenovo ********************************************************************************.
                // PremierSupport administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_admin, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_ps_admin . ',' . $roleid_ps_mgr . ',' . $roleid_ps_stud . ')';
                    $where_params = 'PS-' . $user_groupname;
                // Lenovo ********************************************************************************.
                // PremierSupport managers
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_mgr, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_ps_mgr . ',' . $roleid_ps_stud . ')';
                    $where_params = 'PS-' . $user_groupname;
                }
            // Lenovo ********************************************************************************
            // ServiceDelivery access type.
            // Lenovo ********************************************************************************
            } else if ((preg_match($access_lenovo_sd_siteadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) || (preg_match($access_lenovo_sd_admin, $useraccesstype)) || (preg_match($access_lenovo_sd_mgr, $useraccesstype))) {
                //****************************************************************************************.
                // Common settings for all ServiceDelivery access types.
                //****************************************************************************************.
                $roleid = $roleid_sd_mgr;       // 03/17/19 - Not sure of all the roles using "manager".

                // Lenovo ********************************************************************************.
                // ServiceDelivery site administrators
                // Lenovo ********************************************************************************.
                if (preg_match($access_lenovo_sd_siteadmin, $useraccesstype)) {
                    $roles = 'IN (' . $roleid_sd_siteadmin . ',' . $roleid_sd_geoadmin . ',' . $roleid_sd_admin . ',' . $roleid_sd_mgr . ',' . $roleid_sd_stud . ')';
                    $where_params = 'SD-';
                // Lenovo ********************************************************************************.
                // ServiceDelivery GEO administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_geoadmin, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_sd_geoadmin . ',' . $roleid_sd_admin . ',' . $roleid_sd_mgr . ',' . $roleid_sd_stud . ')';
                    $where_params = 'SD-' . $user_groupname;
                // Lenovo ********************************************************************************.
                // ServiceDelivery administrators
                // Lenovo ********************************************************************************.
                }else if (preg_match($access_lenovo_sd_admin, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_sd_admin . ',' . $roleid_sd_mgr . ',' . $roleid_sd_stud . ')';
                    $where_params = 'SD-' . $user_groupname;
                // Lenovo ********************************************************************************.
                // ServiceDelivery managers
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_mgr, $useraccesstype)) {
                    $roles = 'IN ('. $roleid_sd_mgr . ',' . $roleid_sd_stud . ')';
                    $where_params = 'SD-' . $user_groupname;
                }
            } else {
                // $roles = '= 0';		// 12/19/18 - Not sure...
            }
        }
    // Lenovo ********************************************************************************.
    // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // Lenovo ********************************************************************************.
    } else {
        // $roles = '= 0';		// 12/19/18 - Not sure...
    }



    if (isset($debug)) {
        // Lenovo ********************************************************************************.
        // Always output standard header information.
        // Lenovo ********************************************************************************.
        $messages[] = "Leaving swtc_participation_functions.php ===lenovo_set_where_conditions_by_roleid.exit===";
        $messages[] = "About to print where_params.";
        $messages[] = print_r($where_params, true);
        // print_object($where_params);
        $messages[] = "Finished printing where_params.";
        $messages[] = "About to print where (again).";
        // print_object($where);
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $where_params);
 }
