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
 * Functions used by suggested courses.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	06/10/19 - Initial writing.
 * 07/18/19 - In local_swtc_courses, changed field "courseids" to "courseid"; added row for each courseid (not grouping courseids
 *                      anymore); added "active" field; added logic for checking "new" suggested courses against the one's
 *                      currently in the database.
 * 07/21/19 - Added functions used by modified couse_slider block.
 * 07/26/19 - Removed suggestedcourses_block_course_slider_exists and suggestedcourses_block_course_slider_dynamically_add
 *                      (since we will be using Moosh to add the course_slider block to all courses).
 * 08/05/19 - Changed table name from "local_swtc_courses" to "local_swtc_sc_courses".
 * 08/22/19 - Moved suggestedcourses_get_all_accesstypes to /local/swtc/lib/locallib.php as local_swtc_get_all_accesstypes.
 * 08/22/19 - Changed table names to "local_swtc_sc"and "local_swtc_rc"; skip all the other 'mform_isexpanded_id_***' keys.
 * 08/23/19 - If the user type is Lenovo-admin or Lenovo-siteadmin, set a special block title and get the ServiceProvider courses.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 */

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');   // Include constants.

// require_once("$CFG->libdir/formslib.php");
// require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->libdir. '/modinfolib.php');        // 07/22/19
require_once($CFG->libdir . '/blocklib.php');   // 07/23/19


/**
 * Returns an array of all the user access types based on a pattern. Remember to only use "Lenovo-stud".
 *
 * Notes:
 *          In the mdl_user_info_field table, param1 contains all the possible user access types that are defined.
 *
 * @param $match	The user access type to match.
 *
 * @return $array  All the user access types that match the pattern. Key will be "prettyname" verison of the match string.
 *
 * History:
 *
 * 06/10/19 - Initial writing.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 **/
function suggestedcourses_get_users_by_type($match = null, $prettyname = null) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $access_lenovo_admin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
    $access_lenovo_siteadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
    $returntypes = array();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/suggestedcourseslib.php ===suggestedcourses_get_users_by_type.enter===";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Get ALL the possible user access types.
    $alltypes = local_swtc_get_all_accesstypes();

    if (isset($debug)) {
        $messages[] = "About to print alltypes.";
        $messages[] = print_r($alltypes, true);
        $messages[] = "Finished printing alltypes.";
        // print_object($alltypes);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************.
    // Special case: If the requested type is "sitewide", only return "sitewide" and nothing else.
    // Lenovo ********************************************************************************.
    if (stripos($match, 'sitewide') !== false) {
        if (isset($prettyname)) {
            $returntypes[$prettyname]['accesstypes'][] = 'sitewide';
        } else {
            $returntypes['accesstypes'][] = 'sitewide';
        }
    } else {
        // Loop through and see if there is a match with the type what was passed in. If so, add it to the array.
        foreach ($alltypes as $type) {
            // Lenovo ********************************************************************************.
            // All other types (remember to skip Lenovo-admin and Lenovo-siteadmin).
            // Lenovo ********************************************************************************.
            if ((preg_match($match, $access_lenovo_admin)) || (preg_match($match, $access_lenovo_siteadmin))) {
                continue;
            } else {
                if (preg_match($match, $type)) {
                    if (isset($prettyname)) {
                        $returntypes[$prettyname]['accesstypes'][] = $type;
                    } else {
                        $returntypes['accesstypes'][] = $type;
                    }
                }
            }
        }
    }

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/suggestedcourseslib.php ===suggestedcourses_get_users_by_type.exit===";
        $messages[] = "About to print returntypes.";
        $messages[] = print_r($returntypes, true);
        $messages[] = "Finished printing returntypes.";
        // print_object($returntypes);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

	return $returntypes;

}

/**
 * Saves (puts) all the suggested courses for a particular user access type in the database.
 *
 * @param $types	Array of user access types.
 * @param $array   Array of courses.
 *
 * @return N/A
 *
 * History:
 *
 * 06/11/19 - Initial writing.
 * 07/18/19 - In local_swtc_courses, changed field "courseids" to "courseid"; added row for each courseid (not grouping courseids
 *                      anymore); added "active" field; added logic for checking "new" suggested courses against the one's
 *                      currently in the database.
 * 08/05/19 - Changed table name from "local_swtc_courses" to "local_swtc_sc_courses".
 * 08/22/19 - Changed table names to "local_swtc_sc"and "local_swtc_rc".
 *
 **/
