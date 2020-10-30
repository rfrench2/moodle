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
 * Format swtccustom event handler definition.
 *
 * @package format_swtccustom
 * @copyright  2020 SWTC LMS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *	History:
 *
 * 10/23/10 - Initial writing.
 *
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    // SWTC ********************************************************************************
    // Capture the \core\event\course_updated message and invoke the function shown in callback.
    // SWTC ********************************************************************************
    array(
        'eventname'   => '\core\event\course_updated',
        'callback'    => 'format_swtccustom_observer::course_updated',
    ),
    // SWTC ********************************************************************************
    // Capture the \core\event\course_created message and invoke the function shown in callback.
    // SWTC ********************************************************************************
    array(
        'eventname'   => '\core\event\course_created',
        'callback'    => 'format_swtccustom_observer::course_created',
    )
);
