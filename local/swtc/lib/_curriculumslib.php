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
 * @package    local
 * @subpackage swtc/lib/curriculums.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/17/21 - Initial writing.
 *
 **/

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/user/profile/lib.php');

use \local_swtc\grouplib\grouplib;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/criteria/swtc_completion_info.php');

/**
 * Returns an array of all the curriculum courses the user is enrolled in or NULL if they are not enrolled in any.
 *
 * @param $userid The userid of the user to check.
 *
 * @return $array  All the curriculum courses the user is enrolled in or NULL if they are not enrolled in any.
 *
 * History:
 *
 * 12/18/18 - Initial writing.
 *
 **/
function curriculums_getall_enrollments_for_user($userid) {

    $enrolledcurriculums = array();
    $enrolledcourses = null;

    // Get ALL the curriculum courses in ALL the curriculums.
    $curriculumcourses = curriculums_getall();

    // Get all the courses the user is enrolled in. We'll need this later
    // (to check if they are enrolled in a specific curriculum course).
    $enrolledcourses = enrol_get_users_courses($userid);

    // Loop through and see if user is enrolled. If so, add it to the array.
    foreach ($curriculumcourses as $course) {
        if (!empty($enrolledcourses[$course->courseid])) {
            $enrolledcurriculums[] = $course->courseid;
        }
    }

    return $enrolledcurriculums;

}

/**
 * Looks for all the curriculums a course is a part of.
 *
 * @param $courseid    The courseid of the course you want to search for.
 *
 * @return $array   If the course is found, the curriculum(s) courses array (courseid and the course fullname)
 * OR null if course is not found in any curriculum.
 *
 * History:
 *
 * 12/15/18 - Initial writing.
 *
 **/
function curriculumcourses_find_course($courseid) {

    // Get ALL the curriculum courses in ALL the curriculums.
    $curriculumcourses = curriculumcourses_list('all');

    // Look for the course passed in.
    if (array_key_exists($courseid, $curriculumcourses)) {
        // Found it.
        return $curriculumcourses[$courseid];
    } else {
        // Didn't find it.
        return null;
    }

}

/**
 * Returns all courses in a curriculum based on courseid of curriculum passed in.
 * Or all courses in all curriculums if 'all' option is passed.
 *
 * @param $curriculum    The courseid of the curriculum course to list courses OR 'all' to list all courses in all curriculums.
 *
 * @return $array   The curriculum courses array (courseid, course shortname, and value of "curriculums").
 *
 * History:
 *
 * 04/18/21 - Initial writing.
 *
 **/
function curriculumcourses_list($curriculum = 'all') {
    global $DB;

    if ($curriculum === 'all') {
        $curriculum = '%';
    }

    $sql = "SELECT cfo2.courseid AS 'id', c1.shortname, cfo2.value AS 'curriculums'
                FROM {course_format_options} AS cfo1
                LEFT OUTER JOIN {course} AS c1 ON (cfo1.courseid = c1.id) AND (cfo1.name = 'ispartofcurriculum')
				LEFT OUTER JOIN {course_categories} AS cc ON (cc.id = c1.category)
				LEFT OUTER JOIN {course_format_options} AS cfo2 ON (cfo2.courseid = c1.id) AND (cfo2.name = 'curriculums')
				LEFT OUTER JOIN {course} AS c2 ON (c2.id IN (cfo2.value))
				WHERE (cfo1.value = 1) AND (cfo2.value LIKE concat('%', '$curriculum', '%')) ORDER BY c1.id";

    return $DB->get_records_sql($sql);

}

/**
 * Returns all curriculum courses (based on the course_format_option "iscurriculum" being '1').
 *
 * @param N/A
 *
 * @return $array   The curriculum array (courseid and the course fullname).
 *
 * History:
 *
 * 04/18/21 - Initial writing.
 *
 **/
function curriculums_getall() {
    global $DB;

    $sql = "SELECT cfo.id, cfo.courseid, c.fullname, c.shortname
                FROM {course_format_options} cfo
                LEFT OUTER JOIN {course} c ON (c.id = cfo.courseid)
                WHERE (cfo.name = 'iscurriculum') AND (cfo.value = 1) ORDER BY cfo.courseid";

    return $DB->get_records_sql($sql);

}

 /**
  * Copied from /blocks/completionstatus/details.php. (Students report)
  *
  * History:
  *
  * 04/17/21 - Initial writing.
  *
  **/
