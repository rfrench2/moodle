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
 * Lenovo customized code for Moodle core cohort. Remember to add the following at the top of any module that requires these functions:
 *      use \local_swtc\local\lenovo_cohort_candidate_selector;
 * And put the following within the class that is being overridden:
 *      use lenovo_cohort_candidate_selector;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_cohort_candidate_selector.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	10/11/19 - Initial writing; moved majority of customized code from /cohort/assign.php to functions defined here; added utility functions.
 * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 10/14/19 - Added find_users.
 *
 **/

namespace local_swtc\local;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');
// require_once($CFG->dirroot.'/cohort/locallib.php');

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

// use core_text;
// use html_writer;
// use stdClass;
// use moodle_url;
// use coursecat_helper;
// use lang_string;

// Lenovo ********************************************************************************.
// The following code is copied from:
//          Class: cohort_candidate_selector
//          Location: /cohort/locallib.php
//          Version: Moodle 2019052002.07
//          Release: 3.7.2+ (Build: 20191008)
//
//  10/15/19 - Code copied to here; added Lenovo customized code.
// Lenovo ********************************************************************************.
/**
 * Cohort assignment candidates
 */
class lenovo_cohort_candidate_selector extends \user_selector_base {
    protected $cohortid;

    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
        parent::__construct($name, $options);
    }

    /**
     * Lenovo override of find_users in /cohort/assign.php.
     *
     * Candidate users
     * @param string $search
     * @return array
     *
     * Lenovo history:
     *
     * 11/15/18 - Added additional WHERE condition if $USER role is PremierSupport-manager/admin or ServiceDelivery-manager/admin.
     * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
     *                      removed all three and changed to customized capabilities.
     * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
     *                      for Students are applied in the category context.
     * 12/14/18 - Due to problems with contexts and user access, removing has_capability checking and rolling back to checking
     *                      Accesstype for PremierSupport and ServiceDelivery user types.
     * 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
     *                      to search for access types.
     * 03/03/19 - Added PS/AD site administrator user access types.
     * 03/06/19 - Added PS/SD GEO administrator user access types.
     * 10/11/19 - Moved majority of customized code from /cohort/assign.php to functions defined here.
     * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
     * 10/14/19 - Added find_users.
     *
    */
    public function find_users($search) {
        global $DB, $USER, $SESSION;

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();

        // Other Lenovo variables.
        $user_access_type = $swtc_user->user_access_type;

        // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
        $access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
        $access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
        $access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
        $access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

        $access_lenovo_sd_mgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;
        $access_lenovo_sd_admin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
        $access_lenovo_sd_geoadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin;
        $access_lenovo_sd_siteadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin;
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
			$messages[] = "Entering lenovo_cohort_candidate_selector===find_users.enter===.";
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }

        // Lenovo ********************************************************************************.
        // The following code is copied from:
        //          Function: find_users
        //          Location: /cohort/locallib.php
        //          Version: Moodle 2019052002.07
        //          Release: 3.7.2+ (Build: 20191008)
        //
        //  10/14/19 - Code copied to here; added Lenovo customized code.
        // Lenovo ********************************************************************************.
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['cohortid'] = $this->cohortid;

        // Lenovo ********************************************************************************.
        // Add additional WHERE condition if $USER role is PremierSupport-manager/admin or ServiceDelivery-manager/admin.
        // Lenovo ********************************************************************************.
        // Adding a WHERE condition and params per the function notes for users_search_sql in /lib/datalib.php:
        //          You can combine this SQL with an existing query by adding 'AND $sql' to the
        //          WHERE clause of your query (where $sql is the first element in the array
        //          returned by this function), and merging in the $params array to the parameters
        //          of your query (where $params is the second element). Your query should use
        //          named parameters such as :param, rather than the question mark style.
        // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
        //                          the capabilities for Students are applied in the category context.
        // 12/18/18 - IMPORTANT - Since the access types are in the form of "access_premiersupport_admin_ap1" or
        //          "access_premiersupport_mgr_ca3", we must use the "base" string (for example, "access_premiersupport_admin")
        //          and perform a string compare (rather than a simple ==) to check for the user type.
        // 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match
        //          to search for access types.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // Lenovo ********************************************************************************.
        if (has_capability('local/swtc:ebg_view_mgmt_reports', \context_system::instance())) {
            $where[] = " u.id  IN  (SELECT userid FROM {user_info_data} WHERE (data LIKE :accesstype1))";
            // Set additional WHERE condition.
            if ((preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_ps_geoadmin, $user_access_type)) || (preg_match($access_ps_siteadmin, $user_access_type))) {
                $whereparams['accesstype1'] = "%Premier%";
            } else if ((preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type)) || (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) || (preg_match($access_lenovo_sd_siteadmin, $user_access_type))) {
                $whereparams['accesstype1'] = "%ServiceDelivery%";
            }

            // Lenovo ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "New WHERE statement follows:";
                $messages[] = print_r($wherecondition, true);
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
        }

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
            LEFT JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                WHERE cm.id IS NULL AND $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                // Lenovo ********************************************************************************.
                if (isset($debug)) {
                    $messages[] = "Lenovo ********************************************************************************.";
                    $messages[] = "Leaving lenovo_cohort_candidate_selector===find_users.exit(1).";
                    $messages[] = "Lenovo ********************************************************************************.";
                    debug_logmessage($messages, 'both');
                    unset($messages);
                }
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            // Lenovo ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "Lenovo ********************************************************************************.";
                $messages[] = "Leaving lenovo_cohort_candidate_selector===find_users.exit(2).";
                $messages[] = "Lenovo ********************************************************************************.";
                debug_logmessage($messages, 'both');
                unset($messages);
            }
            return array();
        }


        if ($search) {
            $groupname = get_string('potusersmatching', 'cohort', $search);
        } else {
            $groupname = get_string('potusers', 'cohort');
        }

        // Lenovo ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Leaving lenovo_cohort_candidate_selector===find_users.exit(3).";
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
        $options['file'] = 'cohort/locallib.php';
        return $options;
    }

}
