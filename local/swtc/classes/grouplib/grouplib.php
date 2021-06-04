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
 * SWTC customized code for groups. Remember to add the
 * following at the top of any module that requires these functions:
 * use \local_swtc\grouplib\grouplib;
 *
 * Version details
 *
 * @package    local
 * @subpackage /swtc/classes/grouplib/grouplib.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/19/21 - Initial writing.
 *
 **/
namespace local_swtc\grouplib;

defined('MOODLE_INTERNAL') || die();

use context_system;
use context_course;
use single_select;
use moodle_url;
use stdClass;

/**
 * SWTC group class.
 *
 *
 *
 * @package    local
 * @subpackage swtc/classes/grouplib/grouplib.php
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 04/19/21 - Initial writing.
 *
 **/
class grouplib {

    public function __construct() {

    }

    /**
     * Calls /lib/grouplib/get_all_groups first. Then formats the output.
     *
     * The following is copied from Moodle v3.10 version:
     *
     * Gets array of all groups in a specified course (subject to the conditions imposed by the other arguments).
     *
     * @category group
     * @param int $courseid The id of the course.
     * @param int|int[] $userid optional user id or array of ids, returns only groups continaing one or more of those users.
     * @param int $groupingid optional returns only groups in the specified grouping.
     * @param string $fields defaults to g.*. This allows you to vary which fields are returned.
     *      If $groupingid is specified, the groupings_groups table will be available with alias gg.
     *      If $userid is specified, the groups_members table will be available as gm.
     * @param bool $withmembers if true return an extra field members (int[]) which is the list of userids that
     *      are members of each group. For this to work, g.id (or g.*) must be included in $fields.
     *      In this case, the final results will always be an array indexed by group id.
     * @return array returns an array of the group objects (unless you have done something very weird
     *      with the $fields option).
     *
     * History:
     *
     * 04/20/21 - Initial writing.
     *
     */
    public function groups_get_all_groups($courseid, $userid=0, $groupingid=0, $fields='g.*', $withmembers=false) {
        global $DB, $USER;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $swtcwhere = null;
        $swtcsort = null;
        $accesstype = $swtcuser->get_accesstype();

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering local_swtc\classes\grouplib\grouplib.php.groups_get_all_groups.enter===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC customized functions associcated with Moodle /lib/grouplib.php.
        list($swtcwhere, $swtcsort) = self::set_where_conditions_by_groupname($swtcuser->get_groupname());

        // We need to check that we each field in the fields list belongs to the group table and that it has not being
        // aliased. If its something else we need to avoid the cache and run the query as who knows whats going on.
        $knownfields = true;
        if ($fields !== 'g.*') {
            // Quickly check if the first field is no longer g.id as using the
            // Cache will return an array indexed differently than when expect.
            if (strpos($fields, 'g.*') !== 0 && strpos($fields, 'g.id') !== 0) {
                $knownfields = false;
            } else {
                $fieldbits = explode(',', $fields);
                foreach ($fieldbits as $bit) {
                    $bit = trim($bit);
                    if (strpos($bit, 'g.') !== 0 || stripos($bit, ' AS ') !== false) {
                        $knownfields = false;
                        break;
                    }
                }
            }
        }

        if (empty($userid) && $knownfields && !$withmembers) {
            // We can use the cache.
            $data = groups_get_course_data($courseid);
            if (empty($groupingid)) {
                // All groups.. Easy!
                $groups = $data->groups;
            } else {
                $groups = array();
                foreach ($data->mappings as $mapping) {
                    if ($mapping->groupingid != $groupingid) {
                        continue;
                    }
                    if (isset($data->groups[$mapping->groupid])) {
                        $groups[$mapping->groupid] = $data->groups[$mapping->groupid];
                    }
                }
            }

            // Yay! We could use the cache. One more query saved.
            // SWTC ********************************************************************************.
            // Two code paths are available (this is one of them). Once all groups are returned via the
            // groups_get_course_data call, filter out the groups that the PremierSupport or ServiceDelivery
            // managers or administrators shouldn't see.
            // SWTC ********************************************************************************.
            // Notes:
            // If a userid is passed in, $groups is used to fill the Separate groups pull-down menu
            // (all the groups the user has access to). sort (set above) is used to parse the groups.
            // SWTC ********************************************************************************.
            $temp = array();
            if (($swtcuser->is_psmanagement()) || ($swtcuser->is_sdmanagement())) {
                foreach ($groups as $group) {
                    if (preg_match($swtcsort, $group->name)) {
                        // SWTC ********************************************************************************.
                        // Special situation - Both a PS/SD admin and mgr will use the same $swtcsort string ("PS-US1" or "SD-US1").
                        // Therefore, the same groups will be returned for both access types. However, a manager access type should
                        // not have access to the admin group. So, if the current user is a PS/SD manager, remove the admin group.
                        // SWTC ********************************************************************************.
                        if ((preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))
                            || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))) {
                            if (stripos($group->name, '-admins-') !== false) {
                                // If the group name matches '-admins-', remove it (i.e. don't do anything).
                            } else {
                                $temp[$group->id] = $group;
                            }
                        } else {
                            $temp[$group->id] = $group;
                        }
                    }
                }
                // Reload groups with temp.
                $groups = $temp;

