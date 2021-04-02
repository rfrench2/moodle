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
 * View curriculums reports.
 *
 * @package    local
 * @subpackage SWTC
 * @copyright  2021 SWTC Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * SWTC history:
 *
 * 03/20/21 - Initial writing.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->dirroot . '/user/editlib.php');

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

class portfolio_access_settings_form extends moodleform {
    protected $columns;
    protected $roles;
    protected $roleid;
    protected $catid;
    protected $data = null;
    protected $allowed = null;
    protected $user;
    protected $development;
    protected $table;
    protected $tablename;
    protected $capability = 'moodle/category:viewcourselist';
    protected $baseurl = '/local/swtc/lib/portfolio_access_settings.php';

    function definition() {

        // $mform = $this->_form;

        // Get all the args.
        $this->user = $this->_customdata['user'];
        $this->tablename = $this->_customdata['tablename'];

        // If user clicked on the rolename header.
        $this->roleid = $this->_customdata['roleid'];

        // If user clicked on the portfolio header.
        $this->catid = $this->_customdata['catid'];

        // Set protected properties.
        $this->define_form_columns();
        $this->define_form_roles($this->user);

        // For development and debugging, set roles to only the first,
        //  or first few, roles.
        //  If development, set to true; if production, set to false.
        $this->development = false;

        // Note: load_current_settings must be called from outside this
        //   form as no settings can be saved in any $this variable. In
        //   addition, load_current_settings must be called twice: once
        //   before showing the current state of the permissions (i.e. before
        //   any changes) and after the Save changes button is clicked.
    }

    /**
     * Load information about all the columns we will need information about.
     *
     * SWTC history:
     *
     * 03/20/21 - Initial writing.
     * 04/01/21 - Changed style so it can include tooltip.
     *
     */
    private function define_form_columns() {

        $cell = new html_table_cell();
        // Set the row header (the role name) to bold.
        $cell->style = "font-weight: bold";
        $cell->text = get_string('rolefullname', 'core_role');
        $this->columns[0] = $cell;

        // Get all columns.
        $catids = get_tree(0);
        foreach($catids as $catid) {
            $cell = new html_table_cell();
            // Set the row header (the role name) to bold.
            $cell->style = "font-weight: bold";
            $catname = core_course_category::get($catid, MUST_EXIST, true)->name;
            $cell->text = html_writer::link(new moodle_url($this->baseurl, array('target' => '_blank', 'catid' => $catid)), $catname);
            // Adding this field so that the cell in the table format correctly.
            $cell->attributes['text2'] = $catname;
            $cell->attributes['title'] = $this->get_header_tooltip($catname, $catid);
            $this->columns[$catid] = $cell;
        }

        // See if the user clicked on a category header.
        if (!empty($this->catid)) {
            unset($this->columns);

            // REMEMBER - have to recreate [0] again.
            $cell = new html_table_cell();
            // Set the row header (the role name) to bold.
            $cell->style = "font-weight: bold";
            $cell->text = get_string('rolefullname', 'core_role');
            $this->columns[0] = $cell;

            $cell = new html_table_cell();
            // Set the row header (the role name) to bold.
            $cell->style = "font-weight: bold";
            $catname = core_course_category::get($this->catid, MUST_EXIST, true)->name;
            $cell->text = html_writer::link(new moodle_url($this->baseurl, array('target' => '_blank', 'catid' => $this->catid)), $catname);
            // Adding this field so that the cell in the table format correctly.
            $cell->attributes['text2'] = $catname;
            $cell->attributes['title'] = $this->get_header_tooltip($catname, $this->catid);
            $this->columns[$this->catid] = $cell;
        }
    }

