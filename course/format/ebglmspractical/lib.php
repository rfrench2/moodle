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
 * This file contains main class for the course format Topic
 *
 * @since     Moodle 2.0
 * @package   format_topics
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @since     Moodle 2.0
 * @package   format_ebglmspractical
 * @copyright 2018 Lenovo EBG LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 11/05/18 - Initial writing; based on format_ebglmscustom version 2018083107.
 * 11/12/18 - Added strings for ServiceDelivery and PracticalActivities portfolios.
 * 11/14/18 - Added include of ebglms_constants.php.
 * 12/20/18 - Adding course shortname to curriculums listbox; sorting listbox by course shortname.
 * 05/15/19 - Cleaned up some code.
 * 05/30/19 - Added related courses listbox to /course/edit_form.php; added related courses to course format options.
 * 06/20/19 - Create "related courses" section if course format option "relatedcourses" is set.
 * 07/01/19 - For Moodle 3.7 (and all previous), need to pass more fields to get_courses (since coursecatlib::get_courses
 *                   eventually calls get_course which requires "visible" and "category" fields).
 * 08/12/19 - Added course duration to course overview (and course format options).
 * 08/19/19 - In course_format_options, changed get_courses to relatedcourses_getall to return all courses for listbox; in
 *                      update_course_format_options, saving related courses to course format options AND local_ebglms_rc table.
 * 08/22/19 - Changed call from relatedcourses_getall to local_ebglms_get_all_courses.
 * 09/11/19 - In course_format_options, added important note about "machinetypes" and "duration".
 * 10/15/19 - Changed to new Lenovo EBGLMS classes and methods to load ebglms_user and debug.
 * 12/06/19 - Added "Curriculums Portfolio" and "Site Help Portfolio" as selections; changed strings from dashes
 *                      "practicalactivities-portfolio" to underscores "practicalactivities_portfolio"; removed course format option "relatedcourses".
 * 01/06/20 - In create_edit_form_elements, changed 'duration' from PARAM_INT to PARAM_TEXT.
 * 01/16/20 - In update_course_format_options, if course format option does not exist in $data, do NOT zero out the value.
 * PTR2020Q107 - @01 - 04/29/20 - Changed way "section 0" is loaded (like other course formats).
 *
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot. '/course/lib.php');     // 06/20/19

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/locallib.php');                     // Some needed functions.
require_once($CFG->dirroot.'/local/swtc/lib/curriculumslib.php');         // Include curriculum utility functions.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');   // Include constants.
require_once($CFG->dirroot.'/local/swtc/lib/relatedcourseslib.php');   // Include related courses utility functions.

/**
 * Define the new coursetype for the ebglsmcustom course format.
 * Important! Values must match the values defined in ebglms local plugin lib.php...
 * 11/14/18 - Constants moved to /local/ebglms/lib/ebglms_constants.php.
 */
// define('COURSETYPE_GTP', 3);
// define('COURSETYPE_LENOVOANDIBM', 4);
// define('COURSETYPE_IBM', 4);
// define('COURSETYPE_LENOVO', 5);
// define('COURSETYPE_SERVICEPROVIDER', 6);
// define('COURSETYPE_LENOVOINTERNAL', 7);
// define('COURSETYPE_LENOVOSHAREDRESOURCES', 8);
// define('COURSETYPE_MAINTECH', 9);
// define('COURSETYPE_ASP', 10);
// define('COURSETYPE_PREMIERSUPPORT', 11);
// define('COURSETYPE_SERVICEDELIVERY', 12);
// define('COURSETYPE_PRACTICALACTIVITIES', 13);
// define('COURSETYPE_NONE', 66);

