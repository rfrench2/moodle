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
 * 05/24/21 - Initial writing; based off of Adaptable block_course_slider; added default values for some settings.
 *
 */

defined('MOODLE_INTERNAL') || die();

$defaultfilerelativepaths = array(
    'slickcss' => '/blocks/swtc_relatedcourses_slider/jquery/slick/slick/slick.css',
    'slickthemecss' => '/blocks/swtc_relatedcourses_slider/jquery/slick/slick/slick-theme.css',
    'fontawesomecss' => '/blocks/swtc_relatedcourses_slider/style/fontawesome-iconpicker.min.css',
    'relatedcourseslidercss' => '/blocks/swtc_relatedcourses_slider/style/block_swtc_relatedcourses_slider.css',
    'slickjs' => '/blocks/swtc_relatedcourses_slider/jquery/slick/slick/slick.js',
    'fontawesomejs' => '/blocks/swtc_relatedcourses_slider/jquery/fontawesome-iconpicker.min.js',
    'relatedcoursesliderjs' => '/blocks/swtc_relatedcourses_slider/jquery/block_swtc_relatedcourses_slider.js',
    // 'picture' => '/blocks/swtc_relatedcourses_slider/pix/defaultpicture.png',
    'allpicture' => '/blocks/swtc_relatedcourses_slider/pix/course-default-all-picture.png',
    'papicture' => '/blocks/swtc_relatedcourses_slider/pix/course-default-pa-picture.png',
);

$defaultinstancesettings = array(
    // 'title' => 'Related Course Slider Title',
     'title' => '@@@Dont use; title will be set dynamically@@@',
    // 'courses' => '',
    'cachetime' => 0,
    'borderwidth' => '1px',
    'borderstyle' => 'solid',
    'borderradius' => '2px',
    'navigationgalleryflag' => 'OFF',
    'navigationoptions' => 'Arrows',
    // 'numberofslides' => 4,           
    'numberofslides' => 2,              
    'centermodeflag' => 'OFF',
    // 'autoplayspeed' => 3,                
    'autoplayspeed' => 5,               
    'coursenameflag' => 'Visible',
    'coursesummaryflag' => 'Visible',
    'instancecsstextarea' => '',
    'navigationarrowprev' => 'fa-angle-double-left',
    'navigationarrownext' => 'fa-angle-double-right',
    // 'imagedivheight' => 170,         
    'imagedivheight' => 102,            
    'verticalflag' => 'OFF',            
);

$defaultblocksettings = array(
    'customjsfile' => '',
    'customcssfile' => '',
    // 'backgroundcolor' => '#009688',          
    'backgroundcolor' => '#D9D8D6',         
    // 'color' => '#FFFFFF',
    'color' => '#000000',
    // 'defaultimage' => '/blocks/swtc_relatedcourses_slider/pix/defaultpicture.png',
    'alldefaultimage' => '/blocks/swtc_relatedcourses_slider/pix/course-default-all-picture.png',
    'padefaultimage' => '/blocks/swtc_relatedcourses_slider/pix/course-default-pa-picture.png',
    'borderwidth' => '1px',
    'borderstyle' => 'solid',
    'borderradius' => '2px',
    // 'autoplayspeed' => 3,            
    'autoplayspeed' => 5,           
    'verticalflag' => 'OFF',          
);

$fontawesomeiconunicodes = array(
'fa-angle-double-left' => 'f100',
'fa-angle-double-right' => 'f101',
'fa-angle-left' => 'f104',
'fa-angle-right' => 'f105',
'fa-arrow-circle-left' => 'f0a8',
'fa-arrow-circle-right' => 'f0a9',
'fa-arrow-circle-o-left' => 'f190',
'fa-arrow-circle-o-right' => 'f18e',
'fa-arrow-left' => 'f060',
'fa-arrow-right' => 'f061',
'fa-caret-left' => 'f0d9',
'fa-caret-right' => 'f0da',
'fa-caret-square-o-left' => 'f191',
'fa-caret-square-o-right' => 'f152',
'fa-chevron-circle-left' => 'f137',
'fa-chevron-circle-right' => 'f138',
'fa-chevron-left' => 'f053',
'fa-chevron-right' => 'f054',
'fa-long-arrow-left' => 'f177',
'fa-long-arrow-right' => 'f178',
'fa-backward' => 'f04a',
'fa-forward' => 'f04e',
);

$fontawesomematchprev = array(
'fa-angle-double-right' => 'fa-angle-double-left',
'fa-angle-right' => 'fa-angle-left',
'fa-arrow-circle-right' => 'fa-arrow-circle-left',
'fa-arrow-circle-o-right' => 'fa-arrow-circle-o-left',
'fa-arrow-right' => 'fa-arrow-left',
'fa-caret-right' => 'fa-caret-left',
'fa-caret-square-o-right' => 'fa-caret-square-o-left',
'fa-chevron-circle-right' => 'fa-chevron-circle-left',
'fa-chevron-right' => 'fa-chevron-left',
'fa-long-arrow-right' => 'fa-long-arrow-left',
'fa-forward' => 'fa-backward',
);

$hiddenvisible = array(
    'Visible' => 'Visible',
    'Hidden' => 'Hidden'
);

$onoff = array(
    'ON' => 'ON',
    'OFF' => 'OFF'
);

$navigationoptions = array(
    'No navigation' => 'No navigation',
    'Arrows' => 'Arrows',
    'Radio buttons' => 'Radio buttons',
    'Arrows and Radio buttons' => 'Arrows and Radio buttons',
);

$from0to12px = array();
for ($i = 0; $i < 13; $i++) {
    $from0to12px[$i.'px'] = $i.'px';
}

$from0to12 = array();
for ($i = 0; $i < 13; $i++) {
    $from0to12[$i] = $i;
}

$from0to60by5 = array();
for ($i = 0; $i < 61; $i += 5) {
    $from0to60by5[$i] = $i;
}

$borderstyles = array(
    'none' => 'none',
    'solid' => 'solid',
    'dashed' => 'dashed',
    'dotted' => 'dotted',
    'double' => 'double'
);
