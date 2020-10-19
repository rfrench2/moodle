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
	* Portfolio One section.
	*/
	'local/swtc:swtc_access_one_portfolio' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
		'contextlevel' => CONTEXT_COURSECAT
//		'clonepermissionsfrom' => 'moodle/category:viewhiddencategories'
    ),
	/**
	 * Portfolio Two section.
	 */
	'local/swtc:swtc_access_two_portfolio' => array(
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
);
