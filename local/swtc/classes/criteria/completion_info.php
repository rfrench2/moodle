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
namespace local_swtc\criteria;

use context;
use context_system;
use context_course;
use context_module;

use \local_swtc\curriculums\curriculums;
use \local_swtc\criteria\completion_criteria;
use \local_swtc\criteria\completion_criteria_completion;

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');

/**
 * Class and overriding methods copied from Moodle 3.10 code base.
 *
 * Class overrides the following:
 * completion_info in /lib/completionlib.php
 *
 * Class represents completion information for a course.
 *
 * Does not contain any data, so you can safely construct it multiple times
 * without causing any problems.
 *
 * @package    local
 * @subpackage /swtc/classes/criteria/completion_info.php
 * @copyright  2021 SWTC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_info extends \completion_info {

    /**
     * Course object passed during construction.
     * @var stdClass
     */
    private $course;

    /**
     * Completion criteria {@link completion_info::get_criteria()}.
     * @var array
     */
    private $criteria;

    /**
     * Constructs with course details.
     *
     * When instantiating a new completion info object you must provide a course
     * object with at least id, and enablecompletion properties. Property
     * cacherev is needed if you check completion of the current user since
     * it is used for cache validation.
     *
     * @param stdClass $course Moodle course object.
     */
    public function __construct($course) {
        $this->course = $course;
        $this->course_id = $course->id;
    }

    /**
     * Set additional SQL WHERE conditions based on user acces type.
     *
     * Called from: /local/swtc/lib/curriculumslib.php,
     * /report/completion/index.php
     *
     *  To call:
     *  $completion = new completion_info;
     *  $completion->this_method_name;
     *
     * @param stdClass $swtcuser The SWTC user object.
     * @param array $wherepassed The existing WHERE clause.
     * @param array $whereparams The existing WHERE params.
     * @param int $group The group to use.
     *
     * History:
     *
     * 04/18/21 - Initial writing.
     *
     */
    public function set_where_conditions_by_accesstype($swtcuser, $wherepassed, $whereparams, $group) {

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $swtcwhere = array();

        $useraccesstype = $swtcuser->get_accesstype();
        $usergroupname = $swtcuser->get_groupname();
        $usergeoname = $swtcuser->get_geoname();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Entering local_swtc_criteria_completion_info.php ===set_where_conditions_by_accesstype.enter===";
            $messages[] = "About to print completion (this).";
            $messages[] = print_r($this, true);
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // IMPORTANT! Must have the has_capability checks BEFORE calling this function.
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
        // SWTC ********************************************************************************.
        if (($swtcuser->is_psmanagement()) || ($swtcuser->is_sdmanagement())) {
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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
                $grandtotal = self::get_num_tracked_users(implode(' AND ', $where), $swtcwhere, $group);
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

            $grandtotal = self::get_num_tracked_users('', $swtcwhere, $group);

        }

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Leaving local_swtc_criteria_completion_info.php ===set_where_conditions_by_accesstype.exit===";
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

        // Other SWTC variables.
        $curriculums = new curriculums;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Entering local_swtc_criteria_completion_info.php ===get_num_tracked_users.enter===";
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
        || (!empty($curriculums->getall_enrollments_for_user($USER->id)))) {
            list($enrolledsql, $enrolledparams) = self::get_enrolled_users_by_accesstype($swtcuser, $whereparams, $groupid);

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
            $messages[] = "Leaving local_swtc_criteria_completion_info.php ===get_num_tracked_users.exit===";
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
    public function get_title_detailed_course($courseinstance) {
        global $DB;

        $prereq = $DB->get_record('course', array('id' => $courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));
        $shortname = format_string($prereq->shortname, true, array('context' => $coursecontext));

        return array($courseinstance, shorten_text(urldecode($shortname)), urldecode($fullname));
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return  string
     *
     * SWTC history:
     *
     * 04/21/21 - Initial writing.
     *
     */
    public function get_title_detailed_activity($moduleinstance, $modtype) {
        global $DB;

        $module = $DB->get_record('course_modules', array('id' => $moduleinstance));
        $activity = $DB->get_record($modtype, array('id' => $module->instance));

        return shorten_text(format_string($activity->name, true,
                array('context' => context_module::instance($module->id))));
    }

    /**
     * Obtains progress information across a course for all users on that course, or
     * for all users in a specific group. Intended for use when displaying progress.
     *
     * This includes only users who, in course context, have one of the roles for
     * which progress is tracked (the gradebookroles admin option) and are enrolled in course.
     *
     * Users are included (in the first array) even if they do not have
     * completion progress for any course-module.
     *
     * @param bool $sortfirstname If true, sort by first name, otherwise sort by
     *   last name
     * @param string $where Where clause sql (optional)
     * @param array $whereparams Where clause params (optional)
     * @param int $groupid Group ID or 0 (default)/false for all groups
     * @param int $pagesize Number of users to actually return (optional)
     * @param int $start User to start at if paging (optional)
     * @param context $extracontext If set, includes extra user information fields
     *   as appropriate to display for current user in this context
     * @return stdClass with ->total and ->start (same as $start) and ->users;
     *   an array of user objects (like mdl_user id, firstname, lastname)
     *   containing an additional ->progress array of coursemoduleid => completionstate
     */
    public function get_progress_all($where = '', $whereparams = array(), $groupid = 0,
            $sort = '', $pagesize = '', $start = '', context $extracontext = null) {
        global $DB, $COURSE;

        // Get list of applicable users.
        $users = $this->get_tracked_users($where, $whereparams, $groupid, $sort,
                $start, $pagesize, $extracontext);

        // Get progress information for these users in groups of 1, 000 (if needed)
        // to avoid making the SQL IN too long.
        $results = array();
        $userids = array();
        foreach ($users as $user) {
            $userids[] = $user->id;
            $results[$user->id] = $user;
            $results[$user->id]->progress = array();
        }

        for ($i = 0; $i < count($userids); $i += 1000) {
            $blocksize = count($userids) - $i < 1000 ? count($userids) - $i : 1000;

            list($insql, $params) = $DB->get_in_or_equal(array_slice($userids, $i, $blocksize));
            array_splice($params, 0, 0, array($COURSE->id));
            $rs = $DB->get_recordset_sql("
                SELECT
                    cmc.*
                FROM
                    {course_modules} cm
                    INNER JOIN {course_modules_completion} cmc ON cm.id=cmc.coursemoduleid
                WHERE
                    cm.course=? AND cmc.userid $insql", $params);
            foreach ($rs as $progress) {
                $progress = (object)$progress;
                $results[$progress->userid]->progress[$progress->coursemoduleid] = $progress;
            }
            $rs->close();
        }

        return $results;
    }

    /**
     * Return array of users whose progress is tracked in this course.
     *
     * Optionally supply a search's where clause, group id, sorting, paging.
     *
     * @param string $where Where clause sql, referring to 'u.' fields (optional)
     * @param array $whereparams Where clause params (optional)
     * @param int $groupid Group ID to restrict to (optional)
     * @param string $sort Order by clause (optional)
     * @param int $limitfrom Result start (optional)
     * @param int $limitnum Result max size (optional)
     * @param context $extracontext If set, includes extra user information fields
     *   as appropriate to display for current user in this context
     * @return array Array of user objects with standard user fields
     *
     * SWTC history:
     *
     * 05/11/21 - Initial writing.
     *
     */
    public function get_tracked_users($where = '', $whereparams = array(), $groupid = 0,
             $sort = '', $limitfrom = '', $limitnum = '', context $extracontext = null) {

        global $DB, $USER, $COURSE;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $curriculums = new curriculums;
        $usergroupnames = $swtcuser->get_groupnames();
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Entering local_swtc_criteria_completion_info.php ===get_tracked_users.enter===";
            $messages[] = "where follows :";
            $messages[] = print_r($where, true);
            $messages[] = "whereparams follows :";
            $messages[] = print_r($whereparams, true);
            $messages[] = "groupid is :$groupid";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        // Remember that the capabilities for Managers and Administrators are applied in the system
        // context; the capabilities for Students are applied in the category context.
        // SWTC ********************************************************************************.
        if ((has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance()))
        || (!empty($curriculums->getall_enrollments_for_user($USER->id)))) {
            // Loop through $user_groupnames looking for the "virtual" group (if set).
            if (!empty($usergroupnames)) {
                // Remember that an array will be located that looks like the following:
                // Array
                // (
                // [0] => mgrs_menu
                // [1] => 168690638
                // [2] => uuid
                // )
                $found = local_swtc_array_find_deep($usergroupnames, $groupid);

                // SWTC ********************************************************************************.
                if (isset($debug)) {
                    $messages[] = "About to print found.\n";
                    $messages[] = print_r($found, true);
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
                }
                // SWTC ********************************************************************************.

                if (!empty($found)) {
                    $groups = $found['groups'];
                    $groups = explode(', ', $groups);

                    // SWTC ********************************************************************************.
                    if (isset($debug)) {
                        $messages[] = "About to print groups ==local_swtc_criteria_completion_info.php ===get_tracked_users===.\n";
                        $messages[] = print_r($groups, true);
                        $messages[] = "Finished printing groups ==local_swtc_criteria_completion_info.php
                            ===get_tracked_users===.\n";
                        $debug->logmessage($messages, 'both');
                        unset($messages);
                    }
                }
                // SWTC ********************************************************************************.
            }
            if ($swtcuser->is_psmanagement()) {
                $groupid = isset($groups) ? $groups : $groupid;
                list($enrolledsql, $params) = get_enrolled_sql(
                context_course::instance($COURSE->id), 'local/swtc:swtc_view_mgmt_reports', $groupid, true);
            } else if ($swtcuser->is_sdmanagement()) {
                $groupid = isset($groups) ? $groups : $groupid;
                list($enrolledsql, $params) = get_enrolled_sql(
                    context_course::instance($COURSE->id), 'local/swtc:swtc_view_mgmt_reports', $groupid, true);
            } else {
                list($enrolledsql, $params) = get_enrolled_sql(
                    context_course::instance($COURSE->id),
                    'moodle/course:isincompletionreports', $groupid, true);
            }
        } else {
            list($enrolledsql, $params) = get_enrolled_sql(
                    context_course::instance($COURSE->id),
                    'moodle/course:isincompletionreports', $groupid, true);
        }

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "enrolledsql is :$enrolledsql. ==get_tracked_users===.\n";
            $messages[] = "params follows :.\n";
            $messages[] = print_r($params, true);
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        list($enrolledsql, $params) = get_enrolled_sql(
                context_course::instance($COURSE->id),
                'moodle/course:isincompletionreports', $groupid, true);

        $allusernames = get_all_user_name_fields(true, 'u');
        $sql = 'SELECT u.id, u.idnumber, ' . $allusernames;
        if ($extracontext) {
            $sql .= get_extra_user_fields_sql($extracontext, 'u', '', array('idnumber'));
        }
        $sql .= ' FROM (' . $enrolledsql . ') eu JOIN {user} u ON u.id = eu.id';

        if ($where) {
            $sql .= " AND $where";
            $params = array_merge($params, $whereparams);
        }

        if ($sort) {
            $sql .= " ORDER BY $sort";
        }

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Leaving local_swtc_criteria_completion_info.php ===get_tracked_users.exit===";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    /**
     * Get enrolled users based on user acces type.
     *
     * Called from: get_num_tracked_users
     *  Location: /local/swtc/classes/criteria/completion_info.php
     *  To call: this_function_name
     *
     * @param array $courses      Sorted list of courses.
     * @param int $count      Number of sorted courses.
     *
     * @return array $courses     Sorted list of courses with the courses removed.
     *
     * @package    local
     * @subpackage /swtc/classes/criteria/completion_info.php
     * @copyright  2021 SWTC
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     *
     * History:
     *
     * 04/07/21 - Initial writing.
     *
     */
    public function get_enrolled_users_by_accesstype($swtcuser, $whereparams, $groupid) {
        global $COURSE;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $groups = null;
        $groupnames = $swtcuser->get_groupnames();   // SWTC debugging 04/22/21.
        // $groupnames = array();   // SWTC debugging 04/22/21.

        // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
        $psmanagement = ($swtcuser->is_psmanagement() !== null) ? $swtcuser->is_psmanagement() : null;
        $sdmanagement = ($swtcuser->is_sdmanagement() !== null) ? $swtcuser->is_sdmanagement() : null;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "Entering completion_info.php ===get_enrolled_users_by_accesstype.enter===";
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
        if (!empty($groupnames)) {
            // Remember that an array will be located that looks like the following:
            // Array
            // (
            // [0] => mgrs_menu
            // [1] => 168690638
            // [2] => uuid
            // ).
            $found = local_swtc_array_find_deep($groupnames, $groupid);

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print found.\n";
                $messages[] = print_r($found, true);
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.

            if (!empty($found)) {
                $groups = $found['groups'];
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
            context_course::instance($COURSE->id), 'local/swtc:swtc_view_student_reports', $groupid, true);
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
     * Check if course has completion criteria set
     *
     * @return bool Returns true if there are criteria
     */
    public function has_criteria() {
        $criteriax = $this->get_criteria();

        return (bool) count($criteriax);
    }

    /**
     * Get course completion criteria
     *
     * @param int $criteriatype Specific criteria type to return (optional)
     */
    public function get_criteria($criteriatype = null) {
        global $CFG;

        // Fill cache if empty.
        if (!is_array($this->criteria)) {
            global $DB;

            $params = array(
                'course'    => $this->course->id
            );

            // Load criteria from database.
            $records = (array)$DB->get_records('course_completion_criteria', $params);

            // Order records so activities are in the same order as they appear on the course view page.
            if ($records) {
                $activitiesorder = array_keys(get_fast_modinfo($this->course)->get_cms());
                usort($records, function ($a, $b) use ($activitiesorder) {
                    $aidx = ($a->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) ?
                        array_search($a->moduleinstance, $activitiesorder) : false;
                    $bidx = ($b->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) ?
                        array_search($b->moduleinstance, $activitiesorder) : false;
                    if ($aidx === false || $bidx === false || $aidx == $bidx) {
                        return 0;
                    }
                    return ($aidx < $bidx) ? -1 : 1;
                });
            }

            // Build array of criteria objects.
            $this->criteria = array();
            foreach ($records as $record) {
                require_once($CFG->dirroot.'/local/swtc/classes/criteria/completion_criteria.php');
                $this->criteria[$record->id] = completion_criteria::factory((array)$record);
            }
        }

        // If after all criteria.
        if ($criteriatype === null) {
            return $this->criteria;
        }

        // If we are only after a specific criteria type.
        $criteriax = array();
        foreach ($this->criteria as $criterion) {

            if ($criterion->criteriatype != $criteriatype) {
                continue;
            }

            $criteriax[$criterion->id] = $criterion;
        }

        return $criteriax;
    }

    /**
     * Get all course criteria's completion objects for a user
     *
     * @param int $userid User id
     * @param int $criteriatype Specific criteria type to return (optional)
     * @return array
     */
    public function get_completions($userid, $criteriatype = null) {
        $criteria = $this->get_criteria($criteriatype);

        $completions = array();

        foreach ($criteria as $criterion) {
            $params = array(
                'course'        => $this->course_id,
                'userid'        => $userid,
                'criteriaid'    => $criterion->id
            );

            $completion = new completion_criteria_completion($params);
            $completion->attach_criteria($criterion);

            $completions[] = $completion;
        }

        return $completions;
    }

    /**
     * Obtains a list of activities for which completion is enabled on the
     * course. The list is ordered by the section order of those activities.
     *
     * @return cm_info[] Array from $cmid => $cm of all activities with completion enabled,
     *   empty array if none
     */
    public function get_activities() {
        $modinfo = get_fast_modinfo($this->course);
        $result = array();
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->completion != COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
                $result[$cm->id] = $cm;
            }
        }
        return $result;
    }
}
