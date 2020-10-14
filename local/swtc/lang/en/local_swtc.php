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
 * @copyright  2015 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 03/14/16 - Added 'Lenovo Internal Portfolio' string.
 * 03/29/16 - Added 'Lenovo Shared Resources (Master)' and 'LenInternalSharedResources' strings.
 * 05/11/16 - Added 'Maintech Portfolio' strings.
 * 06/10/16 - Added missing 'role_maintech_stud' string.
 * 08/11/16 - Removed 'Arrow' and 'Global Knowledge'.
 *	08/25/16 - Added "IBM Portfolio"; removed "Lenovo and IBM Portfolio"; added "Set Access type"
 *	09/08/16 - Added error message if customized user profile field "Access type" is blank (checked in /user/editadvanced.php)
 *	09/27/16 - Added "gtp-siteadmin", "gtp-siteadministrator", "AV-GTP-siteadmin", "LQ-GTP-siteadmin", and "IM-GTP-siteadmin".
 *	10/05/16 - Added hybrid role of ASP / Maintech student.
 * 04/26/17 - v15 (cont) - Continued work on adding "Lenovo Services Education" menu to Navigation block for all Lenovo-administrators
 *                                     and site administrators.
 * 08/25/17 - Adding strings for ServiceBench support.
 * 10/05/17 - Adding strings for ASP Portfolio.
 * 10/12/17 - Adding certification strings for ServiceBench support.
 * 10/19/17 - Adding additional certification strings for ServiceBench support.
 * 10/24/17 - Changed user profile and certification strings for ServiceBench support.
 * 11/30/17 - Added user profile shortnames for all fields (without "user_profile_" prefix). Note: MUST be exact shortname as defined in LMS.
 * 12/12/17 - Added strings for adding cron jobs to Moodle for LMS->SB (export) and SB->LMS (import).
 * 12/14/17 - Added strings for ServiceBench legacy certification support.
 * 02/12/18 - Added Legacy certifications RX3550_3650, RX3850_3950_X5, and RX3850_3950_X6; sunset Legacy certifications
 *                          RxLowEnd and RxHighEnd.
 * 03/03/18 - Added PremierSupport portfolio and all associated information.
 * 03/03/18 - Adding Special-access student type.
 * 03/18/18 - Added strings for ServiceBench email support.
 * 04/10/18 - Added TS_MissionCritical (course ES71736).
 * 04/28/18 - Added Self-support.
 * 06/03/18 - Added debug strings.
 * 06/11/18 - Added 'PremSuppTempCourse' (short name) string for the 'PremSuppTempCourse' template course.
 * 06/18/18 - Changed "RXEntrySAN" to "RxEntrySAN" and changed "RXNeXtScale" to "RXNextScale".
 * 07/05/18 - Changing names of scheduled tasks (adding Lenovo to name).
 * 07/27/18 - Added ASP-manager role.
 * 07/30/18 - Added strings for new user accounts (/login/signup_form.php).
 * 08/13/18 - Added strings for user account invitations.
 * 08/14/18 - Added strings for SiteHelp portfolio.
 * 08/27/18 - Added strings for PremierSupport cohort names.
 * 09/26/18 - Added capability strings for customized customsql report category access.
 *	10/08/18 - Added strings to support "Curriculums" tab in "My courses".
 * 11/06/18 - Changed "Curriculums" strings so they can be used in user tours.
 * 11/07/18 - Added additional access type strings for all PremierSupport user types.
 * 11/12/18 - Added strings for ServiceDelivery and PracticalActivities portfolios.
 * 11/13/17 - Added strings for ThinkSystem DE Storage and ThinkSystem DM storage certifications.
 * 11/20/18 - Changed curriculum names and access type names for ServiceDelivery; removed Special-access student type.
 * 11/26/18 - Added strings for curriculums cron jobs.
 * 11/28/18 - Added capability strings for viewing curriculums and viewing reports.
 * 12/04/18 - Added strings to support "swtcbatchemail" setting.
 * 01/03/19 - Added INVITE_RESET (to use if an invitation was expired by mistake).
 * 01/10/19 - Added Curriculums Portfolio.
 * 01/17/19 - Added additional strings for easier stripos comparisons.
 * 01/23/19 - Changed all PremierSupport and ServiceDelivery strings to conform to the standard "PS-<GEO><1-5>-whatever" and
 *						"SD-<GEO><1-5>-whatever"; added strings for all users to use in preg_match calls.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; added additional preg_match strings for matching
 *                      all SD-US users (regardless of group).
 * 02/28/19 - Adding additional PremierSupport and ServiceDelivery strings for "all GEO participants".
 * 03/01/19 - Added additional PremierSupport and ServiceDelivery strings for site administrator access types.
 * 03/03/19 - Fixed bug in preg_match strings (changed "[a-z]+[1-9]" to "[A-Z][A-Z]+[1-9]").
 * 03/04/19 - Added strings for customized PS/SD groups menu; added string that contains all the GEOs.
 * 03/06/19 - Modified PremierSupport and ServiceDelivery "all GEO participants"strings to add either students, managers, administrators,
 *                      or site administrators; added PS/SD GEO administrator role.
 * 03/08/19 - Added PS/SD GEO site administrator user access types.
 * 03/09/19 - Changed all strings containing "EMEA" (4-letters) to "EM" (2-letters) so that the preg_match strings will match.
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 03/31/19 - Removed all customized code referencing "PremSuppTempCourse".
 * 05/09/19 - Added preg_match strings for Lenovo-admins and Lenovo-siteadmins.
 * 05/16/19 - Adding ThinkAgile, and changing some current, certification names (per Cheryl).
 * 05/21/19 - Added TS_ALL_OLD and renamed some ThinkSystem certifications.
 * 05/28/19 - Removed older TA certifications that should not be tracked anymore (based on note from Cheryl dated 05/28/19).
 * 06/04/19 - Added "featured courses" multiple select listbox.
 * 06/10/19 - Added additional preg_match strings for ALL user types.
 * 06/14/19 - Updated preg_match strings for Maintech and ASP-Maintech to start match at the first character.
 * 07/15/19 - Changed 'TS-ST550/ST558-ES71764' to 'TS-ST550-ST558-ES71764'.
 * 07/31/19 - Added maximum of 12 featured courses per section string.
 * 08/01/19 - Changed "featured courses" to "suggested courses".
 * 10/08/19 - Added string for shared simulator course (lensharedsimulators_shortname).
 * 10/24/19 - In /course/edit_form.php, changed the "Course start date" string (startdate), and associated help string, to customized text
 *                      reflecting how Lenovo Education Services uses this field.
 * 10/24/19 - For Moodle 3.7+, changing all Lenovo capability strings from dashes (ebg-access-gtp-portfolio) to
 *                  underscores (ebg_access_gtp_portfolio).
 * 11/14/19 - Added global "Lenovo" setting so that it can be checked in Moodle core files to call customized Lenovo functions.
 * 12/09/19 - Added the "TS-SE350-ES71911" certification.
 * 12/11/19 - Changed the certification string "Oth-SD530-N400-ES71538" to "Oth-SD530/N400-ES71538".
 * 01/17/20 - Added a few missing certifications and the AMD certifications (TS-NE(0152)-ES41923, TS-SR635-ES71942,
 *                      TS-AMDBaseTools-ES51998, TS-AMDArchitecture-ES41999, and TS-SR655-ES71943).
 * 01/31/20 - Added strings to support local_swtc_capture_click and local_swtc_capture_enrollment to record an event.
 * PTR2019Q401 - @01 - 03/17/20 - For all PS / SD users, added custom submit assignment capability.
 * PTR2020Q108 - @02 - 04/27/20 - Moved some DCG custom course format strings to /local/swtc/lang/en/local_swtc.php
 *                      to remove duplication of common strings; added all strings required for swtcevents course format.
 * PTR2020Q109 - @03 - 05/05/20 - Added strings for SD TAM users; uppper-cased GEO abbreviations in all PS/SD user access
 *                      types; added field for user profile field "Accesstype2".
 *
 */

// Lenovo *******************************************************************************
// 10/24/19 - In /course/edit_form.php, changed the "Course start date" string (startdate), and associated help string, to customized text
//                      reflecting how Lenovo Education Services uses this field.
// Lenovo *******************************************************************************
$string['swtcpubdate'] = 'Course publication date';
$string['swtcpubdate_help'] = 'Set this date to the publication (GA) date of the course. In other words, the date the course will be moved from the Lenovo Internal Portfolio to any of the external portfolios.';

// Lenovo *******************************************************************************
// Added strings to support "Curriculums" tab in "My courses".
// Lenovo *******************************************************************************
$string['curriculums'] = 'Curriculums';
$string['mycurriculums'] = 'My Curriculums';
$string['allcurriculums'] = 'All Curriculums';

// Lenovo *******************************************************************************
// PremierSupport curriculum courses.
//      TODO: Either make these generic so everyone can use or add "premiersupport" to the names so no one else will use.
// Lenovo *******************************************************************************
$string['converged_curriculum'] = 'Converged';
$string['nutanix_curriculum'] = 'Nutanix';
$string['cutomer_service_curriculum'] = 'Customer Service Techniques';
$string['microsoft_azure_curriculum'] = 'Microsoft Azure';
$string['high_density_curriculum'] = 'High Density';
$string['networking_curriculum'] = 'Networking';
$string['lxca_curriculum'] = 'LXCA';
$string['premier_curriculum'] = 'Premier';
$string['registrations_curriculum'] = 'Registrations';
$string['thinksystem_curriculum'] = 'ThinkSystem';
$string['vmware_curriculum'] = 'VMware';

// Lenovo *******************************************************************************
// ServiceDelivery curriculum courses.
// Lenovo *******************************************************************************
$string['servicedelivery_base_curriculum'] = 'Base';
$string['servicedelivery_products_curriculum'] = 'Products';
$string['servicedelivery_appliancesolution_curriculum'] = 'Appliance / Solution';

$string['errornotenrolledincurriculum'] = 'You are not enrolled in any curriculum courses.';

$string['mycurriculums_desc1'] = 'The <strong>My Curriculums</strong> page shows you at-a-glance all the curriculum courses you are enrolled in (if any) and your current completion status for each.';
$string['mycurriculums_desc2'] = 'The <strong>All Curriculums</strong> tab lists the curriculum course codes (ex: PSC0002), course names (ex: Converged Curriculum), and an overall completion status for that curriculum.';
$string['mycurriculums_desc3'] = 'Clicking the <strong>Course full name</strong> hyperlink displays the completion report for all courses included in the curriculum.';
$string['mycurriculums_desc4'] = 'Additionally, each curriculum can be viewed individually by clicking its corresponding tab. Each curriculum tab includes the completion report along with a button that will navigate to the curriculum course.';

$string['coursecode'] = 'Course code';
$string['coursename'] = 'Course name';

// Add suggested courses listbox.
$string['suggestedcourses'] = 'Suggested courses';
$string['suggestedcourses_header1'] = 'Suggested courses are advertised on the site in two ways: <strong>sitewide</strong> and <strong>per user type</strong>.';
$string['suggestedcourses_header2'] = 'Courses selected in the <strong>sitewide</strong> section are advertised throughout the etire site. In other words, <strong>ALL</strong> users of <strong>ALL</strong> types can view these advertisments.';
$string['suggestedcourses_header3'] = 'In the <strong>user type</strong> sections, the suggested courses selected are only shown to users of that type. For example, any suggested courses selected in the PremierSupport section will only be advertised to all PremierSupport users.';
$string['suggestedcourses_header4'] = '<strong>Note:</strong> Only the first <strong>12</strong> courses per section will be used.';
$string['suggestedcourses_courses'] = 'The currently suggested course(s) are: {$a->courses}.';
$string['suggestedcourses_none'] = 'There are no suggested courses for this user type.';
$string['suggestedcourses_help'] = 'Select the courses(s) to feature throughout the site.';

$string['sitewidesuggestedcourses'] = 'Sitewide suggested courses';
$string['sitewidesuggestedcourses_header'] = 'Select the courses(s) to feature throughout the site.';
$string['ibmsuggestedcourses'] = 'IBM suggested courses';
$string['ibmsuggestedcourses_header'] = 'Select the courses(s) to feature for IBM users.';
$string['lenovosuggestedcourses'] = 'Lenovo suggested courses';
$string['lenovosuggestedcourses_header'] = 'Select the courses(s) to feature for Lenovo users.';
$string['serviceprovidersuggestedcourses'] = 'Service provider suggested courses';
$string['serviceprovidersuggestedcourses_header'] = 'Select the courses(s) to feature for Service provider users.';
$string['premiersupportsuggestedcourses'] = 'Premier Support suggested courses';
$string['premiersupportsuggestedcourses_header'] = 'Select the courses(s) to feature for Premier Support users.';
$string['servicedeliverysuggestedcourses'] = 'Service delivery suggested courses';
$string['servicedeliverysuggestedcourses_header'] = 'Select the courses(s) to feature for Service delivery users.';
$string['maintechsuggestedcourses'] = 'Maintech suggested courses';
$string['maintechsuggestedcourses_header'] = 'Select the courses(s) to feature for Maintech users.';
$string['aspsuggestedcourses'] = 'ASP suggested courses';
$string['aspsuggestedcourses_header'] = 'Select the courses(s) to feature for ASP users.';

// Lenovo *******************************************************************************
// For use in locking access to customsql report categories (/report/customsql).
// Lenovo *******************************************************************************
$string['customsql:viewallcats'] = 'View custom queries reports in all categories';
$string['customsql:viewpremiersupportcat'] = 'View custom queries reports in PremierSupport category';

// Lenovo *******************************************************************************
// For use in new user account invitation (/auth/emailadmin).
// Lenovo *******************************************************************************
// User account invitation invite strings.
$string['requestinvitation'] = 'Request invitation';
$string['newuserinvitation'] = 'New User Account Invitation';
$string['requestnewuserinvitation'] = 'Request my invitation';
$string['invitationsent'] = 'Invitation sent';

// User account invitation status strings.
$string['invitationactive'] = 'Invitation active';
$string['invitationused'] = 'Invitation used';
$string['invitationexpired'] = 'Invitation expired';
$string['invitationinvalid'] = 'Invitation invalid';

