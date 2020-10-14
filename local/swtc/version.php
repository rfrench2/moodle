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
 * Version details / History
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *  12/13/15 - Initial writing.
 * 	03/14/16 - Incremented version so 'Lenovo Internal Portfolio' option can be used.
 * 	03/29/16 - Incremented version so 'Lenovo Shared Resources (Master)' option can be used.
 * 	04/11/16 - Incremented version so that the '\core\event\user_enrolment_created' can be captured.
 * 	05/11/16 - Incremented version so 'Maintech portfolio' can be added.
 * 	08/11/16 - Incremented version so 'Arrow' and 'Global Knowledge' can be removed.
 *	08/25/16 - Changing from "Lenovo and IBM Portfolio" to just "IBM Portfolio".
 *  10/05/17 - Adding strings for ASP Portfolio.
 *
 */
 /**
 * Notes about using this file
 *
 *	The fields used are:
 *		version - the version of this plugin; MUST be incremented IF swtc/db/access.php is updated.
*		requires - the version of Moodle that is required (2015111600 is Moodle 3.0).
*		component - the name of the plugin (since it is a local plugin, preface the name with 'local_').
*		release - some string that defines some values that mean something to us.
*		maturity - change to MATURITY_*** when GA level.
*		dependencies - Important! Since this plugin may (at some point) modify the BCU theme code, list 'theme_bcu' (since BCU has a
*			dependency on 'block_course_overview', list it also).
*
*
 *
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2020101206;
$plugin->requires  = 2019111805;    // Plugin requires Moodle 3.8.5+ (20200918)
$plugin->component = 'local_swtc';
$plugin->release = 'Version 1.0-Release 01';
$plugin->maturity = MATURITY_BETA;
$plugin->dependencies = array(
    'theme_adaptable' => ANY_VERSION    // Plugin 'may' modify some Adaptable theme settings and functions
);
