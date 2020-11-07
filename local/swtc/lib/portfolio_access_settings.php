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
 * @subpackage swtc/lib/portfolio_access_settings.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/02/20 - Initial writing.
 *
 **/

use local_swtc\swtc_user;
use local_swtc\portfolio_access_table;

require_once ('../../../config.php');
// require_once ($CFG->dirroot. '/auth/emailadmin/locallib.php');
// require_once($CFG->libdir . '/tablelib.php');

global $USER, $DB;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// require_once($CFG->dirroot.'/local/swtc/forms/swtc_portfolio_access_form.php');

// SWTC ********************************************************************************.
// SWTC swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_set_debug();
// SWTC ********************************************************************************.

if (isset($debug)) {
 $messages[] = "In /local/swtc/lib/portfolio_access_settings.php ===1.enter===";
 $debug->logmessage($messages, 'both');
 unset($messages);
}

require_login();

// SWTC ********************************************************************************.
// Restricted list shown to 20 and added paging.
// SWTC ********************************************************************************.
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);

// Set up page.
$PAGE->set_context(context_system::instance());
$url = new moodle_url('/local/swtc/lib/portfolio_access_settings.php', array('perpage' => $perpage, 'page' => $page));
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$pagetitle = get_string('portfolio_access_settings', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

// Setup some things before loading the table.
//  Load the columns to display.
$columns[0] = get_string('rolefullname', 'core_role');

$catids = $swtc_user->get_tree(0);
foreach($catids as $id) {
    $columns[$id] = \core_course_category::get($id, MUST_EXIST, true)->name;
}

// Since all the roles that can be assigned at the category level should be same,
//  use one to get the assignable roles.
// $roles = get_assignable_roles(context_coursecat::instance(141));
$temp = get_all_roles(context_coursecat::instance(141));
// print_object($temp);

// We need to remove all the localized roles (i.e. roles without a name).
//  And we need to save a username for each role.
foreach ($temp as $role) {
    // print_object($role);
    if (!empty($role->name)) {
        $roles[$role->id]['rolename'] = $role->name;
        switch ($role->shortname) {
            // SWTC ********************************************************************************
            // 'Lenovo-instructor'
            // SWTC ********************************************************************************
            case get_string('role_lenovo_instructor', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-inst'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'Lenovo-student'
            // SWTC ********************************************************************************
            case get_string('role_lenovo_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'Lenovo-administrator'
            // SWTC ********************************************************************************
            case get_string('role_lenovo_administrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-admin'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'Lenovo-siteadministrator'
            // SWTC ********************************************************************************
            case get_string('role_lenovo_siteadmin', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-siteadmin'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'GTP-instructor'
            // SWTC ********************************************************************************
            case get_string('role_gtp_instructor', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-gtp-inst'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'GTP-student'
            // SWTC ********************************************************************************
            case get_string('role_gtp_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-gtp-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'GTP-administrator'
            // SWTC ********************************************************************************
            case get_string('role_gtp_administrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-gtp-admin'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'GTP-siteadministrator'
            // SWTC ********************************************************************************
            case get_string('role_gtp_siteadministrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-gtp-siteadmin'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'IBM-student'
            // SWTC ********************************************************************************
            case get_string('role_ibm_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-ibm-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceProvider-student'
            // SWTC ********************************************************************************
            case get_string('role_serviceprovider_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-serviceprovider-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'Maintech-student'
            // SWTC ********************************************************************************
            case get_string('role_maintech_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-maintech-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ASP-Maintech-student'
            // SWTC ********************************************************************************
            case get_string('role_asp_maintech_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-aspmain-stud'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'SelfSupport-student'
            // SWTC ********************************************************************************
            case get_string('role_selfsupport_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-selfsupport-stud1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'PremierSupport-siteadministrator'
            // SWTC ********************************************************************************
            case get_string('role_premiersupport_siteadministrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-ps-siteadmin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'PremierSupport-geoadministrator'
            // SWTC ********************************************************************************
            case get_string('role_premiersupport_geoadministrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-psus-geoadmin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'PremierSupport-administrator'
            // SWTC ********************************************************************************
            case get_string('role_premiersupport_administrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-psus1-admin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'PremierSupport-manager'
            // SWTC ********************************************************************************
            case get_string('role_premiersupport_manager', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-psus1-mgr1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'PremierSupport-student'
            // SWTC ********************************************************************************
            case get_string('role_premiersupport_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-psus1-stud1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceDelivery-siteadministrator'
            // SWTC ********************************************************************************
            case get_string('role_servicedelivery_siteadministrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-sd-siteadmin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceDelivery-geoadministrator'
            // SWTC ********************************************************************************
            case get_string('role_servicedelivery_geoadministrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-sdus-geoadmin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceDelivery-administrator'
            // SWTC ********************************************************************************
            case get_string('role_servicedelivery_administrator', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-sdus1-admin1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceDelivery-manager'
            // SWTC ********************************************************************************
            case get_string('role_servicedelivery_manager', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-sdus1-mgr1'), 'id')->id;
                break;

            // SWTC ********************************************************************************
            // 'ServiceDelivery-student'
            // SWTC ********************************************************************************
            case get_string('role_servicedelivery_student', 'local_swtc'):
                $roles[$role->id]['userid'] = $DB->get_record('user', array('username' => 'test-sdus1-stud1'), 'id')->id;
                break;


            default:
        }

    }
}

// print_object("About to print roles 7:");
// print_object($roles);
// return;

// OUTPUT form.
echo $OUTPUT->header();

// Print out a heading.
echo $OUTPUT->heading($pagetitle, 2, 'headingblock');

// $table = new flexible_table('portfolioaccess');
// print_object(get_declared_classes());
$table = new portfolio_access_table('portfolioaccess');
// SWTC ********************************************************************************.
// Restricted list shown to 20 and added paging.
// SWTC ********************************************************************************.
$table->pagesize($perpage, count($columns));
$table->pageable(true);
$table->define_columns(array_keys($columns));
$table->define_headers(array_values($columns));
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable');

$table->setup();

// Important! Remove 'Role name' (element 0) from the columns array.
// array_shift($columns);
unset($columns[0]);
// print_object($columns);

// SWTC ********************************************************************************.
//
// SWTC ********************************************************************************.
$start = $page * $perpage;
if ($start > count($columns)) {
    $page = 0;
    $start = 0;
}

// $roles = get_all_roles();
// $roles = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);
// print_object("About to print roles:");
// print_object($roles);


// print_object("About to print roles:");
// print_object($roles);


// print_object("About to print columns:");
// print_object($columns);

// Build caps and test username array.
foreach ($columns as $id => $portfolio) {
    switch ($portfolio) {
        // SWTC ********************************************************************************
        // 'GTP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('gtp_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_gtp_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-gtp-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'IBM Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('ibm_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_ibm_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-ibm-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'Lenovo Internal Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('lenovointernal_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_lenovointernal_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'Lenovo Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('lenovo_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_lenovo_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'Lenovo Shared Resources (Master)' - add the capabilities, roleshortnames, and roleids for
		//    the top-level category.
        // SWTC ********************************************************************************
        case get_string('lenovosharedresources_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_lenovosharedresources_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
        // 'Maintech Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('maintech_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_maintech_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-maintech-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'ServiceProvider Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('serviceprovider_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_serviceprovider_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-serviceprovider-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'ASP Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('asp_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_asp_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-aspmain-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'PremierSupport Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('premiersupport_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_premiersupport_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-premiersupport-stud1'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'ServiceDelivery Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('servicedelivery_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_servicedelivery_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-sdus1-stud1'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'Site Help Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('sitehelp_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_sitehelp_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
            break;

        // SWTC ********************************************************************************
		// 'Curriculums Portfolio' - add the capabilities, roleshortnames, and roleids for the top-level category.
        // SWTC ********************************************************************************
        case get_string('curriculums_portfolio', 'local_swtc'):
            $caps[$id]['cap'] = get_string('cap_swtc_access_curriculums_portfolio', 'local_swtc');
            $caps[$id]['userid'] = $DB->get_record('user', array('username' => 'test-lenovo-stud'), 'id')->id;
            break;

        default:
    }
}

// print_object("About to print caps :");
// print_object($caps);
// return;
//
// print_object("About to print roles:");
// print_object($roles);

foreach ($roles as $id => $role) {
    /* Build display row:
     * [0] - portfolio
     * [1] - column2
     * [2] - column3
     */
    $num = 0;
    // print_object("id is :$id");
    // print_object("role is :");
    // print_object($role);
    // $columns[$num++] = get_string('rolefullname', 'core_role');

    // Display the role name.
    // print_object("num is (before) :$num");
    $row[$num++] = $role['rolename'];
    // print_object("num is (after) :$num");

    foreach ($columns as $idx => $column) {
        $checked = '';
        // if ($column)
        // print_object("column is :$column");
        // Get access for each portfolio.
        // print_object("idx is :$idx");
        // print_object("caps is :");
        // print_object($caps[$idx]['cap']);
        // print_object("userid is :" . $caps[$idx]['userid']);
        // $context = context_coursecat::instance($idx);
        $cat = core_course_category::get($idx);
        // print_object($cat);
        // print_object($caps[$idx]['cap']);
        // print_object($cat->get_context());
        // print_object($roles[$id]['userid']);
        // if (has_capability($caps[$idx], $cat->get_context(), 906)) {
        // if (has_capability($caps[$idx]['cap'], $cat->get_context(), $caps[$idx]['userid'])) {
        if (has_capability($caps[$idx]['cap'], $cat->get_context(), $roles[$id]['userid'], false)) {
            $checked = 'checked="checked" ';
        }

        // Display portfolio access.
        // $row[$num++] = $column; WORKS. Use as a check...
        // The following is from admin/roles/allow_role_page.php
        // $row[] = '<input type="checkbox" name="' . $name . '" id="' . $name .
        // $a = new stdClass;
        // $a->rolename = $role;
        // $a->portfolio = $column;
        // $tooltip = get_string('allowaccesstoportfolio', 'local_swtc', $a);
        $tooltip = $table->get_cell_tooltip($role['rolename'], $column);
        $name = 's_' . $id . '_' . $role['rolename'];
        $row[$num++] = '<input type="checkbox" name="' . $name . '" id="' . $name .
            '" title="' . $tooltip . '" value="1" ' . $checked . '/>' .
            '<label for="' . $name . '" class="accesshide">' . $tooltip . '</label>';
        // $row[$num++] = '" title="" value="1" ' . $checked . $disabled . '/>' .
        //    '<label for="' . $column . '" class="accesshide"></label>';

    }

    $table->add_data($row);
}


$table->finish_output();

echo $OUTPUT->footer();