function suggestedcourses_put_courses_by_type($types, $courseids) {
    global $CFG, $DB, $SESSION, $USER;

    // Lenovo ********************************************************************************.
    // Variables begin...
    $params = array();

    // Variables end...
    // Lenovo ********************************************************************************.

    // Temporarily put all the courses into a comma separated string.
    // $courses = implode(', ', $courseids);

    // print_object($types);
    // print_object($courseids);
    // die;

    // Clean input.
    $courseids = clean_param_array($courseids, PARAM_NOTAGS);

    // Get the accesstypes array.
    foreach($types as $accesstypes) {
        foreach($accesstypes['accesstypes'] as $type) {
            // Lenovo ********************************************************************************.
            // For each accesstype, get all the currently active suggested courses.
            // Lenovo ********************************************************************************.
            $params = array($DB->sql_compare_text('accesstype') => $type, 'active' => COURSE_ACTIVE);
            $currently_active = $DB->get_records('local_swtc_sc', $params);
            // print_object($currently_active);

            // Lenovo ********************************************************************************.
            // Loop through each of the currently active suggested courses and:
            //      If currently active (CA) course is NOT in new active (NA) courses:
            //          - Set CA course "active" to 0 (inactive).
            //          - Update CA course modified date / time.
            //          - Update CA course modified userid.
            //      If CA course is IN NA courses:
            //          - Update CA course modified date / time.
            //          - Update CA course modified userid.
            // Lenovo ********************************************************************************.
            foreach($currently_active as $current) {
                // print_object($courseids);
                // Use array_search to find if the key / value pair is present or not.
                // print_object($current);
                // $key = array_search($current->suggestedcourseid, array_column($courseids, 'courseid'));
                if (in_array($current->suggestedcourseid, $courseids)) {
                    // Course exists. Will update timemodified and usermodified later.
                    // print_r("course exists.\n");
                } else {
                    // Course does NOT exist. Update "active" and other fields.
                    // print_r("course does NOT exist. setting $current->suggestedcourseid to inactive.\n");

                    // Update the timemodified, get the USER->id of the user, and set "active" to inactive.
                    $params['id'] = $current->id;
                    $params['active'] = COURSE_INACTIVE;
                    $params['timemodified'] = time();
                    $params['usermodified'] = $USER->id;

                    // Update the record.
                    $DB->update_record('local_swtc_sc', $params);
                }
            }

            foreach($courseids as $courseid) {
                // Lenovo ********************************************************************************.
                // Update the record if it exists.
                // Lenovo ********************************************************************************.
                $params = array($DB->sql_compare_text('accesstype') => $type, 'suggestedcourseid' => $courseid, 'active' => COURSE_ACTIVE);
                if ($DB->record_exists('local_swtc_sc', $params)) {
                    $record = $DB->get_record('local_swtc_sc', $params);

                    // Only update the timemodified, and therefore the courses, if we have changed the course to be saved.
                    // Update the timemodified, get the USER->id of the user, and set "active" to active.
                    $params['id'] = $record->id;
                    $params['active'] = COURSE_ACTIVE;
                    $params['timemodified'] = time();
                    $params['usermodified'] = $USER->id;

                    // Add the course.
                    $DB->update_record('local_swtc_sc', $params);
                } else {
                    // Lenovo ********************************************************************************.
                    // Create the record if it doesn't exist yet.
                    // Lenovo ********************************************************************************.
                    // Update the timecreated, get the USER->id of the user, and set "active" to active.
                    $params['active'] = COURSE_ACTIVE;
                    $params['accesstype'] = $type;
                    $params['usercreated'] = $USER->id;
                    $params['timecreated'] = time();
                    $params['usermodified'] = 0;
                    $params['timemodified'] = 0;
                    $params['suggestedcourseid'] = $courseid;
                    $params['clicks'] = 0;
                    $params['enrollments'] = 0;

                    $recordid = $DB->insert_record('local_swtc_sc', $params, true);
                }
            }
        }
    }

    return;

}

