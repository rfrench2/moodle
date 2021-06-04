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
 * Functions used by related courses.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/24/21 - Initial writing.
 *
 */

use \local_swtc\curriculums\curriculums;

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

/**
 * Saves (puts) all the related courses for a particular course (stored in table local_swtc_rc).
 *
 * @param $integer   Parent courseid.
 * @param $array   Array of courses.
 *
 * @return $bool   Success.
 *
 * History:
 *
 * 05/24/21 - Initial writing.
 *
 **/
function relatedcourses_put_courses($parentcourseid, $relatedcourses) {
    global $DB, $USER;

    // SWTC ********************************************************************************.
    // SWTC LMS swtcuser and debug variables.
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $params = array();
    // SWTC ********************************************************************************.

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/relatedcourseslib.php ===relatedcourses_put_courses.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // Clean input.
    $relatedcourses = clean_param_array($relatedcourses, PARAM_NOTAGS);

    // SWTC ********************************************************************************.
    // For the parent course, get all the currently active related courses.
    // SWTC ********************************************************************************.
    $params = array($DB->sql_compare_text('parentcourseid') => $parentcourseid, 'active' => COURSE_ACTIVE);
    $currently_active = $DB->get_records('local_swtc_rc', $params);
    // print_object($relatedcourses);
    // print_object($currently_active);

    // SWTC ********************************************************************************.
    // Loop through each of the currently active related courses and:
    // If currently active (CA) course is NOT in new active (NA) courses:
    // - Set CA course "active" to 0 (inactive).
    // - Update CA course modified date / time.
    // - Update CA course modified userid.
    // If CA course is IN NA courses:
    // - Update CA course modified date / time.
    // - Update CA course modified userid.
    // SWTC ********************************************************************************.
    foreach ($currently_active as $current) {
        // print_object($current);
        if (in_array($current->relatedcourseid, $relatedcourses)) {
            // Course exists. Will update timemodified and usermodified later.
            // print_r("course exists.\n");
        } else {
            // Course does NOT exist. Update "active" and other fields.
            // print_r("course does NOT exist. setting $current->courseid to inactive.\n");
            // Update the timemodified, get the USER->id of the user, and set "active" to inactive.
            $params['id'] = $current->id;
            $params['active'] = COURSE_INACTIVE;
            $params['timemodified'] = time();
            $params['usermodified'] = $USER->id;

            // Update the record.
            $DB->update_record('local_swtc_rc', $params);
        }
    }

    foreach ($relatedcourses as $courseid) {
        // SWTC ********************************************************************************.
        // Update the record if it exists.
        // SWTC ********************************************************************************.
        $params = array($DB->sql_compare_text('parentcourseid') => $parentcourseid, 'relatedcourseid' => $courseid, 'active' => COURSE_ACTIVE);
        if ($DB->record_exists('local_swtc_rc', $params)) {
            $record = $DB->get_record('local_swtc_rc', $params);

            // Only update the timemodified, and therefore the courses, if we have changed the course to be saved.
            // Update the timemodified, get the USER->id of the user, and set "active" to active.
            $params['id'] = $record->id;
            $params['active'] = COURSE_ACTIVE;
            $params['timemodified'] = time();
            $params['usermodified'] = $USER->id;

            // Add the record.
            $DB->update_record('local_swtc_rc', $params);
        } else {
            // SWTC ********************************************************************************.
            // Create the record if it doesn't exist yet.
            // SWTC ********************************************************************************.
            // Update the timecreated, get the USER->id of the user, and set "active" to active.
            $params['active'] = COURSE_ACTIVE;
            $params['usercreated'] = $USER->id;
            $params['timecreated'] = time();
            $params['usermodified'] = 0;
            $params['timemodified'] = 0;
            $params['parentcourseid'] = $parentcourseid;
            $params['relatedcourseid'] = $courseid;
            $params['clicks'] = 0;
            $params['enrollments'] = 0;
            // print_object("about to print params");
            // print_object($params);

            $DB->insert_record('local_swtc_rc', $params, true);
        }
    }

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/relatedcourseslib.php ===relatedcourses_put_courses.exit===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return true;

}

