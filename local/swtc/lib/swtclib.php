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
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 04/19/18 - Initial writing; finished adding new $SESSION->EBGLMS global variable and all its required changes; added
 *                          swtc_loadcatids.
 * 05/03/18 - Changed the "require_once($CFG->dirroot.'/local/swtc/lib/swtc_debug.php');" to be dependent on the setting
 *                          of a local variable that must be named $debug: "$debug = new stdClass();" = debugging on;
 *                          null = debugging off (since we are using isset() for the check).
 * 06/03/18 - Added check for new swtcdebug setting.
 * 07/12/18 - Added swtc_set_user and swtc_set_relateduser functions.
 * 08/27/18 - Added check if the user has a PremierSupport access type. If so, set the users pscohortname variable.
 * 11/06/18 - Updated the user categories check with the new values.
 * 11/15/18 - Added check if the user should have access to reporting features. If so, set accessreports flag; added check if the user has a
 *                  ServiceDelivery access type. If so, set the users sdcohortname variable.
 * 11/20/18 - Changed access type names for ServiceDelivery.
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 11/30/18 - Changed swtc_get_relateduser to load the portfolio of the user instead of "PORTFOLIO_NONE".
 * 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be done anywhere that
 *						needs it.
 * 01/09/19 - Added correct return from swtc_get_relateduser.
 * 01/10/19 - Changed swtc_get_relateduser to NOT set $SESSION->EBGLMS->USER->relateduser; the calling function must do this.
 * 01/10/19 - Added Curriculums Portfolio.
 * 01/11/19 - Added additional comments, and some code formating, to swtc_get_relateduser.
 * 01/17/19 - In swtc_get_user_details, for checking access to top level category, replaced checking of multiple access types with switch
 *						statement to multiple stripos checks (similar to local_swtc_change_user_access in locallib.php).
 *  01/24/19 - In swtc_get_user_details, due to the updated PremierSupport and ServiceDelivery user access types,
 *						using preg_match to search for access types.
 * 02/07/19 - Moved get_tree from locallib.php to here.
 * 03/03/19 - In swtc_get_user_details, added PS/AD site administrator user access types.
 * 03/06/19 - In swtc_get_user_details, added PS/SD GEO administrator user access types; added function swtc_groups_sort_menu_options.
 * 03/08/19 - In swtc_get_user_details and swtc_groups_sort_menu_options, added PS/SD GEO site administrator user access types.
 * 03/09/19 - Changed all strings containing "EMEA" (4-letters) to "EM" (2-letters) so that the preg_match strings will match.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 05/09/19 - In swtc_get_user_details, added additional code for Lenovo-admins and Lenovo-siteadmins.
 * 08/21/19 - If swtc_get_user is called during login (for example, if auto enroll is being used), $USER will NOT be set
 *                      (because login has not completed yet) and can NOT require login (because we're in the middle of it).
 *                      So, if $USER is not set, return.
 * 10/08/19 - Removing "Notice: Undefined property: stdClass::$profile in /var/www/html/local/swtc/lib/swtclib.php on line 777" error by
 *                  correctly loading the user's profile data.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/23/19 - In swtc_groups_sort_menu_options, added important note that it is only to be called for PS/SD administrator user types
 *                      and above (i.e. NOT for PS/SD students or managers).
 * 10/31/19 - Added correct setting of swtc_user information; changed swtc_get_user to return swtc_user; changed
 *                  swtc_get_user_details to get and return swtc_user; added special processing if $USER is not set yet
 *                  (for instance, in the /enrol/autoenrol plugin).
 * 11/01/19 - Modified how the new Lenovo EBGLMS classes and methods are called throughout all customized code; moved some functions
 *                      to other modules.
 * 12/19/19 - In swtc_user_access_category, added exception for SelfSupport students (because they do not have access to any top
 *                      level category).
 * PTR2019Q402 - @01 - 03/01/20 - Added user timezone to improve performance; changed swtc_user_access_category to pass
 *                  $category information from get() so that swtc_toplevel_category does not neet to call get() again; in
 *                  swtc_user_access_category, changed get() on each category id to core_course_category::make_categories_list with the
 *                  user's main capability; moved core_course_category::make_categories_list higher in the stack for better performance.
 * 03/10/20 - Added swtc_get_user_groupnames.
 * PTR2019Q401 - @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers; added
 *                      swtc_get_user_groupnames_menuid.
 * PTR2020Q109 - @03 - 05/06/20 - Added field for user profile field "Accesstype2".
 *
 *
 *****************************************************************************/
defined('MOODLE_INTERNAL') || die();


// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions. 08/10/18 - RF - Was missing; added.

require_once ($CFG->dirroot.'/cohort/lib.php');
// require_once($CFG->libdir. '/coursecatlib.php');     // Removed for Moodle 3.6
require_once($CFG->dirroot.'/user/profile/lib.php');        // 10/08/19

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
 * 04/19/18 - Initial writing.
 * 11/27/18 - Added ServiceDelivery.
 * 01/10/19 - Added Curriculums Portfolio.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 **/
function swtc_loadcatids($roles) {
	// global $CFG, $USER, $DB, $PAGE, $COURSE, $OUTPUT, $SESSION;
    global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $cats = array();						// A list of all the top-level category information defined (this is returned).
    $strings = $SESSION->EBGLMS->STRINGS;
    $capabilities = $SESSION->EBGLMS->STRINGS->capabilities;
    $top_level_categories = $SESSION->EBGLMS->STRINGS->top_level_categories;
    // Lenovo ********************************************************************************.

	if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_swtclib.php. ===swtc_loadcatids.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    // Lenovo ********************************************************************************
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
    // Lenovo ********************************************************************************
    $catids = get_tree(0);				// '0' means just the top-level categories are returned.

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

    // Lenovo ********************************************************************************
	// Next, load a multi-dimension array for each of the top-level categories (this array will be searched by name for the id below):
    //              'catid'             - the id of the top-level category (returned from the get_tree(0) call above).
    //              'catname'       - the name of the top-level category (ex: "GTP Portfolio").
    //              'context'       - create a context of context_coursecat.
    //              'capability'    - the capability associated with this top-level category (ex: local/swtc:ebg_access_gtp_portfolio).
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
	//					[capability] => local/swtc:ebg_access_gtp_portfolio
	//					[roles] => Array
	//						(
	//							[gtp-instructor] => 15
	//							[gtp-student] => 16
	//							[gtp-administrator] => 10
	//							[gtp-siteadministrator] => 23
	//						)
	//				)
	//
    // Lenovo ********************************************************************************

    // Lenovo ********************************************************************************
	// Build the main $cats array (to be passed back to local_swtc_assign_user_role).
    // Lenovo ********************************************************************************
	foreach ($catids as $key => $catid) {
		$cats[$key]['catid'] = $catid;
		// $cats[$key]['catname'] = coursecat::get($catid, MUST_EXIST, true)->name;     // Moodle 3.6
        $cats[$key]['catname'] = \core_course_category::get($catid, MUST_EXIST, true)->name;
		$cats[$key]['context'] = \context_coursecat::instance($catid);

        // Lenovo ********************************************************************************
		// Remember: top-level categories are accessed by $top_level_categories->xxx; capabilities are accessed by $capabilities->xxx.
		// 		For each top-level category, add a two-dimentional array consisting of the roleshortnames and roleids of the roles that have access
		//		to the top-level category.
		// Lenovo ********************************************************************************

        // Lenovo ********************************************************************************
        // Switch on the 'catname'.
        //      Note: If adding a new portfolio, add a new case to this switch.
        // Lenovo ********************************************************************************
        switch ($cats[$key]['catname'] ) {
            // Lenovo ********************************************************************************
            // 'GTP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->gtp_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_gtp_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    //if (isset($debug)) {
                    //	debug_logmessage("role follows: <br />", 'logfile');
                    //	print_object($role);
                    //	debug_logmessage("role->shortname to search for is <strong>$role->shortname</strong>.<br />", 'logfile');
                    //}
                    if ($role->shortname == $strings->generic_role->role_gtp_administrator) {
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_administrator] = $role->id;
                    } else if ($role->shortname == $strings->generic_role->role_gtp_siteadministrator) {
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_siteadministrator] = $role->id;
                    } else if ($role->shortname == $strings->generic_role->role_gtp_instructor){
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_instructor] = $role->id;
                    }else if ($role->shortname == $strings->generic_role->role_gtp_student){
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'Lenovo Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->lenovo_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_lenovo_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->lenovo->role_lenovo_administrator) {
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_administrator] = $role->id;
                    } else if ($role->shortname == $strings->lenovo->role_lenovo_instructor){
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_instructor] = $role->id;
                    }else if ($role->shortname == $strings->lenovo->role_lenovo_student){
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'IBM Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
			// 08/25/16 - Changed "Lenovo and IBM Portfolio" values to just "IBM Portfolio" so that values
            //                  will be the same (i.e. will help in transition).
            // Lenovo ********************************************************************************
            case $top_level_categories->ibm_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_ibm_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->ibm->role_ibm_student) {
                        $cats[$key]['roles'][$strings->ibm->role_ibm_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'ServiceProvider Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->serviceprovider_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_serviceprovider_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->serviceprovider->role_serviceprovider_student) {
                        $cats[$key]['roles'][$strings->serviceprovider->role_serviceprovider_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'Lenovo Internal Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->lenovointernal_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_lenovointernal_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->lenovo->role_lenovo_administrator) {
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_administrator] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'Maintech Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->maintech_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_maintech_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->maintech->role_maintech_student){
                        $cats[$key]['roles'][$strings->maintech->role_maintech_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'Lenovo Shared Resources (Master)' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->lenovosharedresources_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_lenovosharedresources;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->lenovo->role_lenovo_administrator) {
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_administrator] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'ASP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->asp_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_asp_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->asp_maintech->role_asp_maintech_student){
                        $cats[$key]['roles'][$strings->asp_maintech->role_asp_maintech_student] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'PremierSupport Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->premiersupport_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_premiersupport_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->premiersupport->role_premiersupport_student){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_student] = $role->id;
                    } else if ($role->shortname == $strings->premiersupport->role_premiersupport_administrator){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_administrator] = $role->id;
                    } else if ($role->shortname == $strings->premiersupport->role_premiersupport_manager){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_manager] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'ServiceDelivery Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->servicedelivery_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_servicedelivery_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->servicedelivery->role_servicedelivery_student){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_student] = $role->id;
                    } else if ($role->shortname == $strings->servicedelivery->role_servicedelivery_administrator){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_administrator] = $role->id;
                    } else if ($role->shortname == $strings->servicedelivery->role_servicedelivery_manager){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_manager] = $role->id;
                    }
                }
                break;

            // Lenovo ********************************************************************************
			// 'Site Help Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->sitehelp_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_sitehelp_portfolio;

                // Load all the roleids. Remember that ALL roles have access to this portfolio.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->generic_role->role_gtp_administrator) {
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_administrator] = $role->id;
                    } else if ($role->shortname == $strings->generic_role->role_gtp_siteadministrator) {
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_siteadministrator] = $role->id;
                    } else if ($role->shortname == $strings->generic_role->role_gtp_instructor){
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_instructor] = $role->id;
                    } else if ($role->shortname == $strings->generic_role->role_gtp_student){
                        $cats[$key]['roles'][$strings->generic_role->role_gtp_student] = $role->id;
                    } else if ($role->shortname == $strings->lenovo->role_lenovo_administrator) {
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_administrator] = $role->id;
                    } else if ($role->shortname == $strings->lenovo->role_lenovo_instructor){
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_instructor] = $role->id;
                    }else if ($role->shortname == $strings->lenovo->role_lenovo_student){
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_student] = $role->id;
                    } else if ($role->shortname == $strings->ibm->role_ibm_student) {
                        $cats[$key]['roles'][$strings->ibm->role_ibm_student] = $role->id;
                    } else if ($role->shortname == $strings->serviceprovider->role_serviceprovider_student) {
                        $cats[$key]['roles'][$strings->serviceprovider->role_serviceprovider_student] = $role->id;
                    } else if ($role->shortname == $strings->lenovo->role_lenovo_administrator) {
                        $cats[$key]['roles'][$strings->lenovo->role_lenovo_administrator] = $role->id;
                    } else if ($role->shortname == $strings->asp_maintech->role_asp_maintech_student){
                        $cats[$key]['roles'][$strings->asp_maintech->role_asp_maintech_student] = $role->id;
                    } else if ($role->shortname == $strings->premiersupport->role_premiersupport_student){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_student] = $role->id;
                    } else if ($role->shortname == $strings->premiersupport->role_premiersupport_administrator){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_administrator] = $role->id;
                    } else if ($role->shortname == $strings->premiersupport->role_premiersupport_manager){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_manager] = $role->id;
					} else if ($role->shortname == $strings->servicedelivery->role_servicedelivery_student){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_student] = $role->id;
                    } else if ($role->shortname == $strings->servicedelivery->role_servicedelivery_administrator){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_administrator] = $role->id;
                    } else if ($role->shortname == $strings->servicedelivery->role_servicedelivery_manager){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_manager] = $role->id;
					}
                }
                break;

			// Lenovo ********************************************************************************
			// 'Curriculums Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
            // Lenovo ********************************************************************************
            case $top_level_categories->curriculums_portfolio:
                $cats[$key]['capability']  = $capabilities->cap_ebg_access_curriculums_portfolio;

                // Load all the roleids.
                foreach ($roles as $role) {
                    if ($role->shortname == $strings->servicedelivery->role_servicedelivery_student){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_student] = $role->id;
                    } elseif ($role->shortname == $strings->servicedelivery->role_servicedelivery_administrator){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_administrator] = $role->id;
                    } elseif ($role->shortname == $strings->servicedelivery->role_servicedelivery_manager){
                        $cats[$key]['roles'][$strings->servicedelivery->role_servicedelivery_manager] = $role->id;
                    } elseif ($role->shortname == $strings->premiersupport->role_premiersupport_student){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_student] = $role->id;
                    } elseif ($role->shortname == $strings->premiersupport->role_premiersupport_administrator){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_administrator] = $role->id;
                    } elseif ($role->shortname == $strings->premiersupport->role_premiersupport_manager){
                        $cats[$key]['roles'][$strings->premiersupport->role_premiersupport_manager] = $role->id;
                    }
                }
                break;

            default:
                // unknown type
        }
    }

    // Lenovo ********************************************************************************
	// Note: At this point the $cats array should be fully created...
    // Lenovo ********************************************************************************
    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
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
 * Get current date and time for timestamp. Returns value to set $SESSION->EBGLMS->USER->timestamp.
 *
 * History:
 *
 * @01 - Added user timezone to improve performance.
 *
 */