function curriculum_getcompletionstatus_details($id, $userid) {
    global $DB, $PAGE, $OUTPUT, $USER;

    // To contain all the output.
    $output = '';

    // Load course.
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

    // Load user.
    if ($userid) {
        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    } else {
        $user = $USER;
    }

    // Load completion data.
    $info = new swtc_completion_info($course);

    $returnurl = new moodle_url('/course/view.php', array('id' => $id));

    // Don't display if completion isn't enabled.
    if (!$info->is_enabled()) {
        print_error('completionnotenabled', 'completion', $returnurl);
    }

    // Check this user is enroled.
    if (!$info->is_tracked_user($user->id)) {
        if ($USER->id == $user->id) {
            print_error('notenroled', 'completion', $returnurl);
        } else {
            print_error('usernotenroled', 'completion', $returnurl);
        }
    }

    // Print header.
    $page = get_string('completionprogressdetails', 'block_completionstatus');
    $title = format_string($course->fullname) . ': ' . $page;

    $PAGE->navbar->add($page);
    $PAGE->set_pagelayout('report');
    $PAGE->set_url('/blocks/completionstatus/details.php', array('course' => $course->id, 'user' => $user->id));
    $PAGE->set_title(get_string('course') . ': ' . $course->fullname);
    $PAGE->set_heading($title);

    // Display completion status.
    $output .= html_writer::start_tag('table', array('class' => 'generalbox boxaligncenter'));
    $output .= html_writer::start_tag('tbody');

    // If not display logged in user, show user name.
    if ($USER->id != $user->id) {
        $output .= html_writer::start_tag('tr');
        $output .= html_writer::start_tag('td', array('colspan' => '2'));
        $output .= html_writer::tag('b', get_string('showinguser', 'completion') . ' ');
        $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
        $output .= html_writer::link($url, fullname($user));
        $output .= html_writer::end_tag('td');
        $output .= html_writer::end_tag('tr');
    }

    $output .= html_writer::start_tag('tr');
    $output .= html_writer::start_tag('td', array('colspan' => '2'));
    $output .= html_writer::tag('b', get_string('status') . ' ');

    // Is course complete?
    $coursecomplete = $info->is_course_complete($user->id);

    // Has this user completed any criteria?
    $criteriacomplete = $info->count_course_user_data($user->id);

    // Load course completion.
    $params = array(
        'userid' => $user->id,
        'course' => $course->id,
    );
    $ccompletion = new completion_completion($params);

    if ($coursecomplete) {
        $output .= get_string('complete');
    } else if (!$criteriacomplete && !$ccompletion->timestarted) {
        $output .= html_writer::tag('i', get_string('notyetstarted', 'completion'));
    } else {
        $output .= html_writer::tag('i', get_string('inprogress', 'completion'));
    }

    $output .= html_writer::end_tag('td');
    $output .= html_writer::end_tag('tr');

    // Load criteria to display.
    $completions = $info->get_completions($user->id);

    // Check if this course has any criteria.
    if (empty($completions)) {
        $output .= html_writer::start_tag('tr');
        $output .= html_writer::start_tag('td', array('colspan' => '2'));
        $output .= html_writer::start_tag('br');
        $output .= $OUTPUT->box(get_string('nocriteriaset', 'completion'), 'noticebox');
        $output .= html_writer::end_tag('td');
        $output .= html_writer::end_tag('tr');
        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
    } else {
        $output .= html_writer::start_tag('tr');
        $output .= html_writer::start_tag('td', array('colspan' => '2'));
        $output .= html_writer::tag('b', get_string('required') . ' ');

        // Get overall aggregation method.
        $overall = $info->get_aggregation_method();

        if ($overall == COMPLETION_AGGREGATION_ALL) {
            $output .= get_string('criteriarequiredall', 'completion');
        } else {
            $output .= get_string('criteriarequiredany', 'completion');
        }

        $output .= html_writer::end_tag('td');
        $output .= html_writer::end_tag('tr');
        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');

        // Generate markup for criteria statuses.
        $output .= html_writer::start_tag('table',
                array('class' => 'generalbox logtable boxaligncenter', 'id' => 'criteriastatus', 'width' => '100%'));
        $output .= html_writer::start_tag('tbody');
        $output .= html_writer::start_tag('tr', array('class' => 'ccheader'));
        $output .= html_writer::tag('th', get_string('criteriagroup', 'block_completionstatus'),
            array('class' => 'c0 header', 'scope' => 'col'));
        $output .= html_writer::tag('th', get_string('criteria', 'completion'), array('class' => 'c1 header', 'scope' => 'col'));
        $output .= html_writer::tag('th', get_string('requirement', 'block_completionstatus'),
            array('class' => 'c2 header', 'scope' => 'col'));
        $output .= html_writer::tag('th', get_string('status'), array('class' => 'c3 header', 'scope' => 'col'));
        $output .= html_writer::tag('th', get_string('complete'), array('class' => 'c4 header', 'scope' => 'col'));
        $output .= html_writer::tag('th', get_string('completiondate', 'report_completion'),
            array('class' => 'c5 header', 'scope' => 'col'));
        $output .= html_writer::end_tag('tr');

        // Save row data.
        $rows = array();

        // Loop through course criteria.
        foreach ($completions as $completion) {
            $criteria = $completion->get_criteria();

            $row = array();
            $row['type'] = $criteria->criteriatype;
            $row['title'] = $criteria->get_title();
            $row['status'] = $completion->get_status();
            $row['complete'] = $completion->is_complete();
            $row['timecompleted'] = $completion->timecompleted;
            $row['details'] = $criteria->get_details($completion);
            $rows[] = $row;
        }

        // Print table.
        $lasttype = '';
        $aggtype = false;
        $oddeven = 0;

        foreach ($rows as $row) {

            $output .= html_writer::start_tag('tr', array('class' => 'r' . $oddeven));
            // Criteria group.
            $output .= html_writer::start_tag('td', array('class' => 'cell c0'));
            if ($lasttype !== $row['details']['type']) {
                $lasttype = $row['details']['type'];
                $output .= $lasttype;

                // Reset agg type.
                $aggtype = true;
            } else {
                // Display aggregation type.
                if ($aggtype) {
                    $agg = $info->get_aggregation_method($row['type']);
                    $output .= '('. html_writer::start_tag('i');
                    if ($agg == COMPLETION_AGGREGATION_ALL) {
                        $output .= core_text::strtolower(get_string('all', 'completion'));
                    } else {
                        $output .= core_text::strtolower(get_string('any', 'completion'));
                    }

                    $output .= ' ' . html_writer::end_tag('i') .core_text::strtolower(get_string('required')).')';
                    $aggtype = false;
                }
            }
            $output .= html_writer::end_tag('td');

            // Criteria title.
            $output .= html_writer::start_tag('td', array('class' => 'cell c1'));
            $output .= $row['details']['criteria'];
            $output .= html_writer::end_tag('td');

            // Requirement.
            $output .= html_writer::start_tag('td', array('class' => 'cell c2'));
            $output .= $row['details']['requirement'];
            $output .= html_writer::end_tag('td');

            // Status.
            $output .= html_writer::start_tag('td', array('class' => 'cell c3'));
            $output .= $row['details']['status'];
            $output .= html_writer::end_tag('td');

            // Is complete.
            $output .= html_writer::start_tag('td', array('class' => 'cell c4'));
            $output .= $row['complete'] ? get_string('yes') : get_string('no');
            $output .= html_writer::end_tag('td');

            // Completion data.
            $output .= html_writer::start_tag('td', array('class' => 'cell c5'));
            if ($row['timecompleted']) {
                $output .= userdate($row['timecompleted'], get_string('strftimedate', 'langconfig'));
            } else {
                $output .= '-';
            }
            $output .= html_writer::end_tag('td');
            $output .= html_writer::end_tag('tr');
            // For row striping.
            $oddeven = $oddeven ? 0 : 1;
        }

        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
    }
    $courseurl = new moodle_url("/course/view.php", array('id' => $course->id));
    $output .= html_writer::start_tag('div', array('class' => 'buttons'));
    $output .= $OUTPUT->single_button($courseurl, 'View course ' .$course->fullname, 'get');
    $output .= html_writer::end_tag('div');

    return $output;

}

