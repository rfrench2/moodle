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
use \local_swtc\traits\swtc_completionlib;

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
function swtc_report_completion($completion, $where, $whereparams, $group) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // SWTC.

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
        $messages[] = "SWTC ********************************************************************************.";
        $messages[] = "Entering swtc_completion_functions.php. ===swtc_report_completion.enter.";
        $messages[] = "SWTC ********************************************************************************.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Add PremierSupport roles to whereparams.
    // Add additional WHERE condition if $USER role is PremierSupport-manager or PremierSupport-admin.
    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "About to print where ==swtc_report_completion===.\n";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where ==swtc_report_completion===.\n";
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
    //
    // SWTC ********************************************************************************.
    // Added $swtcuser to parameters to function.
    // If $where or $whereparams was set when this function was called, preserve them.
    // SWTC ********************************************************************************.
    if ((has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance()))
        || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {

        list($where, $whereparams, $grandtotal) =
            swtc_set_where_conditions_by_accesstype($swtcuser, $completion, $where, $whereparams, $group);

    }

    // SWTC ********************************************************************************.
    // If $where or $whereparams was set when this function was called, preserve them.
    // SWTC ********************************************************************************.
    if (!empty($whereparams)) {
        $whereparams = array_merge($whereparams, $whereparams);
    }

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "Leaving swtc_completion_functions.php. ===swtc_report_completion.exit.";
        $messages[] = "About to print whereparams.\n";
        $messages[] = print_r($whereparams, true);
        $messages[] = "Finished printing whereparams.\n";
        $messages[] = "About to print where (again).\n";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).\n";
        $messages[] = "grandtotal is :$grandtotal.\n";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $whereparams, $grandtotal);
}

 /**
  * Set additional SQL WHERE conditions based on user acces type.
  *
  * Called from: N/A
  *  Location: /report/completion/index.php
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
  * 10/14/20 - Initial writing.
  *
  */
