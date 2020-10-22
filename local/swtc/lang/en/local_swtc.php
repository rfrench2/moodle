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
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 */

$string['debug_string'] = 'SWTC *****************************************************************************//';


// SWTC *******************************************************************************
//
// SWTC *******************************************************************************
$string['swtcpubdate'] = 'Course publication date';
$string['swtcpubdate_help'] = 'Set this date to the publication (GA) date of the course. In other words, the date the course will be moved from the SWTC Internal Portfolio to any of the external portfolios.';

// SWTC *******************************************************************************
// Added strings to support "Curriculums" tab in "My courses".
// SWTC *******************************************************************************
$string['curriculums'] = 'Curriculums';
$string['mycurriculums'] = 'My Curriculums';
$string['allcurriculums'] = 'All Curriculums';

// SWTC *******************************************************************************
// PremierSupport curriculum courses.
//      TODO: Either make these generic so everyone can use or add "premiersupport" to the names so no one else will use.
// SWTC *******************************************************************************
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

// SWTC *******************************************************************************
// ServiceDelivery curriculum courses.
// SWTC *******************************************************************************
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

// SWTC *******************************************************************************
// For use in locking access to customsql report categories (/report/customsql).
// SWTC *******************************************************************************
$string['customsql:viewallcats'] = 'View custom queries reports in all categories';
$string['customsql:viewpremiersupportcat'] = 'View custom queries reports in PremierSupport category';

