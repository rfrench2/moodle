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
 * @subpackage swtc/lib/swtc_strings.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	04/14/18 - Initial writing; loads all strings used by EBGLMS.
 * 04/28/18 - Added Self support student strings.
 * 08/27/18 - Added strings for PremierSupport cohort names.
 * 11/07/18 - Added additional access type strings for all PremierSupport user types.
 * 11/12/18 - Added strings for ServiceDelivery portfolio.
 * 11/13/17 - Added strings for ThinkSystem DE Storage and ThinkSystem DM storage certifications.
 * 11/20/18 - Changed strings for ServiceDelivery access types; removed Special-access student type.
 * 11/28/18 - Added capability strings for viewing curriculums and viewing reports.
 * 01/10/19 - Added Curriculums Portfolio.
 * 01/17/19 - Added additional strings for easier stripos comparisons.
 * 01/23/19 - Changed all PremierSupport and ServiceDelivery strings to conform to the standard "PS-<GEO><1-5>-whatever" and
 *						"SD-<GEO><1-5>-whatever"; added strings for all users to use in preg_match calls.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; added additional preg_match strings for matching
 *                      all SD-US users (regardless of group).
 * 02/28/19 - Adding additional PremierSupport and ServiceDelivery strings for "all GEO participants".
 * 03/01/19 - Added additional PremierSupport and ServiceDelivery strings for site administrator access types.
 * 03/04/19 - Added strings for customized PS/SD groups menu; added string that contains all the GEOs.
 * 03/06/19 - Modified PremierSupport and ServiceDelivery "all GEO participants"strings to add either students, managers, administrators,
 *                      or site administrators; added PS/SD GEO administrator role.
 * 03/08/19 - Added PS/SD GEO site administrator user access types.
 * 03/09/19 - Changed all strings containing "EMEA" (4-letters) to "EM" (2-letters) so that the preg_match strings will match.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 05/09/19 - Added preg_match strings for Lenovo-admins and Lenovo-siteadmins.
 * 05/16/19 - Adding ThinkAgile, and changing some current, certification names (per Cheryl).
 * 05/21/19 - Moved new ThinkSystem certifications to separate array so that all new certifications can be run independently
 *                      of all other ThinkSystem certifications; moved ts_basemodules_cert_string and basetools_cert_string
 *                      to "newer" array.
 * 05/28/19 - Removed older TA certifications that should not be tracked anymore (based on note from Cheryl dated 05/28/19).
 * 06/10/19 - Added additional preg_match strings for ALL user types.
 * 10/24/19 - Added addition array of role shortnames so that finding the role ids for all the roles will be easier.
 * 12/09/19 - Added the "TS-SE350-ES71911" certification.
 * 01/17/20 - Added a few missing certifications and the AMD certifications (TS-NE(0152)-ES41923, TS-SR635-ES71942,
 *                      TS-AMDBaseTools-ES51998, TS-AMDArchitecture-ES41999, and TS-SR655-ES71943).
 * PTR2019Q401 - @01 - 03/17/20 - For all PS / SD users, added custom submit assignment capability.
 * PTR2020Q109 - @02 - 05/05/20 - Added strings for SD TAM users; added string for user profile field "Accesstype2".
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// $SESSION is required here.
// Lenovo ********************************************************************************
global $SESSION;


/**
 * Initializes all strings used by EBGLMS and loads them into $SESSION->EBGLMS->STRINGS.
 *
 *      IMPORTANT! $SESSION->EBGLMS MUST be set before calling (i.e. no check for EBGLMS).
 *
 * @param N/A
 *
 * @return N/A
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/14/18 - Initial writing.
 *
 **/

 // Lenovo ********************************************************************************
// Setup temporary reference to $STRINGS.
//      To use: $tmp = $SESSION->EBGLMS->STRINGS
// Lenovo ********************************************************************************
$tmp = $SESSION->EBGLMS->STRINGS;

// Lenovo ********************************************************************************
// Setup the third-level $STRINGS->access_all_geos global variable.
//      To use: $strings = $SESSION->EBGLMS->STRINGS->access_all_geos
// Lenovo ********************************************************************************
$tmp->access_all_geos = new stdClass();
$tmp->access_all_geos = get_string('access_all_geos', 'local_swtc');

// Lenovo ********************************************************************************
// Setup the third-level $STRINGS->top_level_categories global variable.
//      To use: $strings = $SESSION->EBGLMS->STRINGS->top_level_categories
// Lenovo ********************************************************************************
$tmp->top_level_categories = new stdClass();
$tmp->top_level_categories = get_strings(array('gtp_portfolio', 'lenovo_portfolio', 'serviceprovider_portfolio', 'lenovointernal_portfolio','lenovosharedresources_portfolio', 'maintech_portfolio', 'ibm_portfolio', 'asp_portfolio', 'premiersupport_portfolio',
'servicedelivery_portfolio', 'sitehelp_portfolio', 'curriculums_portfolio'), 'local_swtc');

// Lenovo ********************************************************************************
// Setup the third-level $STRINGS->capabilities global variable.
//      To use: $strings = $SESSION->EBGLMS->STRINGS->capabilities
// @01 - For all PS / SD users, added custom submit assignment capability.
// Lenovo ********************************************************************************
$tmp->capabilities = new stdClass();
$tmp->capabilities = get_strings(array('cap_ebg_access_gtp_portfolio', 'cap_ebg_access_lenovo_portfolio',
																// 'cap_ebg_access_lenovoandibm_portfolio', 'cap_ebg_access_serviceprovider_portfolio', 'cap_ebg_access_lenovointernal_portfolio',
																'cap_ebg_access_serviceprovider_portfolio', 'cap_ebg_access_lenovointernal_portfolio',
                                                                'cap_ebg_access_lenovosharedresources',
																'cap_ebg_access_maintech_portfolio', 'cap_ebg_access_ibm_portfolio', 'cap_ebg_access_asp_portfolio',
                                                                'cap_ebg_access_premiersupport_portfolio', 'cap_ebg_access_servicedelivery_portfolio', 'cap_ebg_access_sitehelp_portfolio', 'cap_ebg_access_curriculums_portfolio',
                                                                'cap_ebg_view_curriculums', 'cap_ebg_view_mgmt_reports',
                                                                'cap_ebg_view_stud_reports',
                                                                'cap_ebg_mod_assign_submit_premiersupport',
                                                                'cap_ebg_mod_assign_submit_servicedelivery'), 'local_swtc');

// Lenovo ********************************************************************************
// Setup all the role and access strings for all the EBGLMS user types.
//      To use: $ibm = $SESSION->EBGLMS->STRINGS->ibm
// Lenovo ********************************************************************************
// Load all the IBM strings.
$tmp->ibm = new stdClass();
$tmp->ibm = get_strings(array('access_ibm_stud', 																												// Access strings.
                                                    'access_ibm_pregmatch',
                                                    'role_ibm_stud', 'role_ibm_student'), 'local_swtc');																// Role strings.