/**
 * Copied from /report/completion/index.php. (Managers report)
 *
 * History:
 *
 * 10/24/20 - Initial writing.
 *
 **/
function curriculum_report_completion_index($swtcuser, $courseid) {
    global $DB, $CFG, $OUTPUT, $USER, $PAGE;

    // SWTC ********************************************************************************.
    // SWTC swtcuser and debug variables.
    // In curriculum_report_completion_index, added $swtcuser to parameters to function.
    $debug = swtc_get_debug();

    // Other SWTC variables.
    $output = '';       // To contain all the output.
    $swtcgroups = new grouplib;

    // Remember - PremierSupportand ServiceDelivery managers and admins have special access.
    // Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
    // for Students are applied in the category context.
    $systemcontext = context_system::instance();

    // Get all the portfolios the user has access to (even though we only need one).
    $portfolios = get_portfolios_access($swtcuser->get_roleid());
    foreach ($portfolios as $id => $portfolio) {
        // Get a context to the portfolio.
        $swtcstudcontext = context_coursecat::instance($portfolio->catid);
        break;
    }

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "In /local/swtc/lib/curriculums.php === curriculum_report_completion_index.enter===";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    /**
     * Configuration
     */
    define('COMPLETION_REPORT_PAGE',        25);
    define('COMPLETION_REPORT_COL_TITLES',  true);

    /*
     * Setup page, check permissions
     */

    // Get course.
    $format = '';
    $sort = '';

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id);

    $url = new moodle_url('/report/completion/index.php', array('course' => $course->id));
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('report');

    $firstnamesort = ($sort == 'firstname');
    $excel = ($format == 'excelcsv');
    $csv = ($format == 'csv' || $excel);

    // Load CSV library.
    if ($csv) {
        require_once("{$CFG->libdir}/csvlib.class.php");
    }

    // Paging.
    $start   = optional_param('start', 0, PARAM_INT);
    $sifirst = optional_param('sifirst', 'all', PARAM_NOTAGS);
    $silast  = optional_param('silast', 'all', PARAM_NOTAGS);

    // Whether to show extra user identity information.
    $extrafields = get_extra_user_fields($context);
    $leftcols = 1 + count($extrafields);

    // Check permissions.

    require_capability('report/completion:view', $context);

    // Get group mode.
    // Supposed to verify group.
    $group = $swtcgroups->groups_get_course_group($course, true);
    if ($group === 0 && $course->groupmode == SEPARATEGROUPS) {
        require_capability('moodle/site:accessallgroups', $context);
    }

    // Load data.
    // Retrieve course_module data for all modules in the course.
    $modinfo = get_fast_modinfo($course);

    // Get criteria for course.
    $completion = new swtc_completion_info($course);

    if (!$completion->has_criteria()) {
        print_error('nocriteriaset', 'completion', $CFG->wwwroot.'/course/report.php?id='.$course->id);
    }

    // Get criteria and put in correct order.
    $criteria = array();

    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $criterion) {
        $criteria[] = $criterion;
    }

    foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion) {
        $criteria[] = $criterion;
    }

    foreach ($completion->get_criteria() as $criterion) {
        if (!in_array($criterion->criteriatype, array(
                COMPLETION_CRITERIA_TYPE_COURSE, COMPLETION_CRITERIA_TYPE_ACTIVITY))) {
            $criteria[] = $criterion;
        }
    }

    // Can logged in user mark users as complete?
    // If the logged in user has a role defined in the role criteria.
    $allowmarkingcriteria = null;

    if (!$csv) {
        // Get role criteria.
        $rcriteria = $completion->get_criteria(COMPLETION_CRITERIA_TYPE_ROLE);

        if (!empty($rcriteria)) {

            foreach ($rcriteria as $rcriterion) {
                $users = get_role_users($rcriterion->role, $context, true);

                // If logged in user has this role, allow marking complete.
                if ($users && in_array($USER->id, array_keys($users))) {
                    $allowmarkingcriteria = $rcriterion->id;
                    break;
                }
            }
        }
    }

    /*
     * Setup page header
     */
    if ($csv) {

        $shortname = format_string($course->shortname, true, array('context' => $context));
        $shortname = preg_replace('/[^a-z0-9-]/', '_', core_text::strtolower(strip_tags($shortname)));

        $export = new csv_export_writer();
        $export->set_filename('completion-'.$shortname);

    } else {
        // Navigation and header.
        $strcompletion = get_string('coursecompletion');

        $PAGE->set_title($strcompletion);
        $PAGE->set_heading($course->fullname);

        // Handle groups (if enabled).
        $swtcgroups->groups_print_course_menu($course, $CFG->wwwroot.'/report/completion/index.php?course='.$course->id);
    }

    if ($sifirst !== 'all') {
        set_user_preference('ifirst', $sifirst);
    }
    if ($silast !== 'all') {
        set_user_preference('ilast', $silast);
    }

    if (!empty($USER->preference['ifirst'])) {
        $sifirst = $USER->preference['ifirst'];
    } else {
        $sifirst = 'all';
    }

    if (!empty($USER->preference['ilast'])) {
        $silast = $USER->preference['ilast'];
    } else {
        $silast = 'all';
    }

    // Generate where clause.
    $where = array();
    $whereparams = array();

    if ($sifirst !== 'all') {
        $where[] = $DB->sql_like('u.firstname', ':sifirst', false);
        $whereparams['sifirst'] = $sifirst.'%';
    }

    if ($silast !== 'all') {
        $where[] = $DB->sql_like('u.lastname', ':silast', false);
        $whereparams['silast'] = $silast.'%';
    }

    // SWTC ********************************************************************************.
    // Add PremierSupport roles to whereparams.
    // Add additional WHERE condition if $USER role is PremierSupport-manager or PremierSupport-admin.
    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "About to print where ==curriculum_report_completion_index.===.\n";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where ==curriculum_report_completion_index.===.\n";
        $messages[] = "systemcontext follows :.\n";
        $messages[] = print_r($systemcontext, true);
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // Any user (PremierSupport and ServiceDelivery managers and admins for now) have special access
    // if they have the required capability.
    // SWTC ********************************************************************************.
    // IMPORTANT! Must perform the has_capability checks BEFORE calling set_where_conditions_by_accesstype.
    // SWTC ********************************************************************************.
    if (has_capability('local/swtc:swtc_view_mgmt_reports', $systemcontext)
    || has_capability('local/swtc:swtc_view_student_reports', $swtcstudcontext)) {

        list($where, $whereparams, $grandtotal) =
            $completion->set_where_conditions_by_accesstype($swtcuser, $where, $whereparams, $group);

    }

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "About to print whereparams ==curriculum_report_completion_index.===.\n";
        $messages[] = print_r($whereparams, true);
        $messages[] = "Finished printing whereparams ==curriculum_report_completion_index.===.\n";
        $messages[] = "About to print where (again) ==curriculum_report_completion_index.===.\n";
        $messages[] = print_r($where, true);
        $messages[] = "Finished printing where (again) ==curriculum_report_completion_index.===.\n";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }

    // SWTC ********************************************************************************.
    // SWTC customized code for Moodle core course completion.
    // SWTC ********************************************************************************.
    $total = $completion->get_num_tracked_users(implode(' AND ', $where), $whereparams, $group);

    // SWTC ********************************************************************************.
    if (isset($debug)) {
        $messages[] = "total is :$total. grandtotal is :$grandtotal  ==1.1.curriculum_report_completion_index.===.\n";
        $debug->logmessage($messages, 'both');
        unset($messages);
    }
    // SWTC ********************************************************************************.

    // If no users in this course what-so-ever.
    if (!$grandtotal) {
        // Fixed table output if no users are listed. No OUTPUT here (put in calling function). And change "exit" to "return".
        // $output .= $OUTPUT->container(get_string('err_nousers', 'completion'), 'errorbox errorboxcontent');
        // $output .= $OUTPUT->footer();
        // exit.
        return;
    }

    // Get user data.
    $progress = array();

    if ($total) {
        $progress = $completion->get_progress_all(
            implode(' AND ', $where),
            $whereparams,
            $group,
            $firstnamesort ? 'u.firstname ASC' : 'u.lastname ASC',
            $csv ? 0 : COMPLETION_REPORT_PAGE,
            $csv ? 0 : $start,
            $context
        );
    }
    // Build link for paging.
    $link = $CFG->wwwroot.'/report/completion/index.php?course='.$course->id;
    if (strlen($sort)) {
        $link .= '&amp;sort='.$sort;
    }
    $link .= '&amp;start=';

    $pagingbar = '';

    // Initials bar.
    $prefixfirst = 'sifirst';
    $prefixlast = 'silast';
    $pagingbar .= $OUTPUT->initials_bar($sifirst, 'firstinitial', get_string('firstname'), $prefixfirst, $url);
    $pagingbar .= $OUTPUT->initials_bar($silast, 'lastinitial', get_string('lastname'), $prefixlast, $url);

    // Do we need a paging bar?
    if ($total > COMPLETION_REPORT_PAGE) {

        // Paging bar.
        $pagingbar .= '<div class="paging">';
        $pagingbar .= get_string('page').': ';

        $sistrings = array();
        if ($sifirst != 'all') {
            $sistrings[] = "sifirst={$sifirst}";
        }
        if ($silast != 'all') {
            $sistrings[] = "silast={$silast}";
        }
        $sistring = !empty($sistrings) ? '&amp;'.implode('&amp;', $sistrings) : '';

        // Display previous link.
        if ($start > 0) {
            $pstart = max($start - COMPLETION_REPORT_PAGE, 0);
            $pagingbar .= "(<a class=\"previous\" href=\"{$link}{$pstart}{$sistring}\">".get_string('previous').'</a>)&nbsp;';
        }

        // Create page links.
        $curstart = 0;
        $curpage = 0;
        while ($curstart < $total) {
            $curpage++;

            if ($curstart == $start) {
                $pagingbar .= '&nbsp;'.$curpage.'&nbsp;';
            } else {
                $pagingbar .= "&nbsp;<a href=\"{$link}{$curstart}{$sistring}\">$curpage</a>&nbsp;";
            }

            $curstart += COMPLETION_REPORT_PAGE;
        }

        // Display next link.
        $nstart = $start + COMPLETION_REPORT_PAGE;
        if ($nstart < $total) {
            $pagingbar .= "&nbsp;(<a class=\"next\" href=\"{$link}{$nstart}{$sistring}\">".get_string('next').'</a>)';
        }

        $pagingbar .= '</div>';
    }

    /*
     * Draw table header
     */

    // Start of table.
    if (!$csv) {
        $output .= '<br class="clearer"/>'; // Ugh.

        $totalheader = ($total == $grandtotal) ? $total : "{$total}/{$grandtotal}";
        $output .= $OUTPUT->heading(get_string('allparticipants').": {$totalheader}", 3);

        $output .= $pagingbar;

        if (!$total) {
            // Fixed table output if no users are listed. No OUTPUT here (put in calling function). And change "exit" to "return".
            // $output .= $OUTPUT->heading(get_string('nothingtodisplay'), 2);
            // $output .= $OUTPUT->footer();
            // Exit.
            return;
        }

        $output .= '<table id="completion-progress" class="table table-bordered generaltable flexible boxaligncenter
            completionreport" style="text-align: left" cellpadding="5" border="1">';

        // Print criteria group names.
        $output .= PHP_EOL.'<thead><tr style="vertical-align: top">';
        $output .= '<th scope="row" class="rowheader" colspan="' . $leftcols . '">' .
                get_string('criteriagroup', 'completion') . '</th>';

        $currentgroup = false;
        $colcount = 0;
        for ($i = 0; $i <= count($criteria); $i++) {

            if (isset($criteria[$i])) {
                $criterion = $criteria[$i];

                if ($currentgroup && $criterion->criteriatype === $currentgroup->criteriatype) {
                    ++$colcount;
                    continue;
                }
            }

            // Print header cell.
            if ($colcount) {
                $output .= '<th scope="col" colspan="'.$colcount.
                    '" class="colheader criteriagroup">'.$currentgroup->get_type_title().'</th>';
            }

            if (isset($criteria[$i])) {
                // Move to next criteria type.
                $currentgroup = $criterion;
                $colcount = 1;
            }
        }

        // Overall course completion status.
        $output .= '<th style="text-align: center;">'.get_string('course').'</th>';

        $output .= '</tr>';

        // Print aggregation methods.
        $output .= PHP_EOL.'<tr style="vertical-align: top">';
        $output .= '<th scope="row" class="rowheader" colspan="' . $leftcols . '">' .
                get_string('aggregationmethod', 'completion').'</th>';

        $currentgroup = false;
        $colcount = 0;
        for ($i = 0; $i <= count($criteria); $i++) {

            if (isset($criteria[$i])) {
                $criterion = $criteria[$i];

                if ($currentgroup && $criterion->criteriatype === $currentgroup->criteriatype) {
                    ++$colcount;
                    continue;
                }
            }

            // Print header cell.
            if ($colcount) {
                $hasagg = array(
                    COMPLETION_CRITERIA_TYPE_COURSE,
                    COMPLETION_CRITERIA_TYPE_ACTIVITY,
                    COMPLETION_CRITERIA_TYPE_ROLE,
                );

                if (in_array($currentgroup->criteriatype, $hasagg)) {
                    // Try load a aggregation method.
                    $method = $completion->get_aggregation_method($currentgroup->criteriatype);

                    $method = $method == 1 ? get_string('all') : get_string('any');

                } else {
                    $method = '-';
                }

                $output .= '<th scope="col" colspan="'.$colcount.'" class="colheader aggheader">'.$method.'</th>';
            }

            if (isset($criteria[$i])) {
                // Move to next criteria type.
                $currentgroup = $criterion;
                $colcount = 1;
            }
        }

        // Overall course aggregation method.
        $output .= '<th scope="col" class="colheader aggheader aggcriteriacourse">';

        // Get course aggregation.
        $method = $completion->get_aggregation_method();

        $output .= $method == 1 ? get_string('all') : get_string('any');
        $output .= '</th>';

        $output .= '</tr>';

        // Print criteria titles.
        if (COMPLETION_REPORT_COL_TITLES) {

            $output .= PHP_EOL.'<tr>';
            $output .= '<th scope="row" class="rowheader" colspan="' . $leftcols . '">' .
                    get_string('criteria', 'completion') . '</th>';

            foreach ($criteria as $criterion) {
                // Get criteria details.
                // SWTC ********************************************************************************.
                // Added hyperlinks and tooltips to the column headers.
                // Changed tooltips hyperlink from viewing course to viewing individual course completion report.
                // All of the following must be kept in sync:
                // report/completion/index.php, /completion/criteria/completion_criteria_course.php, and
                // local/swtc/lib/curriculumslib.php.
                // SWTC ********************************************************************************.
                if ($criterion->criteriatype == 8) {
                    list($id, $shortname, $fullname) = $completion->get_title_detailed_course($criterion->courseinstance);
                } else if ($criterion->criteriatype == 4) {
                    list($id, $shortname, $fullname) = $completion->get_title_detailed_activity($criterion->moduleinstance,
                        $criterion->module);
                }

                $tgroups = groups_get_user_groups($id, $swtcuser->get_userid());

                // Note: Should only be one returned (if user is not a Lenovo-admin or Lenovo-siteadmin).
                if (!empty($tgroups[0][0])) {
                    $tgroupid = $tgroups[0][0];

                    $courseurl = new moodle_url("/report/completion/index.php", array('course' => $id, 'group' => $tgroupid));
                } else {
                    $courseurl = new moodle_url("/report/completion/index.php", array('course' => $id));
                }

                $tooltiptext = 'Click to view course completion report for ' .$shortname. ' ' .$fullname;

                $output .= '<th scope="col" class="colheader criterianame">';
                $output .= '<div class="rotated-text-container"><span class="rotated-text"><a href="'.$courseurl->out().
                    '" target="_blank" title=" '.$tooltiptext.' ">'.$shortname.'</a></span></div>';
                $output .= '</th>';
            }

            // Overall course completion status.
            $output .= '<th scope="col" class="colheader criterianame">';
            $output .= '<div class="rotated-text-container"><span class="rotated-text">'.
                get_string('coursecomplete', 'completion').'</span></div>';
            $output .= '</th></tr>';
        }

        // Print user heading and icons.
        $output .= '<tr>';

        // User heading / sort option.
        $output .= '<th scope="col" class="completion-sortchoice" style="clear: both;">';

        $sistring = "&amp;silast={$silast}&amp;sifirst={$sifirst}";

        if ($firstnamesort) {
            $output .=
                get_string('firstname')." / <a href=\"./index.php?course={$course->id}{$sistring}\">".
                get_string('lastname').'</a>';
        } else {
            $output .= "<a href=\"./index.php?course={$course->id}&amp;sort=firstname{$sistring}\">".
                get_string('firstname').'</a> / '.
                get_string('lastname');
        }
        $output .= '</th>';

        // Print user identity columns.
        foreach ($extrafields as $field) {
            $output .= '<th scope="col" class="completion-identifyfield">' .
                    get_user_field_name($field) . '</th>';
        }

        // Print criteria icons.
        foreach ($criteria as $criterion) {

            // Generate icon details.
            $iconlink = '';
            $iconalt = ''; // Required.
            $iconattributes = array('class' => 'icon');
            switch ($criterion->criteriatype) {

                case COMPLETION_CRITERIA_TYPE_ACTIVITY:

                    // Display icon.
                    $iconlink = $CFG->wwwroot.'/mod/'.$criterion->module.'/view.php?id='.$criterion->moduleinstance;
                    $iconattributes['title'] = $modinfo->cms[$criterion->moduleinstance]->get_formatted_name();
                    $iconalt = get_string('modulename', $criterion->module);
                    break;

                case COMPLETION_CRITERIA_TYPE_COURSE:
                    // Load course.
                    $crs = $DB->get_record('course', array('id' => $criterion->courseinstance));

                    // Display icon.
                    $iconlink = $CFG->wwwroot.'/course/view.php?id='.$criterion->courseinstance;
                    $iconattributes['title'] = format_string($crs->fullname, true,
                        array('context' => context_course::instance($crs->id, MUST_EXIST)));
                    $iconalt = format_string($crs->shortname, true, array('context' => context_course::instance($crs->id)));
                    break;

                case COMPLETION_CRITERIA_TYPE_ROLE:
                    // Load role.
                    $role = $DB->get_record('role', array('id' => $criterion->role));

                    // Display icon.
                    $iconalt = $role->name;
                    break;
            }

            // Create icon alt if not supplied.
            if (!$iconalt) {
                $iconalt = $criterion->get_title();
            }

            // Print icon and cell.
            $output .= '<th class="criteriaicon">';

            $output .= ($iconlink ? '<a href="'.$iconlink.'" title="'.$iconattributes['title'].'">' : '');
            $output .= $OUTPUT->render($criterion->get_icon($iconalt, $iconattributes));
            $output .= ($iconlink ? '</a>' : '');

            $output .= '</th>';
        }

        // Overall course completion status.
        $output .= '<th class="criteriaicon">';
        $output .= $OUTPUT->pix_icon('i/course', get_string('coursecomplete', 'completion'));
        $output .= '</th>';

        $output .= '</tr></thead>';

        $output .= '<tbody>';
    } else {
        // The CSV headers.
        $row = array();

        $row[] = get_string('id', 'report_completion');
        $row[] = get_string('name', 'report_completion');
        foreach ($extrafields as $field) {
            $row[] = get_user_field_name($field);
        }

        // Add activity headers.
        foreach ($criteria as $criterion) {

            // Handle activity completion differently.
            if ($criterion->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {

                // Load activity.
                $mod = $criterion->get_mod_instance();
                $row[] = $formattedname = format_string($mod->name, true,
                        array('context' => context_module::instance($criterion->moduleinstance)));
                $row[] = $formattedname . ' - ' . get_string('completiondate', 'report_completion');
            } else {
                // Handle all other criteria.
                // SWTC ********************************************************************************.
                // Added hyperlinks and tooltips to the column headers.
                // Changed tooltips hyperlink from viewing course to viewing individual course completion report.
                // All of the following must be kept in sync:
                // report/completion/index.php, /completion/criteria/completion_criteria_course.php, and
                // local/swtc/lib/curriculumslib.php.
                // SWTC ********************************************************************************.
                if ($criterion->criteriatype == 8) {
                    list($id, $shortname, $fullname) = $completion->get_title_detailed_course($criterion->courseinstance);
                } else if ($criterion->criteriatype == 4) {
                    list($id, $shortname, $fullname) = $criterion->get_title_detailed_activity($criterion->moduleinstance,
                        $criterion->module);
                }
                $row[] = strip_tags($shortname. ' ' .$fullname);
            }
        }

        $row[] = get_string('coursecomplete', 'completion');

        $export->add_data($row);
    }

    // Display a row for each user.
    foreach ($progress as $user) {

        // User name.
        if ($csv) {
            $row = array();
            $row[] = $user->id;
            $row[] = fullname($user);
            foreach ($extrafields as $field) {
                $row[] = $user->{$field};
            }
        } else {
            $output .= PHP_EOL.'<tr id="user-'.$user->id.'">';

            if (completion_can_view_data($user->id, $course)) {
                $userurl = new moodle_url('/blocks/completionstatus/details.php',
                    array('course' => $course->id, 'user' => $user->id));
            } else {
                $userurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id));
            }

            $output .= '<th scope="row"><a href="'.$userurl->out().'">'.fullname($user).'</a></th>';
            foreach ($extrafields as $field) {
                $output .= '<td>'.s($user->{$field}).'</td>';
            }
        }

        // SWTC ********************************************************************************.
        // Changing tooltips hyperlink from viewing course to viewing individual course completion report; changing way course
        // completion data is presented if course completion is based on other courses being complete.
        // SWTC ********************************************************************************.
        // Progress for each course completion criteria.
        foreach ($criteria as $criterion) {

            $criteriacompletion = $completion->get_user_completion($user->id, $criterion);
            $iscomplete = $criteriacompletion->is_complete();

            // Handle activity completion differently.
            if ($criterion->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {

                // Load activity.
                $activity = $modinfo->cms[$criterion->moduleinstance];

                // Get progress information and state.
                if (array_key_exists($activity->id, $user->progress)) {
                    $state = $user->progress[$activity->id]->completionstate;
                } else if ($iscomplete) {
                    $state = COMPLETION_COMPLETE;
                } else {
                    $state = COMPLETION_INCOMPLETE;
                }
                if ($iscomplete) {
                    $date = userdate($criteriacompletion->timecompleted, get_string('strftimedatetimeshort', 'langconfig'));
                } else {
                    $date = '';
                }

                // Work out how it corresponds to an icon.
                switch($state) {
                    case COMPLETION_INCOMPLETE    : $completiontype = 'n';
                        break;
                    case COMPLETION_COMPLETE      : $completiontype = 'y';
                        break;
                    case COMPLETION_COMPLETE_PASS : $completiontype = 'pass';
                        break;
                    case COMPLETION_COMPLETE_FAIL : $completiontype = 'fail';
                        break;
                }

                $auto = $activity->completion == COMPLETION_TRACKING_AUTOMATIC;
                $completionicon = 'completion-'.($auto ? 'auto' : 'manual').'-'.$completiontype;

                $describe = get_string('completion-'.$completiontype, 'completion');
                $a = new StdClass();
                $a->state     = $describe;
                $a->date      = $date;
                $a->user      = fullname($user);
                $a->activity  = $activity->get_formatted_name();
                $fulldescribe = get_string('progress-title', 'completion', $a);

                if ($csv) {
                    $row[] = $describe;
                    $row[] = $date;
                } else {
                    $output .= '<td class="completion-progresscell">';

                    $output .= $OUTPUT->pix_icon('i/' . $completionicon, $fulldescribe);

                    $output .= '</td>';
                }

                // SWTC ********************************************************************************.
                // Changing way course completion data is presented if course completion is based on other courses
                // being complete.
                // Notes:
                // blocks/completionstatus/details.php is a good source of information.
                // SWTC ********************************************************************************.
            } else if ($criterion->criteriatype == COMPLETION_CRITERIA_TYPE_COURSE) {
                // Get criteria for the course that is part of the required list.

                // SWTC ********************************************************************************.
                // For debugging, use the userid of one user to check.
                // SWTC ********************************************************************************.
                // if ($user->id == 13503) {
                    // print_object($criterion);
                    // Note: Since COMPLETION_CRITERIA_TYPE_COURSE was checked, courseinstance will be the id of
                    // the "other" course that needs to be checked.
                    // 03/21/19  Ok, maybe not.
                if (isset($criterion->courseinstance)) {
                    $tempcourse = get_course($criterion->courseinstance);
                    $info = new swtc_completion_info($tempcourse);

                    // Is course complete?
                    $iscomplete = $info->is_course_complete($user->id);

                    // Load course completion.
                    $params = array(
                        'userid' => $user->id,
                        'course' => $criterion->courseinstance,
                    );

                    $criteriacompletion = new completion_completion($params);
                }
                // SWTC ********************************************************************************.
                // All other course completion types.
                // SWTC ********************************************************************************.
            }

            // Handle all other criteria.
            $completiontype = $iscomplete ? 'y' : 'n';
            $completionicon = 'completion-auto-'.$completiontype;

            $describe = get_string('completion-'.$completiontype, 'completion');

            $a = new stdClass();
            $a->state    = $describe;

            if ($iscomplete) {
                $a->date = userdate($criteriacompletion->timecompleted, get_string('strftimedatetimeshort', 'langconfig'));
            } else {
                $a->date = '';
            }

            $a->user     = fullname($user);
            $a->activity = strip_tags($criterion->get_title());
            $fulldescribe = get_string('progress-title', 'completion', $a);

            if ($csv) {
                $row[] = $a->date;
            } else {

                $output .= '<td class="completion-progresscell">';

                if ($allowmarkingcriteria === $criterion->id) {
                    $describe = get_string('completion-'.$completiontype, 'completion');

                    $toggleurl = new moodle_url(
                        '/course/togglecompletion.php',
                        array(
                            'user' => $user->id,
                            'course' => $course->id,
                            'rolec' => $allowmarkingcriteria,
                            'sesskey' => sesskey()
                        )
                    );

                    $output .= '<a href="'.$toggleurl->out().'" title="'.
                        s(get_string('clicktomarkusercomplete', 'report_completion')).'">' .
                        $OUTPUT->pix_icon('i/completion-manual-' . ($iscomplete ? 'y' : 'n'), $describe) . '</a></td>';
                } else {
                    $output .= $OUTPUT->pix_icon('i/' . $completionicon, $fulldescribe) . '</td>';
                }

                $output .= '</td>';
            }
        }

        // Handle overall course completion.

        // Load course completion.
        $params = array(
            'userid'    => $user->id,
            'course'    => $course->id
        );

        $ccompletion = new completion_completion($params);
        $completiontype = $ccompletion->is_complete() ? 'y' : 'n';

        $describe = get_string('completion-'.$completiontype, 'completion');

        $a = new StdClass;

        if ($ccompletion->is_complete()) {
            $a->date = userdate($ccompletion->timecompleted, get_string('strftimedatetimeshort', 'langconfig'));
        } else {
            $a->date = '';
        }

        $a->state    = $describe;
        $a->user     = fullname($user);
        $a->activity = strip_tags(get_string('coursecomplete', 'completion'));
        $fulldescribe = get_string('progress-title', 'completion', $a);

        if ($csv) {
            $row[] = $a->date;
        } else {

            $output .= '<td class="completion-progresscell">';

            // Display course completion status icon.
            $output .= $OUTPUT->pix_icon('i/completion-auto-' . $completiontype, $fulldescribe);

            $output .= '</td>';
        }

        if ($csv) {
            $export->add_data($row);
        } else {
            $output .= '</tr>';
        }
    }

    if ($csv) {
        $export->download_file();
    } else {
        $output .= '</tbody>';
    }

    $output .= '</table>';
    $output .= $pagingbar;

    $csvurl = new moodle_url('/report/completion/index.php', array('course' => $course->id, 'format' => 'csv'));
    $excelurl = new moodle_url('/report/completion/index.php', array('course' => $course->id, 'format' => 'excelcsv'));

    $output .= '<ul class="export-actions">';
    $output .= '<li><a href="'.$csvurl->out().'">'.get_string('csvdownload', 'completion').'</a></li>';
    $output .= '<li><a href="'.$excelurl->out().'">'.get_string('excelcsvdownload', 'completion').'</a></li>';
    $output .= '</ul>';

    // Trigger a report viewed event.
    $event = \report_completion\event\report_viewed::create(array('context' => $context));
    $event->trigger();

    return $output;

}

