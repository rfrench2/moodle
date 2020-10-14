<?php
// declare(strict_types=1); // For debugging.
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General protected License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General protected License for more details.
//
// You should have received a copy of the GNU General protected License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/debuglib.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	04/14/18 - Initial writing; only link this file IF, and ONLY if, you are debugging; $SESSION->EBGLMS MUST already be setup.
 *                      In other words, include AFTER /lib/swtc.php. If $SESSION->EBGLMS->DEBUG is set, we ARE debugging (no need
 *                      for any other types of checking).
 * 04/15/18 - Experimenting with changing log files from .log to .html so that output will format correctly.
 * 04/20/18 - Added check in debug_logmessage so that backtrace information can be saved to logfiles if needed; removed php->enable flag
 *                  (if the php object is available, print it).
 * 04/26/18 - Added debugging hints and tips to header of debug_setup.
 * 05/15/18 - Changed log file name from "details" to "detailed" to align with flag that is passed.
 * 07/17/18 - In swtc_get_debug and debug_start, check for server name and specific list of userids that are authorized to do debugging.
 *                      If running on production or if userid is not one of those authorized, disable debugging.
 * 08/06/18 - Added "override" user to debug_authorized_user so that a valid username is not required.
 * 09/06/18 - Enable debugging in the case of any authorized user using "loggedonas" another user.
 * 11/07/18 - Added more authorized users (new PremierSupport test users).
 * 11/27/18 - Added more authorized users (if called from cron, "admin" is account name) and ServiceDelivery users.
 * 04/11/19 - (Major change) In preparation for moving to Motorola hosting, , moving debugging logs from
 *                      /moodledata/repository/debug/* to /dcgdata/debug/*.
 * 10/11/19 - Changed debug log file location to be off of $CFG->dataroot.
 * 11/02/19 - In preparation for Moodle 3.7+, in swtc_get_debug, added code to check for Lenovo debug setting so that everyone can
 *                      call swtc_get_debug directly.
 * 11/15/19 - In swtc_get_debug and debug_start, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 * 03/02/20 - Moved swtc_get_debug function from debuglib.php to swtc_userlib.php to improve performance.
 *
 **/

defined('MOODLE_INTERNAL') || die();

/**
 * Initializes all debugging options.
 *
 * @param N/A
 *
 * @return $array   The debug array.
 *
 *      Debugging hints and tips:
 *          - The PHP function debug_print_backtrace() prints much more information than the Moodle function format_backtrace.
 *          - If experiencing "Exception - Call to undefined function" to functions in swtc plugin, use the PHP function get_included_files.
 *
 */
 /**
 * Version details
 *
 * History:
 *
 *  04/11/18 - Initial writing (copied from sblib.php).
 *  04/21/18 - Added "scope" variable. Options are "internal", "external", "all".
 *  04/26/18 - Added "includes" array to hold just the include files that have "/local/swtc" in the path.
 *
 **/
function debug_setup() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...

    // Debugging and test flags:
    //      $interactive:           If set, step through the procedure. For example, during export, can step through the
    //                                      writing of the CSV file and updating of LMS.
    //      $preview:               If set, don't do anything - show what you would do.
    //      $print                  If set, print large datasets to log file.
    //      $userid             If set, will search for, and export, only the certifications for that one user (note that this is not the SB technician id).
    //
    // Set global debugging flags for this PHP file. Only have to change it here...effects all output...
    //		Change both Regular and Testing (i.e. keep in sync).
    // To set: All are set below from command line parameters.
    $debugging = new stdClass();

    $debugging->information = '';           // General purpose field to pass / print anything that is required.

    $debugging->interactive = false;     // Anything other than 0 will enable interactive debugging.
    $debugging->preview = false;         // Anything other than 0 will enable preview mode (only display statistics and don't write to either SB servers).

    // For testing, set prt to true.
    $debugging->prt = true;                // Anything other than 0 will enable print mode (print large datasets to log file).
    $debugging->userid = false;          // Anything other than 0 will run the SQL commands against only one userid.
    $debugging->force = false;          // Anything other than 0 will force a write of the row to the CSV even if the user is found to have the cert.

    // Scope: what to debug?
    //      'internal' = EBGLMS functions in EBGLMS PHP files only.
    //      'external' = EBGLMS modified code in other PHP files only.
    //      'all' = Both internal and external.
    $debugging->scope = 'internal';
    // $debugging->scope = 'external';

    // Added log files to EBG_DEBUG (both regular and detailed).
    //  IMPORTANT! Since we are now using $SESSION to save variables, we can't open the log files once and simply pass around the open file
    //      handles (since all file resources are disconnected if another page is loaded). In other words, to write a line of text to the log file we must:
    //          - open the log file and save the open file handle as a local variable
    //          - write the line of text to the log file
    //          - close the log file
    // $debugging->debug_logfp = 0;       // Debug log file pointer; can NOT use any more.
    $debugging->debug_fqlog = "";
    // $debugging->debug_detailedfp = 0;      // Detailed log file pointer; can NOT use any more.
    $debugging->debug_fqdetailed = "";


    //      $sbserver:       Which SB server to use? If  'srv=test' is set on command line (then value is true) and SB test server is used.
    //                           If not passed on command line (which is the default), the SB production server is used.
    $debugging->sbserver = 'prod';

    // Save the current user so that if current user is "root", the function can change the ownership of the file to the "apache" process (so that
    //          files can be written or appended to).
    // Notes:
    //      This should always be included after any fopen, file_put_contents, or after any call that opens a file that potentially both root and apache
    //              would write to.
    //      This can be invoked using either the "root" user (if running from a putty session) or "apache" (if running from Moodle).
    //          If invoked using "apache" AND the log files are created by "apache", all is well (because "root" will append to the log files with no errors).
    //          If invoked using "apache" AND the log files were created by "root", the web process will FAIL ("apache" will not be able to append to a
    //              log file created by "root").
    //          If invoked using "root", all is well (because "root" will either create or append to the log files with no errors).
    //
    // Call posix_getpwuid(posix_geteuid()) to find the user account currently running the dcgsbautouser script (normally either "root" or "apache").
    //
    // Typical logic would be similar to the following:
    //
    //      if ($debugging->username === 'root') {
    //          chown($debugging->fqlog, 'apache');
    //      }
    $current_user = posix_getpwuid(posix_geteuid());
    $debugging->username = $current_user['name'];

    // To hold PHP include files.
    $debugging->includes = array();

    // Variables end...
    // Lenovo ********************************************************************************

    return $debugging;

}

