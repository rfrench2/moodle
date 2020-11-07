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
 * @package    format
 * @package   format_ebglmscustom
 * @copyright 2016 Lenovo EBG LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  *
 * History:
 *
 *		03/14/16 - Incremented version so 'Lenovo Internal Portfolio' option can be used.
 * 	05/25/16 - Added strings for titles of Section 0 and Section 1 of courses.
 * 	08/17/16 - Fixed section0name not getting used (changed 'format_topics' to 'format_ebglmscustom').
 *		08/25/16 - Changed "Lenovo and IBM Portfolio" values to just "IBM Portfolio" so that values will be the same (i.e. will help in transition).
 *
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015112985;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2015111000;        // Requires this Moodle version.
$plugin->component = 'format_ebglmscustom';    // Full name of the plugin (used for diagnostics).