/**
 * Gets all the suggested courses for a particular user access type.
 *
 * @param $string	User access type (one only).
 *
 * @return $array   Array of courses. Key will be "prettyname" verison of the match string.
 *
 * History:
 *
 * 06/12/19 - Initial writing.
 * 07/18/19 - In local_swtc_courses, changed field "courseids" to "courseid"; added row for each courseid (not grouping courseids
 *                      anymore); added "active" field check.
 * 08/05/19 - Changed table name from "local_swtc_courses" to "local_swtc_sc_courses".
 * 08/22/19 - Changed table names to "local_swtc_sc"and "local_swtc_sc".
 * 08/23/19 - If the user type is Lenovo-admin or Lenovo-siteadmin, set a special block title and get the ServiceProvider courses.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 **/
function suggestedcourses_get_courses_by_type($match) {
    global $CFG, $DB, $SESSION, $USER;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $access_ps = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch;
    $access_lenovo_sd = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch;
    $access_ibm = $SESSION->EBGLMS->STRINGS->ibm->access_ibm_pregmatch;
    $access_lenovo_stud = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_stud;
    $access_lenovo_admin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
    $access_lenovo_siteadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
    $access_serviceprovider = $SESSION->EBGLMS->STRINGS->serviceprovider->access_serviceprovider_pregmatch_stud;
    $access_maintech = $SESSION->EBGLMS->STRINGS->maintech->access_maintech_pregmatch_stud;
    $access_asp_maintech = $SESSION->EBGLMS->STRINGS->asp_maintech->access_asp_maintech_pregmatch_stud;

    $sections = array('sitewide', 'ibm', 'lenovo', 'serviceprovider', 'premiersupport', 'servicedelivery', 'maintech', 'asp');
    $types = null;
    $courses = array();
    $suggestedcourses = null;
    $params = array();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/suggestedcourseslib.php ===suggestedcourses_get_courses_by_type.enter===";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Create the strings that will be passed in data.
    foreach ($sections as $section) {
        ${$section . 'suggestedcourses'} = $section . 'suggestedcourses';
    }

    // Lenovo ********************************************************************************.
    // Site wide
    // Lenovo ********************************************************************************.
    if (stripos('sitewide', $match) !== false) {
        $types = suggestedcourses_get_users_by_type($match, $sitewidesuggestedcourses);
        $suggestedcourses = $sitewidesuggestedcourses;

    // Lenovo ********************************************************************************.
    // IBM
    // Lenovo ********************************************************************************.
    } else if (stripos($access_ibm, $match) !== false) {
        // print_object("in ibmsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_ibm, $ibmsuggestedcourses);
        $suggestedcourses = $ibmsuggestedcourses;

    // Lenovo ********************************************************************************.
    // Lenovo student
    // Lenovo ********************************************************************************.
    } else if (stripos($access_lenovo_stud, $match) !== false) {
        // print_object("in lenovostudentsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_lenovo_stud, $lenovosuggestedcourses);
        $suggestedcourses = $lenovosuggestedcourses;

    // Lenovo ********************************************************************************.
    // Lenovo admin (return ServiceProviders courses)
    // Lenovo ********************************************************************************.
    } else if (stripos($access_lenovo_admin, $match) !== false) {
        // print_object("in lenovoadminsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_serviceprovider, $lenovosuggestedcourses);
        $suggestedcourses = $lenovosuggestedcourses;

    // Lenovo ********************************************************************************.
    // Lenovo siteadmin (return ServiceProviders courses)
    // Lenovo ********************************************************************************.
    } else if (stripos($access_lenovo_siteadmin, $match) !== false) {
        // print_object("in lenovositeadminsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_serviceprovider, $lenovosuggestedcourses);
        $suggestedcourses = $lenovosuggestedcourses;

    // Lenovo ********************************************************************************.
    // Service provider
    // Lenovo ********************************************************************************.
    } else if (stripos($access_serviceprovider, $match) !== false) {
        // print_object("in serviceprovidersuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_serviceprovider, $serviceprovidersuggestedcourses);
        $suggestedcourses = $serviceprovidersuggestedcourses;

    // Lenovo ********************************************************************************.
    // Premier Support
    // Lenovo ********************************************************************************.
    } else if (stripos($access_ps, $match) !== false) {
        // print_object("in premiersupportsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_ps, $premiersupportsuggestedcourses);
        $suggestedcourses = $premiersupportsuggestedcourses;

    // Lenovo ********************************************************************************.
    // Service Delivery
    // Lenovo ********************************************************************************.
    } else if (stripos($access_lenovo_sd, $match) !== false) {
        // print_object("in servicedeliverysuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_lenovo_sd, $servicedeliverysuggestedcourses);
        $suggestedcourses = $servicedeliverysuggestedcourses;

    // Lenovo ********************************************************************************.
    // Maintech
    // Lenovo ********************************************************************************.
    } else if (stripos($access_maintech, $match) !== false) {
        // print_object("in maintechsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_maintech, $maintechsuggestedcourses);
        $suggestedcourses = $maintechsuggestedcourses;

    // Lenovo ********************************************************************************.
    // ASP
    // Lenovo ********************************************************************************.
    } else if (stripos($access_asp_maintech, $match) !== false) {
        // print_object("in aspsuggestedcourses");
        // Get all the user types.
        $types = suggestedcourses_get_users_by_type($access_asp_maintech, $aspsuggestedcourses);
        $suggestedcourses = $aspsuggestedcourses;
    }

    // if (isset($debug)) {
    //     $messages[] = "In /local/swtc/lib/suggestedcourseslib.php ===suggestedcourses_get_courses_by_type.1===";
    //     $messages[] = "About to print suggestedcourses.";
    //     $messages[] = print_r($suggestedcourses, true);
    //     $messages[] = "Finished printing suggestedcourses.";
    //     print_object($suggestedcourses);
    //     debug_logmessage($messages, 'both');
    //     unset($messages);
    // }

    if (!empty($types)) {
        // For the user access types, get the courses.
        foreach ($types as $key => $accesstypes) {
            // print_object($accesstypes);
            // $courses[$suggestedcourses] = '';
            foreach ($accesstypes['accesstypes'] as $type) {
                // Get all the records indexed by accesstypes.
                $params = array($DB->sql_compare_text('accesstype') => $type, 'active' => COURSE_ACTIVE);
                if ($records = $DB->get_records('local_swtc_sc', $params)) {
                    // Some records exist.
                    // print_object($records);
                    foreach ($records as $record) {
                        // print_object($record);
                        $courses[$suggestedcourses][] = $record->suggestedcourseid;
                    }
                } else {
                    // No records exist (yet).
                }
            }
        }
    }

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/suggestedcourseslib.php ===suggestedcourses_get_courses_by_type.exit===";
        $messages[] = "About to print courses.";
        $messages[] = print_r($courses, true);
        $messages[] = "Finished printing courses.";
        // print_object($courses);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    return $courses;

}

