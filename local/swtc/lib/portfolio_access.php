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
 * Functions that return only the data contained in the
 * local_swtc_portfolio_access table.
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/portfolio_access.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/08/21 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

/**
 * Return all the portfolio access data for the user roleid.
 *
 * @param int roleid The roleid of the user.
 *
 * @return array Array containing the portfolio access data.
 *
 * History:
 *
 * 04/08/21 - Original version.
 *
 */
function get_portfolios_access($roleid) {
    global $DB;

    // Using the roleid, get all the portfolios the role has access to.
    return $DB->get_records('local_swtc_port_access', array('roleid' => $roleid), '', 'id, roleid, catid, access');

}

/**
 * Return only the portfolio access data for the user roleid
 * and the one portfolio passed.
 *
 * @param int $portid The portfolio to check.
 * @param int $roleid The roleid of the user.
 *
 * @return array Array containing the portfolio access data.
 *
 * History:
 *
 * 04/08/21 - Original version.
 *
 */
function get_portfolio_access($portid, $roleid) {
    global $DB;

    $params = array('roleid' => $roleid, 'catid' => $portid);
    // Using the catid and roleid, get all the portfolios the role has access to.
    return $DB->get_records('local_swtc_port_access', $params, '', 'id, roleid, catid, access');

}

/**
 * Check what access the user SHOULD have to the portfolio.
 *
 * @param int $roleid The roleid of the user.
 *
 * @return array Array containing the portfolio access data.
 *
 * History:
 *
 * 04/08/21 - Original version.
 *
 */
function get_all_portfolios_for_should_accesstype($roleid) {
    global $DB;

    // Using the roleid, get all the portfolios the role has access to.
    return $DB->get_records('local_swtc_port_access', array('roleid' => $roleid), '', 'id, roleid, catid, access');
    
}

/**
 * Set the user's access to the portfolio (i.e. category).
 *
 * @param int $userid The userid to give access to the portfolio.
 * @param array $catid Array containing the portfolio access data
 * (created above).
 *
 * @return boolean Success.
 *
 * History:
 *
 * 04/10/21 - Original version.
 *
 */
function assign_role_to_portfolio($userid, $accessdata) {

    $params = array();

    return;
    foreach ($accessdata as $id => $data) {
        $context = \context_coursecat::instance($catid);

        // print_object($data);
        // print_object($capability);
        // print_object($option);
        // print_object($data->roleid);
        // print_object($context->id);
        // die;
        if ($id = role_assign($data->roleid, $userid, $context->id)) {
            print_object("successfully assigned $userid for $data->roleid for $catid. id is :$id");
        } else {
            print_object("failed to assign $userid for $data->roleid for $catid");
        }

    }
}