// Load all the AV GTP strings.
$tmp->av_gtp = new stdClass();
$tmp->av_gtp = get_strings(array('access_av_gtp', 'access_av_gtp_admin', 'access_av_gtp_siteadmin', 'access_av_gtp_inst', 'access_av_gtp_stud'), 'local_swtc');	// Access strings.

// Load all the IM GTP strings.
$tmp->im_gtp = new stdClass();
$tmp->im_gtp = get_strings(array('access_im_gtp', 'access_im_gtp_admin', 'access_im_gtp_siteadmin', 'access_im_gtp_inst', 'access_im_gtp_stud'), 'local_swtc');	// Access srings.

// Load all the LQ GTP strings.
$tmp->lq_gtp = new stdClass();
$tmp->lq_gtp = get_strings(array('access_lq_gtp', 'access_lq_gtp_admin', 'access_lq_gtp_siteadmin', 'access_lq_gtp_inst', 'access_lq_gtp_stud'), 'local_swtc');		// Access strings.

// Load all the Lenovo strings.
$tmp->lenovo =  new stdClass();
$tmp->lenovo = get_strings(array('access_lenovo_admin', 'access_lenovo_siteadmin', 'access_lenovo_inst', 'access_lenovo_stud', //Access strings.
        'access_lenovo_pregmatch_stud', 'access_lenovo_pregmatch_admin', 'access_lenovo_pregmatch_siteadmin',                        // Pregmatch strings.
                                                            'role_lenovo_administrator', 'role_lenovo_siteadministrator', 'role_lenovo_admin', 'role_lenovo_instructor', 'role_lenovo_inst',	// Role strings.
                                                            'role_lenovo_student',  'role_lenovo_stud'), 'local_swtc');															// Role strings.

// Load all the Service Provider strings.
$tmp->serviceprovider = new stdClass();
$tmp->serviceprovider = get_strings(array('access_serviceprovider_stud',																			// Access strings.
                                                                        'access_serviceprovider_pregmatch_stud',
                                                                        'role_serviceprovider_stud', 'role_serviceprovider_student'), 'local_swtc');	// Role strings.

// Load all the Maintech strings.
$tmp->maintech = new stdClass();
$tmp->maintech = get_strings(array('access_maintech_stud',																								// Access strings.
                                                            'access_maintech_pregmatch_stud',
                                                            'role_maintech_stud', 'role_maintech_student'), 'local_swtc');									// Role strings.

// Load all the ASP/Maintech strings.
$tmp->asp_maintech = new stdClass();
$tmp->asp_maintech = get_strings(array('access_asp_maintech_stud',																					// Access strings.
                                                                    'access_asp_maintech_pregmatch_stud',
                                                            'role_asp_maintech_stud', 'role_asp_maintech_student'), 'local_swtc');					// Role strings.

