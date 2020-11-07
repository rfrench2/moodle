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
 * Event observers used by the ebglmsevent course format.
 *
 * @since     Moodle 2.0
 * @package   format_ebglmsevent
 * @copyright 2018 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * PTR2020Q108 - 04/28/20 - Added ebglmsevent course format.
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for format_ebglmsevent.
 *
 * @package format_ebglmsevent
 * @copyright 2020 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_ebglmsevent_observer {

    /**
     * Course updated
     *
     * @param \core\event\course_updated $event the event
     * @return void
     *
     * History:
     *
     * PTR2020Q108 - 04/28/20 - Added ebglmsevent course format.
     *
     */
    public static function course_updated (\core\event\course_updated $event) {
        if (class_exists('format_ebglmsevent', false)) {
            // If class format_ebglmsevent was never loaded, this is definitely not a course in 'ebglmsevent' format.
            // Course may still be in another format but format_ebglmsevent::course_updated() will check it.
            // format_ebglmsevent::course_updated($event);
            // $course = $event->get_record_snapshot('course', $event->objectid);
            // print_object($course);
            // $format = course_get_format($course);
            // print_object($format);
            
            // ebglms_course_created_or_updated($event);
        }
    }
    
    /**
     * Course created
     *
     * @param \core\event\course_created $event the event
     * @return void
     *
     * History:
     *
     * PTR2020Q108 - 04/28/20 - Added ebglmsevent course format.
     *
     */
    public static function course_created(\core\event\course_created $event) {
        if (class_exists('format_ebglmsevent', false)) {
            // If class format_ebglmsevent was never loaded, this is definitely not a course in 'ebglmsevent' format.
            // Course may still be in another format but format_ebglmsevent::course_updated() will check it.
            
            // ebglms_course_created_or_updated($event);
        }
    }
}
