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
 *
 * All functions associcated with $SESSION->EBGLMS->USER (otherwise known as $swtc_user).
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/swtc_userlib.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 11/01/19 - Initial writing; moved some functions to here from other /local/swtc modules.
 * 11/12/19 - IMPORTANT! If event is user_enrolment_created, the userid might be in either $eventdata->userid or
 *                      $eventdata->relateduser (see details in swtc_get_user below).
 * 11/13/19 - In swtc_get_user, if the user's session has expired, SESSION, and therefore SESSION->EBGLMS->USER,
 *                      will not be set. Therefore, we need to check for this condition.
 * 11/15/19 - In swtc_get_user and swtc_get_user_details, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * PTR2019Q402 - @01 - 03/01/20 - Added user timezone to improve performance; moved swtc_get_debug function from debuglib.php
 *                      to swtc_userlib.php to improve performance; in swtc_get_debug, added call to include debuglib.php (so that all other
 *                      modules do not have to).
 * PTR2019Q401 - @02 - 03/12/20 - In swtc_get_user_details, added global flags for PS / SD management flag
 *                          (if user is manager or above) for easier access checking.
 * PTR2020Q109 - @03 - 05/06/20 - Added field for user profile field "Accesstype2".
 *
 *****************************************************************************/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require($CFG->dirroot.'/local/swtc/lib/swtc.php');                              // All EBGLMS global information.
// require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');        // @01


/**
 * Setup most, but not all, the characteristics of  SESSION->EBGLMS->USER.
 *
 *          Note: Most of the time this is called using $USER (a class) which would have the property $USER->id available. However, in some
 *                  situations (for example, in groups_get_all_groups in /lib/grouplib.php when called from the /enrol/autoenrol plugin), it is called
 *                  using the userid of the user. In other words, just an integer value and NOT an object class.
 *
 * @param $user
 *
 * @return None
 */
 /**
 * Version details
 *
 * History:
 *
 * 07/12/18 - Initial writing.
 * 08/27/18 - Added check if the user has a PremierSupport access type. If so, set the users pscohortname variable.
 * 11/15/18 - Added check if the user should have access to reporting features. If so, set accessreports flag; added check if the user has a
 *                  ServiceDelivery access type. If so, set the users sdcohortname variable.
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be done anywhere that
 *						needs it.
 * 08/21/19 - If swtc_get_user is called during login (for example, if auto enroll is being used), $USER will NOT be set
 *                      (because login has not completed yet) and can NOT require login (because we're in the middle of it).
 *                      So, if $USER is not set, return.
 * 10/08/19 - Removing "Notice: Undefined property: stdClass::$profile in /var/www/html/local/swtc/lib/swtclib.php on line 777" error by
 *                  correctly loading the user's profile data.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/31/19 - Added correct setting of swtc_user information; added special processing if $USER is not set yet
 *                  (for instance, in the /enrol/autoenrol plugin).
 * 11/12/19 - IMPORTANT! In lib.php (in /enrol/autoenrol), enrol_user (in /lib/enrollib.php) is called. In enrol_user, a
 *                      user_enrolment_created event is created. If it is a new enrollment, the userid of the user will be placed in
 *                      $event->relateduserid. If it is an existing enrollment, the userid will be placed in $eventdata->userid. So, both
 *                      situations must be taken into account. Therefore, any functions called in /local/swtc/classes/observer.php must be
 *                      changed to handle this situation (currently only local_swtc_assign_user_role in /local/swtc/lib/locallib.php).
 *                      Likewise, swtc_get_user must be changed to check for the presence of $relateduserid.
 * 11/13/19 - In swtc_get_user, if the user's session has expired, SESSION->EBGLMS->USER will not be set. Therefore, we need
 *                      to check for this condition.
 * 11/15/19 - In swtc_get_user and swtc_get_user_details, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * @01 - 03/01/20 - Added user timezone to improve performance.
 * @03 - 05/06/20 - Added field for user profile field "Accesstype2".
 *
 **/