// Load all the PremierSupport strings.
$tmp->premiersupport = new stdClass();
$tmp->premiersupport = get_strings(array(
                                        // User access types and pregmatch strings for each.
                                        'access_premiersupport_stud',
                                        'access_premiersupport_admin',
                                        'access_premiersupport_geoadmin',
                                        'access_premiersupport_siteadmin',
                                        'access_premiersupport_mgr',
                                        'access_premiersupport_pregmatch',
                                        'access_premiersupport_pregmatch_stud',
                                        'access_premiersupport_pregmatch_mgr',
                                        'access_premiersupport_pregmatch_admin',
                                        'access_premiersupport_pregmatch_geoadmin',
                                        'access_premiersupport_pregmatch_siteadmin',
                                        // For listing group members.
                                        'groups_premiersupport_all_group_participants',
                                        'groups_premiersupport_group_participants',
                                        'groups_premiersupport_group_type_participants',
                                        'groups_premiersupport_all_geo_participants',
                                        'groups_premiersupport_geo_participants',
                                        'groups_premiersupport_geo_type_participants',
                                        'groups_premiersupport_all_participants',
                                        'groups_premiersupport_all_type_participants',
                                        // Students.
                                        'access_premiersupport_us1_stud',
                                        'access_premiersupport_ca1_stud',
                                        'access_premiersupport_la1_stud',
                                        'access_premiersupport_ap1_stud',
                                        'access_premiersupport_em1_stud',
                                        'access_premiersupport_us2_stud',
                                        'access_premiersupport_ca2_stud',
                                        'access_premiersupport_la2_stud',
                                        'access_premiersupport_ap2_stud',
                                        'access_premiersupport_em2_stud',
                                        'access_premiersupport_us3_stud',
                                        'access_premiersupport_ca3_stud',
                                        'access_premiersupport_la3_stud',
                                        'access_premiersupport_ap3_stud',
                                        'access_premiersupport_em3_stud',
                                        'access_premiersupport_us4_stud',
                                        'access_premiersupport_ca4_stud',
                                        'access_premiersupport_la4_stud',
                                        'access_premiersupport_ap4_stud',
                                        'access_premiersupport_em4_stud',
                                        'access_premiersupport_us5_stud',
                                        'access_premiersupport_ca5_stud',
                                        'access_premiersupport_la5_stud',
                                        'access_premiersupport_ap5_stud',
                                        'access_premiersupport_em5_stud',
                                        // Managers.
                                        'access_premiersupport_us1_mgr',
                                        'access_premiersupport_ca1_mgr',
                                        'access_premiersupport_la1_mgr',
                                        'access_premiersupport_ap1_mgr',
                                        'access_premiersupport_em1_mgr',
                                        'access_premiersupport_us2_mgr',
                                        'access_premiersupport_ca2_mgr',
                                        'access_premiersupport_la2_mgr',
                                        'access_premiersupport_ap2_mgr',
                                        'access_premiersupport_em2_mgr',
                                        'access_premiersupport_us3_mgr',
                                        'access_premiersupport_ca3_mgr',
                                        'access_premiersupport_la3_mgr',
                                        'access_premiersupport_ap3_mgr',
                                        'access_premiersupport_em3_mgr',
                                        'access_premiersupport_us4_mgr',
                                        'access_premiersupport_ca4_mgr',
                                        'access_premiersupport_la4_mgr',
                                        'access_premiersupport_ap4_mgr',
                                        'access_premiersupport_em4_mgr',
                                        'access_premiersupport_us5_mgr',
                                        'access_premiersupport_ca5_mgr',
                                        'access_premiersupport_la5_mgr',
                                        'access_premiersupport_ap5_mgr',
                                        'access_premiersupport_em5_mgr',
                                        // Administrators.
                                        'access_premiersupport_us1_admin',
                                        'access_premiersupport_ca1_admin',
                                        'access_premiersupport_la1_admin',
                                        'access_premiersupport_ap1_admin',
                                        'access_premiersupport_em1_admin',
                                        'access_premiersupport_us2_admin',
                                        'access_premiersupport_ca2_admin',
                                        'access_premiersupport_la2_admin',
                                        'access_premiersupport_ap2_admin',
                                        'access_premiersupport_em2_admin',
                                        'access_premiersupport_us3_admin',
                                        'access_premiersupport_ca3_admin',
                                        'access_premiersupport_la3_admin',
                                        'access_premiersupport_ap3_admin',
                                        'access_premiersupport_em3_admin',
                                        'access_premiersupport_us4_admin',
                                        'access_premiersupport_ca4_admin',
                                        'access_premiersupport_la4_admin',
                                        'access_premiersupport_ap4_admin',
                                        'access_premiersupport_em4_admin',
                                        'access_premiersupport_us5_admin',
                                        'access_premiersupport_ca5_admin',
                                        'access_premiersupport_la5_admin',
                                        'access_premiersupport_ap5_admin',
                                        'access_premiersupport_em5_admin',
                                        // GEO administrators.
                                        'access_premiersupport_us_geoadmin',
                                        'access_premiersupport_ca_geoadmin',
                                        'access_premiersupport_la_geoadmin',
                                        'access_premiersupport_ap_geoadmin',
                                        'access_premiersupport_em_geoadmin',
                                        // GEO site administrators.
                                        'access_premiersupport_us_siteadmin',
                                        'access_premiersupport_ca_siteadmin',
                                        'access_premiersupport_la_siteadmin',
                                        'access_premiersupport_ap_siteadmin',
                                        'access_premiersupport_em_siteadmin',
                                        // Site administrator.
                                        'access_premiersupport_siteadmin',
                                        // Roles.
                                        'role_premiersupport_stud',
                                        'role_premiersupport_student',
                                        'role_premiersupport_geoadmin',
                                        'role_premiersupport_geoadministrator',
                                        'role_premiersupport_siteadmin',
                                        'role_premiersupport_siteadministrator',
                                        'role_premiersupport_admin',
                                        'role_premiersupport_administrator',
                                        'role_premiersupport_mgr',
                                        'role_premiersupport_manager',
                                        // Cohorts and pregmatch strings for each.
                                        'cohort_premiersupport_us1_studs',
                                        'cohort_premiersupport_ca1_studs',
                                        'cohort_premiersupport_la1_studs',
                                        'cohort_premiersupport_ap1_studs',
                                        'cohort_premiersupport_em1_studs',
                                        'cohort_premiersupport_us2_studs',
                                        'cohort_premiersupport_ca2_studs',
                                        'cohort_premiersupport_la2_studs',
                                        'cohort_premiersupport_ap2_studs',
                                        'cohort_premiersupport_em2_studs',
                                        'cohort_premiersupport_us3_studs',
                                        'cohort_premiersupport_ca3_studs',
                                        'cohort_premiersupport_la3_studs',
                                        'cohort_premiersupport_ap3_studs',
                                        'cohort_premiersupport_em3_studs',
                                        'cohort_premiersupport_us4_studs',
                                        'cohort_premiersupport_ca4_studs',
                                        'cohort_premiersupport_la4_studs',
                                        'cohort_premiersupport_ap4_studs',
                                        'cohort_premiersupport_em4_studs',
                                        'cohort_premiersupport_us5_studs',
                                        'cohort_premiersupport_ca5_studs',
                                        'cohort_premiersupport_la5_studs',
                                        'cohort_premiersupport_ap5_studs',
                                        'cohort_premiersupport_em5_studs',
                                        'cohort_premiersupport_us1_mgrs',
                                        'cohort_premiersupport_ca1_mgrs',
                                        'cohort_premiersupport_la1_mgrs',
                                        'cohort_premiersupport_ap1_mgrs',
                                        'cohort_premiersupport_em1_mgrs',
                                        'cohort_premiersupport_us2_mgrs',
                                        'cohort_premiersupport_ca2_mgrs',
                                        'cohort_premiersupport_la2_mgrs',
                                        'cohort_premiersupport_ap2_mgrs',
                                        'cohort_premiersupport_em2_mgrs',
                                        'cohort_premiersupport_us3_mgrs',
                                        'cohort_premiersupport_ca3_mgrs',
                                        'cohort_premiersupport_la3_mgrs',
                                        'cohort_premiersupport_ap3_mgrs',
                                        'cohort_premiersupport_em3_mgrs',
                                        'cohort_premiersupport_us4_mgrs',
                                        'cohort_premiersupport_ca4_mgrs',
                                        'cohort_premiersupport_la4_mgrs',
                                        'cohort_premiersupport_ap4_mgrs',
                                        'cohort_premiersupport_em4_mgrs',
                                        'cohort_premiersupport_us5_mgrs',
                                        'cohort_premiersupport_ca5_mgrs',
                                        'cohort_premiersupport_la5_mgrs',
                                        'cohort_premiersupport_ap5_mgrs',
                                        'cohort_premiersupport_em5_mgrs',
                                        'cohort_premiersupport_us1_admins',
                                        'cohort_premiersupport_ca1_admins',
                                        'cohort_premiersupport_la1_admins',
                                        'cohort_premiersupport_ap1_admins',
                                        'cohort_premiersupport_em1_admins',
                                        'cohort_premiersupport_us2_admins',
                                        'cohort_premiersupport_ca2_admins',
                                        'cohort_premiersupport_la2_admins',
                                        'cohort_premiersupport_ap2_admins',
                                        'cohort_premiersupport_em2_admins',
                                        'cohort_premiersupport_us3_admins',
                                        'cohort_premiersupport_ca3_admins',
                                        'cohort_premiersupport_la3_admins',
                                        'cohort_premiersupport_ap3_admins',
                                        'cohort_premiersupport_em3_admins',
                                        'cohort_premiersupport_us4_admins',
                                        'cohort_premiersupport_ca4_admins',
                                        'cohort_premiersupport_la4_admins',
                                        'cohort_premiersupport_ap4_admins',
                                        'cohort_premiersupport_em4_admins',
                                        'cohort_premiersupport_us5_admins',
                                        'cohort_premiersupport_ca5_admins',
                                        'cohort_premiersupport_la5_admins',
                                        'cohort_premiersupport_ap5_admins',
                                        'cohort_premiersupport_em5_admins',
                                        'cohort_premiersupport_ap_geoadmins',
                                        'cohort_premiersupport_ca_geoadmins',
                                        'cohort_premiersupport_em_geoadmins',
                                        'cohort_premiersupport_la_geoadmins',
                                        'cohort_premiersupport_us_geoadmins',
                                        'cohort_premiersupport_ap_siteadmins',
                                        'cohort_premiersupport_ca_siteadmins',
                                        'cohort_premiersupport_em_siteadmins',
                                        'cohort_premiersupport_la_siteadmins',
                                        'cohort_premiersupport_us_siteadmins',
                                        'cohort_premiersupport_siteadmins',
                                        'cohort_premiersupport_pregmatch_geoadmins',
                                        'cohort_premiersupport_pregmatch_siteadmins',
                                        'cohort_premiersupport_pregmatch_admins',
                                        'cohort_premiersupport_pregmatch_mgrs',
                                        'cohort_premiersupport_pregmatch_studs'), 'local_swtc');

