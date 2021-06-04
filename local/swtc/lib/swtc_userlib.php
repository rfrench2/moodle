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
 * @subpackage swtc/lib/swtc_userlib.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 10/21/20 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

use \local_swtc\swtc_user;
use \local_swtc\swtc_debug;

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
function swtc_set_user($userid, $relateduserid = null) {
    global $SESSION;

    // SWTC ********************************************************************************.
    // Setup the SWTC variable.
    // Example: /lib/classes/session/manager.php starting around line 86.
    // SWTC ********************************************************************************.
    $SESSION->SWTC = new stdClass();

    // SWTC ********************************************************************************.
    // Setup the SWTC->USER variable.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->USER = new swtc_user();

    // SWTC ********************************************************************************.
    // Set the additional swtc_user properties.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->USER->set_userid($userid);

    // SWTC ********************************************************************************.
    // Load the user's profile data.
    // SWTC ********************************************************************************.
    $temp = new stdClass();
    $temp->id = $userid;
    profile_load_data($temp);

    $SESSION->SWTC->USER->set_accesstype($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_accesstype2($temp->profile_field_accesstype2);

    // SWTC ********************************************************************************.
    // IMPORTANT! The following four methods must be called before set_roleid.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->USER->set_psuser($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_psmanagement($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_sduser($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_sdmanagement($temp->profile_field_accesstype);

    $SESSION->SWTC->USER->set_roleid($temp->profile_field_accesstype);

    $SESSION->SWTC->USER->set_timestamp();
    $SESSION->SWTC->USER->set_timezone();

    $SESSION->SWTC->USER->set_relateduser();
    $SESSION->SWTC->USER->set_cohortnames($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_groupsort($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_geoname($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_groupname($temp->profile_field_accesstype);
    $SESSION->SWTC->USER->set_groupnames(array());

    return $SESSION->SWTC->USER;

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
function swtc_get_user($args=array()) {
    global $SESSION, $USER;

    $userid = $args['userid'] ?? null;
    $username = $args['username'] ?? null;

    // SWTC ********************************************************************************.
    // If $SESSION is not set, continue.
    // SWTC ********************************************************************************.
    if (is_object($SESSION)) {
        // SWTC ********************************************************************************.
        // If $SWTC->USER is not set, continue.
        // SWTC ********************************************************************************.
        if (!isset($SESSION->SWTC)) {
            // SWTC ********************************************************************************.
            // If $userid is not set, set it to $USER->id.
            // SWTC ********************************************************************************.
            $userid = (isset($userid)) ? $userid : $USER->id;

            // SWTC ********************************************************************************.
            // Setup the SWTC->USER variable.
            // SWTC ********************************************************************************.
            $SESSION->SWTC->USER = swtc_set_user($userid);

            $SESSION->SWTC->USER->set_username($username);
        }

        return $SESSION->SWTC->USER;
    }
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
    global $SESSION, $USER;

    // SWTC ********************************************************************************.
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    // (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    // To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // Setup the SWTC->DEBUG variable.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->DEBUG = new swtc_debug();

    // SWTC ********************************************************************************.
    // Set the fully qualified log file names.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->DEBUG->set_fqlog($USER->id);
    $SESSION->SWTC->DEBUG->set_fqdetailed($USER->id);

    $SESSION->SWTC->DEBUG->set_username();

    // SWTC ********************************************************************************.
    // Always output standard header information.
    // SWTC ********************************************************************************.
    $SESSION->SWTC->DEBUG->logmessage_header('begin');

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

    // SWTC ********************************************************************************.
    // At this point, $DEBUG may or may not be set. We will use a simple local variable based on the setting 'swtcdebug'
    // (set in local/swtc/settings.php) to enable debugging from this point forward.
    //
    // Setup the second-level $DEBUG global variable only if $debug is available.
    // To use: $debug = $SESSION->SWTC->DEBUG;
    // SWTC ********************************************************************************.
    // SWTC ********************************************************************************.
    // If $SESSION is not set, continue.
    // SWTC ********************************************************************************.
    if (is_object($SESSION)) {
        if (get_config('local_swtc', 'swtcdebug')) {
            // SWTC ********************************************************************************.
            // If $SWTC->USER is not set, continue.
            // SWTC ********************************************************************************.
            if (!isset($SESSION->SWTC->DEBUG)) {
                // SWTC ********************************************************************************.
                // Setup the SWTC->DEBUG variable.
                // SWTC ********************************************************************************.
                $SESSION->SWTC->DEBUG = swtc_set_debug();
            }
        } else {
            $SESSION->SWTC->DEBUG = null;
        }

        return $SESSION->SWTC->DEBUG;
    }
}
