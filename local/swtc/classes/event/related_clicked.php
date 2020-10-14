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

/*****************************************************************************
 * The local_swtc related clicked event.
 *
 * Version details
 *
 * @package    local
 * @subpackage swtc/classes/event/related_clicked.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	01/31/20 - Initial writing.
 *
 **/

namespace local_swtc\event;
defined('MOODLE_INTERNAL') || die();

/**
 * Event generated when a user clicks on a related course.
 *
 * @package    local
 * @subpackage swtc/classes/event/related_clicked.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class related_clicked extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_swtc_rc';
    }

    public static function get_name() {
        return get_string('related_clicked', 'local_swtc');
    }

    public function get_description() {
        return "User {$this->userid} has clicked the course with id {$this->objectid}.";
    }

    // public function get_url() {
    //     return new \moodle_url('/report/customsql/index.php');
    // }

    // public function get_legacy_logdata() {
    //     $url = '../report/customsql/index.php';
    //     return array(0, "report_customsql", 'delete query', $url, $this->objectid, $this->contextinstanceid);
    // }
}
