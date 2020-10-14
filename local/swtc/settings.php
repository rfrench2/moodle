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
 * @subpackage swtc
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	08/30/16 - Initial writing.
 *	01/25/17 - Updated with new information.
 * 04/26/17 - v15 (cont) - Continued work on adding "Lenovo Services Education" menu to Navigation block for all Lenovo-administrators
 *                                      and site administrators.
 *  08/25/17 - Moved all ServiceBench work to 'servicebench' folder; adding opening of ServiceBench output html file.
 *                          Note that if this file changes, no need to update version.php - simply refresh web page.
 * 06/02/18 - Added debug setting; updated links to ServiceBench items.
 * 08/02/18 - Added settings for user account invitation process; added invitation history page.
 * 08/17/18 - Added dashboard pages.
 * 12/04/18 - Added "swtcbatchemail" setting to enable / disable the "Welcome" email message if user is enrolled by a cohort.
 * 06/04/19 - Added "featured courses" multiple select listbox.
 * 08/01/19 - Changed "featured courses" to "suggested courses".
 * 11/14/19 - Added global "SWTC" setting so that it can be checked in Moodle core files to call customized SWTC functions.
 * 02/24/20 - Adding Analytics pages (shows LMS equivalents for the Tableau analytics site).
 *
 **/
 defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // Needs this condition or there is error on login page.
    // SWTC *******************************************************************************
    // The following works...don't touch it.
    //$ADMIN->add('root', new admin_externalpage('local_swtc',
    //        get_string('lenovo_services', 'local_swtc'),
    //        new moodle_url("$CFG->wwwroot/local/swtc/form/production_form.php")));
    //
    // The following three lines work (don't touch).
    //      $ADMIN->add('root', new admin_category('ebgadmin', get_string('lenovo_services', 'local_swtc')));
    //      $ADMIN->add('ebgadmin', new admin_externalpage('swtc_prod', 'Calling production.php',
    //        new moodle_url("$CFG->wwwroot/local/swtc/form/production.php")));
    //      $ADMIN->add('ebgadmin', new admin_externalpage('swtc_edit', 'Edit course 159 (sample)',
    //        new moodle_url("/course/edit.php?id=159")));
    //
    //
    // The following is from /admin/users.php
    //          $ADMIN->add('users', new admin_category('accounts', new lang_string('accounts', 'admin')));
    //          // stuff under the "accounts" subcategory
    //          $ADMIN->add('accounts', new admin_externalpage('editusers', new lang_string('userlist','admin'), "$CFG->wwwroot/$CFG->admin/user.php", array('moodle/user:update', 'moodle/user:delete')));
    //          $ADMIN->add('accounts', new admin_externalpage('userbulk', new lang_string('userbulk','admin'), "$CFG->wwwroot/$CFG->admin/user/user_bulk.php", array('moodle/user:update', 'moodle/user:delete')));
    //          $ADMIN->add('accounts', new admin_externalpage('addnewuser', new lang_string('addnewuser'), "$securewwwroot/user/editadvanced.php?id=-1", 'moodle/user:create'));
    // SWTC *******************************************************************************
    // Setup main page.
    // SWTC *******************************************************************************
    $ADMIN->add('root', new admin_category('ebgadmin', get_string('lenovo_services', 'local_swtc')));

    // SWTC *******************************************************************************
    // 06/03/18 - Added new Settings page.
    // SWTC *******************************************************************************
    $settings = new admin_settingpage('local_swtc_settings', new lang_string('settings', 'local_swtc'));
    $ADMIN->add('ebgadmin', $settings);

    // SWTC *******************************************************************************.
    // 11/14/19 - Added global "SWTC" setting so that it can be checked in Moodle core files to call customized SWTC functions.
    // SWTC *******************************************************************************.
    $settings->add(new admin_setting_configcheckbox('local_swtc/swtclenovo', new lang_string('lenovo', 'local_swtc'), new lang_string('lenovo_desc', 'local_swtc'), 1));

    // SWTC *******************************************************************************
    // 08/02/18 - Added Debug Settings section heading.
    // SWTC *******************************************************************************
    $settings->add(new admin_setting_heading('local_swtc_debugsettings', get_string('debugsettings', 'local_swtc'), ''));
    // To use: if (get_config('local_swtc', 'swtcdebug')) OR $config = get_config('local_swtc'); echo $config->swtcdebug;
    $settings->add(new admin_setting_configcheckbox('local_swtc/swtcdebug', new lang_string('swtcdebug', 'local_swtc'), new lang_string('swtcdebug_desc', 'local_swtc'), 0));

	// SWTC *******************************************************************************
    // 12/04/18 - Added Batch email section heading to the Debug Settings section.
    // SWTC *******************************************************************************
    $settings->add(new admin_setting_heading('local_swtc_batchemailsettings', get_string('batchemailsettings', 'local_swtc'), ''));
    $settings->add(new admin_setting_configcheckbox('local_swtc/swtcbatchemail', new lang_string('swtcbatchemail', 'local_swtc'), new lang_string('swtcbatchemail_desc', 'local_swtc'), 0));

    // SWTC *******************************************************************************
    // 06/04/19 - Added new Suggested courses page.
    // SWTC *******************************************************************************
    // $suggestedcourses = new admin_settingpage('local_swtc_sc', new lang_string('suggestedcourses', 'local_swtc'));
    // $ADMIN->add('ebgadmin', $suggestedcourses);
    $ADMIN->add('ebgadmin', new admin_externalpage('suggestedcourses', 'Suggested courses',
            new moodle_url("$CFG->wwwroot/local/swtc/lib/suggestedcourses.php")));

    // SWTC *******************************************************************************
    // 08/02/18 - Added New User Account Invitation section heading.
    // SWTC *******************************************************************************
    // $settings->add(new admin_setting_heading('local_swtc_invitationsettings', get_string('invitationsettings', 'local_swtc'), ''));
    $settings->add(new admin_setting_heading('local_swtc_invitationsettings', get_string('invitationsettings', 'local_swtc'),
                        get_string('invitationsettings_desc', 'local_swtc')));
    // Invitation expiration: Default to 72 hours expiration.
    $settings->add(new admin_setting_configtext('local_swtc/inviteexpiration',
        get_string('inviteexpiration', 'local_swtc'), get_string('inviteexpiration_desc', 'local_swtc'), 259200, PARAM_INT));

    // Inviterid: The default userid that this invitation comes from (normally always "swtc).
    // $settings->add(new admin_setting_configtext('local_swtc/inviteexpiration',
    //    get_string('inviteexpiration', 'local_swtc'), get_string('inviteexpiration_desc', 'local_swtc'), 1209600, PARAM_INT));

    // SWTC *******************************************************************************
    // 08/06/18 - Added new Invitation history page.
    // SWTC *******************************************************************************
    $ADMIN->add('ebgadmin', new  admin_externalpage('invitehistory', new lang_string('invitehistory', 'local_swtc'),
                    new moodle_url("$CFG->wwwroot/local/swtc/lib/invitehistory.php")));

    // SWTC *******************************************************************************
    // 08/17/18 - Added dashboard pages.
    // SWTC *******************************************************************************
    $ADMIN->add('ebgadmin', new admin_category('dashboards', 'Dashboards'));
    $ADMIN->add('dashboards', new admin_externalpage('dashcourse', 'Course dashboard',
            new moodle_url("$CFG->wwwroot/local/swtc/forms/dashboard_course.php")));
    $ADMIN->add('dashboards', new admin_externalpage('dashactivity', 'Activity dashboard',
            new moodle_url("$CFG->wwwroot/local/swtc/forms/dashboard_activity.php")));
    $ADMIN->add('dashboards', new admin_externalpage('dashpremiersupport', 'PremierSupport dashboard',
            new moodle_url("$CFG->wwwroot/local/swtc/lib/dashpremiersupport.php")));

	$ADMIN->add('dashboards', new admin_externalpage('dashadvanced', 'Advanced reporting dashboard',
            new moodle_url("$CFG->wwwroot/local/swtc/forms/dashboard_advreporting.php")));

    // SWTC *******************************************************************************.
    // 02/24/20 - Adding Analytics pages (shows LMS equivalents for the Tableau analytics site).
    // SWTC *******************************************************************************.
    $ADMIN->add('ebgadmin', new admin_category('tableau', 'Tableau analytics'));
    $ADMIN->add('tableau', new admin_externalpage('tableauecut', 'Enrollments (by user type)',
            new moodle_url("$CFG->wwwroot/local/swtc/lib/tableau.php")));


    // $ADMIN->add('ebgadmin', new admin_externalpage('swtc_edit', 'Edit course 159 (sample)',
       //     new moodle_url("/course/edit.php?id=159")));

    // 08/25/17 - New "ServiceBench" menu item; the following is copied from servicebench/sblib.php:
    $sb_folder = "$CFG->dataroot/repository/servicebench/";
    $sb_log_folder = "$CFG->dataroot/repository/servicebench/logs/";
    $lms_prod_import_folder = "$CFG->dataroot/repository/servicebench/prod/out/";
    $lms_prod_export_folder = "$CFG->dataroot/repository/servicebench/prod/in/";
    $debug_ext = date("Ymd").'.log';                // All file extensions will be in the form of 'yyyymmdd.log'.
    $detail_ext = date("Ymd").'.details.log';    // All detail file extensions will be in the form of 'yyyymmdd.details.log'.

    $import_debug_filename = "import_".$debug_ext;       // "Regular" debug log file is named "<import/export>_yyyymmdd.log".
    $export_debug_filename = "export_".$debug_ext;       // "Regular" debug log file is named "<import/export>_yyyymmdd.log".
    $import_detail_filename = "import_".$detail_ext;       // Detailed debug log file is named "<import/export>_yyyymmdd.details.log".
    $export_detail_filename = "export_".$detail_ext;       // Detailed debug log file is named "<import/export>_yyyymmdd.details.log".

    $import_fqlog = $sb_log_folder . $import_debug_filename;                   // Regular log path and log file name.
    $export_fqlog = $sb_log_folder . $export_debug_filename;                   // Regular log path and log file name.
    $import_fqdetailed = $sb_log_folder . $import_detail_filename;                   // Detailed log path and log file name.
    $export_fqdetailed = $sb_log_folder . $export_detail_filename;                   // Detailed log path and log file name.

    $ADMIN->add('ebgadmin', new admin_category('servicebench', get_string('lenovo_servicebench', 'local_swtc')));
    $ADMIN->add('servicebench', new admin_externalpage('swtc_sbench_report_html', 'View latest ServiceBench upload report (html)',
            new moodle_url("/local/swtc/servicebench/autouploaduser-dcg.html")));
    $ADMIN->add('servicebench', new admin_externalpage('swtc_sbench_report_txt', 'View latest ServiceBench error report (error_log.txt)',
            new moodle_url("/local/swtc/servicebench/error_output.txt")));
    $ADMIN->add('servicebench', new admin_externalpage('swtc_sbench_phperror', 'View latest ServiceBench phperror report (php_errors.log)',
            new moodle_url("/local/swtc/servicebench/php_errors.log")));

    // print("Did I get here...xxx.</br>");
}
