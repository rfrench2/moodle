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
 * Lenovo customized code for Moodle core course. Remember to add the following at the top of any module that requires these functions:
 *      require_once($CFG->dirroot.'/local/swtc/lib/lenovo_course_functions.php');
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_course.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	10/14/19 - Initial writing; moved majority of customized code from /course/lib.php to functions defined here; added utility functions;
 *                      changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/14/19 - Added lenovo_update_course.
 * 10/15/19 - Added lenovo_view_course.
 * 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
 *	11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
 * 12/06/19 - Lenovo EBGLMS for Moodle 3.8+; added $updatedfields to lenovo_update_course.
 * 12/12/19 - In lenovo_update_course, changed $updatedfields to an optional field (because it is only in Moodle 3.8+); in lenovo_update_course
 *                      added check for Moodle version.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_lib_enrollib.php');

/**
 * If category was updated, add old and new category to event.
 *
 * Called from: update_course
 *  Location: /course/lib.php
 *  To call: $this-> this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 *
 * Lenovo history:
 *
 * 08/28/19 - Added this header; if category was updated, add old and new category to event.
 *	10/11/19 - Moved majority of Lenovo customized code from here to /local/swtc/lib/lenovo_course_functions.php.
 * 10/14/19 - Initial writing; changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug; added utility functions.
 * 12/06/19 - Lenovo EBGLMS for Moodle 3.8+; added $updatedfields to lenovo_update_course.
 * 12/12/19 - In lenovo_update_course, changed $updatedfields to an optional field (because it is only in Moodle 3.8+); in lenovo_update_course
 *                      added check for Moodle version.
 *
 */
function lenovo_update_course($change, $course, $data, $oldcourse, $updatedfields = null) {
    global $USER, $CFG, $DB, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering lenovo_course===lenovo_update_course.enter===.";
        // $messages[] = "About to print course.";
        // $messages[] = print_r($course, true);
        // $messages[] = "Finished printing course.";
        // $messages[] = "About to print data.";
        // $messages[] = print_r($data, true);
        // $messages[] = "Finished printing data.";
        // $messages[] = "About to print oldcourse.";
        // $messages[] = print_r($oldcourse, true);
        // $messages[] = "Finished printing oldcourse.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************.
    // 08/28/19 - Added this header; if category was updated, add old and new category to event.
    // Lenovo ********************************************************************************.
    if ($change) {
        // Lenovo ********************************************************************************.
        // 12/12/19 - Added check for Moodle version since $updatedfieldsis only in Moodle 3.8+.
        //      Note: The version for Moodle 3.8+ (Build: 20191129) is 2019111800.
        // Lenovo ********************************************************************************.
        if ($CFG->version < 2019111800) {
            $other = array('shortname' => $course->shortname,
                                'fullname' => $course->fullname,
                                'oldcategory' => $oldcourse->category,
                                'newcategory' => $data->category);
        } else {
            $other = array('shortname' => $course->shortname,
                                'fullname' => $course->fullname,
                                'updatedfields' => $updatedfields,
                                'oldcategory' => $oldcourse->category,
                                'newcategory' => $data->category);
        }
    } else {
        // Lenovo ********************************************************************************.
        // 12/12/19 - Added check for Moodle version since $updatedfieldsis only in Moodle 3.8+.
        //      Note: The version for Moodle 3.8+ (Build: 20191129) is 2019111800.
        // Lenovo ********************************************************************************.
        if ($CFG->version < 2019111800) {
            $other = array('shortname' => $course->shortname,
                                'fullname' => $course->fullname);
        } else {
            $other = array('shortname' => $course->shortname,
                                'fullname' => $course->fullname,
                                'updatedfields' => $updatedfields);
        }
    }

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving lenovo_course===lenovo_update_course.exit===.";
        $messages[] = "About to print other.";
        $messages[] = print_r($other, true);
        // print_object($other);
        $messages[] = "Finished printing other.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    return $other;

}

/**
 * Looks for, and if found, removes courses from the user's enrolled courses list.
 *
 * Called from: course_get_enrolled_courses_for_logged_in_user
 *  Location: /course/lib.php
 *  To call: $this-> this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 * @return int $count      Number of sorted courses with the courses removed.
 *
 * Lenovo history:
 *
 *	02/20/19 - Initial writing (old in-line code).
 * 03/31/19 - Removed all customized code referencing "PremSuppTempCourse".
 * 10/08/19 - Added code to remove shared simulator course (lensharedsimulators_shortname); added more comments to existing Lenovo code.
 *	10/11/19 - Moved majority of Lenovo customized code from here to /local/swtc/lib/lenovo_course_functions.php;
 *                      subtile technical issue with using array_search and array_column (returning the incorrect array key).
 * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/14/19 - Initial writing.
 * 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
 *	11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
 * 11/15/19 - In lenovo_view_course, added return of $swtc.
 *
 */
