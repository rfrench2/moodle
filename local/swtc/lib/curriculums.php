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
 * Create the "My Curriculums" page.
 *
 * @package    local_swtc
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/17/21 - Initial writing.
 *
 */
use core_completion\progress;

use \local_swtc\curriculums\curriculums;
use \local_swtc\grouplib\grouplib;
use \local_swtc\criteria\completion_info;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/completionlib.php');

require_once("$CFG->libdir/formslib.php");

// You will process some page parameters at the top here and get the info about
// what instance of your module and what course you're in etc. Make sure you
// include hidden variable in your forms which have their defaults set in set_data
// which pass these variables from page to page.

global $PAGE, $OUTPUT, $USER;

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/forms/curriculums_form.php');

// SWTC ********************************************************************************.
// SWTC swtcuser and debug variables.
$swtcuser = swtc_get_user([
    'userid' => $USER->id,
    'username' => $USER->username]);
$debug = swtc_get_debug();

// Other SWTC variables.
$accesstype = $swtcuser->get_accesstype();

$swtcgroups = new grouplib;
$curriculums = new curriculums;

$groups = null;
$mform = null;
$sort = null;
$enrolledcurriculums = array();
// SWTC ********************************************************************************.

if (isset($debug)) {
    $messages[] = "In /local/swtc/lib/curriculums.php ===1.enter===";
    $debug->logmessage($messages, 'both');
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
$enrolledcurriculums = $curriculums->getall_enrollments_for_user($USER->id);

if (has_capability('local/swtc:swtc_view_curriculums', context_system::instance())) {
    $context = context_system::instance();
} else if (!empty($enrolledcurriculums)) {
    // Just use the first one to get a context.
    $context = context_course::instance($enrolledcurriculums[0]);
}

// Set up page.
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/swtc/lib/curriculums.php'));
$PAGE->set_pagelayout('report');
$pagetitle = get_string('mycurriculums', 'local_swtc');
$PAGE->set_heading($pagetitle);
$PAGE->set_title($pagetitle);

// Always set the "start" navbar.
$pagenav = $PAGE->navbar->add($pagetitle, new moodle_url('/local/swtc/lib/curriculums.php'));

// To set proper context, login must be AFTER setting the system context.
require_login();

$tabs[] = new tabobject('allcurriculums', new moodle_url('/local/swtc/lib/curriculums.php', array()),
    get_string('allcurriculums', 'local_swtc'));

// Get all the courses marked as "curriculum courses".
$records = $curriculums->get_all_curriculums();

foreach ($records as $record) {
    $curriculumarray[$record->courseid]['fullname'] = $record->fullname;
    $curriculumarray[$record->courseid]['shortname'] = $record->shortname;
}

if (isset($debug)) {
    $messages[] = "About to print curriculum_array.";
    $messages[] = print_r($curriculumarray, true);
    $messages[] = "Finished printing curriculum_array.";
    $debug->logmessage($messages, 'detailed');
    unset($messages);
}

if (empty($CFG->navsortmycoursessort)) {
    $sort = 'visible DESC, sortorder ASC';
} else {
    $sort = 'visible DESC, '.$CFG->navsortmycoursessort.' ASC';
}

$courses = enrol_get_my_courses('*', $sort);
$coursesprogress = [];

foreach ($courses as $course) {
    $courseid = $course->id;

    // Only continue if it is a curriculum course.
    if (array_key_exists($courseid, $curriculumarray)) {
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
        $tabname = $curriculumarray[$course->id]['shortname'];
        $tabstring = $curriculumarray[$course->id]['fullname'];

        // SWTC ********************************************************************************.
        // Due to problems with contexts and user access, leaving has_capability checking
        // PremierSupport and ServiceDelivery manager and administrator user types.
        // IMPORTANT! WHERE condition assumes the correct naming of PremierSupport and ServiceDelivery
        // group names. If the naming convention changes, so should this code.
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        // Added 'group' parameter to URL that is built for each tab for PremierSupport
        // and ServiceDelivery managers and administrators; save groupid to curriculum_array for use later.
        // SWTC ********************************************************************************.
        if (has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance())) {
            // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
            if (($swtcuser->is_psmanagement()) || ($swtcuser->is_sdmanagement())) {
                // SWTC ********************************************************************************.
                // One common group setting (either one group or a group of groups).
                // SWTC ********************************************************************************.
                $groups = $swtcgroups->groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
                $sort = $swtcuser->get_groupsort();

                // Note: Should only be one returned.
                foreach ($groups as $group) {
                    if (preg_match($sort, $group->name)) {
                        $groupid = $group->id;
                        break;
                    } else {
                        $groupid = null;
                    }
                }

                if (isset($groupid)) {
                    $params = array('curriculumid' => $course->id, 'group' => $groupid);
                    $curriculumarray[$course->id]['groupid'] = $groupid;
                } else {
                    $params = array('curriculumid' => $course->id);
                    $curriculumarray[$course->id]['groupid'] = '';
                }
                // SWTC ********************************************************************************.
                // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
                // SWTC ********************************************************************************.
            } else {
                $params = array('curriculumid' => $course->id);
                $curriculumarray[$course->id]['groupid'] = '';
            }
        } else {
            $params = array('curriculumid' => $course->id);
            $curriculumarray[$course->id]['groupid'] = '';
        }

        // SWTC ********************************************************************************.
        // IMPORTANT! The following assumes the curriculum tab name to display is EXACTLY equal to the course fullname.
        // SWTC ********************************************************************************.
        $tabs[] = new tabobject($tabname, new moodle_url('/local/swtc/lib/curriculums.php', $params), $tabstring);
    } else {
        unset($courses[$course->id]);
    }
}

