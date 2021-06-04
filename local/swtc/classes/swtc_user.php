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
 * @subpackage swtc/classes/swtc_user.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 * 10/16/20 - Changed to swtc class.
 * 04/09/21 - Removed portfolio and categoryids from swtc_user class.
 * 04/15/21 - Removed roleshortname as they are not used.
 *
 **/

namespace local_swtc;

use stdClass;
use cache;
use core_course_category;
use context_coursecat;
use core_date;
use DateTime;
use core_user;

use \local_swtc\swtc_debug;
use \local_swtc\swtc_counter;

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot . '/local/swtc/lib/portfolio_access.php');

/**
 * Initializes all customized SWTC user information and loads it into $SESSION->SWTC->USER.
 *
 *      IMPORTANT!
 *          DO NOT call this directly. Use $swtc_set_user from /lib/swtc_userlib.php.
 *
 * @param N/A
 *
 * @return $SESSION->SWTC->USER.
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 */
class swtc_user {
    /**
     * Store the user's id.
     * @var integer
     */
    private $userid;

    /**
     * Store the user's username.
     * @var string
     */
    private $username;

    /**
     * Store the user's accesstype.
     * @var string
     */
    private $accesstype;

    /**
     * The user's accesstype 2.
     * @var string
     *
     */
    private $accesstype2;

    /**
     * The user's role id.
     * @var integer
     */
    private $roleid;

    /**
     * The time of this action.
     * @var DateTime
     */
    private $timestamp;

    /**
     * The timezone of the user.
     * @var DateTimeZone
     */
    private $timezone;

    /**
     * If an admin is performing an action on behalf of another user,
     * this is the related user's id.
     * @var integer
     */
    private $relateduser;

    /**
     * The cohort names the user is a member of (if any).
     * @var array
     */
    private $cohortnames;

    /**
     * The group sort string that should be used to
     * find all the groups the user is a member of.
     * @var string
     */
    private $groupsort;

    /**
     * The GEO the user is a member of.
     * @var string
     */
    private $geoname;

    /**
     * The preg_match string that should be used to
     * find all the groups the user is a member of.
     * @var string
     */
    private $groupname;

    /**
     * The groups the user is a member of (if any).
     * @var array
     */
    private $groupnames;

    /**
     * Is the user in Premier Support?
     * @var array
     */
    private $psuser;

    /**
     * Is the user in Premier Support management?
     * @var array
     */
    private $psmanagement;

    /**
     * Is the user in Service Delivery?
     * @var array
     */
    private $sduser;

    /**
     * Is the user in Service Delivery management?
     * @var array
     */
    private $sdmanagement;

