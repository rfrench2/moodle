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

/*****************************************************************************
 *
 * All functions associcated with $SESSION->SWTC->USER (otherwise known as $swtc_user).
 *
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
 * 10/14/20 - Initial writing.
 *
 *****************************************************************************/

defined('MOODLE_INTERNAL') || die();

use \stdClass;

// SWTC ********************************************************************************
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************
// 10/16/20 - SWTC
// require($CFG->dirroot.'/local/swtc/lib/swtc.php');

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
    list($relateduser->timestamp, $relateduser->timezone) = swtc_set_user_timestamp();

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
 * SWTC LMS for Moodle 3.7+.  Get debug instance (returns $debug) if set. If not set, call debug_start.
 *
 * History:
 *
 * 07/17/18 - Check for server name. If running on production, disable debugging.
 * 11/02/19 - In preparation for Moodle 3.7+, in swtc_get_debug, added code to check for Lenovo debug setting so that everyone can
 *                      call swtc_get_debug directly.
 * 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->SWTC)) to
 *                          if (isset($SESSION->SWTC->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * @01 - 03/01/20 - Moving swtc_get_debug function from debuglib.php to swtc_userlib.php to improve performance; added call to include
 *                   debuglib.php (so that all other modules do not have to).
 *
 */
function swtc_get_debug() {
    global $CFG, $SESSION;

    //****************************************************************************************
	// Local variables begin...
    $debug = null;
    $swtc_user = new stdClass();

    // SWTC ********************************************************************************
    // Access to the top-level $EBGLMS global variables (it should ALWAYS be available; set in /lib/swtc.php).
    //      To use: if (isset($SESSION->SWTC))
    // 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->SWTC)) to
    //                      if (isset($SESSION->SWTC->USER)) to hopefully handle PHP errors when debugging if the
    //                      user's session has expired.
    // SWTC ********************************************************************************
    if (isset($SESSION->SWTC->USER)) {
        require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');
        // Set all the EBGLMS variables that will be used.
        $swtc_user = $SESSION->SWTC->USER;
    } else {
        // TODO: Catastrophic error; what to do with $swtc_user?
    }
    // Local variables end...
	//****************************************************************************************

    // SWTC ********************************************************************************
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    //      (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    //      To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************
    if (get_config('local_swtc', 'swtcdebug')) {
        // SWTC ********************************************************************************
        // @01 - 03/01/20 - Added call to include debuglib.php (so that all other modules do not have to).
        // SWTC ********************************************************************************
        require_once($CFG->dirroot.'/local/swtc/lib/debuglib.php');

        // SWTC ********************************************************************************
        // Setup the second-level $DEBUG global variable.
        //      To use: $debug = $SESSION->SWTC->DEBUG;
        // SWTC ********************************************************************************
        if (!isset($SESSION->SWTC->DEBUG)) {
            // $backtrace = format_backtrace(debug_backtrace(), true);
            // print_r("swtc_get_debug: SESSION->SWTC->DEBUG ->NOT<- set. Called from ".debug_backtrace()[1]['function'].".<br />");
            // var_dump($backtrace);
            // die;
            $debug = debug_start();       // EBGLMS->DEBUG is not set yet.
        } else {
            // EBGLMS->DEBUG is set. Check if running on production.
            // $SESSION->SWTC->DEBUG->PHPLOG->backtrace = format_backtrace(debug_backtrace(), true);
            // print_r("swtc_get_debug: SESSION->SWTC->DEBUG =IS= set. Called from ".debug_backtrace()[1]['function'].".<br />");
            // var_dump($SESSION->SWTC->DEBUG);
            // die;
            $debug = $SESSION->SWTC->DEBUG;
        }
    } else {
        $debug = null;
    }

    return $debug;
}
