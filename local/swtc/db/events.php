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
 * Version details / History
 *
 * @package    local
 * @subpackage swtc/db/events.php
 * @copyright  2018 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *	History:
 * 01/02/16 - Initial writing; capturing two messages: user_loggedin and user_loggedinas.
 *	01/23/16 - Added two messages based on 01/22/16 meeting (user_updated and user_created).
 *	01/28/16 - Removed user_loggedinas for now (can't see need for it - only for admins and they should already have
 *                  access to everything they need).
 *	02/03/16 - Experimenting with course_viewed to see if the user views the frontpage (courseid 1), they get their new roles
*                    without logging out.
 *	04/11/16 - Added '\core\event\user_enrolment_created' and '\core\event\role_assigned' to monitor if a user was enrolled via a
 *						Course meta link' to the 'Shared resources (Master)' course; if so, change their role from generic 'Student' (or whatever it is)
 *						to the role stored in their user profile (AccessType flag).
 *	06/25/16 - Added back user_loggedinas so admins can test access for users.
 * 04/18/18 - Added new $SESSION->EBGLMS global variables and all its required changes; experimenting with changing
 *                      'internal' to true to capture events BEFORE they happen.
 * 05/01/18 - Testing removing of '\core\event\user_enrolment_created'.
 * 07/10/18 - Changed callback for '\core\event\user_enrolment_created'; added '\core\event\user_enrolment_deleted'
 *                          and '\core\event\user_enrolment_updated'.
 * 07/19/19 - Attempting to capture \core\event\course_updated events in local_swtc (for reporting purposes).
 * 07/23/19 - Adding \core\event\course_created event.
 * 11/01/19 - Modified how the new Lenovo EBGLMS classes and methods are called throughout all customized code.
 *
 */
/**
 * Notes about using this file
 *
 *	The messages that are captured here are routed to the method (callback) in the php file listed (includefile).
 *	Important! The event that is being captured has already happened. For example, for user_loggedin, the user has already been
 *		logged into the Lenovo EBG LMS site.
*			user_created event happens when a new userid is created.
*			user_updated event happens when an existing user's profile is updated.
*			user_loggedin event happens when an existing user logs into the site.
*			course_viewed event happens when a user views a course.
*			user_enrolment_created event happens when a user is enrolled in a course.
*          course_updated  event happens when a course is updated for any reason.
*
* The following is copied from https://docs.moodle.org/dev/Event_2#Event_observers
*      Event observers
*          Observers are described in db/events.php in the array $observers, the array is not indexed and contains a list of
*               observers defined as an array with the following properties:
*          eventname – fully qualified event class name or "*" indicating all events, ex.: \plugintype_pluginname\event\something_happened.
*          callback - PHP callable type.
*          includefile - optional. File to be included before calling the observer. Path relative to dirroot.
*          priority - optional. Defaults to 0. Observers with higher priority are notified first.
*          internal - optional. Defaults to true. Non-internal observers are not called during database transactions,
*                          but instead after a successful commit of the transaction.
*
 *
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_loggedin message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
        array (
            'eventname'   => '\core\event\user_loggedin',
            // 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_loggedin',
            // 'internal'    => true,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_loggedinas message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
         array (
			'eventname'   => '\core\event\user_loggedinas',
            // 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_loggedinas',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_updated message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
            'eventname'   => '\core\event\user_updated',
            // 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_updated',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_created message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
        array (
			'eventname'   => '\core\event\user_created',
            // 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_created',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\course_viewed message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\course_viewed',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::course_viewed',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_enrolment_created message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
        array (
            'eventname'   => '\core\event\user_enrolment_created',
            // 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_user_enrolment_created',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_enrolment_created',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\role_assigned message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\role_assigned',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::role_assigned',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_enrolment_deleted message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\user_enrolment_deleted',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_enrolment_deleted',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\user_enrolment_updated message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\user_enrolment_updated',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::user_enrolment_updated',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\course_updated message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\course_updated',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::course_updated',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture the \core\event\course_created message and invoke the function shown in callback.
        // Lenovo ********************************************************************************
		array (
			'eventname'   => '\core\event\course_created',
			// 'includefile' => '/local/swtc/lib/notifications.php',
            // 'callback'    => 'swtc_assign_user_role',
            'callback'    => '\local_swtc\observer::course_created',
            // 'internal'    => false,
        ),
        // Lenovo ********************************************************************************
        // Capture all events (for debugging only).
        // Lenovo ********************************************************************************
		array (
		 'eventname'   => '*',
         'callback'    => '\local_swtc\observer::observe_all',
        ),
);