/**
 * Initializes all PHP debugging options.
 *
 * @param N/A
 *
 * @return $array   The PHP log array.
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/12/18 - Initial writing.
 * 04/20/18 - Removed enable flag (if the php object is available, print it).
 *
 **/
function debug_setup_phplog() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...
    $log = new stdClass();

    // $log->enable = false;       // Anything other than 0 will enable PHP backtrace debugging.

    $log->timeofevent = null;       // Exact time of event.

    // For PHP debugging:
    // Put the following in each function that requires tracing.
    //      To use:
    //              $debug->backtrace_current_function = debug_backtrace()[0]['function'];
    //              $debug->backtrace_calling_function = debug_backtrace()[1]['function'];
    // $debug->backtrace_current_function = '';
    // $debug->backtrace_calling_function = '';
    //
    // To hold all backtrace information.
    //      For "pretty" output, call format_backtrace (in /lib/setuplib.php) around line 723.
    $log->backtrace = new stdClass;

    // Variables end...
    // Lenovo ********************************************************************************

    return $log;

}

/**
 * Initializes all debugging options related to $SESSION->EBGLMS->DEBUG->user (lower case).
 *
 * @param $user         REAL user array loaded with "real" values (if any exist).
 *
 * @return $array   The debug_user array with the debug user information added. Must be kept in sync with actual
 *                                  $SESSION->EBGLMS->USER fields.
 */
 /**
 * Version details
 *
 * History:
 *
 *  04/21/18 - Initial writing; mirrors $SESSION->EBGLMS->USER with the addition of what function set the value and what
 *                          date / time was the value set.
 *
 **/
