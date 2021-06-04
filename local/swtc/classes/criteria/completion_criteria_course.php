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
 * SWTC customized code for Moodle course criteria type.
 *
 * @package    local
 * @subpackage get_usercompletion_criteria_course.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/14/21 - Initial writing.
 *
 */
namespace local_swtc\criteria;

use stdClass;
use context_course;
use core_text;

use \local_swtc\criteria\completion_criteria;

defined('MOODLE_INTERNAL') || die();

/**
 * Course completion critieria - completion on course completion
 *
 * This course completion criteria depends on another course with
 * completion enabled to be marked as complete for this user.
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_criteria_course extends \local_swtc\criteria\completion_criteria {
    
    /* @var int Criteria type constant */
    public $criteriatype = COMPLETION_CRITERIA_TYPE_COURSE;
    
    /**
     * Finds and returns a data_object instance based on params.
     *
     * @param array $params associative arrays varname=>value
     * @return data_object instance of data_object or false if none found.
     */
    public static function fetch($params) {
        $params['criteriatype'] = COMPLETION_CRITERIA_TYPE_COURSE;
        return self::fetch_helper('course_completion_criteria', __CLASS__, $params);
    }
    
    /**
     * Add appropriate form elements to the critieria form
     *
     * @param moodleform $mform  Moodle forms object
     * @param stdClass $data details of various modules
     *
     * SWTC history:
     *
     * 05/14/21 - Initial writing.
     *
     */
    public function config_form_display(&$mform, $data = null) {
        global $CFG;

        $link = "<a href=\"{$CFG->wwwroot}/course/view.php?id={$data->id}\">".s($data->fullname).'</a>';
        $mform->addElement('checkbox', 'criteria_course['.$data->id.']', $link);

        if ($this->id) {
            $mform->setDefault('criteria_course['.$data->id.']', 1);
        }
    }
    
    /**
     * Update the criteria information stored in the database
     *
     * @param stdClass $data Form data
     *
     * SWTC history:
     *
     * 05/14/21 - Initial writing.
     *
     */
    public function update_config(&$data) {
        global $DB;

        if (!empty($data->criteria_course) && is_array($data->criteria_course)) {

            $this->course = $data->id;

            foreach ($data->criteria_course as $course) {

                $this->courseinstance = $course;
                $this->id = NULL;
                $this->insert();
            }
        }
    }

    /**
     * Review this criteria and decide if the user has completed
     *
     * @param completion_completion $completion     The user's completion record
     * @param bool $mark Optionally set false to not save changes to database
     * @return bool
     *
     * SWTC history:
     *
     * 04/13/21 - Initial writing.
     *
     */
    public function review($completion, $mark = true) {
        global $DB;

        $course = $DB->get_record('course', array('id' => $this->courseinstance));
        $info = new completion_info_swtc($course);

        // If the course is complete.
        if ($info->is_course_complete($completion->userid)) {

            if ($mark) {
                $completion->mark_complete();
            }

            return true;
        }

        return false;
    }
    
    /**
     * Return criteria title for display in reports
     *
     * @return string
     */
    public function get_title() {
        return get_string('dependenciescompleted', 'completion');
    }

    /**
     * Return a more detailed criteria title for display in reports
     *
     * @return string
     */
    public function get_title_detailed() {
        global $DB;

        $prereq = $DB->get_record('course', array('id' => $this->courseinstance));
        $coursecontext = context_course::instance($prereq->id, MUST_EXIST);
        $fullname = format_string($prereq->fullname, true, array('context' => $coursecontext));
        return shorten_text(urldecode($fullname));
    }
    
    /**
     * Return criteria type title for display in reports
     *
     * @return string
     */
    public function get_type_title() {
        return get_string('dependencies', 'completion');
    }

    /**
     * Find users who have completed this criteria and mark them accordingly.
     *
     * History:
     *
     * 05/14/21 - Initial writing.
     *
     */
    public function cron() {
        global $DB;

        // Get all users who meet this criteria.
        $sql = '
            SELECT DISTINCT
                c.id AS course,
                cr.id AS criteriaid,
                ra.userid AS userid,
                mc.timemodified AS timecompleted
            FROM
                {course_completion_criteria} cr
            INNER JOIN
                {course} c
             ON cr.course = c.id
            INNER JOIN
                {context} con
             ON con.instanceid = c.id
            INNER JOIN
                {role_assignments} ra
              ON ra.contextid = con.id
            INNER JOIN
                {course_modules_completion} mc
             ON mc.coursemoduleid = cr.moduleinstance
            AND mc.userid = ra.userid
            LEFT JOIN
                {course_completion_crit_compl} cc
             ON cc.criteriaid = cr.id
            AND cc.userid = ra.userid
            WHERE
                cr.criteriatype = '.COMPLETION_CRITERIA_TYPE_ACTIVITY.'
            AND con.contextlevel = '.CONTEXT_COURSE.'
            AND c.enablecompletion = 1
            AND cc.id IS NULL
            AND (
                (cr.modulestatus = ' . self::STATUS_COMPLETED. '
                 AND (
                        mc.completionstate = ' . COMPLETION_COMPLETE . '
                     OR mc.completionstate = ' . COMPLETION_COMPLETE_PASS . '
                     OR mc.completionstate = ' . COMPLETION_COMPLETE_FAIL . '
                    )
                )
                OR
                (cr.modulestatus = ' . self::STATUS_COMPLETED_PASS . '
                AND (
                        mc.completionstate = ' . COMPLETION_COMPLETE .'
                     OR mc.completionstate = ' . COMPLETION_COMPLETE_PASS  .'
                    )
                )
            )
        ';

        // Loop through completions, and mark as complete.
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $record) {
            $completion = new completion_criteria_completion_swtc((array) $record, DATA_OBJECT_FETCH_BY_KEY);
            $completion->mark_complete($record->timecompleted);
        }
        $rs->close();
    }

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
    public function get_details($completion) {
        global $CFG, $DB;

        // Get completion info.
        $course = new stdClass();
        $course->id = $completion->course;

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