// User account invitation email message strings.
$string['default_subject'] = 'Lenovo DCG LMS New User Account Invitation for {$a}';
$string['subject'] = 'Subject';
$string['emailmsgtxt'] =
    'INSTRUCTIONS:' . "\n" .
    '------------------------------------------------------------' . "\n" .
    'You have been invited to access the site: {$a->fullname}. You will ' .
    'need to log in to confirm your access to the site. Be advised that by ' .
    'clicking on the site access link provided in this ' .
    'email you are acknowledging that:' . "\n" .
    ' --you are the person to whom this email was addressed and for whom this' .
    '   invitation is intended;' . "\n" .
    ' --the link below will expire on ({$a->expiration}).' . "\n\n" .
    'ACCESS LINK:' . "\n" .
    '------------------------------------------------------------' . "\n" .
    '{$a->inviteurl}' . "\n\n" .
    'If you believe that you have received this message in error or are in need ' .
    'of assistance, please contact: {$a->supportemail}.';
$string['newuserinvitation_desc1'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. Enter the email address you want the invitation email sent to. When you receive the email, click the hyperlink provided in the email to create your user account.';
$string['newuserinvitation_desc1a'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>.';
$string['newuserinvitation_desc2'] = 'Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['newuserinvitation_desc2a'] = '<strong>IBM</strong> and <strong>Lenovo</strong> personnel may use this form to request access to the site. If you are a <strong>Lenovo Authorized Service Provider (ASP)</strong>, please contact your ASP consultant and they can assist you with access. New accounts will be confirmed within 1-2 business days.';
$string['newuserinvitation_desc3'] = 'When your account is confirmed, your password will be automatically generated and sent to the email address that you enter below.';
$string['newuserinvitation_desc3a'] = 'Enter the email address you want the invitation email sent to. When you receive the email, click the hyperlink provided in the email to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['newuserinvitation_desc4a'] = '<p><strong>IBM</strong> and <strong>Lenovo</strong> personnel may use this form to request access to the site. If you are a <strong>Lenovo Authorized Service Provider (ASP)</strong>, please contact your ASP consultant and they can assist you with access. New accounts will be confirmed within 1-2 business days.</p>';
$string['invitationsent_desc1'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. Your personalized invitation has been sent to <strong>{$a}</strong>. Click the hyperlink provided in the email to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['status_invite_active_message'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. An invitation for <strong>{$a->email}</strong> was requested on {$a->timesent} and can still be used until {$a->expiration}. As a reminder, the invitation hyperlink is :' . "\n" .
'{$a->inviteurl}' . "\n\n" .
'Click the hyperlink to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['status_invite_used_message'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. An invitation using the email address <strong>{$a->email}</strong> was accepted on {$a->timeused}. This email address cannot be used again. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';
$string['status_invite_expired_message'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. An invitation for <strong>{$a->email}</strong> was requested on {$a->timesent} and expired on  {$a->expiration}. If you still require access, navigate to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong> and click on <strong>Create new account</strong>. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';
$string['status_invite_invalid_message'] = 'Thank-you for requesting access to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong>. A problem was encountered processing this invitation. If you still require access, navigate to the <strong>Lenovo DCG Services Education Learning Management System (LMS)</strong> and click on <strong>Create new account</strong>. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';

// Invite history strings.
$string['invitehistory'] = 'Invitation history';
$string['noinvitehistory'] = 'No invites sent out yet';
$string['historyid'] = 'ID';
$string['historyinvitee'] = 'Invitee';
$string['historyuserid'] = 'Userid';
$string['historystatus'] = 'Status';
$string['historydatesent'] = 'Date sent';
$string['historydateused'] = 'Date used';
$string['historydateexpiration'] = 'Expiration date';
$string['historyactions'] = 'Actions';
$string['historyexpires_in'] = 'expires in';
$string['used_by'] = ' by {$a->username} ({$a->useremail}) on {$a->timeused}';


// Invite status strings.
$string['status_invite_invalid'] = 'Invalid';
$string['status_invite_expired'] = 'Expired';
$string['status_invite_used'] = 'Accepted';
$string['status_invite_used_noaccess'] = '(no longer has access)';
$string['status_invite_used_expiration'] = '(access ends on {$a})';
$string['status_invite_revoked'] = 'Revoked';
$string['status_invite_resent'] = 'Resent';
$string['status_invite_active'] = 'Active';
$string['status_invite_reset'] = 'Reset';
$string['status_invite_still_active'] = 'Invitation still active';
$string['status_invite_already_used'] = 'Invitation has already been used';

// Invite action strings.
$string['action_expire_invite'] = 'Expire invite';
$string['expire_invite_sucess'] = 'Invitation successfully expired';
$string['action_extend_invite'] = 'Extend invite';
$string['extend_invite_sucess'] = 'Invitation successfully extended';
$string['action_reset_invite'] = 'Reset invite';
$string['reset_invite_sucess'] = 'Invitation successfully reset';

// Strings for datetimehelpers.
$string['less_than_x_seconds'] = 'less than {$a} seconds';
$string['half_minute'] = 'half a minute';
$string['less_minute'] = 'less than a minute';
$string['a_minute'] = '1 minute';
$string['x_minutes'] = '{$a} minutes';
$string['about_hour'] = 'about 1 hour';
$string['about_x_hours'] = 'about {$a} hours';
$string['a_day'] = '1 day';
$string['x_days'] = '{$a} days';



$string['pluginname'] = 'swtc';
$string['configtitle'] = 'Lenovo EBG Server Education plugin';
$string['swtccustom'] = 'swtccustom';

$string['ebg_set_access_type'] = "Set Access type for all users";

// Options for the Type of course pull-down menu (taken from Lenovo EBG LMS custom course format).
// Also the names of the top level categories...
$string['gtp_portfolio'] = 'GTP Portfolio';
// $string['lenovoandibm_portfolio'] = 'Lenovo and IBM Portfolio';
$string['lenovo_portfolio'] = 'Lenovo Portfolio';
$string['serviceprovider_portfolio'] = 'Service Provider Portfolio';
$string['lenovointernal_portfolio'] = 'Lenovo Internal Portfolio';
$string['lenovosharedresources_portfolio'] = 'Lenovo Shared Resources (Master)';
$string['lensharedsimulators_shortname'] = 'ES10000';			                        // The actual short name for the shared simulator course.
$string['leninternalsharedresources'] = 'LenInternalSharedResources';			// The actual short name for the 'Shared Resources (Master)' course.
$string['sharedresources_coursename'] = 'Shared Resources (Master)';		// The actual name for the 'Shared Resources (Master)' course.
$string['maintech_portfolio'] = 'Maintech Portfolio';
$string['ibm_portfolio'] = 'IBM Portfolio';
$string['asp_portfolio'] = 'ASP Portfolio';
$string['premiersupport_portfolio'] = 'PremierSupport Portfolio';
$string['servicedelivery_portfolio'] = 'ServiceDelivery Portfolio';
$string['sitehelp_portfolio'] = 'Site Help Portfolio';
$string['curriculums_portfolio'] = 'Curriculums Portfolio';
$string['practicalactivities_portfolio'] = 'PracticalActivities Portfolio';

// $string['premsupptempcourse'] = 'PremSuppTempCourse';			// The actual short name for the 'PremSuppTempCourse' template course.
// $string['premsupptempcourse_coursename'] = 'PremierSupport Portfolio Template 1.0 (Master)';		// The actual name for the 'PremSuppTempCourse' template course.


// Important! String values based on Accesstype must match the customized user profile value 'Accesstype'...
// Generic role types...
$string['role_gtp_admin'] = 'gtp-admin';
$string['role_gtp_inst'] = 'gtp-inst';
$string['role_gtp_stud'] = 'gtp-stud';
$string['role_gtp_siteadmin'] = 'gtp-siteadmin';

$string['role_ibm_stud'] = 'ibm-stud';

$string['role_lenovo_siteadmin'] = 'lenovo-siteadmin';      // 02/13/19
$string['role_lenovo_admin'] = 'lenovo-admin';
$string['role_lenovo_inst'] = 'lenovo-inst';
$string['role_lenovo_stud'] = 'lenovo-stud';

$string['role_serviceprovider_stud'] = 'serviceprovider-stud';

$string['role_maintech_stud'] = 'maintech-stud';

$string['role_asp_maintech_stud'] = 'asp-maintech-stud';

$string['role_asp_mgr'] = 'asp-mgr';        // 07/27/18

$string['role_premiersupport_stud'] = 'premiersupport-stud';
$string['role_premiersupport_admin'] = 'premiersupport-admin';
$string['role_premiersupport_geoadmin'] = 'premiersupport-geoadmin';
$string['role_premiersupport_siteadmin'] = 'premiersupport-siteadmin';
$string['role_premiersupport_mgr'] = 'premiersupport-mgr';

$string['role_servicedelivery_stud'] = 'servicedelivery-stud';
$string['role_servicedelivery_admin'] = 'servicedelivery-admin';
$string['role_servicedelivery_geoadmin'] = 'servicedelivery-geoadmin';
$string['role_servicedelivery_siteadmin'] = 'servicedelivery-siteadmin';
$string['role_servicedelivery_mgr'] = 'servicedelivery-mgr';

// IBM user types and roles (shortnames)...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['access_ibm_stud'] = 'IBM-stud';
$string['role_ibm_student'] = 'ibm-student';
$string['access_ibm_pregmatch'] = '/IBM/i';

// GTP roles (shortnames) for ALL GTPs...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['role_gtp_administrator'] = 'gtp-administrator';
$string['role_gtp_instructor'] = 'gtp-instructor';
$string['role_gtp_student'] = 'gtp-student';
$string['role_gtp_siteadministrator'] = 'gtp-siteadministrator';

// Avnet (AV) GTP user types...
$string['access_av_gtp'] = 'AV-GTP';		// 01/17/19
$string['access_av_gtp_admin'] = 'AV-GTP-admin';
$string['access_av_gtp_inst'] = 'AV-GTP-inst';
$string['access_av_gtp_stud'] = 'AV-GTP-stud';
$string['access_av_gtp_siteadmin'] = 'AV-GTP-siteadmin';

// Ingram Micro (IM) GTP user types...
$string['access_im_gtp'] = 'IM-GTP';		// 01/17/19
$string['access_im_gtp_admin'] = 'IM-GTP-admin';
$string['access_im_gtp_inst'] = 'IM-GTP-inst';
$string['access_im_gtp_stud'] = 'IM-GTP-stud';
$string['access_im_gtp_siteadmin'] = 'IM-GTP-siteadmin';

// Learn Quest (LQ) GTP user types...
$string['access_lq_gtp'] = 'LQ-GTP';		// 01/17/19
$string['access_lq_gtp_admin'] = 'LQ-GTP-admin';
$string['access_lq_gtp_inst'] = 'LQ-GTP-inst';
$string['access_lq_gtp_stud'] = 'LQ-GTP-stud';
$string['access_lq_gtp_siteadmin'] = 'LQ-GTP-siteadmin';

// Lenovo user types and roles (shortnames)...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['access_lenovo_stud'] = 'Lenovo-stud';
$string['access_lenovo_pregmatch_stud'] = '/Lenovo-stud/i';                       // ex: Lenovo-stud
$string['role_lenovo_student'] = 'lenovo-student';

$string['access_lenovo_inst'] = 'Lenovo-inst';
$string['role_lenovo_instructor'] = 'lenovo-instructor';

$string['access_lenovo_admin'] = 'Lenovo-admin';
$string['access_lenovo_pregmatch_admin'] = '/Lenovo-admin/i';                       // ex: Lenovo-admin
$string['role_lenovo_administrator'] = 'lenovo-administrator';

$string['access_lenovo_siteadmin'] = 'Lenovo-siteadmin';                        // 02/13/19
$string['access_lenovo_pregmatch_siteadmin'] = '/Lenovo-siteadmin/i';                       // ex: Lenovo-siteadmin
$string['role_lenovo_siteadministrator'] = 'lenovo-siteadministrator';      // 02/13/19

// Service Provider user types and roles (shortnames)...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['access_serviceprovider_stud'] = 'ServiceProvider-stud';
$string['access_serviceprovider_pregmatch_stud'] = '/ServiceProvider-stud/i';                       // ex: ServiceProvider-stud
$string['role_serviceprovider_student'] = 'serviceprovider-student';

// Maintech user types and roles (shortnames)...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['access_maintech_stud'] = 'Maintech-stud';
$string['access_maintech_pregmatch_stud'] = '/^Maintech-stud/i';                       // ex: Maintech-stud
$string['role_maintech_student'] = 'maintech-student';

// ASP-Maintech user types and roles (shortnames)...
$string['access_asp_maintech_stud'] = 'ASP-Maintech-stud';
$string['access_asp_maintech_pregmatch_stud'] = '/^ASP-Maintech-stud/i';                       // ex: ASP-Maintech-stud
$string['role_asp_maintech_student'] = 'asp-maintech-student';

// ASP user types and roles (shortnames)...
$string['access_asp_mgr'] = 'ASP-mgr';
$string['role_asp_manager'] = 'asp-manager';

// Strings for customized PS/SD groups menu; added string that contains all the GEOs (comma separated).
$string['access_all_geos'] = 'AP, CA, LA, EM, US';

// Lenovo *******************************************************************************
// PremierSupport section.
// Lenovo *******************************************************************************
// PremierSupport user types and roles (shortnames) - main ones.
$string['access_premiersupport_stud'] = 'PremierSupport-stud';
$string['role_premiersupport_student'] = 'premiersupport-student';
$string['access_premiersupport_mgr'] = 'PremierSupport-mgr';
$string['role_premiersupport_manager'] = 'premiersupport-manager';
$string['access_premiersupport_admin'] = 'PremierSupport-admin';
$string['role_premiersupport_administrator'] = 'premiersupport-administrator';
$string['access_premiersupport_geoadmin'] = 'PremierSupport-geoadmin';
$string['role_premiersupport_geoadministrator'] = 'premiersupport-geoadministrator';
$string['access_premiersupport_siteadmin'] = 'PremierSupport-siteadmin';
$string['role_premiersupport_siteadministrator'] = 'premiersupport-siteadministrator';

$string['access_premiersupport_pregmatch'] = '/PremierSupport/i';                                                       // ex: PremierSupport
$string['access_premiersupport_pregmatch_stud'] = '/PremierSupport-[A-Z][A-Z]+[1-9]-stud/i';        // ex: PremierSupport-US1-stud
$string['access_premiersupport_pregmatch_mgr'] = '/PremierSupport-[A-Z][A-Z]+[1-9]-mgr/i';          // ex: PremierSupport-US1-mgr
$string['access_premiersupport_pregmatch_admin'] = '/PremierSupport-[A-Z][A-Z]+[1-9]-admin/i';  // ex: PremierSupport-US1-admin
$string['access_premiersupport_pregmatch_geoadmin'] = '/PremierSupport-[A-Z][A-Z]-geoadmin/i';  // ex: PremierSupport-US-geoadmin
$string['access_premiersupport_pregmatch_siteadmin'] = '/PremierSupport-siteadmin/i';                       // ex: PremierSupport-siteadmin

// PremierSupport manager and administrator reporting and preg_match strings.  {$a->fullname}
// Note: If the user is a PS admin, the following string is the groupsmenu[0] in /lib/groupslib.php:groups_print_course_menu.
//          If the user is a PS GEO admin, the following string is repeated for all GEOs (using 'access_all_geos') in a custom
//              groups menu (/lib/groupslib.php:groups_sort_menu_options).
// To be used with PremierSupport-admins (assumption is groupname is either "US1" or "US" depending on access type).
$string['groups_premiersupport_all_group_participants'] = 'All PremierSupport {$a->groupname} enrollments';
$string['groups_premiersupport_group_participants'] = 'PremierSupport {$a->groupname} enrollments';
$string['groups_premiersupport_group_type_participants'] = 'PremierSupport {$a->groupname} {$a->type} enrollments';
// To be used with PremierSupport-geoadmins
$string['groups_premiersupport_all_geo_participants'] = 'All PremierSupport {$a->groupname} enrollments';
// To be used with PremierSupport-siteadmins and PremierSupport-geoadmins
$string['groups_premiersupport_geo_participants'] = 'PremierSupport {$a->groupname} enrollments';
$string['groups_premiersupport_geo_type_participants'] = 'PremierSupport {$a->groupname} {$a->type} enrollments';
// To be used with PremierSupport-siteadmins
$string['groups_premiersupport_all_participants'] = 'All PremierSupport enrollments';
$string['groups_premiersupport_all_type_participants'] = 'All PremierSupport {$a->type} enrollments';   // ex: All PremierSupport student enrollments

// Lenovo *******************************************************************************
// PremierSupport GEO student types.
// Lenovo *******************************************************************************
$string['access_premiersupport_us1_stud'] = 'PremierSupport-US1-stud';
$string['access_premiersupport_ca1_stud'] = 'PremierSupport-CA1-stud';
$string['access_premiersupport_la1_stud'] = 'PremierSupport-LA1-stud';
$string['access_premiersupport_ap1_stud'] = 'PremierSupport-AP1-stud';
$string['access_premiersupport_em1_stud'] = 'PremierSupport-EM1-stud';

$string['access_premiersupport_us2_stud'] = 'PremierSupport-US2-stud';
$string['access_premiersupport_ca2_stud'] = 'PremierSupport-CA2-stud';
$string['access_premiersupport_la2_stud'] = 'PremierSupport-LA2-stud';
$string['access_premiersupport_ap2_stud'] = 'PremierSupport-AP2-stud';
$string['access_premiersupport_em2_stud'] = 'PremierSupport-EM2-stud';

$string['access_premiersupport_us3_stud'] = 'PremierSupport-US3-stud';
$string['access_premiersupport_ca3_stud'] = 'PremierSupport-CA3-stud';
$string['access_premiersupport_la3_stud'] = 'PremierSupport-LA3-stud';
$string['access_premiersupport_ap3_stud'] = 'PremierSupport-AP3-stud';
$string['access_premiersupport_em3_stud'] = 'PremierSupport-EM3-stud';

$string['access_premiersupport_us4_stud'] = 'PremierSupport-US4-stud';
$string['access_premiersupport_ca4_stud'] = 'PremierSupport-CA4-stud';
$string['access_premiersupport_la4_stud'] = 'PremierSupport-LA4-stud';
$string['access_premiersupport_ap4_stud'] = 'PremierSupport-AP4-stud';
$string['access_premiersupport_em4_stud'] = 'PremierSupport-EM4-stud';

$string['access_premiersupport_us5_stud'] = 'PremierSupport-US5-stud';
$string['access_premiersupport_ca5_stud'] = 'PremierSupport-CA5-stud';
$string['access_premiersupport_la5_stud'] = 'PremierSupport-LA5-stud';
$string['access_premiersupport_ap5_stud'] = 'PremierSupport-AP5-stud';
$string['access_premiersupport_em5_stud'] = 'PremierSupport-EM5-stud';

// Lenovo *******************************************************************************
// PremierSupport GEO manager types.
// Lenovo *******************************************************************************
$string['access_premiersupport_us1_mgr'] = 'PremierSupport-US1-mgr';
$string['access_premiersupport_ca1_mgr'] = 'PremierSupport-CA1-mgr';
$string['access_premiersupport_la1_mgr'] = 'PremierSupport-LA1-mgr';
$string['access_premiersupport_ap1_mgr'] = 'PremierSupport-AP1-mgr';
$string['access_premiersupport_em1_mgr'] = 'PremierSupport-EM1-mgr';

$string['access_premiersupport_us2_mgr'] = 'PremierSupport-US2-mgr';
$string['access_premiersupport_ca2_mgr'] = 'PremierSupport-CA2-mgr';
$string['access_premiersupport_la2_mgr'] = 'PremierSupport-LA2-mgr';
$string['access_premiersupport_ap2_mgr'] = 'PremierSupport-AP2-mgr';
$string['access_premiersupport_em2_mgr'] = 'PremierSupport-EM2-mgr';

$string['access_premiersupport_us3_mgr'] = 'PremierSupport-US3-mgr';
$string['access_premiersupport_ca3_mgr'] = 'PremierSupport-CA3-mgr';
$string['access_premiersupport_la3_mgr'] = 'PremierSupport-LA3-mgr';
$string['access_premiersupport_ap3_mgr'] = 'PremierSupport-AP3-mgr';
$string['access_premiersupport_em3_mgr'] = 'PremierSupport-EM3-mgr';

$string['access_premiersupport_us4_mgr'] = 'PremierSupport-US4-mgr';
$string['access_premiersupport_ca4_mgr'] = 'PremierSupport-CA4-mgr';
$string['access_premiersupport_la4_mgr'] = 'PremierSupport-LA4-mgr';
$string['access_premiersupport_ap4_mgr'] = 'PremierSupport-AP4-mgr';
$string['access_premiersupport_em4_mgr'] = 'PremierSupport-EM4-mgr';

$string['access_premiersupport_us5_mgr'] = 'PremierSupport-US5-mgr';
$string['access_premiersupport_ca5_mgr'] = 'PremierSupport-CA5-mgr';
$string['access_premiersupport_la5_mgr'] = 'PremierSupport-LA5-mgr';
$string['access_premiersupport_ap5_mgr'] = 'PremierSupport-AP5-mgr';
$string['access_premiersupport_em5_mgr'] = 'PremierSupport-EM5-mgr';

// Lenovo *******************************************************************************
// PremierSupport GEO administrator types.
// Lenovo *******************************************************************************
$string['access_premiersupport_us1_admin'] = 'PremierSupport-US1-admin';
$string['access_premiersupport_ca1_admin'] = 'PremierSupport-CA1-admin';
$string['access_premiersupport_la1_admin'] = 'PremierSupport-LA1-admin';
$string['access_premiersupport_ap1_admin'] = 'PremierSupport-AP1-admin';
$string['access_premiersupport_em1_admin'] = 'PremierSupport-EM1-admin';

$string['access_premiersupport_us2_admin'] = 'PremierSupport-US2-admin';
$string['access_premiersupport_ca2_admin'] = 'PremierSupport-CA2-admin';
$string['access_premiersupport_la2_admin'] = 'PremierSupport-LA2-admin';
$string['access_premiersupport_ap2_admin'] = 'PremierSupport-AP2-admin';
$string['access_premiersupport_em2_admin'] = 'PremierSupport-EM2-admin';

$string['access_premiersupport_us3_admin'] = 'PremierSupport-US3-admin';
$string['access_premiersupport_ca3_admin'] = 'PremierSupport-CA3-admin';
$string['access_premiersupport_la3_admin'] = 'PremierSupport-LA3-admin';
$string['access_premiersupport_ap3_admin'] = 'PremierSupport-AP3-admin';
$string['access_premiersupport_em3_admin'] = 'PremierSupport-EM3-admin';

$string['access_premiersupport_us4_admin'] = 'PremierSupport-US4-admin';
$string['access_premiersupport_ca4_admin'] = 'PremierSupport-CA4-admin';
$string['access_premiersupport_la4_admin'] = 'PremierSupport-LA4-admin';
$string['access_premiersupport_ap4_admin'] = 'PremierSupport-AP4-admin';
$string['access_premiersupport_em4_admin'] = 'PremierSupport-EM4-admin';

$string['access_premiersupport_us5_admin'] = 'PremierSupport-US5-admin';
$string['access_premiersupport_ca5_admin'] = 'PremierSupport-CA5-admin';
$string['access_premiersupport_la5_admin'] = 'PremierSupport-LA5-admin';
$string['access_premiersupport_ap5_admin'] = 'PremierSupport-AP5-admin';
$string['access_premiersupport_em5_admin'] = 'PremierSupport-EM5-admin';

// Lenovo *******************************************************************************
// PremierSupport GEO administrator user types.
// Lenovo *******************************************************************************
$string['access_premiersupport_us_geoadmin'] = 'PremierSupport-US-geoadmin';
$string['access_premiersupport_ca_geoadmin'] = 'PremierSupport-CA-geoadmin';
$string['access_premiersupport_la_geoadmin'] = 'PremierSupport-LA-geoadmin';
$string['access_premiersupport_ap_geoadmin'] = 'PremierSupport-AP-geoadmin';
$string['access_premiersupport_em_geoadmin'] = 'PremierSupport-EM-geoadmin';

// Lenovo *******************************************************************************
// PremierSupport GEO site administrator user types.
// Lenovo *******************************************************************************
$string['access_premiersupport_us_siteadmin'] = 'PremierSupport-US-siteadmin';
$string['access_premiersupport_ca_siteadmin'] = 'PremierSupport-CA-siteadmin';
$string['access_premiersupport_la_siteadmin'] = 'PremierSupport-LA-siteadmin';
$string['access_premiersupport_ap_siteadmin'] = 'PremierSupport-AP-siteadmin';
$string['access_premiersupport_em_siteadmin'] = 'PremierSupport-EM-siteadmin';

// PremierSupport site administrator user type.
$string['access_premiersupport_siteadmin'] = 'PremierSupport-siteadmin';

// PremierSupport cohort names...also used as course group names.
$string['cohort_premiersupport_pregmatch_admins'] = '/PS-[A-Z][A-Z]+[1-9]-admins/i';
$string['cohort_premiersupport_pregmatch_mgrs'] = '/PS-[A-Z][A-Z]+[1-9]-mgrs/i';
$string['cohort_premiersupport_pregmatch_studs'] = '/PS-[A-Z][A-Z]+[1-9]-studs/i';
$string['cohort_premiersupport_pregmatch_geoadmins'] = '/PS-[A-Z][A-Z]-geoadmins/i';
$string['cohort_premiersupport_pregmatch_siteadmins'] = '/PS-siteadmins/i';

$string['cohort_premiersupport_ap1_admins'] = 'PS-AP1-admins';
$string['cohort_premiersupport_ap1_mgrs'] = 'PS-AP1-mgrs';
$string['cohort_premiersupport_ap1_studs'] = 'PS-AP1-studs';
$string['cohort_premiersupport_ap2_admins'] = 'PS-AP2-admins';
$string['cohort_premiersupport_ap2_mgrs'] = 'PS-AP2-mgrs';
$string['cohort_premiersupport_ap2_studs'] = 'PS-AP2-studs';
$string['cohort_premiersupport_ap3_admins'] = 'PS-AP3-admins';
$string['cohort_premiersupport_ap3_mgrs'] = 'PS-AP3-mgrs';
$string['cohort_premiersupport_ap3_studs'] = 'PS-AP3-studs';
$string['cohort_premiersupport_ap4_admins'] = 'PS-AP4-admins';
$string['cohort_premiersupport_ap4_mgrs'] = 'PS-AP4-mgrs';
$string['cohort_premiersupport_ap4_studs'] = 'PS-AP4-studs';
$string['cohort_premiersupport_ap5_admins'] = 'PS-AP5-admins';
$string['cohort_premiersupport_ap5_mgrs'] = 'PS-AP5-mgrs';
$string['cohort_premiersupport_ap5_studs'] = 'PS-AP5-studs';

$string['cohort_premiersupport_ca1_admins'] = 'PS-CA1-admins';
$string['cohort_premiersupport_ca1_mgrs'] = 'PS-CA1-mgrs';
$string['cohort_premiersupport_ca1_studs'] = 'PS-CA1-studs';
$string['cohort_premiersupport_ca2_admins'] = 'PS-CA2-admins';
$string['cohort_premiersupport_ca2_mgrs'] = 'PS-CA2-mgrs';
$string['cohort_premiersupport_ca2_studs'] = 'PS-CA2-studs';
$string['cohort_premiersupport_ca3_admins'] = 'PS-CA3-admins';
$string['cohort_premiersupport_ca3_mgrs'] = 'PS-CA3-mgrs';
$string['cohort_premiersupport_ca3_studs'] = 'PS-CA3-studs';
$string['cohort_premiersupport_ca4_admins'] = 'PS-CA4-admins';
$string['cohort_premiersupport_ca4_mgrs'] = 'PS-CA4-mgrs';
$string['cohort_premiersupport_ca4_studs'] = 'PS-CA4-studs';
$string['cohort_premiersupport_ca5_admins'] = 'PS-CA5-admins';
$string['cohort_premiersupport_ca5_mgrs'] = 'PS-CA5-mgrs';
$string['cohort_premiersupport_ca5_studs'] = 'PS-CA5-studs';

$string['cohort_premiersupport_em1_admins'] = 'PS-EM1-admins';
$string['cohort_premiersupport_em1_mgrs'] = 'PS-EM1-mgrs';
$string['cohort_premiersupport_em1_studs'] = 'PS-EM1-studs';
$string['cohort_premiersupport_em2_admins'] = 'PS-EM2-admins';
$string['cohort_premiersupport_em2_mgrs'] = 'PS-EM2-mgrs';
$string['cohort_premiersupport_em2_studs'] = 'PS-EM2-studs';
$string['cohort_premiersupport_em3_admins'] = 'PS-EM3-admins';
$string['cohort_premiersupport_em3_mgrs'] = 'PS-EM3-mgrs';
$string['cohort_premiersupport_em3_studs'] = 'PS-EM3-studs';
$string['cohort_premiersupport_em4_admins'] = 'PS-EM4-admins';
$string['cohort_premiersupport_em4_mgrs'] = 'PS-EM4-mgrs';
$string['cohort_premiersupport_em4_studs'] = 'PS-EM4-studs';
$string['cohort_premiersupport_em5_admins'] = 'PS-EM5-admins';
$string['cohort_premiersupport_em5_mgrs'] = 'PS-EM5-mgrs';
$string['cohort_premiersupport_em5_studs'] = 'PS-EM5-studs';

$string['cohort_premiersupport_la1_admins'] = 'PS-LA1-admins';
$string['cohort_premiersupport_la1_mgrs'] = 'PS-LA1-mgrs';
$string['cohort_premiersupport_la1_studs'] = 'PS-LA1-studs';
$string['cohort_premiersupport_la2_admins'] = 'PS-LA2-admins';
$string['cohort_premiersupport_la2_mgrs'] = 'PS-LA2-mgrs';
$string['cohort_premiersupport_la2_studs'] = 'PS-LA2-studs';
$string['cohort_premiersupport_la3_admins'] = 'PS-LA3-admins';
$string['cohort_premiersupport_la3_mgrs'] = 'PS-LA3-mgrs';
$string['cohort_premiersupport_la3_studs'] = 'PS-LA3-studs';
$string['cohort_premiersupport_la4_admins'] = 'PS-LA4-admins';
$string['cohort_premiersupport_la4_mgrs'] = 'PS-LA4-mgrs';
$string['cohort_premiersupport_la4_studs'] = 'PS-LA4-studs';
$string['cohort_premiersupport_la5_admins'] = 'PS-LA5-admins';
$string['cohort_premiersupport_la5_mgrs'] = 'PS-LA5-mgrs';
$string['cohort_premiersupport_la5_studs'] = 'PS-LA5-studs';

$string['cohort_premiersupport_us1_admins'] = 'PS-US1-admins';
$string['cohort_premiersupport_us1_mgrs'] = 'PS-US1-mgrs';
$string['cohort_premiersupport_us1_studs'] = 'PS-US1-studs';
$string['cohort_premiersupport_us2_admins'] = 'PS-US2-admins';
$string['cohort_premiersupport_us2_mgrs'] = 'PS-US2-mgrs';
$string['cohort_premiersupport_us2_studs'] = 'PS-US2-studs';
$string['cohort_premiersupport_us3_admins'] = 'PS-US3-admins';
$string['cohort_premiersupport_us3_mgrs'] = 'PS-US3-mgrs';
$string['cohort_premiersupport_us3_studs'] = 'PS-US3-studs';
$string['cohort_premiersupport_us4_admins'] = 'PS-US4-admins';
$string['cohort_premiersupport_us4_mgrs'] = 'PS-US4-mgrs';
$string['cohort_premiersupport_us4_studs'] = 'PS-US4-studs';
$string['cohort_premiersupport_us5_admins'] = 'PS-US5-admins';
$string['cohort_premiersupport_us5_mgrs'] = 'PS-US5-mgrs';
$string['cohort_premiersupport_us5_studs'] = 'PS-US5-studs';

$string['cohort_premiersupport_ap_geoadmins'] = 'PS-AP-geoadmins';
$string['cohort_premiersupport_ca_geoadmins'] = 'PS-CA-geoadmins';
$string['cohort_premiersupport_em_geoadmins'] = 'PS-EM-geoadmins';
$string['cohort_premiersupport_la_geoadmins'] = 'PS-LA-geoadmins';
$string['cohort_premiersupport_us_geoadmins'] = 'PS-US-geoadmins';

$string['cohort_premiersupport_ap_siteadmins'] = 'PS-AP-siteadmins';
$string['cohort_premiersupport_ca_siteadmins'] = 'PS-CA-siteadmins';
$string['cohort_premiersupport_em_siteadmins'] = 'PS-EM-siteadmins';
$string['cohort_premiersupport_la_siteadmins'] = 'PS-LA-siteadmins';
$string['cohort_premiersupport_us_siteadmins'] = 'PS-US-siteadmins';

$string['cohort_premiersupport_siteadmins'] = 'PS-siteadmins';

// Lenovo *******************************************************************************
// ServiceDelivery section.
// Lenovo *******************************************************************************
// ServiceDelivery user types and roles (shortnames) - main ones.
$string['access_lenovo_servicedelivery_stud'] = 'Lenovo-ServiceDelivery-stud';
$string['role_servicedelivery_student'] = 'servicedelivery-student';
$string['access_lenovo_servicedelivery_mgr'] = 'Lenovo-ServiceDelivery-mgr';
$string['role_servicedelivery_manager'] = 'servicedelivery-manager';
$string['access_lenovo_servicedelivery_admin'] = 'Lenovo-ServiceDelivery-admin';
$string['role_servicedelivery_administrator'] = 'servicedelivery-administrator';
$string['access_lenovo_servicedelivery_geoadmin'] = 'Lenovo-ServiceDelivery-geoadmin';
$string['role_servicedelivery_geoadministrator'] = 'servicedelivery-geoadministrator';
$string['access_lenovo_servicedelivery_siteadmin'] = 'Lenovo-ServiceDelivery-siteadmin';
$string['role_servicedelivery_siteadministrator'] = 'servicedelivery-siteadministrator';

$string['access_lenovo_servicedelivery_pregmatch'] = '/Lenovo-ServiceDelivery/i';
$string['access_lenovo_servicedelivery_pregmatch_stud'] = '/Lenovo-ServiceDelivery-[A-Z][A-Z]+[1-9]-stud/i';
$string['access_lenovo_servicedelivery_pregmatch_mgr'] = '/Lenovo-ServiceDelivery-[A-Z][A-Z]+[1-9]-mgr/i';
$string['access_lenovo_servicedelivery_pregmatch_admin'] = '/Lenovo-ServiceDelivery-[A-Z][A-Z]+[1-9]-admin/i';
$string['access_lenovo_servicedelivery_pregmatch_geoadmin'] = '/Lenovo-ServiceDelivery-[A-Z][A-Z]-geoadmin/i';
$string['access_lenovo_servicedelivery_pregmatch_siteadmin'] = '/Lenovo-ServiceDelivery-siteadmin/i';

// ServiceDelivery manager and administrator reporting and preg_match strings.
// Note: If the user is a SD admin, the following string is the groupsmenu[0] in /lib/groupslib.php:groups_print_course_menu.
//          If the user is a SD site admin, the following string is repeated for all GEOs (using 'access_all_geos') in a custom
//              groups menu (/lib/groupslib.php:groups_sort_menu_options).
// To be used with ServiceDelivery-admins (assumption is groupname is either "US1" or "US" depending on access type).
$string['groups_lenovo_servicedelivery_all_group_participants'] = 'All ServiceDelivery {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_group_participants'] = 'ServiceDelivery {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_group_type_participants'] = 'ServiceDelivery {$a->groupname} {$a->type} enrollments';
// To be used with ServiceDelivery-geoadmins
$string['groups_lenovo_servicedelivery_all_geo_participants'] = 'All ServiceDelivery {$a->groupname} enrollments';
// To be used with ServiceDelivery-siteadmins and ServiceDelivery-geoadmins
$string['groups_lenovo_servicedelivery_geo_participants'] = 'ServiceDelivery {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_geo_type_participants'] = 'ServiceDelivery {$a->groupname} {$a->type} enrollments';
// To be used with ServiceDelivery-siteadmins
$string['groups_lenovo_servicedelivery_all_participants'] = 'All ServiceDelivery enrollments';
$string['groups_lenovo_servicedelivery_all_type_participants'] = 'All ServiceDelivery {$a->type} enrollments';   // ex: All ServiceDelivery student enrollments

// Lenovo *******************************************************************************
// ServiceDelivery GEO student types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_us1_stud'] = 'Lenovo-ServiceDelivery-US1-stud';
$string['access_lenovo_servicedelivery_ca1_stud'] = 'Lenovo-ServiceDelivery-CA1-stud';
$string['access_lenovo_servicedelivery_la1_stud'] = 'Lenovo-ServiceDelivery-LA1-stud';
$string['access_lenovo_servicedelivery_ap1_stud'] = 'Lenovo-ServiceDelivery-AP1-stud';
$string['access_lenovo_servicedelivery_em1_stud'] = 'Lenovo-ServiceDelivery-EM1-stud';

$string['access_lenovo_servicedelivery_us2_stud'] = 'Lenovo-ServiceDelivery-US2-stud';
$string['access_lenovo_servicedelivery_ca2_stud'] = 'Lenovo-ServiceDelivery-CA2-stud';
$string['access_lenovo_servicedelivery_la2_stud'] = 'Lenovo-ServiceDelivery-LA2-stud';
$string['access_lenovo_servicedelivery_ap2_stud'] = 'Lenovo-ServiceDelivery-AP2-stud';
$string['access_lenovo_servicedelivery_em2_stud'] = 'Lenovo-ServiceDelivery-EM2-stud';

$string['access_lenovo_servicedelivery_us3_stud'] = 'Lenovo-ServiceDelivery-US3-stud';
$string['access_lenovo_servicedelivery_ca3_stud'] = 'Lenovo-ServiceDelivery-CA3-stud';
$string['access_lenovo_servicedelivery_la3_stud'] = 'Lenovo-ServiceDelivery-LA3-stud';
$string['access_lenovo_servicedelivery_ap3_stud'] = 'Lenovo-ServiceDelivery-AP3-stud';
$string['access_lenovo_servicedelivery_em3_stud'] = 'Lenovo-ServiceDelivery-EM3-stud';

$string['access_lenovo_servicedelivery_us4_stud'] = 'Lenovo-ServiceDelivery-US4-stud';
$string['access_lenovo_servicedelivery_ca4_stud'] = 'Lenovo-ServiceDelivery-CA4-stud';
$string['access_lenovo_servicedelivery_la4_stud'] = 'Lenovo-ServiceDelivery-LA4-stud';
$string['access_lenovo_servicedelivery_ap4_stud'] = 'Lenovo-ServiceDelivery-AP4-stud';
$string['access_lenovo_servicedelivery_em4_stud'] = 'Lenovo-ServiceDelivery-EM4-stud';

$string['access_lenovo_servicedelivery_us5_stud'] = 'Lenovo-ServiceDelivery-US5-stud';
$string['access_lenovo_servicedelivery_ca5_stud'] = 'Lenovo-ServiceDelivery-CA5-stud';
$string['access_lenovo_servicedelivery_la5_stud'] = 'Lenovo-ServiceDelivery-LA5-stud';
$string['access_lenovo_servicedelivery_ap5_stud'] = 'Lenovo-ServiceDelivery-AP5-stud';
$string['access_lenovo_servicedelivery_em5_stud'] = 'Lenovo-ServiceDelivery-EM5-stud';

// Lenovo *******************************************************************************
// ServiceDelivery GEO manager user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_us1_mgr'] = 'Lenovo-ServiceDelivery-US1-mgr';
$string['access_lenovo_servicedelivery_ca1_mgr'] = 'Lenovo-ServiceDelivery-CA1-mgr';
$string['access_lenovo_servicedelivery_la1_mgr'] = 'Lenovo-ServiceDelivery-LA1-mgr';
$string['access_lenovo_servicedelivery_ap1_mgr'] = 'Lenovo-ServiceDelivery-AP1-mgr';
$string['access_lenovo_servicedelivery_em1_mgr'] = 'Lenovo-ServiceDelivery-EM1-mgr';

$string['access_lenovo_servicedelivery_us2_mgr'] = 'Lenovo-ServiceDelivery-US2-mgr';
$string['access_lenovo_servicedelivery_ca2_mgr'] = 'Lenovo-ServiceDelivery-CA2-mgr';
$string['access_lenovo_servicedelivery_la2_mgr'] = 'Lenovo-ServiceDelivery-LA2-mgr';
$string['access_lenovo_servicedelivery_ap2_mgr'] = 'Lenovo-ServiceDelivery-AP2-mgr';
$string['access_lenovo_servicedelivery_em2_mgr'] = 'Lenovo-ServiceDelivery-EM2-mgr';

$string['access_lenovo_servicedelivery_us3_mgr'] = 'Lenovo-ServiceDelivery-US3-mgr';
$string['access_lenovo_servicedelivery_ca3_mgr'] = 'Lenovo-ServiceDelivery-CA3-mgr';
$string['access_lenovo_servicedelivery_la3_mgr'] = 'Lenovo-ServiceDelivery-LA3-mgr';
$string['access_lenovo_servicedelivery_ap3_mgr'] = 'Lenovo-ServiceDelivery-AP3-mgr';
$string['access_lenovo_servicedelivery_em3_mgr'] = 'Lenovo-ServiceDelivery-EM3-mgr';

$string['access_lenovo_servicedelivery_us4_mgr'] = 'Lenovo-ServiceDelivery-US4-mgr';
$string['access_lenovo_servicedelivery_ca4_mgr'] = 'Lenovo-ServiceDelivery-CA4-mgr';
$string['access_lenovo_servicedelivery_la4_mgr'] = 'Lenovo-ServiceDelivery-LA4-mgr';
$string['access_lenovo_servicedelivery_ap4_mgr'] = 'Lenovo-ServiceDelivery-AP4-mgr';
$string['access_lenovo_servicedelivery_em4_mgr'] = 'Lenovo-ServiceDelivery-EM4-mgr';

$string['access_lenovo_servicedelivery_us5_mgr'] = 'Lenovo-ServiceDelivery-US5-mgr';
$string['access_lenovo_servicedelivery_ca5_mgr'] = 'Lenovo-ServiceDelivery-CA5-mgr';
$string['access_lenovo_servicedelivery_la5_mgr'] = 'Lenovo-ServiceDelivery-LA5-mgr';
$string['access_lenovo_servicedelivery_ap5_mgr'] = 'Lenovo-ServiceDelivery-AP5-mgr';
$string['access_lenovo_servicedelivery_em5_mgr'] = 'Lenovo-ServiceDelivery-EM5-mgr';

// Lenovo *******************************************************************************
// ServiceDelivery GEO administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_us1_admin'] = 'Lenovo-ServiceDelivery-US1-admin';
$string['access_lenovo_servicedelivery_ca1_admin'] = 'Lenovo-ServiceDelivery-CA1-admin';
$string['access_lenovo_servicedelivery_la1_admin'] = 'Lenovo-ServiceDelivery-LA1-admin';
$string['access_lenovo_servicedelivery_ap1_admin'] = 'Lenovo-ServiceDelivery-AP1-admin';
$string['access_lenovo_servicedelivery_em1_admin'] = 'Lenovo-ServiceDelivery-EM1-admin';

$string['access_lenovo_servicedelivery_us2_admin'] = 'Lenovo-ServiceDelivery-US2-admin';
$string['access_lenovo_servicedelivery_ca2_admin'] = 'Lenovo-ServiceDelivery-CA2-admin';
$string['access_lenovo_servicedelivery_la2_admin'] = 'Lenovo-ServiceDelivery-LA2-admin';
$string['access_lenovo_servicedelivery_ap2_admin'] = 'Lenovo-ServiceDelivery-AP2-admin';
$string['access_lenovo_servicedelivery_em2_admin'] = 'Lenovo-ServiceDelivery-EM2-admin';

$string['access_lenovo_servicedelivery_us3_admin'] = 'Lenovo-ServiceDelivery-US3-admin';
$string['access_lenovo_servicedelivery_ca3_admin'] = 'Lenovo-ServiceDelivery-CA3-admin';
$string['access_lenovo_servicedelivery_la3_admin'] = 'Lenovo-ServiceDelivery-LA3-admin';
$string['access_lenovo_servicedelivery_ap3_admin'] = 'Lenovo-ServiceDelivery-AP3-admin';
$string['access_lenovo_servicedelivery_em3_admin'] = 'Lenovo-ServiceDelivery-EM3-admin';

$string['access_lenovo_servicedelivery_us4_admin'] = 'Lenovo-ServiceDelivery-US4-admin';
$string['access_lenovo_servicedelivery_ca4_admin'] = 'Lenovo-ServiceDelivery-CA4-admin';
$string['access_lenovo_servicedelivery_la4_admin'] = 'Lenovo-ServiceDelivery-LA4-admin';
$string['access_lenovo_servicedelivery_ap4_admin'] = 'Lenovo-ServiceDelivery-AP4-admin';
$string['access_lenovo_servicedelivery_em4_admin'] = 'Lenovo-ServiceDelivery-EM4-admin';

$string['access_lenovo_servicedelivery_us5_admin'] = 'Lenovo-ServiceDelivery-US5-admin';
$string['access_lenovo_servicedelivery_ca5_admin'] = 'Lenovo-ServiceDelivery-CA5-admin';
$string['access_lenovo_servicedelivery_la5_admin'] = 'Lenovo-ServiceDelivery-LA5-admin';
$string['access_lenovo_servicedelivery_ap5_admin'] = 'Lenovo-ServiceDelivery-AP5-admin';
$string['access_lenovo_servicedelivery_em5_admin'] = 'Lenovo-ServiceDelivery-EM5-admin';

// Lenovo *******************************************************************************
// ServiceDelivery GEO administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_us_geoadmin'] = 'Lenovo-ServiceDelivery-US-geoadmin';
$string['access_lenovo_servicedelivery_ca_geoadmin'] = 'Lenovo-ServiceDelivery-CA-geoadmin';
$string['access_lenovo_servicedelivery_la_geoadmin'] = 'Lenovo-ServiceDelivery-LA-geoadmin';
$string['access_lenovo_servicedelivery_ap_geoadmin'] = 'Lenovo-ServiceDelivery-AP-geoadmin';
$string['access_lenovo_servicedelivery_em_geoadmin'] = 'Lenovo-ServiceDelivery-EM-geoadmin';

// Lenovo *******************************************************************************
// ServiceDelivery GEO site administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_us_siteadmin'] = 'Lenovo-ServiceDelivery-US-siteadmin';
$string['access_lenovo_servicedelivery_ca_siteadmin'] = 'Lenovo-ServiceDelivery-CA-siteadmin';
$string['access_lenovo_servicedelivery_la_siteadmin'] = 'Lenovo-ServiceDelivery-LA-siteadmin';
$string['access_lenovo_servicedelivery_ap_siteadmin'] = 'Lenovo-ServiceDelivery-AP-siteadmin';
$string['access_lenovo_servicedelivery_em_siteadmin'] = 'Lenovo-ServiceDelivery-EM-siteadmin';

// Lenovo *******************************************************************************
// ServiceDelivery site administrator user type.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_siteadmin'] = 'Lenovo-ServiceDelivery-siteadmin';

// ServiceDelivery cohort names...also used as course group names.
$string['cohort_lenovo_servicedelivery_pregmatch_admins'] = '/SD(TAM)?-[A-Z][A-Z]+[1-9]-admins/i';      // 05/06/20 - Lenovo debugging...
$string['cohort_lenovo_servicedelivery_pregmatch_mgrs'] = '/SD(TAM)?-[A-Z][A-Z]+[1-9]-mgrs/i';
$string['cohort_lenovo_servicedelivery_pregmatch_studs'] = '/SD(TAM)?-[A-Z][A-Z]+[1-9]-studs/i';
$string['cohort_lenovo_servicedelivery_pregmatch_geoadmins'] = '/SD(TAM)?-[A-Z][A-Z]-geoadmins/i';
$string['cohort_lenovo_servicedelivery_pregmatch_siteadmins'] = '/SD(TAM)?-siteadmins/i';

$string['cohort_lenovo_servicedelivery_ap1_admins'] = 'SD-AP1-admins';
$string['cohort_lenovo_servicedelivery_ap1_mgrs'] = 'SD-AP1-mgrs';
$string['cohort_lenovo_servicedelivery_ap1_studs'] = 'SD-AP1-studs';
$string['cohort_lenovo_servicedelivery_ap2_admins'] = 'SD-AP2-admins';
$string['cohort_lenovo_servicedelivery_ap2_mgrs'] = 'SD-AP2-mgrs';
$string['cohort_lenovo_servicedelivery_ap2_studs'] = 'SD-AP2-studs';
$string['cohort_lenovo_servicedelivery_ap3_admins'] = 'SD-AP3-admins';
$string['cohort_lenovo_servicedelivery_ap3_mgrs'] = 'SD-AP3-mgrs';
$string['cohort_lenovo_servicedelivery_ap3_studs'] = 'SD-AP3-studs';
$string['cohort_lenovo_servicedelivery_ap4_admins'] = 'SD-AP4-admins';
$string['cohort_lenovo_servicedelivery_ap4_mgrs'] = 'SD-AP4-mgrs';
$string['cohort_lenovo_servicedelivery_ap4_studs'] = 'SD-AP4-studs';
$string['cohort_lenovo_servicedelivery_ap5_admins'] = 'SD-AP5-admins';
$string['cohort_lenovo_servicedelivery_ap5_mgrs'] = 'SD-AP5-mgrs';
$string['cohort_lenovo_servicedelivery_ap5_studs'] = 'SD-AP5-studs';

$string['cohort_lenovo_servicedelivery_ca1_admins'] = 'SD-CA1-admins';
$string['cohort_lenovo_servicedelivery_ca1_mgrs'] = 'SD-CA1-mgrs';
$string['cohort_lenovo_servicedelivery_ca1_studs'] = 'SD-CA1-studs';
$string['cohort_lenovo_servicedelivery_ca2_admins'] = 'SD-CA2-admins';
$string['cohort_lenovo_servicedelivery_ca2_mgrs'] = 'SD-CA2-mgrs';
$string['cohort_lenovo_servicedelivery_ca2_studs'] = 'SD-CA2-studs';
$string['cohort_lenovo_servicedelivery_ca3_admins'] = 'SD-CA3-admins';
$string['cohort_lenovo_servicedelivery_ca3_mgrs'] = 'SD-CA3-mgrs';
$string['cohort_lenovo_servicedelivery_ca3_studs'] = 'SD-CA3-studs';
$string['cohort_lenovo_servicedelivery_ca4_admins'] = 'SD-CA4-admins';
$string['cohort_lenovo_servicedelivery_ca4_mgrs'] = 'SD-CA4-mgrs';
$string['cohort_lenovo_servicedelivery_ca4_studs'] = 'SD-CA4-studs';
$string['cohort_lenovo_servicedelivery_ca5_admins'] = 'SD-CA5-admins';
$string['cohort_lenovo_servicedelivery_ca5_mgrs'] = 'SD-CA5-mgrs';
$string['cohort_lenovo_servicedelivery_ca5_studs'] = 'SD-CA5-studs';

$string['cohort_lenovo_servicedelivery_em1_admins'] = 'SD-EM1-admins';
$string['cohort_lenovo_servicedelivery_em1_mgrs'] = 'SD-EM1-mgrs';
$string['cohort_lenovo_servicedelivery_em1_studs'] = 'SD-EM1-studs';
$string['cohort_lenovo_servicedelivery_em2_admins'] = 'SD-EM2-admins';
$string['cohort_lenovo_servicedelivery_em2_mgrs'] = 'SD-EM2-mgrs';
$string['cohort_lenovo_servicedelivery_em2_studs'] = 'SD-EM2-studs';
$string['cohort_lenovo_servicedelivery_em3_admins'] = 'SD-EM3-admins';
$string['cohort_lenovo_servicedelivery_em3_mgrs'] = 'SD-EM3-mgrs';
$string['cohort_lenovo_servicedelivery_em3_studs'] = 'SD-EM3-studs';
$string['cohort_lenovo_servicedelivery_em4_admins'] = 'SD-EM4-admins';
$string['cohort_lenovo_servicedelivery_em4_mgrs'] = 'SD-EM4-mgrs';
$string['cohort_lenovo_servicedelivery_em4_studs'] = 'SD-EM4-studs';
$string['cohort_lenovo_servicedelivery_em5_admins'] = 'SD-EM5-admins';
$string['cohort_lenovo_servicedelivery_em5_mgrs'] = 'SD-EM5-mgrs';
$string['cohort_lenovo_servicedelivery_em5_studs'] = 'SD-EM5-studs';

$string['cohort_lenovo_servicedelivery_la1_admins'] = 'SD-LA1-admins';
$string['cohort_lenovo_servicedelivery_la1_mgrs'] = 'SD-LA1-mgrs';
$string['cohort_lenovo_servicedelivery_la1_studs'] = 'SD-LA1-studs';
$string['cohort_lenovo_servicedelivery_la2_admins'] = 'SD-LA2-admins';
$string['cohort_lenovo_servicedelivery_la2_mgrs'] = 'SD-LA2-mgrs';
$string['cohort_lenovo_servicedelivery_la2_studs'] = 'SD-LA2-studs';
$string['cohort_lenovo_servicedelivery_la3_admins'] = 'SD-LA3-admins';
$string['cohort_lenovo_servicedelivery_la3_mgrs'] = 'SD-LA3-mgrs';
$string['cohort_lenovo_servicedelivery_la3_studs'] = 'SD-LA3-studs';
$string['cohort_lenovo_servicedelivery_la4_admins'] = 'SD-LA4-admins';
$string['cohort_lenovo_servicedelivery_la4_mgrs'] = 'SD-LA4-mgrs';
$string['cohort_lenovo_servicedelivery_la4_studs'] = 'SD-LA4-studs';
$string['cohort_lenovo_servicedelivery_la5_admins'] = 'SD-LA5-admins';
$string['cohort_lenovo_servicedelivery_la5_mgrs'] = 'SD-LA5-mgrs';
$string['cohort_lenovo_servicedelivery_la5_studs'] = 'SD-LA5-studs';

$string['cohort_lenovo_servicedelivery_us1_admins'] = 'SD-US1-admins';
$string['cohort_lenovo_servicedelivery_us1_mgrs'] = 'SD-US1-mgrs';
$string['cohort_lenovo_servicedelivery_us1_studs'] = 'SD-US1-studs';
$string['cohort_lenovo_servicedelivery_us2_admins'] = 'SD-US2-admins';
$string['cohort_lenovo_servicedelivery_us2_mgrs'] = 'SD-US2-mgrs';
$string['cohort_lenovo_servicedelivery_us2_studs'] = 'SD-US2-studs';
$string['cohort_lenovo_servicedelivery_us3_admins'] = 'SD-US3-admins';
$string['cohort_lenovo_servicedelivery_us3_mgrs'] = 'SD-US3-mgrs';
$string['cohort_lenovo_servicedelivery_us3_studs'] = 'SD-US3-studs';
$string['cohort_lenovo_servicedelivery_us4_admins'] = 'SD-US4-admins';
$string['cohort_lenovo_servicedelivery_us4_mgrs'] = 'SD-US4-mgrs';
$string['cohort_lenovo_servicedelivery_us4_studs'] = 'SD-US4-studs';
$string['cohort_lenovo_servicedelivery_us5_admins'] = 'SD-US5-admins';
$string['cohort_lenovo_servicedelivery_us5_mgrs'] = 'SD-US5-mgrs';
$string['cohort_lenovo_servicedelivery_us5_studs'] = 'SD-US5-studs';

$string['cohort_lenovo_servicedelivery_ap_geoadmins'] = 'SD-AP-geoadmins';
$string['cohort_lenovo_servicedelivery_ca_geoadmins'] = 'SD-CA-geoadmins';
$string['cohort_lenovo_servicedelivery_em_geoadmins'] = 'SD-EM-geoadmins';
$string['cohort_lenovo_servicedelivery_la_geoadmins'] = 'SD-LA-geoadmins';
$string['cohort_lenovo_servicedelivery_us_geoadmins'] = 'SD-US-geoadmins';

$string['cohort_lenovo_servicedelivery_ap_siteadmins'] = 'SD-AP-siteadmins';
$string['cohort_lenovo_servicedelivery_ca_siteadmins'] = 'SD-CA-siteadmins';
$string['cohort_lenovo_servicedelivery_em_siteadmins'] = 'SD-EM-siteadmins';
$string['cohort_lenovo_servicedelivery_la_siteadmins'] = 'SD-LA-siteadmins';
$string['cohort_lenovo_servicedelivery_us_siteadmins'] = 'SD-US-siteadmins';

$string['cohort_lenovo_servicedelivery_siteadmins'] = 'SD-siteadmins';

// @03 - 05/05/20 - Added strings for SD TAM users.
$string['access_lenovo_servicedelivery_tam_pregmatch'] = '/Lenovo-ServiceDelivery-TAM/i';
$string['access_lenovo_servicedelivery_tam_pregmatch_stud'] = '/Lenovo-ServiceDelivery-TAM-[A-Z][A-Z]+[1-9]-stud/i';
$string['access_lenovo_servicedelivery_tam_pregmatch_mgr'] = '/Lenovo-ServiceDelivery-TAM-[A-Z][A-Z]+[1-9]-mgr/i';
$string['access_lenovo_servicedelivery_tam_pregmatch_admin'] = '/Lenovo-ServiceDelivery-TAM-[A-Z][A-Z]+[1-9]-admin/i';
$string['access_lenovo_servicedelivery_tam_pregmatch_geoadmin'] = '/Lenovo-ServiceDelivery-TAM-[A-Z][A-Z]-geoadmin/i';
$string['access_lenovo_servicedelivery_tam_pregmatch_siteadmin'] = '/Lenovo-ServiceDelivery-TAM-siteadmin/i';

// ServiceDelivery manager and administrator reporting and preg_match strings.
// Note: If the user is a SD admin, the following string is the groupsmenu[0] in /lib/groupslib.php:groups_print_course_menu.
//          If the user is a SD site admin, the following string is repeated for all GEOs (using 'access_all_geos') in a custom
//              groups menu (/lib/groupslib.php:groups_sort_menu_options).
// To be used with ServiceDelivery-admins (assumption is groupname is either "US1" or "US" depending on access type).
$string['groups_lenovo_servicedelivery_tam_all_group_participants'] = 'All ServiceDelivery TAM {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_tam_group_participants'] = 'ServiceDelivery TAM {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_tam_group_type_participants'] = 'ServiceDelivery TAM {$a->groupname} {$a->type} enrollments';
// To be used with ServiceDelivery-geoadmins
$string['groups_lenovo_servicedelivery_tam_all_geo_participants'] = 'All ServiceDelivery TAM {$a->groupname} enrollments';
// To be used with ServiceDelivery-siteadmins and ServiceDelivery-geoadmins
$string['groups_lenovo_servicedelivery_tam_geo_participants'] = 'ServiceDelivery TAM {$a->groupname} enrollments';
$string['groups_lenovo_servicedelivery_tam_geo_type_participants'] = 'ServiceDelivery TAM {$a->groupname} {$a->type} enrollments';
// To be used with ServiceDelivery-siteadmins
$string['groups_lenovo_servicedelivery_tam_all_participants'] = 'All ServiceDelivery TAM enrollments';
$string['groups_lenovo_servicedelivery_tam_all_type_participants'] = 'All ServiceDelivery TAM {$a->type} enrollments';   // ex: All ServiceDelivery student enrollments

