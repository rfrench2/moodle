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
 * This file defines tasks performed by the tool.
 *
 * @package    tool_analytics (original)
 * @copyright  2017 David Monllao {@link http://www.davidmonllao.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 /**
 * Version details
 *
 * @package    tasks
 * @subpackage dcgsbautouser
 * @copyright  2017 Lenovo EBG Server Education
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	12/12/17 - Initial writing; copied from admin/tool_analytics.
 * 12/24/17 - Added "preview" tasks (to show what would be done).
 * 01/08/18 - Added Legacy certification tasks.
 * 01/22/18 - Removed unnecessary tasks (legacy); renamed other tasks (remove "ts" and "leg" designation).
 * 11/26/18 - Added tasks for curriculums.
 *
 **/
// Lenovo ********************************************************************************

defined('MOODLE_INTERNAL') || die();

// List of tasks.
// Lenovo ********************************************************************************
// Per agreement, set export to 5pm and import to 12:30pm.
// Lenovo ********************************************************************************
$tasks = array(
    array(
        'classname' => 'local_swtc\task\swtc_export_to_sb',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '5',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_swtc\task\swtc_import_from_sb',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_swtc\task\swtc_pre_export_to_sb',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '5',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_swtc\task\swtc_pre_import_from_sb',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_swtc\task\swtc_verify_curriculums',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'local_swtc\task\swtc_update_curriculums',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '12',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
