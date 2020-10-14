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
 * A scheduled task for Lenovo DCG Services Education. Used to add cron job to Moodle for the LMS->SB export.
 *
 * @package report_customsql (original)
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 /**
 * Version details
 *
 * @package    swtc_export_to_sb (keep synchronized with swtc_pre_export_to_sb).
 * @subpackage dcgsbautouser
 * @copyright  2018 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	12/12/17 - Initial writing (copied from report_customsql).
 * 12/20/17 - Put in stub to call function in dcgsbautouser.php (to do actual work).
 * 01/22/18 - Changing from SB "test" server to "production" server; added "LEG_ALL" to cli string.
 * 07/04/18 - Added "email" as option (to output users email address in CSV files). This option is for debugging ONLY and MUST NOT
 *                          be used in production (as it will wreck automated processing of the CSV file); also added other missing options.
 * 07/09-10/18 - Changed standard options for a one-time export to "catch up" all Compucom users that have obtained a certification: looking
 *                      back 20 days and adding "force" option so CSV row is written. Will change back to "regular" standard options on
 *                      07/10/18 (after the 6:55am scheduled running of the task).
 * 04/11/19 - In all SB tasks execute functions, added global variables $TS_CERT_STRINGS and $LEG_CERT_STRINGS.
 * 05/23-24/19 - Added TA_ALL for new ThinkAgile certifications; changed standard options for a one-time export "look back" to
 *                          04/15/19 to "catch up" all new Lenovo certifications; changed back to one day.
 *
 **/
 // Lenovo ********************************************************************************
// namespace report_customsql\task;
namespace local_swtc\task;