    /**
     * Load information about all the roles we will need information about.
     * For reference, the following are the current roles:
     *  [19] => Lenovo-instructor
     *  [17] => Lenovo-student
     *  [20] => Lenovo-administrator
     *  [26] => Lenovo-siteadministrator
     *  [15] => GTP-instructor
     *  [16] => GTP-student
     *  [10] => GTP-administrator
     *  [23] => GTP-siteadministrator
     *  [21] => IBM-student
     *  [22] => ServiceProvider-student
     *  [24] => Maintech-student
     *  [27] => ASP-Maintech-student
     *  [28] => SelfSupport-student
     *  [41] => PremierSupport-siteadministrator
     *  [39] => PremierSupport-geoadministrator
     *  [32] => PremierSupport-administrator
     *  [34] => PremierSupport-manager
     *  [35] => PremierSupport-student
     *  [40] => ServiceDelivery-siteadministrator
     *  [38] => ServiceDelivery-geoadministrator
     *  [42] => ServiceDelivery-administrator
     *  [37] => ServiceDelivery-manager
     *  [36] => ServiceDelivery-student
     *
     * SWTC history:
     *
     * 03/20/21 - Initial writing.
     *
     */
    private function define_form_roles() {

        // Get all roles.
        $allroles = get_all_roles();
        foreach ($allroles as $role) {
            // We need to remove all the localized roles (i.e. roles without a name).
            //  And we need to save the name for each role.
            if (!empty($role->name)) {
                $this->roles[$role->id] = $role->name;
            }
        }

        // See if the user clicked on a rolename header.
        if (!empty($this->roleid)) {
            // Get the rolename based on the saved roleid.
            $rolename = $this->roles[$this->roleid];
            unset($this->roles);
            $this->roles[$this->roleid] = $rolename;
        }

        // For development and debugging, set roles to only the first,
        // few roles.
        if (!empty($this->development)) {
            unset($this->roles);
            $this->roles[19] = 'Lenovo-instructor';
        }
    }

    /**
     * Load the permission data from the database table. At this time only
     *  looks for 'moodle/category:viewcourselist'.
     *
     * Note: load_current_settings must be called from outside this
     *  form as no settings can be saved in any $this variable. In
     *  addition, load_current_settings must be called twice: once
     *  before showing the current state of the permissions (i.e. before
     *  any changes) and after the Save changes button is clicked.
     *
     * History:
     *
     * 03/28/21 - Initial writing.
     *
     */
    public function load_current_settings() {
        global $DB;

        foreach ($this->roles as $roleid => $rolename) {
            foreach ($this->columns as $columnid => $colname) {
                if (!empty($columnid)) {
                    $this->data[$columnid]['catname'] = $colname;
                    $this->data[$columnid]['capabilities'][$roleid]['name'] = $rolename;
                    // Note: The following is what we're trying to figure out.
                    // Set the initial state as no access (0).
                    $this->data[$columnid]['capabilities'][$roleid]['value'] = 0;
                }
            }
        }

        $rs = $DB->get_recordset($this->tablename);
        foreach ($rs as $record) {
            // Note: Must be cast to integer due to get_recordset returning only string values.
            $this->data[$record->catid]['capabilities'][$record->roleid]['value'] = (int) $record->access;
        }

        $rs->close();

    }

