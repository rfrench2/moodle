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
 * @subpackage swtc/settings.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 12/05/20 - Initial writing.
 * 03/11/21 - Added category / role mapping table.
 *
 **/
 defined('MOODLE_INTERNAL') || die;

// Needs this condition or there is error on login page.
if ($hassiteconfig) {
    // SWTC ********************************************************************************.
    // Setup main page.
    // SWTC ********************************************************************************.
    $ADMIN->add('root', new admin_category('swtcadmin',
        get_string('swtc', 'local_swtc')));

    // SWTC ********************************************************************************.
    //
    // SWTC ********************************************************************************.
    $settings = new admin_settingpage('local_swtc_settings',
        new lang_string('settings', 'local_swtc'));
    $ADMIN->add('swtcadmin', $settings);

    // SWTC ********************************************************************************.
    //
    // SWTC ********************************************************************************.
    $settings->add(new admin_setting_configcheckbox('local_swtc/swtc',
        new lang_string('swtc', 'local_swtc'),
        new lang_string('swtc_desc', 'local_swtc'), 1));

    // SWTC ********************************************************************************.
    // To use: if (get_config('local_swtc', 'swtcdebug')) OR $config = get_config('local_swtc').
    // echo $config->swtcdebug.
    // SWTC ********************************************************************************.
    $settings->add(new admin_setting_heading('local_swtc_debugsettings',
        get_string('debugsettings', 'local_swtc'), ''));
    $settings->add(new admin_setting_configcheckbox('local_swtc/swtcdebug',
        new lang_string('swtcdebug', 'local_swtc'),
        new lang_string('swtcdebug_desc', 'local_swtc'), 0));

    // SWTC ********************************************************************************.
    //
    // SWTC ********************************************************************************.
    $ADMIN->add('swtcadmin', new admin_externalpage('swtcsuggestedcourses', 'Suggested courses',
            new moodle_url("$CFG->wwwroot/local/swtc/lib/swtcsuggestedcourses.php")));

    // SWTC ********************************************************************************.
    //
    // SWTC ********************************************************************************.
    $settings->add(new admin_setting_heading('local_swtc_invitationsettings',
        get_string('invitationsettings', 'local_swtc'),
        get_string('invitationsettings_desc', 'local_swtc')));
    // Invitation expiration: Default to 72 hours expiration.
    $settings->add(new admin_setting_configtext('local_swtc/inviteexpiration',
        get_string('inviteexpiration', 'local_swtc'),
        get_string('inviteexpiration_desc', 'local_swtc'), 259200, PARAM_INT));
    // SWTC ********************************************************************************.
    //
    // SWTC ********************************************************************************.
    $ADMIN->add('swtcadmin', new  admin_externalpage('invitehistory',
        new lang_string('invitehistory', 'local_swtc'),
        new moodle_url("$CFG->wwwroot/local/swtc/lib/invitehistory.php")));

    // SWTC ********************************************************************************.
    // Portfolio access settings form.
    // SWTC ********************************************************************************.
    $ADMIN->add('swtcadmin', new  admin_externalpage('portfolio_access_settings',
        new lang_string('portfolio_access_settings', 'local_swtc'),
        new moodle_url("$CFG->wwwroot/local/swtc/lib/portfolio_access_settings.php")));
}
