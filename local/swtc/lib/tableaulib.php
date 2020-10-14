<?php
// declare(strict_types=1); // For debugging.
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
 * Version details
 *
 * @package    local
 * @subpackage swtc/lib/statslib.php
 * @copyright  2018 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 *		08/17/18 - Initial writing.
 *
 *****************************************************************************/
defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// Include globals (sets $EBGLMS).
// Lenovo ********************************************************************************
// require_once($CFG->dirroot.'/local/swtc/lib/swtc.php');                     // All EBGLMS global information.
// require_once($CFG->dirroot.'/local/swtc/lib/swtclib.php');                 // All EBGLMS library information.



/**
 * Count the activity records in a table where all the given conditions met.
 *
 * @param string $table The table to query.
 * @param string $timeframe Timeframe to set for query (choices are "alltime", "lastyear", "lastmonth", "lastweek", "yesterday", "today").
 * @return int The count of records returned from the specified criteria.
 * @throws dml_exception A DML specific exception is thrown for any errors.
 *
 * History:
 *
 *  08/17/18 - Initial writing.
 *
 */
function count_records_activity($current, $timeframe) {
	global $CFG, $DB, $SESSION;

    //****************************************************************************************
	// Local variables begin...

    // Local variables end...
	//****************************************************************************************
	// As of now, table is going to be "logstore_standard_log".

    // Set $conditions based on timeframe.
    switch ($timeframe) {
        case 'alltime':
            $where = "WHERE (c.id != 1)";
            break;

        case 'lastyear':
            $current_year = $current->format('o');
            $previous_year = $current_year - 1;
            // January 01 of last year
            $begin = strtotime("01 January $previous_year");
            // $begin_str = userdate(strtotime("01 January $previous_year"), '%m-%d-%Y');

            // December 31 of last year
            $end = strtotime("31 December $previous_year");
            // $end_str = userdate(strtotime("31 December $previous_year"), '%m-%d-%Y');
            //
            // AND (l.timecreated >= :start_date) AND (l.timecreated <= :end_date)
            // print_object("begin is :$begin_str end is :$end_str");
            $where = "WHERE (c.id != 1) AND (l.timecreated >= $begin) AND (l.timecreated <= $end)";
            break;

        case 'thisyear':
            $current_year = $current->format('o');
            // January 01 of this year
            $begin = strtotime("01 January $current_year");
            $begin_str = userdate(strtotime("01 January $current_year"), '%m-%d-%Y');

            // Now!
            $end = $current->format('U');
            $end_str = userdate($end, '%m-%d-%Y');
            // print_object("begin is :$begin_str end is :$end_str");

            $where = "WHERE (c.id != 1) AND (l.timecreated >= $begin) AND (l.timecreated <= $end)";
            break;

        case 'lastmonth':
            break;

        case 'lastweek':
            break;

        case 'yesterday':
            break;

        case 'today':
            break;

        default:
            // unknown type
    }

    // Setup SQL statement.
    $sql = "SELECT COUNT(c.id)
                FROM {logstore_standard_log} AS l
                JOIN {course} AS c ON (c.id = l.courseid)
                $where";

    // Setup where condition based on timefame passed in.
    // list($select, $params) = where_clause($table, $conditions);

    // print_object("sql is :$sql");

    $count = $DB->count_records_sql($sql);

    // print_object("count is :$count");

    return $count;

}

/**
 * Count the enrollment records in a table where all the given conditions met.
 *
 * @param string $table The table to query.
 * @param string $timeframe Timeframe to set for query (choices are "alltime", "lastyear", "lastmonth", "lastweek", "yesterday", "today").
 * @return int The count of records returned from the specified criteria.
 * @throws dml_exception A DML specific exception is thrown for any errors.
 *
 * History:
 *
 *  08/17/18 - Initial writing.
 *
 */
function count_records_enrollments($current, $timeframe) {
	global $CFG, $DB, $SESSION;

    //****************************************************************************************
	// Local variables begin...

    // Local variables end...
	//****************************************************************************************
	// WHERE c.id != 1 AND ((ue.timecreated >= :start_date) AND (ue.timecreated <= :end_date))

    // Set $conditions based on timeframe.
    switch ($timeframe) {
        case 'alltime':
            $where = "WHERE (c.id != 1)";
            break;

        case 'lastyear':
            $current_year = $current->format('o');
            $previous_year = $current_year - 1;
            // January 01 of last year
            $begin = strtotime("01 January $previous_year");
            // $begin_str = userdate(strtotime("01 January $previous_year"), '%m-%d-%Y');

            // December 31 of last year
            $end = strtotime("31 December $previous_year");
            // $end_str = userdate(strtotime("31 December $previous_year"), '%m-%d-%Y');
            //
            // AND (l.timecreated >= :start_date) AND (l.timecreated <= :end_date)
            // print_object("begin is :$begin_str end is :$end_str");
            $where = "WHERE (c.id != 1) AND (ue.timecreated >= $begin) AND (ue.timecreated <= $end)";
            break;

        case 'thisyear':
            $current_year = $current->format('o');
            // January 01 of this year
            $begin = strtotime("01 January $current_year");
            $begin_str = userdate(strtotime("01 January $current_year"), '%m-%d-%Y');

            // Now!
            $end = $current->format('U');
            $end_str = userdate($end, '%m-%d-%Y');
            // print_object("begin is :$begin_str end is :$end_str");

            $where = "WHERE (c.id != 1) AND (ue.timecreated >= $begin) AND (ue.timecreated <= $end)";
            break;

        case 'lastmonth':
            break;

        case 'lastweek':
            break;

        case 'yesterday':
            break;

        case 'today':
            break;

        default:
            // unknown type
    }

    // Setup SQL statement.
    $sql = "SELECT COUNT(ue.id)
                FROM {user_enrolments} AS ue
                LEFT OUTER JOIN {enrol} AS en ON (ue.enrolid = en.id)
                LEFT OUTER JOIN {course} AS c ON (c.id = en.courseid)
                $where";

    // Setup where condition based on timefame passed in.
    // list($select, $params) = where_clause($table, $conditions);

    // print_object("sql is :$sql");

    $count = $DB->count_records_sql($sql);

    // print_object("count is :$count");

    return $count;

}
