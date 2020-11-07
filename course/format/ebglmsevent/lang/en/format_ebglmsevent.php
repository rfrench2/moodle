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
 * @package   format_ebglmsevent
 * @copyright 2016 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * PTR2020Q108 - 04/27/20 - Added ebglmsevent course format.
 *
 */

// Lenovo customized, or added, strings...
// Name of plugin...
$string['pluginname'] = 'EBG LMS event format';

// Text of new field in Course format section...
$string['coursetype'] = 'Portfolio for event';
$string['coursetype_help'] = 'Select the portfolio the event should be placed in (either Service Provider, GTP, Lenovo, Lenovo Internal, Maintech, Lenovo Shared Resources (Master), IBM, ASP, Premier Support, ServiceDelivery, or PracticalActivities).';

// Add related courses listbox.
$string['relatedcourses'] = 'Related courses.';
$string['relatedcourses_help'] = 'Select the courses(s) that this course is related to.';
$string['relatedcourses_section'] = '<p>The following courses(s) are related to <strong>%1s</strong>:</p>';

// Text of ispartofcurriculum field in Course format section.
$string['ispartofcurriculum'] = 'Is this course part of a curriculum?';
$string['ispartofcurriculum_help'] = 'Enable if this course is part of a curriculum. Then select the curriculum(s) this course is a part of.';

$string['currentsection'] = 'This topic';
$string['editsection'] = 'Edit topic';
$string['editsectionname'] = 'Edit topic name';
$string['deletesection'] = 'Delete topic';
$string['newsectionname'] = 'New name for topic {$a}';
// $string['sectionname'] = 'Topic';
$string['sectionname'] = 'Event materials';
// $string['section0name'] = 'General';
$string['section0name'] = 'Event overview';
$string['page-event-view-topics'] = 'Any event main page in topics format';
$string['page-event-view-topics-x'] = 'Any event page in topics format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';

// For adding event version, machinetypes and standard formatting to "Event overview" section.
$string['eventversion'] = 'Event version';
$string['eventversion_help'] = 'Enter the current version of the event.';

$string['overview_eventtitle_line1_formatting'] = '<p><span style="color: inherit; font-family: inherit; font-size: 24.5px; font-weight: bold;">string-eventfullname</span></p><p></p>';
$string['overview_eventcode_line2_formatting'] = '<p><b>Course code: </b>string-courseshortname<br />';
$string['overview_currentversion_line3_formatting'] = '<b>Current version: </b>string-currentversion<br />';
$string['overview_machinetypes_line4_formatting'] = '<b>Machine type(s): </b>string-machinetypes<br />';
// $string['overview_duration_line5_formatting'] = '<b>Duration: </b>%1d minutes<br />';        // 01/06/20
$string['overview_duration_line5_formatting'] = '<b>Duration: </b>string-duration minutes<br />';       // 01/06/20
$string['overview_heading_line6_formatting'] = '<p><br /><b>Event overview</b></p>';
$string['overview_version'] = 'version:';

$string['lenovo_header'] = 'Lenovo';
$string['edit_section_warning'] = 'Do not use this for adding or modifying text in the Event overview section (use Course administration > Edit settings > Course summary).';

$string['privacy:metadata'] = 'The Lenovo ebglmsevent course format plugin does not store any personal data.';
