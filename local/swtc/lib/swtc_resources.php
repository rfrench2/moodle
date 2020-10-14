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
 * @subpackage swtc/lib/swtc_resources.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	04/17/18 - Initial writing; loads all customized EBGLMS resource information.
 * 03/31/19 - Removed all customized code referencing "PremSuppTempCourse".
 * 10/08/19 - Added information for shared simulator course (lensharedsimulators_shortname).
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// 04/14/18: $SESSION is required here.
// Lenovo ********************************************************************************
global $DB, $SESSION;


/**
 * Initializes all customized EBGLMS user information and loads it into $SESSION->EBGLMS->USER.
 *
 *      IMPORTANT! $SESSION->EBGLMS MUST be set before calling (i.e. no check for EBGLMS).
 *
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/17/18 - Initial writing.
 * 06/11/18 - Added check for 'PremSuppTempCourse' (just like leninternalsharedresources).
 * 03/31/19 - Removed all customized code referencing "PremSuppTempCourse".
 * 10/08/19 - Added information for shared simulator course (lensharedsimulators_shortname).
 *
 **/

// Lenovo ********************************************************************************
// Setup temporary reference to $EBGLMS->RESOURCES.
//      To use: $tmp = $SESSION->EBGLMS->RESOURCES
// Lenovo ********************************************************************************
$tmp = $SESSION->EBGLMS->RESOURCES;

// Lenovo ********************************************************************************
// 'LenInternalSharedResources' course name
//          Note: Course short name for course is "LenInternalSharedResources".
// Lenovo ********************************************************************************
// The string 'LenInternalSharedResources' - shortname of course.
$tmp->sharedres_shortname = get_string('leninternalsharedresources', 'local_swtc');

// The string 'Shared Resources (Master)'.
$tmp->sharedres_coursename = get_string('sharedresources_coursename', 'local_swtc');

// Find course information of 'LenInternalSharedResources' course.
// $course = $DB->get_record('course', array('shortname' => $tmp->sharedres_shortname));
// $tmp->sharedres_courseid = $course->id;
$tmp->sharedres_courseid = $DB->get_field('course', 'id', array('shortname' => $tmp->sharedres_shortname));


// Lenovo ********************************************************************************
// 'PremSuppTempCourse' course name
//          Note: Course short name for course is "PremSuppTempCourse".
// Lenovo ********************************************************************************
// The string 'PremSuppTempCourse' - shortname of course.
// $tmp->premiersupptempcourse_shortname = get_string('premsupptempcourse', 'local_swtc');

// The string 'PremierSupport Portfolio Template 1.0 (Master)'.
// $tmp->premiersupptempcourse_coursename = get_string('premsupptempcourse_coursename', 'local_swtc');

// Find course information of 'PremSuppTempCourse' course.
// $course = $DB->get_record('course', array('shortname' => $tmp->sharedres_shortname));
// $tmp->sharedres_courseid = $course->id;
// $tmp->premiersupptempcourse_courseid = $DB->get_field('course', 'id', array('shortname' => $tmp->premiersupptempcourse_shortname));

// Lenovo ********************************************************************************.
// Shared simulator course name
//          Note: Course short name for course is "ES10000".
// Lenovo ********************************************************************************.
$tmp->lensharedsimulators_shortname = get_string('lensharedsimulators_shortname', 'local_swtc');
$tmp->lensharedsimulators_courseid = $DB->get_field('course', 'id', array('shortname' => $tmp->lensharedsimulators_shortname));