function debug_setup_user($user) {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Make all the times these variables were set the same.
    // Make all the functions these variables were set the same.
    // Lenovo ********************************************************************************
    $today = new DateTime("now", core_date::get_user_timezone_object());
    $time = $today->format('H:i:s.u');
    $where = "swtc/lib/debuglib.php:debug_setup_user()";

    // Lenovo ********************************************************************************
    // Variables begin...
    $tmp = new stdClass();

    $tmp->userid = array('value' => $user->userid,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->username = array('value' => $user->username,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->user_access_type = array('value' => $user->user_access_type,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->portfolio = array('value' => $user->portfolio,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->roleshortname = array('value' => $user->roleshortname,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->roleid = array('value' => $user->roleid,
                                        'function' => $where,
                                        'time' => $time
                        );

    $tmp->categoryids = array('value' => $user->categoryids,
                                        'function' => $where,
                                        'time' => $time
                        );

    // Variables end...
    // Lenovo ********************************************************************************

    return $tmp;

}

/**
 * Used to start debugging. Returns $SESSION->EBGLMS->DEBUG.
 *
 * @param N/A
 *
 * @return $array   The DEBUG array.
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/12/18 - Initial writing.
 * 04/20/18 - Removed automatic setting of PHP object. It will only be set by debug function that want to use backtrace.
 * 04/22/18 - Added call to debug_setup_user which adds array similar to $swtc_user that includes value,
 *                          what function set the value, and time set.
 * 07/17/18 - Check for server name and specific list of userids that are authorized to do debugging. If running on production
 *                      or if userid is not one of those authorized, disable debugging.
 * 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
 *                          if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
 *                          user's session has expired.
 *
 **/
function debug_start() {
    global $CFG, $SESSION;

    //****************************************************************************************
	// Local variables begin...
    $debug = null;
    $servername_production = 'https://lenovoedu.lenovo.com';
    $swtc_user = new stdClass();

    // Lenovo ********************************************************************************
    // Access to the top-level $EBGLMS global variables (it should ALWAYS be available; set in /lib/swtc.php).
    //      To use: if (isset($SESSION->EBGLMS))
    // 11/15/19 - In swtc_get_debug and debug_setup, changing if (isset($SESSION->EBGLMS)) to
    //                      if (isset($SESSION->EBGLMS->USER)) to hopefully handle PHP errors when debugging if the
    //                      user's session has expired.
    // Lenovo ********************************************************************************
    if (isset($SESSION->EBGLMS->USER)) {
        require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');
        // Set all the EBGLMS variables that will be used.
        $swtc_user = $SESSION->EBGLMS->USER;
    } else {
        // TODO: Catastrophic error; what to do with $swtc_user?
    }
    // Local variables end...
	//****************************************************************************************

    // Lenovo ********************************************************************************
    // If $DEBUG is not set, continue.
    //      To use: $SESSION->EBGLMS or $SESSION->{'EBGLMS'}->{'DEBUG'}
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG)) {
        // Lenovo ********************************************************************************
        // Setup $SESSION->EBGLMS->DEBUG and set a temporary reference to it.
        //      To use: $debug = $SESSION->EBGLMS->DEBUG
        // Lenovo ********************************************************************************
        // print_r("debug_start: SESSION->EBGLMS->DEBUG ->NOT<- set. Called from ".debug_backtrace()[1]['function'].".<br />");
        // If running on production, check the authorized user list.

        $debug = debug_setup();

        $SESSION->EBGLMS->DEBUG = $debug;

        // Lenovo ********************************************************************************
        // Setup the third-level $PHPLOG global variable.
        //      To use: $SESSION->EBGLMS->DEBUG->PHPLOG
        // Lenovo ********************************************************************************
        // $php_log = debug_setup_phplog();                                                // 04/20/18: See above.
        // $SESSION->EBGLMS->DEBUG->PHPLOG = $php_log;      // 04/20/18: See above.
        $SESSION->EBGLMS->DEBUG->PHPLOG = null;

        // Lenovo ********************************************************************************
        // Setup the fourth-level $user global variable.
        //      IMPORTANT! This in only for debugging to track if and when values in $SESSION->EBGLMS->USER get initially set
        //              or change.
        //      To use: $debug_user = $SESSION->EBGLMS->DEBUG->user;
        // Lenovo ********************************************************************************
        // $debug->user = debug_setup_user($SESSION->EBGLMS->USER);  // 04/26/18 - RF - ***here***

        // Lenovo ********************************************************************************
        // Set the fully qualified log file names.
        // Lenovo ********************************************************************************
        list($debug->debug_fqlog, $debug->debug_fqdetailed) = debug_set_fqlogfilenames();

        // Lenovo ********************************************************************************
        // Testing of debug_logmessage...Testing of debug_logmessage...Testing of debug_logmessage...Testing of debug_logmessage...
        //      Testing of debug_logmessage...Testing of debug_logmessage...Testing of debug_logmessage...Testing of debug_logmessage...
        // Lenovo ********************************************************************************
        // Always output standard header information.
        debug_logmessage_header('begin');

        return $SESSION->EBGLMS->DEBUG;

    } else {
        // $SESSION->EBGLMS->DEBUG->PHPLOG->backtrace = format_backtrace(debug_backtrace(), true);      // 07/19/18
        // print_r("SESSION->EBGLMS->DEBUG follows");
        // var_dump($SESSION->EBGLMS->DEBUG);
        // die;
        // print_r("debug_start: SESSION->EBGLMS->DEBUG =IS= set. Called from ".debug_backtrace()[1]['function'].".<br />");

        $SESSION->EBGLMS->DEBUG->information = 'In debug_start() - DEBUG is already set.';

        // Go ahead and update the PHP log information for this call.
        if (isset($SESSION->EBGLMS->DEBUG->PHPLOG)) {
            $today = new DateTime("now", core_date::get_user_timezone_object());
            $SESSION->EBGLMS->DEBUG->PHPLOG->backtrace = format_backtrace(debug_backtrace(), true);     // true generates text; false generates HTML.
            $SESSION->EBGLMS->DEBUG->PHPLOG->timeofevent = $today->format('H:i:s.u');
        }

        $SESSION->EBGLMS->DEBUG->information = 'In debug_start() - Returning...';

        return $SESSION->EBGLMS->DEBUG;
    }

}

// Lenovo ********************************************************************************
// Start series of small get/put functions.
//      Note: Might not be one for each debug setting.
// Lenovo ********************************************************************************
/**
 * Load all the PHP include files. Default option is to only return the includes with '/local/swtc' in the path. If option
 *          is 'all', load ALL the include files.
 */
function debug_load_includes($option = 'swtc') {
    global $CFG, $SESSION;

    $path = '/local/swtc';        // The include path string to search for (below).
    $includes = array();            // Local variable to hold included files.
    $swtc_includes = array();                // Local variable to hold swtc include files.

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $includes = $SESSION->EBGLMS->DEBUG->includes;
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG)) {
        return false;   // EBGLMS is not set yet.
    } else {

        // Since both cases require it, do it now.
        $all_includes = get_included_files();

        switch ($option) {
            case 'swtc':
                // Search for path in array of included files.
                // $swtc_includes = array_search($path, $included);
                foreach ($all_includes as $filename) {
                    if (strpos($filename, $path) !== false) {
                        $swtc_includes[] = $filename;
                    }
                }

                $includes = $swtc_includes;
                break;

            case 'all':
                $includes = $all_includes;
                break;

            default:
                // unknown type
        }

        $SESSION->EBGLMS->DEBUG->includes = $includes;
        return;

    }
}

/**
 * Check if user is an authorized users that can debug. Return true if yes; false if no.
 *
 * History:
 *
 * 07/19/18 - Check for specific list of userids that are authorized to do debugging. If userid is not one of those authorized, return false.
 * 08/06/18 - Added "override" user to debug_authorized_user so that a valid username is not required.
 * 09/06/18 - Enable debugging in the case of any authorized user using "loggedonas" another user.
 * 11/07/18 - Added more authorized users (new PremierSupport test users).
 * 11/27/18 - Added more authorized users (if called from cron, "admin" is account name) and ServiceDelivery users.
 *
 */
function debug_authorized_user($username) {
    global $CFG, $USER, $DB, $SESSION;

    //****************************************************************************************
	// Local variables begin...
    // Debug users:
    $debug_usernames = array('rfrench@lenovo.com',
                            'test-aspmain-stud',
                            'test-maintech-stud',
                            'test-serviceprovider-stud',
                            'test-premiersupport-stud1',        // PremierSupport
                            'test-premiersupport-stud2',
                            'test-premiersupport-mgr1',
                            'test-premiersupport-mgr2',
                            'test-premiersupport-admin1',
                            'test-premiersupport-admin2',
							'test-psus1-stud1',
							'test-psus1-mgr1',
							'test-psus1-admin1',
                            'test-psus1-stud1',
                            'test-psus1-mgr1',
                            'test-psus1-admin1',
                            'test-psus-geoadmin1',
                            'test-ps-siteadmin1',
                            'test-psca1-mgr1',
                            'test-sdus1-stud1',
                            'test-sdus1-mgr1',
                            'test-sdus1-admin1',
                            'test-sd-siteadmin1',
                            'test-sdus-geoadmin1',
                            'test-sdem1-stud1',
                            'test-sdem1-mgr1',
                            'test-sdem1-admin1',
                            'test-sdem-geoadmin1',
                            'test-lenovo-stud',                         // Lenovo
                            'test-lenovo-admin',                         // Lenovo
                            'test-lenovo-siteadmin',                         // Lenovo
                            'test-ibm-stud',                        // IBM
                            // 'admin',
                            'override'
                            );

    // Local variables end...
	//****************************************************************************************
    // Enable debugging in the case of any authorized user using "loggedonas" another user.
    //              If using "loggedonas", $USER->realuser is set to the id of the user doing the debugging. If so,
    //              get the user information for that user (to see if that user is in the list).
    if (isset($USER->realuser)) {
        // Get all the user information based on the userid passed in.
        // Note: '*' returns all fields (normally not needed).
        // $existinguser = $DB->get_record('user', array('id'=>$USER->realuser));
        $existinguser = core_user::get_user($USER->realuser);
        $username = $existinguser->username;
    }

    if (in_array($username, $debug_usernames)) {
        return true;    // User authorized to debug.
    } else {
        return false;  // User not authorized to debug.
    }
}

/**
 * Enable PHPLOG options. Uses format_backtrace which is defined in /lib/setuplib.php.
 *
 * @param object    The debug object.
 *
 * @returns object  The PHPLOG object.
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/20/18 - Initial writing; remember to first call debug_setup_phplog to create the object.
 *
 **/
function debug_enable_phplog($debug, $text = null) {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // No need to setup the second-level $DEBUG global variable (it is passed to the function).
    // Setup the third-level $PHPLOG global variable.
    //      To use: $SESSION->EBGLMS->DEBUG->PHPLOG;
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG->PHPLOG)) {
        $phplog = debug_setup_phplog();
        $debug->PHPLOG = $phplog;

    } else {
        $phplog = $SESSION->EBGLMS->DEBUG->PHPLOG;
    }

    if ($text !== null) {
        $debug->information = $text;
    }

    $today = new DateTime("now", core_date::get_user_timezone_object());
    // $log->backtrace = format_backtrace(debug_backtrace(), true);     // true generates text; false generates HTML.
    $phplog->backtrace = format_backtrace(debug_backtrace(), false);     // true generates text; false generates HTML.
    // $phplog->backtrace = debug_print_backtrace();     // true generates text; false generates HTML.
    $phplog->timeofevent = $today->format('H:i:s.u');

    return $phplog;

}