// Load all the ServiceDelivery strings.
// @02 - 05/05/20 - Added strings for SD TAM users.
$tmp->servicedelivery = new stdClass();
$tmp->servicedelivery = get_strings(array(
                                        // User access types and pregmatch strings for each.
                                        'access_lenovo_servicedelivery_stud',
                                        'access_lenovo_servicedelivery_mgr',
                                        'access_lenovo_servicedelivery_admin',
                                        'access_lenovo_servicedelivery_siteadmin',
                                        'access_lenovo_servicedelivery_geoadmin',
                                        'access_lenovo_servicedelivery_pregmatch',
                                        'access_lenovo_servicedelivery_pregmatch_stud',
                                        'access_lenovo_servicedelivery_pregmatch_mgr',
                                        'access_lenovo_servicedelivery_pregmatch_admin',
                                        'access_lenovo_servicedelivery_pregmatch_siteadmin',
                                        'access_lenovo_servicedelivery_pregmatch_geoadmin',
                                        // For listing group members.
                                        'groups_lenovo_servicedelivery_all_group_participants',
                                        'groups_lenovo_servicedelivery_group_participants',
                                        'groups_lenovo_servicedelivery_group_type_participants',
                                        'groups_lenovo_servicedelivery_all_geo_participants',
                                        'groups_lenovo_servicedelivery_geo_participants',
                                        'groups_lenovo_servicedelivery_geo_type_participants',
                                        'groups_lenovo_servicedelivery_all_participants',
                                        'groups_lenovo_servicedelivery_all_type_participants',
                                        // Students.
                                        'access_lenovo_servicedelivery_us1_stud',
                                        'access_lenovo_servicedelivery_ca1_stud',
                                        'access_lenovo_servicedelivery_la1_stud',
                                        'access_lenovo_servicedelivery_ap1_stud',
                                        'access_lenovo_servicedelivery_em1_stud',
                                        'access_lenovo_servicedelivery_us2_stud',
                                        'access_lenovo_servicedelivery_ca2_stud',
                                        'access_lenovo_servicedelivery_la2_stud',
                                        'access_lenovo_servicedelivery_ap2_stud',
                                        'access_lenovo_servicedelivery_em2_stud',
                                        'access_lenovo_servicedelivery_us3_stud',
                                        'access_lenovo_servicedelivery_ca3_stud',
                                        'access_lenovo_servicedelivery_la3_stud',
                                        'access_lenovo_servicedelivery_ap3_stud',
                                        'access_lenovo_servicedelivery_em3_stud',
                                        'access_lenovo_servicedelivery_us4_stud',
                                        'access_lenovo_servicedelivery_ca4_stud',
                                        'access_lenovo_servicedelivery_la4_stud',
                                        'access_lenovo_servicedelivery_ap4_stud',
                                        'access_lenovo_servicedelivery_em4_stud',
                                        'access_lenovo_servicedelivery_us5_stud',
                                        'access_lenovo_servicedelivery_ca5_stud',
                                        'access_lenovo_servicedelivery_la5_stud',
                                        'access_lenovo_servicedelivery_ap5_stud',
                                        'access_lenovo_servicedelivery_em5_stud',
                                        // Managers.
                                        'access_lenovo_servicedelivery_us1_mgr',
                                        'access_lenovo_servicedelivery_ca1_mgr',
                                        'access_lenovo_servicedelivery_la1_mgr',
                                        'access_lenovo_servicedelivery_ap1_mgr',
                                        'access_lenovo_servicedelivery_em1_mgr',
                                        'access_lenovo_servicedelivery_us2_mgr',
                                        'access_lenovo_servicedelivery_ca2_mgr',
                                        'access_lenovo_servicedelivery_la2_mgr',
                                        'access_lenovo_servicedelivery_ap2_mgr',
                                        'access_lenovo_servicedelivery_em2_mgr',
                                        'access_lenovo_servicedelivery_us3_mgr',
                                        'access_lenovo_servicedelivery_ca3_mgr',
                                        'access_lenovo_servicedelivery_la3_mgr',
                                        'access_lenovo_servicedelivery_ap3_mgr',
                                        'access_lenovo_servicedelivery_em3_mgr',
                                        'access_lenovo_servicedelivery_us4_mgr',
                                        'access_lenovo_servicedelivery_ca4_mgr',
                                        'access_lenovo_servicedelivery_la4_mgr',
                                        'access_lenovo_servicedelivery_ap4_mgr',
                                        'access_lenovo_servicedelivery_em4_mgr',
                                        'access_lenovo_servicedelivery_us5_mgr',
                                        'access_lenovo_servicedelivery_ca5_mgr',
                                        'access_lenovo_servicedelivery_la5_mgr',
                                        'access_lenovo_servicedelivery_ap5_mgr',
                                        'access_lenovo_servicedelivery_em5_mgr',
                                        // Administrators.
                                        'access_lenovo_servicedelivery_us1_admin',
                                        'access_lenovo_servicedelivery_ca1_admin',
                                        'access_lenovo_servicedelivery_la1_admin',
                                        'access_lenovo_servicedelivery_ap1_admin',
                                        'access_lenovo_servicedelivery_em1_admin',
                                        'access_lenovo_servicedelivery_us2_admin',
                                        'access_lenovo_servicedelivery_ca2_admin',
                                        'access_lenovo_servicedelivery_la2_admin',
                                        'access_lenovo_servicedelivery_ap2_admin',
                                        'access_lenovo_servicedelivery_em2_admin',
                                        'access_lenovo_servicedelivery_us3_admin',
                                        'access_lenovo_servicedelivery_ca3_admin',
                                        'access_lenovo_servicedelivery_la3_admin',
                                        'access_lenovo_servicedelivery_ap3_admin',
                                        'access_lenovo_servicedelivery_em3_admin',
                                        'access_lenovo_servicedelivery_us4_admin',
                                        'access_lenovo_servicedelivery_ca4_admin',
                                        'access_lenovo_servicedelivery_la4_admin',
                                        'access_lenovo_servicedelivery_ap4_admin',
                                        'access_lenovo_servicedelivery_em4_admin',
                                        'access_lenovo_servicedelivery_us5_admin',
                                        'access_lenovo_servicedelivery_ca5_admin',
                                        'access_lenovo_servicedelivery_la5_admin',
                                        'access_lenovo_servicedelivery_ap5_admin',
                                        'access_lenovo_servicedelivery_em5_admin',
                                        // GEO administrators.
                                        'access_lenovo_servicedelivery_us_geoadmin',
                                        'access_lenovo_servicedelivery_ca_geoadmin',
                                        'access_lenovo_servicedelivery_la_geoadmin',
                                        'access_lenovo_servicedelivery_ap_geoadmin',
                                        'access_lenovo_servicedelivery_em_geoadmin',
                                        // GEO site administrators.
                                        'access_lenovo_servicedelivery_us_siteadmin',
                                        'access_lenovo_servicedelivery_ca_siteadmin',
                                        'access_lenovo_servicedelivery_la_siteadmin',
                                        'access_lenovo_servicedelivery_ap_siteadmin',
                                        'access_lenovo_servicedelivery_em_siteadmin',
                                        // Site administrator.
                                        'access_lenovo_servicedelivery_siteadmin',
                                        // Roles.
                                        'role_servicedelivery_stud',
                                        'role_servicedelivery_student',
                                        'role_servicedelivery_siteadmin',
                                        'role_servicedelivery_siteadministrator',
                                        'role_servicedelivery_geoadmin',
                                        'role_servicedelivery_geoadministrator',
                                        'role_servicedelivery_admin',
                                        'role_servicedelivery_administrator',
                                        'role_servicedelivery_mgr',
                                        'role_servicedelivery_manager',
                                        // Cohorts and pregmatch strings for each.
                                        'cohort_lenovo_servicedelivery_us1_studs',
                                        'cohort_lenovo_servicedelivery_ca1_studs',
                                        'cohort_lenovo_servicedelivery_la1_studs',
                                        'cohort_lenovo_servicedelivery_ap1_studs',
                                        'cohort_lenovo_servicedelivery_em1_studs',
                                        'cohort_lenovo_servicedelivery_us2_studs',
                                        'cohort_lenovo_servicedelivery_ca2_studs',
                                        'cohort_lenovo_servicedelivery_la2_studs',
                                        'cohort_lenovo_servicedelivery_ap2_studs',
                                        'cohort_lenovo_servicedelivery_em2_studs',
                                        'cohort_lenovo_servicedelivery_us3_studs',
                                        'cohort_lenovo_servicedelivery_ca3_studs',
                                        'cohort_lenovo_servicedelivery_la3_studs',
                                        'cohort_lenovo_servicedelivery_ap3_studs',
                                        'cohort_lenovo_servicedelivery_em3_studs',
                                        'cohort_lenovo_servicedelivery_us4_studs',
                                        'cohort_lenovo_servicedelivery_ca4_studs',
                                        'cohort_lenovo_servicedelivery_la4_studs',
                                        'cohort_lenovo_servicedelivery_ap4_studs',
                                        'cohort_lenovo_servicedelivery_em4_studs',
                                        'cohort_lenovo_servicedelivery_us5_studs',
                                        'cohort_lenovo_servicedelivery_ca5_studs',
                                        'cohort_lenovo_servicedelivery_la5_studs',
                                        'cohort_lenovo_servicedelivery_ap5_studs',
                                        'cohort_lenovo_servicedelivery_em5_studs',
                                        'cohort_lenovo_servicedelivery_us1_mgrs',
                                        'cohort_lenovo_servicedelivery_ca1_mgrs',
                                        'cohort_lenovo_servicedelivery_la1_mgrs',
                                        'cohort_lenovo_servicedelivery_ap1_mgrs',
                                        'cohort_lenovo_servicedelivery_em1_mgrs',
                                        'cohort_lenovo_servicedelivery_us2_mgrs',
                                        'cohort_lenovo_servicedelivery_ca2_mgrs',
                                        'cohort_lenovo_servicedelivery_la2_mgrs',
                                        'cohort_lenovo_servicedelivery_ap2_mgrs',
                                        'cohort_lenovo_servicedelivery_em2_mgrs',
                                        'cohort_lenovo_servicedelivery_us3_mgrs',
                                        'cohort_lenovo_servicedelivery_ca3_mgrs',
                                        'cohort_lenovo_servicedelivery_la3_mgrs',
                                        'cohort_lenovo_servicedelivery_ap3_mgrs',
                                        'cohort_lenovo_servicedelivery_em3_mgrs',
                                        'cohort_lenovo_servicedelivery_us4_mgrs',
                                        'cohort_lenovo_servicedelivery_ca4_mgrs',
                                        'cohort_lenovo_servicedelivery_la4_mgrs',
                                        'cohort_lenovo_servicedelivery_ap4_mgrs',
                                        'cohort_lenovo_servicedelivery_em4_mgrs',
                                        'cohort_lenovo_servicedelivery_us5_mgrs',
                                        'cohort_lenovo_servicedelivery_ca5_mgrs',
                                        'cohort_lenovo_servicedelivery_la5_mgrs',
                                        'cohort_lenovo_servicedelivery_ap5_mgrs',
                                        'cohort_lenovo_servicedelivery_em5_mgrs',
                                        'cohort_lenovo_servicedelivery_us1_admins',
                                        'cohort_lenovo_servicedelivery_ca1_admins',
                                        'cohort_lenovo_servicedelivery_la1_admins',
                                        'cohort_lenovo_servicedelivery_ap1_admins',
                                        'cohort_lenovo_servicedelivery_em1_admins',
                                        'cohort_lenovo_servicedelivery_us2_admins',
                                        'cohort_lenovo_servicedelivery_ca2_admins',
                                        'cohort_lenovo_servicedelivery_la2_admins',
                                        'cohort_lenovo_servicedelivery_ap2_admins',
                                        'cohort_lenovo_servicedelivery_em2_admins',
                                        'cohort_lenovo_servicedelivery_us3_admins',
                                        'cohort_lenovo_servicedelivery_ca3_admins',
                                        'cohort_lenovo_servicedelivery_la3_admins',
                                        'cohort_lenovo_servicedelivery_ap3_admins',
                                        'cohort_lenovo_servicedelivery_em3_admins',
                                        'cohort_lenovo_servicedelivery_us4_admins',
                                        'cohort_lenovo_servicedelivery_ca4_admins',
                                        'cohort_lenovo_servicedelivery_la4_admins',
                                        'cohort_lenovo_servicedelivery_ap4_admins',
                                        'cohort_lenovo_servicedelivery_em4_admins',
                                        'cohort_lenovo_servicedelivery_us5_admins',
                                        'cohort_lenovo_servicedelivery_ca5_admins',
                                        'cohort_lenovo_servicedelivery_la5_admins',
                                        'cohort_lenovo_servicedelivery_ap5_admins',
                                        'cohort_lenovo_servicedelivery_em5_admins',
                                        'cohort_lenovo_servicedelivery_ap_geoadmins',
                                        'cohort_lenovo_servicedelivery_ca_geoadmins',
                                        'cohort_lenovo_servicedelivery_em_geoadmins',
                                        'cohort_lenovo_servicedelivery_la_geoadmins',
                                        'cohort_lenovo_servicedelivery_us_geoadmins',
                                        'cohort_lenovo_servicedelivery_ap_siteadmins',
                                        'cohort_lenovo_servicedelivery_ca_siteadmins',
                                        'cohort_lenovo_servicedelivery_em_siteadmins',
                                        'cohort_lenovo_servicedelivery_la_siteadmins',
                                        'cohort_lenovo_servicedelivery_us_siteadmins',
                                        'cohort_lenovo_servicedelivery_siteadmins',
                                        'cohort_lenovo_servicedelivery_pregmatch_siteadmins',
                                        'cohort_lenovo_servicedelivery_pregmatch_geoadmins',
                                        'cohort_lenovo_servicedelivery_pregmatch_admins',
                                        'cohort_lenovo_servicedelivery_pregmatch_mgrs',
                                        'cohort_lenovo_servicedelivery_pregmatch_studs',
                                        // @02 - 05/05/20 - Added strings for SD TAM users.
                                        'access_lenovo_servicedelivery_tam_pregmatch',
                                        'access_lenovo_servicedelivery_tam_pregmatch_stud',
                                        'access_lenovo_servicedelivery_tam_pregmatch_mgr',
                                        'access_lenovo_servicedelivery_tam_pregmatch_admin',
                                        'access_lenovo_servicedelivery_tam_pregmatch_geoadmin',
                                        'access_lenovo_servicedelivery_tam_pregmatch_siteadmin',
                                        'groups_lenovo_servicedelivery_tam_all_group_participants',
                                        'groups_lenovo_servicedelivery_tam_group_participants',
                                        'groups_lenovo_servicedelivery_tam_group_type_participants',
                                        'groups_lenovo_servicedelivery_tam_all_geo_participants',
                                        'groups_lenovo_servicedelivery_tam_geo_participants',
                                        'groups_lenovo_servicedelivery_tam_geo_type_participants',
                                        'groups_lenovo_servicedelivery_tam_all_participants',
                                        'groups_lenovo_servicedelivery_tam_all_type_participants',
                                        'access_lenovo_servicedelivery_tam_us1_stud',
                                        'access_lenovo_servicedelivery_tam_ca1_stud',
                                        'access_lenovo_servicedelivery_tam_la1_stud',
                                        'access_lenovo_servicedelivery_tam_ap1_stud',
                                        'access_lenovo_servicedelivery_tam_em1_stud',
                                        'access_lenovo_servicedelivery_tam_us2_stud',
                                        'access_lenovo_servicedelivery_tam_ca2_stud',
                                        'access_lenovo_servicedelivery_tam_la2_stud',
                                        'access_lenovo_servicedelivery_tam_ap2_stud',
                                        'access_lenovo_servicedelivery_tam_em2_stud',
                                        'access_lenovo_servicedelivery_tam_us3_stud',
                                        'access_lenovo_servicedelivery_tam_ca3_stud',
                                        'access_lenovo_servicedelivery_tam_la3_stud',
                                        'access_lenovo_servicedelivery_tam_ap3_stud',
                                        'access_lenovo_servicedelivery_tam_em3_stud',
                                        'access_lenovo_servicedelivery_tam_us4_stud',
                                        'access_lenovo_servicedelivery_tam_ca4_stud',
                                        'access_lenovo_servicedelivery_tam_la4_stud',
                                        'access_lenovo_servicedelivery_tam_ap4_stud',
                                        'access_lenovo_servicedelivery_tam_em4_stud',
                                        'access_lenovo_servicedelivery_tam_us5_stud',
                                        'access_lenovo_servicedelivery_tam_ca5_stud',
                                        'access_lenovo_servicedelivery_tam_la5_stud',
                                        'access_lenovo_servicedelivery_tam_ap5_stud',
                                        'access_lenovo_servicedelivery_tam_em5_stud',
                                        'access_lenovo_servicedelivery_tam_us1_mgr',
                                        'access_lenovo_servicedelivery_tam_ca1_mgr',
                                        'access_lenovo_servicedelivery_tam_la1_mgr',
                                        'access_lenovo_servicedelivery_tam_ap1_mgr',
                                        'access_lenovo_servicedelivery_tam_em1_mgr',
                                        'access_lenovo_servicedelivery_tam_us2_mgr',
                                        'access_lenovo_servicedelivery_tam_ca2_mgr',
                                        'access_lenovo_servicedelivery_tam_la2_mgr',
                                        'access_lenovo_servicedelivery_tam_ap2_mgr',
                                        'access_lenovo_servicedelivery_tam_em2_mgr',
                                        'access_lenovo_servicedelivery_tam_us3_mgr',
                                        'access_lenovo_servicedelivery_tam_ca3_mgr',
                                        'access_lenovo_servicedelivery_tam_la3_mgr',
                                        'access_lenovo_servicedelivery_tam_ap3_mgr',
                                        'access_lenovo_servicedelivery_tam_em3_mgr',
                                        'access_lenovo_servicedelivery_tam_us4_mgr',
                                        'access_lenovo_servicedelivery_tam_ca4_mgr',
                                        'access_lenovo_servicedelivery_tam_la4_mgr',
                                        'access_lenovo_servicedelivery_tam_ap4_mgr',
                                        'access_lenovo_servicedelivery_tam_em4_mgr',
                                        'access_lenovo_servicedelivery_tam_us5_mgr',
                                        'access_lenovo_servicedelivery_tam_ca5_mgr',
                                        'access_lenovo_servicedelivery_tam_la5_mgr',
                                        'access_lenovo_servicedelivery_tam_ap5_mgr',
                                        'access_lenovo_servicedelivery_tam_em5_mgr',
                                        'access_lenovo_servicedelivery_tam_us1_admin',
                                        'access_lenovo_servicedelivery_tam_ca1_admin',
                                        'access_lenovo_servicedelivery_tam_la1_admin',
                                        'access_lenovo_servicedelivery_tam_ap1_admin',
                                        'access_lenovo_servicedelivery_tam_em1_admin',
                                        'access_lenovo_servicedelivery_tam_us2_admin',
                                        'access_lenovo_servicedelivery_tam_ca2_admin',
                                        'access_lenovo_servicedelivery_tam_la2_admin',
                                        'access_lenovo_servicedelivery_tam_ap2_admin',
                                        'access_lenovo_servicedelivery_tam_em2_admin',
                                        'access_lenovo_servicedelivery_tam_us3_admin',
                                        'access_lenovo_servicedelivery_tam_ca3_admin',
                                        'access_lenovo_servicedelivery_tam_la3_admin',
                                        'access_lenovo_servicedelivery_tam_ap3_admin',
                                        'access_lenovo_servicedelivery_tam_em3_admin',
                                        'access_lenovo_servicedelivery_tam_us4_admin',
                                        'access_lenovo_servicedelivery_tam_ca4_admin',
                                        'access_lenovo_servicedelivery_tam_la4_admin',
                                        'access_lenovo_servicedelivery_tam_ap4_admin',
                                        'access_lenovo_servicedelivery_tam_em4_admin',
                                        'access_lenovo_servicedelivery_tam_us5_admin',
                                        'access_lenovo_servicedelivery_tam_ca5_admin',
                                        'access_lenovo_servicedelivery_tam_la5_admin',
                                        'access_lenovo_servicedelivery_tam_ap5_admin',
                                        'access_lenovo_servicedelivery_tam_em5_admin',
                                        'access_lenovo_servicedelivery_tam_us_geoadmin',
                                        'access_lenovo_servicedelivery_tam_ca_geoadmin',
                                        'access_lenovo_servicedelivery_tam_la_geoadmin',
                                        'access_lenovo_servicedelivery_tam_ap_geoadmin',
                                        'access_lenovo_servicedelivery_tam_em_geoadmin',
                                        'access_lenovo_servicedelivery_tam_us_siteadmin',
                                        'access_lenovo_servicedelivery_tam_ca_siteadmin',
                                        'access_lenovo_servicedelivery_tam_la_siteadmin',
                                        'access_lenovo_servicedelivery_tam_ap_siteadmin',
                                        'access_lenovo_servicedelivery_tam_em_siteadmin',
                                        'access_lenovo_servicedelivery_tam_siteadmin',
                                        'cohort_lenovo_servicedelivery_tam_us1_studs',
                                        'cohort_lenovo_servicedelivery_tam_ca1_studs',
                                        'cohort_lenovo_servicedelivery_tam_la1_studs',
                                        'cohort_lenovo_servicedelivery_tam_ap1_studs',
                                        'cohort_lenovo_servicedelivery_tam_em1_studs',
                                        'cohort_lenovo_servicedelivery_tam_us2_studs',
                                        'cohort_lenovo_servicedelivery_tam_ca2_studs',
                                        'cohort_lenovo_servicedelivery_tam_la2_studs',
                                        'cohort_lenovo_servicedelivery_tam_ap2_studs',
                                        'cohort_lenovo_servicedelivery_tam_em2_studs',
                                        'cohort_lenovo_servicedelivery_tam_us3_studs',
                                        'cohort_lenovo_servicedelivery_tam_ca3_studs',
                                        'cohort_lenovo_servicedelivery_tam_la3_studs',
                                        'cohort_lenovo_servicedelivery_tam_ap3_studs',
                                        'cohort_lenovo_servicedelivery_tam_em3_studs',
                                        'cohort_lenovo_servicedelivery_tam_us4_studs',
                                        'cohort_lenovo_servicedelivery_tam_ca4_studs',
                                        'cohort_lenovo_servicedelivery_tam_la4_studs',
                                        'cohort_lenovo_servicedelivery_tam_ap4_studs',
                                        'cohort_lenovo_servicedelivery_tam_em4_studs',
                                        'cohort_lenovo_servicedelivery_tam_us5_studs',
                                        'cohort_lenovo_servicedelivery_tam_ca5_studs',
                                        'cohort_lenovo_servicedelivery_tam_la5_studs',
                                        'cohort_lenovo_servicedelivery_tam_ap5_studs',
                                        'cohort_lenovo_servicedelivery_tam_em5_studs',
                                        'cohort_lenovo_servicedelivery_tam_us1_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ca1_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_la1_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ap1_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_em1_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_us2_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ca2_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_la2_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ap2_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_em2_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_us3_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ca3_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_la3_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ap3_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_em3_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_us4_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ca4_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_la4_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ap4_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_em4_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_us5_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ca5_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_la5_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_ap5_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_em5_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_us1_admins',
                                        'cohort_lenovo_servicedelivery_tam_ca1_admins',
                                        'cohort_lenovo_servicedelivery_tam_la1_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap1_admins',
                                        'cohort_lenovo_servicedelivery_tam_em1_admins',
                                        'cohort_lenovo_servicedelivery_tam_us2_admins',
                                        'cohort_lenovo_servicedelivery_tam_ca2_admins',
                                        'cohort_lenovo_servicedelivery_tam_la2_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap2_admins',
                                        'cohort_lenovo_servicedelivery_tam_em2_admins',
                                        'cohort_lenovo_servicedelivery_tam_us3_admins',
                                        'cohort_lenovo_servicedelivery_tam_ca3_admins',
                                        'cohort_lenovo_servicedelivery_tam_la3_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap3_admins',
                                        'cohort_lenovo_servicedelivery_tam_em3_admins',
                                        'cohort_lenovo_servicedelivery_tam_us4_admins',
                                        'cohort_lenovo_servicedelivery_tam_ca4_admins',
                                        'cohort_lenovo_servicedelivery_tam_la4_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap4_admins',
                                        'cohort_lenovo_servicedelivery_tam_em4_admins',
                                        'cohort_lenovo_servicedelivery_tam_us5_admins',
                                        'cohort_lenovo_servicedelivery_tam_ca5_admins',
                                        'cohort_lenovo_servicedelivery_tam_la5_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap5_admins',
                                        'cohort_lenovo_servicedelivery_tam_em5_admins',
                                        'cohort_lenovo_servicedelivery_tam_ap_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_ca_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_em_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_la_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_us_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_ap_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_ca_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_em_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_la_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_us_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_pregmatch_siteadmins',
                                        'cohort_lenovo_servicedelivery_tam_pregmatch_geoadmins',
                                        'cohort_lenovo_servicedelivery_tam_pregmatch_admins',
                                        'cohort_lenovo_servicedelivery_tam_pregmatch_mgrs',
                                        'cohort_lenovo_servicedelivery_tam_pregmatch_studs'), 'local_swtc');

