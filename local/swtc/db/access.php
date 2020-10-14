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
 * @copyright  2015 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *	History:
 *	01/26/16 - Initial writing; one new capability per main portfolio category (GTP, LenovoAndIBM, Lenovo, and
 *							ServiceProvider).
 * 03/29/16 - Added 'Lenovo Shared Resources (Master)' access.
 *	05/11/16 - Added 'Maintech Portfolio' access.
 *	08/25/16 - Added "IBM Portfolio"; removed "Lenovo and IBM Portfolio".
 * 10/05/17 - Adding strings for ASP Portfolio.
 * 03/03/18 - Added PremierSupport portfolio and all associated information.
 * 11/12/18 - Added ServiceDelivery and PracticalActivities and all associated information.
 * 11/14/18 - Backed out PracticalActivities and all associated information.
 * 11/28/18 - Added capability strings for viewing curriculums and viewing reports.
 * 01/10/19 - Added capability strings for Curriculums Portfolio.
 * 10/24/19 - For Moodle 3.7+, changing all Lenovo capability strings from dashes (ebg-access-gtp-portfolio) to
 *                  underscores (ebg_access_gtp_portfolio).
 * PTR2019Q401 - @01 - 03/17/20 - For all PS / SD users, added custom submit assignment capability.
 *
 */
 /**
 * Notes about using this file
 *
 *	The capabilities defined here are only read (and copied into the Moodle database) when the Lenovo EBG LMS local plugin module is installed or
*	upgraded. So every time you edit the db/access.php file you must:
*		Increase the plugin's version number by editing the file local/swtc/version.php.
*		Go to the the Administration ► Notifications page, and click through the steps to let Moodle upgrade itself. You should see the name
*		of your module (swtc) in one of the steps.
*
*
 *
 */

$capabilities = array(
	/**
	* GTP section.
	*/
	'local/swtc:ebg_access_gtp_portfolio' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
//		'clonepermissionsfrom' => 'moodle/category:viewhiddencategories'
    ),
	/**
	 * Lenovo section.
	 */
	'local/swtc:ebg_access_lenovo_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * IBM section.
	 */
	'local/swtc:ebg_access_ibm_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * ServiceProvider section.
	 */
	'local/swtc:ebg_access_serviceprovider_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * Lenovo Internal section.
	 */
	'local/swtc:ebg_access_lenovointernal_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * Lenovo Shared Resources (Master) section.
	 */
	'local/swtc:ebg_access_lenovosharedresources' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * Maintech section.
	 */
	'local/swtc:ebg_access_maintech_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * ASP section.
	 */
	'local/swtc:ebg_access_asp_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * PremierSupport section.
	 */
	'local/swtc:ebg_access_premiersupport_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * ServiceDelivery section.
	 */
	'local/swtc:ebg_access_servicedelivery_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * Site Help section.
	 */
	'local/swtc:ebg_access_sitehelp_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
	/**
	 * Curriculums Portfolio section.
	 */
	'local/swtc:ebg_access_curriculums_portfolio' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * View curriculums section.
	 */
	'local/swtc:ebg_view_curriculums' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * View management reports section.
	 */
	'local/swtc:ebg_view_mgmt_reports' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * View student reports section.
	 */
	'local/swtc:ebg_view_stud_reports' => array(
		'riskbitmask' => RISK_CONFIG,
		'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
	),
    /**
	 * @01 - For all PremierSupport users, added custom submit assignment capability.
	 */
	'local/swtc:ebg_mod_assign_submit_premiersupport' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE
	),
    /**
	 * @01 - For all ServiceDelivery users, added custom submit assignment capability.
	 */
	'local/swtc:ebg_mod_assign_submit_servicedelivery' => array(
		'captype' => 'write',
		'contextlevel' => CONTEXT_MODULE
	),
);