function swtc_get_user($user, $relateduserid = null) {
    global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Local variables begin...
    $swtc_user = new stdClass();     // Local temporary USER variables.
    $temp = new stdClass();
    // print_object(format_backtrace(debug_backtrace(), true));        // 11/12/19 - Lenovo debugging...
    // print_object("about to print user");
    // print_object($user);
    // print_object("about to print USER");
    // print_object($USER);
    // die;
    // Lenovo ********************************************************************************.

    // Lenovo ********************************************************************************
    // Access to the top-level $EBGLMS global variables (it should ALWAYS be available; set in /lib/swtc.php).
    //      To use: if (isset($SESSION->EBGLMS))
    // 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
    //                      if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
    //                      user's session has expired.
    // Lenovo ********************************************************************************
    if (isset($SESSION->EBGLMS->USER)) {
        $swtc_user = $SESSION->EBGLMS->USER;

        // Lenovo ********************************************************************************.
        // This function is called by one of three ways to set $user (the parameter to the function):
        //      $SESSION->EBGLMS->USER - under normal circumstances, when this function is called, this should always
        //                      be set. However, it still might not have actual values set yet (including userid).
        //      $USER - under normal circumstances, when this function is called, this should always be set. However, if the login
        //                      for the user has not fully completed (like when called during autoenrol), it will not have actual values set
        //                      yet (including userid).
        //      $userid - only called from groups_get_all_groups (in /lib/grouplib.php); $userid of the actual user; it should be set when
        //                      this function is called.
        //
        // Lenovo ********************************************************************************.
        // If $swtc_user->id is NOT set (i.e. does NOT have a valid userid set), attempt to find one in either $user or
        //                  $relateduserid (in that order).
        // Lenovo ********************************************************************************.
        if (!isset($swtc_user->userid)) {

            // Lenovo ********************************************************************************.
            // Set some of the EBGLMS variables that will be used.
            //
            // Sets variables:
            //          $swtc_user->userid                                    The userid of the user.
            //			$swtc_user->username								The username of the user.
            //          $swtc_user->timestamp                            The time when any value in any field was changed
            //          $swtc_user->user_access_type                  The type of the user.
            //          $swtc_user->user_access_type2
            // Lenovo ********************************************************************************.
            // Lenovo ********************************************************************************.
            // See if $user is an object. If so, it would be $USER and $USER->id is available.
            //          If not, use $user as the userid of the user.
            // Lenovo ********************************************************************************.

            // Lenovo ********************************************************************************.
            // First check if $user->id is set. If so, load it into $swtc_user->userid.
            // Sample from /lib/enrollib.php: $user1 = isset($user1->id) ? $user1->id : $user1;
            //                                                  if (empty($user1) {
            //                                                      return;
            //                                                  }
            // Lenovo ********************************************************************************.
            // print_object("before: about to print swtc_user->userid");     // 11/12/19 - Lenovo debugging...
            // print_object($swtc_user->userid);     // 11/12/19 - Lenovo debugging...
            // print_object("before: about to print user");     // 11/12/19 - Lenovo debugging...
            // print_object($user);     // 11/12/19 - Lenovo debugging...
            // print_object("before: about to print relateduserid");     // 11/12/19 - Lenovo debugging...
            // print_object($relateduserid);     // 11/12/19 - Lenovo debugging...

            $swtc_user->userid = isset($user->id) ? $user->id : $user;

            // print_object("between: about to print swtc_user->userid");     // 11/12/19 - Lenovo debugging...
            // print_object($swtc_user->userid);     // 11/12/19 - Lenovo debugging...
            // print_object("between: about to print user");     // 11/12/19 - Lenovo debugging...
            // print_object($user);     // 11/12/19 - Lenovo debugging...
            // print_object("between: about to print relateduserid");     // 11/12/19 - Lenovo debugging...
            // print_object($relateduserid);     // 11/12/19 - Lenovo debugging...

            // Lenovo ********************************************************************************.
            // If $swtc_user->userid is still not set, see if $relateduserid is set (if so, it IS the userid).
            // Lenovo ********************************************************************************.
            $swtc_user->userid = empty($swtc_user->userid) ? $relateduserid : $swtc_user->userid;

            // print_object("after: about to print swtc_user->userid");     // 11/12/19 - Lenovo debugging...
            // print_object($swtc_user->userid);     // 11/12/19 - Lenovo debugging...
            // print_object("after: about to print user");     // 11/12/19 - Lenovo debugging...
            // print_object($user);     // 11/12/19 - Lenovo debugging...
            // print_object("after: about to print relateduserid");     // 11/12/19 - Lenovo debugging...
            // print_object($relateduserid);     // 11/12/19 - Lenovo debugging...
            // return;

            // Lenovo ********************************************************************************.
            // Since we have a valid userid, fill in some information.
            // Lenovo ********************************************************************************.
            if (!empty($swtc_user->userid)) {
                // Lenovo ********************************************************************************.
                // Get the username.
                // Lenovo ********************************************************************************.
                $temp = core_user::get_user($swtc_user->userid);
                $swtc_user->username = $temp->username;
                // $swtc_user->timestamp = swtc_set_user_timestamp();
                // @01 - 03/01/20 - Added user timezone to improve performance.
                list($swtc_user->timestamp, $swtc_user->timezone) = swtc_set_user_timestamp();

                // Lenovo ********************************************************************************.
                // 10/08/19 - Removing "Notice: Undefined property: stdClass::$profile in /var/www/html/local/swtc/lib/swtclib.php
                //                      on line 777" error by correctly loading the user's profile data.
                // Lenovo ********************************************************************************.
                $temp->id = $swtc_user->userid;
                profile_load_data($temp);
                // $swtc_user->user_access_type = $user->profile['Accesstype'];
                $swtc_user->user_access_type = $temp->profile_field_accesstype;

                // @03 - 05/06/20 - Added field for user profile field "Accesstype2".
                $swtc_user->user_access_type2 = isset($temp->profile_field_accesstype2) ? $temp->profile_field_accesstype2 : null;

                // Lenovo ********************************************************************************
                // 08/27/18 - Added check if the user has a PremierSupport access type. If so, set the users pscohortname variable.
                // 11/15/18 - Added check if the user has a ServiceDelivery access type. If so, set the users sdcohortname variable.
                // 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be
                //						done anywhere that needs it.
                //      Notes:
                //              This check must also be put outside of swtc_get_user as cohort membership might change while user is logged in.
                //              Cannot use /cohort/lib/cohort_get_user_cohorts since all our cohorts are invisible (visible = 0).
                // Lenovo ********************************************************************************
                swtc_get_user_details();

            } else {
                // Lenovo ********************************************************************************.
                // If $swtc_user->userid is still not set by now, there is nothing more we can do. Return $swtc_user.
                // Lenovo ********************************************************************************.
            }
        } else {
            // Lenovo ********************************************************************************.
            // If $swtc_user->id is NOT zero (i.e. does have a valid userid set), just return what is set in $SESSION->EBGLMS->USER.
            // Lenovo ********************************************************************************.
        }
    } else {
        // TODO: Catastrophic error; what to do.
    }

    return $swtc_user;

}

