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
 * @subpackage swtc/lib/swtc_roleids.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	10/24/19 - Initial writing; loads all customized EBGLMS role id information.
 *
 **/

defined('MOODLE_INTERNAL') || die();

global $SESSION;


/**
 * Initializes all customized EBGLMS role id information and loads it into $SESSION->EBGLMS->ROLEIDS.
 *
 *      IMPORTANT! $SESSION->EBGLMS MUST be set before calling (i.e. no check for EBGLMS).
 *
 * @param N/A
 *
 * @return N/A
 */
 /**
 * Version details
 *
 * History:
 *
 * 10/24/19 - Initial writing; important that swtc_strings.php is called before this.
 *
 **/

 // Lenovo ********************************************************************************
// Setup temporary reference to $EBGLMS->ROLEIDS.
//      To use: $tmp = $SESSION->EBGLMS->ROLEIDS
// Lenovo ********************************************************************************
$tmp = $SESSION->EBGLMS->ROLEIDS;
$role_strings = $SESSION->EBGLMS->STRINGS->role_shortnames;

// Lenovo ********************************************************************************
// Loop through each of the role_shortnames and find its role id.
// Lenovo ********************************************************************************
foreach ($role_strings as $shortname) {
    $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
    $tmp->$shortname = $role->id;
}