// DCG run ThinkSystem export to SB task.
class swtc_export_to_sb extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontaskexport', 'local_swtc');
    }

    /**
     * Function to be run periodically according to the moodle cron
     * This function calls dcgsbautouser.php to do the work.
     *
     * Runs based on cron schedule.
     *
     * @return boolean
     */
    public function execute() {
        global $CFG, $DB, $TS_CERT_STRINGS, $LEG_CERT_STRINGS;

        // Lenovo ********************************************************************************
        // Variables begin...
                // Lenovo ********************************************************************************
        // Required for ServiceBench functions (located in /local/swtc/servicebench).
        // Lenovo ********************************************************************************
        require_once($CFG->dirroot.'/local/swtc/servicebench/sblib.php');

        // Set function return value (default value if the export did NOT work).
        $value = false;

        // Variables end...
        // Lenovo ********************************************************************************

        // Setup parameters like invoking from command line. An example would be:
        //      dcgsbautouser -exp -deb -cer=TS_ALL -tim=365 -sbt

        // Lenovo ********************************************************************************
        // History of command line options used:
        //  01/22/18 - GA options used: -exp -deb -cer=TS_ALL,LEG_ALL -tim=1
        //  02/07/18 - The "preview" tasks always use the ServiceBench test server.
        // 07/09-10/18 - Changed standard options for a one-time export to "catch up" all Compucom users that have obtained a certification: looking
        //                      back 20 days and adding "force" option so CSV row is written. Will change back to "regular" standard
        //                      options on 07/10/18.
        // 05/23-24/19 - Added TA_ALL for new ThinkAgile certifications; changed standard options for a one-time export "look back"
        //                      to 04/15/19 to "catch up" all new Lenovo certifications.
        // Lenovo ********************************************************************************
        // $options = '-exp -deb -cer=TS_ALL,LEG_ALL';
        $options = array(
            'import' => false,
            'export' => true,                                   // Set it!
            'debug' => true,                                    // Set it!
            'interactive' => false,
            'userid' => false,
            'certs' => "TS_ALL,TA_ALL,LEG_ALL",     // Set it!
        //    'timeframe' => "400",                     // 02/05/18 - Used for "Day 1".
            'timeframe' => "1",                           // Use another number of days only for testing.
        //    'timeframe' => "20",                            // 07/09-10/18 - Changed to 20. Change back to 1 on 07/10/18.
        //    'timeframe' => "45",                            // 05/23-24/19 - Changed standard options for a one-time export "look back" to
                                                                        //                      04/15/19 to "catch up" all new Lenovo certifications.
            'preview' => false,                             // Actually export to the ServiceBench production server.
            'sbserver' => 'prod',
            'force' => false,                         // Standard option.
        //    'force' => true,                            // 07/09-10/18 - Changed to true. Change back to false on 07/10/18.
            'date' => false,
            'email' => false,
            'help' => false
        );

        // $time_begin = new DateTime("now", core_date::get_user_timezone_object());

        // Lenovo ********************************************************************************
        // At this point, whether called from the command line or from a LMS scheduled task, we must
        //      load the $EBG_DEBUG options and clean-up the options sent.
        // Lenovo ********************************************************************************
        swtc_sb_setup_debugging();

        // Lenovo ********************************************************************************
        // Initialize certification string variables for both ThinkSystem and Legacy certifications.
        // Lenovo ********************************************************************************
        swtc_sb_initalize_strings();

        // Lenovo ********************************************************************************
        // Process all the command line options passed in.
        // Lenovo ********************************************************************************
        list($cert_options, $timeframe) = swtc_sb_process_optiondata($options);

        // Lenovo ********************************************************************************
        // Open the log file. Note: If problems creating or opening logfile, swtc_sb_open_logfile will exit and not return here.
        // Lenovo ********************************************************************************
        swtc_sb_open_logfile('export');

        // Lenovo ********************************************************************************
        // Finish printing header information.
        // Lenovo ********************************************************************************
        swtc_sb_log_header('begin', $options, $cert_options);

        // Lenovo ********************************************************************************
        // Initialize all vars related to user profile information.
        // Lenovo ********************************************************************************
        swtc_sb_initalize_profile();

        // Lenovo ********************************************************************************
        // Open the export file.
        // Lenovo ********************************************************************************
        $expdata = swtc_sb_open_export_csvfile();

        // sql_getcerts returns ALL the certs, or just the certs passed in on the command line, for ALL the users in the timeframe given.
        $certdata = swtc_sb_sql_getcerts($cert_options, $timeframe);

        // Even if zero certifications were found, we still need to continue (still must write the CSV file to ServiceBench).
    //      In other words, move swtc_sb_write_export_csvfile outside of the "if (count($certdata))".
        if (count($certdata)) {

            $certdata = swtc_sb_remove_dup_certs($certdata);

            list($new_csv_rows, $certs) = swtc_sb_add_certs_userprofile($certdata, $expdata);

        } else {
            // No new certs found; zero out all the counters.
            $new_csv_rows = 0;

            // Zero-out a dummy certstats array.
            foreach($TS_CERT_STRINGS as $key => $string) {
                $certs[$string] = 0;
            }
            $certs['ThinkSystem_Total_Processed'] = 0;

            foreach($LEG_CERT_STRINGS as $key => $string) {
                $certs[$string] = 0;
            }
            $certs['Legacy_Total_Processed'] = 0;

            // Add a total of both ThinkSystem and Legacy certs.
            $certs['Total_ThinkSystem_And_Legacy_Processed'] = 0;
        }

        // If the return value is false, the export did not work for some reason.
        $value = swtc_sb_write_export_csvfile($expdata);


        // If the export worked.
        if ($value == true) {
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            // $time_end = new DateTime("now", core_date::get_user_timezone_object());
            // $timeelapsed = $time_begin->diff($time_end);
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("End***End***End***End***End***End***End***End***End***End***End***End***End***End***End***\n", 'all');
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("Processing has completed. Leaving dcgsbautouser. Statistics follow: ===1.exit=== \n", 'all');
            // swtc_sb_logmessage("Time start :" .$time_begin->format('H:i:s').". ==1.exit===.\n", 'all');
            // swtc_sb_logmessage("Time end :" .$time_end->format('H:i:s').". ==1.exit===.\n", 'all');
            // swtc_sb_logmessage("Time elapsed :" .$timeelapsed->format('%i minutes, %s seconds').".==1.exit===.\n", 'all');

            swtc_sb_logmessage("\nTotal users obtaining the ThinkSystem or Legacy certification in the time frame given are: ===1.exit=== \n", 'all');
            swtc_sb_logmessage(print_r($certs, true), 'all');
            swtc_sb_logmessage("\n***CSV rows written (new user obtaining a certification) :$new_csv_rows.===1.exit=== \n", 'all');

            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("End***End***End***End***End***End***End***End***End***End***End***End***End***End***End***\n", 'all');
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
        } else {
            // The export did NOT work.
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("End***End***End***End***End***End***End***End***End***End***End***End***End***End***End***\n", 'all');
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("Processing has completed. Leaving dcgsbautouser. However the export process failed. ===1.exit=== \n", 'all');
            swtc_sb_logmessage("Check the log files for the cause of the export failure. ===1.exit=== \n", 'all');
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
            swtc_sb_logmessage("End***End***End***End***End***End***End***End***End***End***End***End***End***End***End***\n", 'all');
            swtc_sb_logmessage("Lenovo ********************************************************************************.\n", 'all');
        }

        swtc_sb_cleanup();

        return true;

    }
}
