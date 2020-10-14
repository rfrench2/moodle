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
 * All Lenovo customized functions associcated with Moodle /lib/grouplib.php. Remember to add the following at the top of any
 *          module that requires these functions:
 *              require_once($CFG->dirroot . '/local/swtc/lib/swtc_lib_grouplib.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/swtc_lib_grouplib.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 12/27/18 - Added this header; changed groups_get_all_groups to check for PremierSupport or ServiceDelivery managers or administrators.
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
 * 01/30/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5); remove 'allparticipants' from all menus if
 *                      user type is PremierSupport or ServiceDelivery.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
 *                      to use this new value.
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added call to customized menu function for PS/AD site administrator user access types; added PS/AD GEO administrator
 *                          user access types.
 * 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
 * 05/09/19 - In groups_get_all_groups, added additional debug statements for every return statement; added additional
 *                          code for Lenovo-admins and Lenovo-siteadmins.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/23/19 - In groups_print_course_menu and groups_print_activity_menu, added important note that it is only to be called for PS/SD
 *                      administrator user types and above (i.e. NOT for PS/SD students or managers).
 * 10/31/19 - Added correct setting of swtc_user information.
 *	11/01/19 - Initial writing; moved majority of customized code from /lib/grouplib.php to functions defined here (including history).
 * PTR2020Q109 - @01 - 05/12/20 - Changed $ebgsort to preg_match string.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');


/**
 * Gets array of all groups in a specified course (subject to the conditions imposed by the other arguments).
 *
 * @category group
 * @param int $courseid The id of the course.
 * @param int|int[] $userid optional user id or array of ids, returns only groups continaing one or more of those users.
 * @param int $groupingid optional returns only groups in the specified grouping.
 * @param string $fields defaults to g.*. This allows you to vary which fields are returned.
 *      If $groupingid is specified, the groupings_groups table will be available with alias gg.
 *      If $userid is specified, the groups_members table will be available as gm.
 * @param bool $withmembers if true return an extra field members (int[]) which is the list of userids that
 *      are members of each group. For this to work, g.id (or g.*) must be included in $fields.
 *      In this case, the final results will always be an array indexed by group id.
 * @return array returns an array of the group objects (unless you have done something very weird
 *      with the $fields option).
 *
 * Lenovo history:
 *
 * 12/27/18 - Added module History section (this section); added check for PremierSupport and ServiceDelivery check; Due to
 *						problems with contexts and user access, removing has_capability checking and rolling back to checking Accesstype for
 *						PremierSupport and ServiceDelivery user types. Remember that PremierSupport groups begin with "PS-" and
 *						ServiceDelivery groups begin with "SD-".
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
 * 01/30/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5).
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
 *                      to use this new value.
 * 02/28/19 - Adding additional PremierSupport and ServiceDelivery strings for "all GEO participants".
 * 03/01/19 - To allow grading, without receiving notifications, the PS/SD manager or administrator is only put in the "manager" or
 *                          "administrator" group, NOT the "studs" group. Therefore, we must use both "manager" AND "studs" groups when
 *                          searching.
 * 03/03/19 - Added PS/SD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
 * 05/09/19 - Added additional debug statements for every return statement; added additional code for Lenovo-admins and Lenovo-siteadmins.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/31/19 - Added correct setting of swtc_user information.
 * @01 - 05/12/20 - Changed $ebgsort to preg_match string.
 *
 */
