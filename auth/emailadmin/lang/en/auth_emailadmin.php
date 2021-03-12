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
 * Strings for component 'auth_emailadmin', language 'en', branch 'MOODLE_20_STABLE'
 * NOTE: Based on 'email' package by Martin Dougiamas
 *
 * @package   auth_emailadmin
 * @copyright 2012 onwards Felipe Carasso  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 10/30/20 - Initial writing.
 *
 */

$string['auth_emailadmindescription'] = '<p>Email-based self-registration with Admin confirmation enables a user to create their own account via a \'Create new account\' button on the login page. The site admins then receive an email containing a secure link to a page where they can confirm the account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>Note: In addition to enabling the plugin, email-based self-registration with admin confirmation must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['auth_emailadminnoemail'] = 'Tried to send you an email but failed!';
$string['auth_emailadminrecaptcha'] = 'Adds a visual/audio confirmation form element to the signup page for email self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See http://www.google.com/recaptcha/learnmore for more details. <br /><em>PHP cURL extension is required.</em>';
$string['auth_emailadminrecaptcha_key'] = 'Enable reCAPTCHA element';
$string['auth_emailadminsettings'] = 'Settings';
$string['auth_emailadminuserconfirmation'] = '
Hello {$a->firstname},

Welcome to the SWTC LMS! Your account has been approved and you can now access our education courses. We are glad you have joined us and look forward to providing you with courses to better assist you to learn about and service Lenovo DCG products.

Please visit the <a href=https://lenovoedu.lenovo.com/login/index.php>site</a> and login with the username (email address) and password that you created at registration. When accessing the site, you may need to accept a security certificate.

<a href="http://collabserv.us14.list-manage.com/track/click?u=871c753ad518bb7c904eb3460&amp;id=d73dc4fdf4&amp;e=e0570caad6">Sign me up</a> to receive SWTC announcements.

For any questions, please send an email to <a href=mailto:servicesedu@lenovo.com>servicesedu@lenovo.com</a>.

Thank you,

SWTC

--
SWTC
<a href=https://lenovoedu.lenovo.com/login/index.php>SWTC LMS</a>
<a href=mailto:servicesedu@lenovo.com>servicesedu@lenovo.com</a>

';
$string['auth_emailadminconfirmation'] = '
07/05/18 - Version 2.5

For the SWTC LMS administrator viewing this request, a new account on the SWTC LMS has been requested.

If the user has a Lenovo email address, click the following link to verify the email address of the user (opens Lenovo Central):

        {$a->verify_link}

To confirm the user\'s access to the SWTC LMS and send a confirmation email to the user, click the link immediately below:

		{$a->link}

Note: In most mail programs, the URL above should appear as a blue link which you can just click on. If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

Some important data from the user request follows:

user->lastname: {$a->lastname}
user->firstname: {$a->firstname}
user->email: {$a->email}
user->username: {$a->username}

Based on the email domain of the user, the initial value of "Access type" is shown immediately below (either "Lenovo-stud" or "IBM-stud"):

	 {$a->customfields}

 <strong>***************************************************************************</strong>
 <strong>Additional technical information follows:</strong>

 <strong>If this value is correct for the user,</strong>
	Nothing else needs to be done for the user. Click the link below to confirm the user\'s access to the system and send a confirmation email to the user.

<strong>If this value is NOT correct for the user,</strong>
	Click the link below to confirm the user\'s access to the system and send a confirmation email to the user. Then, you must change the user\'s "Access type" by doing the following:
		- Login to the SWTC LMS
		- Navigate to Site Administration -> Users -> Accounts -> Browse list of users
		- From the user list, locate the user listed above and left-click on their "First name / Last name"
		- From the user profile, click "Edit profile"
		- Scroll down to the "EBG Server Education" section
		- Using the "Access type" pull-down menu, select new access type for the user from the choices provided
		- Click "Update profile"

';
// @01 - Added strings for displaying PremierSupport checkbox; added a separate confirmation email body if the user is requesting
//              a PremierSupport access type.
$string['auth_emailadminconfirmation_premier'] = '
03/27/20 - Version 3.0

For the SWTC LMS administrator viewing this request, a new account on the SWTC LMS has been requested. <strong>In addition</strong>, the user is requesting a <strong>Premier Support</strong> access type. Refer to the procedure (included below) for directions on how to change the user\'s access type.

If the user has a Lenovo email address, click the following link to verify the email address of the user (opens Lenovo Central):

        {$a->verify_link}

To confirm the user\'s access to the SWTC LMS and send a confirmation email to the user, click the link immediately below:

		{$a->link}

Note: In most mail programs, the URL above should appear as a blue link which you can just click on. If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

Some important data from the user request follows:

user->lastname: {$a->lastname}
user->firstname: {$a->firstname}
user->email: {$a->email}
user->username: {$a->username}

Based on the email domain of the user, the initial value of "Access type" is shown immediately below (either "Lenovo-stud" or "IBM-stud"):

	 {$a->customfields}

 <strong>***************************************************************************</strong>
 <strong>Additional technical information follows:</strong>

 <strong>If this value is correct for the user,</strong>
	Nothing else needs to be done for the user. Click the link below to confirm the user\'s access to the system and send a confirmation email to the user.

<strong>If this value is NOT correct for the user,</strong>
	Click the link below to confirm the user\'s access to the system and send a confirmation email to the user. Then, you must change the user\'s "Access type" by doing the following:
		- Login to the SWTC LMS
		- Navigate to Site Administration -> Users -> Accounts -> Browse list of users
		- From the user list, locate the user listed above and left-click on their "First name / Last name"
		- From the user profile, click "Edit profile"
		- Scroll down to the "EBG Server Education" section
		- Using the "Access type" pull-down menu, select new access type for the user from the choices provided
		- Click "Update profile"

';
$string['auth_emailadminconfirmationsubject'] = '{$a}: account confirmation';
// $string['auth_emailadminconfirmsent'] = '<p>
// Your account has been registered and is pending confirmation by the administrator. You should expect to either receive a confirmation or to be contacted for further clarification.</p>
// ';
$string['auth_emailadminconfirmsent'] = '<p>
Your account has been registered and is pending confirmation by SWTC. You should expect to either receive a confirmation or to be contacted for further clarification within two business days.</p>
';
$string['auth_emailadminnotif_failed'] = 'Could not send registration notification to: ';
$string['auth_emailadminnoadmin'] = 'No admin found based on notification strategy. Please check auth_emailadmin configuration.';
$string['auth_emailadminnotif_strategy_key'] = 'Notification strategy:';
$string['auth_emailadminnotif_strategy'] = 'Defines the strategy to send the registration notifications. Available options are "first" admin user, "all" admin users or one specific admin user.';
$string['auth_emailadminnotif_strategy_first'] = 'First admin user';
$string['auth_emailadminnotif_strategy_all'] = 'All admin users';
$string['auth_emailadminnotif_strategy_allupdate'] = 'All admins and users with user update capability';

$string['pluginname'] = 'Email-based self-registration with admin confirmation';

// SWTC
$string['swtc_update_user_profile'] = 'Update user\'s profile?';
$string['swtc_run_nonconfirmed_report'] = 'Run NON-Confirmed user\'s report';

// @01 - Added strings for displaying PremierSupport checkbox.
$string['premiersupportuser'] = 'Update my access to a Premier Support user.';
$string['premiersupportuser_help'] = 'Select this checkbox if your access should be updated to a Premier Support user. Once verified, you will be enrolled in all the Premier Support courses and curriculums.';