function swtc_timestamp() {
    global $CFG, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // Lenovo ********************************************************************************.

    // Lenovo ********************************************************************************
    // Make all the times these variables were set the same.
    // Make all the functions these variables were set the same.
    // Lenovo ********************************************************************************
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
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
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

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    $access_selfsupport_stud = $SESSION->EBGLMS->STRINGS->selfsupport->access_selfsupport_stud;

    // Other Lenovo variables.
    $user_categoryids = $swtc_user->categoryids;

    // Lenovo ********************************************************************************
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again; changed get() on each category id to core_course_category::make_categories_list
    //                  with the user's main capability (category information is cached for 10 minutes by Moodle).
    // $category = coursecat::get($catid, MUST_EXIST, true);        // Moodle 3.6
    // $category = \core_course_category::get($catid, MUST_EXIST, true);
    // Lenovo ********************************************************************************
    // $categories = core_course_category::make_categories_list($capability);

    // Lenovo ********************************************************************************.

	if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
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

    // Lenovo ********************************************************************************
    // Get the top-level category for this catid.
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again.
    // $toplevelcat = swtc_toplevel_category($catid);
    // Lenovo ********************************************************************************
    // $toplevelcat = swtc_toplevel_category($category);

    // if (isset($debug)) {
    //     $messages[] = print_r("toplevelcat category to search for is :$toplevelcat ===swtc_user_access_category.", true);
    //     debug_logmessage($messages, 'detailed');
    //     unset($messages);
    // }

    // Lenovo ********************************************************************************
    // If toplevelcat appears in $swtc_user->categoryids, return true. If not, return false.
    // 12/19/19 - Added exception for SelfSupport students (because they do not have access to any top level category).
    // Lenovo ********************************************************************************
    // if (array_search($toplevelcat, $user_categoryids) !== false) {       // 11/08/18
    // if (array_search($toplevelcat, array_column($user_categoryids, 'catid')) !== false) {
    // @01 - Change to array_keys search.
    if ((in_array($catid, array_keys($cats))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
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
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // Lenovo ********************************************************************************.

    // Lenovo ********************************************************************************
    // @01 - Changed swtc_user_access_category to pass $category information from get() so that swtc_toplevel_category
    //                  does not neet to call get() again.
    // $category = coursecat::get($catid, MUST_EXIST, true);        // Moodle 3.6
    // Lenovo ********************************************************************************
    $category = \core_course_category::get($catid, MUST_EXIST, true);

    // Lenovo ********************************************************************************
    // Get the parents of this category (if any).
    // Lenovo ********************************************************************************
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
 * Takes user's allowed groups and own groups and formats for use in group selector menu
 * If user has allowed groups + own groups will add to an optgroup
 * Own groups are removed from allowed groups
 *
 *  This customized version of groups_sort_menu_options should only be called from the functions groups_print_course_menu
 *           and groups_print_activity_menu and only if the users access type is either a PremierSupport or ServiceDelivery administrator
 *           or site administrator. The customized course/activity menu should look like the following (Manger access types are not affected):
 *
 *      PS/SD administrator (ex: PS-US1-administrator; all centered around "US1")
 *              0 - "All PremierSupport US1 enrollments" (all US1 groups; string is 'groups_premiersupport_all_group_participants')
 *              1 - "PremierSupport US1 enrollments" (string is 'groups_premiersupport_group_participants')
 *              2 -         "PremierSupport US1 students" (all US1 student groups; string is 'groups_premiersupport_group_type_participants')
 *              3 -         "PremierSupport US1 managers" (all US1 manager groups; string is 'groups_premiersupport_group_type_participants')
 *              4 -         "PremierSupport US1 administrators" (all US1 administrator groups; string is 'groups_premiersupport_group_type_participants')
 *
 *      PS/SD GEO administrator (ex: PS-CA-geoadministrator; all centered around "CA")
 *              0 - "All PremierSupport CA enrollments" (all CA groups; string is 'groups_premiersupport_all_geo_participants')
 *              1 - "PremierSupport CA enrollments" (string is 'groups_premiersupport_geo_participants')
 *              2 -         "PremierSupport CA students" (all CA student groups; string is 'groups_premiersupport_geo_type_participants')
 *              3 -         "PremierSupport CA managers" (all CA manager groups; string is 'groups_premiersupport_geo_type_participants')
 *              4 -         "PremierSupport CA administrators" (all CA administrator groups; string is 'groups_premiersupport_geo_type_participants')
 *              5 -         "PremierSupport CA geoadministrators" (all CA geoadministrator groups;
 *                                                                                                      string is 'groups_premiersupport_geo_type_participants')
 *
 *      PS/SD GEO site administrator (ex: SD-US-siteadministrator; all centered around ALL GEOs "US, AP, EM, CA, LA.")
 *                  Uses all the same strings as site administrator (below). This is only for grouping.
 *
 *      PS/SD site administrator (ex: SD-siteadministrator; all centered around ALL GEOs "US, AP, EM, CA, LA.")
 *              0 - "All ServiceDelivery enrollments" (all GEO groups; string is 'groups_servicedelivery_all_participants')
 *              1 - "ServiceDelivery US enrollments" (all US groups; string is 'groups_servicedelivery_geo_participants')
 *              2 -         "ServiceDelivery US students" (all US student groups; string is 'string is 'groups_servicedelivery_geo_type_participants')
 *              3 -         "ServiceDelivery US managers" (all US manager groups)
 *              4 -         "ServiceDelivery US administrators" (all US administrator groups)
 *              5 -         "ServiceDelivery US site administrators" (all US site administrator groups)
 *              6 - "ServiceDelivery LA enrollments" (all LA groups)
 *              7 -         "ServiceDelivery LA students" (all LA student groups)
 *              8 -         "ServiceDelivery LA managers" (all LA manager groups)
 *              9 -         "ServiceDelivery LA administrators" (all LA administrator groups)
 *              10 -         "ServiceDelivery LA site administrators" (all LA site administrator groups)
 *              11 - "ServiceDelivery CA enrollments" (all CA groups)
 *              12 -         "ServiceDelivery CA students" (all CA student groups)
 *              13 -         "ServiceDelivery CA managers" (all CA manager groups)
 *              14 -         "ServiceDelivery CA administrators" (all CA administrator groups)
 *              15 -         "ServiceDelivery CA site administrators" (all CA site administrator groups)
 *              16 - "ServiceDelivery AP enrollments" (all AP groups)
 *              17 -         "ServiceDelivery AP students" (all AP student groups)
 *              18 -         "ServiceDelivery AP managers" (all AP manager groups)
 *              19 -         "ServiceDelivery AP administrators" (all AP administrator groups)
 *              20 -         "ServiceDelivery AP site administrators" (all AP site administrator groups)
 *              21 - "ServiceDelivery EM enrollments" (all EM groups)
 *              22 -         "ServiceDelivery EM students" (all EM student groups)
 *              23 -         "ServiceDelivery EM managers" (all EM manager groups)
 *              24 -         "ServiceDelivery EM administrators" (all EM administrator groups)
 *              25 -         "ServiceDelivery EM site administrators" (all EM site administrator groups)
 *
 * @param array $allowedgroups All groups user is allowed to see
 * @param array $usergroups Groups user belongs to
 * @param int $access_type Access type of user
 * @return array
 */
  /**
 * Version details
 *
 * History:
 *
 * 03/05/19 - Initial writing.
 * 03/08/19 - Added geoname to keep the main GEO for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EM5-mgr, the GEO is EM); added PS/SD GEO site administrator user access types.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/23/19 - In swtc_groups_sort_menu_options, added important note that it is only to be called for PS/SD administrator user types
 *                      and above (i.e. NOT for PS/SD students or managers).
 * @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers; added
 *                      swtc_get_user_groupnames_menuid.
 * @03 - 05/06/20 - Added field for user profile field "Accesstype2".
 *
 **/
function swtc_groups_sort_menu_options($allowedgroups, $usergroups, $access_type) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $user_access_type = $swtc_user->user_access_type;
    $user_access_type2 = $swtc_user->user_access_type2;       // @03
    $user_groupname = null;
    $user_geoname = null;

    $user_groupname = $swtc_user->groupname;
    $user_geoname = $swtc_user->geoname;

    $message_params = new stdClass();
    $message_params->groupname = $user_groupname;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;        // @02
    $access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
    $access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
    $access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

    $access_lenovo_sd_mgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;        // @02
    $access_lenovo_sd_admin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
    $access_lenovo_sd_geoadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin;
    $access_lenovo_sd_siteadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin;

    // Hold the temporary "dummy" group id to display.
    $uuid = null;

    // The following pattern will match "<whatever>-US1-<whatever> or "<whatever>-EM5-<whatever>".
    // 03/03/19 - have to have two strings to compare - one for strings with the GEO number in it (-US1-) and one
    //                      for across all GEOs (-US-).
    $cmp_studs_string = null;
    $cmp_mgrs_string = null;
    $cmp_admins_string = null;
    $cmp_geoadmins_string = null;
    $cmp_siteadmins_string = null;
    // $cmp_geoadmins = '/-([A-Z][A-Z])-/';
    // $cmp_allotherroles = '/-([A-Z][A-Z]+[1-9])-/';

    // Customized menu items.
    $studs_menu = null;
    $studs_menu_item = null;
    $mgrs_menu = null;
    $mgrs_menu_item = null;
    $admins_menu = null;
    $admins_menu_item = null;
    $geoadmins_menu = null;
    $geoadmins_menu_item = null;
    $siteadmins_menu = null;
    $siteadmins_menu_item = null;

    $groups_siteadmins = array();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering /swtc/lib/swtclib/swtc_groups_sort_menu_options.enter===.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    $useroptions = array();
    if ($usergroups) {
        $useroptions = groups_list_to_menu($usergroups);

        // Remove user groups from other groups list.
        foreach ($usergroups as $group) {
            unset($allowedgroups[$group->id]);
        }
    }

    $allowedoptions = array();
    if ($allowedgroups) {
        $allowedoptions = groups_list_to_menu($allowedgroups);
    }

    //****************************************************************************************.
    // Setup all variables needed based on PS/AD access types (excluding manager and student).
    // 10/23/19 - Added important note that it is only to be called for PS/SD administrator user types and above
    //                  (i.e. NOT for PS/SD students or managers).
    //****************************************************************************************.
    // Lenovo ********************************************************************************
    // PremierSupport access type.
    // @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers.
    // Lenovo ********************************************************************************
    if ((preg_match($access_ps_siteadmin, $user_access_type)) || (preg_match($access_ps_geoadmin, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_ps_mgr, $user_access_type))) {
        //****************************************************************************************.
        // Common strings for all PremierSupport access types.
        //****************************************************************************************.
        $cmp_studs_string = get_string('cohort_premiersupport_pregmatch_studs', 'local_swtc');
        $cmp_mgrs_string = get_string('cohort_premiersupport_pregmatch_mgrs', 'local_swtc');
        $cmp_admins_string = get_string('cohort_premiersupport_pregmatch_admins', 'local_swtc');
        $cmp_geoadmins_string = get_string('cohort_premiersupport_pregmatch_geoadmins', 'local_swtc');
        $cmp_siteadmins_string = get_string('cohort_premiersupport_pregmatch_siteadmins', 'local_swtc');

        //****************************************************************************************.
        // PremierSupport site administrators
        //****************************************************************************************.
        if (preg_match($access_ps_siteadmin, $user_access_type)) {
            $groups_menu = get_string('groups_premiersupport_all_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'GEO administrator';
            $geoadmins_menu_item = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // PremierSupport GEO administrators
        //****************************************************************************************.
        } else if (preg_match($access_ps_geoadmin, $user_access_type)) {
            $groups_menu = get_string('groups_premiersupport_all_geo_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'GEO administrator';
            $geoadmins_menu_item = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // PremierSupport administrators
        //****************************************************************************************.
        } else if (preg_match($access_ps_admin, $user_access_type)) {
            $groups_menu = get_string('groups_premiersupport_group_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // PremierSupport managers
        // @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers.
        //****************************************************************************************.
        } else if (preg_match($access_ps_mgr, $user_access_type)) {        // @02
            $groups_menu = get_string('groups_premiersupport_group_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $message_params);
        }
    // Lenovo ********************************************************************************
    // ServiceDelivery access type.
    // @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers.
    // Lenovo ********************************************************************************
    } else if ((preg_match($access_lenovo_sd_siteadmin, $user_access_type)) || (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type)) || (preg_match($access_lenovo_sd_mgr, $user_access_type))) {
        //****************************************************************************************.
        // Common strings for all ServiceDelivery access types.
        //****************************************************************************************.
        $cmp_studs_string = get_string('cohort_lenovo_servicedelivery_pregmatch_studs', 'local_swtc');
        $cmp_mgrs_string = get_string('cohort_lenovo_servicedelivery_pregmatch_mgrs', 'local_swtc');
        $cmp_admins_string = get_string('cohort_lenovo_servicedelivery_pregmatch_admins', 'local_swtc');
        $cmp_geoadmins_string = get_string('cohort_lenovo_servicedelivery_pregmatch_geoadmins', 'local_swtc');
        $cmp_siteadmins_string = get_string('cohort_lenovo_servicedelivery_pregmatch_siteadmins', 'local_swtc');

        //****************************************************************************************.
        // ServiceDelivery site administrators
        //****************************************************************************************.
        if (preg_match($access_lenovo_sd_siteadmin, $user_access_type)) {
            $groups_menu = get_string('groups_lenovo_servicedelivery_all_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'GEO administrator';
            $geoadmins_menu_item = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // ServiceDelivery GEO administrators
        //****************************************************************************************.
        } else if (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) {
            $groups_menu = get_string('groups_lenovo_servicedelivery_all_geo_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'GEO administrator';
            $geoadmins_menu_item = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // ServiceDelivery administrators
        //****************************************************************************************.
        } else if (preg_match($access_lenovo_sd_admin, $user_access_type)) {
            $groups_menu = get_string('groups_lenovo_servicedelivery_group_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'administrator';
            $admins_menu_item = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $message_params);
        //****************************************************************************************.
        // ServiceDelivery managers
        // @02 - 03/24/20 - In swtc_groups_sort_menu_options, add back the check for PS/SD managers.
        //****************************************************************************************.
        } else if (preg_match($access_lenovo_sd_mgr, $user_access_type)) {
            $groups_menu = get_string('groups_lenovo_servicedelivery_group_participants', 'local_swtc', $message_params);

            // The following 6 lines WORK!
            $message_params->type = 'student';
            $studs_menu_item = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $message_params);
            $message_params->type = 'manager';
            $mgrs_menu_item = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $message_params);
        }
    }

    //****************************************************************************************.
    // List all the groups that would be included in the following top-level groups:
    //      If site administrator, <PS/SD>-<GEO>%-studs%, <PS/SD>-<GEO>%-mgrs%, and <PS/SD>-<GEO>%-admins%.
    //      If administrator, <PS/SD>-<GEO><1-9>%-studs%, <PS/SD>-<GEO><1-9>%-mgrs%, and <PS/SD>-<GEO><1-9>%-admins%.
    //
    // Group from allowed groups.
    //****************************************************************************************.
    // @01 - Should this be $usergroups instead of $allowedgroups? 03/25/20 - No (must be allowed), at least for GEO admins.
    if ($allowedgroups) {
        foreach ($allowedgroups as $group) {
    // if ($usergroups) {
    //    foreach ($usergroups as $group) {
            // print_object("groupname to check is :$group->name.\n");
            // Lenovo ********************************************************************************
            // Is it a groups of students?
            // Lenovo ********************************************************************************
            if (preg_match($cmp_studs_string, $group->name)) {
                // $studs_menu[$group->id] = $group->name; WORKS...
                $studs_menu[$group->id] = $group->id;
            // Lenovo ********************************************************************************
            // Is it a groups of managers?
            // Lenovo ********************************************************************************
            } else if (preg_match($cmp_mgrs_string, $group->name)) {
                // $mgrs_menu[$group->id] = $group->name; WORKS...
                $mgrs_menu[$group->id] = $group->id;
            // Lenovo ********************************************************************************
            // Is it a groups of administrators?
            // Lenovo ********************************************************************************
            } else if (preg_match($cmp_admins_string, $group->name)) {
                // $admins_menu[$group->id] = $group->name; WORKS...
                $admins_menu[$group->id] = $group->id;
            // Lenovo ********************************************************************************
            // Is it a groups of GEO administrators?
            // Lenovo ********************************************************************************
            } else if (preg_match($cmp_geoadmins_string, $group->name)) {
                // $geoadmins_menu[$group->id] = $group->name; WORKS...
                // print_object("preg_match string for geo admins is :$cmp_geoadmins_string.\n");
                $geoadmins_menu[$group->id] = $group->id;
            // Lenovo ********************************************************************************
            // Is it a groups of site administrators?
            // Lenovo ********************************************************************************
            } else if (preg_match($cmp_siteadmins_string, $group->name)) {
                // $siteadmins_menu[$group->id] = $group->name; WORKS...
                $siteadmins_menu[$group->id] = $group->id;
            }
        }
    } else {
        // @01 - Must be a PS/SD manager.
        if ($usergroups) {
            foreach ($usergroups as $group) {
                // print_object("groupname to check is :$group->name.\n");
                // Lenovo ********************************************************************************
                // Is it a groups of students?
                // Lenovo ********************************************************************************
                if (preg_match($cmp_studs_string, $group->name)) {
                    // $studs_menu[$group->id] = $group->name; WORKS...
                    $studs_menu[$group->id] = $group->id;
                // Lenovo ********************************************************************************
                // Is it a groups of managers?
                // Lenovo ********************************************************************************
                } else if (preg_match($cmp_mgrs_string, $group->name)) {
                    // $mgrs_menu[$group->id] = $group->name; WORKS...
                    $mgrs_menu[$group->id] = $group->id;
                // Lenovo ********************************************************************************
                // Is it a groups of administrators?
                // Lenovo ********************************************************************************
                } else if (preg_match($cmp_admins_string, $group->name)) {
                    // $admins_menu[$group->id] = $group->name; WORKS...
                    $admins_menu[$group->id] = $group->id;
                // Lenovo ********************************************************************************
                // Is it a groups of GEO administrators?
                // Lenovo ********************************************************************************
                } else if (preg_match($cmp_geoadmins_string, $group->name)) {
                    // $geoadmins_menu[$group->id] = $group->name; WORKS...
                    // print_object("preg_match string for geo admins is :$cmp_geoadmins_string.\n");
                    $geoadmins_menu[$group->id] = $group->id;
                // Lenovo ********************************************************************************
                // Is it a groups of site administrators?
                // Lenovo ********************************************************************************
                } else if (preg_match($cmp_siteadmins_string, $group->name)) {
                    // $siteadmins_menu[$group->id] = $group->name; WORKS...
                    $siteadmins_menu[$group->id] = $group->id;
                }
            }
        }
    }

    // Lenovo ********************************************************************************.
    // Link menu items and groups to display.
    // $groupnames[$group->id]['id'] = $group->id;
    // Lenovo ********************************************************************************.
    if (!empty($studs_menu)) {
        $tmp = implode(', ', $studs_menu);
        $submenu_item = 'studs_menu';
        if (empty($swtc_user->groupnames[$submenu_item])) {
            $uuid = rand();
            // $swtc_user->groupnames[$submenu_item]['uuid'] = $uuid;
            // $swtc_user->groupnames[$submenu_item]['groups'] = $tmp;
            $swtc_user->groupnames[$submenu_item][$uuid]['uuid'] = $uuid;
            $swtc_user->groupnames[$submenu_item][$uuid]['groups'] = $tmp;
        // Get the current uuid.
        } else {
            // Use foreach even though there will only be one key and one value.
            foreach($swtc_user->groupnames[$submenu_item] as $key => $value) {
                $uuid = $key;
            }
        }
        // ${$groups_menu}[$studs_menu_item] = $studs_menu; WORKS!
        ${$groups_menu}[$uuid] = $studs_menu_item;
    }

    if (!empty($mgrs_menu)) {
        $tmp = implode(', ', $mgrs_menu);
        $submenu_item = 'mgrs_menu';
        if (empty($swtc_user->groupnames[$submenu_item])) {
            $uuid = rand();
            // $swtc_user->groupnames[$submenu_item]['uuid'] = $uuid;
            // $swtc_user->groupnames[$submenu_item]['groups'] = $tmp;
            $swtc_user->groupnames[$submenu_item][$uuid]['uuid'] = $uuid;
            $swtc_user->groupnames[$submenu_item][$uuid]['groups'] = $tmp;
            // Get the current uuid.
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach($swtc_user->groupnames[$submenu_item] as $key => $value) {
                    $uuid = $key;
                }
            }
        // ${$groups_menu}[$mgrs_menu_item] = $mgrs_menu; WORKS!
        // ${$groups_menu}[$tmp] = $mgrs_menu_item;
        ${$groups_menu}[$uuid] = $mgrs_menu_item;
    }

    if (!empty($admins_menu)) {
        $tmp = implode(', ', $admins_menu);
        $submenu_item = 'admins_menu';
        if (empty($swtc_user->groupnames[$submenu_item])) {
            $uuid = rand();
            // $swtc_user->groupnames[$submenu_item]['uuid'] = $uuid;
            // $swtc_user->groupnames[$submenu_item]['groups'] = $tmp;
            $swtc_user->groupnames[$submenu_item][$uuid]['uuid'] = $uuid;
           $swtc_user->groupnames[$submenu_item][$uuid]['groups'] = $tmp;
            // Get the current uuid.
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach($swtc_user->groupnames[$submenu_item] as $key => $value) {
                    $uuid = $key;
                }
            }
        // ${$groups_menu}[$admins_menu_item] = $admins_menu; WORKS!
        ${$groups_menu}[$uuid] = $admins_menu_item;
    }

    if (!empty($geoadmins_menu)) {
        $tmp = implode(', ', $geoadmins_menu);
        $submenu_item = 'geoadmins_menu';
        if (empty($swtc_user->groupnames[$submenu_item])) {
            $uuid = rand();
            // $swtc_user->groupnames[$submenu_item]['uuid'] = $uuid;
            // $swtc_user->groupnames[$submenu_item]['groups'] = $tmp;
            $swtc_user->groupnames[$submenu_item][$uuid]['uuid'] = $uuid;
            $swtc_user->groupnames[$submenu_item][$uuid]['groups'] = $tmp;
            // Get the current uuid.
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach($swtc_user->groupnames[$submenu_item] as $key => $value) {
                    $uuid = $key;
                }
            }
        // ${$groups_menu}[$geoadmins_menu_item] = $geoadmins_menu; WORKS!
        ${$groups_menu}[$uuid] = $geoadmins_menu_item;
    }

    if (!empty($siteadmins_menu)) {
        $tmp = implode(', ', $siteadmins_menu);
        $submenu_item = 'siteadmins_menu';
        if (empty($swtc_user->groupnames[$submenu_item])) {
            $uuid = rand();
            // $swtc_user->groupnames[$submenu_item]['uuid'] = $uuid;
            // $swtc_user->groupnames[$submenu_item]['groups'] = $tmp;
            $swtc_user->groupnames[$submenu_item][$uuid]['uuid'] = $uuid;
            $swtc_user->groupnames[$submenu_item][$uuid]['groups'] = $tmp;
            // Get the current uuid.
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach($swtc_user->groupnames[$submenu_item] as $key => $value) {
                    $uuid = $key;
                }
            }
        // ${$groups_menu}[$siteadmins_menu_item] = $siteadmins_menu; WORKS!
        ${$groups_menu}[$uuid] = $siteadmins_menu_item;
    }

    if (isset($debug)) {
        $messages[] = "Lenovo ********************************************************************************.";
		$messages[] = "About to print all groups.";
        $messages[] = print_r($groups_menu, true);
        $messages[] = "Finished printing all groups.";
		// print_object($user_groupname);
        $messages[] = "About to print dynamic groups_menu";
        $messages[] = print_r(${$groups_menu}, true);
        // print_object("in swtc_groups_sort_menu_options - about to print swtc_user->groupnames");
        // print_object($swtc_user->groupnames);
        // print_object("about to print non-dynamic groups_menu");
        // print_object($groups_menu);
        // print_object($groups_mgrs);
        // print_object($groups_admins);
        // print_object($groups_geoadmins);
        // print_object($groups_siteadmins);
        debug_logmessage($messages, 'detailed');
        unset($messages);

        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving /swtc/lib/swtclib/swtc_groups_sort_menu_options.exit===.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
	}

    //****************************************************************************************.
    // TODO: I **KNOW*** there is a better way to build this menu...
    //****************************************************************************************.
    // @01 - Should this be $usergroups instead of $allowedgroups?
    // if ($useroptions && $allowedoptions) {       // @01
    if ($useroptions) {     // @01
        //****************************************************************************************.
        // PS/SD customized menu
        //****************************************************************************************.
        $swtc_menu = array(
            1 => array($groups_menu => ${$groups_menu}),
            2 => array(get_string('mygroups', 'group') => $useroptions),
            3 => array(get_string('othergroups', 'group') => $allowedoptions)
        );
        // print_object($swtc_menu);
        return $swtc_menu;
    } else if ($useroptions) {
        return $useroptions;
    } else {
        return $allowedoptions;
    }
}

/**
 * Saves all the ids and names of all the groups the user has access to in $user->groupnames.
 *
 * $user The $ebg_user variable.
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
    // Local temporary EBGLMS variables.
    // $usergroups = new stdClass();
    $usergroups = array();
    // Local variables end...
	//****************************************************************************************

	// Lenovo ********************************************************************************
	// Loop through the groups passed in and save the information in the swtc_user->groupnames.
	// Lenovo ********************************************************************************
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
 * $user The $ebg_user variable.
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
    // Local temporary EBGLMS variables.
    // $usergroups = new stdClass();
    $grouplist = array();
    $groupnames = array();
    $usergroups = $user->groupnames;
    $firstid = (stripos($option, 'firstid') !== false) ? 1 : null;      // @01

    // Local variables end...
	//****************************************************************************************

	// Lenovo ********************************************************************************
	// Loop through the groups passed in and save the information in the swtc_user->groupnames.
	// Lenovo ********************************************************************************
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
 * $user The $ebg_user variable.
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
    // Local temporary EBGLMS variables.
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