/**
 * Main class for the Topics course format
 *
 * @package    format_ebglmspractical
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_ebglmspractical extends format_base {

    /**
     * Returns true if this course format uses sections
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #")
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     *
     * Lenovo history:
     *
     *  @01 - 04/29/20 - Changed way "section 0" is loaded (like other course formats).
     *
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            // Return the name the user set.
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } if ($section->section == 0) {        // @01
            // Return the general section.      // @01
            return get_string('section0name', 'format_ebglmscustom');       // @01
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name for the topics course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of format_base::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            // return get_string('section0name', 'format_topics');
            return get_string('section0name', 'format_ebglmspractical');
        } else {
            // Use format_base::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            // return parent::get_default_section_name($section);
            return get_string('sectionname', 'format_ebglmspractical');
        }
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else {
                if (empty($CFG->linkcoursesections) && !empty($options['navigation'])) {
                    return null;
                }
                $url->set_anchor('section-'.$sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        global $PAGE;
        // if section is specified in course/view.php, make sure it is expanded in navigation
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // check if there are callbacks to extend course navigation
        parent::extend_course_navigation($navigation, $node);

        // We want to remove the general section if it is empty.
        $modinfo = get_fast_modinfo($this->get_course());
        $sections = $modinfo->get_sections();
        if (!isset($sections[0])) {
            // The general section is empty to find the navigation node for it we need to get its ID.
            $section = $modinfo->get_section_info(0);
            $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            if ($generalsection) {
                // We found the node - now remove it.
                $generalsection->remove();
            }
        }
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
    }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * EBG LMS custom format uses the following options:
     * - coursedisplay
     * - numsections        // 08/31/18 - Since format_topics removed "numsections" in Moodle 3.3, so will we.
     * - hiddensections
     * - coursetype
     *
     * @param bool $foreditform
     * @return array of options
     *
     * Lenovo history:
     *
     *  xx/xx/15 - Added coursetype field to form.
     * 09/10/18 - Adding iscurriculum, ispartofcurriculum, and curriculums.
	 * 12/20/18 - Adding course shortname to curriculums listbox; sorting listbox by course shortname.
     * 05/15/19 - Cleaned up some code.
     * 05/30/19 - Added related courses listbox to /course/edit_form.php; added related courses to course format options.
     * 07/01/19 - For Moodle 3.7 (and all previous), need to pass more fields to get_courses (since coursecatlib::get_courses
     *                   eventually calls get_course which requires "visible" and "category" fields).
     * 08/19/19 - Changed get_courses to relatedcourses_getall to return all courses for listbox.
     * 08/22/19 - Changed call from relatedcourses_getall to local_ebglms_get_all_courses.
     * 09/11/19 - Added important note about "machinetypes" and "duration".
     * 12/06/19 - Added "Curriculums Portfolio" and "Site Help Portfolio" as selections; changed strings from dashes
 *                      "practicalactivities-portfolio" to underscores "practicalactivities_portfolio"; removed course format option "relatedcourses".
     *
     */
    public function course_format_options($foreditform = false) {
        global $DB;     // Lenovo
        static $courseformatoptions = false;
        $curriculum_array = array();            // Lenovo
        $relatedcourses_array = array();      // 05/30/19
        $trunclength = 100;

        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ),
                /* EBG LMS practical format */
                'coursetype' => array(
                    'default' => get_config('format_ebglmspractical', 'coursetype'),
                    'type' => PARAM_INT,
                ),
                /* EBG LMS iscurriculum field */        // 08/31/18
                'iscurriculum' => array(
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                /* EBG LMS ispartofcurriculum field */        // 09/10/18
                'ispartofcurriculum' => array(
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                /* EBG LMS curriculums field */        // 09/10/18
                'curriculums' => array(
                    'default' => 0,
                    'type' => PARAM_TEXT,
                ),
                /* EBG LMS related courses field */        // 05/30/19
                // 'relatedcourses' => array(
                //     'default' => 0,
                //     'type' => PARAM_TEXT,
                // ),
                // Lenovo ********************************************************************************.
                // 09/11/19 - Special note about the course format options "machinetypes" and "duration":
                //      Even though "machinetypes" and "duration" are course format options, and their values reside in the
                //          course_format_options table, they cannot be processed here.
                //
                //      Because they are shown in the General section of the form, they must be processed in
                //          create_edit_form_elements (below). If they are processed here, an error is produced:
                //              Undefined index: label in /var/www/html/course/format/lib.php on line 705
                //
                //      A side effect of this is "machinetypes" and "duration" will NOT be returned using the normal Moodle
                //          course format option function calls (for example, course_get_format($course->id)->get_format_options()).
                //          Therefore, in Moosh scripts (or anywhere else for that matter), we must use raw database calls to
                //          query, get, and update all course format options.
                // Lenovo ********************************************************************************.
                /* EBG LMS machinetypes field */        // 09/10/19
               // 'machinetypes' => array(
               //     'default' => 'N/A',
               //     'type' => PARAM_TEXT,
               // ),
               // /* EBG LMS duration field */        // 09/10/19
               // duration' => array(
               //    'default' => 0,
               //    'type' => PARAM_INT,
               //
            );
        }

        // Lenovo ********************************************************************************
        // 10/22/18 - Get all the curriculum courses and fill the "curriculums" select element.
		// 12/20/18 - Adding course shortname to curriculums listbox; sorting listbox by course shortname.
        // Lenovo ********************************************************************************
        $records = curriculums_getall();

        foreach ($records as $record) {
            $curriculum_array[$record->courseid] = $record->shortname .' '. $record->fullname;
        }

        asort($curriculum_array);
        // print_object($curriculum_array);

        // Lenovo ********************************************************************************.
        // 05/30/19 - Get a list of all the courses.
        // 07/01/19 - For Moodle 3.7 (and all previous), need to pass more fields to get_courses (since coursecatlib::get_courses
        //                  eventually calls get_course which requires "visible" and "category" fields).
        // Lenovo ********************************************************************************.
        // $records = get_courses('all', 'c.sortorder ASC', 'c.id, c.sortorder, c.visible, c.fullname, c.shortname, c.category');

        // Lenovo ********************************************************************************.
        // 08/19/19 - Only list courses NOT in top level categories 60 (Lenovo Internal) and 73 (resource).
        // Lenovo ********************************************************************************.
        // $records = local_ebglms_get_all_courses();
        //
        // foreach ($records as $record) {
        //     $fullnamelength = core_text::strlen($record->fullname);
        //
        //     // If for some reason the length requested is equal to or is greater than the current length of the course fullname, return
        //     //      the entire course fullname. If not, return only what was requested.
        //     if ($trunclength >= $fullnamelength) {
        //         $coursefullname = $record->fullname;
        //     } else {
        //         // shorten_text is in /lib/moodlelib.php.
        //         $coursefullname = shorten_text($record->fullname, $trunclength);
        //     }
        //
        //     $relatedcourses_array[$record->id] = $record->shortname .' '. $coursefullname;
        // }
        //
        // asort($relatedcourses_array);
		// print_object($relatedcourses_array);

        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseformatoptionsedit = array(
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ),
                /* EBG LMS custom format */
                'coursetype' => array(
                    'label' => new lang_string('coursetype', 'format_ebglmspractical'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSETYPE_SERVICEPROVIDER => get_string('serviceprovider_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_GTP => get_string('gtp_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_IBM => get_string('ibm_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_LENOVO => get_string('lenovo_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_LENOVOINTERNAL => get_string('lenovointernal_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_LENOVOSHAREDRESOURCES => get_string('lenovosharedresources_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_MAINTECH => get_string('maintech_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_ASP => get_string('asp_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_PREMIERSUPPORT => get_string('premiersupport_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_SERVICEDELIVERY => get_string('servicedelivery_portfolio', 'format_ebglmspractical'),
                            COURSETYPE_PRACTICALACTIVITIES => get_string('practicalactivities_portfolio', 'format_ebglmspractical')
                        )
                    ),
                    'help' => 'coursetype',
                    'help_component' => 'format_ebglmspractical',
                ),
                /* EBG LMS iscurriculum field */        // 08/31/18
                'iscurriculum' => array(
                    'label' => new lang_string('iscurriculum', 'format_ebglmspractical'),
                    'element_type' => 'advcheckbox',
                    'help' => 'iscurriculum',
                    'help_component' => 'format_ebglmspractical',
                ),
                /* EBG LMS ispartofcurriculum field */        // 09/10/18
                'ispartofcurriculum' => array(
                    'label' => new lang_string('ispartofcurriculum', 'format_ebglmspractical'),
                    'element_type' => 'advcheckbox',
                    'help' => 'ispartofcurriculum',
                    'help_component' => 'format_ebglmspractical',
                ),
                /* EBG LMS curriculums field */        // 09/10/18
                'curriculums' => array(
                    'label' => new lang_string('curriculums', 'format_ebglmspractical'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        $curriculum_array,
                        'multiple' => 'multiple',
                        'size' => 10,
                    ),
                    'help' => 'curriculums',
                    'help_component' => 'format_ebglmspractical',
                ),
                /* EBG LMS related courses field */        // 05/30/19
               // 'relatedcourses' => array(
               //     'label' => new lang_string('relatedcourses', 'format_ebglmspractical'),
               //     'element_type' => 'select',
               //     'element_attributes' => array(
               //         $relatedcourses_array,
               //         'multiple' => 'multiple',
               //         'size' => 10,
               //     ),
               //     'help' => 'relatedcourses',
               //     'help_component' => 'format_ebglmspractical',
               // ),
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@link course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     *
     * Lenovo history:
     *
     *  04/11/17 - Added courseversion and machinetypes field to form.
     *  04/17/17 - Removed (or commented-out) courseversion (not implementing now).
     *  06/27/18 - Added default for machinetypes.
     * 08/12/19 - Added course duration to course overview (and course format options).
     * 01/06/20 - In create_edit_form_elements, changed 'duration' from PARAM_INT to PARAM_TEXT.
     *
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $COURSE, $DB;
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if (!$forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
            // Add "numsections" element to the create course form - it will force new course to be prepopulated
            // with empty sections.
            // The "Number of sections" option is no longer available when editing course, instead teachers should
            // delete and add sections when needed.
            $courseconfig = get_config('moodlecourse');
            $max = (int)$courseconfig->maxsections;
            $element = $mform->addElement('select', 'numsections', get_string('numberweeks'), range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            if (is_null($mform->getElementValue('numsections'))) {
                $mform->setDefault('numsections', $courseconfig->numsections);
            }
            array_unshift($elements, $element);

        }

        // Next, 'Machine types' field.
        // Need to insert it (not just add it). $mform->addElement('text', 'mtlist', get_string('machinetypes', 'format_ebglmspractical'));
        $mform->insertElementBefore($mform->createElement('text', 'machinetypes', get_string('machinetypes', 'format_ebglmspractical'), ''), 'category');
        $mform->addHelpButton('machinetypes', 'machinetypes', 'format_ebglmspractical');
        $mform->setType('machinetypes', PARAM_TEXT);
        $mform->addRule('machinetypes', get_string('required'), 'required', null, 'client');

        // Load form element value with 'machinetypes' from database (if it exists).
        $record = $DB->get_record('course_format_options',  array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => 0, 'name' => 'machinetypes'));

        // 08/31/18 - Do not set default value so that, if nothing is entered in field, an error occurs.
        if ($record) {
            $element = $mform->getElement('machinetypes');
            $machinetypes = $record->value;
            $element->setValue($machinetypes);
        }

        // Next, 'Duration' field (maxlength of 4).
        // Need to insert it (not just add it). $mform->addElement('text', 'mtlist', get_string('duration', 'format_ebglmscustom'));
        // $mform->insertElementBefore($mform->createElement('text', 'duration', get_string('duration', 'format_ebglmspractical'), 'maxlength="4" size="10"'), 'category');     // 01/06/20
        $mform->insertElementBefore($mform->createElement('text', 'duration', get_string('duration', 'format_ebglmspractical'), ''), 'category');     // 01/06/20
        $mform->addHelpButton('duration', 'duration', 'format_ebglmspractical');
        // $mform->setType('duration', PARAM_INT);      // 01/06/20
        $mform->setType('duration', PARAM_TEXT);    // 01/06/20
        $mform->addRule('duration', get_string('required'), 'required', null, 'client');
        // $mform->addRule('duration', get_string('duration_help', 'format_ebglmspractical'), 'numeric', null, 'client');       // 01/06/20

        // Load form element value with 'duration' from database (if it exists).
        $record = $DB->get_record('course_format_options',  array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => 0, 'name' => 'duration'));

        // 08/31/18 - Do not set default value so that, if nothing is entered in field, an error occurs.
        if ($record) {
            $element = $mform->getElement('duration');
            $duration = $record->value;
            $element->setValue($duration);
        }

        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'ebglmspractical', we try to copy options
     * 'coursedisplay', 'hiddensections', and others from the previous format.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     *
     * Lenovo history:
     *
     *  04/11/17 - Added courseversion and machinetypes field to form.
     *  04/17/17 - Removed (or commented-out) courseversion (not implementing now).
     * 10/22/18 - Adding iscurriculum, ispartofcurriculum, and curriculums.
     * 05/30/19 - Added related courses listbox to /course/edit_form.php; added related courses to course format options.
     * 06/20/19 - Create "related courses" section if course format option "relatedcourses" is set.
     * 08/12/19 - Added course duration to course overview (and course format options).
     * 08/19/19 - Saving related courses to course format options AND local_ebglms_rc table.
     * 10/15/19 - Changed to new Lenovo EBGLMS classes and methods to load ebglms_user and debug.
     * 12/06/19 - Added "Curriculums Portfolio" and "Site Help Portfolio" as selections; changed strings from dashes
     *                      "practicalactivities-portfolio" to underscores "practicalactivities_portfolio"; removed course format option "relatedcourses".
     * 01/16/20 - In update_course_format_options, if course format option does not exist in $data, do NOT zero out the value.
     *
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB, $CFG, $USER, $SESSION;     // Lenovo

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS ebglms_user and debug variables.
        $ebglms_user = ebglms_get_user($USER);
        $debug = ebglms_get_debug();

        // Other Lenovo variables.
        $sectionid = 0;
        // Lenovo ********************************************************************************.

        if (isset($debug)) {
            // Lenovo ********************************************************************************
            // Always output standard header information.
            // Lenovo ********************************************************************************
            $messages[] = "Lenovo ********************************************************************************.";
            $messages[] = "Entering /course/format/ebglmspractical/lib.php:update_course_format_options. ==update_course_format_options.enter.";
            $messages[] = "About to print ebglms_user.";
            $messages[] = print_r($ebglms_user, true);
            $messages[] = "Finished printing ebglms_user.";
            $messages[] = "Lenovo ********************************************************************************.";
            debug_logmessage($messages, 'logfile');
            unset($messages);
        }

        $data = (array)$data;
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();

            if (isset($debug)) {
                $messages[] = "About to print options.";
                $messages[] = print_r($options, true);
                $messages[] = "Finished printing options. About to print data (only includes definition of options, not actual data).";
                $messages[] = print_r($data, true);
                $messages[] = "Finished printing data (includes changes just made to course).";
                debug_logmessage($messages, 'detailed');
                unset($messages);
               // print_object($records);
               // print_object($options);
               // print_object($data);
            }



            // $temp = explode(',', $optiondata['curriculums']);
            // $tmp_line = implode(',', $line);


            // key is the course format options themselves: for example - hiddensections, coursedisplay, coursetype, iscurriculum,
            //      ispartofcurriculum, and curriculums (which is an array and has special processing).
            foreach ($options as $key => $unused) {
                if (isset($debug)) {
                    $messages[] = "key (course format option) is :$key.";
                    debug_logmessage($messages, 'detailed');
                    unset($messages);
                   // print_object($key);
                }

                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        // Lenovo ********************************************************************************.
                        // 05/30/19 - Special case: If removing all related courses, $data['relatedcourses'] will NOT be set. Therefore,
                        //      it really doesn't matter what the relatedcourses are in $oldcourse (because they will be removed).
                        // Lenovo ********************************************************************************
                        // if ($key !== 'relatedcourses') {
                            $data[$key] = $oldcourse[$key];
                        // }
                    }
                }
            }

            // Lenovo ********************************************************************************
            // 10/22/18 - Get information for "curriculums". If "curriculums" did not exist, it would have been added
            //              just above here.
            // 10/23/18 - Ignore if "ispartofcurriculum" is NOT set.
            //  TODO: Dynamically disable curriculums select form element if "ispartofcurriculum" is NOT set.
            // 01/16/20 - In update_course_format_options, if course format option does not exist in $data, do NOT zero out the value.
            // Lenovo ********************************************************************************
            // if ((array_key_exists('ispartofcurriculum', $data)) && (!empty($data['ispartofcurriculum']))) {
            if (array_key_exists('curriculums', $data)) {
                if (!empty($data['ispartofcurriculum'])) {
                    // print_object($data['curriculums']);
                    if (!empty($data['curriculums'])) {
                        $data['curriculums'] = implode(', ', $data['curriculums']);
                    }
                } else {
                    // Some curriculums were selected, but the ispartofcurriculum flag was not set (this situation should not happen in the future).
                    $data['curriculums'] = 0;
                }
            }

            // Lenovo ********************************************************************************.
            // 05/30/19 - Added related courses listbox to /course/edit_form.php; added related courses to course format options.
            //          Notes:
            //              $data is the current data that is returned from the just edited form. $curriculums and / or $relatedcourses might
            //              the same (empty), the same (several courses selected, but none are new or removed), changed (courses that were
            //                  selected previously might not be slected now or others might have been added), or empty (it did have some
            //                  courses selected, but not have any courses now).
            //
            //              Several scenarios exist (for curriculums and relatedcourses):
            //                  - If relatedcourses is empty, it will not be found in $data.
            //                  - If relatedcourses is not empty, it will initially be an array that looks like the following:
            //
            //          [data] => Array
            // 					(
            //                      ...
            //                      [hiddensections] => 1
            //                      [coursedisplay] => 0
            //                      [relatedcourses] => array[2]
            // 					                            (
            //                                                  [0] => 597
            //                                                  [1] => 599
            //                                               )
            //                      ...
            //                  )
            //
            //              Before saving to database, it must be imploded into a string.
            // Lenovo ********************************************************************************.
            // if (array_key_exists('relatedcourses', $data)) {
            //     // print_object($data['relatedcourses']);
            //     if (!empty($data['relatedcourses'])) {
            //         if (is_array($data['relatedcourses'])) {
            //             // print_object($data['relatedcourses']);
            //             // Save the related courses in the local_ebglms_rc table.
            //             relatedcourses_put_courses($this->courseid, $data['relatedcourses']);
            //
            //             // Format to save in course format options.
            //             $data['relatedcourses'] = implode(', ', $data['relatedcourses']);
            //             // 08/01/19 - Unset so duplicate entries are not saved.
            //             // unset($data['relatedcourses']);
            //         }
            //     } else {
            //         $data['relatedcourses'] = 0;
            //         // Also remove the courses from the local_ebglms_rc table.
            //         relatedcourses_put_courses($this->courseid, array());
            //     }
            // } else {
            //     $data['relatedcourses'] = 0;
            //     // Also remove the courses from the local_ebglms_rc table.
            //     relatedcourses_put_courses($this->courseid, array());
            // }

            // print_object($data);        // 05/30/19 Lenovo

            // Even though machinetypes is a course format option (and is saved as a course format option), it must be handled differently
            //      because it is shown in the "General" section, not the "Course format" section.
            // Load form element value with 'machinetypes' from database (if it exists).
            // 01/16/20 - In update_course_format_options, if course format option does not exist in $data, do NOT zero out the value.
            if ( array_key_exists('machinetypes', $data)) {
                $newtypes = $data['machinetypes'];

                $record = $DB->get_record('course_format_options',  array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => $sectionid, 'name' => 'machinetypes'));
                if (isset($debug)) {
                    // print_object($record);
                }

                if( !(empty($record))) {
                    $DB->update_record('course_format_options', array(
                        'courseid' => $this->courseid,
                        'format' => $this->format,
                        'id' => $record->id,
                        'sectionid' => $sectionid,
                        'name' => 'machinetypes',
                        'value' => $newtypes
                    ));
                } else {
                    $DB->insert_record('course_format_options', array(
                        'courseid' => $this->courseid,
                        'format' => $this->format,
                        'sectionid' => $sectionid,
                        'name' => 'machinetypes',
                        'value' => $newtypes
                    ));
                }
            }

            // Do the same processing for duration.
            // 01/16/20 - In update_course_format_options, if course format option does not exist in $data, do NOT zero out the value.
            if (array_key_exists('duration', $data)) {
                $duration = $data['duration'];

                $record = $DB->get_record('course_format_options',  array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => $sectionid, 'name' => 'duration'));
                if (isset($debug)) {
                    // print_object($record);
                }

                if( !(empty($record))) {
                    $DB->update_record('course_format_options', array(
                        'courseid' => $this->courseid,
                        'format' => $this->format,
                        'id' => $record->id,
                        'sectionid' => $sectionid,
                        'name' => 'duration',
                        'value' => $duration
                    ));
                } else {
                    $DB->insert_record('course_format_options', array(
                        'courseid' => $this->courseid,
                        'format' => $this->format,
                        'sectionid' => $sectionid,
                        'name' => 'duration',
                        'value' => $duration
                    ));
                }
            }
        }

        return $this->update_format_options($data);
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
                                                         $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            // $edithint = new lang_string('editsectionname', 'format_topics');
            $edithint = new lang_string('editsectionname', 'format_ebglmspractical');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            // $editlabel = new lang_string('newsectionname', 'format_topics', $title);
            $editlabel = new lang_string('newsectionname', 'format_ebglmspractical', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return !$section->section || $section->visible;
    }

    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'topics' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        // $renderer = $PAGE->get_renderer('format_topics');
        $renderer = $PAGE->get_renderer('format_ebglmspractical');
        $rv['section_availability'] = $renderer->section_availability($this->get_section($section));
        return $rv;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     * @since Moodle 3.5
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    /**
     * The course was updated. Determine why it was updated and, if the category was changed, send a notification of the category change.
     *
     * This method is called from event observers and it can not use any modinfo or format caches because
     * events are triggered before the caches are reset.
     *
     * @param object $eventdata information about the message (origin, destination, type, content)
     */
    public static function course_updated($eventdata) {
        global $DB, $COURSE, $CFG, $USER, $SESSION;

        // print_object($eventdata);

        // local_ebglms_assign_user_role($eventdata);

    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 *
 * Lenovo history:
 *
 * 05/02/17 - Finished fix for section names started on 08/17/16.
 *
 */
function format_ebglmspractical_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            // array($itemid, 'topics'), MUST_EXIST); 05/02/17
            array($itemid, 'ebglmspractical'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
