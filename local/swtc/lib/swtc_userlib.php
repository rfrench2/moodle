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
 **/

// namespace local_swtc;        // 10/21/20

defined('MOODLE_INTERNAL') || die();

use local_swtc\SwtcUser;
use local_swtc\SwtcDebug;

// use stdClass;  // 10/21/20
// use core_date;
// use core_user;
// use DateTime;

// require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');

/**
 * Set a reference to SESSION->SWTC->USER.
 *
 * @param $user
 *
 * @return None
 *
 *
 * History:
 *
 * 03/03/21 - Initial writing.
 *
 **/
function swtc_set_user($user, $relateduserid = null) {
    global $SESSION;

    // SWTC *****************************************************************************
    // Setup the SWTC variable.
    //      Example: /lib/classes/session/manager.php starting around line 86.
    // SWTC *****************************************************************************
    $SESSION->SWTC = new stdClass();

    // SWTC *****************************************************************************
    // Setup the SWTC->USER variable.
    // SWTC *****************************************************************************
    // SWTC *****************************************************************************
    // Setup the SWTC->USER variable.
    // SWTC *****************************************************************************
    $SESSION->SWTC->USER = new SwtcUser();

    // SWTC ********************************************************************************
	// Set the additional SwtcUser properties.
	// SWTC ********************************************************************************
    $SESSION->SWTC->USER->set_userid($user);
    $SESSION->SWTC->USER->set_username($user);

    // SWTC ********************************************************************************
	// Load the user's profile data.
	// SWTC ********************************************************************************
	$temp = new stdClass();
	$temp->id = $user->id;
	profile_load_data($temp);
	// print_object("In SwtcUser __construct; about to print profile data');		// 10/16/20 - SWTC
	// print_object($temp);		// 10/16/20 - SWTC
	// $this->user_access_type = $temp->get_string('profile_field_accesstype', 'local_swtc');
	$SESSION->SWTC->USER->set_user_access_type($temp->profile_field_accesstype);

    // Note: $this->portfolio is set in class definition.
    $SESSION->SWTC->USER->set_timestamp();
    $SESSION->SWTC->USER->set_timezone();
	// $this->user_access_type2 = (null !== $temp->get_string('profile_field_accesstype2', 'local_swtc')) ? $temp->get_string('profile_field_accesstype2', 'local_swtc') : null;
	$SESSION->SWTC->USER->set_user_access_type2($temp->profile_field_accesstype2);

    // SWTC ********************************************************************************
    // Copy this object to $SESSION->SWTC->USER.
    // SWTC ********************************************************************************
    // $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
    // $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
    // print_object("In not set SWTC->USER; about to print this");		// 10/16/20 - SWTC
    // print_object($this);		// 10/16/20 - SWTC

    print_object("In swtc_set_user; about to print SESSION->SWTC->USER");
    print_object($SESSION->SWTC->USER);
    print_object("In swtc_set_user; about to print backtrace");
    print_r("Current function : " . debug_backtrace()[0]['function'] . ".<br />");
    print_r("Calling function : " . debug_backtrace()[1]['function'] . ".<br />");
    // debug_print_backtrace();
    return $SESSION->SWTC->USER;


    // $swtc_user = new SwtcUser($user);       // 10/24/20
    // $swtc_user = SwtcUser($user);      // 10/24/20
    // print_object("In swtc_get_user; about to print backtrace");
    // print_object(format_backtrace(debug_backtrace(), true));
    // print_object("In swtc_get_user; about to print swtc_user");		// 10/16/20 - SWTC
    // print_object($swtc_user);		// 10/16/20 - SWTC

}

/**
* Get user instance (returns $SESSION->SWTC->USER) if set.
*      If not set, call swtc_set_user.
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
function swtc_get_user($user = null) {
    global $SESSION, $USER;

    // SWTC ********************************************************************************
    // If $SESSION is not set, continue.
    // SWTC ********************************************************************************
    if (is_object($SESSION)) {
        // SWTC ********************************************************************************
        // If $SWTC->USER is not set, continue.
        // SWTC ********************************************************************************
        // print_object("In swtc_userlib->swtc_get_user; did I get here 1; about to print SESSION");		// 10/16/20 - SWTC
        // print_object($SESSION);		// 10/16/20 - SWTC
        if (!isset($SESSION->SWTC)) {
            // SWTC ********************************************************************************
            // If $user is not set, set it to $USER.
            // SWTC ********************************************************************************
            $user = (isset($user)) ? $user : clone $USER;

            // SWTC *****************************************************************************
            // Setup the SWTC->USER variable.
            // SWTC *****************************************************************************
            print_object("In swtc_get_user; about to call swtc_set_user");
            $SESSION->SWTC->USER = swtc_set_user($user);
        }

        // SWTC ********************************************************************************
        // Copy this object to $SESSION->SWTC->USER.
        // SWTC ********************************************************************************
        // $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
        // $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
        // print_object("In not set SWTC->USER; about to print this");		// 10/16/20 - SWTC
        // print_object($this);		// 10/16/20 - SWTC

        print_object("In swtc_get_user; about to print SESSION->SWTC->USER");
        print_object($SESSION->SWTC->USER);
        print_object("In swtc_get_user; about to print backtrace");
        print_r("Current function : " . debug_backtrace()[0]['function'] . ".<br />");
        print_r("Calling function : " . debug_backtrace()[1]['function'] . ".<br />");

        return $SESSION->SWTC->USER;
    }

    // $swtc_user = new SwtcUser($user);       // 10/24/20
    // $swtc_user = SwtcUser($user);      // 10/24/20
    // print_object("In swtc_get_user; about to print backtrace");
    // print_object(format_backtrace(debug_backtrace(), true));
    // print_object("In swtc_get_user; about to print swtc_user");		// 10/16/20 - SWTC
    // print_object($swtc_user);		// 10/16/20 - SWTC

}

/**
 * Set debug instance (returns $debug).
 *
 * History:
 *
 * 03/03/21 - Initial writing.
 *
 */