// SWTC *******************************************************************************
// For use in new user account invitation (/auth/emailadmin).
// SWTC *******************************************************************************
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
$string['default_subject'] = 'SWTC DCG LMS New User Account Invitation for {$a}';
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
$string['newuserinvitation_desc1'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. Enter the email address you want the invitation email sent to. When you receive the email, click the hyperlink provided in the email to create your user account.';
$string['newuserinvitation_desc1a'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>.';
$string['newuserinvitation_desc2'] = 'Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['newuserinvitation_desc2a'] = '<strong>IBM</strong> and <strong>SWTC</strong> personnel may use this form to request access to the site. If you are a <strong>SWTC Authorized Service Provider (ASP)</strong>, please contact your ASP consultant and they can assist you with access. New accounts will be confirmed within 1-2 business days.';
$string['newuserinvitation_desc3'] = 'When your account is confirmed, your password will be automatically generated and sent to the email address that you enter below.';
$string['newuserinvitation_desc3a'] = 'Enter the email address you want the invitation email sent to. When you receive the email, click the hyperlink provided in the email to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['newuserinvitation_desc4a'] = '<p><strong>IBM</strong> and <strong>SWTC</strong> personnel may use this form to request access to the site. If you are a <strong>SWTC Authorized Service Provider (ASP)</strong>, please contact your ASP consultant and they can assist you with access. New accounts will be confirmed within 1-2 business days.</p>';
$string['invitationsent_desc1'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. Your personalized invitation has been sent to <strong>{$a}</strong>. Click the hyperlink provided in the email to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['status_invite_active_message'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. An invitation for <strong>{$a->email}</strong> was requested on {$a->timesent} and can still be used until {$a->expiration}. As a reminder, the invitation hyperlink is :' . "\n" .
'{$a->inviteurl}' . "\n\n" .
'Click the hyperlink to create your user account. Please note that this invitation is only for you, should not be shared, and expires in 72 hours.';
$string['status_invite_used_message'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. An invitation using the email address <strong>{$a->email}</strong> was accepted on {$a->timeused}. This email address cannot be used again. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';
$string['status_invite_expired_message'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. An invitation for <strong>{$a->email}</strong> was requested on {$a->timesent} and expired on  {$a->expiration}. If you still require access, navigate to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong> and click on <strong>Create new account</strong>. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';
$string['status_invite_invalid_message'] = 'Thank-you for requesting access to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong>. A problem was encountered processing this invitation. If you still require access, navigate to the <strong>SWTC DCG Services Education Learning Management System (LMS)</strong> and click on <strong>Create new account</strong>. If you believe that you have received this message in error or are in need of assistance, please contact: {$a->supportemail}.';

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
$string['configtitle'] = 'SWTC plugin';
$string['swtccustom'] = 'swtccustom';

$string['swtc_set_access_type'] = "Set Access type for all users";

// Options for the Type of course pull-down menu (taken from SWTC LMS custom course format).
// Also the names of the top level categories...
$string['one_portfolio'] = 'One Portfolio';
$string['two_portfolio'] = 'Two Portfolio';
$string['sitehelp_portfolio'] = 'Site Help Portfolio';
$string['curriculums_portfolio'] = 'Curriculums Portfolio';


// Important! String values based on Accesstype must match the customized user profile value 'Accesstype'...
// Generic role types...
$string['role_swtc_siteadmin'] = 'swtc-siteadmin';
$string['role_swtc_admin'] = 'swtc-admin';
$string['role_swtc_inst'] = 'swtc-inst';
$string['role_swtc_stud'] = 'swtc-stud';

// SWTC user types and roles (shortnames)...
// Important! All role strings must match the roles defined on the SWTC LMS web site.
$string['access_swtc_stud'] = 'SWTC-stud';
$string['access_swtc_pregmatch_stud'] = '/SWTC-stud/i';
$string['role_swtc_student'] = 'swtc-student';

$string['access_swtc_inst'] = 'SWTC-inst';
$string['role_swtc_instructor'] = 'swtc-instructor';

$string['access_swtc_admin'] = 'SWTC-admin';
$string['access_swtc_pregmatch_admin'] = '/SWTC-admin/i';
$string['role_swtc_administrator'] = 'swtc-administrator';

$string['access_swtc_siteadmin'] = 'SWTC-siteadmin';                        // 02/13/19
$string['access_swtc_pregmatch_siteadmin'] = '/SWTC-siteadmin/i';                       // ex: SWTC-siteadmin
$string['role_swtc_siteadministrator'] = 'swtc-siteadministrator';      // 02/13/19


$string['role_one_inst'] = 'one-inst';
$string['role_one_stud'] = 'one-stud';
$string['access_one_stud'] = 'One-stud';
$string['access_one_pregmatch_stud'] = '/One-stud/i';
$string['role_one_student'] = 'one-student';

$string['role_two_inst'] = 'two-inst';
$string['role_two_stud'] = 'two-stud';
$string['access_two_stud'] = 'Two-stud';
$string['access_two_pregmatch_stud'] = '/Two-stud/i';
$string['role_two_student'] = 'two-student';



// Strings for customized PS/SD groups menu; added string that contains all the GEOs (comma separated).
$string['access_all_geos'] = 'AP, CA, LA, EM, US';

// SWTC *******************************************************************************
// For site administration menu
// SWTC *******************************************************************************
$string['swtc_services'] = 'SWTC Services Education';
$string['settings'] = 'Settings';
// SWTC *******************************************************************************
$string['swtc'] = 'SWTC';
$string['swtc_desc'] = 'Global "SWTC" setting so that it can be checked in Moodle core files to call customized SWTC functions.';

// SiteHelp user types and roles (shortnames)...
$string['access_sitehelp_stud'] = 'SiteHelp-stud';
$string['role_sitehelp_stud'] = 'sitehelp-stud';
$string['role_sitehelp_student'] = 'sitehelp-student';

// Customized capabilites...
// Important! All role strings must match the roles defined on the SWTC LMS web site.
$string['swtc:swtc_access_one_portfolio'] = 'Access to ONE portfolio';
$string['swtc:swtc_access_two_portfolio'] = 'Access to TWO portfolio';
$string['swtc:swtc_access_sitehelp_portfolio'] = 'Access to Site Help portfolio';
$string['swtc:swtc_access_curriculums_portfolio'] = 'Access to Curriculums portfolio';

// Customized capabilites strings...
// Important! All role strings must match the roles defined on the SWTC LMS web site.
$string['cap_swtc_access_one_portfolio'] = 'local/swtc:swtc_access_one_portfolio';
$string['cap_swtc_access_two_portfolio'] = 'local/swtc:swtc_access_two_portfolio';
$string['cap_swtc_access_sitehelp_portfolio'] = 'local/swtc:swtc_access_sitehelp_portfolio';
$string['cap_swtc_access_curriculums_portfolio'] = 'local/swtc:swtc_access_curriculums_portfolio';

// SWTC *******************************************************************************
// Debug settings strings.
// SWTC *******************************************************************************
$string['debugsettings'] = 'Debug Settings';
$string['swtcdebug'] = 'Debug';
$string['swtcdebug_desc'] = 'Normally disabled on production, enable to set global debugging flag. Warning: this will turn on debugging for all users!';

// SWTC *******************************************************************************
// Invitation settings strings.
// SWTC *******************************************************************************
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

// For site administration menu: ServiceBench sub-menu; also for general ServiceBench support.;
$string['profile_field_accesstype'] = 'profile_field_accesstype';
$string['profile_field_geo'] = 'profile_field_geo';
$string['profile_field_accesstype2'] = 'profile_field_accesstype2';

// User profile fields - shortnames only
$string['shortname_accesstype'] = 'accesstype';
$string['shortname_geo'] = 'geo';
$string['shortname_accesstype2'] = 'accesstype2';

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

// SWTC ********************************************************************************.
// @02 - Moved some DCG custom course format strings to /local/swtc/lang/en/local_swtc.php
//          to remove duplication of common strings.
// SWTC ********************************************************************************.
// Common SWTC customized course format strings.
// SWTC ********************************************************************************.
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