/**
 * If the user has a PremierSupport or ServiceDelivery access type, set the appropriate variable. Also sets accessreports
 *      if the user has a manager / administrator user type.
 *
 * None
 *
 * @return $SESSION->EBGLMS->USER->cohortnames set (or NULL).
 */
 /**
 * Version details
 *
 * History:
 *
 * 08/27/18 - Initial writing.
 * 11/15/18 - Added check if the user has a ServiceDelivery access type. If so, set the users sdcohortname variable; added setting
 *                          of accessreports and specialaccess.
 * 11/27/18 - Fixed check of Accesstype (can't use === since we have so many types now); added ALL the PremierSupport and
 *                          ServiceDelivery access strings.
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be done anywhere that
 *						needs it.
 * 01/17/19 - In swtc_get_user_details, for checking access to top level category, replaced checking of multiple access types with switch
 *						statement to multiple stripos checks (similar to local_swtc_change_user_access in locallib.php).
 * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
 * 01/29/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5).
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/08/19 - Added geoname to keep the main GEO for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the GEO is EMEA); added PS/SD GEO site administrator user access types.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 05/09/19 - In swtc_get_user_details, added additional code for Lenovo-admins and Lenovo-siteadmins.
 * 11/15/19 - In swtc_get_user and swtc_get_user_details, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * @02 - 03/12/20 - In swtc_get_user_details, added global flags for PS / SD management flag (if user is manager or above)
 *                  for easier access checking.
 *
 **/
