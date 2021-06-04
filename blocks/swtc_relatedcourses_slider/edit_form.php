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

require_once($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/lib.php');
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Related course slider edit form implementation class.
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
class block_swtc_relatedcourses_slider_edit_form extends block_edit_form {

    /**
     * Override specific definition to provide course slider instance settings.
     *
     * @param stdClass $mform
     *
     * History:
     *
     * 05/24/21 - Initial writing.
     *
     */
    protected function specific_definition($mform) {
        global $CFG, $DB;
        require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

        // Section header title according to language file.
        $mform->addElement('header', 'general', get_string('generalconfiguration', 'block_swtc_relatedcourses_slider'));

        // Title.
        $mform->addElement('text', 'config_title', get_string('title', 'block_swtc_relatedcourses_slider'));
        $mform->setDefault('config_title', $defaultinstancesettings['title']);
        $mform->setType('config_title', PARAM_TEXT);

        // Cache time.
        $mform->addElement('duration', 'config_cachetime', get_string('cachetime', 'block_swtc_relatedcourses_slider'));
        $mform->setDefault('config_cachetime', $defaultinstancesettings['cachetime']);
        $mform->setType('config_cachetime', PARAM_INT);

        // Courses.
        // $mform->addElement('text', 'config_courses', get_string('courses', 'block_swtc_relatedcourses_slider'));
        // $mform->setDefault('config_courses', $defaultinstancesettings['courses']);
        // $mform->setType('config_courses', PARAM_TEXT);
        // $mform->addHelpButton('config_courses', 'courses', 'block_swtc_relatedcourses_slider');

        // Style Configuration.
        $mform->addElement('header', 'optionssection', get_string('styleconfiguration', 'block_swtc_relatedcourses_slider'));

        // Border width.
        $mform->addElement('select', 'config_borderwidth', get_string('borderwidth', 'block_swtc_relatedcourses_slider'), $from0to12px);
        $mform->setDefault('config_borderwidth', $defaultinstancesettings['borderwidth']);
        $mform->setType('config_borderwidth', PARAM_TEXT);

        // Border style.
        $mform->addElement('select', 'config_borderstyle', get_string('borderstyle', 'block_swtc_relatedcourses_slider'), $borderstyles);
        $mform->setDefault('config_borderstyle', $defaultinstancesettings['borderstyle']);
        $mform->setType('config_borderstyle', PARAM_TEXT);

        // Border radius.
        $mform->addElement('select', 'config_borderradius', get_string('borderradius', 'block_swtc_relatedcourses_slider'), $from0to12px);
        $mform->setDefault('config_borderradius', $defaultinstancesettings['borderradius']);
        $mform->setType('config_borderradius', PARAM_TEXT);

        // Image height.
        $mform->addElement('text', 'config_imagedivheight', get_string('imagedivheight', 'block_swtc_relatedcourses_slider'));
        $mform->setDefault('config_imagedivheight', $defaultinstancesettings['imagedivheight']);
        $mform->setType('config_imagedivheight', PARAM_TEXT);

        // Navigation Configuration.
        $mform->addElement('header', 'optionssection', get_string('navigationconfiguration', 'block_swtc_relatedcourses_slider'));
        
        // Navigation Vertical Slide Mode.       // 09/17/19
        $mform->addElement('select', 'config_verticalflag', get_string('verticalflag', 'block_swtc_relatedcourses_slider'), $onoff);
        $mform->setDefault('config_verticalflag', $defaultinstancesettings['verticalflag']);
        $mform->setType('config_verticalflag', PARAM_TEXT);

        // Navigation Gallery.
        $mform->addElement('select', 'config_navigationgalleryflag',
                get_string('navigationgalleryflag', 'block_swtc_relatedcourses_slider'), $onoff);
        $mform->setDefault('config_navigationgalleryflag', $defaultinstancesettings['navigationgalleryflag']);
        $mform->setType('config_navigationgalleryflag', PARAM_TEXT);

        // Navigation Options.
        $mform->addElement('select', 'config_navigationoptions',
                get_string('navigationoptions', 'block_swtc_relatedcourses_slider'), $navigationoptions);
        $mform->setDefault('config_navigationoptions', $defaultinstancesettings['navigationoptions']);
        $mform->setType('config_navigationoptions', PARAM_TEXT);

        // Navigation Arrow Icons.
        $mform->addElement('text', 'config_navigationarrownext', get_string('navigationarrownext', 'block_swtc_relatedcourses_slider'));
        $formhtmlnext = '<link href="'.$CFG->wwwroot.'/blocks/swtc_relatedcourses_slider/style/fontawesome-iconpicker.min.css"' .
                'rel="stylesheet" type="text/css">';
        $formhtmlnext .= '<script type="text/javascript" src="' .
                $CFG->wwwroot.'/blocks/swtc_relatedcourses_slider/jquery/fontawesome-iconpicker.min.js"></script>';
        $formhtmlnext .= '<script type="text/javascript">$(function(){ $("#id_config_navigationarrownext").' .
                         'iconpicker({placement: "right", selectedCustomClass: "label label-success"}); });</script>';
        $mform->addElement('html', $formhtmlnext);
        $mform->setType('config_navigationarrownext', PARAM_TEXT);

        // Number of slides.
        $mform->addElement('select', 'config_numberofslides', get_string('numberofslides', 'block_swtc_relatedcourses_slider'), $from0to12);
        $mform->setDefault('config_numberofslides', $defaultinstancesettings['numberofslides']);
        $mform->setType('config_numberofslides', PARAM_INT);

        // Center mode.
        $mform->addElement('select', 'config_centermodeflag', get_string('centermodeflag', 'block_swtc_relatedcourses_slider'), $onoff);
        $mform->setDefault('config_centermodeflag', $defaultinstancesettings['centermodeflag']);
        $mform->setType('config_centermodeflag', PARAM_TEXT);

        // Autoplay speed.
        $mform->addElement('duration', 'config_autoplayspeed', get_string('autoplayspeed', 'block_swtc_relatedcourses_slider'));
        $mform->setDefault('config_autoplayspeed', $defaultinstancesettings['autoplayspeed']);
        $mform->setType('config_autoplayspeed', PARAM_INT);

        // Course configuration.
        $mform->addElement('header', 'optionssection', get_string('courseconfiguration', 'block_swtc_relatedcourses_slider'));

        // Course Name.
        $mform->addElement('select', 'config_coursenameflag',
                get_string('coursenameflag', 'block_swtc_relatedcourses_slider'), $hiddenvisible);
        $mform->setDefault('config_coursenameflag', $defaultinstancesettings['coursenameflag']);
        $mform->setType('config_coursenameflag', PARAM_TEXT);

        // Course Summary.
        $mform->addElement('select', 'config_coursesummaryflag', get_string('coursesummaryflag', 'block_swtc_relatedcourses_slider'),
                $hiddenvisible);
        $mform->setDefault('config_coursesummaryflag', $defaultinstancesettings['coursesummaryflag']);
        $mform->setType('config_coursesummaryflag', PARAM_TEXT);

        // Instance CSS customisation.
        $mform->addElement('header', 'optionssection', get_string('instancecsscustomisation', 'block_swtc_relatedcourses_slider'));

        // Instance CSS ID.
        $mform->addElement('static', 'config_instancecssid', get_string('instancecssid', 'block_swtc_relatedcourses_slider'));

        // Instance custom CSS.
        $mform->addElement('textarea', 'config_instancecustomcsstextarea',
                get_string('instancecustomcsstextarea', 'block_swtc_relatedcourses_slider'));
        $mform->setDefault('config_instancecustomcsstextarea', $defaultinstancesettings['instancecsstextarea']);
        $mform->setType('config_instancecustomcsstextarea', PARAM_TEXT);

    }
}