// Load all the SelfSupport strings.
$tmp->selfsupport = new stdClass();
$tmp->selfsupport = get_strings(array('access_selfsupport_stud',                                                            // Access strings.
                                                            'role_selfsupport_student'), 'local_swtc');						            // Role strings.

// Load all the SpecialAccess strings.
// $tmp->specialaccess = new stdClass();
// $tmp->specialaccess = get_strings(array('access_special_user', 'access_specialaccess_stud',                          // Access strings.
//                                                    'role_specialaccess_student'), 'local_swtc');						            // Role strings.

// Load all the generic GTP role type strings.
$tmp->generic_role = new stdClass();
$tmp->generic_role = get_strings(array('role_gtp_admin', 'role_gtp_administrator', 'role_gtp_inst', 'role_gtp_instructor',	// Role strings.
                                                                    'role_gtp_siteadmin', 'role_gtp_siteadministrator', 'role_gtp_stud', 'role_gtp_student'), 'local_swtc'); // Role strings.

// Load all the SiteHelp strings.
$tmp->sitehelp = new stdClass();
$tmp->sitehelp = get_strings(array('access_sitehelp_stud',                                                            // Access strings.
                                                            'role_sitehelp_student'), 'local_swtc');						            // Role strings.

// Lenovo ********************************************************************************
// List of ThinkSystem certifications.
// 05/21/19 - Moved new ThinkSystem certifications to separate array so that all new certifications can be run independently
//                      of all other ThinkSystem certifications.
// Lenovo ********************************************************************************
// To use: $this->ts_cert->ts_racks_cert_string
$tmp->ts_cert = new stdClass();
$tmp->ts_cert = get_strings(array('ts_basemodules_cert_string', 'basetools_cert_string', 'ts_cloudos_es41782', 'ts_db_400d_800d_es41729', 'ts_db610s_es41727', 'ts_db620s_es41728', 'ts_ds_6200_4200_2200_es41607', 'ts_ne_1032_10032_2572_es41673', 'ts_ne_0152_es41923', 'ts_ne1032_es41735', 'ts_ne2572_es41773', 'ts_sd530_es71629', 'ts_sd650_es71709', 'ts_sn550_es71741', 'ts_sn850_es71740', 'ts_sr250_es71935', 'ts_sr530_es71749', 'ts_sr550_es71750', 'ts_sr570_es71790', 'ts_sr590_es71791', 'ts_sr630_es71744', 'ts_sr635_es71942', 'ts_sr650_es71743', 'ts_sr655_es71943', 'ts_sr670_es71878', 'ts_sr850_es71718', 'ts_sr860_es71754', 'ts_sr950_es71736', 'ts_st250_es71934', 'ts_st50_es71875', 'ts_se350_es71911', 'ts_st550_st558_es71764', 'ts_xclarity_essentials_es21787', 'ts_xclarity_es71043', 'ts_amdbasetools_es51998', 'ts_amdarchitecture_es41999', 'oth_sd530_n400_es71538_cert_string', 'rs_g8052_es5237_cert_string', 'rs_g8272_es41483_cert_string', 'rs_g8296_es41288_cert_string'), 'local_swtc');