function swtc_get_user_details() {
    global $CFG, $DB, $USER, $SESSION;

	//****************************************************************************************.
    // Lenovo EBGLMS debug variables.
    $debug = swtc_get_debug();

	// Other Lenovo variables.
	$swtc_user = new stdClass();

    // The following pattern will match "<whatever>-US1-<whatever> or "<whatever>-EM5-<whatever>".
    // 03/03/19 - have to have two strings to compare - one for strings with the GEO number in it (-US1-) and one
    //                      for across all GEOs (-US-).
    // 03/14/19 - No need for siteadmin string (they will match all GEOs and all groups).
    // $cmp_site_admins = '/siteadmin/';
    $cmp_geo_admins = '/-([A-Z][A-Z])-/';
    $cmp_allotherroles = '/-([A-Z][A-Z]+[1-9])-/';

	// Lenovo ********************************************************************************
    // Access to the top-level $EBGLMS global variables (it should ALWAYS be available; set in /lib/swtc.php).
    //      To use: if (isset($SESSION->EBGLMS))
    // 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
    //                      if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
    //                      user's session has expired.
    // Lenovo ********************************************************************************
    if (isset($SESSION->EBGLMS->USER)) {
        // require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');
		// Set all the EBGLMS variables that will be used.
        $swtc_user = $SESSION->EBGLMS->USER;
		$user_access_type = $SESSION->EBGLMS->USER->user_access_type;

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

        $access_lenovoadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
        $access_lenovositeadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
    }

    // Lenovo ********************************************************************************
    // 08/27/18 - Added check if the user has a PremierSupport access type. If so, set the users pscohortname variable.
    // 11/15/18 - Added check if the user has a ServiceDelivery access type. If so, set the users sdcohortname variable.
	// 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be
	//							done anywhere that needs it.
    //      Notes:
    //              This check must also be put outside of swtc_get_user as cohort membership might change while user is logged in.
    //              Cannot use /cohort/lib/cohort_get_user_cohorts since all our cohorts are invisible (visible = 0).
    // Lenovo ********************************************************************************

	// Lenovo ********************************************************************************
	// PremierSupport access type.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/08/19 - Added PS/SD GEO site administrator user access types.
	// Lenovo ********************************************************************************
	if ((preg_match($access_ps_stud, $user_access_type)) || (preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_ps_geoadmin, $user_access_type)) || (preg_match($access_ps_siteadmin, $user_access_type))) {
		// Get cohort the user is a member of.
		$cohorts = array();
		$sql = 'SELECT c.*
			FROM {cohort} c
			JOIN {cohort_members} cm ON (c.id = cm.cohortid)
			WHERE (cm.userid = ?) AND (c.visible = 0)';
		$cohorts = $DB->get_records_sql($sql, array($swtc_user->userid));
		foreach($cohorts as $cohort) {
			$swtc_user->cohortnames .= $cohort->name . ' ';
		}

	// Lenovo ********************************************************************************
	// ServiceDelivery access type.
    // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
    // 03/03/19 - Added PS/AD site administrator user access types.
    // 03/06/19 - Added PS/SD GEO administrator user access types.
    // 03/08/19 - Added PS/SD GEO site administrator user access types.
	// Lenovo ********************************************************************************
	} else if ((preg_match($access_lenovo_sd_stud, $user_access_type)) || (preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type)) || (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) || (preg_match($access_lenovo_sd_siteadmin, $user_access_type))) {
		// Get cohort the user is a member of.
		$cohorts = array();
		$sql = 'SELECT c.*
			FROM {cohort} c
			JOIN {cohort_members} cm ON (c.id = cm.cohortid)
			WHERE (cm.userid = ?) AND (c.visible = 0)';
		$cohorts = $DB->get_records_sql($sql, array($swtc_user->userid));
		foreach($cohorts as $cohort) {
			$swtc_user->cohortnames .= $cohort->name . ' ';
		}

	// Lenovo ********************************************************************************
	// Keep cohortnames as NULL.
	// Lenovo ********************************************************************************
	} else {
		$swtc_user->cohortnames = null;
	}

    // Lenovo ********************************************************************************
    // 01/29/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
    //                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5).
    // 03/08/19 - Added geoname to keep the main GEO for user based on access type (ex. if access type is
    //                      Lenovo-ServiceDelivery-EMEA5-mgr, the GEO is EMEA).
    // 05/09/19 - Additional code for Lenovo-admins and Lenovo-siteadmins.
    // @02 - In swtc_get_user_details, added global flags for PS / SD management flag (if user is manager or above)
    //                  for easier access checking.
    // Lenovo ********************************************************************************
    // Lenovo ********************************************************************************.
    // Due to reporting, some user types (managers and administrators) require the groupname (ex: "US5") and some
    //              access types (geoadministrator) require only the GEO name (ex: "US"),
    //              and some (siteadministrator) don't require either the groupname nor the GEO name (only use "%" to match
    //              all enrollments).
    // Lenovo ********************************************************************************.
    // print_object($swtc_user);
    if (preg_match($access_ps_siteadmin, $user_access_type)) {
        $swtc_user->groupname = '%';
        $swtc_user->geoname = '%';
        $swtc_user->psmanagement = true;
    } else if (preg_match($access_lenovo_sd_siteadmin, $user_access_type)) {
        $swtc_user->groupname = '%';
         $swtc_user->geoname = '%';
         $swtc_user->sdmanagement = true;
    } else if (preg_match($access_ps_geoadmin, $user_access_type)) {
        $swtc_user->psmanagement = true;
        // $cmp_geo_admins = '/-([A-Z][A-Z])-/';
        $result = preg_match($cmp_geo_admins, $user_access_type, $match);

        // print_object($result);
        // print_object($match);
        if (!empty($match)) {
            $swtc_user->groupname = $match[1];
            $swtc_user->geoname = $swtc_user->groupname;
        }
    } else if (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) {
        $swtc_user->sdmanagement = true;
        // $cmp_geo_admins = '/-([A-Z][A-Z])-/';
        $result = preg_match($cmp_geo_admins, $user_access_type, $match);

        // print_object($result);
        // print_object($match);
        if (!empty($match)) {
            $swtc_user->groupname = $match[1];
            $swtc_user->geoname = $swtc_user->groupname;
        }
    // Lenovo ********************************************************************************.
    // All other PS / SD roles.
    // Lenovo ********************************************************************************.
    } else if ((preg_match($access_ps_stud, $user_access_type)) || (preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_lenovo_sd_stud, $user_access_type)) || (preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type))) {
        // $cmp_allotherroles = '/-([A-Z][A-Z]+[1-9])-/';
        $result = preg_match($cmp_allotherroles, $user_access_type, $match);

        if (isset($debug)) {
            $messages[] = "About to print result.";
            $messages[] = print_r($result, true);
            $messages[] = "Finished printing result. About to print match.";
            $messages[] = print_r($match, true);
            // print_object($result);
            // print_object($match);
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        if (!empty($match)) {
            $swtc_user->groupname = $match[1];
            $swtc_user->geoname = substr($swtc_user->groupname, 0, -1);
        }

        // Lenovo ********************************************************************************.
        // @02 - In swtc_get_user_details, added global flags for PS / SD management flag (if user is manager or above)
        //                  for easier access checking.
        // Lenovo ********************************************************************************.
        if ((preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type))) {
            $swtc_user->psmanagement = true;
        } else if ((preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type))) {
            $swtc_user->sdmanagement = true;
        }
    // Lenovo ********************************************************************************.
    // 05/09/19 - Additional code for Lenovo-admins and Lenovo-siteadmins.
    // Lenovo ********************************************************************************.
    } else if ((preg_match($access_lenovoadmin, $user_access_type)) || (preg_match($access_lenovositeadmin, $user_access_type))) {
        $swtc_user->groupname = '%';
        $swtc_user->geoname = '%';
    }

    return;

}