function swtc_set_debug() {
    global $SESSION;

    // SWTC ********************************************************************************
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    //      (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    //      To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************
    // SWTC *****************************************************************************
    // Setup the SWTC->DEBUG variable.
    // SWTC *****************************************************************************
    $SESSION->SWTC->DEBUG = new SwtcDebug();

    // SWTC ********************************************************************************
    // Set the fully qualified log file names.
    // SWTC ********************************************************************************
    $SESSION->SWTC->DEBUG->set_fqlog();
    $SESSION->SWTC->DEBUG->set_fqdetailed();

    $SESSION->SWTC->DEBUG->set_username();

    // SWTC ********************************************************************************
    // Always output standard header information.
    // SWTC ********************************************************************************
    $SESSION->SWTC->DEBUG->logmessage_header('begin');

    print_object("In swtc_set_debug; about to print SESSION->SWTC->DEBUG");
    print_object($SESSION->SWTC->DEBUG);
    print_object("In swtc_set_debug; about to print backtrace");
    print_r("Current function : " . debug_backtrace()[0]['function'] . ".<br />");
    print_r("Calling function : " . debug_backtrace()[1]['function'] . ".<br />");

    return $SESSION->SWTC->DEBUG;

}

/**
 * Get debug instance (returns $debug) if set.
 *      If not set, call swtc_set_debug.
 *
 * History:
 *
 * 03/03/21 - Initial writing.
 *
 */
function swtc_get_debug() {
    global $SESSION;

    // SWTC ********************************************************************************
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    //      (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    //      To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************
    // SWTC ********************************************************************************
    // If $SESSION is not set, continue.
    // SWTC ********************************************************************************
    if (is_object($SESSION)) {
        if (get_config('local_swtc', 'swtcdebug')) {
            // SWTC ********************************************************************************
            // If $SWTC->USER is not set, continue.
            // SWTC ********************************************************************************
            print_object("In swtc_userlib->swtc_get_debug; did I get here 1; about to print SESSION");		// 10/16/20 - SWTC
            // print_object($SESSION);		// 10/16/20 - SWTC
            if (!isset($SESSION->SWTC->DEBUG)) {
                // print_object("In swtc_get_user; did I get here 2");		// 10/16/20 - SWTC
                // SWTC *****************************************************************************
                // Setup the SWTC variable.
                //      Example: /lib/classes/session/manager.php starting around line 86.
                // SWTC *****************************************************************************
                // $debug = new stdClass();

                // SWTC *****************************************************************************
                // Setup the SWTC->DEBUG variable.
                // SWTC *****************************************************************************
                print_object("In swtc_get_debug; about to call swtc_set_debug");
                $SESSION->SWTC->DEBUG = swtc_set_debug();
            }

            print_object("In swtc_get_debug; about to print SESSION->SWTC->DEBUG");
            print_object($SESSION->SWTC->DEBUG);
            print_object("In swtc_get_debug; about to print backtrace");
            print_r("Current function : " . debug_backtrace()[0]['function'] . ".<br />");
            print_r("Calling function : " . debug_backtrace()[1]['function'] . ".<br />");
        } else {
            $SESSION->SWTC->DEBUG = null;
        }

        return $SESSION->SWTC->DEBUG;
    }
}

/**
 * Setup most, but not all, the characteristics of  SESSION->SWTC->USER->relateduser.
 *
 * @param N/A
 *
 * @return None
 *
 * History:
 *
 * 10/24/20 - Initial writing.
 * 02/23/21 - TO TO: function also in swtc_userlib.php.
 *
 **/
function swtc_user_get_relateduser($userid) {
    global $USER;

    // SWTC ********************************************************************************
    // SWTC SWTC swtc_user and debug variables.
    $swtc_user = swtc_get_user($USER);
    // $debug = swtc_get_debug();       // 10/17/20

    $relateduser = new stdClass();     // Local temporary relateduserid variables.
    // SWTC ********************************************************************************

	// SWTC ********************************************************************************
	// Set some of the SWTC->relateduser variables that will be used IF a relateduserid is found.
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
    $relateduser->portfolio = $swtc_user->get_portfolio();

    // @01 - 03/01/20 - Added user timezone to improve performance.
    $relateduser->timestamp = $swtc_user->set_timestamp;

	// Important! roleshortname and roleid are what the roles SHOULD be, not necessarily what the roles are.
	$relateduser->roleshortname = null;
	$relateduser->roleid = null;

	$relateduser->categoryids = array();

	// print_object($relateduser);

	// Last step. Note that this sets $SESSION->SWTC->USER->relateduser.
	// $swtc_user->relateduser = $relateduser;		// 01/10/19

	// print_object($relateduser);
	return $relateduser;		// 01/10/19

}