// ServiceDelivery TAM cohort names...also used as course group names.
$string['cohort_lenovo_servicedelivery_tam_pregmatch_admins'] = '/SDTAM-[A-Z][A-Z]+[1-9]-admins/i';
$string['cohort_lenovo_servicedelivery_tam_pregmatch_mgrs'] = '/SDTAM-[A-Z][A-Z]+[1-9]-mgrs/i';
$string['cohort_lenovo_servicedelivery_tam_pregmatch_studs'] = '/SDTAM-[A-Z][A-Z]+[1-9]-studs/i';
$string['cohort_lenovo_servicedelivery_tam_pregmatch_geoadmins'] = '/SDTAM-[A-Z][A-Z]-geoadmins/i';
$string['cohort_lenovo_servicedelivery_tam_pregmatch_siteadmins'] = '/SDTAM-siteadmins/i';

$string['cohort_lenovo_servicedelivery_tam_ap1_admins'] = 'SDTAM-AP1-admins';
$string['cohort_lenovo_servicedelivery_tam_ap1_mgrs'] = 'SDTAM-AP1-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ap1_studs'] = 'SDTAM-AP1-studs';
$string['cohort_lenovo_servicedelivery_tam_ap2_admins'] = 'SDTAM-AP2-admins';
$string['cohort_lenovo_servicedelivery_tam_ap2_mgrs'] = 'SDTAM-AP2-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ap2_studs'] = 'SDTAM-AP2-studs';
$string['cohort_lenovo_servicedelivery_tam_ap3_admins'] = 'SDTAM-AP3-admins';
$string['cohort_lenovo_servicedelivery_tam_ap3_mgrs'] = 'SDTAM-AP3-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ap3_studs'] = 'SDTAM-AP3-studs';
$string['cohort_lenovo_servicedelivery_tam_ap4_admins'] = 'SDTAM-AP4-admins';
$string['cohort_lenovo_servicedelivery_tam_ap4_mgrs'] = 'SDTAM-AP4-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ap4_studs'] = 'SDTAM-AP4-studs';
$string['cohort_lenovo_servicedelivery_tam_ap5_admins'] = 'SDTAM-AP5-admins';
$string['cohort_lenovo_servicedelivery_tam_ap5_mgrs'] = 'SDTAM-AP5-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ap5_studs'] = 'SDTAM-AP5-studs';

