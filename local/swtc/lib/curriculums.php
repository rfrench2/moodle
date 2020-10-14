<?php
// This file is part of the UCLA Site Invitation Plugin for Moodle - http://moodle.org/
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
 * Create the "My Curriculums" page.
 *
 * @package    local_swtc
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/01/18 - Initial writing.
 * 11/06/18 - Added module History section (this section); updated the description paragraphs with the new strings.
 * 11/15/18 - Added check for ServiceDelivery managers and administrators (specialaccess).
 * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
 *                      removed all three and changed to customized capabilities.
 * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
 *                      for Students are applied in the category context.
 * 12/18/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and ServiceDelivery
 *						manager and administrator user types; changing to checking "is_enrolled" for PS / SD student user types.
 * 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery managers
 *			 				and administrators.
 * 02/13/19 - Added PremierSupport and ServiceDelivery strings for "all participants"; changed sql queries and group searching
 *                      to use this new value.
 * 03/03/19 - Added PS/AD site administrator user access types.
 * 03/06/19 - Added PS/SD GEO administrator user access types.
 * 03/08/19 - Added geoname to keep the main GEO for user based on access type (ex. if access type is
 *                      Lenovo-ServiceDelivery-EMEA5-mgr, the GEO is EMEA).
 * 03/11/19 - Removed all "geositeadmin" strings (not needed).
 * 04/11/19 - Updated to return group if access type is Lenovo administrator or site administrator.
 * 05/13/19 - Set navbar to be either "My Curriculums" or "My Curriculums > name-of-curriculum" based on input parameters.
 * 05/18/19 - Updated if no group is returned (user is either Lenovo-admin or Lenovo-siteadmin).
 *	10/16/19 - Changed to new Lenovo EBGLMS classes and methods to load swtc_user and debug.
 * 11/11/19 - When calling curriculum_report_completion_index, added $swtc_user as parameter to function.
 * PTR2020Q109 - @01 - 05/07/20 - Added fix for breaking out of loop looking for group name.
 *
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/grouplib.php');

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot.'/local/swtc/forms/curriculums_form.php');
// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

global $SESSION, $PAGE, $OUTPUT, $USER;

use core_completion\progress;

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');         // Include curriculum utility functions.

// Lenovo ********************************************************************************.
// Lenovo EBGLMS swtc_user and debug variables.
$swtc_user = swtc_get_user($USER);
$debug = swtc_get_debug();

// Other Lenovo variables.
$user_access_type = $swtc_user->user_access_type;
$user_geoname = $SESSION->EBGLMS->USER->geoname;

// Remember - PremierSupport and ServiceDelivery managers and admins have special access.
$access_ps_mgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
$access_ps_admin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;
$access_ps_geoadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_geoadmin;
$access_ps_siteadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_siteadmin;

$access_lenovo_sd_mgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;
$access_lenovo_sd_admin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
$access_lenovo_sd_geoadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_geoadmin;
$access_lenovo_sd_siteadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_siteadmin;

$groups = null;
$mform = null;
$ebgsort = null;
$enrolled_curriculums = array();
// Lenovo ********************************************************************************.

if (isset($debug)) {
    $messages[] = "In /local/swtc/lib/curriculums.php ===1.enter===";
    debug_logmessage($messages, 'both');
    // print_object($swtc_user);
    unset($messages);
}

// Get parameters to page.
$curriculumid = optional_param('curriculumid', 0, PARAM_INT);
// If the user clicked the "View course XXX" button, we need to view the course.
$courseid = optional_param('id', 0, PARAM_INT);

// Redirect to the actual course.
if (!empty($courseid)) {
    $courseurl = new moodle_url("/course/view.php", array('id' => $courseid));
    redirect($courseurl);
}

// Look for key in catlist array.
// 12/04/18 - Change this; need to add ServiceDelivery and has_capability.
// 12/18/18 - Due to problems with contexts and user access, adding has_capability checking PremierSupport and
//							ServiceDelivery manager and administrator user types; changing to checking "is_enrolled" for
//							PS / SD student user types.
$enrolled_curriculums = curriculums_getall_enrollments_for_user($USER->id);
if (has_capability('local/swtc:ebg_view_curriculums', context_system::instance())) {
	$context = context_system::instance();
} else if (!empty($enrolled_curriculums)) {
	// Just use the first one to get a context.
	$context = context_course::instance($enrolled_curriculums[0]);
}