/**
 * Version details
 *
 * History:
 *
 * 10/31/18 - Initial writing.
 * 11/06/18 - Changed column names "shortnamecourse" to "coursecode".
 * 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery managers
 *                         and administrators; use groupid from curriculumarray.
 * 05/09/19 - In curriculums_print_table, removed image from link.
 *
 **/
function curriculums_print_table($coursesprogress, $curriculumarray) {

    $shortname = get_string('coursecode', 'local_swtc');
    $fullname = get_string('fullnamecourse');
    $progress = get_string('inprogress', 'block_myoverview');

    $table = new html_table();
    $table->head  = array($shortname, $fullname, $progress);
    $table->colclasses = array('mdl-left issue', 'mdl-left value', 'mdl-left comments', 'mdl-left config');
    $table->attributes = array('class' => 'admintable performancereport generaltable');
    $table->id = 'curriculums_print_table';
    $table->data  = array();

    foreach ($coursesprogress as $key => $courseid) {
        /* Build display row:
         * [0] - shortname
         * [1] - fullname
         * [2] - inprogress
         */
        // Display shortname.
        $row[0] = $curriculumarray[$key]['shortname'];

        // Display fullname.
        $fullname = $curriculumarray[$key]['fullname'];
        // To view the course, use this link.
        // $url = new moodle_url('/course/view.php', array('id' => $key));
        // SWTC ********************************************************************************.
        // PremierSupport managers and admins have special access.
        // Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery managers
        // and administrators; use groupid from curriculumarray.
        // SWTC ********************************************************************************.
        if (!empty($curriculumarray[$key]['groupid'])) {
            $params = array('curriculumid' => $key, 'group' => $curriculumarray[$key]['groupid']);
        } else {
            $params = array('curriculumid' => $key);
        }
        // To view the course completion report (if manager).
        $url = new moodle_url('/local/swtc/lib/curriculums.php', $params);
        $viewlink = "<a href='$url' alt='$fullname'>$fullname</a>";                             // 05/09/19
        $row[1] = $viewlink;

        // Display progress.
        $row[2] = $coursesprogress[$key]['progress'];

        $table->data[] = new html_table_row($row);

    }

    return html_writer::table($table);
}