$string['cohort_lenovo_servicedelivery_tam_ca1_admins'] = 'SDTAM-CA1-admins';
$string['cohort_lenovo_servicedelivery_tam_ca1_mgrs'] = 'SDTAM-CA1-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ca1_studs'] = 'SDTAM-CA1-studs';
$string['cohort_lenovo_servicedelivery_tam_ca2_admins'] = 'SDTAM-CA2-admins';
$string['cohort_lenovo_servicedelivery_tam_ca2_mgrs'] = 'SDTAM-CA2-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ca2_studs'] = 'SDTAM-CA2-studs';
$string['cohort_lenovo_servicedelivery_tam_ca3_admins'] = 'SDTAM-CA3-admins';
$string['cohort_lenovo_servicedelivery_tam_ca3_mgrs'] = 'SDTAM-CA3-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ca3_studs'] = 'SDTAM-CA3-studs';
$string['cohort_lenovo_servicedelivery_tam_ca4_admins'] = 'SDTAM-CA4-admins';
$string['cohort_lenovo_servicedelivery_tam_ca4_mgrs'] = 'SDTAM-CA4-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ca4_studs'] = 'SDTAM-CA4-studs';
$string['cohort_lenovo_servicedelivery_tam_ca5_admins'] = 'SDTAM-CA5-admins';
$string['cohort_lenovo_servicedelivery_tam_ca5_mgrs'] = 'SDTAM-CA5-mgrs';
$string['cohort_lenovo_servicedelivery_tam_ca5_studs'] = 'SDTAM-CA5-studs';

