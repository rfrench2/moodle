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
 * @subpackage swtc/classes/swtc_user.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 10/16/20 - Changed to swtc class.
 *
 **/

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

use \stdClass;


/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT! $SESSION->SWTC MUST be set before calling (i.e. no check for SWTC).
 *
 * To call: $renderable = new \block_myoverview\output\main($group, $sort, $view, $paging, $customfieldvalue);
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
 // use cm_info;
 // use core_text;
 // use html_writer;
 // use context_course;
 // use moodle_url;
 // use coursecat_helper;



class swtc_user {
    /**
     * Store the user's id.
     *
     * @private integer
     */
    private $userid;

     /**
      * Store the user's username.
      *
      * @private string
      */
    private $username;

     /**
      * Store the user's accesstype.
      *
      * @private string
      */
    private $user_access_type;


    private $portfolio;
    private $roleshortname;
    private $roleid;
    private $categoryids;
    private $capabilities;
    private $timestamp;
    private $relateduser;
    private $cohortnames;
    private $groupname;
    private $geoname;
    private $groupnames;
    private $timezone;
    private $user_access_type2;

    /**
     * main constructor.
    * Initialize the SWTC swtc_user privateiable and loads into SWTC->USER.
    *
    * @param string $grouping Grouping user preference
    * @param string $sort Sort user preference
    * @param string $view Display user preference
    * @param int $paging
    * @param string $customfieldvalue
    *
    * @throws \dml_exception
    */
    public function __construct($user) {
        global $SESSION;

        print_object("In swtc_user __construct");		// 10/18/20 - SWTC
        // print_object("In swtc_user __construct; about to print backtrace");		// 10/16/20 - SWTC
        // print_object(format_backtrace(debug_backtrace(), true));        // SWTC-debug
        // print_object($user);		// 10/16/20 - SWTC

        // SWTC ********************************************************************************
        // If $SWTC->USER is not set, continue.
        // SWTC ********************************************************************************
        if (is_object($SESSION)) {
            print_object("In swtc_user __construct; did I get here 1; about to print SESSION");		// 10/16/20 - SWTC
            print_object($SESSION);		// 10/16/20 - SWTC
            if (!isset($SESSION->SWTC)) {
                print_object("In swtc_user __construct; did I get here 2");		// 10/16/20 - SWTC
                // SWTC *****************************************************************************
                // Setup the SWTC privateiable.
                //      Example: /lib/classes/session/manager.php starting around line 86.
                // SWTC *****************************************************************************
                $SESSION->SWTC = new stdClass();

                // SWTC *****************************************************************************
                // Setup the SWTC->USER privateiable.
                // SWTC *****************************************************************************
                $SESSION->SWTC->USER = new stdClass();

                // The following should be set in $USER.
                $this->userid = (isset($user->id)) ? $user->id : null;
                $this->username = (isset($user->username)) ? $user->username : null;

                // SWTC ********************************************************************************
                // Load the user's profile data.
                // SWTC ********************************************************************************
                $temp = new stdClass();
                $temp->id = $this->userid;
                profile_load_data($temp);
                // print_object("In swtc_user __construct; about to print profile data");		// 10/16/20 - SWTC
                // print_object($temp);		// 10/16/20 - SWTC
                // $this->user_access_type = $temp->get_string('profile_field_accesstype', 'local_swtc');
                $this->user_access_type = (isset($temp->profile_field_accesstype)) ? $temp->profile_field_accesstype : null;
                // $this->user_access_type2 = (null !== $temp->get_string('profile_field_accesstype2', 'local_swtc')) ? $temp->get_string('profile_field_accesstype2', 'local_swtc') : null;
                $this->user_access_type2 = (isset($temp->profile_field_accesstype2)) ? $temp->profile_field_accesstype2 : null;;

                // The following we'll set in ***.
                $this->portfolio = 'PORTFOLIO_NONE';
                $this->roleshortname = null;
                $this->roleid = null;
                $this->categoryids = null;
                $this->capabilities = null;
                $this->relateduser = null;
                $this->cohortnames = null;
                $this->groupname = null;
                $this->geoname = null;
                $this->groupnames = null;

                // SWTC ********************************************************************************
                // Get the additional swtc_user properties; user's timestamp and timezone.
                // SWTC ********************************************************************************
                list($this->timestamp, $this->timezone) = $this->set_timestamp();

                // SWTC ********************************************************************************
                // Copy this object to $SESSION->SWTC->USER.
                // SWTC ********************************************************************************
                $SESSION->SWTC->USER = clone($this);
                print_object("In not set SWTC->USER; about to print this");		// 10/16/20 - SWTC
                print_object($this);		// 10/16/20 - SWTC
            } else {
                // SWTC ********************************************************************************
                // Copy $SESSION->SWTC->USER to this object.
                // SWTC ********************************************************************************
                print_object("In swtc_user __construct; did I get here 4");		// 10/16/20 - SWTC
                $tmp = $SESSION->SWTC->USER;
                $this->userid = $tmp->userid;
                $this->username = $tmp->username;
                $this->user_access_type = $tmp->user_access_type;
                $this->portfolio = $tmp->portfolio;
                $this->roleshortname = $tmp->roleshortname;
                $this->roleid = $tmp->roleid;
                $this->categoryids = $tmp->categoryids;
                $this->capabilities = $tmp->capabilities;
                $this->relateduser = $tmp->relateduser;
                $this->cohortnames = $tmp->cohortnames;
                $this->groupname = $tmp->groupname;
                $this->geoname = $tmp->geoname;
                $this->groupnames = $tmp->groupnames;
                $this->user_access_type2 = $tmp->user_access_type2;

                // SWTC ********************************************************************************
                // User always gets a new timestamp and timezone.
                // SWTC ********************************************************************************
                list($this->timestamp, $this->timezone) = $this->set_timestamp();

                print_object("In IS set SWTC->USER; about to print this");		// 10/16/20 - SWTC
                print_object($this);		// 10/16/20 - SWTC
            }
        }
    }

    public function set_timestamp() {
        $timezone = \core_date::get_user_timezone_object();
        $today = new \DateTime("now", $timezone);
        $time = $today->format('H:i:s.u');
        return array($time, $timezone);
    }
}
