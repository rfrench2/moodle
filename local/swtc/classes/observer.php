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
 * Event observers used by the weeks course format.
 *
 * @package    local
 * @subpackage swtc/classes/observer.php
 * @copyright  2018 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	07/19/19 - Initial writing; migrating events to use this file.
 * 07/22/19 - In course_updated and course_created, dynamically add course_slider block (if not already there).
 * 07/23/19 - Adding \core\event\course_created event.
 * 11/01/19 - Changed functions that are called (sunsetting /lib/notifications.php).
 * 11/12/19 - IMPORTANT! If event is user_enrolment_created, the userid might be in either $eventdata->userid or $eventdata->relateduser
 *                      (see details in /local/swtc/lib/swtc_userlib.php).
 *
 **/

namespace local_swtc;
defined('MOODLE_INTERNAL') || die();

require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');
// require_once($CFG->libdir. '/blocklib.php');        // 07/22/19


class observer {

    /**
     * User loggedin
     *
     * @param \core\event\user_loggedin $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_loggedin(\core\event\user_loggedin $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * User loggedinas
     *
     * @param \core\event\user_loggedinas $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_loggedinas(\core\event\user_loggedinas $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * User updated
     *
     * @param \core\event\user_updated $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_updated(\core\event\user_updated $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * User created
     *
     * @param \core\event\user_created $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_created(\core\event\user_created $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * Course viewed
     *
     * @param \core\event\course_viewed $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header; only check if this is the site home page (SITEID).
     *
     */
    public static function course_viewed(\core\event\course_viewed $event) {

        // print_object($event);
        // Lenovo ********************************************************************************.
        //  07/23/19 - On the site home page, dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************.
        // if ($event->courseid == SITEID) {
        //     if (!featuredcourses_block_course_slider_exists($event->courseid)) {
        //         // featuredcourses_block_course_slider_dynamically_add($event->courseid);
        //     }
        // }

        local_swtc_assign_user_role($event);
    }

    /**
     * User enrollment created
     *
     * @param \core\event\user_enrolment_created $event the event
     * @return void
     *
     * History:
     *
     * 11/12/19 - IMPORTANT! If event is user_enrolment_created, the userid might be in either $eventdata->userid or $eventdata->relateduser
     *                      (see details in /local/swtc/lib/swtc_userlib.php).
     *
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {

        // print_object("In observer:user_enrolment_created. About to print event");
        // print_object($event);
        local_swtc_assign_user_role($event);
    }

    /**
     * Role assigned
     *
     * @param \core\event\role_assigned $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * User enrollment deleted
     *
     * @param \core\event\user_enrolment_deleted $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * User enrollment updated
     *
     * @param \core\event\user_enrolment_updated $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        local_swtc_assign_user_role($event);
    }

    /**
     * Course updated
     *
     * @param \core\event\course_updated $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function course_updated(\core\event\course_updated $event) {

        // print_object($event);
        // Lenovo ********************************************************************************.
        //  07/22/19 - Dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************.
        // if (!featuredcourses_block_course_slider_exists($event->courseid)) {
        //     featuredcourses_block_course_slider_dynamically_add($event->courseid);
        // }

        // swtc_course_created_or_updated($event);
    }

    /**
     * Course created
     *
     * @param \core\event\course_created $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function course_created(\core\event\course_created $event) {

        // print_object($event);
        // Lenovo ********************************************************************************.
        //  07/22/19 - Dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************.
        // if (!featuredcourses_block_course_slider_exists($event->courseid)) {
        //     featuredcourses_block_course_slider_dynamically_add($event->courseid);
        // }
        //
        // swtc_course_created_or_updated($event);
    }

    /**
     * All events
     *
     * @param * $event the event
     * @return void
     *
     * History:
     *
     * 07/23/19 - Added this header.
     *
     */
    public static function observe_all($event) {
        global $SESSION;

        // Only print for debugging.
        // print_object($event);
        // print_object($SESSION->EBGLMS);
        // swtc_course_updated($event);
    }

}