$string['cohort_lenovo_servicedelivery_tam_em1_admins'] = 'SDTAM-EM1-admins';
$string['cohort_lenovo_servicedelivery_tam_em1_mgrs'] = 'SDTAM-EM1-mgrs';
$string['cohort_lenovo_servicedelivery_tam_em1_studs'] = 'SDTAM-EM1-studs';
$string['cohort_lenovo_servicedelivery_tam_em2_admins'] = 'SDTAM-EM2-admins';
$string['cohort_lenovo_servicedelivery_tam_em2_mgrs'] = 'SDTAM-EM2-mgrs';
$string['cohort_lenovo_servicedelivery_tam_em2_studs'] = 'SDTAM-EM2-studs';
$string['cohort_lenovo_servicedelivery_tam_em3_admins'] = 'SDTAM-EM3-admins';
$string['cohort_lenovo_servicedelivery_tam_em3_mgrs'] = 'SDTAM-EM3-mgrs';
$string['cohort_lenovo_servicedelivery_tam_em3_studs'] = 'SDTAM-EM3-studs';
$string['cohort_lenovo_servicedelivery_tam_em4_admins'] = 'SDTAM-EM4-admins';
$string['cohort_lenovo_servicedelivery_tam_em4_mgrs'] = 'SDTAM-EM4-mgrs';
$string['cohort_lenovo_servicedelivery_tam_em4_studs'] = 'SDTAM-EM4-studs';
$string['cohort_lenovo_servicedelivery_tam_em5_admins'] = 'SDTAM-EM5-admins';
$string['cohort_lenovo_servicedelivery_tam_em5_mgrs'] = 'SDTAM-EM5-mgrs';
$string['cohort_lenovo_servicedelivery_tam_em5_studs'] = 'SDTAM-EM5-studs';

