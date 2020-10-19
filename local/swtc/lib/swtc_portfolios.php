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
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();

global $SESSION;


/**
 * Initializes all portfolio values used by EBGLMS and loads them into $SESSION->SWTC->PORTFOLIOS.
 *
 *      IMPORTANT! $SESSION->SWTC MUST be set before calling (i.e. no check for SWTC).
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
 * 10/14/20 - Initial writing.
 *
 **/

// SWTC ********************************************************************************
// Setup temporary reference to $PORTFOLIOS.
//      To use: $tmp = $SESSION->SWTC->PORTFOLIOS
// SWTC ********************************************************************************
$tmp = $SESSION->SWTC->PORTFOLIOS;

// Lenovo ********************************************************************************
// Setup the third-level $PORTFOLIOS global variables.
//      To use: $portfolios = $SESSION->SWTC->PORTFOLIOS
// Lenovo ********************************************************************************
$tmp->PORTFOLIO_ONE = get_string('one_portfolio', 'local_swtc');
$tmp->PORTFOLIO_TWO = get_string('two_portfolio', 'local_swtc');
