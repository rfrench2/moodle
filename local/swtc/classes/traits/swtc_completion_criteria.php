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
 * This file contains the course criteria type.
 *
 * Lenovo customized code for Moodle course criteria type. Remember to add the following at the top of any module that requires these functions:
 *      use \local_swtc\traits\lenovo_completion_criteria;
 * And put the following within the class that is being overridden:
 *      use lenovo_completion_criteria;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_completion_criteria.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 12/13/18 - In get_title_detailed, changed course fullname to course shortname so it will format better in reports.
 * 12/15/18 - In get_details, appended course shortname to course fullname so it will format better in reports.
 * 03/20/19 - Changing tooltips hyperlink from viewing course to viewing individual course completion report.
 *	10/15/19 - Initial writing; moved majority of customized code from completion/criteria/completion_criteria_course.php to
 *                      functions defined here; added utility functions; changed to new Lenovo EBGLMS classes and methods to load
 *                      swtc_user and debug; added get_title_detailed and get_details.
 * 10/23/19 - Added lenovo_get_title_detailed_course and lenovo_get_title_detailed_activity.
 * 10/28/19 - Added lenovo_get_details_course.
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

// require_once($CFG->libdir.'/completionlib.php');

use stdClass;
use context_course;
use context_module;
use moodle_url;
use completion_info;

/**
 * Course completion critieria - completion on course completion
 *
 * This course completion criteria depends on another course with
 * completion enabled to be marked as complete for this user
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// class lenovo_completion_criteria_course extends completion_criteria_course {
trait lenovo_completion_criteria {

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return string
     *
	 * Lenovo history:
	 *
	 * 12/13/18 - Changed course fullname to course shortname so it will format better in reports.
	 * 12/19/18 - Changed to returning array - course shortname followed by course fullname.
     * 03/20/19 - Changing tooltips hyperlink from viewing course to viewing individual course completion report. So, need to return
     *                  course id also. All of the following must be kept in sync:
     *                   /report/completion/index.php, /completion/criteria/completion_criteria_course.php, and
     *                  /local/swtc/lib/curriculumslib.php.
     *	10/23/19 - Moved majority of customized code to here from /completion/criteria/completion_criteria_course.php (including history).
	 *
     */
    public function lenovo_get_title_detailed_course($group) {
        global $DB;
        // print_object("in lenovo_get_title_detailed_course - Class: " . __CLASS__ . PHP_EOL);
        // print_object($this->courseinstance);
        $prereq = $DB->get_record('course', array('id' => $this->courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));
        $shortname = format_string($prereq->shortname, true, array('context' => $coursecontext));

        return array($this->courseinstance, shorten_text(urldecode($shortname)), urldecode($fullname));
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return  string
     *
	 * Lenovo history:
	 *
     *	10/23/19 - Moved majority of customized code to here from /completion/criteria/completion_criteria_activity.php (including history).
	 *
     */
    public function lenovo_get_title_detailed_activity() {
        global $DB;
        // print_object("in lenovo_get_title_detailed_activity - Class: " . __CLASS__ . PHP_EOL);
        // print_object($this->moduleinstance);
        $module = $DB->get_record('course_modules', array('id' => $this->moduleinstance));
        $activity = $DB->get_record($this->module, array('id' => $module->instance));

        return shorten_text(format_string($activity->name, true,
                array('context' => context_module::instance($module->id))));
    }

    /**
     * Return criteria progress details for display in reports
     *
     * @param completion_completion $completion The user's completion record
     * @return array An array with the following keys:
     *     type, criteria, requirement, status
     *
     * Lenovo history:
     *
     *	10/28/19 - Moved majority of customized code from here to /local/swtc/classes/traits/lenovo_completion_criteria_course.php (including history).
     *
     */
    public function lenovo_get_details_course($completion) {
        global $CFG, $DB;
        // print_object("in lenovo_get_details_course - Class: " . __CLASS__ . PHP_EOL);
        // print_object($this->courseinstance);

        // Get completion info
        $course = new stdClass();
        $course->id = $completion->course;
        $info = new completion_info($course);

        $prereq = $DB->get_record('course', array('id' => $this->courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));

        $fullname = $prereq->shortname .'  '. $fullname;		// Lenovo

        $prereq_info = new completion_info($prereq);

        $details = array();
        $details['type'] = $this->get_title();
        $details['criteria'] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$this->courseinstance.'">'.s($fullname).'</a>';
        $details['requirement'] = get_string('coursecompleted', 'completion');
        $details['status'] = '<a href="'.$CFG->wwwroot.'/blocks/completionstatus/details.php?course='.$this->courseinstance.'">'.get_string('seedetails', 'completion').'</a>';

        return $details;
    }

}
