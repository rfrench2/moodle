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
 * @subpackage swtc/db/events.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/17/20 - Initial writing.
 * 02/23/21 - Removed a few events and cleaned up.
 *
 * Notes about using this file
 *
 *    The messages that are captured here are routed to the method (callback) in the php file listed (includefile).
 *    Important! The event that is being captured has already happened. For example, for user_loggedin,
 *    the user has already been logged into the SWTC LMS site.
 *            user_created event happens when a new userid is created.
 *            user_updated event happens when an existing user's profile is updated.
 *            user_loggedin event happens when an existing user logs into the site.
 *            course_viewed event happens when a user views a course.
 *            user_enrolment_created event happens when a user is enrolled in a course.
 *          course_updated  event happens when a course is updated for any reason.
 *
 * The following is copied from https://docs.moodle.org/dev/Event_2#Event_observers
 *      Event observers
 *          Observers are described in db/events.php in the array $observers, the array is not
 *               indexed and contains a list of observers defined as an array with the following
 *               properties:
 *                   eventname – fully qualified event class name or "*" indicating all events, ex.:
 *                       \plugintype_pluginname\event\something_happened.
 *                   callback - PHP callable type.
 *                   includefile - optional. File to be included before calling the observer. Path relative
 *                      to dirroot.
 *                   priority - optional. Defaults to 0. Observers with higher priority are notified first.
 *                   internal - optional. Defaults to true. Non-internal observers are not called during
 *                          database transactions, but instead after a successful commit of the transaction.
 *
 *
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
        array (
            'eventname'   => '\core\event\user_loggedin',
            'callback'    => '\local_swtc\observer::user_loggedin',
        ),
        array (
            'eventname'   => '\core\event\user_loggedinas',
            'callback'    => '\local_swtc\observer::user_loggedinas',
        ),
        array (
            'eventname'   => '\core\event\user_updated',
            'callback'    => '\local_swtc\observer::user_updated',
        ),
        array (
            'eventname'   => '\core\event\user_created',
            'callback'    => '\local_swtc\observer::user_created',
        ),
        array (
            'eventname'   => '\core\event\course_viewed',
            'callback'    => '\local_swtc\observer::course_viewed',
        ),
        array (
            'eventname'   => '\core\event\user_enrolment_created',
            'callback'    => '\local_swtc\observer::user_enrolment_created',
        ),
        array (
            'eventname'   => '\core\event\role_assigned',
            'callback'    => '\local_swtc\observer::role_assigned',
        ),
        array (
            'eventname'   => '\core\event\user_enrolment_deleted',
            'callback'    => '\local_swtc\observer::user_enrolment_deleted',
        ),
        array (
            'eventname'   => '\core\event\user_enrolment_updated',
            'callback'    => '\local_swtc\observer::user_enrolment_updated',
        ),
);
