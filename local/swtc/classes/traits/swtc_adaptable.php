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
 * Lenovo EBGLMS for Moodle 3.7+. All Lenovo customized functions associcated with the Adaptable theme.
 *      Remember to add the following at the top of any module that requires these functions:
 *
 *      use \local_swtc\traits\lenovo_adaptable;
 *
 * And put the following within the class that is being overridden:
 *      use lenovo_adaptable;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_adaptable.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	11/06/19 - Initial writing; moved majority of customized code from Adaptable to functions defined here; added utility functions;
 *                      added call to swtc_find_and_remove_excludecourses.
 * 03/02/20 - In frontpage_available_courses, added call to core_course_category::make_categories_list with the
 *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
 * PTR2020Q108 - @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
 *                      (changed core_course_category to \core_course_category).
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

use core_text;
use html_writer;
use stdClass;
use moodle_url;
use coursecat_helper;
use lang_string;


trait lenovo_adaptable {

    /**
     * Lenovo frontpage_available_courses
     *
     * @package    local
     * @subpackage swtc
     * @copyright  2018 Lenovo EBG Server Education
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     *
     * History:
     *
     *	xx/xx/15 - Initial writing.
     * 03/20/17 - Updated for Adaptable v1.6.1 (see below for details).
     * 04/19/17 - Added new $SESSION->EBGLMS global variable and all its required changes; removed call to
     *                      searchForShortName (changed to array_search).
     * 05/03/18 - Changed the "require_once($CFG->dirroot.'/local/swtc/lib/swtc_debug.php');" to be dependent on the setting of a
     *                      local variable that must be named $debug: "$debug = new stdClass();" = debugging on; null = debugging off
     *                      (since we are using isset() for the check).
     * 06/03/18 - Added check for new swtcdebug setting.
     * 08/04/18 - Added sort parameters to set_courses_display_options.
     *	10/10/19 - Moved majority of customized code from here to functions defined in /local/swtc/lib/lenovo_adaptable.php.
     * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
     * 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
     * 11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
     * 03/02/20 - In frontpage_available_courses, added call to core_course_category::make_categories_list with the
     *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
     * @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
     *                      (changed core_course_category to \core_course_category).
     *
    */
    public function frontpage_available_courses() {
        // global $CFG, $USER, $DB, $PAGE, $COURSE, $OUTPUT;
        global $CFG, $DB, $PAGE, $USER, $SESSION;
        // require_once($CFG->libdir. '/coursecatlib.php');         // Moodle 3.6

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();

        // Other Lenovo variables.
        $swtc_resources = $SESSION->EBGLMS->RESOURCES;
        $capability = $swtc_user->capabilities[0];
        $access_selfsupport_stud = $SESSION->EBGLMS->STRINGS->selfsupport->access_selfsupport_stud;
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            debug_logmessage("In lenovo_adaptable ===frontpage_available_courses.enter===.", 'logfile');
        }

        // Lenovo ********************************************************************************
        // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
        //                  of access (before moving to core_course_category::can_view_category).
        // @01 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
        //              (changed core_course_category to \core_course_category).
        // Lenovo ********************************************************************************
        $categories = \core_course_category::make_categories_list($capability);
        // print_object($categories);     // 03/01/20 - Lenovo debugging...

