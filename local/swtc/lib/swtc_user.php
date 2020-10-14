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
 * @subpackage swtc/lib/swtc_user.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	04/14/18 - Initial writing; loads all customized EBGLMS user information.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// 04/14/18: $SESSION is required here.
// Lenovo ********************************************************************************
global $SESSION;


/**
 * Initializes all customized EBGLMS user information and loads it into $SESSION->EBGLMS->USER.
 *
 *      IMPORTANT! $SESSION->EBGLMS MUST be set before calling (i.e. no check for EBGLMS).
 *
 * @param N/A
 *
 * @return N/A
 */
 /**
 * Version details
 *
 * History:
 *
 * 04/16/18 - Initial writing.
 * 04/23/18 - Added timestamp (the time when any value in any field was changed).
 * 06/04/18 - Added capabilities array (to hold all the capabilities of the user).
 * 07/18/18 - Added relateduser to hold the information for the related user.
 * 08/27/18 - Added pscohortname to hold the cohort name if the user has a PremierSupport access type.
 * 11/15/18 - Added accessreports flag (if the user should have access to reporting features) and sdcohortname
 *                  (to hold the cohort name if the user has a ServiceDelivery access type); added generic flag "specialaccess".
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 12/27/18 - Changing pscohortname and sdcohortname to just cohortnames (plural) so that only one check can be done anywhere that
 *						needs it.
 * 01/29/19 - Added groupname to keep the main group for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the groupname is EMEA5).
 * 03/08/19 - Added geoname to keep the main GEO for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the GEO is EMEA); added groupnames field (cannot pre-fill like cohortnames
 *                      because group membership is more fluid than cohort membership).
 * 03/01/20 - Added user timezone to improve performance.
 * PTR2019Q401 - @01 - 03/12/20 - Added global flags for PS / SD management flag (if user is manager or above)
 *                  for easier access checking.
 * PTR2020Q109 - @02 - 05/06/20 - Added field for user profile field "Accesstype2".
 *
 **/

// Lenovo ********************************************************************************
// Setup temporary reference to $EBGLMS->USER.
//      To use: $tmp = $SESSION->EBGLMS->USER
// Lenovo ********************************************************************************
$tmp = $SESSION->EBGLMS->USER;

// Lenovo ********************************************************************************
//
// Lenovo ********************************************************************************
$tmp->userid = null;
$tmp->username = null;
$tmp->user_access_type = null;

// Lenovo ********************************************************************************
//  The following is taken from local_swtc_get_user_access (in locallib.php).
// Lenovo ********************************************************************************
$tmp->portfolio = 'PORTFOLIO_NONE';
$tmp->roleshortname = null;
$tmp->roleid = null;
$tmp->categoryids = null;
$tmp->capabilities = null;
$tmp->timestamp = null;
$tmp->relateduser = null;
$tmp->cohortnames = null;
$tmp->groupname = null;
$tmp->geoname = null;
$tmp->groupnames = null;
$tmp->timezone = null;
$tmp->psmanagement = null;      // @01
$tmp->sdmanagement = null;      // @01
$tmp->user_access_type2 = null;     //@02
