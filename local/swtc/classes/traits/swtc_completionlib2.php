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
 * SWTC customized code for Moodle core completion. Remember to add the
 * following at the top of any module that requires these functions:
 *      use \local_swtc\traits\swtc_completionlib;
 * And put the following within the class that is being overridden:
 *      use swtc_completionlib;
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc_completionlib
 * @copyright  2019 SWTC DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/18/21 - Initial writing.
 *
 **/

namespace local_swtc\traits;

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

// SWTC ********************************************************************************.
// Include SWTC functions.
// SWTC ********************************************************************************.
// require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

use context_system;
use context_course;
use moodle_url;
use stdClass;
use completion_info;

trait swtc_completionlib {

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
     * History:
     *
     * 04/18/21 - Initial writing.
     *
     */
    public function get_num_tracked_users($where = '', $whereparams = array(), $groupid = 0) {
        global $DB, $COURSE, $USER;

        print_object("get_num_tracked_users in /local/swtc/classes/traits/swtc_completionlib.php");

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Entering swtc_completion_functions.php ===get_num_tracked_users.enter===";
            $messages[] = "where is :$where.\n";
            $messages[] = "whereparams follows :.\n";
            $messages[] = print_r($whereparams, true);
            $messages[] = "groupid follows :\n";
            $messages[] = print_r($groupid, true);
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Added capability to get_enrolled_sql call if user is PremierSupport or ServiceDelivery managers and admins.
        // SWTC ********************************************************************************.
        if ((has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance()))
            || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {

            list($enrolledsql, $enrolledparams) = get_enrolled_users_by_accesstype($swtcuser, $whereparams, $groupid);

        } else {
            list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                    context_course::instance($COURSE->id), 'moodle/course:isincompletionreports', $groupid, true);
        }

        $sql  = 'SELECT COUNT(eu.id) FROM (' . $enrolledsql . ') eu JOIN {user} u ON u.id = eu.id';
        if ($where) {
            $sql .= " WHERE $where";
        }

        $params = array_merge($enrolledparams, $whereparams);

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Leaving swtc_completion_functions.php ===get_num_tracked_users.exit===";
            $messages[] = "enrolledsql is :$enrolledsql. ==get_num_tracked_users.exit===.\n";
            $messages[] = "enrolledparams follows :.\n";
            $messages[] = print_r($enrolledparams, true);
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        return $DB->count_records_sql($sql, $params);
    }
}