/**
 * Disable PHPLOG options.
 *
 * @param N/A
 *
 * @returns N/A
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/20/18 - Initial writing; calls unset on the phplog object.
 *
 **/
function debug_disable_phplog() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
        // Setup the third-level $PHPLOG global variable.
        //      To use: $SESSION->EBGLMS->DEBUG->PHPLOG
        // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG->PHPLOG)) {
        return false;       // PHPLOG is not set yet.
    } else {
        $phplog = $SESSION->EBGLMS->DEBUG->PHPLOG;
        unset($phplog);
        $SESSION->EBGLMS->DEBUG->PHPLOG = null;
        return;
    }
}

/**
 * Get username (returns $username). REQUIRES $SESSION->EBGLMS->DEBUG to be set.
 */
function swtc_get_debug_username() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG)) {
        return false;   // EBGLMS is not set yet.
    } else {
        $debug = $SESSION->EBGLMS->DEBUG;
        // $debug->information = 'In swtc_get_debug_username().';

        if (isset($debug->username)) {
            return $debug->username;
        } else {
            debug_logmessage("Debug username is not set.", 'display');
            return false;
        }
    }

}

/**
 * Use to query if debugging has been started (returns value of isset($SESSION->EBGLMS->DEBUG)); true = debug is running;
 *                  false = not running.
 *      Example: if ($debug->running())...
 */
