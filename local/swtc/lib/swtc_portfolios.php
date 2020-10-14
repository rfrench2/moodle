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
 * @subpackage swtc/lib/swtc_portfolios.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	04/14/18 - Initial writing; loads all portfolio values used by EBGLMS.
 * 07/11/18 - Index of array is equal to portfolio number.
 * 11/12/18 - Added strings for ServiceDelivery portfolio.
 *
 **/

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// 04/15/18: $SESSION is required here.
// Lenovo ********************************************************************************
global $SESSION;


/**
 * Initializes all portfolio values used by EBGLMS and loads them into $SESSION->EBGLMS->PORTFOLIOS.
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
 * 04/15/18 - Initial writing.
 *
 **/

 // Lenovo ********************************************************************************
// Setup temporary reference to $PORTFOLIOS.
//      To use: $tmp = $SESSION->EBGLMS->PORTFOLIOS
// Lenovo ********************************************************************************
$tmp = $SESSION->EBGLMS->PORTFOLIOS;

// Lenovo ********************************************************************************
// Setup the third-level $PORTFOLIOS global variables.
//      To use: $portfolios = $SESSION->EBGLMS->PORTFOLIOS
// Lenovo ********************************************************************************
/**
 * RF Lenovo EBG The user should be assigned to a role based on the user profile value...
 * Important! Following values must match the values defined in swtccustom course format lib.php...
 * 07/11/18 - Index of array is equal to portfolio number.
 */
$tmp->PORTFOLIO_GTP = get_string('gtp_portfolio', 'local_swtc');
$tmp->PORTFOLIO_IBM = get_string('ibm_portfolio', 'local_swtc');
$tmp->PORTFOLIO_LENOVO = get_string('lenovo_portfolio', 'local_swtc');
$tmp->PORTFOLIO_SERVICEPROVIDER = get_string('serviceprovider_portfolio', 'local_swtc');
$tmp->PORTFOLIO_LENOVOINTERNAL = get_string('lenovointernal_portfolio', 'local_swtc');
$tmp->PORTFOLIO_LENOVOSHAREDRESOURCES = get_string('lenovosharedresources_portfolio', 'local_swtc');
$tmp->PORTFOLIO_MAINTECH = get_string('maintech_portfolio', 'local_swtc');
$tmp->PORTFOLIO_ASP = get_string('asp_portfolio', 'local_swtc');
$tmp->PORTFOLIO_PREMIERSUPPORT = get_string('premiersupport_portfolio', 'local_swtc');
$tmp->PORTFOLIO_SERVICEDELIVERY = get_string('servicedelivery_portfolio', 'local_swtc');
$tmp->PORTFOLIO_SITEHELP = get_string('sitehelp_portfolio', 'local_swtc');
$tmp->PORTFOLIO_NONE = 'PORTFOLIO_NONE';
// $tmp->PORTFOLIO_GTP = 3;
// $tmp->PORTFOLIO_IBM = 4;
// $tmp->PORTFOLIO_LENOVO = 5;
// $tmp->PORTFOLIO_SERVICEPROVIDER = 6;
// $tmp->PORTFOLIO_LENOVOINTERNAL = 7;
// $tmp->PORTFOLIO_LENOVOSHAREDRESOURCES = 8;
// $tmp->PORTFOLIO_MAINTECH = 9;
// $tmp->PORTFOLIO_ASP = 10;
// $tmp->PORTFOLIO_PREMIERSUPPORT = 11;
// $tmp->PORTFOLIO_NONE = 66;
