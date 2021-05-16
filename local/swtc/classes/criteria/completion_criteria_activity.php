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
 * This file contains the activity criteria type.
 *
 * SWTC customized code for Moodle activity criteria type.
 *
 * @package    local
 * @subpackage swtc_completion_criteria.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/14/21 - Initial writing.
 *
 **/

namespace local_swtc\criteria;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');


// use stdClass;
// use context_course;
// use context_module;
// use moodle_url;
// use completion_info;

/**
 * Course completion critieria - completion on activity completion
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_criteria_activity extends \completion_criteria_activity {

    /** Status requiring any type of completion. */
    const STATUS_COMPLETED = 1;
    /** Status requiring successful completion. */
    const STATUS_COMPLETED_PASS = 2;

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
        $modnames = get_module_types_names();
        $field = 'criteria_activity[' . $data->id . ']';
        $label = $modnames[self::get_mod_name($data->module)] . ' - ' . format_string($data->name);

        $choices = [];
        $choices[0] = '--';
        $choices[self::STATUS_COMPLETED] = get_string('activityiscompleted', 'core_completion');
        $choices[self::STATUS_COMPLETED_PASS] = get_string('activityissuccessfullycompleted', 'core_completion');

        $mform->addElement('select', $field, $label, $choices, ['group' => 1]);

        if ($this->id) {
            $mform->setDefault($field, $this->modulestatus);                        // Lenovo
        }
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

        // Loop through completions, and mark as complete
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $record) {
            $completion = new completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
            $completion->mark_complete($record->timecompleted);
        }
        $rs->close();
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

        if (!empty($data->criteria_activity) && is_array($data->criteria_activity)) {

            $this->course = $data->id;

            // Data comes from advcheckbox, so contains keys for all activities.
            // A value of 0 is 'not checked' whereas 1 is 'checked'.
            foreach ($data->criteria_activity as $activity => $val) {
                // Only update those which are checked.
                if (!empty($val)) {
                    $module = $DB->get_record('course_modules', array('id' => $activity));
                    $this->module = self::get_mod_name($module->module);
                    $this->moduleinstance = $activity;
                    $this->modulestatus = $val;
                    $this->id = null;
                    $this->insert();
                }
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

        $course = $DB->get_record('course', array('id' => $completion->course));
        $cm = $DB->get_record('course_modules', array('id' => $this->moduleinstance));
        $info = new swtc_completion_info($course);

        $data = $info->get_data($cm, false, $completion->userid);

        if ($this->modulestatus == self::STATUS_COMPLETED) {
            // Any status of completion is accepted.
            $statesaccepted = [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL];
        } else if ($this->modulestatus == self::STATUS_COMPLETED_PASS) {
            // Successful statuses of completion are accepted.
            $statesaccepted = [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS];
        }

        // If the activity is complete
        if (in_array($data->completionstate, array(COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL))) {
            if ($mark) {
                $completion->mark_complete();
            }

            return true;
        }

        return false;
    }
}
