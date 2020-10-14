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
 * All Lenovo customized functions associcated with Moodle /lib/enrollib.php. Remember to add the following at the top of any
 *          module that requires these functions:
 *              require_once($CFG->dirroot . '/local/swtc/lib/swtc_lib_enrollib.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/swtc_lib_enrollib.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	11/03/19 - Initial writing; moved swtc_find_and_remove_shared_resources from /local/swtc/lib/locallib to here; changed
 *                      name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses;
 *                      added swtc_get_excludecourses.
 * 11/15/19 - In swtc_find_and_remove_excludecourses, added call to swtc_user_access_category
 *                      to remove any enrolled courses that have been archived.
 * 03/02/20 - In swtc_find_and_remove_excludecourses, added call to core_course_category::make_categories_list with the
 *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
 * 03/04/20 - Design change: will allow student access to an archived course (in Lenovo Internal Portfolio) only if they are enrolled in it;
 *                  still must not be able to browse, search, or direct link to any other courses in Lenovo Internal Portfolio.
 * PTR2020Q108 - @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
 *                      (changed core_course_category to \core_course_category).
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');


/**
 * Look for any of the Lenovo shared resources (defined in /lib/swtc_resources.php) and any courses that have been moved to archive
 *          in the array passed. Returns "true" if any of the courses are found; "false" if none of the courses are found.
 *
 *      Note: In /lib/enrollib.php enrol_get_my_courses, they are refered to as excludecourses.
 *
 * @param array The list of the courses to search. Note: if an object is passed in, it will be converted to a array.
 * @param int       The total number of courses
 *
 * @return array    Array of courses with the Lenovo shared resources removed.
 *
 * History:
 *
 * 10/21/19 - Original version; subtle technical issue with using array_search and array_column (returning the incorrect array key).
 * 11/15/19 - In swtc_find_and_remove_excludecourses, added call to swtc_user_access_category
 *                      to remove any enrolled courses that have been archived.
 * 03/02/20 - In swtc_find_and_remove_excludecourses, added call to core_course_category::make_categories_list with the
 *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
 * 03/04/20 - Design change: will allow student access to an archived course (in Lenovo Internal Portfolio) only if they are enrolled in it;
 *                  still must not be able to browse, search, or direct link to any other courses in Lenovo Internal Portfolio.
 * @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
 *                      (changed core_course_category to \core_course_category).
 *
 */
function swtc_find_and_remove_excludecourses($courses) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $swtc_resources = $SESSION->EBGLMS->RESOURCES;
    $newcourses = array();              // To hold all the courses to send back.

    $capability = $swtc_user->capabilities[0];
    $access_selfsupport_stud = $SESSION->EBGLMS->STRINGS->selfsupport->access_selfsupport_stud;
    // Lenovo ********************************************************************************.

    // Convert objects to arrays.
    if (is_object($courses)) {
        $courses = (array)$courses;
    }

    // Lenovo ********************************************************************************
    // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
    //                  of access (before moving to core_course_category::can_view_category).
    //  @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
    //                      (changed core_course_category to \core_course_category).
    // Lenovo ********************************************************************************
    $categories = \core_course_category::make_categories_list($capability);
    // print_object($categories);     // 03/02/20 - Lenovo debugging...

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_enrollib.php. ===swtc_find_and_removed_excludecourses.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "About to print courses.";
        $messages[] = print_r((array)$courses, true);
        $messages[] = "Finished printing courses. About to print categories.";
        $messages[] = print_r($categories, true);
        $messages[] = "Finished printing categories.";
        // print_object($courses);
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Loop through all the courses.
    foreach ($courses as $course) {
        // Lenovo ********************************************************************************.
        // Search for 'LenInternalSharedResources' in courses.
        // 10/11/19 - Subtle technical issue with using array_search and array_column (returning the incorrect array key).
        // Lenovo ********************************************************************************.
        if ($course->id === $swtc_resources->sharedres_courseid) {
            if (isset($debug)) {
                debug_logmessage("Found courseid $swtc_resources->sharedres_courseid ($swtc_resources->sharedres_coursename) in courses. Removing.", 'both');
            }
        }

        // Lenovo ********************************************************************************.
        // Looking for shared simulator course in courses. If found, remove it.
        //		Note: Course short name for course is "lensharedsimulators_shortname".
        // 10/11/19 - subtle technical issue with using array_search and array_column (returning the incorrect array key).
        // Lenovo ********************************************************************************
        else if ($course->id === $swtc_resources->lensharedsimulators_courseid) {
            if (isset($debug)) {
                debug_logmessage("Found courseid $swtc_resources->lensharedsimulators_courseid ($swtc_resources->lensharedsimulators_shortname) in courses. Removing.", 'logfile');
            }
        // Lenovo ********************************************************************************.
        // 11/15/19 - Added call to swtc_user_access_category to remove any enrolled courses that have been archived.
        // Lenovo ********************************************************************************.
        // } else if (!swtc_user_access_category($categories, $course->category)) {       // 03/02/20
        } else if ((!in_array($course->category, array_keys($categories))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
            // If the user does NOT have access to the category, it will not be listed.
        } else {
            // Create indexed array (indexed by course id).
            $newcourses[$course->id] = $course;
        }
    }

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving swtc_lib_enrollib.php. ===swtc_find_and_removed_excludecourses.exit.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "newcourses array follows :";
        $messages[] = print_r($newcourses, true);
        $messages[] = "newcourses array ends.";
        // print_object($newcourses);
        // var_dump("old count was " . count($courses));
        // var_dump("new count is " . count($newcourses));
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Remember to return newcourses.
    // print_object($newcourses);      // 11/15/19 - Lenovo debugging...
    return $newcourses;
}

/**
 * Return an array of all the courseid's for the Lenovo shared resources (defined in /lib/swtc_resources.php) in the array passed.
 *
 *      Note: In /lib/enrollib.php enrol_get_my_courses, they are refered to as excludecourses.
 *
 * @param array     Current list of exclude courses (almost certain it will be empty).
 *
 * @return array    Array of the Lenovo shared resources courseid's.
 *
 * History:
 *
 * 11/03/19 - Initial writing.
 *
 */
function swtc_get_excludecourses($excludecourses) {
    global $CFG, $DB, $USER, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $swtc_resources = $SESSION->EBGLMS->RESOURCES;

    // $phplog = debug_enable_phplog($debug);      // 04/23/20 - Lenovo debugging...
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering swtc_lib_enrollib.php. ===swtc_get_excludecourses.enter.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************.
    // Load the courseid for 'LenInternalSharedResources'.
    // Load the courseid for the shared simulator course.
    // Lenovo ********************************************************************************.
    $excludecourses[] = $swtc_resources->sharedres_courseid;
    $excludecourses[] = $swtc_resources->lensharedsimulators_courseid;

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving swtc_lib_enrollib.php. ===swtc_get_excludecourses.exit.";
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "excludecourses array follows :";
        $messages[] = print_r($excludecourses, true);
        $messages[] = "excludecourses array ends.";
        // $messages[] = "phplog follows :";
        //  $messages[] = print_r($phplog, true);
        // $messages[] = "phplog ends.";
        // print_object($excludecourses);
        // var_dump("old count was " . count($courses));
        // var_dump("new count is " . count($newcourses));
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Remember to return excludecourses.
    return $excludecourses;
}
