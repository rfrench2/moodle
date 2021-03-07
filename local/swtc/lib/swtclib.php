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
 * @subpackage swtc/lib/swtclib.php
 * @copyright  2020 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * History:
 *
 * 02/16/21 - Initial writing.
 *
 *
 *****************************************************************************/
defined('MOODLE_INTERNAL') || die();

// use \stdClass;

// SWTC ********************************************************************************
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');

// require_once($CFG->dirroot.'/cohort/lib.php');
// require_once($CFG->dirroot.'/user/profile/lib.php');


/**
 * Returns the entry from categories tree and makes sure the application-level tree cache is built
 *
 * The following keys can be requested:
 *
 * 'countall' - total number of categories in the system (always present)
 * 0 - array of ids of top-level categories (always present)
 * '0i' - array of ids of top-level categories that have visible=0 (always present but may be empty array)
 * $id (int) - array of ids of categories that are direct children of category with id $id. If
 *   category with id $id does not exist returns false. If category has no children returns empty array
 * $id.'i' - array of ids of children categories that have visible=0
 *
 * @param int|string $id
 * @return mixed
 */
function get_tree($id) {
	global $DB;
	$coursecattreecache = cache::make('core', 'coursecattree');
	$rv = $coursecattreecache->get($id);
	if ($rv !== false) {
		return $rv;
	}
	// Re-build the tree.
	$sql = "SELECT cc.id, cc.parent, cc.visible
			FROM {course_categories} cc
			ORDER BY cc.sortorder";
	$rs = $DB->get_recordset_sql($sql, array());
	$all = array(0 => array(), '0i' => array());
	$count = 0;
	foreach ($rs as $record) {
		$all[$record->id] = array();
		$all[$record->id. 'i']= array();
		if (array_key_exists($record->parent, $all)) {
			$all[$record->parent][] = $record->id;
			if (!$record->visible) {
				$all[$record->parent. 'i'][] = $record->id;
			}
		} else {
			// Parent not found. This is data consistency error but next fix_course_sortorder() should fix it.
			$all[0][] = $record->id;
			if (!$record->visible) {
				$all['0i'][] = $record->id;
			}
		}
		$count++;
	}
	$rs->close();
	if (!$count) {
		// No categories found.
		// This may happen after upgrade of a very old moodle version.
		// In new versions the default category is created on install.
		$defcoursecat = $self::create(array('name' => get_string('miscellaneous')));
		set_config('defaultrequestcategory', $defcoursecat->id);
		$all[0] = array($defcoursecat->id);
		$all[$defcoursecat->id] = array();
		$count++;
	}
	// We must add countall to all in case it was the requested ID.
	$all['countall'] = $count;
	foreach ($all as $key => $children) {
		$coursecattreecache->set($key, $children);
	}
	if (array_key_exists($id, $all)) {
		return $all[$id];
	}
	// Requested non-existing category.
	return array();
}
