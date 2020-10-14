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

/*****************************************************************************
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/swtc.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 04/14/18 - Original version; used to load all variables (except DEBUG) into the EBGLMS $SESSION variable.
 * 04/16/18 - Added USER.
 * 04/24/18 - $USER->profile is not set yet. So moved setting of $SESSION->EBGLMS->USER->user_access_type
 *                  from here to notifications.php.
 * 10/28/18 - Added check to see if $SESSION is set (maybe the user's session has timed out; $SESSION will be null then).
 * 10/24/19 - Added swtc_roleids and call to /local/swtc/lib/swtc_roleids.php.
 * 11/05/19 - Fixed MAJOR omission by adding $CFG.
 *
 *****************************************************************************/
defined('MOODLE_INTERNAL') || die();


// Lenovo ********************************************************************************
// 11/05/19 - Fixed MAJOR omission by adding $CFG.
// Lenovo ********************************************************************************
global $CFG, $USER, $SESSION;


// Lenovo ********************************************************************************
//  Include links to all other global files here.
//
// Lenovo ********************************************************************************

// Lenovo ********************************************************************************
// If $EBGLMS is not set, continue.
//      To use: $SESSION->EBGLMS or $SESSION->{'EBGLMS'}->{'DEBUG'}
// Lenovo ********************************************************************************
if (is_object($SESSION)) {
    if (!isset($SESSION->EBGLMS)) {
        // Lenovo ********************************************************************************
        // Setup the EBGLMS variable.
        //      Example: /lib/classes/session/manager.php starting around line 86.
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS = new stdClass();

        // Lenovo ********************************************************************************
        // Setup the second-level $STRINGS global variable.
        //      To use: $SESSION->EBGLMS->STRINGS
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS->STRINGS = new stdClass();
        require_once($CFG->dirroot.'/local/swtc/lib/swtc_strings.php');

        // Lenovo ********************************************************************************
        // Setup the second-level $PORTFOLIOS global variable.
        //      To use: $SESSION->EBGLMS->PORTFOLIOS
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS->PORTFOLIOS = new stdClass();
        require_once($CFG->dirroot.'/local/swtc/lib/swtc_portfolios.php');

        // Lenovo ********************************************************************************
        // Setup the second-level $USER global variable.
        //      To use: $SESSION->EBGLMS->USER
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS->USER = new stdClass();
        require_once($CFG->dirroot.'/local/swtc/lib/swtc_user.php');

        // Lenovo ********************************************************************************
        // Setup the second-level $RESOURCES global variable.
        //      To use: $SESSION->EBGLMS->RESOURCES
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS->RESOURCES = new stdClass();
        require_once($CFG->dirroot.'/local/swtc/lib/swtc_resources.php');

        // Lenovo ********************************************************************************
        // Setup the second-level $ROLEIDS global variable.
        //      To use: $SESSION->EBGLMS->ROLEIDS
        // Lenovo ********************************************************************************
        $SESSION->EBGLMS->ROLEIDS = new stdClass();
        require_once($CFG->dirroot.'/local/swtc/lib/swtc_roleids.php');
    }
}