                // SWTC ********************************************************************************.
                // No sorting of returned groups for Lenovo-admins or Lenovo-siteadmins.
                // SWTC ********************************************************************************.
            } else if ((preg_match(get_string('access_lenovo_pregmatch_siteadmin', 'local_swtc'), $accesstype))
                || (preg_match(get_string('access_lenovo_pregmatch_admin', 'local_swtc'), $accesstype))) {
                // Just returning $groups.
            }

            if (isset($debug)) {
                // SWTC ********************************************************************************.
                // Always output standard header information.
                // SWTC ********************************************************************************.
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Leaving local_swtc\classes\grouplib\grouplib.php.groups_get_all_groups.exit (upper1)===.";
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "sort is :$swtcsort. About to print groups.";
                $messages[] = print_r($groups, true);
                $messages[] = "Finished printing groups. About to print swtcuser->groupnames.";
                $messages[] = print_r($swtcuser->get_groupnames(), true);
                $messages[] = "Finished printing swtcuser->groupnames.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            return $groups;

        }

        $params = [];
        $userfrom  = '';
        $userwhere = '';
        if (!empty($userid)) {
            list($usql, $params) = $DB->get_in_or_equal($userid);
            $userfrom  = "JOIN {groups_members} gm ON gm.groupid = g.id";
            $userwhere = "AND gm.userid $usql";
        }

        $groupingfrom  = '';
        $groupingwhere = '';
        if (!empty($groupingid)) {
            $groupingfrom  = "JOIN {groupings_groups} gg ON gg.groupid = g.id";
            $groupingwhere = "AND gg.groupingid = ?";
            $params[] = $groupingid;
        }

        // SWTC ********************************************************************************.
        // Add where condition.
        // SWTC ********************************************************************************.
        if (isset($swtcwhere) && !empty($groupingwhere)) {
            $groupingwhere .= $swtcwhere;
        }

        array_unshift($params, $courseid);

        $results = $DB->get_records_sql("
                    SELECT $fields
                      FROM {groups} g
                      $userfrom
                      $groupingfrom
                     WHERE g.courseid = ?
                       $userwhere
                       $groupingwhere
                  ORDER BY g.name ASC", $params);

        if (!$withmembers) {
            if (isset($debug)) {
                // SWTC ********************************************************************************.
                // Always output standard header information.
                // SWTC ********************************************************************************.
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $messages[] = "Leaving local_swtc\classes\grouplib\grouplib.php.groups_get_all_groups.exit (upper2)===.";
                $messages[] = "withmembers is empty.";
                $messages[] = get_string('swtc_debug', 'local_swtc');
                $debug->logmessage($messages, 'both');
                unset($messages);
            }

            return $results;
        }

        // We also want group members. We do this in a separate query, becuse the above
        // query will return a lot of data (e.g. g.description) for each group, and
        // some groups may contain hundreds of members. We don't want the results
        // to contain hundreds of copies of long descriptions.
        $groups = [];
        foreach ($results as $row) {
            $groups[$row->id] = $row;
            $groups[$row->id]->members = [];
        }
        $groupmembers = $DB->get_records_list('groups_members', 'groupid', array_keys($groups));
        foreach ($groupmembers as $gm) {
            $groups[$gm->groupid]->members[$gm->userid] = $gm->userid;
        }

        $results = $groups;

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Leaving local_swtc\classes\grouplib\grouplib.php.groups_get_all_groups.exit (lower)===.";
            $messages[] = $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);

            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = "groups_get_all_groups==3==.";
            $tmp = "SELECT $fields FROM {groups} g $userfrom $groupingfrom
                WHERE g.courseid = ? $userwhere $groupingwhere
                ORDER BY g.name ASC";
            $messages[] = print_r($tmp, true);
            $messages[] = "groups_get_all_groups==4==: params follow :";
            $messages[] = print_r($params, true);
            $messages[] = "groups_get_all_groups==5==: results follow :";
            $messages[] = print_r($results, true);
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        return $results;

    }

    /**
     * Print group menu selector for course level.
     *
     * The following is copied from Moodle v3.10 version:
     *
     * @category group
     * @param stdClass $course course object
     * @param mixed $urlroot return address. Accepts either a string or a moodle_url
     * @param bool $return return as string instead of printing
     * @return mixed void or string depending on $return param
     *
     * History:
     *
     * 04/20/21 - Initial writing.
     *
     */
    public function groups_print_course_menu($course, $urlroot, $return=false) {
        global $USER, $OUTPUT;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $accesstype = $swtcuser->get_accesstype();
        $groupname = $swtcuser->get_groupname();
        $messageparams = new stdClass;
        $messageparams->groupname = $groupname;

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering local_swtc\classes\grouplib\grouplib.php.groups_print_course_menu.enter===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        if (!$groupmode = $course->groupmode) {
            if ($return) {
                return '';
            } else {
                return;
            }
        }

        $context = context_course::instance($course->id);
        $aag = has_capability('moodle/site:accessallgroups', $context);

        $usergroups = array();
        if ($groupmode == VISIBLEGROUPS || $aag) {
            $allowedgroups = self::groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
            // Get user's own groups and put to the top.
            $usergroups = self::groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        } else {
            $allowedgroups = self::groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
        }

        $activegroup = self::groups_get_course_group($course, true, $allowedgroups);

        $groupsmenu = array();
        if (!$allowedgroups || $groupmode == VISIBLEGROUPS || $aag) {
            // SWTC ********************************************************************************.
            // IMPORTANT! The following code assumes the following:
            // For PS/SD manager access types (ex: PS-US1-manager):
            // $groupsmenu[0] will set to "All PremierSupport US1 enrollments".
            //
            // For PS/SD administrator access types (ex: PS-US1-administrator):
            // $groupsmenu[0] will be set to "All PremierSupport US enrollments".
            // SWTC ********************************************************************************.
            if (($swtcuser->is_psmanagement()) || ($swtcuser->is_sdmanagement())) {
                // SWTC ********************************************************************************.
                // PremierSupport site administrators
                // SWTC ********************************************************************************.
                if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                    $groupsmenu[0] = get_string('groups_premiersupport_all_participants', 'local_swtc', $messageparams);
                    $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups, $accesstype);
                // SWTC ********************************************************************************.
                // PremierSupport GEO administrators
                // PremierSupport administrators
                // PremierSupport managers
                // SWTC ********************************************************************************.
                } else if ((preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
                || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
                || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))) {
                    $groupsmenu[0] = get_string('groups_premiersupport_all_geo_participants', 'local_swtc', $messageparams);
                    $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups, $accesstype);
                // SWTC ********************************************************************************.
                // ServiceDelivery site administrators
                // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                    $groupsmenu[0] = get_string('groups_lenovo_servicedelivery_all_participants', 'local_swtc', $messageparams);
                    $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups, $accesstype);
                    // SWTC ********************************************************************************.
                    // ServiceDelivery GEO administrators
                    // ServiceDelivery administrators
                    // ServiceDelivery managers
                    // SWTC ********************************************************************************.
                } else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))
                    || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))
                    || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))) {
                    $groupsmenu[0] = get_string('groups_lenovo_servicedelivery_all_geo_participants', 'local_swtc',
                        $messageparams);
                    $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups, $accesstype);
                }
                // SWTC ********************************************************************************.
                // Lenovo administrators
                // SWTC ********************************************************************************.
            } else {
                $groupsmenu[0] = get_string('allparticipants');
                $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups);
            }
        }

        // SWTC ********************************************************************************.
        // Moving call to self::groups_sort_menu_options above so that a customized version can be used
        // for PS/SD site administrators.
        // $groupsmenu += self::groups_sort_menu_options($allowedgroups, $usergroups);    // SWTC
        // SWTC ********************************************************************************.

        if ($groupmode == VISIBLEGROUPS) {
            $grouplabel = get_string('groupsvisible');
        } else {
            $grouplabel = get_string('groupsseparate');
        }

        if ($aag && $course->defaultgroupingid) {
            if ($grouping = groups_get_grouping($course->defaultgroupingid)) {
                $grouplabel = $grouplabel . ' (' . format_string($grouping->name) . ')';
            }
        }

        if (count($groupsmenu) == 1) {
            $groupname = reset($groupsmenu);
            $output = $grouplabel.': '.$groupname;
        } else {
            $select = new single_select(new moodle_url($urlroot), 'group', $groupsmenu, $activegroup, null, 'selectgroup');
            $select->label = $grouplabel;
            $output = $OUTPUT->render($select);
        }

        $output = '<div class="groupselector">'.$output.'</div>';

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Leaving local_swtc\classes\grouplib\grouplib.php.groups_print_course_menu.exit===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);

            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "About to print groupsmenu.";
            $messages[] = print_r($groupsmenu, true);
            $messages[] = "Finished printing groupsmenu.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * Returns group active in course, changes the group by default if 'group' page param present
     *
     * The following is copied from Moodle v3.10 version:
     *
     * @category group
     * @param stdClass $course course bject
     * @param bool $update change active group if group param submitted
     * @param array $allowedgroups list of groups user may access (INTERNAL, to be used only from groups_print_course_menu())
     * @return mixed false if groups not used, int if groups used, 0 means all groups (access must be verified in SEPARATE mode)
     *
     * History:
     *
     * 04/21/21 - Initial writing.
     *
     */
    public function groups_get_course_group($course, $update=false, $allowedgroups=null) {
        global $USER, $SESSION;

        if (!$groupmode = $course->groupmode) {
            // NOGROUPS used.
            return false;
        }

        $context = context_course::instance($course->id);
        if (has_capability('moodle/site:accessallgroups', $context)) {
            $groupmode = 'aag';
        }

        if (!is_array($allowedgroups)) {
            if ($groupmode == VISIBLEGROUPS || $groupmode === 'aag') {
                $allowedgroups = self::groups_get_all_groups($course->id, 0, $course->defaultgroupingid);
            } else {
                $allowedgroups = self::groups_get_all_groups($course->id, $USER->id, $course->defaultgroupingid);
            }
        }

        _group_verify_activegroup($course->id, $groupmode, $course->defaultgroupingid, $allowedgroups);

        // Set new active group if requested.
        $changegroup = optional_param('group', -1, PARAM_INT);
        if ($update && $changegroup != -1) {

            if ($changegroup == 0) {
                // Do not allow changing to all groups without accessallgroups capability.
                if ($groupmode == VISIBLEGROUPS || $groupmode === 'aag') {
                    $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = 0;
                }

            } else {
                if ($allowedgroups && array_key_exists($changegroup, $allowedgroups)) {
                    $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = $changegroup;
                } else {
                    // SWTC ********************************************************************************.
                    // This *might* be all that is needed to show the custom menu item.
                    // SWTC ********************************************************************************.
                    $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid] = $changegroup;
                }
            }
        }

        return $SESSION->activegroup[$course->id][$groupmode][$course->defaultgroupingid];
    }

    /**
     * Takes user's allowed groups and own groups and formats for use in group selector menu
     * If user has allowed groups + own groups will add to an optgroup
     * Own groups are removed from allowed groups.
     *
     * IMPORTANT! Sets swtcuser->groupnames.
     *
     * The following is copied from Moodle v3.10 version:
     *
     * @param array $allowedgroups All groups user is allowed to see
     * @param array $usergroups Groups user belongs to
     * @return array
     *
     * History:
     *
     * 04/21/21 - Initial writing.
     *
     */
    public function groups_sort_menu_options($allowedgroups, $usergroups) {
        global $USER;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $accesstype = $swtcuser->get_accesstype();
        $groupname = $swtcuser->get_groupname();
        $messageparams = new stdClass;
        $messageparams->groupname = $groupname;

        // Hold the temporary "dummy" group id to display.
        $uuid = null;

        // The following pattern will match "<whatever>-US1-<whatever> or "<whatever>-EM5-<whatever>".
        $cmpstudsstring = null;
        $cmpmgrsstring = null;
        $cmpadminsstring = null;
        $cmpgeoadminsstring = null;
        $cmpsiteadminsstring = null;

        // Customized menu items.
        $studsmenu = null;
        $studsmenuitem = null;
        $mgrsmenu = null;
        $mgrsmenuitem = null;
        $adminsmenu = null;
        $adminsmenuitem = null;
        $geoadminsmenu = null;
        $geoadminsmenuitem = null;
        $siteadminsmenu = null;
        $siteadminsmenuitem = null;

        // Literal strings.
        $student = 'student';
        $manager = 'manager';
        $administrator = 'administrator';
        $geoadministrator = 'GEO administrator';

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering local_swtc\classes\grouplib\grouplib.php.groups_sort_menu_options.enter===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        $useroptions = array();
        if ($usergroups) {
            $useroptions = groups_list_to_menu($usergroups);

            // Remove user groups from other groups list.
            foreach ($usergroups as $group) {
                unset($allowedgroups[$group->id]);
            }
        }

        $allowedoptions = array();
        if ($allowedgroups) {
            $allowedoptions = groups_list_to_menu($allowedgroups);
        }

        // SWTC ********************************************************************************.
        // Setup all variables needed based on PS/AD access types (excluding manager and student).
        // Added important note that it is only to be called for PS/SD administrator user types and above
        // (i.e. NOT for PS/SD students or managers).
        // SWTC ********************************************************************************.
        // SWTC ********************************************************************************.
        // PremierSupport access type.
        // SWTC ********************************************************************************.
        if ((preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Common strings for all PremierSupport access types.
            // SWTC ********************************************************************************.
            $cmpstudsstring = get_string('cohort_premiersupport_pregmatch_studs', 'local_swtc');
            $cmpmgrsstring = get_string('cohort_premiersupport_pregmatch_mgrs', 'local_swtc');
            $cmpadminsstring = get_string('cohort_premiersupport_pregmatch_admins', 'local_swtc');
            $cmpgeoadminsstring = get_string('cohort_premiersupport_pregmatch_geoadmins', 'local_swtc');
            $cmpsiteadminsstring = get_string('cohort_premiersupport_pregmatch_siteadmins', 'local_swtc');

            // SWTC ********************************************************************************.
            // PremierSupport site administrators
            // SWTC ********************************************************************************.
            if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_premiersupport_all_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $geoadministrator;
                $geoadminsmenuitem = get_string('groups_premiersupport_all_type_participants', 'local_swtc', $messageparams);
                // SWTC ********************************************************************************.
                // PremierSupport GEO administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_premiersupport_all_geo_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $geoadministrator;
                $geoadminsmenuitem = get_string('groups_premiersupport_geo_type_participants', 'local_swtc', $messageparams);
                // SWTC ********************************************************************************.
                // PremierSupport administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_premiersupport_group_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $messageparams);
                // SWTC ********************************************************************************.
                // PremierSupport managers
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_premiersupport_group_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_premiersupport_group_type_participants', 'local_swtc', $messageparams);
            }
            // SWTC ********************************************************************************.
            // ServiceDelivery access type.
            // SWTC ********************************************************************************.
        } else if ((preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype))
        || (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype))) {
            // SWTC ********************************************************************************.
            // Common strings for all ServiceDelivery access types.
            // SWTC ********************************************************************************.
            $cmpstudsstring = get_string('cohort_lenovo_servicedelivery_pregmatch_studs', 'local_swtc');
            $cmpmgrsstring = get_string('cohort_lenovo_servicedelivery_pregmatch_mgrs', 'local_swtc');
            $cmpadminsstring = get_string('cohort_lenovo_servicedelivery_pregmatch_admins', 'local_swtc');
            $cmpgeoadminsstring = get_string('cohort_lenovo_servicedelivery_pregmatch_geoadmins', 'local_swtc');
            $cmpsiteadminsstring = get_string('cohort_lenovo_servicedelivery_pregmatch_siteadmins', 'local_swtc');

            // SWTC ********************************************************************************.
            // ServiceDelivery site administrators
            // SWTC ********************************************************************************.
            if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_lenovo_servicedelivery_all_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $geoadministrator;
                $geoadminsmenuitem = get_string('groups_lenovo_servicedelivery_all_type_participants', 'local_swtc',
                    $messageparams);
                // SWTC ********************************************************************************.
                // ServiceDelivery GEO administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_lenovo_servicedelivery_all_geo_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $geoadministrator;
                $geoadminsmenuitem = get_string('groups_lenovo_servicedelivery_geo_type_participants', 'local_swtc',
                    $messageparams);
                // SWTC ********************************************************************************.
                // ServiceDelivery administrators
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_lenovo_servicedelivery_group_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $administrator;
                $adminsmenuitem = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $messageparams);
                // SWTC ********************************************************************************.
                // ServiceDelivery managers
                // SWTC ********************************************************************************.
            } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                $groupsmenu = get_string('groups_lenovo_servicedelivery_group_participants', 'local_swtc', $messageparams);

                // The following 6 lines WORK!
                $messageparams->type = $student;
                $studsmenuitem = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $messageparams);
                $messageparams->type = $manager;
                $mgrsmenuitem = get_string('groups_lenovo_servicedelivery_group_type_participants', 'local_swtc', $messageparams);
            }
        }

        // SWTC ********************************************************************************.
        // List all the groups that would be included in the following top-level groups:
        // If site administrator, <PS/SD>-<GEO>%-studs%, <PS/SD>-<GEO>%-mgrs%, and <PS/SD>-<GEO>%-admins%.
        // If administrator, <PS/SD>-<GEO><1-9>%-studs%, <PS/SD>-<GEO><1-9>%-mgrs%, and <PS/SD>-<GEO><1-9>%-admins%.
        //
        // Group from allowed groups.
        // SWTC ********************************************************************************.
        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                // SWTC ********************************************************************************.
                // Is it a groups of students?
                // SWTC ********************************************************************************.
                if (preg_match($cmpstudsstring, $group->name)) {
                    $studsmenu[$group->id] = $group->id;
                    // SWTC ********************************************************************************.
                    // Is it a groups of managers?
                    // SWTC ********************************************************************************.
                } else if (preg_match($cmpmgrsstring, $group->name)) {
                    $mgrsmenu[$group->id] = $group->id;
                    // SWTC ********************************************************************************.
                    // Is it a groups of administrators?
                    // SWTC ********************************************************************************.
                } else if (preg_match($cmpadminsstring, $group->name)) {
                    $adminsmenu[$group->id] = $group->id;
                    // SWTC ********************************************************************************.
                    // Is it a groups of GEO administrators?
                    // SWTC ********************************************************************************.
                } else if (preg_match($cmpgeoadminsstring, $group->name)) {
                    $geoadminsmenu[$group->id] = $group->id;
                    // SWTC ********************************************************************************.
                    // Is it a groups of site administrators?
                    // SWTC ********************************************************************************.
                } else if (preg_match($cmpsiteadminsstring, $group->name)) {
                    $siteadminsmenu[$group->id] = $group->id;
                }
            }
        } else {
            if ($usergroups) {
                foreach ($usergroups as $group) {
                    // SWTC ********************************************************************************.
                    // Is it a groups of students?
                    // SWTC ********************************************************************************.
                    if (preg_match($cmpstudsstring, $group->name)) {
                        $studsmenu[$group->id] = $group->id;
                        // SWTC ********************************************************************************.
                        // Is it a groups of managers?
                        // SWTC ********************************************************************************.
                    } else if (preg_match($cmpmgrsstring, $group->name)) {
                        $mgrsmenu[$group->id] = $group->id;
                        // SWTC ********************************************************************************.
                        // Is it a groups of administrators?
                        // SWTC ********************************************************************************.
                    } else if (preg_match($cmpadminsstring, $group->name)) {
                        $adminsmenu[$group->id] = $group->id;
                        // SWTC ********************************************************************************.
                        // Is it a groups of GEO administrators?
                        // SWTC ********************************************************************************.
                    } else if (preg_match($cmpgeoadminsstring, $group->name)) {
                        $geoadminsmenu[$group->id] = $group->id;
                        // SWTC ********************************************************************************.
                        // Is it a groups of site administrators?
                        // SWTC ********************************************************************************.
                    } else if (preg_match($cmpsiteadminsstring, $group->name)) {
                        $siteadminsmenu[$group->id] = $group->id;
                    }
                }
            }
        }

        // SWTC ********************************************************************************.
        // Link menu items and groups to display.
        // SWTC ********************************************************************************.
        // Students menu.
        // SWTC ********************************************************************************.
        if (!empty($studsmenu)) {
            $submenuitem = 'studs_menu';
            if (empty($swtcuser->get_groupnames($submenuitem))) {
                $uuid = rand();
                $temp[$submenuitem][$uuid]['uuid'] = $uuid;
                $temp[$submenuitem][$uuid]['groups'] = implode(', ', $studsmenu);
                $swtcuser->set_groupnames($temp);
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach ($swtcuser->get_groupnames($submenuitem) as $key => $value) {
                    $uuid = $key;
                }
            }
            ${$groupsmenu}[$uuid] = $studsmenuitem;
        }

        // SWTC ********************************************************************************.
        // Managers menu.
        // SWTC ********************************************************************************.
        if (!empty($mgrsmenu)) {
            $submenuitem = 'mgrs_menu';
            if (empty($swtcuser->get_groupnames($submenuitem))) {
                $uuid = rand();
                $temp[$submenuitem][$uuid]['uuid'] = $uuid;
                $temp[$submenuitem][$uuid]['groups'] = implode(', ', $mgrsmenu);
                $swtcuser->set_groupnames($temp);
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach ($swtcuser->get_groupnames($submenuitem) as $key => $value) {
                    $uuid = $key;
                }
            }
            ${$groupsmenu}[$uuid] = $mgrsmenuitem;
        }

        // SWTC ********************************************************************************.
        // Administrators menu.
        // SWTC ********************************************************************************.
        if (!empty($adminsmenu)) {
            $submenuitem = 'admins_menu';
            if (empty($swtcuser->get_groupnames($submenuitem))) {
                $uuid = rand();
                $temp[$submenuitem][$uuid]['uuid'] = $uuid;
                $temp[$submenuitem][$uuid]['groups'] = implode(', ', $adminsmenu);
                $swtcuser->set_groupnames($temp);
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach ($swtcuser->get_groupnames($submenuitem) as $key => $value) {
                    $uuid = $key;
                }
            }
            ${$groupsmenu}[$uuid] = $adminsmenuitem;
        }

        // SWTC ********************************************************************************.
        // GEO Administrators menu.
        // SWTC ********************************************************************************.
        if (!empty($geoadminsmenu)) {
            $submenuitem = 'geoadmins_menu';
            if (empty($swtcuser->get_groupnames($submenuitem))) {
                $uuid = rand();
                $temp[$submenuitem][$uuid]['uuid'] = $uuid;
                $temp[$submenuitem][$uuid]['groups'] = implode(', ', $geoadminsmenu);
                $swtcuser->set_groupnames($temp);
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach ($swtcuser->get_groupnames($submenuitem) as $key => $value) {
                    $uuid = $key;
                }
            }
            ${$groupsmenu}[$uuid] = $geoadminsmenuitem;
        }

        // SWTC ********************************************************************************.
        // Site Administrators menu.
        // SWTC ********************************************************************************.
        if (!empty($siteadminsmenu)) {
            $submenuitem = 'siteadmins_menu';
            if (empty($swtcuser->get_groupnames($submenuitem))) {
                $uuid = rand();
                $temp[$submenuitem][$uuid]['uuid'] = $uuid;
                $temp[$submenuitem][$uuid]['groups'] = implode(', ', $siteadminsmenu);
                $swtcuser->set_groupnames($temp);
            } else {
                // Use foreach even though there will only be one key and one value.
                foreach ($swtcuser->get_groupnames($submenuitem) as $key => $value) {
                    $uuid = $key;
                }
            }
            ${$groupsmenu}[$uuid] = $siteadminsmenuitem;
        }

        if (isset($debug)) {
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "About to print all groups.";
            $messages[] = print_r($groupsmenu, true);
            $messages[] = "Finished printing all groups.";
            $messages[] = "About to print dynamic groupsmenu";
            $messages[] = print_r(${$groupsmenu}, true);
            $debug->logmessage($messages, 'detailed');
            unset($messages);

            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Leaving local_swtc\classes\grouplib\grouplib.php.groups_sort_menu_options.exit===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // TODO: I **KNOW*** there is a better way to build this menu...
        // SWTC ********************************************************************************.
        // Should this be $usergroups instead of $allowedgroups?
        if ($useroptions) {
            // SWTC ********************************************************************************.
            // PS/SD customized menu
            // SWTC ********************************************************************************.
            $custommenu = array(
                1 => array($groupsmenu => ${$groupsmenu}),
                2 => array(get_string('mygroups', 'group') => $useroptions),
                3 => array(get_string('othergroups', 'group') => $allowedoptions)
            );

            return $custommenu;
        } else if ($useroptions) {
            return $useroptions;
        } else {
            return $allowedoptions;
        }
    }

    /**
     * Sets the SQL "WHERE" and sort condition based on the group name passed in.
     *
     * @param string $groupname The group name.
     * @return string $where The SQL "Where" condition to use.
     * @return string $sort The SQL sort string to use.
     *
     * History:
     *
     * 04/19/21 - Initial writing.
     *
     */
    public function set_where_conditions_by_groupname($groupname) {
        global $USER;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $where = null;
        $sort = null;
        $accesstype = $swtcuser->get_accesstype();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering local_swtc\classes\grouplib\grouplib.php.set_where_conditions_by_groupname.enter===.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // IMPORTANT! The following code assumes the following:
        // For PS/SD manager access types (ex: PS-US1-manager):
        // Site cohorts: The user account has been added to both PS-US1-mgrs (for grading AND reporting)
        // AND PS-US1-studs (for notifications).
        // In the Separate groups pull-down menu:
        // The initial group shown will be "PS-US1-mgrs-enrollments".
        // My groups will list "PS-US1-mgrs-enrollments" and "PS-US1-studs-enrollments".
        // No other groups will be listed.
        //
        // For PS/SD administrator access types (ex: PS-US1-administrator):
        // Site cohorts: The user account has been added to only PS-US1-admins (for grading AND reporting).
        // In the Separate groups pull-down menu:
        // The initial group shown will be "PS-US1-admins-enrollments".
        // My groups will list "PS-US1-admins-enrollments".
        // All other "PS-US" groups will be listed.
        // The "All PremierSupport US enrollments" group will be listed first (see groups_print_course_menu).
        // SWTC ********************************************************************************.
        if (has_capability('local/swtc:swtc_view_mgmt_reports', context_system::instance())) {
            // SWTC ********************************************************************************.
            // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
            // SWTC ********************************************************************************.
            // Remember that Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins also have this capability.
            if (($swtcuser->is_psmanagement()) || ($swtcuser->is_sdmanagement())) {
                // SWTC ********************************************************************************.
                // PremierSupport site administrators
                // SWTC ********************************************************************************.
                if (preg_match(get_string('access_premiersupport_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                    // Use PS so that site administrators can view all enrollments.
                    $where = " AND ((g.name LIKE 'PS-" .$groupname. "'))";
                    $sort = '/PS-/i';
                    // SWTC ********************************************************************************.
                    // PremierSupport GEO administrators
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_premiersupport_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US) so that managers can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'PS-" .$groupname. "-studs%') " .
                        " OR (g.name LIKE 'PS-" .$groupname. "-mgrs%')" .
                        " OR (g.name LIKE 'PS-" .$groupname. "-admins%')" .
                        " OR (g.name LIKE 'PS-" .$groupname. "-geoadmins%'))";
                    $sort = '/PS-' . $groupname . '/i';
                    // SWTC ********************************************************************************.
                    // PremierSupport administrators
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_premiersupport_pregmatch_admin', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US1) so that admins can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'PS-" .$groupname. "-studs%')
                        OR (g.name LIKE 'PS-" .$groupname. "-mgrs%')
                        OR (g.name LIKE 'PS-" .$groupname. "-admins%'))";
                    $sort = '/PS-' . $groupname . '/i';
                    // SWTC ********************************************************************************.
                    // PremierSupport managers
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_premiersupport_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US1) so that managers can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'PS-" .$groupname. "-studs%')
                        OR (g.name LIKE 'PS-" .$groupname. "-mgrs%'))";
                    $sort = '/PS-' . $groupname . '/i';
                    // SWTC ********************************************************************************.
                    // ServiceDelivery site administrators
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_siteadmin', 'local_swtc'), $accesstype)) {
                    $where = " AND ((g.name LIKE 'SD%-" .$groupname. "'))";
                    $sort = '/SD(TAM)?-/i';
                    // SWTC ********************************************************************************.
                    // ServiceDelivery GEO administrators
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_geoadmin', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US) so that managers can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'SD%-" .$groupname. "-studs%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-mgrs%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-admins%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-geoadmins%'))";
                    $sort = '/SD(TAM)?-' . $groupname . '/i';
                    // SWTC ********************************************************************************.
                    // ServiceDelivery administrators
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_admin', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US1) so that admins can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'SD%-" .$groupname. "-studs%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-mgrs%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-admins%'))";
                    $sort = '/SD(TAM)?-' . $groupname . '/i';
                    // SWTC ********************************************************************************.
                    // ServiceDelivery managers
                    // SWTC ********************************************************************************.
                } else if (preg_match(get_string('access_lenovo_servicedelivery_pregmatch_mgr', 'local_swtc'), $accesstype)) {
                    // Use groupname (ex: US1) so that managers can view only their GEO enrollments.
                    $where = " AND ((g.name LIKE 'SD%-" .$groupname. "-studs%')
                        OR (g.name LIKE 'SD%-" .$groupname. "-mgrs%'))";
                    $sort = '/SD(TAM)?-' . $groupname . '/i';
                }
            } else if ((preg_match(get_string('access_lenovo_pregmatch_siteadmin', 'local_swtc'), $accesstype))
                || (preg_match(get_string('access_lenovo_pregmatch_admin', 'local_swtc'), $accesstype))) {
                // SWTC ********************************************************************************.
                // Processing for Moodle site administrators, Lenovo-admins, and Lenovo-siteadmins.
                // SWTC ********************************************************************************.
                $where = " AND ((g.name LIKE '" .$groupname. "'))";
                $sort = '';
            }
        }

        return array($where, $sort);

    }

}
