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
 * @package   format_swtcpractical
 * @copyright 2021 SWTC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/13/21 - Initial writing.
 *
 */

// SWTC customized, or added, strings...
// Name of plugin...
$string['pluginname'] = 'SWTC LMS practical activity format';

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
$string['gtp_portfolio'] = 'GTP Portfolio';
$string['ibm_portfolio'] = 'IBM Portfolio';
$string['lenovo_portfolio'] = 'Lenovo Portfolio';
$string['serviceprovider_portfolio'] = 'Service Provider Portfolio';
$string['lenovointernal_portfolio'] = 'Lenovo Internal Portfolio';
$string['lenovosharedresources_portfolio'] = 'Lenovo Shared Resources (Master)';
$string['maintech_portfolio'] = 'Maintech Portfolio';
$string['asp_portfolio'] = 'ASP Portfolio';
$string['premiersupport_portfolio'] = 'PremierSupport Portfolio';
$string['servicedelivery_portfolio'] = 'ServiceDelivery Portfolio';
$string['practicalactivities_portfolio'] = 'PracticalActivities Portfolio';
$string['sitehelp_portfolio'] = 'Site Help Portfolio';
$string['curriculums_portfolio'] = 'Curriculums Portfolio';


$string['currentsection'] = 'This topic';
$string['editsection'] = 'Edit topic';
$string['editsectionname'] = 'Edit topic name';
$string['deletesection'] = 'Delete topic';
$string['newsectionname'] = 'New name for topic {$a}';
$string['sectionname'] = 'Activity materials';
$string['section0name'] = 'Activity overview';
$string['page-course-view-topics'] = 'Any activity main page in topics format';
$string['page-course-view-topics-x'] = 'Any activity page in topics format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';

// For adding activity version, machinetypes and standard formatting to "Activity overview" section.
$string['courseversion'] = 'Activity version';
$string['courseversion_help'] = 'Enter the current version of the activity.';

$string['machinetypes'] = 'Machine type(s)';
$string['machinetypes_help'] = 'List all machine type(s) for the system(s) specifically covered in this activity (not prerequisite activities). Separate each machine type by a space. If  the system does not have a machine type, use N/A.';

$string['duration'] = 'Duration (in minutes only)';
$string['duration_help'] = 'Estimated duration to complete this course (in minutes only).';

$string['overview_coursetitle_line1_formatting'] = '<p><span style="color: inherit; font-family: inherit; font-size: 24.5px; font-weight: bold;">string-coursefullname</span></p><p></p>';
$string['overview_coursecode_line2_formatting'] = '<p><b>Activity code: </b>string-courseshortname<br />';
$string['overview_currentversion_line3_formatting'] = '<b>Current version: </b>string-currentversion<br />';
$string['overview_machinetypes_line4_formatting'] = '<b>Machine type(s): </b>string-machinetypes<br />';
$string['overview_duration_line5_formatting'] = '<b>Duration: </b>string-duration minutes<br />';
$string['overview_heading_line6_formatting'] = '<p><br /><b>Activity overview</b></p>';
$string['overview_version'] = 'version:';

$string['lenovo_header'] = 'Lenovo';
$string['edit_section_warning'] = 'Do not use this for adding or modifying text in the Activity overview section (use Course administration > Edit settings > Course summary).';

$string['privacy:metadata'] = 'The SWTC swtcpractical course format plugin does not store any personal data.';