function swtc_set_where_conditions_by_accesstype($swtcuser, $completion, $wherepassed, $whereparams, $group) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $debug = swtc_get_debug();

    // Other SWTC variables.
    $whereparams = array();

    $useraccesstype = $swtcuser->user_access_type;
    $usergroupname = $swtcuser->groupname;
    $usergeoname = $SESSION->SWTC->USER->geoname;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $accesspsmgr = get_string('access_premiersupport_pregmatch_mgr', 'local_swtc');
    $accesspsadmin = get_string('access_premiersupport_pregmatch_admin', 'local_swtc');
    $accesspsgeoadmin = get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc');
    $accesspssiteadmin = get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc');

    $accesslenovosdmgr = get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc');
    $accesslenovosdadmin = get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc');
    $accesslenovosdgeoadmin = get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc');
    $accesslenovosdsiteadmin = get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc');
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Entering swtc_completion_functions.php ===swtc_set_where_conditions_by_accesstype.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // IMPORTANT! Must have the has_capability checks BEFORE calling this function.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
    // SWTC ********************************************************************************.
    if ((preg_match($accesspsmgr, $useraccesstype)) || (preg_match($accesspsadmin, $useraccesstype))
        || (preg_match($accesspsgeoadmin, $useraccesstype)) || (preg_match($accesspssiteadmin, $useraccesstype))
        || (preg_match($accesslenovosdmgr, $useraccesstype)) || (preg_match($accesslenovosdadmin, $useraccesstype))
        || (preg_match($accesslenovosdgeoadmin, $useraccesstype)) || (preg_match($accesslenovosdsiteadmin, $useraccesstype))) {
        // One common where clause.
        // If debug is enabled, list all users including any testing users (%test%) in case cohort has not
        // been populated with actual users.
        if (isset($debug)) {
            $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1))
                AND (u.suspended != 1) AND (u.deleted != 1)";
        } else {
            $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1))
                AND (u.firstname NOT LIKE '%test%') AND (u.suspended != 1) AND (u.deleted != 1)";
        }
        // SWTC ********************************************************************************.
        // If $wherepassed or $whereparams was set when this function was called, preserve them.
        // SWTC ********************************************************************************.
        if (!empty($wherepassed)) {
            $where = array_merge($where, $wherepassed);
        }

        // SWTC ********************************************************************************.
        // PremierSupport site administrators
        // SWTC ********************************************************************************.
        if (preg_match($accesspssiteadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%PremierSupport-%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // PremierSupport GEO administrators
            // SWTC ********************************************************************************.
        } else if (preg_match($accesspsgeoadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%PremierSupport-" .$usergeoname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // PremierSupport administrators
            // SWTC ********************************************************************************.
        } else if (preg_match($accesspsadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%PremierSupport-" .$usergroupname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // PremierSupport managers
            // SWTC ********************************************************************************.
        } else if (preg_match($accesspsmgr, $useraccesstype)) {
            $whereparams['accesstype1'] = "%PremierSupport-" .$usergroupname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // ServiceDelivery site administrators
            // SWTC ********************************************************************************.
        } else if (preg_match($accesslenovosdsiteadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%ServiceDelivery-%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // ServiceDelivery GEO administrators
            // SWTC ********************************************************************************.
        } else if (preg_match($accesslenovosdgeoadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%ServiceDelivery-" .$usergeoname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // ServiceDelivery administrators
            // SWTC ********************************************************************************.
        } else if (preg_match($accesslenovosdadmin, $useraccesstype)) {
            $whereparams['accesstype1'] = "%ServiceDelivery-" .$usergroupname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
            // SWTC ********************************************************************************.
            // ServiceDelivery managers
            // SWTC ********************************************************************************.
        } else if (preg_match($accesslenovosdmgr, $useraccesstype)) {
            $whereparams['accesstype1'] = "%ServiceDelivery-" .$usergroupname. "%";
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this function was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($whereparams)) {
                $whereparams = array_merge($whereparams, $whereparams);
            }
            $grandtotal = $completion->swtc_get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);
        }
        // SWTC ********************************************************************************.
        // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
        // SWTC ********************************************************************************.
    } else {
        $where = array();
        // SWTC ********************************************************************************.
        // If $wherepassed or $whereparams was set when this function was called, preserve them.
        // SWTC ********************************************************************************.
        if (!empty($wherepassed)) {
            $where = array_merge($where, $wherepassed);
        }

        if (!empty($whereparams)) {
            $whereparams = array_merge($whereparams, $whereparams);
        }

        $grandtotal = $completion->swtc_get_num_tracked_users('', $whereparams, $group);           // 12/30/19

    }

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Leaving swtc_completion_functions.php ===swtc_set_where_conditions_by_accesstype.exit===";
        $messages[] = "About to print whereparams.";
        $messages[] = print_r($whereparams, true);
        $messages[] = "Finished printing whereparams.";
        $messages[] = "About to print where (again).";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again).";
        $messages[] = "grandtotal is :$grandtotal.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return array($where, $whereparams, $grandtotal);
}

 /**
  * Get enrolled users based on user acces type.
  *
  * Called from: swtc_get_num_tracked_users
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
function swtc_get_enrolled_users_by_accesstype($swtcuser, $whereparams, $groupid) {
    global $DB, $CFG, $SESSION, $COURSE, $USER;       // SWTC.

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $useraccesstype = $swtcuser->user_access_type;
    $groups = null;
    $usergroupnames = $SESSION->SWTC->USER->groupnames;

    // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
    $psmanagement = isset($swtcuser->psmanagement) ? $swtcuser->psmanagement : null;
    $sdmanagement = isset($swtcuser->sdmanagement) ? $swtcuser->sdmanagement : null;
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = "Entering swtc_completion_functions.php ===swtc_set_where_conditions_on_accesstype.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // 10/22/19 - IMPORTANT! Must have the has_capability checks BEFORE calling this function.
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
    // SWTC ********************************************************************************.
    // Loop through $usergroupnames looking for the "virtual" group (if set).
    if (!empty($usergroupnames)) {
        // Remember that an array will be located that looks like the following:
        // Array
        // (
        // [0] => mgrs_menu
        // [1] => 168690638
        // [2] => uuid
        // ).
        $found = swtc_array_find_deep($usergroupnames, $groupid);
        if (!empty($found)) {
            $groups = $usergroupnames[$found[0]][$found[1]]['groups'];
            $groups = explode(', ', $groups);

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print groups.\n";
                $messages[] = print_r($groups, true);
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
        context_course::instance($COURSE->id), 'local/swtc:ebg_access_premiersupport_portfolio', $groupid, true);
    } else if (isset($sdmanagement)) {
        $groupid = isset($groups) ? $groups : $groupid;
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
            context_course::instance($COURSE->id), 'local/swtc:ebg_access_servicedelivery_portfolio', $groupid, true);
    } else {
        list($enrolledsql, $enrolledparams) = get_enrolled_sql(
                context_course::instance($COURSE->id), 'moodle/course:isincompletionreports', $groupid, true);
    }

    // SWTC ********************************************************************************.
    // If $whereparams was set when this function was called, preserve it.
    // SWTC ********************************************************************************.
    $params = array_merge($enrolledparams, $whereparams);

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "Leaving swtc_completion_functions.php ===swtc_get_num_tracked_users.exit===";
        $messages[] = "enrolledsql is :$enrolledsql. ==get_num_tracked_users.exit===.\n";
        $messages[] = "params follows :.\n";
        $messages[] = print_r($params, true);
        $debug->logmessage($messages, 'detailed');
        unset($messages);
    }
    // SWTC ********************************************************************************.

    return array($enrolledsql, $params);
}
