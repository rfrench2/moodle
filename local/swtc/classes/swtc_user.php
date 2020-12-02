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
use \cache;

// SWTC ********************************************************************************
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************
// require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');
require_once($CFG->dirroot . '/local/swtc/lib/locallib.php');


/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT!
 *          DO NOT call this class directly. Use $swtc_get_user from /lib/swtc_userlib.php.
 *
 * @param N/A
 *
 * @return $SESSION->SWTC->USER.
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
     * @private  integer
     */
    private  $userid;

     /**
      * Store the user's username.
      *
      * @private  string
      */
    private  $username;

     /**
      * Store the user's accesstype.
      *
      * @private  string
      */
    private  $user_access_type;

    /**
     * The user's main portfolio they have access to.
     *
     * @private  integer
     */
    private  $portfolio;

    /**
     * The user's role shortname.
     *
     * @private  integer
     */
    private  $roleshortname;

    /**
     * The user's role id.
     *
     * @private  integer
     */
    private  $roleid;

    /**
     * The categories the user has access to.
     *
     * @private  integer
     */
    private  $categoryids;

    /**
     * The user's capabilities.
     *
     * @private  integer
     */
    private  $capabilities;

    /**
     * The time of this action.
     *
     * @private  integer
     */
    private  $timestamp;

    /**
     * If an admin is performing an action on behalf of another user, this is the related user's id.
     *
     * @private  integer
     */
    private  $relateduser;

    /**
     * The cohort names the user is a member of (if any).
     *
     * @private  integer
     */
    private  $cohortnames;

    /**
     * The preg_match string that should be used to find all the groups the user is a member of.
     *
     * @private  integer
     */
    private  $groupname;

    /**
     * The user's GEO.
     *
     * @private  integer
     */
    private  $geoname;

    /**
     * The groups the user is a member of (if any).
     *
     * @private  integer
     */
    private  $groupnames;

    /**
     * The timezone of the user.
     *
     * @private  integer
     */
    private  $timezone;

    /**
     * The user's accesstype 2.
     *
     * @private  integer
     */
    private  $user_access_type2;

    /**
     * Constructor
     *
     * Constructor is private, use local_stwc->get_user() to retrieve SWTC user information.
     *
     * @param class $USER or $user
     *
    */
    public function __construct($user) {


        // print_object("In swtc_user __construct");		// 10/18/20 - SWTC
        // print_object("In swtc_user __construct; about to print backtrace");		// 10/16/20 - SWTC
        // print_object(format_backtrace(debug_backtrace(), true));        // SWTC-debug
        // print_object($user);		// 10/16/20 - SWTC

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
        $this->user_access_type = (isset($temp->profile_field_Accesstype)) ? $temp->profile_field_Accesstype : null;
        // $this->user_access_type2 = (null !== $temp->get_string('profile_field_accesstype2', 'local_swtc')) ? $temp->get_string('profile_field_accesstype2', 'local_swtc') : null;
        $this->user_access_type2 = (isset($temp->profile_field_accesstype2)) ? $temp->profile_field_accesstype2 : null;

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
        // $SESSION->SWTC->USER = clone($this);     // 10/19/20 - SWTC
        // $SESSION->SWTC->USER = $this;       // 10/19/20 - SWTC
        // print_object("In not set SWTC->USER; about to print this");		// 10/16/20 - SWTC
        // print_object($this);		// 10/16/20 - SWTC
        // print_object("About to leave swtc_user __construct; about to print SESSION->SWTC");		// 10/20/20 - SWTC
        // print_object($SESSION->SWTC);      // 10/20/20 - SWTC
    }

    /**
     * All Setter and Getter methods for all properties.
     *
     * Setter methods:
     *      @param $value
     *      @return N/A
     *
     * Getter methods:
     *      @param N/A
     *      @return value
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     **/
    public function get_user($user) {
        // $swtc_user = new swtc_user;      // 10/24/20
        $swtc_user = new swtc_user($user);
        // print_object("In swtc_user.get_user; about to print swtc_user");
        // print_object($swtc_user);
        return $swtc_user;
    }

    public function get_userid() {
        return $this->userid;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    public function get_user_access_type() {
        return $this->user_access_type;
    }

    public function get_capabilities() {
        return $this->capabilities;
    }

    public function get_portfolio() {
        return $this->portfolio;
    }

    /**
     * Set current date and time for timestamp. Returns value to set $SESSION->SWTC->USER->timestamp.
     *
     * History:
     *
     * 10/19/20 - Initial writing.
     *
     **/
    public function set_timestamp() {
        $timezone = \core_date::get_user_timezone_object();
        $today = new \DateTime("now", $timezone);
        $time = $today->format('H:i:s.u');
        return array($time, $timezone);
    }

    /**
     * Assign user role.
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     * 11/08/20 - Not needed anymore since using moodle/category:viewcourselist.
     *
     */

    function assign_user_role($eventdata) {

        return;

    }

    // SWTC ********************************************************************************
    // Get the logged in user customized user profile value 'Accesstype'. Accesstype is used to determine
    //      which portfolio of classes the user should have access to (in other words, which top-level
    //      category they should have access to). Note that this function returns the information the
    //      user 'should' have access to. What the user actually has access to (and whether they need
    //      more or less access) is determined above.
    //
    //  Important! Case of Accesstype is important. It must match the case defined in Moodle.
    //
    //  Returns array: first element portfolio value; second element the user's role shortname
    //      (i.e. 'ibm-student' or 'gtp-administrator'); third element is the top-level category id
    //      the user 'should' have access to (checked above).
    //
    // SWTC ********************************************************************************
    /**
     * Used get the users access.
     *
     * @param N/A
     *
     * @return $array   The catlist array.
     * @return $array   An array of values used to set $SESSION->SWTC->USER.
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    function get_user_access() {
    	global $USER, $SESSION;

        // SWTC - Debug 11/08/20
        return;

        // SWTC ********************************************************************************.
        // SWTC LMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_set_debug();

        // Other Lenovo variables.
        $cats = array();        // A list of all the top-level category information defined (returned to assign_user_role).
        $temp_user = new stdClass();    // Returned to calling function.

        // Temporary variables. Use these during the function and return values.
        $roleshortname = null;
        $portfolio = null;
        $categoryids = array(); // A list of all the categories the user should have access to (set in $swtc_user->categoryids).
        $capabilities = array();    // A list of all the capabilities the user should have (set in $swtc_user->capabilities).
        $roleid = null;

        // SWTC ********************************************************************************
        // 07/12/18 - Added check if swtc_user->relateduser is set. If so, use that user information to determine access.
        //                  Note that no switching of users below should be necessary.
        // SWTC ********************************************************************************
        if (isset($SESSION->SWTC->USER->relateduser)) {
            $swtc_user = $SESSION->SWTC->USER->relateduser;
            $user_access_type = $SESSION->SWTC->USER->relateduser->user_access_type;
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "swtc_user->relateduser is set; the userid that will be used throughout get_user_access is :<strong>$swtc_user->userid</strong>.";
            $messages[] = "swtc_user->relateduser is set; the username that will be used throughout get_user_access is :<strong>$swtc_user->username</strong>.";
    		$messages[] = "swtc_user->relateduser is set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
        } else {
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "swtc_user->relateduser is NOT set; the userid that will be used throughout get_user_access is :<strong>$swtc_user->userid</strong>.";
            $messages[] = "swtc_user->relateduser is NOT set; the username that will be used throughout get_user_access is :<strong>$swtc_user->username</strong>.";
    		$messages[] = "swtc_user->relateduser is NOT set; the user_access_type is :<strong>$swtc_user->user_access_type</strong>.";
            $swtc_user = $SESSION->SWTC->USER;
            $user_access_type = $SESSION->SWTC->USER->user_access_type;
        }
        // SWTC ********************************************************************************.

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            // $messages[] = "Lenovo ********************************************************************************.";
            // $messages[] = "Entering swtc_lib_locallib.php. ===3.get_user_access.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            // $phplog = debug_enable_phplog($debug, "In get_user_access.");
            $debug->logmessage($messages, 'both');
            unset($messages);

    		// SWTC ********************************************************************************
            // Additional diagnostic information.
            // SWTC ********************************************************************************
            // $messages[] = "About to print strings.";
            // $messages[] = print_r($strings, true);
            // $messages[] = "Finished printing strings.";
            // $debug->logmessage($messages, 'detailed');
            // unset($messages);
    	}

        // SWTC ********************************************************************************
    	// We already know what the user's role should be (either they have it or need it assigned to
    	//     them from above). And we've loaded all the roles defined in the system (Part 1). However,
    	//     we don't know the specific roleid ($role->id) assigned to that role name. In the $roles array,
    	//     search for the role ($access[roleshortname]) that the user should have. Once found,
        //      save the $role->id ($userroleid) in $swtc_user->roleid. Later, we will use this array to
        //      list all the other roles the user should NOT have and remove them.
        // SWTC ********************************************************************************
        // Load all the roles (context is not needed (see below)). The returned value, $roles, is an
        //      array that has the following format:
    	// 			[11] => stdClass Object
    	//		    (
    	//			        [id] => 11
    	//			        [name] => Lenovo-instructor
    	//			        [shortname] => lenovo-instructor
    	//			        [description] => A Lenovo instructor.
    	//			        [sortorder] => 12
    	//			        [archetype] => teacher
    	//			        ***[localname] => Lenovo-instructor - field not returned using get_all_roles()
    	//			    )
    	//
    	// 01/23/16 - Don't think the instance is needed: $context = context_coursecat::instance(CONTEXT_COURSECAT);
    	//		 		Changing role_get_names() to get_all_roles(). get_all_roles() defined in /lib/accesslib.php.
    	//		 		Returns array of all the defined roles (just like role_get_names), except it does not contain
    	//		 		the role localname field (that field is added by role_fix_names()). It DOES contain
        //              the role shortname field (that we use later). Note: get_all_roles() does NOT need a context
        //              to be passed to return all the defined roles (also doesn't matter what type of user that runs it).
        //
    	// 09/27/16 - Important! Hidden dependency is that the role name and the role shortname must match!
        // SWTC ********************************************************************************
    	$roles = get_all_roles();

        $cats = $this->loadcatids($roles);

        // SWTC ********************************************************************************
    	// Note: At this point the $cats array should be fully created...
        // SWTC ********************************************************************************
        //if (isset($debug)) {
        //    $messages[] = "cats array follows:";
        //    // $messages[] = print_object($cats, true);
        //    $messages[] = print_r($cats, true);
        //    $messages[] = "cats array ends.";
        //    $debug->logmessage($messages, 'detailed');
        //    unset($messages);
        ////  //	die();
        //}

        // SWTC ********************************************************************************
    	// Determine what portfolio the user should be able to view based on value in access_type
    	//     Important! Since the switch statement is using the EXACT $access_xxx_yyy strings for
    	//     comparison to the Accesstype flag, they must be defined that way in the
    	//     /lang/en/local_swtc.php file...
        //
    	// 03/29/16 - Even though all users might use a shared resource, no users should have direct access to
        //                  'Lenovo Shared Resources (Master)' except Lenovo-admins.
    	// 08/31/16 - Adding Lenovo-stud and Lenovo-inst to Maintech Portfolio.
        // SWTC ********************************************************************************
    	if (isset($debug)) {
    		$messages[] = "swtc_user array follows: ";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends. user_access_type to check is:  $user_access_type";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    		// print_object($swtc_user, true);
            // die();
    	}

        // SWTC ********************************************************************************
        // Switch on the users access type.
        //
        // Sets variables:
    	//			$swtc_user->roleshortname     The actual name of the role the user has.
        //			$swtc_user->portfolio      The name of the portfolio the user has access to.
        //			$swtc_user->categoryids    An array of category ids the user has access to.
        //			$swtc_user->capabilities   An array of capabilities the user has.
        //
        // SWTC ********************************************************************************

    	// SWTC ********************************************************************************
    	// Check for Lenovo-admin, Lenovo-inst, or Lenovo-stud user
    	// SWTC ********************************************************************************
    	if ((stripos($user_access_type, get_string('access_lenovo_admin', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_inst', 'local_swtc')) !== false) || (stripos($user_access_type, get_string('access_lenovo_stud', 'local_swtc')) !== false)) {

    		if (stripos($user_access_type, get_string('role_lenovo_admin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_lenovo_administrator', 'local_swtc');
    			$portfolio = get_string('lenovo_portfolio', 'local_swtc');

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovointernal_portfolio', 'local_swtc'), $cats);

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovosharedresources_portfolio', 'local_swtc'), $cats);

    			list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

                list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('curriculums_portfolio', 'local_swtc'), $cats);

    		} else if (stripos($user_access_type, get_string('role_lenovo_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_lenovo_instructor', 'local_swtc');
    				$portfolio = get_string('lenovo_portfolio', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_lenovo_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_lenovo_student', 'local_swtc');
    				$portfolio = get_string('lenovo_portfolio', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    		// 07/23/18 - Added access to PremierSupport portfolio for Lenovo-administrators until GA.
    		// 11/30/18 - Changed access type names for ServiceDelivery and added access for the appropriate user types; modified
    		//                      access for PremierSupport.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('premiersupport_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('servicedelivery_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_av_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_im_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_lq_gtp', 'local_swtc')) !== false) {

    		$portfolio = get_string('gtp_portfolio', 'local_swtc');

    		if (stripos($user_access_type, get_string('role_gtp_siteadmin', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_admin', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_administrator', 'local_swtc');
    		} else if (stripos($user_access_type, get_string('role_gtp_inst', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_instructor', 'local_swtc');
    		} else if  (stripos($user_access_type, get_string('role_gtp_stud', 'local_swtc')) !== false) {
    				$roleshortname = get_string('role_gtp_student', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('gtp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for IBM-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_ibm_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('ibm_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_ibm_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id value.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for ServiceProvider-stud user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_serviceprovider_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_serviceprovider_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for Maintech-stud user
        // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/get_user_access to fix
        //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
    	// SWTC ********************************************************************************
    	// } elseif (stripos($user_access_type, get_string('access_maintech_stud', 'local_swtc')) !== false) {      // 11/25/19
        } elseif (strncasecmp($user_access_type, get_string('access_maintech_stud', 'local_swtc'), strlen($user_access_type)) == 0) {   // 11/25/19

    		$portfolio = get_string('maintech_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_maintech_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for ASP-Maintech-stud user
        // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/get_user_access to fix
        //                      ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
    	// SWTC ********************************************************************************
    	// } elseif (stripos($user_access_type, get_string('access_asp_maintech_stud', 'local_swtc')) !== false) {      // 11/25/19
        } elseif (strncasecmp($user_access_type, get_string('access_asp_maintech_stud', 'local_swtc'), strlen($user_access_type)) == 0) {   // 11/25/19

    		$portfolio = get_string('serviceprovider_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_asp_maintech_student', 'local_swtc');

    		// Search for category name in cats array. When found, load the category id values.
    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for PremierSupport users
    	// 05/16/18 - For testing, added PremierSupport-mgr1, PremierSupport-mgr2, and PremierSupport-mgr3 roles.
    	// 11/07/18 - Added additional access type strings for all PremierSupport user types.
    	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
    	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
    	// SWTC ********************************************************************************
    	} else if ((preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {

    		$portfolio = get_string('premiersupport_portfolio', 'local_swtc');

    		if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    			$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
    		} else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		if (has_capability($cats[array_search(get_string('premiersupport_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['capability'],
    			$cats[array_search(get_string('premiersupport_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['context'])) {
    			// $debug->logmessage("===Yes===", 'detailed');
    		} else {
    			// $debug->logmessage("===No===", 'detailed');
    		}

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('premiersupport_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);
    		// print_object("did I get here??");       // 11/20/19 - Lenovo debugging...
            // print_object($categoryids);
    	// SWTC ********************************************************************************
    	// Check for ServiceDelivery users
    	// 11/15/18 - Added additional access type strings for all ServiceDelivery user types.
    	// 01/17/19 - For checking access, replaced checking of multiple access types with switch statement to multiple stripos checks.
    	// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
    	// SWTC ********************************************************************************
    	} else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type))) {
    		$portfolio = get_string('servicedelivery_portfolio', 'local_swtc');

    		if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_madmin', 'local_swtc'), $user_access_type)) {
    			$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
    		} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    				$roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
    		}

    		// Search for category name in cats array. When found, load the category id values.
    		if (has_capability($cats[array_search(get_string('servicedelivery_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['capability'],
    			$cats[array_search(get_string('servicedelivery_portfolio', 'local_swtc'), array_column($cats, 'catname'))]['context'])) {
    			// $debug->logmessage("===Yes===", 'detailed');
    		} else {
    			// $debug->logmessage("===No===", 'detailed');
    		}

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('servicedelivery_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('ibm_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('lenovo_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('maintech_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('serviceprovider_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('asp_portfolio', 'local_swtc'), $cats);

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);

    	// SWTC ********************************************************************************
    	// Check for Self support user
    	// SWTC ********************************************************************************
    	} elseif (stripos($user_access_type, get_string('access_selfsupport_stud', 'local_swtc')) !== false) {

    		$portfolio = get_string('none_portfolio', 'local_swtc');
    		$roleshortname = get_string('role_selfsupport_student', 'local_swtc');

    		list($categoryids[], $capabilities[]) = $this->get_portfolio_name(get_string('sitehelp_portfolio', 'local_swtc'), $cats);
    		// $categoryids[] = 'none';
    		// $capabilities[] = 'none';

    	// SWTC ********************************************************************************
    	// Check for Special access user
    	// SWTC ********************************************************************************
    	// case $access_special_user:
    	// case $access_specialaccess_stud:
    	//
    	//     $roleshortname = $role_specialaccess_student;
    	//
    	// 	break;

    	// SWTC ********************************************************************************
    	// Accesstype is not recognized
    	// SWTC ********************************************************************************
    	} else {
    		$portfolio = get_string('PORTFOLIO_NONE', 'local_swtc');
    		$roleshortname = 'none';
    		$categoryids[] = 'none';
    		$capabilities[] = 'none';
    	}

        // SWTC ********************************************************************************
        // Loop through all the roles defined. When the shortname is found, load the role's id value.
        //
        // Sets variables:
    	//			$swtc_user->roleid								The id of the role the user has.
        // SWTC ********************************************************************************
    	foreach ($roles as $role) {
    		if ($role->shortname == $roleshortname) {
    			$roleid = $role->id;
    			break;
    		}
    	}

        // SWTC ********************************************************************************
        // Finished. Set $temp_user to all the appropriate values so it can be returned.
        // SWTC ********************************************************************************
        $temp_user->portfolio = $portfolio;
        $temp_user->roleshortname = $roleshortname;
        $temp_user->categoryids = $categoryids;
        $temp_user->capabilities = $capabilities;
        $temp_user->roleid = $roleid;

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Leaving swtc_lib_locallib.php. ===3.get_user_access.exit.";
            $messages[] = "Lenovo ********************************************************************************.";
    		$messages[] = "temp_user array follows: ";
            $messages[] = print_r($temp_user, true);
            $messages[] = "After printing temp_user";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	}

        return array($cats, $temp_user);
    }

    /**
     * Load all the category (portfolio) ids and information about each of them.
     *
     * @param N/A
     *
     * @return $array   All category information.
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     **/
    function loadcatids($roles) {

        // SWTC ********************************************************************************.
        // SWTC LMS swtc_user and debug variables.
        $debug = swtc_set_debug();

        // A list of all the top-level category information defined (this is returned).
        $cats = array();

    	if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_swtclib.php. ===swtc_loadcatids.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

        // SWTC ********************************************************************************
        // Get a list of all top-level categories defined in the system (whether the user can view them or not) using get_tree.
    	//		Note: The following array is returned; the number in the listing is the top-level category id number ($catids->id). Example:
    	//			array (					At the time of this writing, the top-level category names are:
    	//				[0] => 14			'GTP Portfolio'
    	//				[1] => 36			'IBM Portfolio'
    	//				[2] => 47			'Lenovo Portfolio'
    	//				[3] => 60			'Lenovo Internal Portfolio'
    	//				[4] => 73			'Lenovo Shared Resources (Master)'
    	//				[5] => 74			'Maintech Portfolio'
    	//				[6] => 25			'Service Provider'
        //				[7] => 97			'ASP Portfolio'
        //				[8] => 110		'Premier Support Portfolio'
        //				[9] => 137		'Service Delivery Portfolio'
        //				[10] => 136		'Site Help Portfolio'
    	//				[11] => 141		'Curriculums Portfolio'
    	//			)
    	//			Important! The category id's returned are NOT guaranteed to be the numbers shown (although they should be). However,
    	//					the category NAMES ARE guaranteed to be strings shown (unless specifically changed on the Lenovo EBG LMS site).
    	//			Important! To access context for each category: $context = $cats[0-8]['context'];
        // SWTC ********************************************************************************
        $catids = $this->get_tree(0);				// '0' means just the top-level categories are returned.

    	if (isset($debug)) {
            // debug_enable_phplog($debug, "2 - In swtc_loadcatids.");
    		$messages[] = "catids array follows:";
            $messages[] = print_r($catids, true);
            $messages[] = "catids array ends.";
    		// print_object($catids);
    	//	$debug->logmessage("roles array follows: <br />", 'detailed');
    	//	print_object($roles);
    	//	die();
            $debug->logmessage($messages, 'detailed');
            unset($messages);
    	}

        // SWTC ********************************************************************************
    	// Next, load a multi-dimension array for each of the top-level categories (this array will be searched by name for the id below):
        //              'catid'             - the id of the top-level category (returned from the get_tree(0) call above).
        //              'catname'       - the name of the top-level category (ex: "GTP Portfolio").
        //              'context'       - create a context of context_coursecat.
        //              'capability'    - the capability associated with this top-level category (ex: local/swtc:ebg_access_gtp_portfolio).
        //              'roles'             - array of all roles and roleids associated with this top-level category (see below for example).
        //
    	//			An example array (filled-in below) has the following format (as of 08/28/16 taken from .244 sandbox):
    	//
    	//			[0] => Array
    	//				(
    	//					[catid] => 14
    	//					[catname] => GTP Portfolio
    	//					[context] => context_coursecat Object
    	//						(
    	//							[_id:protected] => 511
    	//							[_contextlevel:protected] => 40
    	//							[_instanceid:protected] => 14
    	//							[_path:protected] => /1/511
    	//							[_depth:protected] => 2
    	//						)
    	//					[capability] => local/swtc:ebg_access_gtp_portfolio
    	//					[roles] => Array
    	//						(
    	//							[gtp-instructor] => 15
    	//							[gtp-student] => 16
    	//							[gtp-administrator] => 10
    	//							[gtp-siteadministrator] => 23
    	//						)
    	//				)
    	//
        // SWTC ********************************************************************************

        // SWTC ********************************************************************************
    	// Build the main $cats array (to be passed back to assign_user_role).
        // SWTC ********************************************************************************
    	foreach ($catids as $key => $catid) {
    		$cats[$key]['catid'] = $catid;
    		// $cats[$key]['catname'] = coursecat::get($catid, MUST_EXIST, true)->name;     // Moodle 3.6
            $cats[$key]['catname'] = \core_course_category::get($catid, MUST_EXIST, true)->name;
    		$cats[$key]['context'] = \context_coursecat::instance($catid);

            // SWTC ********************************************************************************
            // Switch on the 'catname'.
            //      Note: If adding a new portfolio, add a new case to this switch.
            // SWTC ********************************************************************************
            switch ($cats[$key]['catname'] ) {
                // SWTC ********************************************************************************
                // 'GTP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('gtp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_gtp_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        //if (isset($debug)) {
                        //	$debug->logmessage("role follows: <br />", 'logfile');
                        //	print_object($role);
                        //	$debug->logmessage("role->shortname to search for is <strong>$role->shortname</strong>.<br />", 'logfile');
                        //}
                        if ($role->shortname == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_gtp_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovo_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovo_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_lenovo_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'IBM Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
    			// 08/25/16 - Changed "Lenovo and IBM Portfolio" values to just "IBM Portfolio" so that values
                //                  will be the same (i.e. will help in transition).
                // SWTC ********************************************************************************
                case get_string('ibm_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_ibm_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ServiceProvider Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('serviceprovider_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_serviceprovider_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Internal Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovointernal_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovointernal_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Maintech Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('maintech_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_maintech_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_maintech_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Lenovo Shared Resources (Master)' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('lenovosharedresources_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_lenovosharedresources_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ASP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('asp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_asp_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_asp_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'PremierSupport Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('premiersupport_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_premiersupport_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'ServiceDelivery Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('servicedelivery_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_servicedelivery_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                // SWTC ********************************************************************************
    			// 'Site Help Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('sitehelp_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_sitehelp_portfolio', 'local_swtc');

                    // Load all the roleids. Remember that ALL roles have access to this portfolio.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_gtp_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_siteadministrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_gtp_siteadministrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_instructor', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_gtp_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_gtp_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_instructor', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_instructor', 'local_swtc')] = $role->id;
                        }else if ($role->shortname == get_string('role_lenovo_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_lenovo_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_ibm_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_ibm_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_serviceprovider_student', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_serviceprovider_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_lenovo_administrator', 'local_swtc')) {
                            $cats[$key]['roles'][get_string('role_lenovo_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_asp_maintech_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_asp_maintech_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
    					} else if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } else if ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
    					}
                    }
                    break;

    			// SWTC ********************************************************************************
    			// 'Curriculums Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
                // SWTC ********************************************************************************
                case get_string('curriculums_portfolio', 'local_swtc'):
                    $cats[$key]['capability']  = get_string('cap_swtc_access_curriculums_portfolio', 'local_swtc');

                    // Load all the roleids.
                    foreach ($roles as $role) {
                        if ($role->shortname == get_string('role_servicedelivery_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_student', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_servicedelivery_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_administrator', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_servicedelivery_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_servicedelivery_manager', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_student', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_student', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_administrator', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_administrator', 'local_swtc')] = $role->id;
                        } elseif ($role->shortname == get_string('role_premiersupport_manager', 'local_swtc')){
                            $cats[$key]['roles'][get_string('role_premiersupport_manager', 'local_swtc')] = $role->id;
                        }
                    }
                    break;

                default:
                    // unknown type
            }
        }

        // SWTC ********************************************************************************
    	// Note: At this point the $cats array should be fully created...
        // SWTC ********************************************************************************
        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Exiting swtc_lib_swtclib.php. ===swtc_loadcatids.exit.";
            $messages[] = "Lenovo ********************************************************************************.";
            // debug_enable_phplog($debug);
            // $messages[] =  "cats array follows:";
            // $messages[] = print_object($cats);
            // $messages[] = print_r($cats, true);
            // $messages[] = "cats array ends.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        //	die();
        }

    	return $cats;
    }

    /**
     * Returns the entry from categories tree and makes sure the application-level tree cache is built
     *
     * The following keys can be requested:
     *
     * 'countall' - total number of categories in the system (always present)
     * 0 - array of ids of top-level categories (always present)
     * '0i' - array of ids of top-level categories that have visible=0 (always present but may be empty array)
     * $id (int) - array of ids of categories that are direct children of category with id $id. If
     *   category with id $id does not exist returns false. If category has no children returns empty array
     * $id.'i' - array of ids of children categories that have visible=0
     *
     * @param int|string $id
     * @return mixed
     */
    function get_tree($id) {
    	global $DB;
    	$coursecattreecache = cache::make('core', 'coursecattree');
    	$rv = $coursecattreecache->get($id);
    	if ($rv !== false) {
    		return $rv;
    	}
    	// Re-build the tree.
    	$sql = "SELECT cc.id, cc.parent, cc.visible
    			FROM {course_categories} cc
    			ORDER BY cc.sortorder";
    	$rs = $DB->get_recordset_sql($sql, array());
    	$all = array(0 => array(), '0i' => array());
    	$count = 0;
    	foreach ($rs as $record) {
    		$all[$record->id] = array();
    		$all[$record->id. 'i']= array();
    		if (array_key_exists($record->parent, $all)) {
    			$all[$record->parent][] = $record->id;
    			if (!$record->visible) {
    				$all[$record->parent. 'i'][] = $record->id;
    			}
    		} else {
    			// Parent not found. This is data consistency error but next fix_course_sortorder() should fix it.
    			$all[0][] = $record->id;
    			if (!$record->visible) {
    				$all['0i'][] = $record->id;
    			}
    		}
    		$count++;
    	}
    	$rs->close();
    	if (!$count) {
    		// No categories found.
    		// This may happen after upgrade of a very old moodle version.
    		// In new versions the default category is created on install.
    		$defcoursecat = $self::create(array('name' => get_string('miscellaneous')));
    		set_config('defaultrequestcategory', $defcoursecat->id);
    		$all[0] = array($defcoursecat->id);
    		$all[$defcoursecat->id] = array();
    		$count++;
    	}
    	// We must add countall to all in case it was the requested ID.
    	$all['countall'] = $count;
    	foreach ($all as $key => $children) {
    		$coursecattreecache->set($key, $children);
    	}
    	if (array_key_exists($id, $all)) {
    		return $all[$id];
    	}
    	// Requested non-existing category.
    	return array();
    }

    /**
     * Look for the portfolio name in the $categoryids array. When found, save the values we want and
     *      return the newly created array.
     *
     * @param The portfolio name to look for and the list of all portfolios.
     *
     * @return $tmp   The catlist array used to set $SESSION->SWTC->USER.
     * @return string   The capability.
     *
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    function get_portfolio_name($portfolio_name, $cats) {
        $tmp = array();

        $cat = $cats[array_search($portfolio_name, array_column($cats, 'catname'))];

        $tmp['catid'] = $cat['catid'];
        $tmp['catname'] = $cat['catname'];
        $tmp['context'] = $cat['context'];
        $tmp['capability'] = $cat['capability'];

        return array($tmp, $cat['capability']);

    }

    /**
     * If PremierSupport or ServiceDelivery manager or administrator ventures outside their own portfolio,
     *          they are no longer considered a manager or administrator. Substitute either
     *          PremierSupport-student or ServiceDelivery-student as role.
     *
     * @param $cat		A catlist class variable.
     * @param $user		A user class variable.
     *
     * @return $temp_user	$user (passed in) with the rolename and roleid changed if required.
     *
     *
     * History:
     *
     * 10/24/20 - Initial writing.
     *
     */
    function change_user_access($cat, &$user) {
        global $DB;

        // SWTC - Debug 10/30/20
        return;

        // SWTC ********************************************************************************.
        // SWTC SWTC swtc_user and debug variables.
        $swtc_user = swtc_get_user($user);
        $debug = swtc_set_debug();

        // Other Lenovo variables.
        $user_access_type = $swtc_user->user_access_type;
        $roleshortname = null;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************
            // Always output standard header information.
            // SWTC ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering swtc_lib_locallib.php. ===local_swtc_change_user_access.enter.";
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "swtc_user array ends.";
            // $debug->logmessage(print_r($swtc, true), 'detailed');
            $debug->logmessage($messages, 'both');
            unset($messages);
    	}

    	// $topcat = $cat['catname'];
    	// print_object("In local_swtc_change_user_access. catname to check is :$topcat.");

    	// 01/10/19 - Just a test...
    	// if (has_capability($cat['capability'], $cat['context'])) {
    	// 	print_object("User has access to category $topcat");
    	// } else {
    	// 	print_object("User does NOT have access to category $topcat");
    	// }

        // SWTC ********************************************************************************
        // Substitute PremierSupport-student as role if outside of PremierSupport portfolio.
        // Substitute ServiceDelivery-student as role if outside of ServiceDelivery portfolio.
        // SWTC ********************************************************************************
    	// print_object($user->user_access_type);
    	// if (stripos($user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc')) !== false) {
    	// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), stripos was true");
    	// } else {
    	// 	print_object("$user->user_access_type, get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), stripos was false");
    	// }

    	// SWTC ********************************************************************************
    	// PremierSupport access type.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/08/19 - Added PS/SD GEO site administrator user access types.
    	// SWTC ********************************************************************************
        //****************************************************************************************.
        // PremierSupport managers
        //****************************************************************************************.
    	if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the mgr access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		}
        //****************************************************************************************.
        // PremierSupport administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
        //****************************************************************************************.
        // PremierSupport GEO administrators
        //****************************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the GEO admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
        //****************************************************************************************.
        // PremierSupport site administrators
        //****************************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is PremierSupport, continue with the site admin access.
    		if (stripos($cat['catname'], get_string('premiersupport_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
    		} else {
    			// If the portfolio is NOT PremierSupport, substitute PremierSupport-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_premiersupport_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_PREMIERSUPPORT.", 'detailed');
            }
    	// SWTC ********************************************************************************
    	// ServiceDelivery access type.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/08/19 - Added PS/SD GEO site administrator user access types.
    	// SWTC ********************************************************************************
        //****************************************************************************************.
        // ServiceDelivery managers
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the mgr access.
    		// print_object("I found a servicedelivery-mgr.");
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery GEO administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        //****************************************************************************************.
        // ServiceDelivery site administrators
        //****************************************************************************************.
    	} else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $user_access_type)) {
    		// If the portfolio is ServiceDelivery, continue with the admin access.
    		if (stripos($cat['catname'], get_string('servicedelivery_portfolio', 'local_swtc')) !== false) {
    			$roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
    			// $debug->logmessage("In found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		} else {
    			// If the portfolio is NOT ServiceDelivery, substitute ServiceDelivery-student role id (not a string; hard-code for now).
    			$roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
    			// $debug->logmessage("In NOT found PORTFOLIO_SERVICEDELIVERY.", 'detailed');
    		}
        }
        // SWTC ********************************************************************************
        // Remember to set the roleid.
    	// 12/19/18 - Instead of directly changing the roleshortname, set a temporary variable and at the end of the function,
    	//						if it is set, then change $user->roleshortname. If not changing role, remember to set it to whatever
    	//						it was when this was called.
        // SWTC ********************************************************************************
    	if (!empty($roleshortname)) {
    		$user->roleshortname = $roleshortname;
    		$role = $DB->get_record('role', array('shortname' => $user->roleshortname), '*', MUST_EXIST);
    		$user->roleid = $role->id;
    	} else {

    	}

    	// return $tmp_user;
    	return;
    }
}
