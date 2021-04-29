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
 * SWTC customized code for Moodle course criteria type. Remember to add the following
 * at the top of any module that requires these functions:
 *      use \local_swtc\traits\swtc_completion_criteria;
 * And put the following within the class that is being overridden:
 *      use swtc_completion_criteria;
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc_completion_criteria.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/07/21 - Initial writing.
 *
 **/

namespace local_swtc\traits;

defined('MOODLE_INTERNAL') || die();


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
trait swtc_completion_criteria {

    /**
     * Return criteria progress details for display in reports
     *
     * @param completion_completion $completion The user's completion record
     * @return array An array with the following keys:
     *     type, criteria, requirement, status
     *
     * History:
     *
     * 04/07/21 - Initial writing.
     *
     */
    public function swtc_get_details_course($completion) {
        global $CFG, $DB;

        // Get completion info.
        $course = new stdClass();
        $course->id = $completion->course;
        $info = new completion_info($course);

        $prereq = $DB->get_record('course', array('id' => $this->courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));

        $fullname = $prereq->shortname .'  '. $fullname;

        $details = array();
        $details['type'] = $this->get_title();
        $details['criteria'] = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$this->courseinstance.'">'.s($fullname).'</a>';
        $details['requirement'] = get_string('coursecompleted', 'completion');
        $details['status'] = '<a href="'.$CFG->wwwroot.'/blocks/completionstatus/details.php?course='.$this->courseinstance.'">'.
            get_string('seedetails', 'completion').'</a>';

        return $details;
    }

}
