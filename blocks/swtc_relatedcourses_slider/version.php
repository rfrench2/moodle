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
 * @package   block_swtc_relatedcourses_slider
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/24/21 - Initial writing; based off of Adaptable block_course_slider.
 *
 */

defined('MOODLE_INTERNAL') || die();

// Recommended since 2.0.2 (MDL-26035). Required since 3.0 (MDL-48494).
$plugin->component = 'block_swtc_relatedcourses_slider';

// YYYYMMDDHH (year, month, day, 24-hr time).
$plugin->version = 2019072931;

// YYYYMMDDHH (This is the release version for Moodle 3.2).
$plugin->requires = 2016120500;

// $plugin->dependencies = array('theme_adaptable' => 2017053100);

$plugin->maturity = MATURITY_STABLE;
$plugin->release = "1.0";
