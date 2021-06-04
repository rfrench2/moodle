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
 * @subpackage swtc/classes/swtc_debug.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/210 - Initial writing.
 *
 **/
namespace local_swtc;

use stdClass;
use DateTime;
use core_date;

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot . '/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot . '/local/swtc/lib/swtclib.php');

/**
 * Initializes all debugging options.
 *
 *      IMPORTANT!
 *          DO NOT call this class directly. Use $swtc_get_debug from /lib/swtc_userlib.php.
 *
 * @param N/A
 *
 * @return $SESSION->SWTC->DEBUG
 *
 *      Debugging hints and tips:
 *          - The PHP function debug_print_backtrace() prints much more information than the
 *                  Moodle function format_backtrace.
 *          - If experiencing "Exception - Call to undefined function" to functions in swtc plugin,
 *              use the PHP function get_included_files.
 *
 * History:
 *
 * 10/19/20 - Initial writing.
 *
 **/
class swtc_debug {
    /**
     * General purpose field to pass / print anything that is required.
     *
     * @private integer
     */
    private $information;

    /**
     * Anything other than 0 will enable interactive debugging.
     *
     * @private boolean
     */
    private $interactive;

    /**
     * Anything other than 0 will enable preview mode (only display statistics and don't write to either SB servers).
     *
     * @private boolean
     */
    private $preview;

    /**
     * Anything other than 0 will enable print mode (print large datasets to log file). For testing, set prt to true.
     *
     * @private boolean
     */
    private $prt;

    /**
     * Anything other than 0 will run the SQL commands against only one userid.
     *
     * @private boolean
     */
    private $userid;

    /**
     * Anything other than 0 will force a write of the row to the CSV even if the user is found to have the cert.
     *
     * @private boolean
     */
    private $force;

    // Scope: what to debug?
    // 'internal' = SWTC functions in SWTC PHP files only.
    // 'external' = SWTC modified code in other PHP files only.
    // 'all' = Both internal and external.
    /**
     * Scope: what to debug?
     *      'internal' = SWTC functions in SWTC PHP files only.
     *      'external' = SWTC modified code in other PHP files only.
     *      'all' = Both internal and external.
     *
     * @private string
     */
    private $scope;

    /**
     * Regular log filename.
     *
     * Added log files to EBG_DEBUG (both regular and detailed).
     *      IMPORTANT! Since we are now using $SESSION to save variables, we can't open the log files once and
     *          simply pass around the open file handles (since all file resources are disconnected if another
     *          page is loaded). In other words, to write a line of text to the log file we must:
     *              - open the log file and save the open file handle as a local variable.
     *              - write the line of text to the log file.
     *              - close the log file.
     *
     *      Debug log file pointer; can NOT use any more.
     *      $debugging->debug_logfp = 0;
     *
     * @private string
     */
    private $fqlog;

    /**
     * Detialed log filename.
     *
     * @private string
     */
    private $fqdetailed;

    /**
     * Which SB server to use? If  'srv=test' is set on command line (then value is true) and SB test server is used.
     * If not passed on command line (which is the default), the SB production server is used.
     *
     * @private string
     */
    private $sbserver;

    /**
     * Save the current user so that if current user is "root", the function can change the ownership of the
     *      file to the "apache" process (so that files can be written or appended to).
     *
     * @private integer
     */
    private $username;

    /**
     * To hold PHP include files.
     *
     * @private array
     */
    private $includes;

    /**
     * To hold a temporary counter value.
     *
     * @private array
     */
    private $counter;

    /**
     * To hold the PHP log.
     *
     * @private array
     */
    private $phplog;

    /** @var integer If set, enable debugging of all events. */
    private $events;

    public function __construct() {

        // SWTC ********************************************************************************.
        // Load all the DEBUG default vales.
        // SWTC ********************************************************************************.
        $this->information = '';
        $this->interactive = false;
        $this->preview = false;
        $this->prt = true;
        $this->userid = false;
        $this->force = false;
        $this->scope = 'internal';
        $this->fqlog = null;
        $this->fqdetailed = null;
        $this->sbserver = 'prod';
        $this->username = '';
        $this->includes = array();
        $this->counter = null;
        $this->phplog = null;
        $this->events = null;

        // SWTC ********************************************************************************.
        // Setup the SWTC->DEBUG->phplog private variable.
        // SWTC ********************************************************************************.
        $this->phplog = null;
    }