function lenovo_course_get_enrolled_courses_for_logged_in_user($fields, $sort, $querylimit, $includecourses, $offset, $hiddencourses) {
    global $USER, $CFG, $DB, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();

    // Other Lenovo variables.
    $swtc_resources = $SESSION->EBGLMS->RESOURCES;
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering lenovo_course_functions===lenovo_course_get_enrolled_courses_for_logged_in_user.enter===.";
        $messages[] = "About to print swtc_user.";
        $messages[] = print_r($swtc_user, true);
        $messages[] = "Finished printing swtc_user.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************.
    // Get all the courses the user is enrolled in.
    // Lenovo ********************************************************************************.
    $courses = enrol_get_my_courses($fields, $sort, $querylimit, $includecourses, false, $offset, $hiddencourses);

    // Lenovo ********************************************************************************.
    // 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
    // 11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
    // Lenovo ********************************************************************************.
    $courses = swtc_find_and_remove_excludecourses($courses);

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Leaving lenovo_course_functions===lenovo_course_get_enrolled_courses_for_logged_in_user.exit===.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // print_object($courses);      // 11/15/19 - Lenovo debugging...
    return $courses;

}

/**
 * If course is viewed, gather Lenovo information.
 *
 * Called from: view.php
 *  Location: /course
 *  To call: $this-> this_function_name
 *
 * @param array $courses      Sorted list of courses.
 * @param int $count      Number of sorted courses.
 *
 * @return array $courses     Sorted list of courses with the courses removed.
 * @return int $count      Number of sorted courses with the courses removed.
 *
 * Lenovo history:
 *
 * 08/14/10 - Added this header; added capturing of "click" parameters from swtc_relatedcourses_slider.
 * 08/23/19 - Added click parameter to distinguish between "related", "suggested", and any other "clicks".
 * 08/27/19 - Added swtc parameters to urlparameters.
 *	10/11/19 - Moved majority of Lenovo customized code from here to /local/swtc/lib/lenovo_course_functions.php.
 * 10/15/19 - Initial writing; changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug; added utility functions.
 * 11/15/19 - In lenovo_view_course, added return of $swtc.
 *
 */
function lenovo_view_course() {
    global $USER, $CFG, $DB, $SESSION;

    // Lenovo ********************************************************************************.
    // Lenovo EBGLMS swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    $debug = swtc_get_debug();
    // Lenovo ********************************************************************************.

    if (isset($debug)) {
        // Lenovo ********************************************************************************
        // Always output standard header information.
        // Lenovo ********************************************************************************
        $messages[] = "Lenovo ********************************************************************************.";
        $messages[] = "Entering lenovo_course_functions===lenovo_view_course.enter===.";
        // $messages[] = "About to print course.";
        // $messages[] = print_r($course, true);
        // $messages[] = "Finished printing course.";
        // $messages[] = "About to print data.";
        // $messages[] = print_r($data, true);
        // $messages[] = "Finished printing data.";
        // $messages[] = "About to print oldcourse.";
        // $messages[] = print_r($oldcourse, true);
        // $messages[] = "Finished printing oldcourse.";
        $messages[] = "Lenovo ********************************************************************************.";
        debug_logmessage($messages, 'both');
        unset($messages);
    }

    // Lenovo ********************************************************************************.
    // Get "click" data if available.
    // Lenovo ********************************************************************************.
    $swtc        = optional_param('swtc', '', PARAM_TEXT);

    // 08/14/19 Lenovo
    if (!empty($swtc)) {
        require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');

        // 08/14/19 - For debugging.
        // print_object($swtc);

        $data = json_decode(base64_decode($swtc));
        // 08/14/19 - For debugging.
        // print_object($data);

        // Lenovo ********************************************************************************.
        // Switch on the type of operation that is being performed (either 'click' or 'enroll').
        // Lenovo ********************************************************************************.
        switch ($data->action) {
            // Lenovo ********************************************************************************
            // Processing a 'click'.
            // Lenovo ********************************************************************************
            case 'click':
                local_swtc_capture_click($data);

                // Change 'action' from 'click' to 'enroll' and save it back to swtc.
                $data->action = 'enroll';
                $swtc = (array) $data;
                $swtc = base64_encode(json_encode((object)$swtc));

                break;

            // Lenovo ********************************************************************************
            // Processing an 'enrollment'.
            // Lenovo ********************************************************************************
            case 'enroll':
                local_swtc_capture_enrollment($data);

                break;

            // Lenovo ********************************************************************************
            // Event - all others
            // Lenovo ********************************************************************************
            default:
                break;
        }
    }

    return $swtc;

}
