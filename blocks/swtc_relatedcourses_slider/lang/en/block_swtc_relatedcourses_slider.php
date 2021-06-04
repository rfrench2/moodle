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
$string['pluginname'] = 'SWTC related course slider';
$string['block_swtc_relatedcourses_slider'] = 'SWTC related course slider';
$string['swtc_relatedcourses_slider:addinstance'] = 'Add a new related course slider block';
$string['swtc_relatedcourses_slider:myaddinstance'] = 'Add a new related course slider block to the My Moodle page.';

// Strings used for Related courses.
$string['title_typical_user'] = 'Courses related to {$a}:';

// General.
$string['generalconfiguration'] = 'General Configuration';

// Settings page.
$string['related_course_slider'] = 'Related Course Slider';
$string['related_course_slider_heading'] = 'Related Course Slider Settings';
$string['related_course_slider_heading_desc'] = 'Customise the appearance of the Related Course Slider Block.';

// Course slider title text.
$string['title'] = 'Title:';
$string['titledesc'] = 'The title for the Course slider block.';

$string['defaultimage'] = 'Default image';
$string['defaultimagedesc'] = 'The default image to use when one is not available for the Course.';

// Course configuration.
$string['courseconfiguration'] = 'Course Configuration';

// Course name flag.
$string['coursenameflag'] = 'Name:';
$string['coursenameflagdesc'] = 'Select Visible to make the name of the course visible or Hidden to hide it.';

// Course summary flag.
$string['coursesummaryflag'] = 'Summary:';
$string['coursesummaryflagdesc'] = 'Select Visible to make the summary of the course visible or Hidden to hide it.';

// Cache time.
$string['cachetime'] = 'Cache Time:';
$string['cachetimedesc'] = 'The number of minutes the Course slider block is cached for.';

// Courses.
$string['courses'] = 'Courses:';
$string['courses_help'] = 'The list of courses to be in course slider. Specify comma-separated course ids. For example:<br><br>1,2,3,4';
$string['coursesdesc'] = 'The list of courses to be in course slider.';

// Custom js file.
$string['customjsfile'] = 'Custom JS File';
$string['customjsfiledesc'] = 'Add the relative path of a JS file you would like to load (e.g. /blocks/swtc_relatedcourses_slider/jquery/test.js).';

// Custom css file.
$string['customcssfile'] = 'Custom CSS File';
$string['customcssfiledesc'] = 'Add the relative path of a CSS file you would like to load (e.g. /blocks/swtc_relatedcourses_slider/styles/test.css).';

// Course slider hover background color.
$string['backgroundcolor'] = 'Background colour';
$string['backgroundcolordesc'] = 'Background colour of each course in all course sliders. Use instance custom css to alter colour of individual instances.';

// Course slider hover color.
$string['color'] = 'Colour';
$string['colordesc'] = 'The colour of each course in all course sliders. Use instance custom css to alter the colour of individual instances.';

// Course slider style configuration.
$string['styleconfiguration'] = 'Style Configuration';

// Course slider border radius.
$string['borderradius'] = 'Border radius:';
$string['borderradiusdesc'] = 'The border radius of a course.';

// Course slider border style.
$string['borderstyle'] = 'Border style:';
$string['borderstyledesc'] = 'The border style of a course.';

// Course slider border width.
$string['borderwidth'] = 'Border width:';
$string['borderwidthdesc'] = 'The border width of a course.';

// Course slider image height.
$string['imagedivheight'] = 'Image height(px):';
$string['imagedivheightdesc'] = 'The height of the image div.';

// Course slider navigation configuration.
$string['navigationconfiguration'] = 'Navigation Configuration';

// Course slider vertical slide mode (True for vertical or False for horizontal).     // 09/17/19
$string['verticalflag'] = 'Vertical slide mode:';                      // 09/17/19
$string['verticalflagdesc'] = 'Select either ON (for Vertical) or OFF (for Horizontal).';   // 09/17/19

// Course slider navigation flag.
$string['navigationgalleryflag'] = 'Navigation gallery:';
$string['navigationgalleryflagdesc'] = 'Select ON to enable the navigation gallery and OFF to disable it.';

// Course slider number of slides.
$string['numberofslides'] = 'Number of slides:';
$string['numberofslidesdesc'] = 'The number of slides to appear in the course slider at any given moment.';

// Course slider center mode.
$string['centermodeflag'] = 'Center mode:';
$string['centermodeflagdesc'] = 'Select ON to enable course slider center mode and OFF to disable it.';

// Course slider navigationoptions.
$string['navigationoptions'] = 'Navigation Options:';
$string['navigationoptionsdesc'] = 'Select from a range of navigation options, such as Arrows, Radio buttons, Arrows and Radio buttons.';

// Navigation arrow icon.
$string['navigationarrownext'] = 'Navigation arrow icon:';
$string['navigationarrownextdesc'] = 'Select from a range fontawesome arrow icons.';

// Instance CSS customisation.
$string['instancecsscustomisation'] = 'Instance CSS Customisation';


// Course slider instance css ID.
$string['instancecssid'] = 'Instance CSS ID:';
$string['instancecssiddesc'] = 'Use this ID to customise the specific course slider.';

// Course slider instance custom CSS.
$string['instancecustomcsstextarea'] = 'Instance CSS:';
$string['instancecustomcsstextareadesc'] = 'Use this area to type custom css for the specific course slider. Begin styles with the instance CSS ID.';

// Autoplay Speed.
$string['autoplayspeed'] = 'Autoplay Speed:';
$string['autoplayspeeddesc'] = 'Use this field to determine the autoplay speed of the course slider.';

// @01
$string['cachedef_blockdata'] = 'SWTC related course slider caching';
