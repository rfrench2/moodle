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
 * Lenovo customized code for Moodle core completion. Remember to add the following at the top of any module that requires these functions:
 *      use \local_swtc\traits\lenovo_completionlib;
 * And put the following within the class that is being overridden:
 *      use lenovo_completionlib;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_completionlib
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	10/21/19 - Initial writing; moved majority of customized code from /lib/completionlib.php to functions defined here; added utility functions;
 *                      changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 11/11/19 - In lenovo_get_num_tracked_users, when calling lenovo_get_enrolled_users_by_accesstype, added $swtc_user
 *                      as parameter to function.
 * 12/30/19 - In lenovo_get_num_tracked_users, add $whereparams parameter; if $whereparams was set when these functions
 *                      were called, pass it along and preserve it.
 * 03/02/20 - Removed require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php') from all modules except /local/swtc/lib/swtc_userlib.php.
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');        // 03/02/20
// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

use context_system;
use context_course;
use moodle_url;
use stdClass;
use completion_info;

trait lenovo_completionlib {

    /**
     * Returns the number of users whose progress is tracked in this course.
     *
     * Called from: get_num_tracked_users
     *  Location: /lib/completionlib.php
     *  To call: this_function_name
     *
     * Optionally supply a search's where clause, or a group id.
     *
     * @param string $where Where clause sql (use 'u.whatever' for user table fields)
     * @param array $whereparams Where clause params
     * @param int $groupid Group id
     * @return int Number of tracked users
     *
     * Lenovo history:
     *
     * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
     *                      for Students are applied in the category context.
     * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
     *                      to search for access types.
     * 03/03/19 - Added PS/AD site administrator user access types.
     * 03/06/19 - Added PS/SD GEO administrator user access types.
     * 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
     *                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the other groups
     *                      to use).
     *	10/21/19 - Moved majority of Lenovo customized code to here from /lib/completionlib.php;changed to new Lenovo EBGLMS
     *                      classes and methods to load swtc_user and debug; added utility functions.
     * 11/11/19 - When calling lenovo_get_enrolled_users_by_accesstype, added $swtc_user as parameter to function.
     * 12/30/19 - In lenovo_get_num_tracked_users, add $whereparams parameter; if $whereparams was set when these functions
     *                      were called, pass it along and preserve it.
     *
     */
    public function lenovo_get_num_tracked_users($where = '', $whereparams = array(), $groupid = 0) {
        global $DB, $CFG, $SESSION, $COURSE, $USER;       // Lenovo.

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            // Lenovo ********************************************************************************.
            // Always output standard header information.
            // Lenovo ********************************************************************************.
            $messages[] = "Entering lenovo_completion_functions.php ===lenovo_get_num_tracked_users.enter===";
            $messages[] = "where is :$where.\n";
            $messages[] = "whereparams follows :.\n";
            $messages[] = print_r($whereparams, true);
            $messages[] = "groupid follows :\n";
            $messages[] = print_r($groupid, true);
            // print_object("In /lib/completionlib.php ===get_num_tracked_users.enter - groupid is :$groupid.\n");
            debug_logmessage($messages, 'both');
            unset($messages);
        }

        // Lenovo ********************************************************************************
        // 11/15/18 - Added capability to get_enrolled_sql call if user is PremierSupport or ServiceDelivery managers and admins.
        // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
        //                          the capabilities for Students are applied in the category context.
        // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
        //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
        //							PS / SD student user types.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //                          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/13/19 - For PS/SD users that have customized menus, the groupid value that is passed will be the "virtual" value that
        //                      is saved in swtc_user->groupnames; use the value as the key into the array (to get the list of all the
        //                      other groups to use).
        // Lenovo ********************************************************************************.
        // 11/11/19 - When calling lenovo_get_enrolled_users_by_accesstype, added $swtc_user as parameter to function.
        // 12/30/19 - If $whereparams was set when this function was called, pass it along and preserve it.
        // Lenovo ********************************************************************************.
        if ((has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {
            list($enrolledsql, $enrolledparams) = lenovo_get_enrolled_users_by_accesstype($swtc_user, $whereparams, $groupid);

        } else {
            list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                    context_course::instance($COURSE->id), 'moodle/course:isincompletionreports', $groupid, true);
        }

        $sql  = 'SELECT COUNT(eu.id) FROM (' . $enrolledsql . ') eu JOIN {user} u ON u.id = eu.id';
        if ($where) {
            $sql .= " WHERE $where";
        }

        $params = array_merge($enrolledparams, $whereparams);
        // print_object("in lenovo_get_num_tracked_users about to print sql");        // 12/30/10 - Lenovo debugging...
        // print_object($sql);        // 12/30/10 - Lenovo debugging...
        // print_object("in lenovo_get_num_tracked_users about to print params");        // 12/30/10 - Lenovo debugging...
        // print_object($params);        // 12/30/10 - Lenovo debugging...
        $num = $DB->count_records_sql($sql, $params);
        // print_object($num);        // 12/30/10 - Lenovo debugging...
        // Lenovo ********************************************************************************
        if (isset($debug)) {
            $messages[] = "Leaving lenovo_completion_functions.php ===lenovo_get_num_tracked_users.exit===";
            $messages[] = "enrolledsql is :$enrolledsql. ==get_num_tracked_users.exit===.\n";
            $messages[] = "enrolledparams follows :.\n";
            $messages[] = print_r($enrolledparams, true);
            // print_object("==/lib/completionlib.php:get_num_tracked_users===.\n");
            // print_object($enrolledsql);
            // print_object($enrolledparams);
            // print_object($sql);
            // print_object($params);
            // print_object("count_records_sql is :$num\n");
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }
        // Lenovo ********************************************************************************

        return $DB->count_records_sql($sql, $params);
    }
}