if (isset($debug)) {
    $messages[] = "About to print coursesprogress.";
    $messages[] = print_r($coursesprogress, true);
    $messages[] = "Finished printing coursesprogress.";
    $messages[] = "About to print curriculum_array.";
    $messages[] = print_r($curriculumarray, true);
    $messages[] = "Finished printing curriculum_array.";
    $debug->logmessage($messages, 'detailed');
    unset($messages);
}

// No curriculumid, so set the navbar to "My Curriculums". If curriculumid is set, navbar will be set later.
if (!$curriculumid) {
    $pagenav->add(get_string('allcurriculums', 'local_swtc'), new moodle_url('/local/swtc/lib/curriculums.php'));      // 05/14/19
} else {
    // If curriculumid is set, navbar will be set to "My Curriculums > curriculum-name".
    $pagenav->add($curriculumarray[$curriculumid]['shortname'], new moodle_url('/local/swtc/lib/curriculums.php'));
}

// SWTC ********************************************************************************.
// Moved output of header to here so I can put the correct in the navbar above.
// SWTC ********************************************************************************.
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
        echo $OUTPUT->tabtree($tabs, $curriculumarray[$curriculumid]['shortname']);

        // SWTC ********************************************************************************.
        // Any user (PremierSupport and ServiceDelivery managers and admins for now) have special access
        // if they have the required capability.
        // SWTC ********************************************************************************.
        if (has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance())) {
            // If true, then user is NOT a student; so show them the course completion report.
            // To view the course completion report (if manager).
            $data = $curriculums->report_completion_index($swtcuser, $curriculumid);
        } else if (has_capability('local/swtc:swtc_view_student_reports', $context)) {
            // If false, then user is a student; so show them the course completionstatus report.
            // To view the completionstatus report.
            $data = $curriculums->getcompletionstatus_details($curriculumid, $USER->id);
        }
    } else {
        // For tabs.
        echo $OUTPUT->tabtree($tabs, 'allcurriculums');

        $data = $curriculums->print_table($coursesprogress, $curriculumarray);
    }

    // Create the form and send all the data to it.
    $mform = new curriculums_form(null, array('data' => $data));
}

if (isset($mform)) {
    $mform->display();
}

if (empty($data)) {
    echo $OUTPUT->container(get_string('err_nousers', 'completion'), 'errorbox errorboxcontent');
}

echo $OUTPUT->footer();
