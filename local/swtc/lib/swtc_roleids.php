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
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

global $DB, $SESSION;


/**
 * Initializes all customized EBGLMS role id information and loads it into $SESSION->SWTC->ROLEIDS.
 *
 *      IMPORTANT! $SESSION->SWTC MUST be set before calling (i.e. no check for EBGLMS).
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
 * 10/14/20 - Initial writing.
 *
 **/

// SWTC ********************************************************************************
// Setup temporary reference to $SWTC->ROLEIDS.
//      To use: $tmp = $SESSION->SWTC->ROLEIDS
// SWTC ********************************************************************************
print_object("In swtc_roleids; about to print backtrace.");
print_object(format_backtrace(debug_backtrace(), true));
print_object($SESSION);
$tmp = $SESSION->SWTC->ROLEIDS;

$role_strings = get_strings(array('role_lenovo_instructor', 'role_lenovo_student', 'role_lenovo_administrator', 'role_lenovo_siteadmin'), 'local_ebglms');

// SWTC ********************************************************************************
// Loop through each of the role_shortnames and find its role id.
// SWTC ********************************************************************************
foreach ($role_strings as $shortname) {
    $role = $DB->get_record('role', array('shortname' => $shortname), '*', MUST_EXIST);
    $tmp->$shortname = $role->id;
}
