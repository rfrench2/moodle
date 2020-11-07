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
 * Strings for component 'format_topics', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   format_topics
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   format_ebglmspractical
 * @copyright 2016 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/05/18 - Initial writing; based on format_ebglmscustom version 2018083107.
 * 11/12/18 - Added strings for ServiceDelivery and PracticalActivities portfolios.
 * 04/12/19 - Added string for data privacy (Moodle 3.6+).
 * 05/30/19 - Added related courses listbox to /course/edit_form.php; added related courses to course format options.
 * 08/12/19 - Added course duration to course overview (and course format options).
 * 12/06/19 - Added "Curriculums Portfolio" as a selection; changed strings from dashes "practicalactivities-portfolio" to underscores
 *                      "practicalactivities_portfolio".
 * 01/06/20 - Changed 'duration' from PARAM_INT to PARAM_TEXT.
 * PTR2020Q107 - @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
 *                      to remove duplication of common strings.
 *
 */

// Lenovo customized, or added, strings...
// Name of plugin...
$string['pluginname'] = 'EBG LMS practical activity format';

// Text of new field in Activity format section...
$string['coursetype'] = 'Portfolio for activity';
$string['coursetype_help'] = 'Select the portfolio the course should be placed in (either Service Provider, GTP, Lenovo, Lenovo Internal, Maintech, Lenovo Shared Resources (Master), IBM, ASP, PremierSupport, ServiceDelivery, or PracticalActivities).';

// Text of iscurriculum field in Activity format section.
$string['iscurriculum'] = 'Is this a curriculum course?';
$string['iscurriculum_help'] = 'Enable if this course is a curriculum course.';

// Text of curriculumcourses field in Activity format section.
$string['curriculums'] = 'Select the curriculum(s) that this activity is part of.';
$string['curriculums_help'] = 'Select the curriculum(s) (ex: PSC0012) that this activity is part of. To select multiple curriculums, CTRL+left click. If this activity is NOT part of a curriculum, all values will be ignored.';
$string['curriculums_none'] = 'None';

// Add related courses listbox.
$string['relatedcourses'] = 'Related courses';
$string['relatedcourses_help'] = 'Select the courses(s) that this course is related to.';
$string['relatedcourses_section'] = '<p>The following courses(s) are related to <strong>%1s</strong>:</p>';

// Text of ispartofcurriculum field in Activity format section.
$string['ispartofcurriculum'] = 'Is this activity part of a curriculum?';
$string['ispartofcurriculum_help'] = 'Enable if this activity is part of a curriculum. Then select the curriculum(s) this activity is a part of.';

// Options for the Type of course pull-down menu...
// @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
//                      to remove duplication of common strings.
// $string['gtp_portfolio'] = 'GTP Portfolio';
// $string['lenovoandibm_portfolio'] = 'Lenovo and IBM Portfolio';
// $string['ibm_portfolio'] = 'IBM Portfolio';
// $string['lenovo_portfolio'] = 'Lenovo Portfolio';
// $string['serviceprovider_portfolio'] = 'Service Provider Portfolio';
// $string['lenovointernal_portfolio'] = 'Lenovo Internal Portfolio';
// $string['lenovosharedresources_portfolio'] = 'Lenovo Shared Resources (Master)';
// $string['maintech_portfolio'] = 'Maintech Portfolio';
// $string['asp_portfolio'] = 'ASP Portfolio';
// $string['premiersupport_portfolio'] = 'PremierSupport Portfolio';
// $string['servicedelivery_portfolio'] = 'ServiceDelivery Portfolio';
// $string['practicalactivities_portfolio'] = 'PracticalActivities Portfolio';
// $string['sitehelp_portfolio'] = 'Site Help Portfolio';
// $string['curriculums_portfolio'] = 'Curriculums Portfolio';

// @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
//                      to remove duplication of common strings.
// $string['currentsection'] = 'This topic';
// $string['editsection'] = 'Edit topic';
// $string['editsectionname'] = 'Edit topic name';
// $string['deletesection'] = 'Delete topic';

// $string['newsectionname'] = 'New name for topic {$a}';
// $string['sectionname'] = 'Topic';
$string['sectionname'] = 'Activity materials';
// $string['section0name'] = 'General';
$string['section0name'] = 'Activity overview';
$string['page-course-view-topics'] = 'Any activity main page in topics format';
$string['page-course-view-topics-x'] = 'Any activity page in topics format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';

// For adding activity version, machinetypes and standard formatting to "Activity overview" section.
$string['courseversion'] = 'Activity version';
$string['courseversion_help'] = 'Enter the current version of the activity.';

// @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
//                      to remove duplication of common strings.
// $string['machinetypes'] = 'Machine type(s)';
// $string['machinetypes_help'] = 'List all machine type(s) for the system(s) specifically covered in this activity (not prerequisite activities). Separate each machine type by a space. If  the system does not have a machine type, use N/A.';

// @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
//                      to remove duplication of common strings.
// $string['duration'] = 'Duration (in minutes only)';
//  $string['duration_help'] = 'Estimated duration to complete this course (in minutes only).';

// @01 - 04/27/20 - Moved some DCG custom course format strings to /local/ebglms/lang/en/local_ebglms.php 
//                      to remove duplication of common strings.
// $string['overview_coursetitle_line1_formatting'] = '<p><span style="color: inherit; font-family: inherit; font-size: 24.5px; font-weight: bold;">string-coursefullname</span></p><p></p>';
// $string['overview_coursecode_line2_formatting'] = '<p><b>Activity code: </b>string-courseshortname<br />';
// $string['overview_currentversion_line3_formatting'] = '<b>Current version: </b>string-currentversion<br />';
// $string['overview_machinetypes_line4_formatting'] = '<b>Machine type(s): </b>string-machinetypes<br />';
// 08/12/19
// $string['overview_duration_line5_formatting'] = '<b>Duration: </b>%1d minutes<br />';        // 01/06/20
// $string['overview_duration_line5_formatting'] = '<b>Duration: </b>string-duration minutes<br />';       // 01/06/20
// $string['overview_heading_line6_formatting'] = '<p><br /><b>Activity overview</b></p>';
// $string['overview_version'] = 'version:';

$string['lenovo_header'] = 'Lenovo';
$string['edit_section_warning'] = 'Do not use this for adding or modifying text in the Activity overview section (use Course administration > Edit settings > Course summary).';

$string['privacy:metadata'] = 'The Lenovo ebglmspractical course format plugin does not store any personal data.';