/**
 * Get current date and time for timestamp. Returns value to set $SESSION->EBGLMS->USER->timestamp.
 *
 * History:
 *
 * @01 - 03/01/20 - Added user timezone to improve performance.
 *
 */
function swtc_set_user_timestamp() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Make all the times these variables were set the same.
    // Make all the functions these variables were set the same.
    // Lenovo ********************************************************************************
    // @01 - 03/01/20 - Added user timezone to improve performance.
    $timezone = core_date::get_user_timezone_object();

    $today = new DateTime("now", $timezone);
    $time = $today->format('H:i:s.u');

    return array($time, $timezone);

}



/**
 * Setup most, but not all, the characteristics of  SESSION->EBGLMS->USER->relateduser.
 *
 * @param N/A
 *
 * @return None
 */
 /**
 * Version details
 *
 * History:
 *
 * 07/12/18 - Initial writing.
 * 11/30/18 - Changed swtc_get_relateduser to load the portfolio of the user instead of "PORTFOLIO_NONE".
 * 01/09/19 - Added correct return from swtc_get_relateduser.
 * 01/10/19 - Changed swtc_get_relateduser to NOT set $SESSION->EBGLMS->USER->relateduser; the calling function must do this.
 * 01/11/19 - Added additional comments, and some code formating, to swtc_get_relateduser.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * @01 - 03/01/20 - Added user timezone to improve performance.
 *
 **/