    /**
     * All Setter methods for all properties.
     *
     * Setter methods:
     * @param $value
     * @return N/A
     *
     * History:
     *
     * 03/03/21 - Initial writing.
     *
     **/

    /**
     * Set events to enable event debugging throughout.
     *
     * History
     *
     * 03/09/2021 - Initial writing.
     *
     */
    public function set_events() {
        $this->events = 1;
    }

    public function set_username() {
        // SWTC ********************************************************************************.
        // Save the current user so that if current user is "root", the function can change the ownership of the
        // file to the "apache" process (so that files can be written or appended to).
        //
        // Notes:
        // This should always be included after any fopen, file_put_contents, or after any call that opens a file
        // that potentially both root and apache would write to. This can be invoked using either the "root"
        // user (if running from a putty session) or "apache" (if running from Moodle).
        // If invoked using "apache" AND the log files are created by "apache", all is well (because "root" will
        // append to the log files with no errors). If invoked using "apache" AND the log files were created
        // by "root", the web process will FAIL ("apache" will not be able to append to a log file created by "root").
        // If invoked using "root", all is well (because "root" will either create or append to the log files with no
        // errors).
        //
        // Call posix_getpwuid(posix_geteuid()) to find the user account currently running the dcgsbautouser script
        // (normally either "root" or "apache").
        //
        // Typical logic would be similar to the following:
        //
        // if ($debugging->username === 'root') {
        // chown($debugging->fqlog, 'apache');
        // }
        // SWTC ********************************************************************************.
        // 04/28/21 - Windows 10 does not support posix.
        // $currentuser = posix_getpwuid(posix_geteuid());
        // $this->username = $currentuser['name'];
    }

    public function set_fqlog($userid) {
        global $CFG;

        // SWTC ********************************************************************************.
        // $paths holds all important file location information. Even though this information may only be used in a few functions,
        // putting information in a global variable makes it easier to maintain.
        // Remember that the pathing is referenced off of the $CFG->dataroot/swtc/sb folder (see above).
        //
        // The following folder structure must be created under /moodledata/repository/ ($CFG->dataroot/repository/):
        // debug
        // debug/logs
        //
        // AND the "debug" repository must be created in LMS (pointing to $CFG->dataroot/repository/debug).
        //
        // AND Linux filesystem ownership and group should be set to "root:root" for all folders and sub-folders.
        //
        // SWTC ********************************************************************************.
        // To use: $paths['debug_logs']
        // $paths = array('debug_folder' => "/lenovo_data/debug/",
        // 'debug_log_folder' => "/lenovo_data/debug/logs/",
        // );
        // SWTC ********************************************************************************.
        // 10/11/19 - Changed debug log file location to be off of $CFG->dataroot.
        // SWTC ********************************************************************************.
        $paths = array('debug_folder' => $CFG->dataroot . '/repository/debug/',
                  'debug_log_folder' => $CFG->dataroot . '/repository/debug/logs/');

        // All log files, like error logs, are written to $paths['debug_log_folder'].
        // Notes:
        // $CFG->tempdir is configured to be "/moodledata/temp", so no need to send that
        // part of the path when calling make_temp_directory.
        // "Regular" debug log file is named "debug_yyyymmdd.log".
        // Detailed debug log file is named "debug_yyyymmdd.detail.log".
        // make_temp_directory is defined in /lib/setuplib.php.
        // Example call (from csvlib_class.php): $filename = make_temp_directory('csvimport/'.$type.'/'.$USER->id);
        // $logpath =  "swtc" . $dsep . "sb" . $dsep . "logs".
        $logpath = $paths['debug_log_folder'];
        // Regular debug log file is named "debug_yyyymmdd.log".
        $debugfilename = "debug_" . date("Ymd") . "_" . $userid . '.html';

        // SWTC ********************************************************************************.
        // Check that $logpath was correctly created.
        if (file_exists($logpath) && is_dir($logpath)) {
            // Log, and other, file information.
            $this->fqlog = $logpath . $debugfilename;
        } else {
            // TODO: How to display error? Like purgecache?
            $this->logmessage(get_string('swtc_debug', 'local_swtc'), 'display');
            $this->logmessage("Error opening folder ($logpath). Exiting.", 'display');
            $this->logmessage(get_string('swtc_debug', 'local_swtc'), 'display');
        }
    }