        // Lenovo ********************************************************************************
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // Lenovo ********************************************************************************
        // Lenovo ********************************************************************************
        // 08/04/18 - Added sort parameters to set_courses_display_options
        // Lenovo ********************************************************************************
        $chelper = new coursecat_helper();
        // $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
        $chelper->set_show_courses($this::COURSECAT_SHOW_COURSES_EXPANDED)->
                set_courses_display_options(array(
                    'sort' => array('id' => -1, 'shortname' => 1),
                    'recursive' => true,
                    'limit' => $CFG->frontpagecourselimit,
                    'viewmoreurl' => new moodle_url('/course/index.php'),
                    'viewmoretext' => new lang_string('fulllistofcourses')));

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
        // Lenovo ********************************************************************************
        // Starting with Adaptable 1.6.0, the override of the course_renderer class was moved from theme/adaptable/renderers.php to
        //      theme/adaptable/classes/output/core/course_renderer.php. As such, we need to put prepend pathing information (i.e. '\')
        //      to locate and use coursecat functions.
        // Lenovo ********************************************************************************
        // $courses = \coursecat::get(0)->get_courses($chelper->get_courses_display_options());                                     // Moodle 3.6
        // $totalcount = \coursecat::get(0)->get_courses_count($chelper->get_courses_display_options());                        // Moodle 3.6
        $courses = \core_course_category::get(0)->get_courses($chelper->get_courses_display_options());                     // Moodle 3.6
        $totalcount = \core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());        // Moodle 3.6

        // Lenovo ********************************************************************************
        // 07/13/18 - For each course found, get the top-level category and see if user has access. Code similar to
        //          /course/renderer.php in function coursecat_category. If user does not have access, remove the course
        //          from the courses list.
        // Lenovo ********************************************************************************
        $index = null;
        $courses_removed = 0;
        // Lenovo ********************************************************************************
        // Main loop. Find top-level catid of category. If user has access, leave the course in the list.
        //      If the user doesn't, remove it from the list.
        // Lenovo ********************************************************************************
        // Category id for the category name in question is in $coursecat->id.
        // In theory, if the category id appears in $swtc_user->categoryids, that alone should be enough to list the category.
        // Lenovo ********************************************************************************
        foreach ($courses as $key => $course) {
            // if (swtc_user_access_category($course->category)) {            // 03/02/20
            if ((in_array($course->category, array_keys($categories))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
                if (isset($debug)) {
                    $messages[] = print_r("index is :$key. Will list the course===8.4===.", true);
                    debug_logmessage($messages, 'detailed');
                    unset($messages);
                }
            } else {
                if (isset($debug)) {
                    $messages[] = "index not found. Will NOT list the course. Removing course===8.4===.";
                    debug_logmessage($messages, 'both');
                    unset($messages);
                }
                $courses_removed++;
                unset($courses[$key]);
            }
        }
        $totalcount = $totalcount - $courses_removed;       // Lenovo

        //
        // Modification for Lenovo EBG LMS site.
        //		Everyone that self-enrolls in a course that requires a simulator will also automatically be enrolled in the Lenovo Internal Portfolio course
        //		'Shared Resources (Master)'. However, this internal course should never be exposed / shown directly to anyone other than users with the
        //		Lenovo-admin role. Several places throughout the Moodle UI list the student's enrolled courses: the front page,
        //          the Navigation > My courses node, and the Dashboard (Course overview block).
        //		This code hides the 'Shared Resources (Master)' course on the student's front page. Since the Adaptable them overrides
        //              the core_course_renderer function (theme_adaptable_core_course_renderer), we must modify the Adaptable
        //              renderers.php file to include the base function 'frontpage_available_courses'.
        //		The added frontpage_available_courses function is only a stub that calls this function - local_swtc_frontpage_available_courses.
        //
        // Specific modification (removing 'Shared Resources (Master)' from the returned course list)
        //		Search $courses for 'Shared Resources (Master)'. If found, remove it.
        //			If removed, subtract 1 from $totalcount.
        //		Note: The coursecat::get(0)->get_courses function returns a 'course_in_list' array. The format of course_in_list is:
        //
        //		[18] => course_in_list Object
        //				(
        //					[record:protected] => stdClass Object
        //						(
        //							[id] => 18
        //							[category] => 21
        //							[sortorder] => 370001
        //							[shortname] => serviceprovidertest1
        //							[fullname] => Service-Provider-test-course-1
        //							[idnumber] =>
        //							[startdate] => 1449205200
        //							[visible] => 0
        //							[cacherev] => 1460571745
        //							[summary] =>
        //							[summaryformat] => 1
        //							[managers] => Array
        //								(
        //								)
        //
        //						)
        //
        //					[coursecontacts:protected] =>
        //					[canaccess:protected] =>
        //				)
        //
        if (isset($debug)) {
            $messages[] = "About to print courses ===33.1===.";
            $messages[] = print_r($courses, true);
            $messages[] = "Finished printing courses ===33.1===.";
            debug_logmessage($messages, 'logfile');
            unset($messages);
        }

        // Lenovo ********************************************************************************.
        // 10/21/19 - Added call to local_swtc_find_and_remove_shared_resources.
        // 11/03/19 - Changed name of local_swtc_find_and_remove_shared_resources to swtc_find_and_remove_excludecourses.
        // Lenovo ********************************************************************************.
        $courses = swtc_find_and_remove_excludecourses($courses);
        $totalcount = count($courses);          // 10/21/19 - Set new totalcount.

        // Lenovo ********************************************************************************
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // Starting with Adaptable 1.6.0, the override of the course_renderer class was moved from theme/adaptable/renderers.php to
        //      theme/adaptable/classes/output/core/course_renderer.php. As such, we need to put prepend pathing information (i.e. '\')
        //      to locate and use coursecat functions.
        // Lenovo ********************************************************************************
        // Lenovo ********************************************************************************
        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', \context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }

        if (isset($debug)) {
            debug_logmessage("Leaving lenovo_adaptable ===frontpage_available_courses.exit.", 'logfile');
        }

        // return coursecat_courses($chelper, $courses, $totalcount);  // Lenovo - coursecat_courses is a protected method.
        //$courserenderer = $PAGE->get_renderer('core', 'course');
        //return $courserenderer->courses_list($courses);
        //return $courserenderer->coursecat_courses($chelper, $courses, $totalcount);
        // Lenovo ********************************************************************************
        // The following was taken from Moodle/course/renderer.php; function 'frontpage_available_courses().
        // Lenovo ********************************************************************************
        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }

    /**
     * Lenovo override of search_courses in /course/renderer.php.
     *
     * Renders html to display search result page
     *
     * @param array $searchcriteria may contain elements: search, blocklist, modulelist, tagid
     * @return string
     *
     * @package    local
     * @subpackage swtc
     * @copyright  2019 Lenovo EBG Server Education
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     *
     * History:
     *
     *	xx/xx/15 - Initial writing.
     * 03/23/17 - Updated for Adaptable v1.6.1 (see below for details).
     * 04/19/17 - Added new $SESSION->EBGLMS global variable and all its required changes; removed call to
     *                      searchForShortName (changed to array_search).
     * 05/03/18 - Changed the "require_once($CFG->dirroot.'/local/swtc/lib/swtc_debug.php');" to be dependent on the setting of a
     *                      local variable that must be named $debug: "$debug = new stdClass();" = debugging on; null = debugging off
     *                      (since we are using isset() for the check).
     * 06/03/18 - Added check for new swtcdebug setting.
     *	10/11/19 - Moved majority of customized code to here from /theme/adaptable/classes/output/core/course_renderer.
     * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
     * 03/02/20 - In frontpage_available_courses, added call to core_course_category::make_categories_list with the
     *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
     * @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
     *                      (changed core_course_category to \core_course_category).
     *
     */
    public function search_courses($searchcriteria) {
        global $CFG, $USER, $SESSION;                         // Lenovo (added $USER and $SESSION).

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();

        // Other Lenovo variables.
        $requiredcapabilities = array();                    // List all the capabilities found for the user and send to search to limit returned courses.
        $options = array();                                     // Dummy array to send to search_courses_count to limit returned courses.
        $catlist = array();						// A list of all the top-level categories defined (returned from local_swtc_get_user_access).
        $tmp_user = new stdClass();    // Hold return values from local_swtc_get_user_access.
        $capability = $swtc_user->capabilities[0];
        $access_selfsupport_stud = $SESSION->EBGLMS->STRINGS->selfsupport->access_selfsupport_stud;
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            // 10/13/19 - The following is only a test...
            // $courses = parent::search_courses($searchcriteria);
            //print_object($courses);
            $messages[] = "Lenovo ********************************************************************************.";
			$messages[] = "Entering lenovo_adaptable===search_courses.enter===.";
            $messages[] = "The userid that will be used throughout lenovo_adaptable/search_courses is :<strong>$USER->id</strong>.";
            $messages[] = 'The user_access_type is :<strong>' . $swtc_user->user_access_type . '</strong>.';
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }

        // Lenovo ********************************************************************************
        // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
        //                  of access (before moving to core_course_category::can_view_category).
        // @01 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
        //              (changed core_course_category to \core_course_category).
        // Lenovo ********************************************************************************
        $categories = \core_course_category::make_categories_list($capability);
        // print_object($categories);     // 03/01/20 - Lenovo debugging...

        // Lenovo ********************************************************************************
        // The following is taken from /local/swtc/lib/locallib.php.
        //
        //			The returned value, $access, is a multidimensional array that has the following format (Note: roleid will be loaded later):
        //			$access = array(
        //					'portfolio'=>'',
        //					'roleshortname'=>'',
        //					'categoryid'=>'',
        //					'roleid'=>''
        //			);
        //
        //          The returned value, $allroles, is an array that has the following format:
        // 			[11] => stdClass Object
        //		    (
        //			        [id] => 11
        //			        [name] => Lenovo-instructor
        //			        [shortname] => lenovo-instructor
        //			        [description] => A Lenovo instructor.
        //			        [sortorder] => 12
        //			        [archetype] => teacher
        //			        ***[localname] => Lenovo-instructor - field not returned using get_all_roles()
        //			    );
        // $access = local_swtc_get_user_access($user_access_type, $allroles, $catlist);
        // Lenovo ********************************************************************************
        // list($access, $allroles) = local_swtc_get_user_access($user_access_type, $catlist);
        list($catlist, $tmp_user) = local_swtc_get_user_access();
        // print_r($SESSION->EBGLMS->DEBUG);
        // Lenovo ********************************************************************************
        // Use the categoryids (ex: 25 and 97) as an index into $catlist. Load the capability found. An $access example is:
        //
        //         Array
        // (
        //     [portfolio] => 6
        //     [roleshortname] => serviceprovider-student
        //     [roleid] => none
        //     [categoryids] => Array
        //         (
        //             [0] => 25
        //             [1] => 97
        //         )
        //
        // )
        //
        // An example $catlist entry is:
        //      [6] => Array
        //      (
        //          [catid] => 25
        //          [catname] => Service Provider Portfolio
        //          [context] => context_coursecat Object
        //              (
        //                  [_id:protected] => 522
        //                  [_contextlevel:protected] => 40
        //                  [_instanceid:protected] => 25
        //                  [_path:protected] => /1/522
        //                  [_depth:protected] => 2
        //              )
        //
        //          [capability] => local/swtc:ebg_access_serviceprovider_portfolio
        //          [roles] => Array
        //              (
        //                  [serviceprovider-student] => 22
        //              )
        //
        //      )
        //
        // Lenovo ********************************************************************************
        if (isset($debug)) {
            $messages[] = "tmp_user follows :===8.2===.";
            $messages[] = print_r($tmp_user, true);
            $messages[] = "tmp_user ends. catlist follows :===8.2===.";
            $messages[] = print_r($catlist, true);
            $messages[] = "catlist ends===8.2===.";
            debug_logmessage($messages, 'logfile');
            unset($messages);
        }

        // foreach($tmp_user['categoryids'] as $key => $catid) {
        foreach($tmp_user->categoryids as $key => $catid) {
            foreach ($catlist as $key => $val) {
                if ($val['catid'] === $catid) {
                   $requiredcapabilities[] = $val['capability'];
               }
            }
        }

        if (isset($debug)) {
            $messages[] = "requiredcapabilities follows :===8.3===.";
            $messages[] = print_r($requiredcapabilities, true);
            $messages[] = "requiredcapabilities ends===8.3===.";
            debug_logmessage($messages, 'logfile');
            unset($messages);
        }

        // Lenovo ********************************************************************************.
        // The following code is copied from:
        //          Function: search_courses
        //          Location: /course/renderer.php
        //          Version: Moodle 2019052002.07
        //          Release: 3.7.2+ (Build: 20191008)
        //
        //  10/14/19 - Code copied to here; added Lenovo customized code.
        // Lenovo ********************************************************************************.
        $content = '';
        if (!empty($searchcriteria)) {
            // print search results

            $displayoptions = array('sort' => array('displayname' => 1));
            // take the current page and number of results per page from query
            $perpage = optional_param('perpage', 0, PARAM_RAW);
            if ($perpage !== 'all') {
                $displayoptions['limit'] = ((int)$perpage <= 0) ? $CFG->coursesperpage : (int)$perpage;
                $page = optional_param('page', 0, PARAM_INT);
                $displayoptions['offset'] = $displayoptions['limit'] * $page;
            }
            // options 'paginationurl' and 'paginationallowall' are only used in method coursecat_courses()
            $displayoptions['paginationurl'] = new moodle_url('/course/search.php', $searchcriteria);
            $displayoptions['paginationallowall'] = true; // allow adding link 'View all'

            $class = 'course-search-result';
            foreach ($searchcriteria as $key => $value) {
                if (!empty($value)) {
                    $class .= ' course-search-result-'. $key;
                }
            }
            $chelper = new coursecat_helper();
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT)->
                    set_courses_display_options($displayoptions)->
                    set_search_criteria($searchcriteria)->
                    set_attributes(array('class' => $class));

            // Lenovo ********************************************************************************
            // Starting with Adaptable 1.6.0, the override of the course_renderer class was moved from theme/adaptable/renderers.php to
            //      theme/adaptable/classes/output/core/course_renderer.php. As such, we need to put prepend pathing information (i.e. '\')
            //      to locate and use coursecat functions.
            // Lenovo ********************************************************************************
            //  03/22/18 - RF - Experimenting with capabilities...
            // * @param array $requiredcapabilites List of capabilities required to see return course.
            // 03/23/18 - Clone code from swtc plugin that gets accesstype of user, loads caps of user, etc.
            // $requiredcapabilities = array('local/swtc:ebg_access_premiersupport_portfolio');
            // Lenovo ********************************************************************************
            // 06/04/18 - For each course found, get the top-level category and see if user has capability. Code similar to
            //          /course/renderer.php in function coursecat_category. If user does not have capability, remove the course
            //          from the courses list.
            // Lenovo ********************************************************************************
            // $courses = \coursecat::search_courses($searchcriteria, $chelper->get_courses_display_options(), $requiredcapabilities);  // Moodle 3.6
            // $totalcount = \coursecat::search_courses_count($searchcriteria, $options, $requiredcapabilities);        // Moodle 3.6
            // $courses = core_course_category::search_courses($searchcriteria, $chelper->get_courses_display_options());
            // $totalcount = core_course_category::search_courses_count($searchcriteria);
            $courses = \core_course_category::search_courses($searchcriteria, $chelper->get_courses_display_options(), $requiredcapabilities);
            $totalcount = \core_course_category::search_courses_count($searchcriteria, $options, $requiredcapabilities);

            // Lenovo ********************************************************************************
            // 06/04/18 - For each course found, get the top-level category and see if user has access. Code similar to
            //          /course/renderer.php in function coursecat_category. If user does not have access, remove the course
            //          from the courses list.
            // Lenovo ********************************************************************************
            $index = null;
            $courses_removed = 0;
            // Lenovo ********************************************************************************
            // Main loop. Find top-level catid of category. If user has access, leave the course in the list.
            //      If the user doesn't, remove it from the list.
            // Lenovo ********************************************************************************
            // Category id for the category name in question is in $coursecat->id.
            // In theory, if the category id appears in $swtc_user->categoryids, that alone should be enough to list the category.
            // Lenovo ********************************************************************************
            foreach ($courses as $key => $course) {
                // if (swtc_user_access_category($course->category)) {        // 03/02/20
                if ((in_array($course->category, array_keys($categories))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
                    if (isset($debug)) {
                        $messages[] = print_r("index is :$index. Will list the course===8.4===.", true);
                        debug_logmessage($messages, 'detailed');
                        unset($messages);
                    }
                } else {
                    if (isset($debug)) {
                        $messages[] = "index not found. Will NOT list the course. Removing course===8.4===.";
                        debug_logmessage($messages, 'both');
                        unset($messages);
                    }
                    $courses_removed++;
                    unset($courses[$key]);
                }
            }
            $totalcount = $totalcount - $courses_removed;       // Lenovo

            $courseslist = $this->coursecat_courses($chelper, $courses, $totalcount);

            // Lenovo ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "courses follows :===8.3.5===.";
                $messages[] = print_r($courses, true);
                $messages[] = "courses ends. totalcount is :$totalcount.===8.3.5===.";
                $messages[] = "courseslist follows :===8.3.5===.";
                $messages[] = print_r($courseslist, true);
                $messages[] = "courseslist ends.===8.3.5===.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }

            if (!$totalcount) {
                if (!empty($searchcriteria['search'])) {
                    $content .= $this->heading(get_string('nocoursesfound', '', $searchcriteria['search']));
                } else {
                    $content .= $this->heading(get_string('novalidcourses'));
                }
            } else {
                $content .= $this->heading(get_string('searchresults'). ": $totalcount");
                $content .= $courseslist;
            }

            if (!empty($searchcriteria['search'])) {
                // print search form only if there was a search by search string, otherwise it is confusing
                $content .= $this->box_start('generalbox mdl-align');
                $content .= $this->course_search_form($searchcriteria['search']);
                $content .= $this->box_end();
            }
        } else {
            // just print search form
            $content .= $this->box_start('generalbox mdl-align');
            $content .= $this->course_search_form();
            $content .= $this->box_end();
        }
        return $content;
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
     * Lenovo history:
     *
     * 11/06/18 - Added module History section (this section); updated the PremierSupport check with the new strings.
     * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
     *                      removed all three and changed to customized capabilities.
     * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
     *                          for Students are applied in the category context.
	 * 12/11/18 - If the user has not logged in yet (as in resetting password), $SESSION->EBGLMS and $SESSION->EBGLMS->USER will
	 *						exist, but most values will not be set yet (because they haven't logged in yet). Therefore, we must check for this condition
	 *						in many places.
	 * 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and ServiceDelivery
	 *						manager and administrator user types; changing to checking "is_enrolled" for PS / SD student user types.
     *	10/11/19 - Moved majority of customized code to here from /theme/adaptable/classes/output/core/course_renderer; added utility functions.
     * 10/13/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
     * 10/14/19 - Not working; removed.
     *
     */
    public function add_menu_content($branch) {
        global $PAGE, $COURSE, $OUTPUT, $CFG, $USER, $SESSION;

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();

        // Other Lenovo variables.
        $mycurriculums = get_string('mycurriculums', 'local_swtc');       // The title for 'My Curriculums'.
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering renderers.php. ===theme_adaptable.enter.";
            $messages[] = "About to print swtc_user.";
            $messages[] = print_r($swtc_user, true);
            $messages[] = "Finished printing swtc_user.";
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }

        // Lenovo ********************************************************************************
        // 11/05/18 - Adding two menu items under "My Courses": "My Courses" and "My Curriculums".
        // 11/15/18 - All PremierSupport and ServiceDelivery user types and Lenovo-administrators get the new
        //                      "My Curriculums" menu item.
        // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
        //                      the capabilities for Students are applied in the category context.
        // 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
        //							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
        //							PS / SD student user types.
        // Lenovo ********************************************************************************
        if ((has_capability('local/swtc:ebg_view_curriculums', context_system::instance())) || (!empty(curriculums_getall_enrollments_for_user($USER->id)))) {
            // Use the "mysites" string in theme_adaptable since it is in the correct case ("My Courses").
            $branch->add(get_string('mysites', 'theme_adaptable'), new moodle_url('/my/index.php'), '', null, 'mycourses');
            $branch->add($mycurriculums, new moodle_url('/local/swtc/lib/curriculums.php'), '', null, 'mycurriculums');
        }

        return $branch;

    }

}