$string['cohort_lenovo_servicedelivery_tam_la1_admins'] = 'SDTAM-LA1-admins';
$string['cohort_lenovo_servicedelivery_tam_la1_mgrs'] = 'SDTAM-LA1-mgrs';
$string['cohort_lenovo_servicedelivery_tam_la1_studs'] = 'SDTAM-LA1-studs';
$string['cohort_lenovo_servicedelivery_tam_la2_admins'] = 'SDTAM-LA2-admins';
$string['cohort_lenovo_servicedelivery_tam_la2_mgrs'] = 'SDTAM-LA2-mgrs';
$string['cohort_lenovo_servicedelivery_tam_la2_studs'] = 'SDTAM-LA2-studs';
$string['cohort_lenovo_servicedelivery_tam_la3_admins'] = 'SDTAM-LA3-admins';
$string['cohort_lenovo_servicedelivery_tam_la3_mgrs'] = 'SDTAM-LA3-mgrs';
$string['cohort_lenovo_servicedelivery_tam_la3_studs'] = 'SDTAM-LA3-studs';
$string['cohort_lenovo_servicedelivery_tam_la4_admins'] = 'SDTAM-LA4-admins';
$string['cohort_lenovo_servicedelivery_tam_la4_mgrs'] = 'SDTAM-LA4-mgrs';
$string['cohort_lenovo_servicedelivery_tam_la4_studs'] = 'SDTAM-LA4-studs';
$string['cohort_lenovo_servicedelivery_tam_la5_admins'] = 'SDTAM-LA5-admins';
$string['cohort_lenovo_servicedelivery_tam_la5_mgrs'] = 'SDTAM-LA5-mgrs';
$string['cohort_lenovo_servicedelivery_tam_la5_studs'] = 'SDTAM-LA5-studs';

$string['cohort_lenovo_servicedelivery_tam_us1_admins'] = 'SDTAM-US1-admins';
$string['cohort_lenovo_servicedelivery_tam_us1_mgrs'] = 'SDTAM-US1-mgrs';
$string['cohort_lenovo_servicedelivery_tam_us1_studs'] = 'SDTAM-US1-studs';
$string['cohort_lenovo_servicedelivery_tam_us2_admins'] = 'SDTAM-US2-admins';
$string['cohort_lenovo_servicedelivery_tam_us2_mgrs'] = 'SDTAM-US2-mgrs';
$string['cohort_lenovo_servicedelivery_tam_us2_studs'] = 'SDTAM-US2-studs';
$string['cohort_lenovo_servicedelivery_tam_us3_admins'] = 'SDTAM-US3-admins';
$string['cohort_lenovo_servicedelivery_tam_us3_mgrs'] = 'SDTAM-US3-mgrs';
$string['cohort_lenovo_servicedelivery_tam_us3_studs'] = 'SDTAM-US3-studs';
$string['cohort_lenovo_servicedelivery_tam_us4_admins'] = 'SDTAM-US4-admins';
$string['cohort_lenovo_servicedelivery_tam_us4_mgrs'] = 'SDTAM-US4-mgrs';
$string['cohort_lenovo_servicedelivery_tam_us4_studs'] = 'SDTAM-US4-studs';
$string['cohort_lenovo_servicedelivery_tam_us5_admins'] = 'SDTAM-US5-admins';
$string['cohort_lenovo_servicedelivery_tam_us5_mgrs'] = 'SDTAM-US5-mgrs';
$string['cohort_lenovo_servicedelivery_tam_us5_studs'] = 'SDTAM-US5-studs';

$string['cohort_lenovo_servicedelivery_tam_ap_geoadmins'] = 'SDTAM-AP-geoadmins';
$string['cohort_lenovo_servicedelivery_tam_ca_geoadmins'] = 'SDTAM-CA-geoadmins';
$string['cohort_lenovo_servicedelivery_tam_em_geoadmins'] = 'SDTAM-EM-geoadmins';
$string['cohort_lenovo_servicedelivery_tam_la_geoadmins'] = 'SDTAM-LA-geoadmins';
$string['cohort_lenovo_servicedelivery_tam_us_geoadmins'] = 'SDTAM-US-geoadmins';

$string['cohort_lenovo_servicedelivery_tam_ap_siteadmins'] = 'SDTAM-AP-siteadmins';
$string['cohort_lenovo_servicedelivery_tam_ca_siteadmins'] = 'SDTAM-CA-siteadmins';
$string['cohort_lenovo_servicedelivery_tam_em_siteadmins'] = 'SDTAM-EM-siteadmins';
$string['cohort_lenovo_servicedelivery_tam_la_siteadmins'] = 'SDTAM-LA-siteadmins';
$string['cohort_lenovo_servicedelivery_tam_us_siteadmins'] = 'SDTAM-US-siteadmins';

$string['cohort_lenovo_servicedelivery_tam_siteadmins'] = 'SDTAM-siteadmins';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM GEO student types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_us1_stud'] = 'Lenovo-ServiceDelivery-TAM-US1-stud';
$string['access_lenovo_servicedelivery_tam_ca1_stud'] = 'Lenovo-ServiceDelivery-TAM-CA1-stud';
$string['access_lenovo_servicedelivery_tam_la1_stud'] = 'Lenovo-ServiceDelivery-TAM-LA1-stud';
$string['access_lenovo_servicedelivery_tam_ap1_stud'] = 'Lenovo-ServiceDelivery-TAM-AP1-stud';
$string['access_lenovo_servicedelivery_tam_em1_stud'] = 'Lenovo-ServiceDelivery-TAM-EM1-stud';

$string['access_lenovo_servicedelivery_tam_us2_stud'] = 'Lenovo-ServiceDelivery-TAM-US2-stud';
$string['access_lenovo_servicedelivery_tam_ca2_stud'] = 'Lenovo-ServiceDelivery-TAM-CA2-stud';
$string['access_lenovo_servicedelivery_tam_la2_stud'] = 'Lenovo-ServiceDelivery-TAM-LA2-stud';
$string['access_lenovo_servicedelivery_tam_ap2_stud'] = 'Lenovo-ServiceDelivery-TAM-AP2-stud';
$string['access_lenovo_servicedelivery_tam_em2_stud'] = 'Lenovo-ServiceDelivery-TAM-EM2-stud';

$string['access_lenovo_servicedelivery_tam_us3_stud'] = 'Lenovo-ServiceDelivery-TAM-US3-stud';
$string['access_lenovo_servicedelivery_tam_ca3_stud'] = 'Lenovo-ServiceDelivery-TAM-CA3-stud';
$string['access_lenovo_servicedelivery_tam_la3_stud'] = 'Lenovo-ServiceDelivery-TAM-LA3-stud';
$string['access_lenovo_servicedelivery_tam_ap3_stud'] = 'Lenovo-ServiceDelivery-TAM-AP3-stud';
$string['access_lenovo_servicedelivery_tam_em3_stud'] = 'Lenovo-ServiceDelivery-TAM-EM3-stud';

$string['access_lenovo_servicedelivery_tam_us4_stud'] = 'Lenovo-ServiceDelivery-TAM-US4-stud';
$string['access_lenovo_servicedelivery_tam_ca4_stud'] = 'Lenovo-ServiceDelivery-TAM-CA4-stud';
$string['access_lenovo_servicedelivery_tam_la4_stud'] = 'Lenovo-ServiceDelivery-TAM-LA4-stud';
$string['access_lenovo_servicedelivery_tam_ap4_stud'] = 'Lenovo-ServiceDelivery-TAM-AP4-stud';
$string['access_lenovo_servicedelivery_tam_em4_stud'] = 'Lenovo-ServiceDelivery-TAM-EM4-stud';