    public function set_fqdetailed($userid) {
        global $CFG;

        // SWTC ********************************************************************************.
        // $paths holds all important file location information. Even though this information may only be used in a few functions,
        // putting information in a global variable makes it easier to maintain.
        // Remember that the pathing is referenced off of the $CFG->dataroot/swtc/sb folder (see above).
        //
        // The following folder structure must be created under /moodledata/repository/ ($CFG->dataroot/repository/):
        // debug
        // debug/logs
        //
        // AND the "debug" repository must be created in LMS (pointing to $CFG->dataroot/repository/debug).
        //
        // AND Linux filesystem ownership and group should be set to "root:root" for all folders and sub-folders.
        //
        // SWTC ********************************************************************************.
        // To use: $paths['debug_logs']
        // $paths = array('debug_folder' => "/lenovo_data/debug/",
        // 'debug_log_folder' => "/lenovo_data/debug/logs/",
        // );
        // SWTC ********************************************************************************.
        //
        // SWTC ********************************************************************************.
        $paths = array('debug_folder' => $CFG->dataroot . '/repository/debug/',
                      'debug_log_folder' => $CFG->dataroot . '/repository/debug/logs/');

        // All log files, like error logs, are written to $paths['debug_log_folder'].
        // Notes:
        // $CFG->tempdir is configured to be "/moodledata/temp", so no need to send that
        // part of the path when calling make_temp_directory.
        // "Regular" debug log file is named "debug_yyyymmdd.log".
        // Detailed debug log file is named "debug_yyyymmdd.detail.log".
        // make_temp_directory is defined in /lib/setuplib.php.
        // Example call (from csvlib_class.php): $filename = make_temp_directory('csvimport/'.$type.'/'.$USER->id).
        $logpath = $paths['debug_log_folder'];
        // Detailed debug log file is named "debug_yyyymmdd.details.log".
        $detailfilename = "debug_" . date("Ymd") . "_" . $userid . '.detailed.html';

        // SWTC ********************************************************************************.***.
        // Check that $logpath was correctly created.
        if (file_exists($logpath) && is_dir($logpath)) {
            // Log, and other, file information.
            $this->fqdetailed = $logpath . $detailfilename;
        } else {
            // TODO: How to display error? Like purgecache?
            $this->logmessage(get_string('swtc_debug', 'local_swtc'), 'display');
            $this->logmessage("Error opening folder ($logpath). Exiting.", 'display');
            $this->logmessage(get_string('swtc_debug', 'local_swtc'), 'display');
        }
    }

    /**
     * All Getter methods for all properties.
     *
     * Getter methods:
     * @param N/A
     * @return value
     *
     * History:
     *
     * 03/03/21 - Initial writing.
     *
     **/
    public function get_debug() {
        if (isset($this)) {
            return $this;
        } else {
            return set_debug();
        }
    }

    public function get_counter() {
        return $this->counter;
    }

    public function get_events() {
        return $this->events;
    }

    /*
     * Get the fqlog log file name. REQUIRES $SESSION->SWTC->DEBUG to be set.
     **/
    public function get_fqlog() {
        return $this->fqlog;
    }

    /*
     * Get the fqdetailed log file name. REQUIRES $SESSION->SWTC->DEBUG to be set.
     **/
    public function get_fqdetailed() {
        return $this->fqdetailed;
    }

    /**
     * Print log headers ("begin") or footer ("end").
     *
     * @param $option   Either "begin" or "end".
     *
     * @return N/A
     *
     * History:
     *
     * 10/21/20 - Initial writing.
     *
     **/
    public function logmessage_header($option) {

        $messages = array();        // Temporary array to queue messages to be written to log file locations.

        // SWTC ********************************************************************************.
        // Setup the second-level $DEBUG global variable.
        // To use: $debug = $this->get_debug();
        // SWTC ********************************************************************************.
        $debug = $this->get_debug();

        // Setup date and time variables.
        $da = date("F j, Y, g:i a");
        $today = new DateTime("now", core_date::get_user_timezone_object());

        switch ($option) {
            case 'begin':
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***";
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Starting debugging ===debug_start_logfile=== $da ";
                $messages[] = "Timestamp is :" .$today->format('H:i:s.u').".==1.debug_start===.";
                $messages[] = "Log filename is :". $debug->get_fqlog() .".==debug_start===.";
                $messages[] = "Detailed log filename is :". $debug->get_fqdetailed() .".==debug_start===.";
                // Write the above header text to 'logfile'. Will create a new file (instead of appending to it).
                $this->logmessage($messages, 'logfile', 'create');
                unset($messages);

                // Use print_r for now.
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***Begin***";
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Starting debugging ===1.debug_start_detailed=== $da ";
                $messages[] = "About to print SESSION->SWTC->DEBUG. ==1.debug_start===.";
                $messages[] = print_r($debug, true);
                $messages[] = "Finished printing SESSION->SWTC->DEBUG. ==1.debug_start===.";
                // Write the above header text to 'detailed'. Will create a new file (instead of appending to it).
                $this->logmessage($messages, 'detailed', 'create');
                unset($messages);
                break;

            case 'end':
                // Will write to detailed logfile.
                break;

            default:
                // Unknown type.
        }

        return;

    }

