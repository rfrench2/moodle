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
 * Contains classes, functions and constants used during the tracking
 * of activity completion for users.
 *
 * Completion top-level options (admin setting enablecompletion)
 *
 * @package core_completion
 * @category completion
 * @copyright 1999 onwards Martin Dougiamas   {@link http://moodle.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 04/18/21 - Initial writing.
 *
 */

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_completion_functions.php');

// use \local_swtc\traits\swtc_completionlib;
use context_system;


/**
 * Class represents completion information for a course.
 *
 * Does not contain any data, so you can safely construct it multiple times
 * without causing any problems.
 *
 * @package core
 * @category completion
 * @copyright 2008 Sam Marshall
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class swtc_completion_info extends \completion_info {

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
     * 04/18/21 - Initial writing.
     *
     */
    public function set_where_conditions_by_accesstype($swtcuser, $completion, $wherepassed, $whereparams, $group) {

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $swtcwhere = array();

        $useraccesstype = $swtcuser->get_accesstype();
        $usergroupname = $swtcuser->get_groupname();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Entering swtc_completion_functions.php ===swtc_set_where_conditions_by_accesstype.enter===";
            $messages[] = "About to print completion.";
            $messages[] = print_r($completion, true);
            // print_object($completion);
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // IMPORTANT! Must have the has_capability checks BEFORE calling this function.
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $useraccesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $useraccesstype))) {
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
            // If $wherepassed or $whereparams was set when this was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($wherepassed)) {
                $where = array_merge($where, $wherepassed);
            }

            // SWTC ********************************************************************************.
            // PremierSupport site administrators
            // SWTC ********************************************************************************.
            if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%PremierSupport-%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // PremierSupport GEO administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%PremierSupport-" .$usergeoname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // PremierSupport administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%PremierSupport-" .$usergroupname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // PremierSupport managers
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%PremierSupport-" .$usergroupname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // ServiceDelivery site administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%ServiceDelivery-%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // ServiceDelivery GEO administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%ServiceDelivery-" .$usergeoname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // ServiceDelivery administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%ServiceDelivery-" .$usergroupname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
                // SWTC ********************************************************************************.
                // ServiceDelivery managers
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $useraccesstype)) {
                $swtcwhere['accesstype1'] = "%ServiceDelivery-" .$usergroupname. "%";
                // SWTC ********************************************************************************.
                // If $wherepassed or $whereparams was set when this was called, preserve them.
                // SWTC ********************************************************************************.
                if (!empty($whereparams)) {
                    $swtcwhere = array_merge($swtcwhere, $whereparams);
                }
                $grandtotal = $completion->get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
            }
            // SWTC ********************************************************************************.
            // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
            // SWTC ********************************************************************************.
        } else {
            $where = array();
            // SWTC ********************************************************************************.
            // If $wherepassed or $whereparams was set when this was called, preserve them.
            // SWTC ********************************************************************************.
            if (!empty($wherepassed)) {
                $where = array_merge($where, $wherepassed);
            }

            if (!empty($whereparams)) {
                $swtcwhere = array_merge($swtcwhere, $whereparams);
            }

            $grandtotal = $completion->get_num_tracked_users('', $swtcwhere, $group);

        }

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Leaving swtc_completion_functions.php ===swtc_set_where_conditions_by_accesstype.exit===";
            $messages[] = "About to print swtcwhere.";
            $messages[] = print_r($swtcwhere, true);
            $messages[] = "Finished printing whereparams.";
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
            $messages[] = "where is :$where.";
            $messages[] = "whereparams follows :.";
            $messages[] = print_r($whereparams, true);
            $messages[] = "groupid follows :";
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
            $messages[] = "enrolledsql is :$enrolledsql.";
            $messages[] = "enrolledparams follows :";
            $messages[] = print_r($enrolledparams, true);
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return string
     *
     * History:
     *
     * 04/21/21 - Initial writing.
     *
     */
    function get_title_detailed_course($group) {
        global $DB;

        $prereq = $DB->get_record('course', array('id' => $this->courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));
        $shortname = format_string($prereq->shortname, true, array('context' => $coursecontext));

        return array($this->courseinstance, shorten_text(urldecode($shortname)), urldecode($fullname));
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return  string
     *
     * History:
     *
     * 04/21/21 - Initial writing.
     *
     */
    function get_title_detailed_activity() {
        global $DB;

        $module = $DB->get_record('course_modules', array('id' => $this->moduleinstance));
        $activity = $DB->get_record($this->module, array('id' => $module->instance));

        return shorten_text(format_string($activity->name, true,
                array('context' => context_module::instance($module->id))));
    }
}
