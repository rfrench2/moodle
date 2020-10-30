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

// use \local_swtc\swtc_user as SwtcUser;       // 10/20/20 - SWTC
// use \local_swtc\swtc_debug;      // 10/20/20 - SWTC

// require($CFG->dirroot.'/local/swtc/lib/swtc.php');   // 10/18/20
// require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');    // 10/14/20
// require_once($CFG->dirroot . '/local/swtc/classes/swtc_user.php');    // 10/20/20
require_once($CFG->dirroot . '/local/swtc/lib/swtc_userlib.php');


class observer {

    /**
     * User loggedin
     *
     * @param \core\event\user_loggedin $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function user_loggedin(\core\event\user_loggedin $event) {
        global $USER;

        $swtc_user = swtc_get_user($USER);       // 10/24/20
        $swtc_user->assign_user_role($event);     // 10/17/20
        // print_object("in observer user_loggedin; about to print __namespace__");        // SWTC-debug
        // print_object(__NAMESPACE__);        // SWTC-debug
        // print_object("in observer user_loggedin; about to print backtrace");        // SWTC-debug
        // print_object(format_backtrace(debug_backtrace(), true));        // SWTC-debug
        // print_object("in observer user_loggedin; about to print CFG");     // SWTC-debug
        // print_object($CFG);		// 10/16/20 - SWTC

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\local_swtc\swtc_user\swtc_user()
        // $swtc_user = local_swtc\swtc_user\swtc_user($USER);

        // The following yeilds:
        // Exception - Exception - Class 'local_swtc\swtc_user\swtc_user' not found
        // $swtc_user = new \local_swtc\swtc_user\swtc_user($USER);

        // The following yeilds:
        // WORKS! WORKS! WORKS! WORKS! WORKS! WORKS!
        // $swtc_user = new swtc_user($USER);

        // The following yeilds:
        //  Exception - Call to undefined function local_swtc\local_swtc\swtc_user\get_user()
        // $swtc_user = \local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - Class 'local_swtc\swtc_user\get_user' not found
        // $swtc_user = new \local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - Class 'local_swtc\swtc_user\swtc_user\get_user' not found
        // $swtc_user = new \local_swtc\swtc_user\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\local_swtc\swtc_user\get_user()
        // $swtc_user = local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Can not execute event observer '\local_swtc\observer::user_loggedin'
        // $swtc_user = new \local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\get_user()
        // $swtc_user = get_user($USER);			// 10/18/20 - SWTC

        // The following yeilds:
        // Exception - Class 'local_swtc\get_user' not found
        // $swtc_user = new get_user($USER);			// 10/18/20 - SWTC

        // The following yeilds:
        // Exception - Class 'local_swtc\local_swtc\swtc_user' not found
        // $swtc_user = local_swtc\swtc_user::get_user($USER);

        // After changing get_user to static...

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\get_user()
        // $swtc_user = get_user($USER);

        // The following yeilds:
        // Exception - Class 'local_swtc\local_swtc\swtc_user' not found
        // $swtc_user = local_swtc\swtc_user::get_user($USER);

        // The following yeilds:
        // Seems to work!
        // $swtc_user = SwtcUser::get_user($USER);

        // The following yeilds:
        // Exception - syntax error, unexpected '-&gt;' (T_OBJECT_OPERATOR)
        // $swtc_user = new \local_swtc\swtc_user->get_user($USER);

        // After adding require_once($CFG->dirroot . '/local/swtc/lib/swtc_user.php');

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\get_user()
        // $swtc_user = get_user($USER);

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\get_user()
        // $swtc_user = \local_swtc\get_user($USER);

        // The following yeilds:
        // Exception - Call to undefined function local_swtc\swtc_user\get_user()
        // $swtc_user = \local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - Class 'local_swtc\swtc_user\get_user' not found
        // $swtc_user = new \local_swtc\swtc_user\get_user($USER);

        // The following yeilds:
        // Exception - syntax error, unexpected '-&gt;' (T_OBJECT_OPERATOR)
        // $swtc_user = new \local_swtc\swtc_user->get_user($USER);

        // After including swtc_userlib.
        // The following yeilds:
        // WORKS! WORKS! WORKS! WORKS! WORKS! WORKS! WORKS!
        // $swtc_user = swtc_get_user($USER);

        // print_object("in observer user_loggedin; about to print swtc_user");        // SWTC-debug
    	// print_object($swtc_user);		// 10/16/20 - SWTC
        //  WORKS! WORKS! WORKS! WORKS! WORKS! WORKS!
        // $swtc_debug = swtc_set_debug();
        // print_object("in observer user_loggedin; about to print swtc_debug");        // SWTC-debug
    	// print_object($swtc_debug);
    	// die;		// 10/16/20 - SWTC

        // print_object("in observer user_loggedin; about to print SESSION");        // SWTC-debug
        // print_object($SESSION);      // SWTC-debug
        // die;     // SWTC-debug
        // require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');        // 10/17/20
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        // print_object($event);
        // Lenovo ********************************************************************************
        //  07/23/19 - On the site home page, dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************
        // if ($event->courseid == SITEID) {
        //     if (!featuredcourses_block_course_slider_exists($event->courseid)) {
        //         // featuredcourses_block_course_slider_dynamically_add($event->courseid);
        //     }
        // }
        // return;     // 10/17/20 - SWTC
        if (isloggedin()) {     // 10/14/20
            $swtc_user = swtc_get_user($USER);
            $swtc_user->assign_user_role($event);
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
        global $USER;

        // print_object("In observer:user_enrolment_created. About to print event");
        // print_object($event);
        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
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
        global $USER;

        $swtc_user = swtc_get_user($USER);
        $swtc_user->assign_user_role($event);
    }

    /**
     * Course updated
     *
     * @param \core\event\course_updated $event the event
     * @return void
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $USER;

        // print_object($event);
        // Lenovo ********************************************************************************
        //  07/22/19 - Dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************
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
     * 10/14/20 - Initial writing.
     *
     */
    public static function course_created(\core\event\course_created $event) {
        global $USER;

        // print_object($event);
        // Lenovo ********************************************************************************
        //  07/22/19 - Dynamically add course_slider block (if not already there).
        // Lenovo ********************************************************************************
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
     * 10/14/20 - Initial writing.
     *
     */
    public static function observe_all($event) {
        global $USER;
        // global $SESSION;

        // Only print for debugging.
        // print_object($event);
        // print_object($SESSION->SWTC);
        // swtc_course_updated($event);
    }

}
