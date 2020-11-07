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
 * Upgrade scripts for course format "ebglmspractical"
 *
 * @package    format_topics
 * @copyright  2017 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package    format
 * @package   format_ebglmspractical
 * @copyright 2016 Lenovo EBG LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/05/18 - Initial writing; based on format_ebglmscustom version 2018083107.
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for format_ebglmspractical
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_format_ebglmspractical_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/course/format/ebglmspractical/db/upgradelib.php');

    if ($oldversion < 2017020200) {

        // Remove 'numsections' option and hide or delete orphaned sections.
        format_ebglmspractical_upgrade_remove_numsections();

        upgrade_plugin_savepoint(true, 2017020200, 'format', 'ebglmspractical');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.4.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2018030900) {

        // During upgrade to Moodle 3.3 it could happen that general section (section 0) became 'invisible'.
        // It should always be visible.
        $DB->execute("UPDATE {course_sections} SET visible=1 WHERE visible=0 AND section=0 AND course IN
        (SELECT id FROM {course} WHERE format=?)", ['ebglmspractical']);

        upgrade_plugin_savepoint(true, 2018030900, 'format', 'ebglmspractical');
    }

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
