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
 * @package   format_swtccustom
 * @copyright 2021 SWTC LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 10/23/20 - Initial writing.
 *
 */

use \local_swtc\curriculums\curriculums;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot. '/course/lib.php');

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot.'/local/swtc/lib/swtc_constants.php');

/**
 * Main class for the Topics course format
 *
 * @package    format_swtccustom
 * @copyright  2021 SWTC LMS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_swtccustom extends format_base {

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
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            // Return the name the user set.
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_swtccustom');
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
            return get_string('section0name', 'format_swtccustom');
        } else {
            // Use format_base::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return get_string('sectionname', 'format_swtccustom');
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
        // If section is specified in course/view.php, make sure it is expanded in navigation.
        if ($navigation->includesectionnum === false) {
            $selectedsection = optional_param('section', null, PARAM_INT);
            if ($selectedsection !== null && (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0') &&
                    $PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
                $navigation->includesectionnum = $selectedsection;
            }
        }

        // Check if there are callbacks to extend course navigation.
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
    public function ajax_section_move() {
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
     * SWTC LMS custom format uses the following options:
     * - coursedisplay
     * - numsections        // 08/31/18 - Since format_topics removed "numsections" in Moodle 3.3, so will we.
     * - hiddensections
     * - coursetype
     * - machinetypes
     * - iscurriculum
     * - ispartofcurriculum
     * - curriculums
     * - relatedcourses
     * - duration
     *
     * @param bool $foreditform
     * @return array of options
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    public function course_format_options($foreditform = false) {
        global $DB;

        $curriculums = new curriculums;

        static $courseformatoptions = false;
        $curriculumarray = array();
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
                /* SWTC LMS custom format */
                'coursetype' => array(
                    'default' => get_config('format_swtccustom', 'coursetype'),
                    'type' => PARAM_INT,
                ),
                /* SWTC LMS iscurriculum field */
                'iscurriculum' => array(
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                /* SWTC LMS ispartofcurriculum field */
                'ispartofcurriculum' => array(
                    'default' => 0,
                    'type' => PARAM_INT,
                ),
                /* SWTC LMS curriculums field */
                'curriculums' => array(
                    'default' => 0,
                    'type' => PARAM_TEXT,
                ),
            );
        }

        // SWTC ********************************************************************************.
        // 10/22/18 - Get all the curriculum courses and fill the "curriculums" select element.
        // 12/20/18 - Adding course shortname to curriculums listbox; sorting listbox by course shortname.
        // SWTC ********************************************************************************.
        $records = $curriculums->get_all_curriculums();

        foreach ($records as $record) {
            $curriculumarray[$record->courseid] = $record->shortname .' '. $record->fullname;
        }

        asort($curriculumarray);

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
                /* SWTC LMS custom format */
                'coursetype' => array(
                    'label' => new lang_string('coursetype', 'format_swtccustom'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSETYPE_SERVICEPROVIDER => get_string('serviceprovider_portfolio', 'local_swtc'),
                            COURSETYPE_GTP => get_string('gtp_portfolio', 'local_swtc'),
                            COURSETYPE_IBM => get_string('ibm_portfolio', 'local_swtc'),
                            COURSETYPE_LENOVO => get_string('lenovo_portfolio', 'local_swtc'),
                            COURSETYPE_LENOVOINTERNAL => get_string('lenovointernal_portfolio', 'local_swtc'),
                            COURSETYPE_LENOVOSHAREDRESOURCES => get_string('lenovosharedresources_portfolio', 'local_swtc'),
                            COURSETYPE_MAINTECH => get_string('maintech_portfolio', 'local_swtc'),
                            COURSETYPE_ASP => get_string('asp_portfolio', 'local_swtc'),
                            COURSETYPE_PREMIERSUPPORT => get_string('premiersupport_portfolio', 'local_swtc'),
                            COURSETYPE_SERVICEDELIVERY => get_string('servicedelivery_portfolio', 'local_swtc'),
                            COURSETYPE_PRACTICALACTIVITIES => get_string('practicalactivities_portfolio', 'local_swtc'),
                            COURSETYPE_CURRICULUMS => get_string('curriculums_portfolio', 'local_swtc'),
                            COURSETYPE_SITEHELP => get_string('sitehelp_portfolio', 'local_swtc')
                        )
                    ),
                    'help' => 'coursetype',
                    'help_component' => 'format_swtccustom',
                ),
                /* SWTC LMS iscurriculum field */
                'iscurriculum' => array(
                    'label' => new lang_string('iscurriculum', 'format_swtccustom'),
                    'element_type' => 'advcheckbox',
                    'help' => 'iscurriculum',
                    'help_component' => 'format_swtccustom',
                ),
                /* SWTC LMS ispartofcurriculum field */
                'ispartofcurriculum' => array(
                    'label' => new lang_string('ispartofcurriculum', 'format_swtccustom'),
                    'element_type' => 'advcheckbox',
                    'help' => 'ispartofcurriculum',
                    'help_component' => 'format_swtccustom',
                ),
                /* SWTC LMS curriculums field */
                'curriculums' => array(
                    'label' => new lang_string('curriculums', 'format_swtccustom'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        $curriculumarray,
                        'multiple' => 'multiple',
                        'size' => 10,
                    ),
                    'help' => 'curriculums',
                    'help_component' => 'format_swtccustom',
                ),
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
     * History:
     *
     * 10/23/20 - Initial writing.
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
        // Need to insert it (not just add it).
        // Moved some SWTC custom course format strings to /local/swtc/lang/en/local_swtc.php
        // to remove duplication of common strings.
        $mform->insertElementBefore($mform->createElement('text', 'machinetypes',
            get_string('machinetypes', 'local_swtc'), ''), 'category');
        $mform->addHelpButton('machinetypes', 'machinetypes', 'local_swtc');
        $mform->setType('machinetypes', PARAM_TEXT);
        $mform->addRule('machinetypes', get_string('required'), 'required', null, 'client');

         // Load form element value with 'machinetypes' from database (if it exists).
        $record = $DB->get_record('course_format_options',
            array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => 0, 'name' => 'machinetypes'));

        // Do not set default value so that, if nothing is entered in field, an error occurs.
        if ($record) {
            $element = $mform->getElement('machinetypes');
            $machinetypes = $record->value;
            $element->setValue($machinetypes);
        }

        // Next, 'Duration' field (maxlength of 4).
        // Need to insert it (not just add it).
        // Moved some SWTC custom course format strings to /local/swtc/lang/en/local_swtc.php
        // to remove duplication of common strings.
        $mform->insertElementBefore($mform->createElement('text', 'duration',
            get_string('duration', 'local_swtc'), ''), 'category');
        $mform->addHelpButton('duration', 'duration', 'local_swtc');
        $mform->setType('duration', PARAM_TEXT);
        $mform->addRule('duration', get_string('required'), 'required', null, 'client');

        // Load form element value with 'duration' from database (if it exists).
        $record = $DB->get_record('course_format_options',
            array('courseid' => $this->courseid, 'format' => $this->format, 'sectionid' => 0, 'name' => 'duration'));

        // Do not set default value so that, if nothing is entered in field, an error occurs.
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
     * In case if course format was changed to 'swtccustom', we try to copy options
     * 'coursedisplay', 'hiddensections', and others from the previous format.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB, $CFG, $USER, $SESSION;

        // SWTC ********************************************************************************.
        // SWTC LMS swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $sectionid = 0;
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            // SWTC ********************************************************************************.
            // Always output standard header information.
            // SWTC ********************************************************************************.
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $messages[] = "Entering /course/format/swtccustom/lib.php==update_course_format_options.enter.";
            $messages[] = "About to print swtcuser.";
            $messages[] = print_r($swtcuser, true);
            $messages[] = "Finished printing swtcuser.";
            $messages[] = get_string('swtc_debug', 'local_swtc');
            $debug->logmessage($messages, 'logfile');
            unset($messages);
        }

        $data = (array)$data;
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();

            if (isset($debug)) {
                $messages[] = "About to print options.";
                $messages[] = print_r($options, true);
                $messages[] = "Finished printing options. About to print data
                    (only includes definition of options, not actual data).";
                $messages[] = print_r($data, true);
                $messages[] = "Finished printing data (includes changes just made to course).";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            // Key is the course format options themselves: for example - hiddensections, coursedisplay, coursetype, iscurriculum,
            // ispartofcurriculum, curriculums, and relatedcourses (which is an array and has special processing).
            foreach ($options as $key => $unused) {
                if (isset($debug)) {
                    $messages[] = "key (course format option) is :$key.";
                    $debug->logmessage($messages, 'detailed');
                    unset($messages);
                }

                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        // SWTC ********************************************************************************.
                        // Special case: If removing all related courses, $data['relatedcourses'] will NOT be set. Therefore,
                        // it really doesn't matter what the relatedcourses are in $oldcourse (because they will be removed).
                        // SWTC ********************************************************************************.
                        $data[$key] = $oldcourse[$key];
                    }
                }
            }

            // SWTC ********************************************************************************.
            // Get information for "curriculums". If "curriculums" did not exist, it would have been added
            // just above here.
            // Ignore if "ispartofcurriculum" is NOT set.
            // TODO: Dynamically disable curriculums select form element if "ispartofcurriculum" is NOT set.
            // If course format option does not exist in $data, do NOT zero out the value.
            // SWTC ********************************************************************************.
            if (array_key_exists('curriculums', $data)) {
                if (!empty($data['ispartofcurriculum'])) {
                    if (!empty($data['curriculums'])) {
                        if (is_array($data['curriculums'])) {
                            $data['curriculums'] = implode(', ', $data['curriculums']);
                        }
                    }
                } else {
                    // Some curriculums were selected, but the ispartofcurriculum flag was not set
                    // (this situation should not happen in the future).
                    $data['curriculums'] = 0;
                }
            }

            // Even though machinetypes is a course format option (and is saved as a course format option),
            // it must be handled differently because it is shown in the "General" section,
            // not the "Course format" section. Load form element value with 'machinetypes'
            // from database (if it exists). If course format option does not exist in $data,
            // do NOT zero out the value.
            if (array_key_exists('machinetypes', $data)) {
                $newtypes = $data['machinetypes'];

                $record = $DB->get_record('course_format_options', array('courseid' => $this->courseid, 'format' => $this->format,
                    'sectionid' => $sectionid, 'name' => 'machinetypes'));

                if ( !(empty($record))) {
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
            // If course format option does not exist in $data, do NOT zero out the value.
            if (array_key_exists('duration', $data)) {
                $duration = $data['duration'];

                $record = $DB->get_record('course_format_options',  array('courseid' => $this->courseid, 'format' => $this->format,
                    'sectionid' => $sectionid, 'name' => 'duration'));

                if ( !(empty($record))) {
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
     *
     * History:
     *
     * 10/23/20 - Initial writing.
     *
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
                                                         $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'local_swtc');
        }
        if (empty($editlabel)) {
            $title = get_section_name($section->course, $section);
            $editlabel = new lang_string('newsectionname', 'local_swtc', $title);
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
        $renderer = $PAGE->get_renderer('format_swtccustom');
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
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 *
 * History:
 *
 * 10/23/20 - Initial writing.
 *
 */
function format_swtccustom_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'swtccustom'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
