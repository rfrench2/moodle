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
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/swtc/lib/swtc_userlib.php');


class observer {
    /**
     * User loggedin
     * @param  coreeventuser_loggedin $event The eventdata.
     * @return void
     *
     * History
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_loggedin(\core\event\user_loggedin $event) {
        /**
         * $event->objectid  The userid (i.e. $USER->id).
         * $event->other['username']  The username used to login.
         */
        print_object("in observer; user_loggedin - about to print event");
        print_object($event);
        // die;
        $swtc_user = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtc_user->set_user_role($event);

    }

    /**
     * User loggedinas
     *
     * @param \core\event\user_loggedinas $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_loggedinas(\core\event\user_loggedinas $event) {

        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * User updated
     *
     * @param \core\event\user_updated $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_updated(\core\event\user_updated $event) {

        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * User created
     *
     * @param \core\event\user_created $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_created(\core\event\user_created $event) {

        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * Course viewed
     *
     * @param \core\event\course_viewed $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function course_viewed(\core\event\course_viewed $event) {

        if (isloggedin()) {     // 10/14/20
            $swtc_user = swtc_get_user();
            // print_object($swtc_user);
            $swtc_user->set_user_role($event);
        }
    }

    /**
     * User enrollment created
     *
     * @param \core\event\user_enrolment_created $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {

        // print_object("In observer:user_enrolment_created. About to print event");
        // print_object($event);
        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * Role assigned
     *
     * @param \core\event\role_assigned $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function role_assigned(\core\event\role_assigned $event) {

        // print_object("in observer role_assigned. about to print event");
        // print_object($event);
        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * User enrollment deleted
     *
     * @param \core\event\user_enrolment_deleted $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {

        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

    /**
     * User enrollment updated
     *
     * @param \core\event\user_enrolment_updated $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {

        $swtc_user = swtc_get_user();
        $swtc_user->set_user_role($event);
    }

}