function swtc_set_where_conditions_by_groupname() {
    global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $ebgwhere = null;
    $ebgsort = null;

    $user_access_type = $swtc_user->user_access_type;
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

    $access_lenovoadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
    $access_lenovositeadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
  // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering /lib/grouplib.php.groups_get_all_groups.enter===.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************
	// 12/27/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
	//							ServiceDelivery manager and administrator user types.
	//		IMPORTANT! WHERE condition assumes the correct naming of PremierSupport and ServiceDelivery
	//						group names. If the naming convention changes, so should this code.
	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
    // 01/30/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
    //                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5).
    // 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
    //                      to use this new value.
    // 02/28/19 - Adding additional PremierSupport and ServiceDelivery strings for "all GEO participants".
    // 03/01/19 - To allow grading, without receiving notifications, the PS/SD manager or administrator is only put in the "manager" or
    //                      "administrator" group, NOT the "studs" group. Therefore, we must use both "manager" AND "studs" groups when
    //                      searching.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/19/19 - For customized PS/SD code, added exceptions for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
    // 05/09/19 - Added additional debug statements for every return statement; added additional code for Lenovo-admins and Lenovo-siteadmins.
	// Lenovo ********************************************************************************.
    // Lenovo ********************************************************************************.
    // IMPORTANT! The following code assumes the following:
    //          For PS/SD manager access types (ex: PS-US1-manager):
    //                  Site cohorts: The user account has been added to both PS-US1-mgrs (for grading AND reporting)
    //                                              AND PS-US1-studs (for notifications).
    //                  In the Separate groups pull-down menu:
    //                          The initial group shown will be "PS-US1-mgrs-enrollments".
    //                          My groups will list "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
    //                          No other groups will be listed.
    //
    //          For PS/SD administrator access types (ex: PS-US1-administrator):
    //                  Site cohorts: The user account has been added to only PS-US1-admins (for grading AND reporting).
    //                  In the Separate groups pull-down menu:
    //                          The initial group shown will be "PS-US1-admins-enrollments".
    //                          My groups will list "PS-US1-admins-enrollments".
    //                          All other "PS-US" groups will be listed.
    //                          The "All PremierSupport US enrollments" group will be listed first (see groups_print_course_menu).
    //
    // Lenovo ********************************************************************************.

	// if (has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) {
    if (has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance()) && isset($user_access_type)) {
        // Lenovo ********************************************************************************.
        // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
        // Lenovo ********************************************************************************.
        if ((preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_ps_geoadmin, $user_access_type)) || (preg_match($access_ps_siteadmin, $user_access_type)) || (preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type)) || (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) || (preg_match($access_lenovo_sd_siteadmin, $user_access_type))) {
            // Lenovo ********************************************************************************.
            // PremierSupport site administrators
            // Lenovo ********************************************************************************.
            if (preg_match($access_ps_siteadmin, $user_access_type)) {
                // Use PS so that site administrators can view all enrollments.
                // $ebgwhere = " AND ((g.name LIKE 'PS-" .$user_groupname. "-studs%') OR (g.name LIKE 'PS-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'PS-" .$user_groupname. "-admins%') OR (g.name LIKE 'PS-" .$user_groupname. "-geoadmins%') OR (g.name LIKE 'PS-" .$user_groupname. "-siteadmins%'))";
                $ebgwhere = " AND ((g.name LIKE 'PS-" .$user_groupname. "'))";
                $ebgsort = '/PS-/i';
            // Lenovo ********************************************************************************.
            // PremierSupport GEO administrators
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_ps_geoadmin, $user_access_type)) {
                // Use user_groupname (ex: US) so that managers can view only their GEO enrollments.
                $ebgwhere = " AND ((g.name LIKE 'PS-" .$user_groupname. "-studs%') OR (g.name LIKE 'PS-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'PS-" .$user_groupname. "-admins%') OR (g.name LIKE 'PS-" .$user_groupname. "-geoadmins%'))";
                $ebgsort = '/PS-' . $user_groupname . '/i';
            // Lenovo ********************************************************************************.
            // PremierSupport administrators
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_ps_admin, $user_access_type)) {
                $ebgwhere = " AND ((g.name LIKE 'PS-" .$user_groupname. "-studs%') OR (g.name LIKE 'PS-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'PS-" .$user_groupname. "-admins%'))";
                // Use user_groupname (ex: US1) so that managers can view only their GEO enrollments.
                $ebgsort = '/PS-' . $user_groupname . '/i';
            // Lenovo ********************************************************************************.
            // PremierSupport managers
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_ps_mgr, $user_access_type)) {
                $ebgwhere = " AND ((g.name LIKE 'PS-" .$user_groupname. "-studs%') OR (g.name LIKE 'PS-" .$user_groupname. "-mgrs%'))";
                // Use user_groupname (ex: US1) so that managers can view only their GEO enrollments.
                $ebgsort = '/PS-' . $user_groupname . '/i';
            // Lenovo ********************************************************************************.
            // ServiceDelivery site administrators
            // @01 - 05/06/20 - Changed $ebgsort to preg_match string.
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_lenovo_sd_siteadmin, $user_access_type)) {
                // $ebgwhere = " AND ((g.name LIKE 'SD-" .$user_groupname. "-studs%') OR (g.name LIKE 'SD-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'SD-" .$user_groupname. "-admins%') OR (g.name LIKE 'SD-" .$user_groupname. "-geoadmins%') OR (g.name LIKE 'SD-" .$user_groupname. "-siteadmins%'))";
                $ebgwhere = " AND ((g.name LIKE 'SD%-" .$user_groupname. "'))";
                $ebgsort = '/SD(TAM)?-/i';
            // Lenovo ********************************************************************************.
            // ServiceDelivery GEO administrators
            // @01 - 05/06/20 - Changed $ebgsort to preg_match string.
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) {
                // Use user_groupname (ex: US) so that managers can view only their GEO enrollments.
                $ebgwhere = " AND ((g.name LIKE 'SD%-" .$user_groupname. "-studs%') OR (g.name LIKE 'SD%-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'SD%-" .$user_groupname. "-admins%') OR (g.name LIKE 'SD%-" .$user_groupname. "-geoadmins%'))";
                $ebgsort = '/SD(TAM)?-' . $user_groupname . '/i';
            // Lenovo ********************************************************************************.
            // ServiceDelivery administrators
            // @01 - 05/06/20 - Changed $ebgsort to preg_match string.
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_lenovo_sd_admin, $user_access_type)) {
                $ebgwhere = " AND ((g.name LIKE 'SD%-" .$user_groupname. "-studs%') OR (g.name LIKE 'SD%-" .$user_groupname. "-mgrs%') OR (g.name LIKE 'SD%-" .$user_groupname. "-admins%'))";
                // Use user_groupname (ex: US1) so that managers can view only their GEO enrollments.
                $ebgsort = '/SD(TAM)?-' . $user_groupname . '/i';
            // Lenovo ********************************************************************************.
            // ServiceDelivery managers
            // @01 - 05/06/20 - Changed $ebgsort to preg_match string.
            // Lenovo ********************************************************************************.
            } else if (preg_match($access_lenovo_sd_mgr, $user_access_type)) {
                $ebgwhere = " AND ((g.name LIKE 'SD%-" .$user_groupname. "-studs%') OR (g.name LIKE 'SD%-" .$user_groupname. "-mgrs%'))";
                // Use user_groupname (ex: US1) so that managers can view only their GEO enrollments.
                $ebgsort = '/SD(TAM)?-' . $user_groupname . '/i';
            }
        // Lenovo ********************************************************************************.
        // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
        // Lenovo ********************************************************************************.
        } else if ((preg_match($access_lenovoadmin, $user_access_type)) || (preg_match($access_lenovositeadmin, $user_access_type))) {
            $ebgwhere = " AND ((g.name LIKE '" .$user_groupname. "'))";
            $ebgsort = '';
        }
	}

    return array($ebgwhere, $ebgsort);

}