/**
 * Processes all data returned from the suggestedcourses_form.
 *
 * @param $data     All the form data.
 *
 * @return N/A
 *
 * History:
 *
 * 06/11/19 - Initial writing.
 * 07/18/19 - In local_swtc_courses, changed field "courseids" to "courseid"; added row for each courseid (not grouping courseids
 *                      anymore).
 * 08/22/19 - Skip all the other 'mform_isexpanded_id_***' keys.
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 *
 **/
function suggestedcourses_process_formdata($data) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $access_ps = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch;
    $access_lenovo_sd = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch;
    $access_ibm = $SESSION->EBGLMS->STRINGS->ibm->access_ibm_pregmatch;
    $access_lenovo_stud = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_stud;
    $access_lenovo_admin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_admin;
    $access_lenovo_siteadmin = $SESSION->EBGLMS->STRINGS->lenovo->access_lenovo_pregmatch_siteadmin;
    $access_serviceprovider = $SESSION->EBGLMS->STRINGS->serviceprovider->access_serviceprovider_pregmatch_stud;
    $access_maintech = $SESSION->EBGLMS->STRINGS->maintech->access_maintech_pregmatch_stud;
    $access_asp_maintech = $SESSION->EBGLMS->STRINGS->asp_maintech->access_asp_maintech_pregmatch_stud;

    $sections = array('sitewide', 'ibm', 'lenovo', 'serviceprovider', 'premiersupport', 'servicedelivery', 'maintech', 'asp');
    $types = null;
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/suggestedcourseslib.php===suggestedcourses_process_formdata.enter===";
        debug_logmessage($messages, 'both');
        unset($messages);

        $messages[] = "About to print form data.";
        $messages[] = print_r($data, true);
        $messages[] = "Finished printing form data.";
        // print_object($data);
        debug_logmessage($messages, 'detailed');
        unset($messages);
    }

    // Create the strings that will be passed in data.
    foreach ($sections as $section) {
        ${$section . 'suggestedcourses'} = $section . 'suggestedcourses';
    }

    // print_object($sitewidesuggestedcourses);
    // print_object($ibmsuggestedcourses);
    // print_object($lenovosuggestedcourses);
    // print_object($serviceprovidersuggestedcourses);
    // print_object($premiersupportsuggestedcourses);
    // print_object($servicedeliverysuggestedcourses);
    // print_object($maintechsuggestedcourses);
    // print_object($aspsuggestedcourses);

    // Now get all the courses for each section.
    foreach($data as $key => $value) {
        // print_object($key);
        // Lenovo ********************************************************************************.
        // Skip 'submitbutton' and all the 'mform_isexpanded_id_sitewide'.
        // 08/22/19 - Skip all the other 'mform_isexpanded_id_***' keys. mform_isexpanded_id_lenovo
        // Lenovo ********************************************************************************.
        if ((stripos($key, 'submitbutton') === false) && (stripos($key, 'mform_isexpanded_id_sitewide') === false) && (stripos($key, 'mform_isexpanded_id_ibm') === false) && (stripos($key, 'mform_isexpanded_id_lenovo') === false) && (stripos($key, 'mform_isexpanded_id_serviceprovider') === false) && (stripos($key, 'mform_isexpanded_id_premiersupport') === false) && (stripos($key, 'mform_isexpanded_id_servicedelivery') === false) && (stripos($key, 'mform_isexpanded_id_servicedelivery') === false) && (stripos($key, 'mform_isexpanded_id_maintech') === false) && (stripos($key, 'mform_isexpanded_id_asp') === false)) {

            // Lenovo ********************************************************************************.
            // Site wide
            // Lenovo ********************************************************************************.
            if (stripos($key, $sitewidesuggestedcourses) !== false) {
                // print_object("in sitewidesuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type('sitewide', $sitewidesuggestedcourses);

            // Lenovo ********************************************************************************.
            // IBM
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $ibmsuggestedcourses) !== false) {
                // print_object("in ibmsuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_ibm, $ibmsuggestedcourses);

            // Lenovo ********************************************************************************.
            // Lenovo
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $lenovosuggestedcourses) !== false) {
                // print_object("in lenovosuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_lenovo_stud, $lenovosuggestedcourses);
                // TODO: Other Lenovo user types.

            // Lenovo ********************************************************************************.
            // Service provider
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $serviceprovidersuggestedcourses) !== false) {
                // print_object("in serviceprovidersuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_serviceprovider, $serviceprovidersuggestedcourses);

            // Lenovo ********************************************************************************.
            // Premier Support
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $premiersupportsuggestedcourses) !== false) {
                // print_object("in premiersupportsuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_ps, $premiersupportsuggestedcourses);

            // Lenovo ********************************************************************************.
            // Service Delivery
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $servicedeliverysuggestedcourses) !== false) {
                // print_object("in servicedeliverysuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_lenovo_sd, $servicedeliverysuggestedcourses);

            // Lenovo ********************************************************************************.
            // Maintech
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $maintechsuggestedcourses) !== false) {
                // print_object("in maintechsuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_maintech, $maintechsuggestedcourses);

            // Lenovo ********************************************************************************.
            // ASP
            // Lenovo ********************************************************************************.
            } else if (stripos($key, $aspsuggestedcourses) !== false) {
                // print_object("in aspsuggestedcourses");
                // Get all the user types.
                $types = suggestedcourses_get_users_by_type($access_asp_maintech, $aspsuggestedcourses);
            }

            if (isset($debug)) {
                $messages[] = "In /local/swtc/lib/suggestedcourseslib.php:suggestedcourses_process_formdata ===1.leave===";
                debug_logmessage($messages, 'both');
                unset($messages);

                $messages[] = "About to print types.";
                $messages[] = print_r($types, true);
                $messages[] = "Finished printing types. About to print courses.";
                $messages[] = print_r($value, true);
                $messages[] = "Finished printing courses.";
                // print_object($types);
                // print_object($value);
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }

            suggestedcourses_put_courses_by_type($types, $value);

        }
    }

    return;

}