    /**
     * Returns structure that can be passed to print_table,
     * containing one cell for each checkbox.
     * @return html_table a table
     *
     * SWTC history:
     *
     * 03/20/21 - Initial writing.
     *
     */
    public function get_portfolio_access_table() {

        $debug = swtc_get_debug();

        $this->table = new html_table();
        $this->table->tablealign = 'center';
        $this->table->cellpadding = 5;
        $this->table->cellspacing = 0;
        $this->table->width = '90%';
        $this->table->align = array('left');
        $this->table->rotateheaders = true;
        $this->table->head = array('&#xa0;');
        $this->table->colclasses = array('');
        $this->table->head = $this->columns;
        $this->table->data = array();

        foreach ($this->roles as $roleid => $rolename) {
            /* Build display row:
             * [0] - 'Role name'
             * [1] - portfolio1
             * [2] - portfolio2
             * ...
             * [12] - portfolio12
             */
            $row = new html_table_row();
            $row->cells = array();

            foreach ($this->columns as $columnid => $column) {
                if (!empty($columnid)) {
                    $checked = '';

                    if ($this->data[$columnid]['capabilities'][$roleid]['value'] == 1) {
                        $checked = 'checked="checked" ';
                    }

                    // Use text2 so that internal cells format correctly.
                    // $tooltip = $this->get_cell_tooltip($rolename, $column->text);
                    $tooltip = $this->get_cell_tooltip($rolename, $column->attributes['text2']);
                    // $chkboxname = 's_' . $roleid . '_' . $rolename;
                    $chkboxname = 's_' . $roleid . '_' . $columnid;

                    // Save the current state of the checkbox.
                    // 03/27/21 - Can't save the state of the checkboxes between states.
                    // $this->currentdata[$chkboxname] = empty($checked) ? '0' : 1;

                    // 03/27/21 - WORKS! $args = array('class' => "form-check-input", 'id' => $chkboxname, 'for' => $chkboxname, 'title' => $tooltip, 'value' => 1);
                    // 03/26/27 - WORKS! $row->cells[] = html_writer::checkbox($chkboxname, $chkboxname, $checked, '', $args);
                    // 03/29/21 - WORKS!!! Experimenting with advcheckbox. The following is taken from data/field/checkbox/field.class.php:
                    //          '<input type="hidden" name="field_' . $this->field->id . '[]" value="" />';
                    $row->cells[] = '<input type="hidden" name="' . $chkboxname . '" value="" /><input type="checkbox" name="' . $chkboxname . '" id="' . $chkboxname .
                        '" title="' . $tooltip . '" value="1" ' . $checked . '/>' .
                        '<label for="' . $chkboxname . '" class="accesshide">' . $tooltip . '</label>';
                } else {
                    // Add the rolename as the row header.
                    // 03/31/21 - Experimenting...
                    // WORKS!! $row->cells[] = $rolename;
                    // WORKS!! The following 5 lines:
                    $cell = new html_table_cell();
                    // Set the row header (the role name) to bold.
                    $cell->style = "font-weight: bold";
                    // $cell->text = $rolename;
                    $cell->text = html_writer::link(new moodle_url($this->baseurl, array('target' => '_blank', 'roleid' => $roleid)), $rolename);
                    // Add a tooltip.
                    $cell->attributes['title'] = $this->get_cell0_tooltip($rolename, $roleid);
                    $row->cells[] = $cell;

                    // $attributes['title'] = $this->tooltip;
                    //$tooltip = $this->get_header_tooltip($colname, $columnid);
                    //title=" '.$tooltip.' "
                }
            }

            $this->table->data[] = $row;

        }

        if (isset($debug)) {
            $messages[] = print_r("In get_portfolio_access_table; about to print table.", true);
            $messages[] = print_r($this->table, true);
            // print_object($this->table);
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        return $this->table;

    }

    /**
     * Update the data with the new settings submitted by the user.
     *
     * Notes:
     * - The formdata is formatted 's_<roleid>_<portfolioid>'.
     * - The submitted data that is returned in formdata looks
     *      like the following:
     *
     * stdClass Object
     * (
     *  [sesskey] => 7h3HBq2KjX
     *  [s_19_14] => 1
     *  [s_19_36] => 1
     *  [s_19_60] => 1
     *  [s_19_47] => 1
     *  [s_19_73] =>
     *  [s_19_74] =>
     *  [s_19_25] => 1
     *  [s_19_97] => 1
     *  [s_19_110] =>
     *  [s_19_137] => 1
     *  [s_19_136] =>
     *  [s_19_141] => 1
     *  [submit] => Save changes
     * )
     *
     * REMEMBER!! The state of ALL checkboxes are returned. If set, the value
     *      will be 1; if not, they will look like they are not set, but
     *      are zero.
     *
     */
    // Note: For debugging, use the following line:
    // process_submission($formdata) {
    public function process_submission($portapply = null) {
        global $DB;

        $debug = swtc_get_debug();

        if (isset($debug)) {
            $messages[] = "In /local/swtc/forms/portfolio_access_settings_form.php ===process_submission.enter===";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }
        // print_object("did I get here?? about to print formdata");
        // print_object($formdata);
        $this->load_current_settings();

        // Need to create the newly updated settings just like we created the
        // current settings (load_current_settings).
        //
        // foreach ($this->data as $catid => $catid['capabilities'])
        foreach ($this->roles as $roleid => $rolename) {
            foreach ($this->columns as $catid => $catname) {
                // Remember to skip $this->columns[0] which is the column header.
                // Examples:
                //      $chkboxname = 's_' . $roleid . '_' . $columnid;
                //      [s_19_141] => 1
                if (!empty($catid)) {
                    // 03/29/21 - START HERE!!!
                    // If we enter the following, the checkbox was set.
                    // print_r("in process_submission; about to print optional_param and newvalue");
                    $newvalue = (int) optional_param('s_' . $roleid . '_' . $catid, null, PARAM_INT);
                    // print_r("new value is :$newvalue" . "\n");

                    // if (optional_param('s_' . $roleid . '_' . $catid, false, PARAM_INT)) {
                    // The value SHOULD be either 1 (accessable) or 0 (inaccessable).
                    if (!is_null($newvalue)) {
                        $params = array();
                        $params['roleid'] = $roleid;
                        $params['catid'] = $catid;
                        // Get the current database record.
                        if ($record = $DB->get_record($this->tablename, $params, 'id, catid, roleid, access')) {
                            // The database record exists.
                            // If the state of the access changes, remember to
                            // add / update the timemodified and usermodified.
                            // If the NEW value is different than the CURRENT value, change it.
                            if ((int)$record->access !== $newvalue) {
                                if (isset($debug)) {
                                    // print_r("currvalue and newvalue are DIFFERENT; changing the [$roleid][$catid] value");
                                    $messages[] = print_r("Values are DIFFERENT; changing " . $rolename . "(" . $roleid . ") access to " . $catname . "(" . $catid . ") from " . (int)$record->access . " to $newvalue.", true);
                                    $debug->logmessage($messages, 'detailed');
                                    unset($messages);
                                }
                                $params['id'] = $record->id;
                                $params['access'] = $newvalue;
                                $params['timemodified'] = time();
                                $params['usermodified'] = $this->user->get_userid();
                                $DB->update_record($this->tablename, $params);

                                if (isset($portapply)) {
                                    // Since the access has changed, we must update the permissions
                                    // for the top-level category.
                                    // First, prevent all roles from the category.
                                    // all-category-changecapability.sh -c -d -cap moodle/category:viewcourselist -opt prevent -por gtp -rsn all
                                    // moosh -n -v swtc-role-update-capability "$debug" "$capability" "$option" "$x" "$roleshortname"
                                    // moosh -n -v swtc-role-update-capability --deb moodle/category:viewcourselist prevent gtp lenovo
                                    $context = context_coursecat::instance($catid);
                                    $option = !empty($newvalue) ? CAP_ALLOW : CAP_PREVENT;
                                    // print_r("roleid is :$roleid, option is :$option");
                                    // die;
                                    if (assign_capability($this->capability, $option, $roleid, $context->id, true)) {
                                        if (isset($debug)) {
                                            $messages[] = print_r("Assigned capability for $rolename($roleid) to $catname($catid) successfully.", true);
                                            $debug->logmessage($messages, 'detailed');
                                            unset($messages);
                                        }
                                    } else {
                                        if (isset($debug)) {
                                            $messages[] = print_r("Error - Unable to assigned capability for $rolename($roleid) to $catname($catid).", true);
                                            $debug->logmessage($messages, 'detailed');
                                            unset($messages);
                                        }
                                    }
                                    // die;
                                }
                            } else {
                                // if (isset($debug)) {
                                //     // print_r("currvalue and newvalue are the same; nothing to see here\n");
                                //     $messages[] = print_r("Values are the same; nothing to do.", true);
                                //     $debug->logmessage($messages, 'detailed');
                                //     unset($messages);
                                // }
                            }
                        } else {
                            // The database record did NOT exist...yet.
                            // Add the created date and userid.
                            // $value = $this->data[$catid]['capabilities'][$roleid]['value'];
                            // print_r("in process_submission; about to print value :$newvalue");
                            $params = array();
                            $params['roleid'] = $roleid;
                            $params['catid'] = $catid;
                            $params['access'] = $newvalue;
                            $params['timecreated'] = time();
                            $params['usercreated'] = $this->user->get_userid();
                            $params['timemodified'] = '';
                            $params['usermodified'] = '';

                            if ($DB->insert_record($this->tablename, $params, false)) {
                                // The record was successfully created.
                                if (isset($debug)) {
                                    $messages[] = print_r("Added new record: " . $rolename . "(" . $roleid . ") access to " . $catname . "(" . $catid . ") added.", true);
                                    $debug->logmessage($messages, 'detailed');
                                    unset($messages);
                                }
                                if (isset($portapply)) {
                                    // Since the access has changed, we must update the permissions
                                    // for the top-level category.
                                    // First, prevent all roles from the category.
                                    // all-category-changecapability.sh -c -d -cap moodle/category:viewcourselist -opt prevent -por gtp -rsn all
                                    // moosh -n -v swtc-role-update-capability "$debug" "$capability" "$option" "$x" "$roleshortname"
                                    // moosh -n -v swtc-role-update-capability --deb moodle/category:viewcourselist prevent gtp lenovo
                                    $context = context_coursecat::instance($catid);
                                    $option = !empty($newvalue) ? CAP_ALLOW : CAP_PREVENT;
                                    // print_r("roleid is :$roleid, option is :$option");
                                    // die;
                                    if (assign_capability($this->capability, $option, $roleid, $context->id, true)) {
                                        // print_object("it worked");
                                    } else {
                                        // print_object("it DIDNT work");
                                    }
                                    // die;
                                }
                            } else {
                                // The record was NOT successfully created.
                                // Not sure what to do here either.
                            }
                        }
                    }
                }
            }
        }
    }

    public function get_cell_tooltip($role, $column) {
        $a = new stdClass;
        $a->rolename = $role;
        $a->portfolio = $column;
        return get_string('allowaccesstoportfolio', 'local_swtc', $a);
    }

    public function get_cell0_tooltip($role, $roleid) {
        $a = new stdClass;
        $a->rolename = $role;
        $a->roleid = $roleid;
        return get_string('portfolioroleid', 'local_swtc', $a);
    }

    public function get_header_tooltip($catname, $catid) {
        $a = new stdClass;
        $a->portfolio = $catname;
        $a->catid = $catid;
        return get_string('portfoliocatid', 'local_swtc', $a);
    }

    /**
     * Snippet of text displayed above the table, telling the admin what to do.
     * @return string
     */
    public function get_intro_text() {
        $text = get_string('portfolio_access_intro', 'local_swtc');
        // $text .= get_string('portfolio_access_intro2', 'local_swtc');
        // $text = '<p>Master table of portfolio to customized user roles (accesstype) access permissions.<br>';
        // $text .= '<br>Initial state of the table is set from values found in the <strong>local_swtc_port_access</strong> database table.';
        // $text .= '<br>These values will be used throughout the site to verify a user' . "'" . 's access.';
        // $text .= '<br>Including the setting of the <strong>moodle/category:viewcourselist</strong> at the top-level portfolio level.';
        // $text .= '<br>Changing these values only changes the values in the <strong>local_swtc_port_access</strong> database table.';
        // $text .= '<br><br><strong>No changing of moodle/category:viewcourselist will be performed.</strong></p>';
        return $text;
    }
}