function swtc_user_get_relateduser($userid) {
    global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    $relateduser = new stdClass();     // Local temporary relateduserid variables.
    // Lenovo ********************************************************************************.

	// Lenovo ********************************************************************************
	// Set some of the EBGLMS->relateduser variables that will be used IF a relateduserid is found.
	// Lenovo ********************************************************************************
	// Get all the user information based on the userid passed in.
	// Note: '*' returns all fields (normally not needed).
	$relateduser = core_user::get_user($userid);
	profile_load_data($relateduser);

	// Lenovo ********************************************************************************
	// Since we are using get_user and profile_load_data, there is no need to copy any other fields.
	// Lenovo ********************************************************************************
	// $relateduser->username = $relateduser->username;

	// Lenovo ********************************************************************************
	// The following fields MUST be added to $relateduser (as they normally do not exist).
	// Lenovo ********************************************************************************
	$relateduser->userid = $userid;
	$relateduser->user_access_type = $relateduser->profile_field_accesstype;
	// $relateduser->portfolio = 'PORTFOLIO_NONE';      // 11/30/18 - RF - not sure if this is correct.
	// 01/17/19 - Since we are working with a related user, assigning the portfolio as the same as the administrator is not a good idea.
	$relateduser->portfolio = $swtc_user->portfolio;      // 11/30/18

    // @01 - 03/01/20 - Added user timezone to improve performance.
    list($relateduser->timestamp, $relateduser->timezone) = swtc_set_user_timestamp();

	// Important! roleshortname and roleid are what the roles SHOULD be, not necessarily what the roles are.
	$relateduser->roleshortname = null;
	$relateduser->roleid = null;

	$relateduser->categoryids = array();

	// Important! capabilities are what the capabilities SHOULD be, not necessarily what the capabilities are.
	$relateduser->capabilities = array();
	$relateduser->timestamp = swtc_timestamp();

	// print_object($relateduser);

	// Last step. Note that this sets $SESSION->EBGLMS->USER->relateduser.
	// $swtc_user->relateduser = $relateduser;		// 01/10/19

	// print_object($relateduser);
	return $relateduser;		// 01/10/19

}

