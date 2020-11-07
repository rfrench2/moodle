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
 * Change role access to all portfolios.
 *
 * @package    local
 * @subpackage swtc
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/04/20 - Initial writing.
 *
 */

namespace local_swtc;
use flexible_table;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class portfolio_access_table extends flexible_table {
    function definition() {
        parent::__construct('portfolioaccess');

    }

    public function get_cell_tooltip($role, $column) {
        $a = new stdClass;
        $a->rolename = $role;
        $a->portfolio = $column;
        return get_string('allowaccesstoportfolio', 'local_swtc', $a);
    }

}
