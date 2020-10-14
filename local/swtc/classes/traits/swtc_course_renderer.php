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
 * Lenovo EBGLMS for Moodle 3.7+. All Lenovo customized functions associcated with Moodle /course/renderer.php.
 *      Remember to add the following at the top of any module that requires these functions:
 *
 *      use \local_swtc\traits\lenovo_course_renderer;
 *
 * And put the following within the class that is being overridden:
 *      use lenovo_course_renderer;
 *
 * Version details
 *
 * @package    local
 * @subpackage lenovo_course_renderer.php
 * @copyright  2019 Lenovo DCG Education Services
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 *	11/18/19 - Initial writing; moved majority of customized code from /course/renderer.php to functions defined here; added utility functions;
 *                      added lenovo_coursecat_category.
 * 03/02/20 - In local_swtc_extend_navigation, added call to core_course_category::make_categories_list with the
 *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
 * PTR2020Q108 - @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
 *                      (changed core_course_category to \core_course_category); added a return value if not a PS/SD user.
 *
 **/

namespace local_swtc\traits;
defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require($CFG->dirroot.'/local/swtc/lib/swtc.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS functions.
// Lenovo ********************************************************************************.
// require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
// require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');

// use core_text;
// use html_writer;
// use stdClass;
// use moodle_url;
// use coursecat_helper;
// use lang_string;


trait lenovo_course_renderer {

    /**
     * Returns true if course category should be listed; false if not.
     *
     * Called from: coursecat_category
     *  Location: /course/renderer.php
     *  To call: $this->this_function_name
     *
     * This is an internal function, to display a particular category and all its contents
     * use {@link core_course_renderer::course_category()}
     *
     * @param core_course_category $coursecat
     * @return string
     *
     * Lenovo history:
     *
     * 11/06/18 - Added module History section (this section); updated the PremierSupport check with the new strings.
     * 11/28/18 - Changed specialaccess to viewcurriculums; split accessreports into accessmgrreports and accessstudreports;
     *                      removed all three and changed to customized capabilities.
     * 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context; the capabilities
     *                      for Students are applied in the category context.
	 * 12/14/18 - Due to problems with contexts and user access, removing has_capability checking and rolling back to checking Accesstype for
	 *						PremierSupport and ServiceDelivery user types.
	 *  01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
     * 03/02/20 - In local_swtc_extend_navigation, added call to core_course_category::make_categories_list with the
     *                      user's main capability for easier checking of access (before moving to core_course_category::can_view_category).
     * @01 - 04/17/20 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
     *                      (changed core_course_category to \core_course_category); added a return value if not a PS/SD user.
     *
     */
    public function lenovo_coursecat_category($coursecat) {
        global $CFG, $DB, $PAGE, $USER, $SESSION;

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS swtc_user and debug variables.
        $swtc_user = swtc_get_user($USER);
        $debug = swtc_get_debug();

        // Other Lenovo variables.
        $user_access_type = $SESSION->EBGLMS->USER->user_access_type;

        $capability = $swtc_user->capabilities[0];
        $access_selfsupport_stud = $SESSION->EBGLMS->STRINGS->selfsupport->access_selfsupport_stud;

        // Remember - PremierSupport and ServiceDelivery managers and admins have special access.
        $access_psmgr = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_mgr;
        $access_psadmin = $SESSION->EBGLMS->STRINGS->premiersupport->access_premiersupport_pregmatch_admin;

        $access_lenovo_sdmgr = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_mgr;
        $access_lenovo_sdadmin = $SESSION->EBGLMS->STRINGS->servicedelivery->access_lenovo_servicedelivery_pregmatch_admin;
        // Lenovo ********************************************************************************.

        //****************************************************************************************.
        if (isset($debug)) {
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "In /local/swtc/classes/traits/lenovo_course_renderer.php===lenovo_course_renderer.enter";
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'both');
            unset($messages);
        }
        //****************************************************************************************.

        // Lenovo ********************************************************************************
        // 03/02/20 - Added call to core_course_category::make_categories_list with the user's main capability for easier checking
        //                  of access (before moving to core_course_category::can_view_category).
        // @01 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
        //              (changed core_course_category to \core_course_category); added a return value if not a PS/SD user.
        // Lenovo ********************************************************************************
        $categories = \core_course_category::make_categories_list($capability);      // @01
        // print_object($categories);     // 04/17/20 - Lenovo debugging...
        // print_object($swtc_user);     // 04/17/20 - Lenovo debugging...

        // Lenovo ********************************************************************************
        // 11/28/18 - Any user (PremierSupport and ServiceDelivery users for now) have special access
        //                      if they have the required capability.
        // 12/03/18 - Remember that the capabilities for Managers and Administrators are applied in the system context;
        //                          the capabilities for Students are applied in the category context.
		// 12/18/18 - IMPORTANT - Since the access types are in the form of "access_premiersupport_admin_ap1" or
		//			"access_premiersupport_mgr_ca3", we must use the "base" string (for example, "access_premiersupport_admin")
		//			and perform a string compare (rather than a simple ==) to check for the user type.
		// 01/24/19 - Due to the updated PremierSupport and ServiceDelivery user access types, using preg_match to search for access types.
        // Lenovo ********************************************************************************
        if ((preg_match($access_psmgr, $user_access_type)) || (preg_match($access_psadmin, $user_access_type)) || (preg_match($access_lenovo_sdmgr, $user_access_type)) || (preg_match($access_lenovo_sdadmin, $user_access_type))) {
            // Lenovo ********************************************************************************
            // Lenovo ********************************************************************************
            // Main loop. Find top-level catid of category. If user has access, leave the course in the list.
            //      If the user doesn't, remove it from the list.
            // Lenovo ********************************************************************************
            // Category id for the category name in question is in $coursecat->id.
            // In theory, if the category id appears in $swtc_user->categoryids, that alone should be enough to list the category.
            // Lenovo ********************************************************************************
            // if (swtc_user_access_category($coursecat->id)) {       // 03/02/20
            if ((in_array($coursecat->id, array_keys($categories))) || (stripos($swtc_user->user_access_type, $access_selfsupport_stud) !== false)) {
                if (isset($debug)) {
                    $messages[] = print_r("catid found. Will list the category===lenovo_course_renderer.exit", true);
                    debug_logmessage($messages, 'detailed');
                    unset($messages);
                }

                return true;

            } else {
                if (isset($debug)) {
                    $messages[] = "catid NOT found. Will NOT list the category===lenovo_course_renderer.exit";
                    debug_logmessage($messages, 'both');
                    unset($messages);
                }

                return false;

            }
        } else {
            // Lenovo ********************************************************************************.
            // @01 - Fixed core_course_category error in /local/swtc/classes/traits/lenovo_course_renderer.php
            //              (changed core_course_category to \core_course_category); added a return value if not a PS/SD user.
            // Lenovo ********************************************************************************.
            return true;
        }
    }


}