function debug_running() {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Return the second-level $DEBUG global variable (if set).
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    return (isset($SESSION->EBGLMS->DEBUG));
}

/**
 * Get the log file name (gets either $debug_fqlog or $debug_fqdetailed). REQUIRES $SESSION->EBGLMS->DEBUG to be set.
 */
function swtc_get_debug_logfilename($logfile) {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG)) {
        return false;   // EBGLMS is not set yet.
    } else {
        $debug = $SESSION->EBGLMS->DEBUG;
        // $debug->information = 'In swtc_get_debug_logfilename().';

        if ($logfile === 'log') {
            return $debug->debug_fqlog;
        } else {
            return $debug->debug_fqdetailed;
        }
    }

}

/**
 * Set the fully qualified log file names (sets either $debug_fqlog or $debug_fqdetailed). REQUIRES $SESSION->EBGLMS->DEBUG to be set.
 *
 * @param N/A
 *
 * @returns N/A
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/21/18 - Experimenting with text output (to .log file) or HTML output (to .html file).
 * 05/15/18 - Changed log file name from "details" to "detailed" to align with flag that is passed.
 * 04/11/19 - (Major change) In preparation for moving to Motorola hosting, , moving debugging logs from
 *                      /moodledata/repository/debug/* to /dcgdata/debug/*.
 * 10/11/19 - Changed debug log file location to be off of $CFG->dataroot.
 *
 **/
function debug_set_fqlogfilenames() {
    // global $CFG, $SESSION, $OUTPUT;
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    //  Note: No need for reference here.
    // Lenovo ********************************************************************************

    // Lenovo ********************************************************************************
    // Variables begin...
    $fqlog = "";
    $fqdetailed = "";

    // Lenovo ********************************************************************************
    // $PATHS holds all important file location information. Even though this information may only be used in a few functions,
    // putting information in a global variable makes it easier to maintain.
    // Remember that the pathing is referenced off of the $CFG->dataroot/swtc/sb folder (see above).
    //
    // The following folder structure must be created under /moodledata/repository/ ($CFG->dataroot/repository/):
    //      debug
    //      debug/logs
    //
    //      AND the "debug" repository must be created in LMS (pointing to $CFG->dataroot/repository/debug).
    //
    //      AND Linux filesystem ownership and group should be set to "root:root" for all folders and sub-folders.
    //
    // Lenovo ********************************************************************************
    // To use: $PATHS['debug_logs']
    // $PATHS = array('debug_folder' => "/lenovo_data/debug/",
    //                            'debug_log_folder' => "/lenovo_data/debug/logs/",
    //                            );
    // Lenovo ********************************************************************************.
    // 10/11/19 - Changed debug log file location to be off of $CFG->dataroot.
    // Lenovo ********************************************************************************.
    $PATHS = array('debug_folder' => $CFG->dataroot . '/repository/debug/',
                                'debug_log_folder' => $CFG->dataroot . '/repository/debug/logs/',
                                );

    // All log files, like error logs, are written to $PATHS['debug_log_folder'].
    //      Notes:
    //          $CFG->tempdir is configured to be "/moodledata/temp", so no need to send that part of the path when calling make_temp_directory.
    //          "Regular" debug log file is named "debug_yyyymmdd.log".
    //          Detailed debug log file is named "debug_yyyymmdd.detail.log".
    //          make_temp_directory is defined in /lib/setuplib.php.
    //          Example call (from csvlib_class.php): $filename = make_temp_directory('csvimport/'.$type.'/'.$USER->id);
    // $logpath =  "swtc" . $dsep . "sb" . $dsep . "logs";
    $logpath = $PATHS['debug_log_folder'];

    // $debug_ext = date("Ymd").'.log';                // All file extensions will be in the form of 'yyyymmdd.log'.
    $debug_ext = date("Ymd").'.html';                // 04/21/18 - Experimenting...
    // $debug_ext = date("Ymd").'.txt';                // 04/21/18 - Experimenting some more.

    // $detail_ext = date("Ymd").'.details.log';    // All detail file extensions will be in the form of 'yyyymmdd.details.log'.
    $detail_ext = date("Ymd").'.detailed.html';    // 04/21/18 - Experimenting...
    // $detail_ext = date("Ymd").'.details.txt';    // 04/21/18 - Experimenting some more.

    $debug_filename = "debug_".$debug_ext;       // "Regular" debug log file is named "debug_yyyymmdd.log".
    $detail_filename = "debug_".$detail_ext;       // Detailed debug log file is named "debug_yyyymmdd.details.log".

    // Variables end...
    // Lenovo ********************************************************************************
    // Check that $logpath was correctly created.
    if (file_exists($logpath) && is_dir($logpath)) {
        // Log, and other, file information.
        $fqlog = $logpath . $debug_filename;                   // Regular log path and log file name.
        // set_logfilename('log', $logpath . $debug_filename);

        $fqdetailed = $logpath . $detail_filename;                   // Detailed log path and log file name.
        // set_logfilename('detailed', $logpath . $detail_filename);
    } else {
        // TODO: How to display error? Like purgecache?
        // echo $OUTPUT->notification(get_string('success'), 'notifysuccess');
        debug_logmessage("Lenovo ********************************************************************************.", 'display');
        debug_logmessage("Error opening folder ($logpath). Exiting.", 'display');
        debug_logmessage("Lenovo ********************************************************************************.", 'display');
        // die;
        return;
    }

    return array($fqlog, $fqdetailed);
}

