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
 * @subpackage swtc/lib/swtc_constants.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/14/20 - Initial writing.
 *
 **/

namespace local_swtc;

defined('MOODLE_INTERNAL') || die();

/**
 * Important! Define the values to use in swtccustom and swtcpractical course formats
 */
define('COURSETYPE_GTP', 3);
define('COURSETYPE_IBM', 4);
define('COURSETYPE_LENOVO', 5);
define('COURSETYPE_SERVICEPROVIDER', 6);
define('COURSETYPE_LENOVOINTERNAL', 7);
define('COURSETYPE_LENOVOSHAREDRESOURCES', 8);
define('COURSETYPE_MAINTECH', 9);
define('COURSETYPE_ASP', 10);
define('COURSETYPE_PREMIERSUPPORT', 11);
define('COURSETYPE_SERVICEDELIVERY', 12);
define('COURSETYPE_PRACTICALACTIVITIES', 13);
define('COURSETYPE_CURRICULUMS', 14);
define('COURSETYPE_SITEHELP', 15);
define('COURSETYPE_NONE', 66);

define('COURSE_ACTIVE', 1);
define('COURSE_INACTIVE', 0);

define('SWTC_SQL_MAX_RECORDS', 40000);
