<?php
// declare(strict_types=1); // For debugging.
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
 * @subpackage swtc/lib/swtc_userlib.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 10/21/20 - Initial writing.
 *
 *****************************************************************************/

// namespace local_swtc;        // 10/21/20

defined('MOODLE_INTERNAL') || die();

use local_swtc\swtc_user;
use local_swtc\swtc_debug;

// use \stdClass;  // 10/21/20

// require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');

/**
 * Get a reference to SESSION->SWTC->USER.
 *
 * @param $user
 *
 * @return None
 *
 *
 * History:
 *
 * 10/21/20 - Initial writing.
 *
 **/
function swtc_get_user($user, $relateduserid = null) {

    $swtc_user = new swtc_user($user);
    // print_object("In swtc_get_user; about to print backtrace");
    // print_object(format_backtrace(debug_backtrace(), true));
    // print_object("In swtc_get_user; about to print swtc_user");		// 10/16/20 - SWTC
    // print_object($swtc_user);		// 10/16/20 - SWTC

    return $swtc_user;

}

/**
 * SWTC LMS for Moodle 3.7+.  Set debug instance (returns $debug) if set. If not set, call swtc_debug.
 *
 * History:
 *
 * 10/21/20 - Initial writing.
 *
 */
function swtc_set_debug() {
    global $SESSION;

    $debug = null;

    // SWTC ********************************************************************************
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    //      (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    //      To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************
    if (get_config('local_swtc', 'swtcdebug')) {
        // SWTC ********************************************************************************
        // Get a reference to SESSION->SWTC->DEBUG.
        // SWTC ********************************************************************************
        $debug = new swtc_debug();
        // print_object("In swtc_set_debug; about to print debug");		// 10/16/20 - SWTC
        // print_object($debug);		// 10/16/20 - SWTC

        // SWTC ********************************************************************************
        // Always output standard header information.
        // SWTC ********************************************************************************
        $debug->logmessage_header('begin');

    } else {
        $debug = null;
    }

    return $debug;
}

/**
 * Setup most, but not all, the characteristics of  SESSION->SWTC->USER->relateduser.
 *
 * @param N/A
 *
 * @return None
 */
 /**
 * Version details
 *
 * History:
 *
 * 07/12/18 - Initial writing.
 * 11/30/18 - Changed swtc_get_relateduser to load the portfolio of the user instead of "PORTFOLIO_NONE".
 * 01/09/19 - Added correct return from swtc_get_relateduser.
 * 01/10/19 - Changed swtc_get_relateduser to NOT set $SESSION->SWTC->USER->relateduser; the calling function must do this.
 * 01/11/19 - Added additional comments, and some code formating, to swtc_get_relateduser.
 *	10/16/19 - Changed to new SWTC LMS classes and methods to load swtc_user and debug.
 * @01 - 03/01/20 - Added user timezone to improve performance.
 *
 **/
function swtc_user_get_relateduser($userid) {
    global $USER;

    // SWTC ********************************************************************************
    // SWTC EBGLMS swtc_user and debug variables.
    // $swtc_user = swtc_get_user($USER);       // 10/17/20
    // $debug = swtc_get_debug();       // 10/17/20

    $relateduser = new stdClass();     // Local temporary relateduserid variables.
    // SWTC ********************************************************************************

	// SWTC ********************************************************************************
	// Set some of the EBGLMS->relateduser variables that will be used IF a relateduserid is found.
	// SWTC ********************************************************************************
	// Get all the user information based on the userid passed in.
	// Note: '*' returns all fields (normally not needed).
	$relateduser = core_user::get_user($userid);
	profile_load_data($relateduser);

	// SWTC ********************************************************************************
	// Since we are using get_user and profile_load_data, there is no need to copy any other fields.
	// SWTC ********************************************************************************
	// $relateduser->username = $relateduser->username;

	// SWTC ********************************************************************************
	// The following fields MUST be added to $relateduser (as they normally do not exist).
	// SWTC ********************************************************************************
	$relateduser->userid = $userid;
	$relateduser->user_access_type = $relateduser->profile_field_accesstype;
	// $relateduser->portfolio = 'PORTFOLIO_NONE';      // 11/30/18 - RF - not sure if this is correct.
	// 01/17/19 - Since we are working with a related user, assigning the portfolio as the same as the administrator is not a good idea.
	$relateduser->portfolio = $swtc_user->portfolio;      // 11/30/18

    // @01 - 03/01/20 - Added user timezone to improve performance.
    list($relateduser->timestamp, $relateduser->timezone) = swtc_timestamp();

	// Important! roleshortname and roleid are what the roles SHOULD be, not necessarily what the roles are.
	$relateduser->roleshortname = null;
	$relateduser->roleid = null;

	$relateduser->categoryids = array();

	// Important! capabilities are what the capabilities SHOULD be, not necessarily what the capabilities are.
	$relateduser->capabilities = array();
	$relateduser->timestamp = swtc_timestamp();

	// print_object($relateduser);

	// Last step. Note that this sets $SESSION->SWTC->USER->relateduser.
	// $swtc_user->relateduser = $relateduser;		// 01/10/19

	// print_object($relateduser);
	return $relateduser;		// 01/10/19

}

/**
 * Get current date and time for timestamp. Returns value to set $SESSION->SWTC->USER->timestamp.
 *
 * History:
 *
 * 10/21/20 - Initial writing.
 *
 */
function swtc_timestamp() {
    global $USER;

    $swtc_user = swtc_get_user($USER);

    // SWTC ********************************************************************************
    // Make all the times these variables were set the same.
    // Make all the functions these variables were set the same.
    // SWTC ********************************************************************************
    $today = new DateTime("now", $swtc_user->get_timezone());
    $time = $today->format('H:i:s.u');

    return $time;

}