/**
 * Write an array (using file_put_contents) of text strings to the given stream (wherever and whatever it may be).
 *              REQUIRES $SESSION->EBGLMS->DEBUG to be set.
 *
 * @param array $messages array of text messages to be written
 * @param string $option where to write the text (either "logfile", "detailed", "both", "display", or "all").
 * @param string $flags either empty (where FILE_APPEND will be set) or 'create' to create a new log file.
 */
 /**
 * Version details
 *
 * History:
 *
 *	09/06/17 - Initial writing; used to write text to a log file (for regular output or debugging). Since I'm not sure what will be the final debugging
 *                      and log file write functions this will use, wrote this to capture ALL output (so, if things need to change in the future,
 *                      only this function needs to change).
 * 12/27/17 - Added $option as second parameter (currently either 'screen', 'logfile', 'detailed', 'both', or 'all').
 * 01/12/18 - Removing $stream parameter (since the log file handles are now in $EBG_DEBUG).
 * 04/13/18 - May be adding flock around the writing of data (due to session constraints). To cut down on the overhead,
 *                      now takes just one line or an array of lines to write. Array format is "text option".
 * 04/16/18 - Text should now be format as HTML (nothing to do here; must put HTML tags in every string that may require it).
 * 04/21/18 - Changing back to TEXT format; adds eol character to end of each line in $messages.
 * 06/03/18 - Added timestamp at the start of each message.
 *
 **/
function debug_logmessage($messages, $option, $flags = null) {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    if (!isset($SESSION->EBGLMS->DEBUG)) {
        return false;   // EBGLMS is not set yet.
    } else {
        $debug = $SESSION->EBGLMS->DEBUG;

        // $debug->information = 'In debug_logmessage().';      // Do not over write information from calling function.
        // REMEMBER - don't trace here - trace in the calling functions.

         // Lenovo ********************************************************************************
        // Variables begin...
        $tmp_messages = array();

        //****************************************************************************************
        // Assign the correct end of line character:
        //      If writing to either of the log files (or both for that matter), the end of line character is "\n" (or "<br />").
        //      If writing to the screen, the end of line character is PHP_EOL.
        //****************************************************************************************
        if (($option === 'logfile' ) OR ($option === 'detailed') OR ($option === 'both')) {
            // 05/16/18 - testing - using explode and '\n' to see if output formats better. For now, overlay $messages.
            // $messages = explode('\n', $messages);
            $eol = "\n";
            // $eol = "<br />";
        } else {
            $eol = PHP_EOL;
        }

        $fqlog = $debug->debug_fqlog;
        $fqdetailed = $debug->debug_fqdetailed;

        // Set flags. If flags is already set, it can only be 'create'.
        if (!isset($flags)) {
            $flags = FILE_APPEND | LOCK_EX;
        } else {
            // Leave flags set at whatever value it has.
        }

        // Local variables end...
        //****************************************************************************************

        //****************************************************************************************
        // If writing to either of the log files (or both for that matter), the end of line character is "\n".
        //      Note: If writing to the screen, loop through messages and add a PHP_EOL and the end of each line.
        //****************************************************************************************
        // If $messages is an array, loop through adding the $eol.
        if (is_array($messages)) {
            foreach ($messages as $message) {
                // echo("message ==>:".$message."<== message");
                // 06/03/18 - Added timestamp at the start of each message.
                $tmp_messages[] = swtc_timestamp() . " " . $message . $eol;
            }

            // Assign back to messages.
            $messages = $tmp_messages;
            unset($tmp_messages);
        } else {
            // $messages is just one text string. Place $eol character at the end and move on.
            // 06/03/18 - Added timestamp at the start of each message.
            $messages = swtc_timestamp() . " " . $messages . $eol;
        }


        // Lenovo ********************************************************************************
        // For each filesystem option, remember to flock and unlock before using file_put_contents.
        //      Note: Use append option file_put_contents($file, print_r($array, true), FILE_APPEND)
        //     Per the PHP documentation, file_put_contents is function is identical to calling fopen(), fwrite() and fclose()
        //         successively to write data to a file.
        //
        // From PHP documentation:
        //  flags
        //  The value of flags can be any combination of the following flags (with some restrictions), joined with the binary OR (|) operator.
        //      FILE_USE_INCLUDE_PATH − Search for filename in the include directory.
        //      FILE_APPEND − If file filename already exists, append the data to the file instead of overwriting it.
        //      LOCK_EX − Acquire an exclusive lock on the file while proceeding to the writing.
        //      FILE_TEXT − data is written in text mode. This flag cannot be used with FILE_BINARY. This flag is only available since PHP 6.
        //      FILE_BINARY − data will be written in binary mode. This is the default setting and cannot be used with FILE_TEXT.
        //          This flag is only available since PHP 6
        //
        // Lenovo ********************************************************************************

        switch ($option) {
            case 'logfile':
                // Will write to $fqlog.
                if ($flags === 'create') {
                     // Create a new file.
                    file_put_contents($fqlog, $messages);
                    return;
                } else {
                   file_put_contents($fqlog, $messages, $flags);
                }
                break;

            case 'detailed':
                // Will write to $fqdetailed.
                if ($flags === 'create') {
                    // Create a new file.
                    file_put_contents($fqdetailed, $messages);
                } else {
                    file_put_contents($fqdetailed, $messages, $flags);
                }
                break;

            case 'both':
                // Will write to $fqlog and $fqdetailed.
                if ($flags === 'create') {
                    // Create a new file.
                    file_put_contents($fqlog, $messages);
                    file_put_contents($fqdetailed, $messages);
                } else {
                    file_put_contents($fqlog, $messages, $flags);
                    file_put_contents($fqdetailed, $messages, $flags);
                }
                break;

            case 'display':
                // 04/26/18: TODO: Not sure how to print to screen.
                // Note: Since we are printing in a web browser (i.e. HTML), to get a line feed, use "\n" at the end of the line in the calling routine.
                // Will output to STDOUT.
                // cli_write($text.PHP_EOL);
                // $text = $text.PHP_EOL;
                // print_r($text);
                // print_r($text.PHP_EOL);
                // print_r($text."\r\n");
                // print_r($text."<br />");  // WORKS!
                // print_r($messages."\n");  // WORKS!
                // print_r($messages);  // WORKS!
                // cli_writeln($text);
                break;

            case 'all':
                // Will write to both debug logs and STDOUT.
                if ($flags === 'create') {
                    // Create a new file.
                    file_put_contents($fqlog, $messages);
                    file_put_contents($fqdetailed, $messages);
                } else {
                    file_put_contents($fqlog, $messages, $flags);
                    file_put_contents($fqdetailed, $messages, $flags);
                }

                // TODO: Not sure how to print to screen. 04/23/18 - RF - Experimenting...
                print_r($messages);

                break;

            default:
                // unknown type
        }
    }

    return;

}

