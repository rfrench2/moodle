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
 * SWTC customized code for Moodle activity completion. Remember to add the
 * following at the top of any module that requires these functions:
 *      require_once($CFG->dirroot.'/local/swtc/lib/swtc_completion_functions.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc_completion_functions.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// SWTC customized code for Moodle core completion.
// SWTC ********************************************************************************.
// use \local_swtc\traits\swtc_completion_info;

// SWTC ********************************************************************************.
// Include SWTC user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

/**
 * Course completion progress report
 *
 * Called from: index.php
 *  Location: /report/completion/
 *  To call: this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * @package    report
 * @subpackage completion
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 */
function report_completion($completion, $where, $whereparams, $group) {
    global $USER;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $swtcuser = swtc_get_user([
       'userid' => $USER->id,
       'username' => $USER->username]);
    $debug = swtc_get_debug();
    print_object("about to print completion");
    print_object($completion);
    print_object("about to print where");
    print_object($where);
    print_object("about to print whereparams");
    print_object($whereparams);
    print_object("about to print group");
    print_object($group);
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering swtc_completion_functions.php. ===report_completion.enter.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Add PremierSupport roles to swtcwhere.
    // Add additional WHERE condition if $USER role is PremierSupport-manager or PremierSupport-admin.
    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "About to print where ==report_completion===.\n";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where ==report_completion===.\n";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // IMPORTANT! The following code assumes the following:
    // For PS/SD manager access types (ex: PS-US1-manager):
    // Should only see enrollments in "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
    //
    // For PS/SD administrator access types (ex: PS-US1-administrator):
    // $groupsmenu[0] will be set to "All PremierSupport US enrollments".
    // SWTC ********************************************************************************.
    if ((has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance()))
        || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {

        list($where, $swtcwhere, $grandtotal) =
            swtc_set_where_conditions_by_accesstype($swtcuser, $completion, $where, $whereparams, $group);

    }

    // SWTC ********************************************************************************.
    // If $where or $whereparams was set when this was called, preserve them.
    // SWTC ********************************************************************************.
    if (!empty($whereparams)) {
        $swtcwhere = array_merge($swtcwhere, $whereparams);
    }

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "Leaving swtc_completion_functions.php. ===report_completion.exit.";
        $messages[] = "About to print swtcwhere.";
        $messages[] = print_r($swtcwhere, true);
        $messages[] = "Finished printing swtcwhere.";
        $messages[] = "About to print where (again).";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).";
        $messages[] = "grandtotal is :$grandtotal.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $swtcwhere, $grandtotal);
}



/**
 * Get enrolled users based on user acces type.
 *
 * Called from: get_num_tracked_users
 *  Location: /local/swtc/classes/traits/swtc_completionlib.php
 *  To call: this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * @package    local
 * @subpackage /swtc/lib/swtc_completion_functions.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/07/21 - Initial writing.
 *
 */
function get_enrolled_users_by_accesstype($swtcuser, $whereparams, $groupid) {
    global $COURSE;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $debug = swtc_get_debug();

    // Other SWTC variables.
    $groups = null;
    $groupnames = $swtcuser->get_groupnames();   // SWTC debugging 04/22/21.
    // $groupnames = array();   // SWTC debugging 04/22/21.

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $psmanagement = ($swtcuser->get_psmanagement() !== null) ? $swtcuser->get_psmanagement() : null;
    $sdmanagement = ($swtcuser->get_sdmanagement() !== null) ? $swtcuser->get_sdmanagement() : null;
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Entering swtc_completion_functions.php ===get_enrolled_users_by_accesstype.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // IMPORTANT! Must have the has_capability checks BEFORE calling this function.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
    // SWTC ********************************************************************************.
    // Loop through $groupnames looking for the "virtual" group (if set).
    // print_object("groupid is :$groupid");
    // print_object("about to print groupnames");
    print_object($groupnames);
    if (!empty($groupnames)) {
        // Remember that an array will be located that looks like the following:
        // Array
        // (
        // [0] => mgrs_menu
        // [1] => 168690638
        // [2] => uuid
        // ).
        $found = swtc_array_find_deep($groupnames, $groupid);
        print_object("did I get here? printing found");
        print_object($found);
        if (!empty($found)) {
            $groups = $groupnames[$found[0]][$found[1]]['groups'];
            $groups = explode(', ', $groups);

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print groups.\n";
                $messages[] = print_r($groups, true);
                // print_object("about to print groups (after swtc_array_find_deep)");
                // print_object($groups);
                $messages[] = "Finished printing groups.\n";
                $debug->logmessage($messages, 'both');
                unset($messages);
            }
        }
        // SWTC ********************************************************************************.
    }

    if (isset($psmanagement)) {
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
        context_course::instance($COURSE->id), 'local/swtc:swtc_access_premiersupport_portfolio', $groupid, true);
    } else if (isset($sdmanagement)) {
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
            context_course::instance($COURSE->id), 'local/swtc:swtc_access_servicedelivery_portfolio', $groupid, true);
    } else {
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                context_course::instance($COURSE->id), 'moodle/course:isincompletionreports', $groupid, true);
    }

    // SWTC ********************************************************************************.
    // If $whereparams was set when this was called, preserve it.
    // SWTC ********************************************************************************.
    $params = array_merge($enrolledparams, $whereparams);

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "Leaving swtc_completion_functions.php ===get_num_tracked_users.exit===";
        $messages[] = "enrolledsql is :$enrolledsql. ==get_num_tracked_users.exit===.";
        $messages[] = "params follows :";
        $messages[] = print_r($params, true);
        $debug->logmessage($messages, 'detailed');
        unset($messages);
    }
    // SWTC ********************************************************************************.

    return array($enrolledsql, $params);
}

/**
 * Function to recursively search for a given value.
 *
 *      For example, if this is the multi-dimensional array:
 *      Array
 *      (
 *          [studs_menu] => Array
 *              (
 *                  [1478973742] => Array
 *                      (
 *                          [uuid] => 1478973742
 *                          [groups] => 18421, 18422, 18423, 18424, 18425
 *                      )
 *
 *              )
 *
 *          [mgrs_menu] => Array
 *              (
 *                  [168690638] => Array
 *                      (
 *                          [uuid] => 168690638
 *                          [groups] => 18426, 18427, 18428, 18429, 18430
 *                      )
 *
 *              )
 *
 *          [admins_menu] => Array
 *              (
 *                  [630459861] => Array
 *                      (
 *                          [uuid] => 630459861
 *                          [groups] => 18431, 18432, 18433, 18434, 18435
 *                      )
 *
 *              )
 *
 *      )
 *
 *      If you are searching for "168690638", the following will be returned:
 *      Array
 *      (
 *          [0] => mgrs_menu
 *          [1] => 168690638
 *          [2] => uuid
 *      )
 *
 * History:
 *
 * 04/22/21 - Initial writing.
 *
 **/
function swtc_array_find_deep($array, $search, $keys = array()) {
    print_object("array follows :");
    print_object($array);
    print_object("search is :$search");
    print_object("keys follow :");
    print_object($keys);
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $sub = swtc_array_find_deep($value, $search, array_merge($keys, array($key)));
            if (count($sub)) {
                return $sub;
            }
        } else if ($value === $search) {
            return array_merge($keys, array($key));
        }
    }

    return array();
}
