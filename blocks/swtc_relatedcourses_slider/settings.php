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
 *
 * Settings for Block block_swtc_relatedcourses_slider.
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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/lib.php');
require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

if ($ADMIN->fulltree) {

    // Course slider customjsfile.
    $name = 'block_swtc_relatedcourses_slider/customjsfile';
    $title = get_string('customjsfile', 'block_swtc_relatedcourses_slider');
    $description = get_string('customjsfiledesc', 'block_swtc_relatedcourses_slider');
    $defaultvalue = $defaultblocksettings['customjsfile'];
    $settings->add(new admin_setting_configtext($name, $title, $description, $defaultvalue));

    // Course slider customcssfile.
    $name = 'block_swtc_relatedcourses_slider/customcssfile';
    $title = get_string('customcssfile', 'block_swtc_relatedcourses_slider');
    $description = get_string('customcssfiledesc', 'block_swtc_relatedcourses_slider');
    $defaultvalue = $defaultblocksettings['customcssfile'];
    $settings->add(new admin_setting_configtext($name, $title, $description, $defaultvalue));

    // Course slider background color.
    $name = 'block_swtc_relatedcourses_slider/backgroundcolor';
    $title = get_string('backgroundcolor', 'block_swtc_relatedcourses_slider');
    $description = get_string('backgroundcolordesc', 'block_swtc_relatedcourses_slider');
    $defaultvalue = $defaultblocksettings['backgroundcolor'];
    $previewconfig = null;
    $settings->add(new admin_setting_configcolourpicker($name, $title, $description, $defaultvalue, $previewconfig));

    // Course slider color.
    $name = 'block_swtc_relatedcourses_slider/color';
    $title = get_string('color', 'block_swtc_relatedcourses_slider');
    $description = get_string('colordesc', 'block_swtc_relatedcourses_slider');
    $defaultvalue = $defaultblocksettings['color'];
    $previewconfig = null;
    $settings->add(new admin_setting_configcolourpicker($name, $title, $description, $defaultvalue, $previewconfig));

    // Default course image.
    $name = 'block_swtc_relatedcourses_slider/defaultimage';
    $title = get_string('defaultimage', 'block_swtc_relatedcourses_slider');
    $description = get_string('defaultimagedesc', 'block_swtc_relatedcourses_slider');
    $settings->add(new admin_setting_configstoredfile($name, $title, $description, 'defaultimage'));
    
}