// Lenovo ********************************************************************************
// List of ThinkSystem certifications.
// 05/21/19 - Moved new ThinkSystem certifications to separate array so that all new certifications can be run independently
//                      of all other ThinkSystem certifications.
// Lenovo ********************************************************************************
// To use: $this->ts_cert_old->ts_racks_cert_string
$tmp->ts_cert_old = new stdClass();
$tmp->ts_cert_old = get_strings(array('ts_racks_cert_string', 'ts_bladenodes_cert_string', 'ts_highdensity_cert_string', 'ts_towers_cert_string', 'ts_highend_cert_string', 'ts_missioncritical_cert_string', 'ts_de_storage_es71931_cert_string', 'ts_dm_storage_es71914_cert_string'), 'local_swtc');

// Lenovo ********************************************************************************
// List of ThinkAgile certifications.
// 05/28/19 - Removed older TA certifications that should not be tracked anymore (based on note from Cheryl dated 05/28/19).
// Lenovo ********************************************************************************
// To use: $this->ta_cert->ta_hx_st550_cert_string
$tmp->ta_cert = new stdClass();
// $tmp->ta_cert = get_strings(array('ta_hx_st550_cert_string', 'ta_hx_sd530_cert_string', 'ta_hx_sr630_cert_string', 'ta_hx_sr650_cert_string',  'ta_hx_sr950_cert_string', 'ta_thinkagile_sxn_sxm_cert_string', 'ta_vx_sd530_cert_string', 'ta_vx_sr630_cert_string', 'ta_vx_sr650_cert_string', 'ta_cp_sd530_cert_string', 'ta_cp_series_es21956_cert_string', 'ta_cp_series_es41868_cert_string', 'ta_cp_tools_es61921_cert_string', 'ta_hx_series_es41641_cert_string', 'ta_sx_nutanix_es41785_cert_string', 'ta_sx_azure_es41765_cert_string', 'ta_vx_series_es41800_cert_string'), 'local_swtc');
$tmp->ta_cert = get_strings(array('ta_cp_series_es21956_cert_string', 'ta_cp_series_es41868_cert_string', 'ta_cp_tools_es61921_cert_string', 'ta_hx_series_es41641_cert_string', 'ta_sx_nutanix_es41785_cert_string', 'ta_sx_azure_es41765_cert_string', 'ta_vx_series_es41800_cert_string'), 'local_swtc');