$string['access_lenovo_servicedelivery_tam_us5_stud'] = 'Lenovo-ServiceDelivery-TAM-US5-stud';
$string['access_lenovo_servicedelivery_tam_ca5_stud'] = 'Lenovo-ServiceDelivery-TAM-CA5-stud';
$string['access_lenovo_servicedelivery_tam_la5_stud'] = 'Lenovo-ServiceDelivery-TAM-LA5-stud';
$string['access_lenovo_servicedelivery_tam_ap5_stud'] = 'Lenovo-ServiceDelivery-TAM-AP5-stud';
$string['access_lenovo_servicedelivery_tam_em5_stud'] = 'Lenovo-ServiceDelivery-TAM-EM5-stud';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM GEO manager user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_us1_mgr'] = 'Lenovo-ServiceDelivery-TAM-US1-mgr';
$string['access_lenovo_servicedelivery_tam_ca1_mgr'] = 'Lenovo-ServiceDelivery-TAM-CA1-mgr';
$string['access_lenovo_servicedelivery_tam_la1_mgr'] = 'Lenovo-ServiceDelivery-TAM-LA1-mgr';
$string['access_lenovo_servicedelivery_tam_ap1_mgr'] = 'Lenovo-ServiceDelivery-TAM-AP1-mgr';
$string['access_lenovo_servicedelivery_tam_em1_mgr'] = 'Lenovo-ServiceDelivery-TAM-EM1-mgr';

$string['access_lenovo_servicedelivery_tam_us2_mgr'] = 'Lenovo-ServiceDelivery-TAM-US2-mgr';
$string['access_lenovo_servicedelivery_tam_ca2_mgr'] = 'Lenovo-ServiceDelivery-TAM-CA2-mgr';
$string['access_lenovo_servicedelivery_tam_la2_mgr'] = 'Lenovo-ServiceDelivery-TAM-LA2-mgr';
$string['access_lenovo_servicedelivery_tam_ap2_mgr'] = 'Lenovo-ServiceDelivery-TAM-AP2-mgr';
$string['access_lenovo_servicedelivery_tam_em2_mgr'] = 'Lenovo-ServiceDelivery-TAM-EM2-mgr';

$string['access_lenovo_servicedelivery_tam_us3_mgr'] = 'Lenovo-ServiceDelivery-TAM-US3-mgr';
$string['access_lenovo_servicedelivery_tam_ca3_mgr'] = 'Lenovo-ServiceDelivery-TAM-CA3-mgr';
$string['access_lenovo_servicedelivery_tam_la3_mgr'] = 'Lenovo-ServiceDelivery-TAM-LA3-mgr';
$string['access_lenovo_servicedelivery_tam_ap3_mgr'] = 'Lenovo-ServiceDelivery-TAM-AP3-mgr';
$string['access_lenovo_servicedelivery_tam_em3_mgr'] = 'Lenovo-ServiceDelivery-TAM-EM3-mgr';

$string['access_lenovo_servicedelivery_tam_us4_mgr'] = 'Lenovo-ServiceDelivery-TAM-US4-mgr';
$string['access_lenovo_servicedelivery_tam_ca4_mgr'] = 'Lenovo-ServiceDelivery-TAM-CA4-mgr';
$string['access_lenovo_servicedelivery_tam_la4_mgr'] = 'Lenovo-ServiceDelivery-TAM-LA4-mgr';
$string['access_lenovo_servicedelivery_tam_ap4_mgr'] = 'Lenovo-ServiceDelivery-TAM-AP4-mgr';
$string['access_lenovo_servicedelivery_tam_em4_mgr'] = 'Lenovo-ServiceDelivery-TAM-EM4-mgr';

$string['access_lenovo_servicedelivery_tam_us5_mgr'] = 'Lenovo-ServiceDelivery-TAM-US5-mgr';
$string['access_lenovo_servicedelivery_tam_ca5_mgr'] = 'Lenovo-ServiceDelivery-TAM-CA5-mgr';
$string['access_lenovo_servicedelivery_tam_la5_mgr'] = 'Lenovo-ServiceDelivery-TAM-LA5-mgr';
$string['access_lenovo_servicedelivery_tam_ap5_mgr'] = 'Lenovo-ServiceDelivery-TAM-AP5-mgr';
$string['access_lenovo_servicedelivery_tam_em5_mgr'] = 'Lenovo-ServiceDelivery-TAM-EM5-mgr';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM GEO administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_us1_admin'] = 'Lenovo-ServiceDelivery-TAM-US1-admin';
$string['access_lenovo_servicedelivery_tam_ca1_admin'] = 'Lenovo-ServiceDelivery-TAM-CA1-admin';
$string['access_lenovo_servicedelivery_tam_la1_admin'] = 'Lenovo-ServiceDelivery-TAM-LA1-admin';
$string['access_lenovo_servicedelivery_tam_ap1_admin'] = 'Lenovo-ServiceDelivery-TAM-AP1-admin';
$string['access_lenovo_servicedelivery_tam_em1_admin'] = 'Lenovo-ServiceDelivery-TAM-EM1-admin';

$string['access_lenovo_servicedelivery_tam_us2_admin'] = 'Lenovo-ServiceDelivery-TAM-US2-admin';
$string['access_lenovo_servicedelivery_tam_ca2_admin'] = 'Lenovo-ServiceDelivery-TAM-CA2-admin';
$string['access_lenovo_servicedelivery_tam_la2_admin'] = 'Lenovo-ServiceDelivery-TAM-LA2-admin';
$string['access_lenovo_servicedelivery_tam_ap2_admin'] = 'Lenovo-ServiceDelivery-TAM-AP2-admin';
$string['access_lenovo_servicedelivery_tam_em2_admin'] = 'Lenovo-ServiceDelivery-TAM-EM2-admin';

$string['access_lenovo_servicedelivery_tam_us3_admin'] = 'Lenovo-ServiceDelivery-TAM-US3-admin';
$string['access_lenovo_servicedelivery_tam_ca3_admin'] = 'Lenovo-ServiceDelivery-TAM-CA3-admin';
$string['access_lenovo_servicedelivery_tam_la3_admin'] = 'Lenovo-ServiceDelivery-TAM-LA3-admin';
$string['access_lenovo_servicedelivery_tam_ap3_admin'] = 'Lenovo-ServiceDelivery-TAM-AP3-admin';
$string['access_lenovo_servicedelivery_tam_em3_admin'] = 'Lenovo-ServiceDelivery-TAM-EM3-admin';

$string['access_lenovo_servicedelivery_tam_us4_admin'] = 'Lenovo-ServiceDelivery-TAM-US4-admin';
$string['access_lenovo_servicedelivery_tam_ca4_admin'] = 'Lenovo-ServiceDelivery-TAM-CA4-admin';
$string['access_lenovo_servicedelivery_tam_la4_admin'] = 'Lenovo-ServiceDelivery-TAM-LA4-admin';
$string['access_lenovo_servicedelivery_tam_ap4_admin'] = 'Lenovo-ServiceDelivery-TAM-AP4-admin';
$string['access_lenovo_servicedelivery_tam_em4_admin'] = 'Lenovo-ServiceDelivery-TAM-EM4-admin';

