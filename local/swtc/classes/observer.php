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
 * @copyright  2021 SWTC
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
        // SWTC ********************************************************************************.
        // $event->objectid  The userid (i.e. $USER->id).
        // $event->other['username']  The username used to login.
        // An example event looks like the following:
        // core\event\user_loggedin Object
        // (
        // [data:protected] => Array
        // (
        // [eventname] => \core\event\user_loggedin
        // [component] => core
        // [action] => loggedin
        // [target] => user
        // [objecttable] => user
        // [objectid] => 4
        // [crud] => r
        // [edulevel] => 0
        // [contextid] => 1
        // [contextlevel] => 10
        // [contextinstanceid] => 0
        // [userid] => 4
        // [courseid] => 0
        // [relateduserid] =>
        // [anonymous] => 0
        // [other] => Array
        // (
        // [username] => rfrench@lenovo.com
        // )
        // [timecreated] => 1615230740
        // )
        // [logextra:protected] =>
        // [context:protected] => context_system Object
        // (
        // [_id:protected] => 1
        // [_contextlevel:protected] => 10
        // [_instanceid:protected] => 0
        // [_path:protected] => /1
        // [_depth:protected] => 1
        // [_locked:protected] => 0
        // )
        // [triggered:core\event\base:private] => 1
        // [dispatched:core\event\base:private] =>
        // [restored:core\event\base:private] =>
        // [recordsnapshots:core\event\base:private] => Array
        // (
        // )
        // )
        // SWTC ********************************************************************************.
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);

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

        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
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
        // SWTC ********************************************************************************.
        // $event->objectid  The userid (i.e. $USER->id).
        // $event->other['username']  The username used to login.
        // An example event looks like the following:
        // core\event\user_updated Object
        // (
        // [data:protected] => Array
        // (
        // [eventname] => \core\event\user_updated
        // [component] => core
        // [action] => updated
        // [target] => user
        // [objecttable] => user
        // [objectid] => 4
        // [crud] => u
        // [edulevel] => 0
        // [contextid] => 31
        // [contextlevel] => 30
        // [contextinstanceid] => 4
        // [userid] => 4
        // [courseid] => 0
        // [relateduserid] => 4
        // [anonymous] => 0
        // [other] =>
        // [timecreated] => 1615233955
        // )
        // [logextra:protected] =>
        // [context:protected] => context_user Object
        // (
        // [_id:protected] => 31
        // [_contextlevel:protected] => 30
        // [_instanceid:protected] => 4
        // [_path:protected] => /1/31
        // [_depth:protected] => 2
        // [_locked:protected] => 0
        // )
        // [triggered:core\event\base:private] => 1
        // [dispatched:core\event\base:private] =>
        // [restored:core\event\base:private] =>
        // [recordsnapshots:core\event\base:private] => Array
        // (
        // )
        // )
        // Note: This eventdata was obtained by User menu > Preferences > Editor preference
        // and changing editor.
        // SWTC ********************************************************************************.
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid]);
        $swtcuser->set_user_role($event);
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
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
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
        // SWTC ********************************************************************************.
        // $event->userid  The userid (i.e. $USER->id).
        // $event->other['username']  The username used to login.
        // An example event looks like the following:
        // core\event\course_viewed Object
        // (
        // [data:protected] => Array
        // (
        // [eventname] => \core\event\course_viewed
        // [component] => core
        // [action] => viewed
        // [target] => course
        // [objecttable] =>
        // [objectid] =>
        // [crud] => r
        // [edulevel] => 2
        // [contextid] => 123
        // [contextlevel] => 50
        // [contextinstanceid] => 6
        // [userid] => 4
        // [courseid] => 6
        // [relateduserid] =>
        // [anonymous] => 0
        // [other] =>
        // [timecreated] => 1615231191
        // )
        // [logextra:protected] =>
        // [context:protected] => context_course Object
        // (
        // [_id:protected] => 123
        // [_contextlevel:protected] => 50
        // [_instanceid:protected] => 6
        // [_path:protected] => /1/511/513/514/123
        // [_depth:protected] => 5
        // [_locked:protected] => 0
        // )
        // [triggered:core\event\base:private] => 1
        // [dispatched:core\event\base:private] =>
        // [restored:core\event\base:private] =>
        // [recordsnapshots:core\event\base:private] => Array
        // (
        // )
        // )
        // SWTC ********************************************************************************.

        if (isloggedin()) {
            $swtcuser = swtc_get_user([
                'userid' => $event->userid]);
            $swtcuser->set_user_role($event);
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
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
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
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
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
        $swtcuser = swtc_get_user([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
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
        $swtcuser = swtc_get_use([
            'userid' => $event->objectid,
            'username' => $event->other['username']]);
        $swtcuser->set_user_role($event);
    }

}
