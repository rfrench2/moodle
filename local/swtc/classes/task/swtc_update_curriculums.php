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
 * A scheduled task for Lenovo DCG Services Education. Used to add cron job to Moodle for updating curriculums.
 *
 * Version details
 *
 * @package    swtc_update_curriculums
 * @copyright  2018 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	11/26/18 - Initial writing (cloned from /classes/tasks/swtc_export_to_sb.php); calling the existing Moosh command since it will
 *                      save a LOT of work.
 * 11/27/18 - Added email function (swtc_util_emailsend).
 * 12/29/18 - Changed parameters to match the Lenovo Moosh command.
 * 05/06/19 - (Major change) Backing off change of log file location (changing back to /moodledata/repository/servicebench/*); changing
 *                          location to the Motorola "data\moodledata\*" folder.
 *
 **/
 // Lenovo ********************************************************************************
namespace local_swtc\task;

class swtc_update_curriculums extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontaskupdatecurriculums', 'local_swtc');
    }

    /**
     * Function to be run periodically according to the moodle cron
     * Runs based on cron schedule.
     *
     * @return boolean
     *
     * History:
     *
     * 05/06/19 - Added this header.
     * 05/06/19 - (Major change) Backing off change of log file location (changing back to /moodledata/repository/servicebench/*); changing
     *                          location to the Motorola "data\moodledata\*" folder.
     *
     */
    public function execute() {
        global $CFG, $DB;

        // Lenovo ********************************************************************************
        // Variables begin...

        // Variables required for the saving of the debug log to a file.
        $log_folder = $CFG->dataroot.'/repository/debug/logs/';
        $file_ext = date("Ymd").'.txt';

        // To separate log files, name the file the name of the task being run.
        $log_filename = $log_folder . 'swtc_update_curriculums.debug' .".". $file_ext;

        // Setup parameters like invoking from command line. An example would be:
        //      moosh -v -n lenovo-curriculums --deb PSC0002
        // Base parameters on the task being run.
		$fqpath = '/usr/local/bin/';
        $moosh = $fqpath . 'moosh -n -v lenovo-curriculums --upd --com --deb %';

        // Lenovo ********************************************************************************
        // Strings required for header and footer. Always output standard header information.
        // Lenovo ********************************************************************************
        $header = "Lenovo ********************************************************************************.\n";
        $header .=  "Running scheduled task LenovoCurriculums. Entering swtc_update_curriculums.execute.enter===.\n";

        $footer = "Running scheduled task LenovoCurriculums. Leaving swtc_update_curriculums.execute.exit===.\n";
        $footer .= "Lenovo ********************************************************************************.\n";

        // Variables end...
        // Lenovo ********************************************************************************

        // Set the current directory to the Moodle root (Moosh runs from there).
        chdir($CFG->dirroot);

        // Run the Moosh command;
        $cmd_output = shell_exec($moosh);
        echo $cmd_output;

        // Assemble all the output.
        $output = $header;
        $output .= $cmd_output;
        $output .= $footer;

        // Lenovo ********************************************************************************
        // Output to the log file. Per the PHP documentation, file_put_contents is function is identical to calling fopen(), fwrite()
        //      and fclose() successively to write data to a file.
        // Lenovo ********************************************************************************
        file_put_contents($log_filename, $output, FILE_APPEND);

        // Lenovo ********************************************************************************
        // Email the log file.
        // Lenovo ********************************************************************************
        $this->swtc_util_emailsend($log_filename);

        return true;
    }

    /**
     * Send an email to a user with the "regular" log attached. Uses the Moodle email_to_user() function (lib/moodlelib.php).
     *      IMPORTANT! Cannot write to log files from here as the files must be closed before attaching to email. If any debugging must be
     *              done, must write only to screen.
     *
     * @param             $logfile (string) The name of the log file to send.
     *
     * @return             N/A
     */
    /**
     * Version details
     *
     * History:
     *
     *	11/27/18 - Initial writing (cloned from /servicebench/sblib.php).
     *
     **/
    public function swtc_util_emailsend($logfile) {
            global $CFG, $SITE;

        // Lenovo ********************************************************************************
        // Variables begin...

        // Setup data to be passed to get_string.
        $data = array();
        $data["sitename"]  = format_string($SITE->fullname);
        $data["date"]  = date("F j, Y, g:i a");

        // Strings for curriculums email support.
        $str_emailsubject = get_string('swtc_curriculums_email_subject', 'local_swtc', $data);
        $str_emailbody = get_string('swtc_curriculums_email_body', 'local_swtc', $data);

        // Email of administrator to use (and to use for lookup).
        $email = 'rfrench@lenovo.com';

        // Path to log file (attachment for email).
        $fqlog = '';

        // Variables end...
        // Lenovo ********************************************************************************

        // Object for toUser.
        $toUser = new \stdClass();

        // Get the rest of the user information.
        //      Other helpful functions:
        //          $user = get_complete_user_data('id', $userid);
        //          $userinfo = get_complete_user_data('id', $moodleuser->id);
        //          $moodleuser = \core_user::get_user_by_email($userinfo['email']);
        $toUser = \core_user::get_user_by_email($email);

        //  if ($EBG_DEBUG->cli) {
        //      swtc_sb_logmessage("\nAbout to print toUser.==11.1===.\n", 'display');
        //      swtc_sb_logmessage(print_r($toUser, true), 'display');
        //      swtc_sb_logmessage("Finished printing toUser.==11.1===.\n", 'display');
        //  }

        // Object for fromUser (the Moodle "support" admin id).
        $supportuser = new \stdClass();
        $supportuser = \core_user::get_support_user();

        //  if ($EBG_DEBUG->cli) {
        //      swtc_sb_logmessage("\nAbout to print supportuser.==11.2===.\n", 'display');
        //      swtc_sb_logmessage(print_r($supportuser, true), 'display');
        //      swtc_sb_logmessage("Finished printing supportuser.==11.2===.\n", 'display');
        //  }

        // Get just the name of the log file.
        $basename = pathInfo($logfile, PATHINFO_BASENAME);

        // The following is taken from the header of the email_to_user function in lib/moodlelib.php:
        //      @param string $attachment a file on the filesystem, either relative to $CFG->dataroot or a full path to a file in $CFG->tempdir
        //      @param string $attachname the name of the file (extension indicates MIME)
        //
        //      Therefore, we must remove "$CFG->dataroot/" from $EBG_DEBUG->fqlog.
        //
        // if (strpos($EBG_DEBUG->fqlog, 'RXHighEnd') !== false) {
        // $attachpath = str_replace('\\', '/', $attachmentpath);
        $dataroot = $CFG->dataroot . '/';
        $fqlog = str_replace($dataroot, '', $logfile);


        //  if ($EBG_DEBUG->cli) {
        //      swtc_sb_logmessage("\nbasename is :$basename.==11.3===.\n", 'display');
        //      swtc_sb_logmessage("fqlog path is :$fqlog.==11.3===.\n", 'display');
        //  }

        // Send the email.
        // swtc_sb_logmessage("\n$str_emailsubject.\n", 'display');
        //  Example: email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, $completeFilePath, $nameOfFile, true);
        // email_to_user($toUser, $supportuser, $str_emailsubject, '', $str_emailbody, 'repository/servicebench/logs/export_20180319.log', $basename, true);
        email_to_user($toUser, $supportuser, $str_emailsubject, '', $str_emailbody, $fqlog, $basename, true);

        return;
    }
}