/**
 * Gets all the related courses for a particular course. The logic flow for determining related courses is:
 *      if (the course belongs to any curriculum(s) and the course is NOT a PA) then
 *          list all the other courses in the curriculum as related courses
 *      else if (the course is a PA) then
 *          list all the other PA courses in the category as related courses
 *      else ***The course is not in any curriculum and is NOT a PA
 *          list the Service Provider Curriculum Base courses as related courses
 *
 * @param $integer  The parent courseid.
 *
 * @return $array   Array of related courses or empty array if none.
 *
 * History:
 *
 * 05/24/21 - Initial writing.
 *
 **/
function relatedcourses_get_courses($courseid) {
    global $DB, $COURSE;

    // SWTC ********************************************************************************.
    // SWTC LMS swtcuser and debug variables.
    $debug = swtc_set_debug();

    // Other SWTC variables.
    $relatedcourses = array();
    $params = array();
    $pastring = 'PA';
    $curriculums = new curriculums;
    // SWTC ********************************************************************************.

    // Get all the entire course record.
    $params = array('id' => $courseid);
    $record = $DB->get_record('course', $params, '*', MUST_EXIST);

    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/relatedcourseslib.php ===relatedcourses_get_courses.enter===";
        // $messages[] = "About to print record.";
        // $messages[] = print_r($record, true);
        // $messages[] = "Finished printing record.";
        // print_object($record);
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Determine if the course is a PA.
    // SWTC ********************************************************************************.
    if (substr_compare($record->shortname, $pastring, 0, strlen($pastring)) == 0) {
        $patype = true;
    } else {
        $patype = null;
    }

    // SWTC ********************************************************************************.
    // If the course is NOT a PA, see if the course is part of any curriculum. If so, load all the courses.
    // SWTC ********************************************************************************.
    if (!isset($patype)) {
        $coursepartofcurriculum = $curriculums->find_course($courseid);

        // If this course it is part of ANY curriculum, get a list of all those courses.
        if (isset($coursepartofcurriculum)) {
            $currs = explode(', ', $coursepartofcurriculum->curriculums);

            foreach ($currs as $curriculum) {
                // print_object($curriculum);
                // Get all the courses in the curriculum.
                $courses = $curriculums->list_courses($curriculum);
                // print_object($courses);
            }
        }

        // SWTC ********************************************************************************.
        // If the course is a PA, list all the courses in the same category as related courses.
        // SWTC ********************************************************************************.
    } else if ($patype) {

        // Save the category (path) to the course.
        $category = $record->category;

        // See if they're any other courses in the category (we know there is at least one...).
        $courses = $DB->get_records('course', array('category' => $category), 'sortorder ASC');
    }

    // SWTC ********************************************************************************.
    // If we found some courses above, add them to relatedcourses.
    // SWTC ********************************************************************************.
    // print_object($courses);
    if (!empty($courses)) {
        foreach ($courses as $course) {
            if ($course->id !== $COURSE->id) {
                $relatedcourses[$course->id] = $course->id;
            }
        }
    }

    // SWTC ********************************************************************************.
    // If relatedcourses is still empty at this point, load all the courses in the Service Provider Base curricum (as a default
    // list of courses).
    // SWTC ********************************************************************************.
    if (empty($relatedcourses)) {
        // Get all the entire course record.
        $params = array('shortname' => 'SPC0010');
        $rec = $DB->get_record('course', $params, '*', MUST_EXIST);

        $courses = $curriculums->list_courses($rec->id);

        foreach($courses as $course) {
            if ($course->id !== $COURSE->id) {
                $relatedcourses[$course->id] = $course->id;
            }
        }
    }

    if (isset($debug)) {
        $messages[] = "About to print relatedcourses.===";
        $messages[] = print_r($relatedcourses, true);
         // print_object($relatedcourses);
        $messages[] = "Finished printing relatedcourses.===";
        $messages[] = "In /local/swtc/lib/relatedcourseslib.php ===relatedcourses_get_courses.exit===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    return $relatedcourses;

}