    /**
     * Although constructor is public, use /locallib/swtc_get_user() to
     * retrieve SWTC user information.
     */
    public function __construct($args=array()) {
        $this->userid = $args['userid'] ?? null;
        $this->username = $args['username'] ?? null;
        $this->accesstype = null;
        $this->accesstype2 = null;
        $this->roleid = null;
        $this->timestamp = null;
        $this->timezone = null;
        $this->relateduser = null;
        $this->cohortnames = null;
        $this->groupsort = null;
        $this->geoname = null;
        $this->groupname = null;
        $this->groupnames = array();
        $this->psuser = null;
        $this->psmanagement = null;
        $this->sduser = null;
        $this->sdmanagement = null;
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
    public function set_userid($userid) {
        $this->userid = (isset($userid)) ? $userid : null;
    }

    public function set_username($username) {
        $this->username = (isset($username)) ? $username : null;
    }

    public function set_accesstype($accesstype) {
        $this->accesstype = $accesstype;
    }

    public function set_accesstype2($accesstype2) {
        $this->accesstype2 = $accesstype2;
    }

    public function set_roleid($accesstype) {
        global $DB;

        // SWTC ********************************************************************************.
        // Check for Lenovo access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_lenovo_pregmatch_siteadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_pregmatch_inst', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_pregmatch_stud', 'local_swtc'), $accesstype))) {

            if (stripos($accesstype, get_string('role_lenovo_siteadministrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_siteadministrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_lenovo_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_lenovo_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('role_lenovo_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_lenovo_student', 'local_swtc');
            }
            // SWTC ********************************************************************************.
            // Check for AV-GTP-admin, AV-GTP-inst, or AV-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_av_gtp_pregmatch', 'local_swtc'), $accesstype)) {

            if (stripos($accesstype, get_string('access_av_siteadministrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_av_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_av_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_av_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }
            // SWTC ********************************************************************************.
            // Check for IM-GTP-admin, IM-GTP-inst, or IM-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_im_gtp_pregmatch', 'local_swtc'), $accesstype)) {

            if (stripos($accesstype, get_string('access_im_siteadministrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_im_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_im_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_im_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }
            // SWTC ********************************************************************************.
            // Check for LQ-GTP-admin, LQ-GTP-inst, or LQ-GTP-stud user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_lq_gtp_pregmatch', 'local_swtc'), $accesstype)) {

            if (stripos($accesstype, get_string('access_lq_siteadministrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_siteadministrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_lq_administrator', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_administrator', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_lq_instructor', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_instructor', 'local_swtc');
            } else if (stripos($accesstype, get_string('access_lq_student', 'local_swtc')) !== false) {
                $roleshortname = get_string('role_gtp_student', 'local_swtc');
            }
            // SWTC ********************************************************************************.
            // Check for IBM-stud user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_ibm_pregmatch', 'local_swtc'), $accesstype)) {

            $roleshortname = get_string('role_ibm_student', 'local_swtc');

            // SWTC ********************************************************************************.
            // Check for ServiceProvider-stud user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_serviceprovider_pregmatch_stud', 'local_swtc'), $accesstype)) {

            $roleshortname = get_string('role_serviceprovider_student', 'local_swtc');

            // SWTC ********************************************************************************.
            // Check for Maintech-stud user
            // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
            // ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_maintech_pregmatch_stud', 'local_swtc'), $accesstype)) {

            $roleshortname = get_string('role_maintech_student', 'local_swtc');

            // SWTC ********************************************************************************.
            // Check for ASP-Maintech-stud user
            // 11/25/19 - Changing stripos to strncasecmp in /local/swtc/lib/locallib/local_swtc_get_user_access to fix
            // ASP-Maintech-stud access (to add access to ASP Portfolio and Service Provider Portfolio).
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_asp_maintech_pregmatch_stud', 'local_swtc'), $accesstype)) {

            $roleshortname = get_string('role_asp_maintech_student', 'local_swtc');

            // SWTC ********************************************************************************.
            // Check for PremierSupport users
            // SWTC ********************************************************************************.
        } else if ($this->is_psuser()) {
            if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_administrator', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_student', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_manager', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_siteadministrator', 'local_swtc');
            } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_premiersupport_geoadministrator', 'local_swtc');
            }

            // SWTC ********************************************************************************.
            // Check for ServiceDelivery users
            // SWTC ********************************************************************************.
        } else if ($this->is_sduser()) {
            if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_administrator', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_student', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_manager', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_siteadministrator', 'local_swtc');
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                $roleshortname = get_string('role_servicedelivery_geoadministrator', 'local_swtc');
            }

            // SWTC ********************************************************************************.
            // Check for Self support user
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_selfsupport_pregmatch', 'local_swtc'), $accesstype)) {

            $roleshortname = get_string('role_selfsupport_student', 'local_swtc');

        }

        // SWTC ********************************************************************************.
        // Get the roleid from the roleshortname.
        // SWTC ********************************************************************************.
        $role = $DB->get_record('role', array('shortname' => $roleshortname), '*', MUST_EXIST);
        $this->roleid = $role->id;

        return;
    }

    public function set_timestamp() {
        $timezone = core_date::get_user_timezone_object();
        $today = new DateTime("now", $timezone);
        $this->timestamp = $today->format('H:i:s.u');
        return $this->timestamp;
    }

    public function set_timezone() {
        $this->timezone = core_date::get_user_timezone_object();
        return $this->timezone;
    }

    public function set_relateduser() {
        $this->relateduser = null;
    }

    public function set_cohortnames($accesstype) {
        global $DB;

        // SWTC ********************************************************************************.
        // Add for all PremierSupport access types.
        // SWTC ********************************************************************************.
        if ($this->is_psuser()) {
            // Get cohort the user is a member of.
            $cohorts = array();
            $sql = 'SELECT c.*
                FROM {cohort} c
                JOIN {cohort_members} cm ON (c.id = cm.cohortid)
                WHERE (cm.userid = ?) AND (c.visible = 0)';
            $cohorts = $DB->get_records_sql($sql, array($this->userid));
            foreach ($cohorts as $cohort) {
                $this->cohortnames .= $cohort->name . ' ';
            }
            // SWTC ********************************************************************************.
            // Add for all ServiceDelivery access types.
            // SWTC ********************************************************************************.
        } else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))) {
            // Get cohort the user is a member of.
            $cohorts = array();
            $sql = 'SELECT c.*
                FROM {cohort} c
                JOIN {cohort_members} cm ON (c.id = cm.cohortid)
                WHERE (cm.userid = ?) AND (c.visible = 0)';
            $cohorts = $DB->get_records_sql($sql, array($this->userid));
            foreach ($cohorts as $cohort) {
                $this->cohortnames .= $cohort->name . ' ';
            }
        }
    }

    public function set_groupsort($accesstype) {

        // SWTC ********************************************************************************.
        // PremierSupport site administrators
        // SWTC ********************************************************************************.
        if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_premiersupport_pregmatch_siteadmins', 'local_swtc');
            // SWTC ********************************************************************************.
            // PremierSupport GEO administrators
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_premiersupport_pregmatch_geoadmins', 'local_swtc');
            // SWTC ********************************************************************************.
            // PremierSupport administrators
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_premiersupport_pregmatch_admins', 'local_swtc');
            // SWTC ********************************************************************************.
            // PremierSupport managers
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_premiersupport_pregmatch_mgrs', 'local_swtc');
            // SWTC ********************************************************************************.
            // ServiceDelivery site administrators
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_lenovo_servicedelivery_pregmatch_siteadmins', 'local_swtc');
            // SWTC ********************************************************************************.
            // ServiceDelivery GEO administrators
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_lenovo_servicedelivery_pregmatch_geoadmins', 'local_swtc');
            // SWTC ********************************************************************************.
            // ServiceDelivery administrators
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_lenovo_servicedelivery_pregmatch_admins', 'local_swtc');
            // SWTC ********************************************************************************.
            // ServiceDelivery managers
            // SWTC ********************************************************************************.
        } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype)) {
            $this->groupsort = get_string('cohort_lenovo_servicedelivery_pregmatch_mgrs', 'local_swtc');
        }
    }

    public function set_geoname($accesstype) {

        // The following pattern will match "<whatever>-US1-<whatever> or "<whatever>-EM5-<whatever>".
        $cmpgeoadmins = '/-([A-Z][A-Z])-/';
        $cmpallotherroles = '/-([A-Z][A-Z]+[1-9])-/';

        // SWTC ********************************************************************************.
        // Add for PS / SD management access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))) {
            $this->geoname = '%';
        } else if ((preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))) {
            $this->geoname = '%';
        } else if ((preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Add for all other PS / SD access types.
            // SWTC ********************************************************************************.
            preg_match($cmpallotherroles, $accesstype, $match);
            $this->geoname = !empty($match) ? $match[1] : null;
        } else if ((preg_match(get_string('access_lenovo_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_pregmatch_siteadmin', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Add for all Lenovo administrator access types.
            // SWTC ********************************************************************************.
            $this->geoname = '%';
        }
    }

    public function set_groupname($accesstype) {

        // The following pattern will match "<whatever>-US1-<whatever> or "<whatever>-EM5-<whatever>".
        $cmpgeoadmins = '/-([A-Z][A-Z])-/';
        $cmpallotherroles = '/-([A-Z][A-Z]+[1-9])-/';

        // SWTC ********************************************************************************.
        // Add for PS / SD management access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))) {
            $this->groupname = '%';
        } else if ((preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))) {
            $this->groupname = '%';
            preg_match($cmpgeoadmins, $accesstype, $match);
            $this->groupname = !empty($match) ? $match[1] : null;
        } else if ((preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Add for all other PS / SD access types.
            // SWTC ********************************************************************************.
            preg_match($cmpallotherroles, $accesstype, $match);
            $this->groupname = !empty($match) ? $match[1] : null;
        } else if ((preg_match(get_string('access_lenovo_pregmatch_admin', 'local_swtc'), $accesstype))
            || (preg_match(get_string('access_lenovo_pregmatch_siteadmin', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Add for all Lenovo administrator access types.
            // SWTC ********************************************************************************.
            $this->groupname = '%';
        }
    }

    public function set_groupnames($groupnames) {

        $this->groupnames = array_merge($this->groupnames, $groupnames);

        return $this->groupnames;
    }

    public function set_psuser($accesstype) {
        // SWTC ********************************************************************************.
        // Set for all Premier Support access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_stud', 'local_swtc'), $accesstype))) {
            $this->psuser = true;
        }
    }

    public function set_psmanagement($accesstype) {
        // SWTC ********************************************************************************.
        // Set for all Premier Support management access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))) {
            $this->psmanagement = true;
        }
    }

    public function set_sduser($accesstype) {
        // SWTC ********************************************************************************.
        // Set for all Service Delivery access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_stud', 'local_swtc'), $accesstype))) {
            $this->sduser = true;
        }
    }

    public function set_sdmanagement($accesstype) {
        // SWTC ********************************************************************************.
        // Set for all Service Delivery management access types.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))) {
            $this->sdmanagement = true;
        }
    }

    /**
     * Set (assign) user access to all portfolios.
     * *
     * @param array $eventdata The event data.
     *
     * History:
     *
     * 04/09/21 - Initial writing; complete re-write of set_user_role.
     *
     */
    public function set_user_access($eventdata) {
        global $USER;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        // SWTC ********************************************************************************.
        $debug = swtc_get_debug();

        // Load the event name.
        $eventname = $eventdata->eventname;

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering /local/swtc/classes/swtc_user.php.===set_user_access.enter.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "swtc_user array follows :";
            $messages[] = print_r($this, true);
            $messages[] = "swtc_user array ends.";
            $messages[] = "eventname follows :";
            $messages[] = print_r($eventname, true);
            $messages[] = "eventname ends.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ***********************************************************************************.
        // Quick check...if user is not logged in ($USER->id is empty), skip all this and return...
        // SWTC ***********************************************************************************.
        if (empty($USER->id)) {
            if (isset($debug)) {
                $debug->logmessage("User has not logged on yet; set_user_role.exit===2.1===.", 'logfile');
            }
            return;
        }

        // SWTC ********************************************************************************.
        // Trick to refresh the users roles without logging out and logging in again.
        // If the user is already logged OUT and their role changes, they get an updated view next
        // time they login. However, if the user is already logged IN and their role changes, we must
        // reload a web page for the new role assignments to take affect. This means capturing the
        // course_viewed message, calling purge_all_caches, and immediately returning.
        //
        // Will tell user to click on the home page link to refresh their access, but viewing any
        // course will work. In fact, just clicking refresh in the web browser should work (have
        // not testing will all browsers in all circumstances).
        // SWTC ********************************************************************************.
        if ($eventname == '\core\event\course_viewed') {
            if (isset($debug)) {
                if ($eventdata->courseid == 1) {
                    $debug->logmessage("User is viewing the front page (courseid = 1). Continuing...", 'logfile');
                    purge_all_caches();
                } else {
                    $debug->logmessage("User is viewing a course. About to return.", 'logfile');
                    $debug->logmessage("Leaving set_user_role.exit===11===.", 'logfile');
                    purge_all_caches();
                    return;
                }
            }
        }

        // SWTC ********************************************************************************.
        // Important! Properties passed via $eventdata defined in
        // https://docs.moodle.org/dev/Event_2#Information_contained_in_events
        // Note: <strong> and </strong> begins and ends bold printing.
        // Also adds CRLF to end of print statement.
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "==========1.2===========";
            $messages[] = "eventdata properties follow...";
            $messages[] = "event message :<strong>$eventdata->eventname</strong>";
            $messages[] = "contextid is : <strong>$eventdata->contextid</strong>";
            $messages[] = "possible contextlevel values are: CONTEXT_SYSTEM (10); CONTEXT_USER (30); CONTEXT_COURSECAT (40);
            CONTEXT_COURSE (50); CONTEXT_MODULE (70); CONTEXT_BLOCK (80).";
            $messages[] = "contextlevel is :<strong>$eventdata->contextlevel</strong>";
            $messages[] = "courseid is :<strong>$eventdata->courseid</strong>";
            $messages[] = "contextinstanceid is :<strong>$eventdata->contextinstanceid</strong>";
            $messages[] = "userid is :<strong>$eventdata->userid</strong> (either userid, 0 when not logged in, or -1 when other).";
            $messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than
            $eventdata->userid, admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "all eventdata properties follow :";
            $messages[] = print_r($eventdata, true);
            $messages[] = "all eventdata properties end.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Check to see if the administrator is working on behalf of a user, or the actual user is doing something.
        // Important! If an administrator is working on behalf of a user (for example, updating the user's profile
        // or creating a new user), $eventdata->relateduserid will be the userid of the user and the userid the rest
        // of the plug-in should work with. If a "regular" user is doing something, $eventdata->relateduserid will
        // be empty.
        //
        // Sets variables:
        // $this->userid    The userid of the "actual" user (not the administrator).
        // $this->username  The username of the "actual" user (not the administrator).
        // $this->accesstype  The most important variable; triggers all the rest that follows.
        // $this->timestamp
        // $this->accesstype2
        // SWTC ********************************************************************************.
        if (!empty($eventdata->relateduserid) && ($eventdata->objectid !== $eventdata->relateduserid)) {
            if (isset($debug)) {
                switch ($eventname) {
                    // SWTC ********************************************************************************.
                    // Event \core\event\user_loggedinas
                    // SWTC ********************************************************************************.
                    case '\core\event\user_loggedinas':
                        $debug->logmessage("Admin has logged on as user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_updated
                    // SWTC ********************************************************************************.
                    case '\core\event\user_updated':
                        $debug->logmessage("Admin has updated a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_created
                    // SWTC ********************************************************************************.
                    case '\core\event\user_created':
                        $debug->logmessage("Admin has created a user (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\role_assigned
                    // SWTC ********************************************************************************.
                    case '\core\event\role_assigned':
                        $debug->logmessage("Admin has triggered a role assignment on behalf of a user
                (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_deleted
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_deleted':
                        $debug->logmessage("Admin has triggered an unenrollment from a course on behalf of a user
                (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_updated
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_updated':
                        $debug->logmessage("Admin has triggered an updated enrollment in a course on behalf of a user
                (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event \core\event\user_enrolment_created
                    //
                    // If user_enrolment_created was done by a cohort, eventdata will look like the following (Notes are embedded):
                    //
                    // core\event\user_enrolment_created Object
                    // (
                    // [data:protected] => Array
                    // (
                    // [eventname] => \core\event\user_enrolment_created
                    // [component] => core
                    // [action] => created
                    // [target] => user_enrolment
                    // [objecttable] => user_enrolments
                    // [objectid] => 139952
                    // [crud] => c
                    // [edulevel] => 0
                    // [contextid] => 3819
                    // [contextlevel] => 50                (CONTEXT_COURSE)
                    // [contextinstanceid] => 159        (courseid 159 = ES11611)
                    // [userid] => 4                            (4 = rfrench)
                    // [courseid] => 159                    (courseid 159 = ES11611)
                    // [relateduserid] => 12983        (userid of user dropped in cohort)
                    // [anonymous] => 0
                    // [other] => Array
                    // (
                    // [enrol] => cohort
                    // )
                    //
                    // [timecreated] => 1547760579
                    // )
                    //
                    // [logextra:protected] =>
                    // [context:protected] => context_course Object
                    // (
                    // [_id:protected] => 3819
                    // [_contextlevel:protected] => 50
                    // [_instanceid:protected] => 159            (courseid 159 = ES11611)
                    // [_path:protected] => /1/511/513/514/3819
                    // [_depth:protected] => 5
                    // )
                    //
                    // [triggered:core\event\base:private] => 1
                    // [dispatched:core\event\base:private] => 1
                    // [restored:core\event\base:private] =>
                    // [recordsnapshots:core\event\base:private] => Array
                    // (
                    // [user_enrolments] => Array
                    // (
                    // [139952] => stdClass Object
                    // (
                    // [id] => 139952
                    // [status] => 0
                    // [enrolid] => 4887
                    // [userid] => 12983
                    // [timestart] => 0
                    // [timeend] => 0
                    // [modifierid] => 4
                    // [timecreated] => 1547760579
                    // [timemodified] => 1547760579
                    // [enrol] => cohort
                    // [courseid] => 159
                    // )
                    // )
                    // )
                    // )
                    // SWTC ********************************************************************************.
                    case '\core\event\user_enrolment_created':
                        $debug->logmessage("Admin has triggered an enrollment in a course on behalf of a user
                (eventname is <strong>$eventname</strong>).", 'both');
                        break;

                    // SWTC ********************************************************************************.
                    // Event - all others
                    // SWTC ********************************************************************************.
                    default:
                        $debug->logmessage("Something happened. Log it. (eventname is <strong>$eventname</strong>).", 'both');
                        break;
                }
            }

            // Set the users userid and access_type.
            // 07/12/18 - Added call to get_relateduser.
            // 07/18/18 - Set $userrelated to $this->relateduser (otherwise $userrelated is NULL).
            $userrelated = ($eventdata->objectid !== $eventdata->relateduserid) ?
                $this->get_relateduser($eventdata->relateduserid) : null;
            $this->relateduser = $userrelated;

            if (isset($debug)) {
                $messages[] = "In top of set_user_role (relateduserid). Setting this->relateduser
        information of $eventdata->relateduserid. ===11===.";
                $messages[] = "get_relateduser follow:";
                $messages[] = print_r($this->relateduser, true);
                $messages[] = "get_relateduser end.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            if (isset($debug)) {
                $messages[] = "In top of local_swtc_set_user_role (relateduserid). After setting swtc_user to new values.";
                $messages[] = "swtc_user array follows :";
                $messages[] = print_r($this, true);
                $messages[] = "swtc_user array ends.";
                $debug->logmessage($messages, 'both');
                unset($messages);
            }
        }

        if (isset($debug)) {
            $messages[] = "The userid that will be used throughout this plugin is :<strong>$this->userid</strong>.";
            $messages[] = "The username that will be used throughout this plugin is :<strong>$this->username</strong>.";
            $messages[] = "The accesstype is :<strong>$this->accesstype</strong>.";
            $messages[] = "The timestamp is :<strong>$this->timestamp</strong>.";
            $messages[] = "relateduserid is :<strong>$eventdata->relateduserid</strong> (if different than $eventdata->userid,
            admin is working with this userid).";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        if ($eventname == '\core\event\role_assigned') {

        }

        // SWTC ********************************************************************************.
        // Get the current list of categories that a user of this access type
        // should have acces to.
        // [97] => stdClass Object
        // (
        // [id] => 97
        // [roleid] => 21
        // [catid] => 14
        // [access] => -1
        // )
        // SWTC ********************************************************************************.
        $portfolios = get_portfolios_access($this->roleid);

        foreach ($portfolios as $id => $portfolio) {
            // Check to see if the user has any roles assigned to this top-level category.
            $context = context_coursecat::instance($portfolio->catid);
            // SWTC - 04/15/21 - START HERE...
            // SWTC - 04/13/21 - Leave the following here for now...
            // print_object("testing of has_capability follows - $portfolio->catid:");
            // if (has_capability('moodle/category:viewcourselist', $context)) {
            // print_object("the user DOES have capability");
            // } else {
            // print_object("the user does NOT have capability");
            // }

            $userroles = get_user_roles($context, $this->userid, false);
            $countroles = count($userroles);

            if (isset($debug)) {
                $catname = core_course_category::get($portfolio->catid, MUST_EXIST, true)->name;
                if ($countroles != 0) {
                    $messages[] = "Userid <strong>$this->userid DOES</strong> have userroles in <strong>$catname</strong>.";
                    $messages[] = "The number of roles userid <strong>$this->userid has assigned in <strong>$catname</strong>
                        is <strong>==>$countroles<==</strong>.";
                    $messages[] = "Next is to check if they SHOULD have access.";
                    $messages[] = "About to print userroles.";
                    $messages[] = print_r($userroles, true);
                    $messages[] = "Finished printing userroles. About to print context.";
                    $messages[] = print_r($context, true);
                    $messages[] = "Finished printing context.";
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
                } else {
                    $debug->logmessage("Userid <strong>$this->userid</strong> does <strong>NOT</strong>
                        have any roles in <strong>$catname</strong>.", 'both');
                }

                // SWTC ********************************************************************************.
                // Does the current user have any roles assigned in this category? If so, check to make sure
                // it's the CORRECT role. What does CORRECT role mean? The CORRECT role would be the one that
                // the user should have been assigned (based on the 'Access type' flag).
                // Note: The only way for a user to have more than one role assigned to them in a top-level
                // category is if an administrator purposely did it (since 'Access type' is a single-select,
                // it is impossible to get more than one role from it). For example, a user was given the
                // GTP-student AND GTP-instructor role in the 'GTP Portfolio' top-level category.
                // SWTC ********************************************************************************.
                if ($countroles != 0) {
                    // For each of the user roles in this portfolio,
                    // check to see if the roles match.
                    foreach ($userroles as $role) {
                        // The user's role id is in role->id. Compare it to
                        // data->roleid. If they compare, they have the correct
                        // access.
                        // For each of the roles, if $role->id == $this->roleid, the user has the correct access.
                        // If they don't match, remove the user from the role.
                        if ($role->id == $this->roleid) {
                            if ($portfolio->access != CAP_ALLOW) {
                                // The user should NOT have access to this portfolio.
                                // Unassign the user from the incorrect role...
                                role_unassign($role->id, $this->userid, $context->id);
                            }
                        } else {
                            // Unassign the user from the incorrect role...
                            role_unassign($role->id, $this->userid, $context->id);
                        }
                    }
                } else {
                    // The user does NOT have even ONE role in the category.
                    // But should they? If so, give the user the access they
                    // should have.
                    // Assign the user to the correct role...
                    // If the user should have access to this portfolio, assign it.
                    if ($portfolio->access == CAP_ALLOW) {
                        // Assign the user to the correct role...
                        role_assign($portfolio->roleid, $this->userid, $context->id);
                    }
                }
            }
        }

        return;

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
     * 10/14/20 - Initial writing.
     *
     **/
    public function get_userid() {
        return $this->userid;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_accesstype() {
        return $this->accesstype;
    }

    public function get_accesstype2() {
        return $this->accesstype2;
    }

    public function get_roleid() {
        return $this->roleid;
    }

    public function get_timestamp() {
        return $this->timestamp;
    }

    public function get_timezone() {
        return $this->timezone;
    }

    /**
     * Setup most, but not all, the characteristics of  SESSION->SWTC->USER->relateduser.
     *
     * @param  integer $userid The userid of the user.
     * @return swtc_user         The related user's information.
     *
     * History
     *
     * 02/22/21 - Initial writing.
     *
     */
    public function get_relateduser($userid) {

        // Temporary variable to hold related userid information.
        $relateduser = new stdClass();
        // SWTC ********************************************************************************.
        // Set some of the SWTC->relateduser variables that will be used IF a relateduserid is found.
        // SWTC ********************************************************************************.
        // Get all the user information based on the userid passed in.
        // Note: '*' returns all fields (normally not needed).
        $relateduser = core_user::get_user($userid);
        profile_load_data($relateduser);

        // SWTC ********************************************************************************.
        // Since we are using get_user and profile_load_data, there is no need to copy any other fields.
        // SWTC ********************************************************************************.
        // $relateduser->username = $relateduser->username.

        // SWTC ********************************************************************************.
        // The following fields MUST be added to $relateduser (as they normally do not exist).
        // SWTC ********************************************************************************.
        $relateduser->userid = $userid;
        $relateduser->accesstype = $relateduser->profile_field_accesstype;

        // Add user timezone to improve performance.
        $relateduser->timezone = $this->set_timezone();
        $relateduser->timestamp = $this->set_timestamp();

        return $relateduser;
    }

    public function get_cohortnames() {
        return $this->cohortnames;
    }

    public function get_groupsort() {
        return $this->groupsort;
    }

    public function get_geoname() {
        $this->geoname = null;
    }

    public function get_groupname() {
        return $this->groupname;
    }

    public function get_groupnames($submenu = "") {
        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        // SWTC ********************************************************************************.
        $debug = swtc_get_debug();
        $returnvalue = array();

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering /local/swtc/classes/swtc_user.php.===get_groupnames.enter.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "The submenu that is being looked for follows :";
            $messages[] = print_r($submenu, true);
            $messages[] = "Submenu ends; the existing this->groupnames follow :";
            $messages[] = print_r($this->groupnames, true);
            $messages[] = "Existing this->groupnames ends.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        if (empty($submenu)) {
            $returnvalue = $this->groupnames;
        } else {
            if (!empty($this->groupnames[$submenu])) {
                $returnvalue = $this->groupnames[$submenu];
            } else {
                $returnvalue = array();
            }
        }

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Leaving /local/swtc/classes/swtc_user.php.===get_groupnames.exit.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "About to return the following :";
            $messages[] = print_r($returnvalue, true);
            $messages[] = "Returnvalue ends.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        return $returnvalue;
    }

    /**
     * All Is methods for all properties.
     *
     * Is methods:
     * @param N/A
     * @return value
     *
     * History:
     *
     * 05/12/21 - Initial writing.
     *
     **/
    public function is_psuser() {
        return $this->psuser;
    }

    public function is_psmanagement() {
        return $this->psmanagement;
    }

    public function is_sduser() {
        return $this->sduser;
    }

    public function is_sdmanagement() {
        return $this->sdmanagement;
    }
}