$string['access_lenovo_servicedelivery_tam_us5_admin'] = 'Lenovo-ServiceDelivery-TAM-US5-admin';
$string['access_lenovo_servicedelivery_tam_ca5_admin'] = 'Lenovo-ServiceDelivery-TAM-CA5-admin';
$string['access_lenovo_servicedelivery_tam_la5_admin'] = 'Lenovo-ServiceDelivery-TAM-LA5-admin';
$string['access_lenovo_servicedelivery_tam_ap5_admin'] = 'Lenovo-ServiceDelivery-TAM-AP5-admin';
$string['access_lenovo_servicedelivery_tam_em5_admin'] = 'Lenovo-ServiceDelivery-TAM-EM5-admin';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM GEO administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_us_geoadmin'] = 'Lenovo-ServiceDelivery-TAM-US-geoadmin';
$string['access_lenovo_servicedelivery_tam_ca_geoadmin'] = 'Lenovo-ServiceDelivery-TAM-CA-geoadmin';
$string['access_lenovo_servicedelivery_tam_la_geoadmin'] = 'Lenovo-ServiceDelivery-TAM-LA-geoadmin';
$string['access_lenovo_servicedelivery_tam_ap_geoadmin'] = 'Lenovo-ServiceDelivery-TAM-AP-geoadmin';
$string['access_lenovo_servicedelivery_tam_em_geoadmin'] = 'Lenovo-ServiceDelivery-TAM-EM-geoadmin';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM GEO site administrator user types.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_us_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-US-siteadmin';
$string['access_lenovo_servicedelivery_tam_ca_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-CA-siteadmin';
$string['access_lenovo_servicedelivery_tam_la_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-LA-siteadmin';
$string['access_lenovo_servicedelivery_tam_ap_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-AP-siteadmin';
$string['access_lenovo_servicedelivery_tam_em_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-EM-siteadmin';

// Lenovo *******************************************************************************
// @03 - 05/05/20 - Added strings for SD TAM users.
// ServiceDelivery TAM site administrator user type.
// Lenovo *******************************************************************************
$string['access_lenovo_servicedelivery_tam_siteadmin'] = 'Lenovo-ServiceDelivery-TAM-siteadmin';


// Special-access user types and roles (shortnames)...
// $string['access_special_user'] = 'Special-access';
// $string['access_specialaccess_stud'] = 'SpecialAccess-stud';
// $string['role_specialaccess_student'] = 'specialaccess-student';

// Self-support user types and roles (shortnames)...
$string['access_selfsupport_stud'] = 'SelfSupport-stud';
$string['role_selfsupport_student'] = 'selfsupport-student';

// SiteHelp user types and roles (shortnames)...
$string['access_sitehelp_stud'] = 'SiteHelp-stud';
$string['role_sitehelp_stud'] = 'sitehelp-stud';
$string['role_sitehelp_student'] = 'sitehelp-student';        // 08/14/18

// Customized capabilites...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['swtc:ebg_access_gtp_portfolio'] = 'Access to GTP portfolio';
$string['swtc:ebg_access_lenovo_portfolio'] = 'Access to Lenovo portfolio';
// $string['swtc:ebg_access_lenovoandibm_portfolio'] = 'Access to Lenovo and IBM portfolios';
$string['swtc:ebg_access_serviceprovider_portfolio'] = 'Access to ServiceProvider portfolio';
$string['swtc:ebg_access_lenovointernal_portfolio'] = 'Access to Lenovo Internal portfolio';
$string['swtc:ebg_access_lenovosharedresources'] = 'Access to Lenovo Shared Resources (Master)';
$string['swtc:ebg_access_maintech_portfolio'] = 'Access to Maintech portfolio';
$string['swtc:ebg_access_ibm_portfolio'] = 'Access to IBM portfolio';
$string['swtc:ebg_access_asp_portfolio'] = 'Access to ASP portfolio';
$string['swtc:ebg_access_premiersupport_portfolio'] = 'Access to PremierSupport portfolio';
$string['swtc:ebg_access_servicedelivery_portfolio'] = 'Access to ServiceDelivery portfolio';
$string['swtc:ebg_access_sitehelp_portfolio'] = 'Access to Site Help portfolio';
$string['swtc:ebg_access_curriculums_portfolio'] = 'Access to Curriculums portfolio';

// @01 - For all PS / SD users, added custom submit assignment capability.
$string['swtc:ebg_mod_assign_submit_premiersupport'] = 'Submit assignment (PremierSupport users)';
$string['swtc:ebg_mod_assign_submit_servicedelivery'] = 'Submit assignment (ServiceDelivery users)';


// Customized capabilites strings...
// Important! All role strings must match the roles defined on the Lenovo EBG LMS web site.
$string['cap_ebg_access_gtp_portfolio'] = 'local/swtc:ebg_access_gtp_portfolio';
$string['cap_ebg_access_lenovo_portfolio'] = 'local/swtc:ebg_access_lenovo_portfolio';
// $string['cap_ebg_access_lenovoandibm_portfolio'] = 'local/swtc:ebg_access_lenovoandibm_portfolio';
$string['cap_ebg_access_serviceprovider_portfolio'] = 'local/swtc:ebg_access_serviceprovider_portfolio';
$string['cap_ebg_access_lenovointernal_portfolio'] = 'local/swtc:ebg_access_lenovointernal_portfolio';
$string['cap_ebg_access_lenovosharedresources'] = 'local/swtc:ebg_access_lenovosharedresources';
$string['cap_ebg_access_maintech_portfolio'] = 'local/swtc:ebg_access_maintech_portfolio';
$string['cap_ebg_access_ibm_portfolio'] = 'local/swtc:ebg_access_ibm_portfolio';
$string['cap_ebg_access_asp_portfolio'] = 'local/swtc:ebg_access_asp_portfolio';
$string['cap_ebg_access_premiersupport_portfolio'] = 'local/swtc:ebg_access_premiersupport_portfolio';
$string['cap_ebg_access_servicedelivery_portfolio'] = 'local/swtc:ebg_access_servicedelivery_portfolio';
$string['cap_ebg_access_sitehelp_portfolio'] = 'local/swtc:ebg_access_sitehelp_portfolio';
$string['cap_ebg_access_curriculums_portfolio'] = 'local/swtc:ebg_access_curriculums_portfolio';

// @01 - For all PS / SD users, added custom submit assignment capability.
$string['cap_ebg_mod_assign_submit_premiersupport'] = 'local/swtc:ebg_mod_assign_submit_premiersupport';
$string['cap_ebg_mod_assign_submit_servicedelivery'] = 'local/swtc:ebg_mod_assign_submit_servicedelivery';

// Capability strings for viewing curriculums and viewing reports. Since these are new, use underscores instead of dashes.
$string['cap_ebg_view_curriculums'] = 'local/swtc:ebg_view_curriculums';
$string['cap_ebg_view_mgmt_reports'] = 'local/swtc:ebg_view_mgmt_reports';
$string['cap_ebg_view_stud_reports'] = 'local/swtc:ebg_view_stud_reports';

$string['swtc:ebg_view_curriculums'] = 'Access to view curriculums';
$string['swtc:ebg_view_mgmt_reports'] = 'Access to view management reports';
$string['swtc:ebg_view_stud_reports'] = 'Access to view student reports';

// When editing a user, if Accesstype is blank, error message...
$string['access_type_required'] = 'Accesstype is blank; the EBG Server Education Access type field for each user must be set.';

// Lenovo *******************************************************************************
// For site administration menu
// Lenovo *******************************************************************************
$string['lenovo_services'] = 'Lenovo Services Education';
$string['settings'] = 'Settings';
// Lenovo *******************************************************************************
$string['lenovo'] = 'Lenovo';
$string['lenovo_desc'] = 'Global "Lenovo" setting so that it can be checked in Moodle core files to call customized Lenovo functions.';
// Lenovo *******************************************************************************
// Debug settings strings.
// Lenovo *******************************************************************************
$string['debugsettings'] = 'Debug Settings';
$string['swtcdebug'] = 'Debug';
$string['swtcdebug_desc'] = 'Normally disabled on production, enable to set global debugging flag. Warning: this will turn on debugging for all users!';
// Lenovo *******************************************************************************
// Batch email settings strings.
// Lenovo *******************************************************************************
$string['batchemailsettings'] = 'Batch Email Settings';
$string['swtcbatchemail'] = 'Enable batch email to students';
$string['swtcbatchemail_desc'] = 'Normally enabled. If enrolling many users in a course using a cohort sync enrollment method, temporarily disable (uncheck) to turn-off the sending of the "Welcome" email to each user. When finished, switch back to enabled (checked).

Note that this setting only affects users enrolled in courses using a a cohort sync enrollment method. Users enrolled using any other method will always be sent the "Welcome" email.';
// Lenovo *******************************************************************************
// Invitation settings strings.
// Lenovo *******************************************************************************
$string['invitationsettings'] = 'New User Account Invitation Settings';
$string['invitationsettings_desc'] = '<strong>Create new user</strong> now sends an invitation to the user by email. These invitations can be used only once. Users clicking on the email link are directed to the original <strong>New account</strong> page.';
$string['inviteexpiration'] = 'Invitation expiration';
$string['inviteexpiration_desc'] = 'Length of time that an invitation is valid (in seconds). Default is 72 hours.';
$string['inviteemailmsgtxt'] =
    'INSTRUCTIONS:' . "\n" .
    '------------------------------------------------------------' . "\n" .
    'You have been invited to create a new user account on the site: {$a->fullname}. You will ' .
    'need to navigate to the site to create your new user account and password. Be advised that by ' .
    'clicking on the site access link provided in this ' .
    'email you are acknowledging that:' . "\n" .
    ' --you are the person to whom this email was addressed and for whom this' .
    '   invitation is intended;' . "\n" .
    ' --the link below will expire on ({$a->expiration}).' . "\n\n" .
    'ACCESS LINK:' . "\n" .
    '------------------------------------------------------------' . "\n" .
    '{$a->inviteurl}' . "\n\n" .
    'If you believe that you have received this message in error or are in need ' .
    'of assistance, please contact: {$a->supportemail}.';

// For site administration menu: ServiceBench sub-menu; also for general ServiceBench support.
$string['lenovo_servicebench'] = 'ServiceBench';
$string['profile_field_accesstype'] = 'profile_field_accesstype';
$string['profile_field_sbtechnicianid'] = 'profile_field_sbtechnicianid';
$string['profile_field_tscertifications'] = 'profile_field_tscertifications';
$string['profile_field_geo'] = 'profile_field_geo';
$string['profile_field_legcertifications'] = 'profile_field_legcertifications';
$string['profile_field_tscertdates'] = 'profile_field_tscertdates';
$string['profile_field_legcertdates'] = 'profile_field_legcertdates';
$string['profile_field_tacertifications'] = 'profile_field_tacertifications';
$string['profile_field_tacertdates'] = 'profile_field_tacertdates';
$string['profile_field_accesstype2'] = 'profile_field_accesstype2';     // @03

// User profile fields - shortnames only
$string['shortname_Accesstype'] = 'Accesstype';
$string['shortname_sbtechnicianid'] = 'sbtechnicianid';
$string['shortname_tscertifications'] = 'tscertifications';
$string['shortname_geo'] = 'geo';
$string['shortname_legcertifications'] = 'legcertifications';
$string['shortname_tscertdates'] = 'tscertdates';
$string['shortname_legcertdates'] = 'legcertdates';
$string['shortname_accesstype2'] = 'accesstype2';       // @03

// ThinkSystem certification strings
$string['ts_racks_cert_string'] = 'TS_Racks';
$string['ts_bladenodes_cert_string'] = 'TS_BladeNodes';
$string['ts_highdensity_cert_string'] = 'TS_HighDensity';
$string['ts_towers_cert_string'] = 'TS_Towers';
$string['ts_basemodules_cert_string'] = 'TS_BaseModules';
$string['ts_highend_cert_string'] = 'TS_HighEnd';
$string['ts_missioncritical_cert_string'] = 'TS_MissionCritical';
$string['ts_de_storage_es71931_cert_string'] = 'TS-DE_Storage-ES71931';
$string['ts_dm_storage_es71914_cert_string'] = 'TS-DM_Storage-ES71914';
$string['ts_cloudos_es41782'] = 'TS-CLOUDOS-ES41782';
$string['ts_db_400d_800d_es41729'] = 'TS-DB(400D/800D)-ES41729';
$string['ts_db610s_es41727'] = 'TS-DB610S-ES41727';
$string['ts_db620s_es41728'] = 'TS-DB620S-ES41728';
$string['ts_ds_6200_4200_2200_es41607'] = 'TS-DS(6200/4200/2200)-ES41607';
$string['ts_ne_1032_10032_2572_es41673'] = 'TS-NE(1032/10032/2572)-ES41673';
$string['ts_ne_0152_es41923'] = 'TS-NE(0152)-ES41923';      // 01/17/20
$string['ts_ne1032_es41735'] = 'TS-NE1032-ES41735';
$string['ts_ne2572_es41773'] = 'TS-NE2572-ES41773';
$string['ts_sd530_es71629'] = 'TS-SD530-ES71629';
$string['ts_sd650_es71709'] = 'TS-SD650-ES71709';
$string['ts_sn550_es71741'] = 'TS-SN550-ES71741';
$string['ts_sn850_es71740'] = 'TS-SN850-ES71740';
$string['ts_sr250_es71935'] = 'TS-SR250-ES71935';
$string['ts_sr530_es71749'] = 'TS-SR530-ES71749';
$string['ts_sr550_es71750'] = 'TS-SR550-ES71750';
$string['ts_sr570_es71790'] = 'TS-SR570-ES71790';
$string['ts_sr590_es71791'] = 'TS-SR590-ES71791';
$string['ts_sr630_es71744'] = 'TS-SR630-ES71744';
$string['ts_sr635_es71942'] = 'TS-SR635-ES71942';       // 01/17/20
$string['ts_sr650_es71743'] = 'TS-SR650-ES71743';
$string['ts_sr655_es71943'] = 'TS-SR655-ES71943';       // 01/17/20
$string['ts_sr670_es71878'] = 'TS-SR670-ES71878';
$string['ts_sr850_es71718'] = 'TS-SR850-ES71718';
$string['ts_sr860_es71754'] = 'TS-SR860-ES71754';
$string['ts_sr950_es71736'] = 'TS-SR950-ES71736';
$string['ts_st250_es71934'] = 'TS-ST250-ES71934';
$string['ts_st50_es71875'] = 'TS-ST50-ES71875';
$string['ts_se350_es71911'] = 'TS-SE350-ES71911';
$string['ts_st550_st558_es71764'] = 'TS-ST550-ST558-ES71764';
$string['ts_xclarity_essentials_es21787'] = 'TS-xClarity-Essentials-ES21787';
$string['ts_xclarity_es71043'] = 'TS-xClarity-ES71043';
$string['ts_amdbasetools_es51998'] = 'TS-AMDBaseTools-ES51998';     // 01/17/20
$string['ts_amdarchitecture_es41999'] = 'TS-AMDArchitecture-ES41999';     // 01/17/20
$string['ts_all'] = 'TS_ALL';
$string['ts_all_old'] = 'TS_ALL_OLD';
$string['ts_none'] = 'TS_NONE';

// ThinkAgile certification strings
// 05/28/19 - Removed older TA certifications that should not be tracked anymore (based on note from Cheryl dated 05/28/19).
// $string['ta_hx_st550_cert_string'] = 'TA-HX-ST550';
// $string['ta_hx_sd530_cert_string'] = 'TA-HX-SD530';
// $string['ta_hx_sr630_cert_string'] = 'TA-HX-SR630';
// $string['ta_hx_sr650_cert_string'] = 'TA-HX-SR650';
// $string['ta_hx_sr950_cert_string'] = 'TA-HX-SR950';
// $string['ta_thinkagile_sxn_sxm_cert_string'] = 'ThinkAgile-SXN/SXM';
// $string['ta_vx_sd530_cert_string'] = 'TA-VX-SD530';
// $string['ta_vx_sr630_cert_string'] = 'TA-VX-SR630';
// $string['ta_vx_sr650_cert_string'] = 'TA-VX-SR650';
// $string['ta_cp_sd530_cert_string'] = 'TA-CP-SD530';
$string['ta_cp_series_es21956_cert_string'] = 'TA-CP-Series-ES21956';
$string['ta_cp_series_es41868_cert_string'] = 'TA-CP-Series-ES41868';
$string['ta_cp_tools_es61921_cert_string'] = 'TA-CP-Tools-ES61921';
$string['ta_hx_series_es41641_cert_string'] = 'TA-HX-Series-ES41641';
$string['ta_sx_nutanix_es41785_cert_string'] = 'TA-SX-Nutanix-ES41785';
$string['ta_sx_azure_es41765_cert_string'] = 'TA-SX-Azure-ES41765';
$string['ta_vx_series_es41800_cert_string'] = 'TA-VX-Series-ES41800';
$string['ta_all'] = 'TA_ALL';
$string['ta_none'] = 'TA_NONE';


// Other certification strings
$string['basetools_cert_string'] = 'BaseTools';
$string['oth_sd530_n400_es71538_cert_string'] = 'Oth-SD530/N400-ES71538';
$string['rs_g8052_es5237_cert_string'] = 'RS-G8052-ES5237';
$string['rs_g8272_es41483_cert_string'] = 'RS-G8272-ES41483';
$string['rs_g8296_es41288_cert_string'] = 'RS-G8296-ES41288';

// Manual certification strings
$string['sxm_9565_rch_rcj_rck_cert_string'] = 'SXM_9565-RCH/RCJ/RCK';
$string['sxm_9565_rcc_rcd_rce_cert_string'] = 'SXM_9565-RCC/RCD/RCE';
$string['sxn_9565_rcf_rcg_cert_string'] = 'SXN_9565-RCF/RCG';


// Legacy certification strings
$string['leg_basemodules_cert_string'] = 'RXBaseDCG';
$string['leg_thinkserver_cert_string'] = 'RxThinkServer';
$string['leg_san_cert_string'] = 'RxEntrySAN';
$string['leg_bladectr_cert_string'] = 'RXBladeCtr';
$string['leg_flex_cert_string'] = 'RXFlex';
$string['leg_nextscale_cert_string'] = 'RXNextScale';
$string['leg_rx3550_3650_cert_string'] = 'RX3550_3650';
$string['leg_rx3850_3950_x5_cert_string'] = 'RX3850_3950_X5';
$string['leg_rx3850_3950_x6_cert_string'] = 'RX3850_3950_X6';
$string['leg_all'] = 'LEG_ALL';
$string['leg_none'] = 'LEG_NONE';

// Strings for SB cron jobs.
$string['crontaskexport'] = 'Lenovo DCG Services Education: run export to SB task';
$string['crontaskpreexport'] = 'Lenovo DCG Services Education: preview export to SB task';
$string['crontaskimport'] = 'Lenovo DCG Services Education: run import from SB task';
$string['crontaskpreimport'] = 'Lenovo DCG Services Education: preview import from SB task';

// Strings for update curriculums cron jobs.
$string['crontaskverifycurriculums'] = 'Lenovo DCG Services Education: run verify curriculums task';
$string['crontaskupdatecurriculums'] = 'Lenovo DCG Services Education: run update curriculums task';

// Strings for SB email support.
$string['swtc_sb_emailadminlog_subject'] = '{$a->sitename} ServiceBench log file for {$a->date}';
$string['swtc_sb_emailadminlog_body'] = '

Attached is the ServiceBench log file for {$a->date}.

';

// Strings for curriculums email support.
$string['swtc_curriculums_email_subject'] = '{$a->sitename} curriculums log file for {$a->date}';
$string['swtc_curriculums_email_body'] = '

Attached is the curriculums log file for {$a->date}.

';

// 01/31/20 - Added strings to support local_swtc_capture_click and local_swtc_capture_enrollment to record an event.
$string['related_clicked'] = 'Related clicked';
$string['suggested_clicked'] = 'Suggested clicked';
$string['related_enrolled'] = 'Related enrolled';
$string['suggested_enrolled'] = 'Suggested enrolled';

// Lenovo ********************************************************************************.
// @02 - Moved some DCG custom course format strings to /local/swtc/lang/en/local_swtc.php
//          to remove duplication of common strings.
// Lenovo ********************************************************************************.
// Common Lenovo customized course format strings.
// Lenovo ********************************************************************************.
$string['newsectionname'] = 'New name for section {$a}';
$string['currentsection'] = 'This section';
$string['editsection'] = 'Edit section';
$string['editsectionname'] = 'Edit section name';
$string['deletesection'] = 'Delete section';

$string['overview_coursetitle_line1_formatting'] = '<p><span style="color: inherit; font-family: inherit; font-size: 24.5px; font-weight: bold;">string-coursefullname</span></p><p></p>';
$string['overview_coursecode_line2_formatting'] = '<p><b>Course code: </b>string-courseshortname<br />';
$string['overview_currentversion_line3_formatting'] = '<b>Current version: </b>string-currentversion<br />';
$string['overview_machinetypes_line4_formatting'] = '<b>Machine type(s): </b>string-machinetypes<br />';
$string['overview_duration_line5_formatting'] = '<b>Duration: </b>string-duration minutes<br />';
$string['overview_heading_line6_formatting'] = '<p><br /><b>Course overview</b></p>';
$string['overview_version'] = 'version:';

$string['machinetypes'] = 'Machine type(s)';
$string['machinetypes_help'] = 'List all machine type(s) for the system(s) specifically covered in this event (not prerequisite events). Separate each machine type by a space. If  the system does not have a machine type, use N/A.';

$string['duration'] = 'Duration (in minutes only)';
$string['duration_help'] = 'Estimated duration to complete this course (in minutes only).';
