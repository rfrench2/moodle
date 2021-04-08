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
 * SWTC LMS for Moodle 3.7+. All SWTC customized functions associcated with the Adaptable theme.
 *      Remember to add the following at the top of any module that requires these functions:
 *
 *      use \local_swtc\traits\swtc_adaptable;
 *
 * And put the following within the class that is being overridden:
 *      use swtc_adaptable;
 *
 * Version details
 *
 * @package    local
 * @subpackage SWTC_adaptable.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/09/20 - Initial writing.
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtcuserlib.php');
// SWTC ********************************************************************************.
// Include SWTC LMS functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

use core_text;
use html_writer;
use stdClass;
use moodle_url;
use coursecat_helper;
use lang_string;


trait swtc_adaptable {

    /**
     * SWTC frontpage_available_courses
     *
     * @package    local
     * @subpackage swtc
     * @copyright  2021 SWTC
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     *
     * History:
     *
     * 11/09/20 - Initial writing.
     *
     */
    public function frontpage_available_courses() {
        global $CFG, $USER, $SESSION;

        // SWTC ********************************************************************************.
        // SWTC LMS swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_set_debug();

        // Other SWTC variables.
        $selfsupportstud = get_string("access_selfsupport_stud" , "local_swtc");
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            $debug->logmessage("In lenovo_adaptable ===frontpage_available_courses.enter===.", 'logfile');
        }

        // SWTC ********************************************************************************.
        // Added call to core_course_category::make_categories_list with the user's main capability for easier checking
        // of access (before moving to core_course_category::can_view_category).
        // @01 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
        // (changed core_course_category to \core_course_category).
        // SWTC ********************************************************************************.
        $categories = \core_course_category::make_categories_list($capability);

