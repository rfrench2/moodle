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
 * Local_swtc
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	11/01/18 - Initial writing. Added local_swtc_userinvitation and local_swtc_userbookmarks.
 * 06/07/19 - Adding local_swtc_courses to hold featured courses (and possibly other course data).
 * 07/18/19 - Changed local_swtc_courses "courseids" to "courseid"; added fields "views", "enrollments", and "active".
 * 08/05/19 - Changed table name from "local_swtc_courses" to "local_swtc_sc".
 * 08/16/19 - Added "viewuserids" and "enrollmentuserids" to "local_swtc_rc" table; added
 *                  "local_swtc_rc_details" to track users interaction with all related courses throughout the site.
 * 08/22/19 - Changed table names to "local_swtc_sc"and "local_swtc_rc"; added "local_swtc_sc_details".
 *
 */

/**
 * @global moodle_database $DB
 * @param int $oldversion
 */
function xmldb_local_swtc_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Version is the version of the local_swtc plugin. Remember that production, sandbox, and debug sites may (will) have different versions.
    if ($oldversion < 2015121125) {

        // Define table local_swtc_userinvitation to be created.
        $table = new xmldb_table('local_swtc_userinvitation');

        // Adding fields to table local_swtc_userinvitation.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('email', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('tokenused', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timesent', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeexpiration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timeused', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('subject', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_swtc_userinvitation.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Adding indexes to table local_swtc_userinvitation.
        $table->add_index('token', XMLDB_INDEX_UNIQUE, array('token'));

        // Conditionally launch create table for local_swtc_userinvitation.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ebglms savepoint reached.
        upgrade_plugin_savepoint(true, 2015121125, 'local', 'swtc');
    }

    if ($oldversion < 2015121125) {

        // Define table local_swtc_userbookmarks to be created.
        $table = new xmldb_table('local_swtc_userbookmarks');

        // Adding fields to table local_swtc_userbookmarks.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_swtc_userbookmarks.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));

        // Adding indexes to table local_swtc_userbookmarks.
        $table->add_index('name', XMLDB_INDEX_UNIQUE, array('name'));

        // Conditionally launch create table for local_swtc_userbookmarks.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ebglms savepoint reached.
        upgrade_plugin_savepoint(true, 2015121125, 'local', 'swtc');
    }

	if ($oldversion < 2015121138) {

        // Define index name (unique) to be dropped form local_swtc_userbookmarks.
        $table = new xmldb_table('local_swtc_userbookmarks');
        $index = new xmldb_index('name', XMLDB_INDEX_UNIQUE, array('name'));

        // Conditionally launch drop index name.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

		// Define key userid_name (unique) to be added to local_swtc_userbookmarks.
        $key = new xmldb_key('userid_name', XMLDB_KEY_UNIQUE, array('userid', 'name'));

        // Launch add key userid_name.
        $dbman->add_key($table, $key);

        // Ebglms savepoint reached.
        upgrade_plugin_savepoint(true, 2015121138, 'local', 'swtc');
    }

    // Lenovo ********************************************************************************.
    // Define tables "local_swtc_sc", "local_swtc_rc", "local_swtc_rc_details", and "local_swtc_sc_details"
    // Lenovo ********************************************************************************.
    if ($oldversion < 2015121266) {

        // Lenovo ********************************************************************************.
        // Define table local_swtc_sc to be created.
        // Lenovo ********************************************************************************.
        $table = new xmldb_table('local_swtc_sc');

        // Adding fields to table local_swtc_sc.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('accesstype', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('suggestedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('clicks', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enrollments', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_swtc_sc.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_swtc_sc. // 06/07/19 - Not sure about this one.
        // $table->add_index('accesstype', XMLDB_INDEX_UNIQUE, array('accesstype'));

        // Conditionally launch create table for local_swtc_sc.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lenovo ********************************************************************************.
        // Define table local_swtc_sc_details to be created.
        // Lenovo ********************************************************************************.
        $table = new xmldb_table('local_swtc_sc_details');

        // Adding fields to table local_swtc_sc_details.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('parentcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('suggestedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dateclicked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dateenrolled', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding key to table local_swtc_sc_details.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_swtc_sc_details.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lenovo ********************************************************************************.
        // Define table local_swtc_rc to be created.
        // Lenovo ********************************************************************************.
        $table = new xmldb_table('local_swtc_rc');

        // Adding fields to table local_swtc_rc.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('active', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usercreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('parentcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('relatedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('clicks', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enrollments', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding key to table local_swtc_rc.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_swtc_rc.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Lenovo ********************************************************************************.
        // Define table local_swtc_rc_details to be created.
        // Lenovo ********************************************************************************.
        $table = new xmldb_table('local_swtc_rc_details');

        // Adding fields to table local_swtc_rc_details.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('parentcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('relatedcourseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dateclicked', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dateenrolled', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding key to table local_swtc_rc_details.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_swtc_rc_details.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ebglms savepoint reached.
        upgrade_plugin_savepoint(true, 2015121266, 'local', 'swtc');
    }

    return true;
}
