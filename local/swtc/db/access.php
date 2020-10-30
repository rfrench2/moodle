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
 *
 */
 /**
 * Notes about using this file
 *
 *	The capabilities defined here are only read (and copied into the Moodle database) when the SWTC LMS local
 *		plugin module is installed or upgraded. So every time you edit the db/access.php file you must:
 *		Increase the plugin's version number by editing the file local/swtc/version.php.
 *		Go to the the Administration ► Notifications page, and click through the steps to let Moodle
 *			upgrade itself. You should see the name of your module (swtc) in one of the steps.
 *
 *
 *
 */

 $capabilities = array(
 	/**
 	* GTP section.
 	*/
 	'local/swtc:swtc_access_gtp_portfolio' => array(
         'riskbitmask' => RISK_CONFIG,
         'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 //		'clonepermissionsfrom' => 'moodle/category:viewhiddencategories'
     ),
 	/**
 	 * Lenovo section.
 	 */
 	'local/swtc:swtc_access_lenovo_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * IBM section.
 	 */
 	'local/swtc:swtc_access_ibm_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * ServiceProvider section.
 	 */
 	'local/swtc:swtc_access_serviceprovider_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * Lenovo Internal section.
 	 */
 	'local/swtc:swtc_access_lenovointernal_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * Lenovo Shared Resources (Master) section.
 	 */
 	'local/swtc:swtc_access_lenovosharedresources' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * Maintech section.
 	 */
 	'local/swtc:swtc_access_maintech_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * ASP section.
 	 */
 	'local/swtc:swtc_access_asp_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * PremierSupport section.
 	 */
 	'local/swtc:swtc_access_premiersupport_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * ServiceDelivery section.
 	 */
 	'local/swtc:swtc_access_servicedelivery_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
     /**
 	 * Site Help section.
 	 */
 	'local/swtc:swtc_access_sitehelp_portfolio' => array(
 		'riskbitmask' => RISK_CONFIG,
 		'captype' => 'write',
 		'contextlevel' => CONTEXT_COURSECAT
 	),
 	/**
 	 * Curriculums Portfolio section.
 	 */
 	'local/swtc:swtc_access_curriculums_portfolio' => array(
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
 	'local/swtc:swtc_view_stud_reports' => array(
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