        // SWTC ********************************************************************************.
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        $chelper = new coursecat_helper();
        $chelper->set_show_courses($this::COURSECAT_SHOW_COURSES_EXPANDED)
            ->set_courses_display_options(array(
                    'sort' => array('id' => -1, 'shortname' => 1),
                    'recursive' => true,
                    'limit' => $CFG->frontpagecourselimit,
                    'viewmoreurl' => new moodle_url('/course/index.php'),
                    'viewmoretext' => new lang_string('fulllistofcourses')));

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
        // SWTC ********************************************************************************.
        // Starting with Adaptable 1.6.0, the override of the course_renderer class was moved from theme/adaptable/renderers.php to
        // theme/adaptable/classes/output/core/course_renderer.php. As such, we need to put prepend pathing information (i.e. '\')
        // to locate and use coursecat functions.
        // SWTC ********************************************************************************.
        $totalcount = \core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());

        // SWTC ********************************************************************************.
        // For each course found, get the top-level category and see if user has access. Code similar to
        // course/renderer.php in function coursecat_category. If user does not have access, remove the course
        // from the courses list.
        // SWTC ********************************************************************************.
        $coursesremoved = 0;
        // SWTC ********************************************************************************.
        // Main loop. Find top-level catid of category. If user has access, leave the course in the list.
        // If the user doesn't, remove it from the list.
        // SWTC ********************************************************************************.
        // Category id for the category name in question is in $coursecat->id.
        // In theory, if the category id appears in $swtcuser->categoryids, that alone should be enough to list the category.
        // SWTC ********************************************************************************.
        foreach ($courses as $key => $course) {
            if ((in_array($course->category, array_keys($categories)))
                || (stripos($swtcuser->user_access_type, $selfsupportstud) !== false)) {
                if (isset($debug)) {
                    $messages[] = print_r("index is :$key. Will list the course===8.4===.", true);
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
                }
            } else {
                if (isset($debug)) {
                    $messages[] = "index not found. Will NOT list the course. Removing course===8.4===.";
                    $debug->logmessage($messages, 'both');
                    unset($messages);
                }
                $coursesremoved++;
                unset($courses[$key]);
            }
        }
        $totalcount = $totalcount - $coursesremoved;

        // SWTC ********************************************************************************.
        // Modification for SWTC LMS site.
        // Everyone that self-enrolls in a course that requires a simulator will also automatically
        // be enrolled in the SWTC Internal Portfolio course 'Shared Resources (Master)'. However,
        // this internal course should never be exposed / shown directly to anyone other than users with the
        // SWTC-admin role. Several places throughout the Moodle UI list the student's enrolled courses:
        // the front page, the Navigation > My courses node, and the Dashboard (Course overview block).
        // This code hides the 'Shared Resources (Master)' course on the student's front page. Since the
        // Adaptable them overrides the core_course_renderer function (theme_adaptable_core_course_renderer),
        // we must modify the Adaptable renderers.php file to include the base function 'frontpage_available_courses'.
        // The added frontpage_available_courses function is only a stub that calls this function -
        // local_swtc_frontpage_available_courses.
        //
        // Specific modification (removing 'Shared Resources (Master)' from the returned course list)
        // Search $courses for 'Shared Resources (Master)'. If found, remove it.
        // If removed, subtract 1 from $totalcount.
        // Note: The coursecat::get(0)->get_courses function returns a 'course_in_list' array. The format
        // of course_in_list is:
        //
        // [18] => course_in_list Object
        // (
        // [record:protected] => stdClass Object
        // (
        // [id] => 18
        // [category] => 21
        // [sortorder] => 370001
        // [shortname] => serviceprovidertest1
        // [fullname] => Service-Provider-test-course-1
        // [idnumber] =>
        // [startdate] => 1449205200
        // [visible] => 0
        // [cacherev] => 1460571745
        // [summary] =>
        // [summaryformat] => 1
        // [managers] => Array
        // (
        // )
        //
        // )
        //
        // [coursecontacts:protected] =>
        // [canaccess:protected] =>
        // )
        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "About to print courses ===33.1===.";
            $messages[] = print_r($courses, true);
            $messages[] = "Finished printing courses ===33.1===.";
            $debug->logmessage($messages, 'logfile');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
        // 11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
        // SWTC ********************************************************************************.
        $courses = swtc_find_and_remove_excludecourses($courses);
        $totalcount = count($courses);          // 10/21/19 - Set new totalcount.

        // SWTC ********************************************************************************.
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // Starting with Adaptable 1.6.0, the override of the course_renderer class was moved from theme/adaptable/renderers.php to
        // theme/adaptable/classes/output/core/course_renderer.php. As such, we need to put prepend pathing information (i.e. '\')
        // to locate and use coursecat functions.
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        if (!$totalcount && !$this->page->user_is_editing()
            && has_capability('moodle/course:create', \context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }

        if (isset($debug)) {
            $debug->logmessage("Leaving lenovo_adaptable ===frontpage_available_courses.exit.", 'logfile');
        }

        // SWTC ********************************************************************************.
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // SWTC ********************************************************************************.
        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }

    /**
     * Adds "My Curriculums" to My Courses navigation menu for specific user acess types.
     *
     * Called from: navigation_menu_content
     *  Location: /theme/adaptable/renderers.php
     *
     * @param array $branch
     *
     * @return N/A
     *
     * History:
     *
     * 11/09/20 - Initial writing.
     *
     */
    public function add_menu_content($branch) {
        global $USER;

        // SWTC ********************************************************************************.
        // SWTC LMS swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_set_debug();

        // Other SWTC variables.
        $mycurriculums = get_string('mycurriculums', 'local_swtc');       // The title for 'My Curriculums'.
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering renderers.php. ===theme_adaptable.enter.";
            $messages[] = "About to print swtcuser.";
            $messages[] = print_r($swtcuser, true);
            $messages[] = "Finished printing swtcuser.";
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Adding two menu items under "My Courses": "My Courses" and "My Curriculums".
        // SWTC ********************************************************************************.
        if ((has_capability('local/swtc:swtc_view_curriculums', context_system::instance()))
            || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {
            // Use the "mysites" string in theme_adaptable since it is in the correct case ("My Courses").
            $branch->add(get_string('mysites', 'theme_adaptable'), new moodle_url('/my/index.php'), '', null, 'mycourses');
            $branch->add($mycurriculums, new moodle_url('/local/swtc/lib/curriculums.php'), '', null, 'mycurriculums');
        }

        return $branch;

    }

    /**
     * Returns menu object containing main navigation.
     *
     * @return menu boject
     *
     * History:
     *
     * 11/09/20 - Initial writing.
     *
     **/
    public function navigation_menu_content() {
        global $PAGE, $COURSE, $OUTPUT, $USER;

        // SWTC ********************************************************************************.
        // SWTC LMS user and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_set_debug();

        // Other SWTC variables.
        // The title for 'My Curriculums'.
        $mycurriculums = get_string('mycurriculums', 'local_swtc');
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "SWTC ********************************************************************************.";
            $messages[] = "Entering renderers.php. ===theme_adaptable.enter.";
            $messages[] = "About to print swtcuser.";
            $messages[] = print_r($swtcuser, true);
            $messages[] = "Finished printing swtcuser.";
            $messages[] = "SWTC ********************************************************************************.";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        $menu = new custom_menu();

        $access = true;
        $overridelist = false;
        $overridestrings = false;
        $overridetype = 'off';

        if (!empty($PAGE->theme->settings->navbardisplayicons)) {
            $navbardisplayicons = true;
        } else {
            $navbardisplayicons = false;
        }

        $usernavbar = 'excludehidden';
        if (!empty($PAGE->theme->settings->enablemysites)) {
            $mysitesvisibility = $PAGE->theme->settings->enablemysites;
        }

        $mysitesmaxlength = '30';
        if (!empty($PAGE->theme->settings->mysitesmaxlength)) {
            $mysitesmaxlength = $PAGE->theme->settings->mysitesmaxlength;
        }

        $mysitesmaxlengthhidden = $mysitesmaxlength - 3;

        if (isloggedin() && !isguestuser()) {
            if (!empty($PAGE->theme->settings->enablehome)) {
                $branchtitle = get_string('home', 'theme_adaptable');
                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-home fa-lg"></i>';
                }
                $branchlabel .= ' ' . $branchtitle;

                if (!empty($PAGE->theme->settings->enablehomeredirect)) {
                    $branchurl   = new moodle_url('/?redirect=0');
                } else {
                    $branchurl   = new moodle_url('/');
                }
                $branchsort  = 9998;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            if (!empty($PAGE->theme->settings->enablemyhome)) {
                $branchtitle = get_string('myhome');

                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-dashboard fa-lg"></i> ';
                }
                $branchlabel .= ' ' . $branchtitle;
                $branchurl   = new moodle_url('/my/index.php');
                $branchsort  = 9999;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            if (!empty($PAGE->theme->settings->enableevents)) {
                $branchtitle = get_string('events', 'theme_adaptable');
                $branchlabel = '';
                if ($navbardisplayicons) {
                    $branchlabel .= '<i class="fa fa-calendar fa-lg"></i>';
                }
                $branchlabel .= ' ' . $branchtitle;

                $branchurl   = new moodle_url('/calendar/view.php');
                $branchsort  = 10000;
                $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
            }

            if (!empty($PAGE->theme->settings->mysitessortoverride) && $PAGE->theme->settings->mysitessortoverride != 'off'
                    && !empty($PAGE->theme->settings->mysitessortoverridefield)) {

                $overridetype = $PAGE->theme->settings->mysitessortoverride;
                $overridelist = $PAGE->theme->settings->mysitessortoverridefield;

                if ($overridetype == 'profilefields' || $overridetype == 'profilefieldscohort') {
                    $overridelist = $this->get_profile_field_contents($overridelist);

                    if ($overridetype == 'profilefieldscohort') {
                        $overridelist = array_merge($this->get_cohort_enrollments(), $overridelist);
                    }
                }

                if ($PAGE->theme->settings->mysitessortoverride == 'strings') {
                    $overridelist = explode(',', $overridelist);
                }
            }

            if ($mysitesvisibility != 'disabled') {
                $showmysites = true;

                // Check custom profile field to restrict display of menu.
                if (!empty($PAGE->theme->settings->enablemysitesrestriction)) {
                    $fields = explode('=', $PAGE->theme->settings->enablemysitesrestriction);
                    $ftype = $fields[0];
                    $setvalue = $fields[1];

                    if (!$this->check_menu_access($ftype, $setvalue, 'mysitesrestriction')) {
                        $showmysites = false;
                    }

                }

                if ($showmysites) {
                    $branchtitle = get_string('mysites', 'theme_adaptable');
                    $branchlabel = '';

                    if ($navbardisplayicons) {
                        $branchlabel .= '<i class="fa fa-briefcase fa-lg"></i>';
                    }

                    $branchlabel .= ' ' . $branchtitle;

                    // SWTC ********************************************************************************.
                    // Fixed the link for "My Courses".
                    // $branchurl   = new moodle_url('#');
                    // Lenovo ********************************************************************************.
                    $branchurl   = new moodle_url('/my/index.php');
                    $branchsort  = 10001;

                    $menudisplayoption = '';

                    // Check menu hover settings.
                    if (isset($PAGE->theme->settings->mysitesmenudisplay)) {
                        $menudisplayoption = $PAGE->theme->settings->mysitesmenudisplay;
                    } else {
                        $menudisplayoption = 'shortcodehover';
                    }

                    // The two variables below will control the 4 options available from the settings above for mysitesmenuhover.
                    $showshortcode = true;  // If false, then display full course name.
                    $showhover = true;

                    switch ($menudisplayoption) {
                        case 'shortcodenohover':
                            $showhover = false;
                            break;
                        case 'fullnamenohover':
                            $showshortcode = false;
                            $showhover = false;
                        case 'fullnamehover':
                            $showshortcode = false;
                            break;
                    }

                    // Calls a local method (render_mycourses) to get list of a user's current courses that they are enrolled on.
                    list($sortedcourses) = $this->render_mycourses();

                    // After finding out if there will be at least one course to display, check
                    // for the option of displaying a sub-menu arrow symbol.
                    if (!empty($PAGE->theme->settings->navbardisplaysubmenuarrow)) {
                        $branchlabel .= ' &nbsp;<i class="fa fa-caret-down"></i>';
                    }

                    // Add top level menu option here after finding out if there will be at least one course to display.  This is
                    // for the option of displaying a sub-menu arrow symbol above, if configured in the theme settings.
                    $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
                    $icon = '';

                    if ($sortedcourses) {
                        foreach ($sortedcourses as $course) {
                            $coursename = '';
                            $rawcoursename = ''; // Untrimmed course name.

                            if ($showshortcode) {
                                $coursename = mb_strimwidth(format_string($course->shortname), 0,
                                        $mysitesmaxlength, '...', 'utf-8');
                                $rawcoursename = $course->shortname;
                            } else {
                                $coursename = mb_strimwidth(format_string($course->fullname), 0, $mysitesmaxlength, '...', 'utf-8');
                                $rawcoursename = $course->fullname;
                            }

                            if ($showhover) {
                                $alttext = $course->fullname;
                            } else {
                                $alttext = '';
                            }

                            if ($course->visible) {
                                if (!$overridelist) { // Feature not in use, add to menu as normal.
                                    $branch->add($coursename,
                                            new moodle_url('/course/view.php?id='.$course->id), $alttext);
                                } else {
                                    // We want to check against array from profile field.
                                    if ((($overridetype == 'profilefields' ||
                                        $overridetype == 'profilefieldscohort') &&
                                                        in_array($course->shortname, $overridelist)) ||
                                                        ($overridetype == 'strings' &&
                                                        $this->check_if_in_array_string($overridelist, $course->shortname))) {
                                        $icon = '';

                                        $branch->add($icon . $coursename,
                                                    new moodle_url('/course/view.php?id='.$course->id), $alttext, 100);
                                    } else {
                                        // If not in array add to sub menu item.
                                        if (!isset($parent)) {
                                            $icon = '<i class="fa fa-history"></i> ';
                                            $parent = $branch->add($icon . $trunc = rtrim(
                                                        mb_strimwidth(format_string(get_string('pastcourses', 'theme_adaptable')),
                                                        0, $mysitesmaxlengthhidden)) . '...', $this->page->url, $alttext, 1000);
                                        }

                                        $parent->add($trunc = rtrim(mb_strimwidth(format_string($rawcoursename),
                                                            0, $mysitesmaxlengthhidden)) . '...',
                                                            new moodle_url('/course/view.php?id='.$course->id),
                                                            format_string($rawcoursename));
                                    }
                                }
                            }
                        }

                        $icon = '<i class="fa fa-eye-slash"></i> ';
                        $parent = null;
                        foreach ($sortedcourses as $course) {
                            if (!$course->visible && $mysitesvisibility == 'includehidden') {
                                if (empty($parent)) {
                                    $parent = $branch->add($icon .
                                        $trunc = rtrim(mb_strimwidth(format_string(get_string('hiddencourses', 'theme_adaptable')),
                                        0, $mysitesmaxlengthhidden)) . '...', $this->page->url, '', 2000);
                                }

                                $parent->add($icon . $trunc = rtrim(mb_strimwidth(format_string($course->fullname),
                                        0, $mysitesmaxlengthhidden)) . '...',
                                        new moodle_url('/course/view.php?id='.$course->id), format_string($course->shortname));
                            }
                        }

                        // SWTC ********************************************************************************.
                        // Adding two menu items under "My Courses": "My Courses" and "My Curriculums".
                        // SWTC ********************************************************************************.
                        if ((has_capability('local/swtc:swtc_view_curriculums', context_system::instance()))
                            || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {
                            // Use the "mysites" string in theme_adaptable since it is in the
                            // correct case ("My Courses").
                            $branch->add(get_string('mysites', 'theme_adaptable'), new moodle_url('/my/index.php'),
                                '', null, 'mycourses');
                            $branch->add($mycurriculums, new moodle_url('/local/swtc/lib/curriculums.php'),
                                '', null, 'mycurriculums');
                        }
                    } else {
                        $noenrolments = get_string('noenrolments', 'theme_adaptable');
                        $branch->add('<em>'.$noenrolments.'</em>', new moodle_url('/'), $noenrolments);
                    }
                }
            }

            if (!empty($PAGE->theme->settings->enablethiscourse)) {
                if (ISSET($COURSE->id) && $COURSE->id > 1) {
                    $branchtitle = get_string('thiscourse', 'theme_adaptable');
                    $branchlabel = '';
                    if ($navbardisplayicons) {
                        $branchlabel .= '<i class="fa fa-sitemap fa-lg"></i><span class="menutitle">';
                    }

                    $branchlabel .= $branchtitle . '</span>';

                    $data = theme_adaptable_get_course_activities();

                    // Check the option of displaying a sub-menu arrow symbol.
                    if (!empty($PAGE->theme->settings->navbardisplaysubmenuarrow)) {
                        $branchlabel .= ' &nbsp;<i class="fa fa-caret-down"></i>';
                    }

                    $branchurl = $this->page->url;
                    $branch = $menu->add($branchlabel, $branchurl, '', 10002);

                    // Course sections.
                    if ($PAGE->theme->settings->enablecoursesections) {
                        $this->create_course_sections_menu($branch);
                    }

                    // Display Participants.
                    if ($PAGE->theme->settings->displayparticipants) {
                        $branchtitle = get_string('people', 'theme_adaptable');
                        $branchlabel = '<i class="icon fa fa-users fa-lg"></i>'.$branchtitle;
                        $branchurl = new moodle_url('/user/index.php', array('id' => $PAGE->course->id));
                        $branch->add($branchlabel, $branchurl, '', 100004);
                    }

                    // Display Grades.
                    if ($PAGE->theme->settings->displaygrades) {
                        $branchtitle = get_string('grades');
                        $branchlabel = $OUTPUT->pix_icon('i/grades', '', '', array('class' => 'icon')).$branchtitle;
                        $branchurl = new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id));
                        $branch->add($branchlabel, $branchurl, '', 100005);
                    }

                    // Display Competencies.
                    if (get_config('core_competency', 'enabled')) {
                        if ($PAGE->theme->settings->enablecompetencieslink) {
                            $branchtitle = get_string('competencies', 'competency');
                            $branchlabel = $OUTPUT->pix_icon('i/competencies', '', '', array('class' => 'icon')).$branchtitle;
                            $branchurl = new moodle_url('/admin/tool/lp/coursecompetencies.php',
                                         array('courseid' => $PAGE->course->id));
                            $branch->add($branchlabel, $branchurl, '', 100006);
                        }
                    }

                    // Display activities.
                    foreach ($data as $modname => $modfullname) {
                        if ($modname === 'resources') {
                            $icon = $OUTPUT->pix_icon('icon', '', 'mod_page', array('class' => 'icon'));
                            $branch->add($icon.$modfullname, new moodle_url('/course/resources.php',
                                    array('id' => $PAGE->course->id)));
                        } else {
                            $icon = $OUTPUT->pix_icon('icon', '', $modname, array('class' => 'icon'));
                            $branch->add($icon.$modfullname, new moodle_url('/mod/'.$modname.'/index.php',
                                    array('id' => $PAGE->course->id)));
                        }
                    }
                }
            }
        }

        if ($navbardisplayicons) {
            $helpicon = '<i class="fa fa-life-ring fa-lg"></i>';
        } else {
            $helpicon = '';
        }

        if (!empty($PAGE->theme->settings->helplinkscount)) {
            for ($helpcount = 1; $helpcount <= $PAGE->theme->settings->helplinkscount; $helpcount++) {
                $enablehelpsetting = 'enablehelp'.$helpcount;
                if (!empty($PAGE->theme->settings->$enablehelpsetting)) {
                    $access = true;
                    $helpprofilefieldsetting = 'helpprofilefield'.$helpcount;
                    if (!empty($PAGE->theme->settings->$helpprofilefieldsetting)) {
                        $fields = explode('=', $PAGE->theme->settings->$helpprofilefieldsetting);
                        $ftype = $fields[0];
                        $setvalue = $fields[1];
                        if (!$this->check_menu_access($ftype, $setvalue, 'help'.$helpcount)) {
                            $access = false;
                        }
                    }

                    if ($access && !$this->hideinforum()) {
                        $helplinktitlesetting = 'helplinktitle'.$helpcount;
                        if (empty($PAGE->theme->settings->$helplinktitlesetting)) {
                            $branchtitle = get_string('helptitle', 'theme_adaptable', array('number' => $helpcount));
                        } else {
                            $branchtitle = $PAGE->theme->settings->$helplinktitlesetting;
                        }
                        $branchlabel = $helpicon.$branchtitle;
                        $branchurl = new moodle_url($PAGE->theme->settings->$enablehelpsetting,
                            array('helptarget' => $PAGE->theme->settings->helptarget));

                        $branchsort  = 10003;
                        $branch = $menu->add($branchlabel, $branchurl, '', $branchsort);
                    }
                }
            }
        }

        return $menu;

    }

}