/**
 * Open, or create, a file. Once created, set owner and group to "apache".
 *
 * @param             $fq_file      Fully qualified path to file to open.
 * @param             $options     Options to use when opening file (for example, "+a", "w+").
 *
 * @return             $fp              File pointer of file that was opened.
 */
/**
 * Version details
 *
 * History:
 *
 *	02/27/18 - Initial writing.
 **/
function debug_util_fileopen($fq_file, $options) {
    global $CFG, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...

    // File pointer.
    $fp = 0;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    // $debug = swtc_get_debug();
    $debug = $SESSION->EBGLMS->DEBUG;
    // $debug->information = 'In debug_util_fileopen().';

    debug_enable_phplog($debug);
    // If set, immediately start backtrace.
    // if ($debug->PHPLOG->enable) {
    //     $today = new DateTime("now", core_date::get_user_timezone_object());
    //     $debug->PHPLOG->backtrace = format_backtrace(debug_backtrace(), true);     // true generates text; false generates HTML.
    //     $debug->PHPLOG->timeofevent = $today->format('H:i:s.u');
    // }

    // Variables end...
    // Lenovo ********************************************************************************

    // Lenovo ********************************************************************************
    // Always output standard header information.
    // Lenovo ********************************************************************************
    // echo debug_backtrace()[1]['function']; // 04/11/18 - RF - testing...
    debug_logmessage("Lenovo ********************************************************************************.", 'display');
    debug_logmessage("Entering debug_util_fileopen. ==6.debug_util_fileopen.enter===.", 'display');

    // Open the file. Possible options are:
    //      'a+' = Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
    $fp = fopen($fq_file, $options);

    if ($fp) {
        // If current user is "root", change owner of log file to "apache" process.
        //      See swtc_sb_open_logfiles for more information.
        //
        // if ($debug->username === 'root') {
        if (swtc_get_debug_username() === 'root') {
            chown($fq_file, 'apache');
            chgrp($fq_file, 'apache');
        }
    } else {
        debug_logmessage("Error creating or opening file $fq_file.", 'display');
    }

    // Lenovo ********************************************************************************
    // Always output standard header information.
    // Lenovo ********************************************************************************
    debug_logmessage("Leaving debug_util_fileopen. ==6.debug_util_fileopen.exit===.", 'display');
    debug_logmessage("Log filename is :$fq_file.==6.debug_util_fileopen.exit===.", 'display');
    // debug_logmessage("Log filename is :$fq_file. fp follows.==6.debug_util_fileopen.exit===.", 'display');
    debug_logmessage("Lenovo ********************************************************************************.", 'display');

    return $fp;
}