// Lenovo ********************************************************************************
// List of legacy certifications.
// Lenovo ********************************************************************************
// To use: $this->leg_cert->leg_basemodules_cert_string
$tmp->leg_cert = new stdClass();
$tmp->leg_cert = get_strings(array('leg_san_cert_string', 'leg_bladectr_cert_string', 'leg_flex_cert_string', 'leg_basemodules_cert_string', 'leg_nextscale_cert_string', 'leg_thinkserver_cert_string', 'leg_rx3550_3650_cert_string', 'leg_rx3850_3950_x5_cert_string', 'leg_rx3850_3950_x6_cert_string'), 'local_swtc');

// To use: $leg_cert_none_string->leg_none
// $leg_cert_none_string = get_strings(array('leg_none'), 'local_swtc')

// To use: $this->userprofile->profile_field_accesstype
$tmp->userprofile = new stdClass();
$tmp->userprofile = get_strings(array('profile_field_accesstype', 'profile_field_sbtechnicianid', 'profile_field_tscertifications', 'profile_field_geo', 'profile_field_legcertifications', 'profile_field_tscertdates', 'profile_field_legcertdates', 'profile_field_tacertifications', 'profile_field_tacertdates', 'profile_field_accesstype2'), 'local_swtc');

// To use: $this->shortname->shortname_Accesstype
$tmp->shortname = new stdClass();
$tmp->shortname = get_strings(array('shortname_Accesstype', 'shortname_sbtechnicianid', 'shortname_tscertifications', 'shortname_geo', 'shortname_legcertifications', 'shortname_tscertdates', 'shortname_legcertdates', 'shortname_accesstype2'), 'local_swtc');