    /**
     * Write an array (using file_put_contents) of text strings to the given stream (wherever and whatever it may be).
     *              REQUIRES $SESSION->SWTC->DEBUG to be set.
     *
     * @param array $messages array of text messages to be written
     * @param string $option where to write the text (either "logfile", "detailed", "both", "display", or "all").
     * @param string $flags either empty (where FILE_APPEND will be set) or 'create' to create a new log file.
     *
     * History:
     *
     * 10/14/20 - Initial writing.
     *
     **/
    public function logmessage($messages, $option, $flags = null) {
        global $USER;

        // SWTC ********************************************************************************.
        // Setup the second-level $USER global variable.
        // To use: swtcuser = new swtc_user($USER);
        // SWTC ********************************************************************************.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);

        // SWTC ********************************************************************************.
        // Setup the second-level $DEBUG global variable.
        // To use: $debug = $this->get_debug();
        // SWTC ********************************************************************************.
        $debug = $this->get_debug();

        if (!isset($debug)) {
            return false;   // SWTC is not set yet.
        } else {

            $tmpmessages = array();

            // SWTC ********************************************************************************.
            // Assign the correct end of line character:
            // If writing to either of the log files (or both for that matter), the end of line character is "\n" (or "<br />").
            // If writing to the screen, the end of line character is PHP_EOL.
            // SWTC ********************************************************************************.
            if (($option === 'logfile' ) OR ($option === 'detailed') OR ($option === 'both')) {
                $eol = "\n";
            } else {
                $eol = PHP_EOL;
            }

            $fqlog = $debug->get_fqlog();
            $fqdetailed = $debug->get_fqdetailed();

            // Set flags. If flags is already set, it can only be 'create'.
            if (!isset($flags)) {
                $flags = FILE_APPEND | LOCK_EX;
            }

            // SWTC ********************************************************************************.
            // If writing to either of the log files (or both for that matter), the end of line character is "\n".
            // Note: If writing to the screen, loop through messages and add a PHP_EOL and the end of each line.
            // SWTC ********************************************************************************.
            // If $messages is an array, loop through adding the $eol.
            if (is_array($messages)) {
                foreach ($messages as $message) {
                    $tmpmessages[] = $swtcuser->set_timestamp() . " " . $message . $eol;
                }

                // Assign back to messages.
                $messages = $tmpmessages;
                unset($tmpmessages);
            } else {
                // Messages is just one text string. Place $eol character at the end and move on.
                $tmpmessages[] = $swtcuser->set_timestamp() . " " . $messages . $eol;

                // Assign back to messages.
                $messages = $tmpmessages;
                unset($tmpmessages);
            }

            // SWTC ********************************************************************************.
            // For each filesystem option, remember to flock and unlock before using file_put_contents.
            // Note: Use append option file_put_contents($file, print_r($array, true), FILE_APPEND)
            // Per the PHP documentation, file_put_contents is function is identical to calling fopen(), fwrite() and fclose()
            // successively to write data to a file.
            //
            // From PHP documentation:
            // flags
            // The value of flags can be any combination of the following flags (with some restrictions),
            // joined with the binary OR (|) operator.
            // FILE_USE_INCLUDE_PATH − Search for filename in the include directory.
            // FILE_APPEND − If file filename already exists, append the data to the file instead of overwriting it.
            // LOCK_EX − Acquire an exclusive lock on the file while proceeding to the writing.
            // FILE_TEXT − data is written in text mode. This flag cannot be used with FILE_BINARY.
            // This flag is only available since PHP 6.
            // FILE_BINARY − data will be written in binary mode. This is the default setting and cannot be used with FILE_TEXT.
            // This flag is only available since PHP 6
            //
            // SWTC ********************************************************************************.
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
                    // Note: Since we are printing in a web browser (i.e. HTML), to get a line feed,
                    // use "\n" at the end of the line in the calling routine.
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
                    // Unknown type.
            }
        }

        return;

    }

}