/**
 * Print log headers ("begin") or footer ("end").
 *
 * @param $option   Either "begin" or "end".
 *
 * @return ***TO DO
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/16/18 - Initial writing.
 *
 **/
function debug_logmessage_header($option) {
    global $CFG, $OUTPUT, $PAGE, $SESSION;

    // Lenovo ********************************************************************************
    // Variables begin...
    $messages = array();        // Temporary array to queue messages to be written to log file locations.
    $debug = null;

    // Lenovo ********************************************************************************
    // Setup the second-level $DEBUG global variable.
    //      To use: $debug = $SESSION->EBGLMS->DEBUG;
    // Lenovo ********************************************************************************
    $debug = $SESSION->EBGLMS->DEBUG;

    // Variables end...
    // Lenovo ********************************************************************************

    // Setup date and time variables.
    $da = date("F j, Y, g:i a");
    $today = new DateTime("now", core_date::get_user_timezone_object());

    switch ($option) {
        case 'begin':
            // echo $OUTPUT->header() = $messages[];
            // $messages[] = $header;
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***";
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Starting debugging ===1.debug_start_logfile=== $da ";
            // $messages[] = "Timestamp is :$today.==1.debug_start===.";
            $messages[] = "Timestamp is :" .$today->format('H:i:s.u').".==1.debug_start===.";
            // $messages[] = "Time start is :$time_begin.==1.debug_start===.";
            // $messages[] = "Log filename is :$debug->debug_fqlog.==1.debug_start===.";
            $messages[] = "Log filename is :". swtc_get_debug_logfilename('log') .".==1.debug_start===.";
            $messages[] = "Detailed log filename is :". swtc_get_debug_logfilename('detailed') .".==1.debug_start===.";
            // Write the above header text to 'logfile'. Will create a new file (instead of appending to it).
            debug_logmessage($messages, 'logfile', 'create');
            unset($messages);

            // Use print_r for now.
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***";
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Starting debugging ===1.debug_start_detailed=== $da ";
            $messages[] = "About to print SESSION->EBGLMS->DEBUG. ==1.debug_start===.";
            $messages[] = print_r($debug, true);
            $messages[] = "Finished printing SESSION->EBGLMS->DEBUG. ==1.debug_start===.";
            //  $messages[] = "About to print SESSION->EBGLMS->DEBUG using var_dump. ==1.debug_start===.";
            //  $messages[] = var_dump(swtc_get_debug());
            //  $messages[] = "Finished printing SESSION->EBGLMS->DEBUG using var_dump. ==1.debug_start===.";
            //  $messages[] = "About to print SESSION->EBGLMS->DEBUG using var_export. ==1.debug_start===.";
            //  $messages[] = var_export(swtc_get_debug(), true);
            //  $messages[] = "Finished printing SESSION->EBGLMS->DEBUG using var_export. ==1.debug_start===.";
            // $messages[] = "About to print SESSION->EBGLMS->DEBUG using print_object. ==1.debug_start===.";
            // $messages[] = print_object(swtc_get_debug(), true);       // Note: Also goes to screen.
            // $messages[] = "Finished printing SESSION->EBGLMS->DEBUG using print_object. ==1.debug_start===.";
            // Write the above header text to 'detailed'. Will create a new file (instead of appending to it).
            debug_logmessage($messages, 'detailed', 'create');
            unset($messages);
            break;

        case 'end':
            // Will write to detailed logfile.
            // cli_write($text, $EBG_DEBUG->detailed);
            break;

        default:
            // unknown type
    }


    // Write the header text to both log files. Will create a new file (instead of appending to it).
    // debug_logmessage($messages, 'both', 'create');

    return;

}

/**
 * Display a navigation node and its children.
 *
 * Example:
 *    debug_navigation($PAGE->navigation);
 *    debug_navigation($PAGE->settingsnav);
 *
 * @param navigation_node $node
 * @return void It echo's out.
 */
function debug_navigation(navigation_node $node) {
    $result = debug_navigation_node($node);
	echo "<pre style='margin:2px;padding:5px;border:1px solid #000;background-color:#FFF;'>".join("<br />", $result)."</pre>";
}

/**
 * Recursive function called to build an array of displayable information about the
 * given navigation_node and its children.
 *
 * If you want things echo'd out nicely call debug_navigation instead.
 */
function debug_navigation_node(navigation_node $node, $depth = 0) {
    $str = str_repeat(' ', 2*$depth).'* ['.$node->key.'] '.$node->text;
    if ($node->action instanceof moodle_url) {
        $str .= ' '.$node->action->out_as_local_url();
    } else if (is_object($node->action)) {
        $str .= ' '.get_class($node);
    }

    if ($node->isactive) {
        $str = '<b>'.$str.'</b>';
    }
    if ($node->forceopen) {
        $str = '<i>'.$str.'</i>';
    }
    if ($node->contains_active_node()) {
        $str = '<span style="color:red;">'.$str.'</span>';
    }

    $result = array($str);
    if ($node->has_children()) {
        foreach ($node->children as $child) {
            $result = array_merge($result, debug_navigation_node($child, $depth+1));
        }
    }
    return $result;
}