// Lenovo ********************************************************************************
// Setup the third-level $STRINGS->role_shortnames global variable.
//      To use: $strings = $SESSION->EBGLMS->STRINGS->role_shortnames
// Lenovo ********************************************************************************
$tmp->role_shortnames = new stdClass();
$tmp->role_shortnames = get_strings(array('role_lenovo_instructor',
                                                                        'role_lenovo_student',
                                                                        'role_lenovo_administrator',
                                                                        'role_lenovo_siteadmin',
                                                                        'role_gtp_instructor',
                                                                        'role_gtp_student',
                                                                        'role_gtp_administrator',
                                                                        'role_gtp_siteadministrator',
                                                                        'role_ibm_student',
                                                                        'role_serviceprovider_student',
                                                                        'role_maintech_student',
                                                                        'role_asp_maintech_student',
                                                                        'role_selfsupport_student',
                                                                        'role_premiersupport_siteadministrator',
                                                                        'role_premiersupport_geoadministrator',
                                                                        'role_premiersupport_administrator',
                                                                        'role_premiersupport_manager',
                                                                        'role_premiersupport_student',
                                                                        'role_servicedelivery_siteadministrator',
                                                                       'role_servicedelivery_geoadministrator',
                                                                       'role_servicedelivery_administrator',
                                                                       'role_servicedelivery_manager',
                                                                        'role_servicedelivery_student'), 'local_swtc');