/**
 * Lenovo EBGLMS for Moodle 3.7+.  Get debug instance (returns $debug) if set. If not set, call debug_start.
 *
 * History:
 *
 * 07/17/18 - Check for server name. If running on production, disable debugging.
 * 11/02/19 - In preparation for Moodle 3.7+, in swtc_get_debug, added code to check for Lenovo debug setting so that everyone can
 *                      call swtc_get_debug directly.
 * 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * @01 - 03/01/20 - Moving swtc_get_debug function from debuglib.php to swtc_userlib.php to improve performance; added call to include
 *                   debuglib.php (so that all other modules do not have to).
 *
 */
function swtc_get_debug() {
    global $CFG, $USER, $SESSION;

    //****************************************************************************************
	// Local variables begin...
    $debug = null;
    $servername_production = 'https://lenovoedu.lenovo.com';
    $swtc_user = new stdClass();

    // Lenovo ********************************************************************************
    // Access to the top-level $EBGLMS global variables (it should ALWAYS be available; set in /lib/swtc.php).
    //      To use: if (isset($SESSION->EBGLMS))
    // 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
    //                      if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
    //                      user's session has expired.
    // Lenovo ********************************************************************************
    if (isset($SESSION->EBGLMS->USER)) {
        require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');
        // Set all the EBGLMS variables that will be used.
        $swtc_user = $SESSION->EBGLMS->USER;
    } else {
        // TODO: Catastrophic error; what to do with $swtc_user?
    }
    // Local variables end...
	//****************************************************************************************

    // Lenovo ********************************************************************************
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    //      (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    if (get_config('local_swtc', 'swtcdebug')) {
        // Lenovo ********************************************************************************.
        // @01 - 03/01/20 - Added call to include debuglib.php (so that all other modules do not have to).
        // Lenovo ********************************************************************************.
        require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');

        // Lenovo ********************************************************************************
        // Setup the second-level $DEBUG global variable.
        //      To use: $debug = $SESSION->EBGLMS->DEBUG;
        // Lenovo ********************************************************************************
        if (!isset($SESSION->EBGLMS->DEBUG)) {
            // $backtrace = format_backtrace(debug_backtrace(), true);
            // print_r("swtc_get_debug: SESSION->EBGLMS->DEBUG ->NOT<- set. Called from ".debug_backtrace()[1]['function'].".<br />");
            // var_dump($backtrace);
            // die;
            $debug = debug_start();       // EBGLMS->DEBUG is not set yet.
        } else {
            // EBGLMS->DEBUG is set. Check if running on production.
            // $SESSION->EBGLMS->DEBUG->PHPLOG->backtrace = format_backtrace(debug_backtrace(), true);
            // print_r("swtc_get_debug: SESSION->EBGLMS->DEBUG =IS= set. Called from ".debug_backtrace()[1]['function'].".<br />");
            // var_dump($SESSION->EBGLMS->DEBUG);
            // die;
            $debug = $SESSION->EBGLMS->DEBUG;
        }
    } else {
        $debug = null;
    }

    return $debug;
}
