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
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/locallib.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 03/28/21 - Added *** function.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS classes.
// SWTC ********************************************************************************.
use \format_swtccustom\output\htmlpage;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');

require_once($CFG->libdir. '/accesslib.php');
require_once($CFG->dirroot. '/user/profile/lib.php');
require_once($CFG->dirroot.'/enrol/locallib.php');
require_once($CFG->dirroot. '/user/lib.php');

/**
 * Look for the portfolio name in the $categoryids array. When found, only return the contextid (instanceid).
 *
 * @param The portfolio name to look for and the list of all portfolios.
 *
 * @return $int   The context of the portfolio name.
 *
 *
 * History:
 *
 *  11/28/18 - Original version.
 *
 */
function local_swtc_find_context_from_name($portfolioname, $cats) {
    $cat = $cats[array_search($portfolioname, array_column($cats, 'catname'))];
    return $cat['context'];
}

/**
 * If PremierSupport or ServiceDelivery manager or administrator ventures outside their own portfolio, they are no longer
 *          considered a manager or administrator. Substitute either PremierSupport-student or ServiceDelivery-student as role.
 *
 * @param $cat        A catlist variable.
 * @param $user        A user variable.
 *
 * @return $temp_user    $user (passed in) with the rolename and roleid changed if required.
 *
 *
 * History:
 *
 * 11/06/20 - Initial writing.
 *
 */
function local_swtc_change_user_access($cat, &$user) {
    global $DB;

    // SWTC ********************************************************************************.
    // SWTC LMS swtcuser and debug variables.
    $swtcuser = swtc_get_user([
        'userid' => $user->id,
        'username' => $user->username]);
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $roleshortname = null;
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_change_user_access.enter.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "swtcuser array follows :";
        $messages[] = print_r($swtcuser, true);
        $messages[] = "swtcuser array ends.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Remember to set the roleid.
    // Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
    // if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
    // it was when this was called.
    // SWTC ********************************************************************************.
    if (!empty($roleshortname)) {
        $user->roleshortname = $roleshortname;
        $role = $DB->get_record('role', array('shortname' => $user->roleshortname), '*', MUST_EXIST);
        $user->roleid = $role->id;
    }

    return;
}

