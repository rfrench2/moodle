<?php

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
 * Version details / History
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 * 10/15/20 - Initial writing.
 * 11/08/20 - Most capabilities not needed anymore since using
 *          moodle/category:viewcourselist.
 * 03/11/21 - Experimenting with has_capability to determine user portfolio access.
 *
 */

 $capabilities = array(
    /**
 	 * View curriculums section.
 	 */
 	'local/swtc:swtc_view_curriculums' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
    /**
	 * View curriculums section.
	 */
	'local/swtc:swtc_view_curriculums' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
 	 * View management reports section.
 	 */
 	'local/swtc:swtc_view_mgmt_reports' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * View student reports section.
 	 */
 	'local/swtc:swtc_view_student_reports' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * For all PremierSupport users, added custom submit assignment capability.
 	 */
 	'local/swtc:swtc_mod_assign_submit_premiersupport' => array(
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_MODULE
 	),
     /**
 	 * For all ServiceDelivery users, added custom submit assignment capability.
 	 */
 	'local/swtc:swtc_mod_assign_submit_servicedelivery' => array(
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_MODULE
 	),
 );
