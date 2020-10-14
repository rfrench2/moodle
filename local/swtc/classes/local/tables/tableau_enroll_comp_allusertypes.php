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
 * Tableau report, table for showing enrollments / completions for all user types.
 * Version details
 *
 * @package    local
 * @subpackage lenovo_tableau_table.php
 * @copyright  2020 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	02/25/20 - Initial writing.
 *
 */

namespace local_swtc\local\tables;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

/**
 * This table shows enrollments / completions for all user types.
 *
 * @subpackage tableau_enroll_comp_allusertypes.php
 * @copyright  2020 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	02/25/20 - Initial writing.
 *
 */
class tableau_enroll_comp_allusertypes extends \flexible_table {
    /**
     * Construct the Tableau enrollments / completions table for all user types.
     */
    public function __construct() {
        parent::__construct('tableau-enroll-comp-allusertypes');

        $baseurl = new \moodle_url('/tool/usertours/configure.php');
        $this->define_baseurl($baseurl);

        // Column definition.
        $this->define_columns(array(
            'name',
            'description',
            'appliesto',
            'enabled',
            'actions',
        ));

        $this->define_headers(array(
            get_string('name', 'tool_usertours'),
            get_string('description', 'tool_usertours'),
            get_string('appliesto', 'tool_usertours'),
            get_string('enabled', 'tool_usertours'),
            get_string('actions', 'tool_usertours'),
        ));

        $this->set_attribute('class', 'admintable generaltable');
        $this->setup();

        $this->tourcount = helper::count_tours();
    }

    /**
     * Set up columns and column names and other table settings.
     *
     * @param moodle_url $reporturl
     * @param object     $questiondata
     * @param integer    $s             number of attempts on this question.
     * @param \core_question\statistics\responses\analysis_for_question $responseanalysis
     */
    public function question_setup($reporturl, $questiondata, $s, $responseanalysis) {
        $this->questiondata = $questiondata;
        $this->s = $s;

        $this->define_baseurl($reporturl->out());
        $this->collapsible(false);
        $this->set_attribute('class', 'generaltable generalbox boxaligncenter quizresponseanalysis');

        // Define the table columns.
        $columns = array();
        $headers = array();

        if ($responseanalysis->has_subparts()) {
            $columns[] = 'part';
            $headers[] = get_string('partofquestion', 'quiz_statistics');
        }

        if ($responseanalysis->has_multiple_response_classes()) {
            $columns[] = 'responseclass';
            $headers[] = get_string('modelresponse', 'quiz_statistics');

            if ($responseanalysis->has_actual_responses()) {
                $columns[] = 'response';
                $headers[] = get_string('actualresponse', 'quiz_statistics');
            }

        } else {
            $columns[] = 'response';
            $headers[] = get_string('response', 'quiz_statistics');
        }

        $columns[] = 'fraction';
        $headers[] = get_string('optiongrade', 'quiz_statistics');

        if (!$responseanalysis->has_multiple_tries_data()) {
            $columns[] = 'totalcount';
            $headers[] = get_string('count', 'quiz_statistics');
        } else {
            $countcolumns = range(1, $responseanalysis->get_maximum_tries());
            foreach ($countcolumns as $countcolumn) {
                $columns[] = 'trycount'.$countcolumn;
                $headers[] = get_string('counttryno', 'quiz_statistics', $countcolumn);
            }
        }

        $columns[] = 'frequency';
        $headers[] = get_string('frequency', 'quiz_statistics');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->sortable(false);

        $this->column_class('fraction', 'numcol');
        $this->column_class('count', 'numcol');
        $this->column_class('frequency', 'numcol');

        $this->column_suppress('part');
        $this->column_suppress('responseclass');

        parent::setup();
    }

    /**
     * Take a float where 1 represents 100% and return a string representing the percentage.
     *
     * @param float $fraction The fraction.
     * @return string The fraction as a percentage.
     */
    protected function format_percentage($fraction) {
        return format_float($fraction * 100, 2) . '%';
    }

    /**
     * The mark fraction that this response earns.
     * @param object $response containst the data to display.
     * @return string contents of this table cell.
     */
    protected function col_fraction($response) {
        if (is_null($response->fraction)) {
            return '';
        }

        return $this->format_percentage($response->fraction);
    }

    /**
     * The frequency with which this response was given.
     * @param object $response contains the data to display.
     * @return string contents of this table cell.
     */
    protected function col_frequency($response) {
        if (!$this->s) {
            return '';
        }
        return $this->format_percentage($response->totalcount / $this->s);
    }

    /**
     * If there is not a col_{column name} method then we call this method. If it returns null
     * that means just output the property as in the table raw data. If this returns none null
     * then this is the output for this cell of the table.
     *
     * @param string $colname  The name of this column.
     * @param object $response The raw data for this row.
     * @return string|null The value for this cell of the table or null means use raw data.
     */
    public function other_cols($colname, $response) {
        if (preg_match('/^trycount(\d+)$/', $colname, $matches)) {
            if (isset($response->trycount[$matches[1]])) {
                return $response->trycount[$matches[1]];
            } else {
                return 0;
            }
        } else if ($colname == 'part' || $colname == 'responseclass' || $colname == 'response') {
            return s($response->$colname);
        } else {
            return null;
        }
    }
}