/**
 * Returns an array of all the configured user access types.
 *
 * Notes:
 *          In the mdl_user_info_field table, param1 contains all the possible user access types that are defined.
 *
 * @param N/A
 *
 * @return $array  All the configured user access types.
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_get_all_accesstypes() {
    global $DB;

    // SWTC ********************************************************************************.
    // Variables begin...

    // Variables end...
    // SWTC ********************************************************************************.

    // Get ALL the possible user access types.
    $types = $DB->get_record('user_info_field', array('shortname' => 'accesstype'), 'id, param1');

    // Explode alltypes into an array using a delimiter of a new line character.
    $alltypes = explode("\n", $types->param1);

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/locallib.php ===local_swtc_get_all_accesstypes.exit===";
        $messages[] = "About to print alltypes.";
        $messages[] = print_r($alltypes, true);
        $messages[] = "Finished printing alltypes.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return $alltypes;

}

/**
 * Returns all courses NOT in any Lenovo internal portfolios (for listing in related courses and suggested courses listboxes).
 *
 * @param N/A
 *
 * @return $array   The courses array.
 *
 * Version details
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_get_all_courses() {
    global $DB;

    // SWTC ********************************************************************************.
    // Variables begin...
    // SWTC ********************************************************************************.
    // Only list courses NOT in top level categories 60 (Lenovo Internal) and 73 (resource).
    // SWTC ********************************************************************************.
    $where = "WHERE ((cc.path NOT LIKE '/60/%') AND (cc.path NOT LIKE '%/60'))
        AND ((cc.path NOT LIKE '/73/%') AND (cc.path NOT LIKE '%/73'))";

    $sql = "SELECT c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category
                FROM {course} c
                LEFT OUTER JOIN {course_categories} cc ON (c.category = cc.id)
                $where
                ORDER BY c.shortname ASC";

    // Variables end...
    // SWTC ********************************************************************************.
    $records = $DB->get_recordset_sql($sql, null, 0, SWTC_SQL_MAX_RECORDS);

    return $records;

}

/**
 * Capture (record) the click.
 *
 * @param $object  stdClass object in the following structure:
 *              action ("click")
 *              type ("related" or "suggested")
 *              parentcourseid  (only if "related")
 *              clickedcourseid
 *              clickeduserid
 *
 * @return N/A
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_capture_click($data) {
    global $DB, $USER;

    // SWTC ********************************************************************************.
    // SWTC LMS swtcuser and debug variables.
    $swtcuser = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_get_debug();

    // Other SWTC variables.
    $useraccesstype = $swtcuser->useraccesstype;
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_capture_click.enter.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "swtcuser array follows :";
        $messages[] = print_r($swtcuser, true);
        $messages[] = "swtcuser array ends.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Switch on each type of "click".
    // SWTC ********************************************************************************.
    switch ($data->type) {
        // SWTC ********************************************************************************.
        // User clicked on a "related" course.
        // SWTC ********************************************************************************.
        case 'related':
            // SWTC ********************************************************************************.
            // See if the user has clicked on the related course, from the parent course, in the past.
            // $data->parentcourseid      Parent course id.
            // $data->clickedcourseid     The course id that was clicked on.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // SWTC ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['relatedcourseid'] = $data->clickedcourseid;
            if (!$record = $DB->get_record('local_swtc_rc_details', $params)) {
                // User has NOT clicked this course before.
                // Save the click by this user.
                // SWTC ********************************************************************************.
                // User has NOT clicked this course before; create the record.
                // SWTC ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['accesstype'] = $useraccesstype;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;
                $params['dateclicked'] = time();
                $params['dateenrolled'] = 0;

                if ($DB->insert_record('local_swtc_rc_details', $params, false)) {
                    // The record was successfully created.
                    // Update the "clicks" counter in local_swtc_rc.
                    $params = array('active' => COURSE_ACTIVE, 'parentcourseid' => $data->parentcourseid,
                        'relatedcourseid' => $data->clickedcourseid);
                    if ($record = $DB->get_record('local_swtc_rc', $params)) {
                        // Increment "clicks".
                        $record->clicks ++;
                        // Update just the "clicks" field.
                        $DB->set_field('local_swtc_rc', 'clicks', $record->clicks, array('active' => COURSE_ACTIVE,
                            'parentcourseid' => $data->parentcourseid, 'relatedcourseid' => $data->clickedcourseid));
                        // In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                        // inserting (needed for log events function).
                        $recordid = $record->id;
                    } else {
                        // Updated to add record in "local_swtc_rc" if the record doesn't exist.
                        // SWTC ********************************************************************************.
                        // Create the record if it doesn't exist yet.
                        // SWTC ********************************************************************************.
                        // Update the timecreated, get the USER->id of the user, and set "active" to active.
                        $params['active'] = COURSE_ACTIVE;
                        $params['usercreated'] = $USER->id;
                        $params['timecreated'] = time();
                        $params['usermodified'] = 0;
                        $params['timemodified'] = 0;
                        $params['parentcourseid'] = $data->parentcourseid;
                        $params['relatedcourseid'] = $data->clickedcourseid;
                        $params['clicks'] = 1;
                        $params['enrollments'] = 0;

                        $DB->insert_record('local_swtc_rc', $params, true);

                        // In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                        // inserting (needed for log events function).
                        $record = $DB->get_record('local_swtc_rc', $params);
                        $recordid = $record->id;
                    }
                } else {
                    // The record was NOT successfully created.
                    // Update the "clicks" counter in local_swtc_rc.
                    $params = array('active' => COURSE_ACTIVE, 'parentcourseid' => $data->parentcourseid,
                        'relatedcourseid' => $data->clickedcourseid);
                }

                // Added several log event functions to write events and data to mdl_logstore_standard_log.
                local_swtc_log_related_clicked($recordid, $data->clickedcourseid);
            }
            break;

        // SWTC ********************************************************************************.
        // User clicked on a "suggested" course.
        // SWTC ********************************************************************************.
        case 'suggested':
            // SWTC ********************************************************************************.
            // See if the user has clicked on the suggested course, from the parent course, in the past.
            // $data->parentcourseid      Parent course id.
            // $data->clickedcourseid     The course id that was clicked on.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // SWTC ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['suggestedcourseid'] = $data->clickedcourseid;
            if (!$record = $DB->get_record('local_swtc_sc_details', $params)) {
                // User has NOT clicked this course before.
                // Save the click by this user.
                // SWTC ********************************************************************************.
                // User has NOT clicked this course before; create the record.
                // SWTC ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['suggestedcourseid'] = $data->clickedcourseid;
                $params['dateclicked'] = time();
                $params['dateenrolled'] = 0;

                if ($DB->insert_record('local_swtc_sc_details', $params, false)) {
                    // The record was successfully created.
                    // Update the "clicks" counter in local_swtc_sc.
                    $params = array('active' => COURSE_ACTIVE, 'suggestedcourseid' => $data->clickedcourseid);
                    if ($record = $DB->get_record('local_swtc_sc', $params)) {
                        // Increment "clicks".
                        $record->clicks ++;
                        $DB->set_field('local_swtc_sc', 'clicks', $record->clicks, array('active' => COURSE_ACTIVE,
                            'suggestedcourseid' => $data->clickedcourseid));
                    }
                }
            }
            break;

        // SWTC ********************************************************************************.
        // Event - all others
        // SWTC ********************************************************************************.
        default:
            break;
    }
}

/**
 * Capture (record) the enrollment.
 *
 * @param $object  stdClass object in the following structure:
 *              action ("enroll")
 *              type ("related" or "suggested")
 *              parentcourseid  (only if "related")
 *              clickedcourseid
 *              clickeduserid
 *
 * @return N/A
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_capture_enrollment($data) {
    global $DB, $USER;

    // SWTC ********************************************************************************.
    // SWTC LMS swtcuser and debug variables.
    $swtcuser = swtc_get_user([
        'userid' => $USER->id,
        'username' => $USER->username]);
    $debug = swtc_get_debug();
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        // SWTC ********************************************************************************.
        // Always output standard header information.
        // SWTC ********************************************************************************.
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_capture_enrollment.enter.";
        $messages[] = get_string('swtc_debug', 'local_swtc');
        $messages[] = "swtcuser array follows :";
        $messages[] = print_r($swtcuser, true);
        $messages[] = "swtcuser array ends.";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Switch on each type of "click".
    // SWTC ********************************************************************************.
    switch ($data->type) {
        // SWTC ********************************************************************************.
        // User clicked on a "related" course.
        // SWTC ********************************************************************************.
        case 'related':
            // SWTC ********************************************************************************.
            // See if the user has previously clicked on the course they just enrolled in.
            // $data->parentcourseid           Parent course id.
            // $data->clickedcourseid          The course they just enrolled in.
            // $data->clickeduserid        The userid of the user that did the clicking.
            // SWTC ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['relatedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_rc_details', $params)) {
                // In local_swtc_capture_click and local_swtc_capture_enrollment, saved the record id after
                // inserting (needed for log events function).
                $recordid = $record->id;

                // User has clicked this course before. So, capture enrollment.
                // TODO: This section.
                // SWTC ********************************************************************************.
                // User has clicked this course before; update the record with the time they enrolled.
                // SWTC ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;
                $dateenrolled = time();

                // Update just the "dateenrolled" field.
                $DB->set_field('local_swtc_rc_details', 'dateenrolled', $dateenrolled, $params);

                // Note: Have to remove "userid".
                $params = array();
                $params['active'] = COURSE_ACTIVE;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['relatedcourseid'] = $data->clickedcourseid;

                // Update the "enrollments" counter in local_swtc_rc.
                if ($record = $DB->get_record('local_swtc_rc', $params)) {
                    // Increment "enrollments".
                    $record->enrollments ++;
                    // Update just the "enrollments" field.
                    $DB->set_field('local_swtc_rc', 'enrollments', $record->enrollments, $params);
                }

                // Added several log event functions to write events and data to mdl_logstore_standard_log.
                local_swtc_log_related_enrolled($recordid, $data->clickedcourseid);
            }
            break;

        // SWTC ********************************************************************************.
        // User clicked on a "suggested" course.
        // SWTC ********************************************************************************.
        case 'suggested':
            // SWTC ********************************************************************************.
            // See if the user has previously clicked on the course they just enrolled in.
            // $data['clickedcourseid']          The coure they just enrolled in.
            // $data['userid']              The userid of the user.
            // SWTC ********************************************************************************.
            $params = array();
            $params['userid'] = $data->clickeduserid;
            $params['parentcourseid'] = $data->parentcourseid;
            $params['suggestedcourseid'] = $data->clickedcourseid;
            if ($record = $DB->get_record('local_swtc_sc_details', $params)) {
                // User has clicked this course before. So, capture enrollment.
                // TODO: This section.
                // SWTC ********************************************************************************.
                // User has clicked this course before; update the record with the time they enrolled.
                // SWTC ********************************************************************************.
                $params = array();
                $params['userid'] = $data->clickeduserid;
                $params['parentcourseid'] = $data->parentcourseid;
                $params['suggestedcourseid'] = $data->clickedcourseid;
                $dateenrolled = time();

                // Update just the "dateenrolled" field.
                $DB->set_field('local_swtc_sc_details', 'dateenrolled', $dateenrolled, $params);

                // Note: Have to remove "userid".
                $params = array();
                $params['active'] = COURSE_ACTIVE;
                $params['suggestedcourseid'] = $data->clickedcourseid;

                // Update the "enrollments" counter in local_swtc_rc.
                if ($record = $DB->get_record('local_swtc_sc', $params)) {
                    // Increment "enrollments".
                    $record->enrollments ++;
                    // Update just the "enrollments" field.
                    $DB->set_field('local_swtc_sc', 'enrollments', $record->enrollments, $params);
                }
            }
            break;

            // SWTC ********************************************************************************.
            // Event - all others
            // SWTC ********************************************************************************.
        default:
                break;
    }

    return;

}

/**
 * Log event functions to write events and data to mdl_logstore_standard_log. The functions are:
 *      local_swtc_log_related_clicked
 *      local_swtc_log_related_enrolled
 *      local_swtc_log_suggested_clicked
 *      local_swtc_log_suggested_enrolled
 *
 * Modeled after /report/customsql:
 *
 *           function report_customsql_log_delete($id) {
 *              $event = \report_customsql\event\query_deleted::create(
 *                      array('objectid' => $id, 'context' => context_system::instance()));
 *              $event->trigger();
 *          }
 *
 * @param $id  integer The record id from the table being modified.
 *
 * @return N/A
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_log_related_clicked($id, $courseid) {
    // SWTC ********************************************************************************.
    // The objectid (id) is the record id from the table being modified.
    // SWTC ********************************************************************************.
    $event = \local_swtc\event\related_clicked::create(
                        array('objectid' => $id, 'context' => context_course::instance($courseid)));
    $event->trigger();
}

function local_swtc_log_related_enrolled($id, $courseid) {
    // SWTC ********************************************************************************.
    // The objectid (id) is the record id from the table being modified.
    // SWTC ********************************************************************************.
    $event = \local_swtc\event\related_enrolled::create(
                        array('objectid' => $id, 'context' => context_course::instance($courseid)));
    $event->trigger();
}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach ($arr as $key => $unused) {
            return $key;
        }
        return null;
    }
}

/**
 * Returns all the user profile information for the userid passed.
 *
 * @param $userid   The userid of the user to check.
 *
 * @return $array   Array of all the user profile information.
 *
 * Version details
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 **/
function local_swtc_get_user_profile($userid) {

    $temp = new stdClass();

    $temp->id = $userid;
    profile_load_data($temp);

    return $temp;

}

/**
 * Test whether a given cohortid+roleid has been defined
 *
 * @param integer $cohortid the id of a cohort
 * @param integer|null $roleid the id of a role, or null to just test cohort
 * @return boolean
 *
 * Version details
 *
 * History:
 *
 * 10/15/20 - Initial writing.
 *
 */
function local_swtc_cohortrole_exists($cohortid, $roleid = null) {
    global $DB;

    $params = array('cohortid' => $cohortid);
    if ($roleid !== null) {
        $params['roleid'] = $roleid;
    }

    return $DB->record_exists('local_cohortrole', $params);
}
