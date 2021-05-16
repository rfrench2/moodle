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
 * Completion data for a specific user, course and critieria
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_swtc\criteria;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/completion/data_object.php');

/**
 * Completion data for a specific user, course and critieria
 *
 * @package core_completion
 * @category completion
 * @copyright 2009 Catalyst IT Ltd
 * @author Aaron Barnes <aaronb@catalyst.net.nz>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_criteria_completion extends \completion_criteria_completion {

    /**
     * Constructs with course details.
     *
     * When instantiating a new completion info object you must provide a course
     * object with at least id, and enablecompletion properties. Property
     * cacherev is needed if you check completion of the current user since
     * it is used for cache validation.
     *
     * @param stdClass $course Moodle course object.
     */
    public function __construct($course) {
        parent::__construct($course);
        print_object("in local_swtc_criteria_completion_criteria_completion __construct");
        print_object($this);
    }


    /**
     * Return the associated criteria with this completion
     * If nothing attached, load from the db
     *
     * @return completion_criteria
     */
    public function get_criteria() {

        if (!$this->_criteria) {
            global $DB;

            $params = array(
                'id'    => $this->criteriaid
            );

            $record = $DB->get_record('course_completion_criteria', $params);

            $this->attach_criteria(completion_criteria::factory((array) $record));
        }

        return $this->_criteria;
    }
}
