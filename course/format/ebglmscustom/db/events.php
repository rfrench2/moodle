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
 * Format ebglmscustom event handler definition.
 *
 * @package format_ebglmscustom
 * @copyright  2018 Lenovo EBG Server Education
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *	
 *	History:
 *
 * 07/23/19 - Initial writing; adding \core\event\course_created event.
 *
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    // Lenovo ********************************************************************************
    // Capture the \core\event\course_updated message and invoke the function shown in callback.
    // Lenovo ********************************************************************************
    array(
        'eventname'   => '\core\event\course_updated',
        'callback'    => 'format_ebglmscustom_observer::course_updated',
    ),
    // Lenovo ********************************************************************************
    // Capture the \core\event\course_created message and invoke the function shown in callback.
    // Lenovo ********************************************************************************
    array(
        'eventname'   => '\core\event\course_created',
        'callback'    => 'format_ebglmscustom_observer::course_created',
    )
);
