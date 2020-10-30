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
 * @package   format_swtccustom
 * @copyright 2016 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/23/20 - Initial writing.
 *
 */

// SWTC customized, or added, strings...
// Name of plugin...
$string['pluginname'] = 'SWTC LMS custom format';

// Text of new field in Course format section...
$string['coursetype'] = 'Portfolio for course';
$string['coursetype_help'] = 'Select the portfolio the course should be placed in (either Service Provider, GTP, Lenovo, Lenovo Internal, Maintech, Lenovo Shared Resources (Master), IBM, ASP, Premier Support, ServiceDelivery, or PracticalActivities).';

// Text of iscurriculum field in Course format section.
$string['iscurriculum'] = 'Is this a curriculum course?';
$string['iscurriculum_help'] = 'Enable if this course is a curriculum course.';

// Text of curriculumcourses field in Course format section.
$string['curriculums'] = 'Select the curriculum(s) that this course is part of.';
$string['curriculums_help'] = 'Select the curriculum(s) (ex: PSC0012) that this course is part of. To select multiple curriculums, CTRL+left click. If this course is NOT part of a curriculum, all values will be ignored.';
$string['curriculums_none'] = 'None';

// Add related courses listbox.
$string['relatedcourses'] = 'Related courses.';
$string['relatedcourses_help'] = 'Select the courses(s) that this course is related to.';
$string['relatedcourses_section'] = '<p>The following courses(s) are related to <strong>%1s</strong>:</p>';

// Text of ispartofcurriculum field in Course format section.
$string['ispartofcurriculum'] = 'Is this course part of a curriculum?';
$string['ispartofcurriculum_help'] = 'Enable if this course is part of a curriculum. Then select the curriculum(s) this course is a part of.';

$string['sectionname'] = 'Course materials';
// $string['section0name'] = 'General';
$string['section0name'] = 'Course overview';
$string['page-course-view-topics'] = 'Any course main page in topics format';
$string['page-course-view-topics-x'] = 'Any course page in topics format';
$string['hidefromothers'] = 'Hide topic';
$string['showfromothers'] = 'Show topic';

// For adding course version, machinetypes and standard formatting to "Course overview" section.
$string['courseversion'] = 'Course version';
$string['courseversion_help'] = 'Enter the current version of the course.';

$string['swtc_header'] = 'SWTC';
$string['edit_section_warning'] = 'Do not use this for adding or modifying text in the Course overview section (use Course administration > Edit settings > Course summary).';

$string['privacy:metadata'] = 'The Lenovo ebglmscustom course format plugin does not store any personal data.';