// Set up page.
// $context = context_system::instance();
// $context = context_user::instance($USER->id);
// $catid = $xcat['catid'];
// $context = context_coursecat::instance($catid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/swtc/lib/curriculums.php'));
$PAGE->set_pagelayout('report');     // 10/29/18 - RF - to research...
$pagetitle = get_string('mycurriculums', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

// 05/13/19 - Always set the "start" navbar.
$pagenav = $PAGE->navbar->add($pagetitle, new moodle_url('/local/swtc/lib/curriculums.php'));      // 05/13/19

// To set proper context, login must be AFTER setting the system context.
require_login();

$tabs[] = new tabobject('allcurriculums', new moodle_url('/local/swtc/lib/curriculums.php', array()), get_string('allcurriculums', 'local_swtc'));

// Get all the courses marked as "curriculum courses".
$records = curriculums_getall();

foreach ($records as $record) {
    $curriculum_array[$record->courseid]['fullname'] = $record->fullname;
    $curriculum_array[$record->courseid]['shortname'] = $record->shortname;
}

if (isset($debug)) {
    $messages[] = "About to print curriculum_array.";
    $messages[] = print_r($curriculum_array, true);
    $messages[] = "Finished printing curriculum_array.";
    // print_object($curriculum_array);
    debug_logmessage($messages, 'detailed');
    unset($messages);
}

if (empty($CFG->navsortmycoursessort)) {
    $sort = 'visible DESC, sortorder ASC';
} else {
    $sort = 'visible DESC, '.$CFG->navsortmycoursessort.' ASC';
}

$courses = enrol_get_my_courses('*', $sort);
// print_object(array_keys($courses));     // 05/13/20 - Lenovo debugging...
$coursesprogress = [];

foreach ($courses as $course) {
    $courseid = $course->id;

    // Only continue if it is a curriculum course.
    if (array_key_exists($courseid, $curriculum_array)) {

        $completion = new completion_info($course);

        // First, let's make sure completion is enabled.
        if (!$completion->is_enabled()) {
            continue;
        }

        $percentage = progress::get_course_progress_percentage($course);
        if (!is_null($percentage)) {
            $percentage = floor($percentage);
        }

        $coursesprogress[$course->id]['completed'] = $completion->is_course_complete($USER->id);
        $coursesprogress[$course->id]['progress'] = $percentage;

        // Print the curriculum tab.
        $tabname = $curriculum_array[$course->id]['shortname'];
        $tabstring = $curriculum_array[$course->id]['fullname'];

		// Lenovo ********************************************************************************
		// 12/27/18 - Due to problems with contexts and user access, leaving has_capability checking PremierSupport and
		//							ServiceDelivery manager and administrator user types.
		//		IMPORTANT! WHERE condition assumes the correct naming of PremierSupport and ServiceDelivery
		//						group names. If the naming convention changes, so should this code.
		// Lenovo ********************************************************************************
		// Lenovo ********************************************************************************
		// 12/28/18 - Added 'group' parameter to URL that is built for each tab for PremierSupport and ServiceDelivery managers
		//						and administrators; save groupid to curriculum_array for use later.
        // 03/03/19 - Added PS/AD site administrator user access types.
        // 03/06/19 - Added PS/SD GEO administrator user access types.
        // 03/08/19 - Added PS/SD GEO site administrator user access types.
		// Lenovo ********************************************************************************
		if (has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) {
             // Lenovo ********************************************************************************.
            // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
            // Lenovo ********************************************************************************.
			if ((preg_match($access_ps_mgr, $user_access_type)) || (preg_match($access_ps_admin, $user_access_type)) || (preg_match($access_ps_geoadmin, $user_access_type)) || (preg_match($access_ps_siteadmin, $user_access_type)) || (preg_match($access_lenovo_sd_mgr, $user_access_type)) || (preg_match($access_lenovo_sd_admin, $user_access_type)) || (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) || (preg_match($access_lenovo_sd_siteadmin, $user_access_type))) {
                // Lenovo ********************************************************************************.
                // One common group setting (either one group or a group of groups).
                // Lenovo ********************************************************************************.
                $groups = groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);     // 03/25/19

                // Lenovo ********************************************************************************.
                // PremierSupport site administrators
                // Lenovo ********************************************************************************.
                if (preg_match($access_ps_siteadmin, $user_access_type)) {
                    $ebgsort = get_string('cohort_premiersupport_pregmatch_siteadmins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // PremierSupport GEO administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_geoadmin, $user_access_type)) {
                    $ebgsort = get_string('cohort_premiersupport_pregmatch_geoadmins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // PremierSupport administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_admin, $user_access_type)) {
                    $ebgsort = get_string('cohort_premiersupport_pregmatch_admins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // PremierSupport managers
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_ps_mgr, $user_access_type)) {
                    $ebgsort = get_string('cohort_premiersupport_pregmatch_mgrs', 'local_swtc');
                // Lenovo ********************************************************************************.
                // ServiceDelivery site administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_siteadmin, $user_access_type)) {
                    $ebgsort = get_string('cohort_lenovo_servicedelivery_pregmatch_siteadmins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // ServiceDelivery GEO administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_geoadmin, $user_access_type)) {
                    $ebgsort = get_string('cohort_lenovo_servicedelivery_pregmatch_geoadmins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // ServiceDelivery administrators
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_admin, $user_access_type)) {
                    $ebgsort = get_string('cohort_lenovo_servicedelivery_pregmatch_admins', 'local_swtc');
                // Lenovo ********************************************************************************.
                // ServiceDelivery managers
                // Lenovo ********************************************************************************.
                } else if (preg_match($access_lenovo_sd_mgr, $user_access_type)) {
                    $ebgsort = get_string('cohort_lenovo_servicedelivery_pregmatch_mgrs', 'local_swtc');
                }

                // print_object("in curriculums - about to print groups.\n");
                // print_object($groups);      // Lenovo 01/30/19
                // print_object($ebgsort);      // Lenovo 01/30/19
                // Note: Should only be one returned.
                foreach($groups as $group) {
                    // print_object("groupname is :$group->name.\n");
                    // if (stripos($group->name, $ebgsort) !== false) {         // Lenovo 01/30/19...here...
                    if (preg_match($ebgsort, $group->name)) {
                        $groupid = $group->id;
                        // print_object("groupid is :$groupid.\n");     // Lenovo 01/30/19
                        // continue;       // @01 - 05/07/20 - Added fix for breaking out of loop looking for group name.
                        break;      // @01 - 05/07/20 - Added fix for breaking out of loop looking for group name.
                    } else {
                        $groupid = null;
                    }
                }

                // @01 - 05/07/20 - Added fix for breaking out of loop looking for group name.
                if (isset($groupid)) {
                    $params = array('curriculumid' => $course->id, 'group' => $groupid);
                    $curriculum_array[$course->id]['groupid'] = $groupid;
                } else {
                    $params = array('curriculumid' => $course->id);
                    $curriculum_array[$course->id]['groupid'] = '';
                }
            // Lenovo ********************************************************************************.
            // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
            // Lenovo ********************************************************************************.
            } else {
                // 04/11/19 - Updated to not return group if access type is Lenovo administrator or site administrator.
                $params = array('curriculumid' => $course->id);
                $curriculum_array[$course->id]['groupid'] = '';
            }
		} else {
			$params = array('curriculumid' => $course->id);
			$curriculum_array[$course->id]['groupid'] = '';
		}

        // Lenovo ********************************************************************************
        // IMPORTANT! The following assumes the curriculum tab name to display is EXACTLY equal to the course fullname.
        // Lenovo ********************************************************************************
        // $tabs[] = new tabobject($tabname, new moodle_url('/local/swtc/lib/curriculums.php', array('curriculumid' => $course->id)), $tabstring);
		$tabs[] = new tabobject($tabname, new moodle_url('/local/swtc/lib/curriculums.php', $params), $tabstring);
    } else { // Lenovo
        unset($courses[$course->id]); // Lenovo
    } // Lenovo
}
// print_object($swtc_user); // 03/11/10
if (isset($debug)) {
    $messages[] = "About to print coursesprogress.";
    $messages[] = print_r($coursesprogress, true);
    $messages[] = "Finished printing coursesprogress.";
    // print_object($coursesprogress);
    $messages[] = "About to print curriculum_array.";
    $messages[] = print_r($curriculum_array, true);
    $messages[] = "Finished printing curriculum_array.";
    // print_object($curriculum_array);        // 05/10/19
    debug_logmessage($messages, 'detailed');
    unset($messages);
}

// 05/13/19 - No curriculumid, so set the navbar to "My Curriculums". If curriculumid is set, navbar will be set later.
if (!$curriculumid) {
    $pagenav->add(get_string('allcurriculums', 'local_swtc'), new moodle_url('/local/swtc/lib/curriculums.php'));      // 05/14/19
} else {
    // 05/13/19 - If curriculumid is set, navbar will be set to "My Curriculums > curriculum-name".
    $pagenav->add($curriculum_array[$curriculumid]['shortname'], new moodle_url('/local/swtc/lib/curriculums.php'));      // 05/13/19
}

// Lenovo ********************************************************************************.
// 05/14/19 - Moved output of header to here so I can put the correct in the navbar above.
// Lenovo ********************************************************************************.
// OUTPUT form.
// Note: Must print header before queueing up other output.
echo $OUTPUT->header();

// Print out a heading.
echo $OUTPUT->heading($pagetitle, 2, 'headingblock');

// Print out the description paragraphs.
echo html_writer::tag('p', get_string('mycurriculums_desc1', 'local_swtc'));
echo html_writer::empty_tag('br');
echo html_writer::tag('p', get_string('mycurriculums_desc2', 'local_swtc'));
echo html_writer::empty_tag('br');
echo html_writer::tag('p', get_string('mycurriculums_desc3', 'local_swtc'));
echo html_writer::empty_tag('br');
echo html_writer::tag('p', get_string('mycurriculums_desc4', 'local_swtc'));
echo html_writer::empty_tag('br');

if (empty($coursesprogress)) {
    // If not enrolled in ANY curriculum courses, output a message.
    echo $OUTPUT->notification(get_string('errornotenrolledincurriculum', 'local_swtc'), 'notifymessage');
} else {
    if ($curriculumid) {
        // For tabs.
        // print_tabs(array($tabs), $curriculum_array[$curriculumid]['shortname']);
        echo $OUTPUT->tabtree($tabs, $curriculum_array[$curriculumid]['shortname']);

        // Lenovo ********************************************************************************
        // 11/28/18 - Any user (PremierSupport and ServiceDelivery managers and admins for now) have special access
        //                      if they have the required capability.
        // if (completion_can_view_data($USER->id, $curriculumid)) {
        // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
        //                          the capabilities for Students are applied in the category context.
        // 11/11/19 - When calling curriculum_report_completion_index, added $swtc_user as parameter to function.
        // Lenovo ********************************************************************************
        if (has_capability('local/swtc:ebg_view_mgmt_reports', context_system::instance())) {
            // If true, then user is NOT a student; so show them the course completion report.
            // To view the course completion report (if manager).
            // print_object($groupid); // 03/25/19
            // print_object($curriculum_array[$curriculumid]['groupid']);  // 03/25/19
            $data = curriculum_report_completion_index($swtc_user, $curriculumid, $curriculum_array[$curriculumid]['groupid']);     // 03/25/19
        } else if (has_capability('local/swtc:ebg_view_stud_reports', $context)) {
            // If false, then user is a student; so show them the course completionstatus report.
            // To view the completionstatus report.
            $data = curriculum_getcompletionstatus_details($curriculumid, $USER->id);
        }
    } else {
        // For tabs.
        // print_tabs(array($tabs), 'allcurriculums');
        echo $OUTPUT->tabtree($tabs, 'allcurriculums');

        $data = curriculums_print_table($coursesprogress, $curriculum_array);
    }

    // Create the form and send all the data to it.
    $mform = new curriculums_form(null, array('data' => $data));//name of the form you defined in file above.
}

// echo $output;
if (isset($mform)) {
    $mform->display();
}

if (empty($data)) {
    // echo $OUTPUT->notification(get_string('nothingtodisplay'));
    echo $OUTPUT->container(get_string('err_nousers', 'completion'), 'errorbox errorboxcontent');
}

echo $OUTPUT->footer();
