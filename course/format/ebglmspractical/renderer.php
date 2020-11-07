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
 * Renderer for outputting the ebglmspractical course format.
 *
 * @package format_topics
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 *
 * @package   format_ebglmspractical
 * @copyright 2018 Lenovo EBG LMS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 *
 * Lenovo history:
 *
 * 11/05/18 - Initial writing; based on format_ebglmscustom version 2018083107.
 * 05/15/19 - Cleaned up some code.
 * 08/12/19 - Added course duration to course overview (and course format options).
 * 10/15/19 - Changed to new Lenovo EBGLMS classes and methods to load ebglms_user and debug.
 * 01/06/20 - In format_summary_text, changed 'duration' from PARAM_INT to PARAM_TEXT.
 *
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

// Lenovo ********************************************************************************.
// Include Lenovo EBGLMS user and debug functions.
// Lenovo ********************************************************************************.
require_once($CFG->dirroot.'/local/ebglms/lib/ebglms_userlib.php');

/**
 * Basic renderer for topics format.
 *
 * @copyright 2017 Lenovo EBG LMS
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_ebglmspractical_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_ebglmspractical_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'ebglmspractical'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     *
     * Lenovo history:
     *
     * 05/09/19 - Changed $controls settings.
     * 05/15/19 - Added this section.
     * 10/15/19 - Changed to new Lenovo EBGLMS classes and methods to load ebglms_user and debug.
     *
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE, $CFG, $USER, $SESSION;       // Lenovo

        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS ebglms_user and debug variables.
        $ebglms_user = ebglms_get_user($USER);
        $debug = ebglms_get_debug();
        // Lenovo ********************************************************************************.
        
        // Lenovo *******************************************************************************
        if (isset($debug)) {
            $messages[] = "Entering section_edit_control_items in renderer.php==77.enter===.";
            debug_logmessage($messages, 'both');
            unset($messages);
            
            // $section = $modinfo->get_section_info(0);
            // $generalsection = $node->get($section->id, navigation_node::TYPE_SECTION);
            $messages[] = "About to print section.";
            $messages[] = print_r($section, true);
            $messages[] = "Finished printing section.";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }
        // Lenovo *******************************************************************************

        if (!$PAGE->user_is_editing()) {
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                debug_logmessage("Leaving section_edit_control_items in renderer.php==77.exit1===.", 'both');
            }
            // Lenovo *******************************************************************************
            return array();
        }

        $coursecontext = context_course::instance($course->id);
        
        // Lenovo *******************************************************************************
        if (isset($debug)) {
            $messages[] = "About to print section number==77.1===.";
            // Yes, $section->section is the section number (0, 1, 2, ...).
            $messages[] = "Section number is :$section->section.";
            $messages[] = "Finished printing section number==77.1===.";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }
        // Lenovo *******************************************************************************

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        // $isstealth = $section->section > $course->numsections;       // Lenovo
        $controls = array();
        // if (!$isstealth && $section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {     // Lenovo
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {                                   // Lenovo
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));       // 05/09/19
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));              // 05/09/19
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "edit array_key_exists==77.4===.";
                $messages[] = "Section number is :$section->section.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                // Lenovo *******************************************************************************
                // print("key is :$key. action follows:</br>");
                // print_object($action);
                // Lenovo *******************************************************************************
                if ($key == "edit") {
                    // Lenovo *******************************************************************************
                    if ($section->section === 0) {
                        // print("In section 0, found edit menu, about to remove edit key==77.5===.</br>");
                        // $action['attr']['title'] = "this is the new title";
                        // print_object($action);
                    }
                    // Lenovo *******************************************************************************
                    $merged[$key] = $action;
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "Leaving section_edit_control_items in renderer.php==77.exit2===.";
                debug_logmessage($messages, 'both');
                unset($messages);
                
                $messages[] = "About to print merged:";
                $messages[] = print_r($merged, true);
                $messages[] = "Finished printing merged.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            return $merged;
        } else {
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "Leaving section_edit_control_items in renderer.php==77.exit3===.";
                debug_logmessage($messages, 'both');
                unset($messages);
                
                $tmp = array();
                $tmp = array_merge($controls, $parentcontrols);
                $messages[] = "About to print merged (tmp):";
                $messages[] = print_r($tmp, true);
                $messages[] = "Finished printing merged (tmp).";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            return array_merge($controls, $parentcontrols);
        }
    }
    
    /**
     * Copied from course/format/renderer.php and modified for Lenovo's use.
     *
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     *
     * Lenovo history:
     *
     *  04/11/17 - Added courseversion and machinetypes field to form.
     *  04/17/17 - Removed (or commented-out) courseversion (not implementing now).
     * 08/12/19 - Added course duration to course overview (and course format options).
     * 10/15/19 - Changed to new Lenovo EBGLMS classes and methods to load ebglms_user and debug.
     * 01/06/20 - In format_summary_text, changed 'duration' from PARAM_INT to PARAM_TEXT.
     *
     */
    protected function format_summary_text($section) {
        global $COURSE, $DB, $CFG, $USER, $SESSION;        // Lenovo

        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);
                
        // Lenovo ********************************************************************************.
        // Lenovo EBGLMS ebglms_user and debug variables.
        $ebglms_user = ebglms_get_user($USER);
        $debug = ebglms_get_debug();
        
        // Other Lenovo variables.
        // Load section number; only do all of our custom processor for section 0 ("Course overview" section).
        $section_number = $section->section;
        
        // "Course overview" section will consist of these five strings followed by the text currently stored in course.summary.
        $header_string_1 = get_string('overview_coursetitle_line1_formatting', 'format_ebglmspractical');
        $header_string_2 = get_string('overview_coursecode_line2_formatting', 'format_ebglmspractical');        
        // 04/17/17 - Removed (or commented-out) courseversion (not implementing now).
        // $header_string_3 = get_string('overview_currentversion_line3_formatting', 'format_ebglmspractical');        
        $header_string_4 = get_string('overview_machinetypes_line4_formatting', 'format_ebglmspractical');
        // 08/12/19
        $header_string_5 = get_string('overview_duration_line5_formatting', 'format_ebglmspractical');
        $header_string_6 = get_string('overview_heading_line6_formatting', 'format_ebglmspractical');
        $header_string_7 = $COURSE->summary;
        $header_string_emptyline = '<br />';
        
        // For searching for current version of course.
        // 04/17/17 - Removed (or commented-out) courseversion (not implementing now).
        // $overview_version = get_string('overview_version', 'format_ebglmspractical');
        
        // Initialize (in case they don't exist yet). Done below also.
        // $courseversion = '1.0';
        $machinetypes = '';
        $duration = '';
        
        // Lenovo ********************************************************************************.
        
        // Lenovo *******************************************************************************
        if (isset($debug)) {
            $messages[] = "Entering format_summary_text in ebglmspracticalformat/renderer.php ===88.enter===.";
            debug_logmessage($messages, 'both');
            unset($messages);
            
            $messages[] = "Section data follows ===88.enter===.";
            // print_object($section);
            $messages[] = print_r("section number is :$section->section.", true);
            $messages[] = "Finished printing section data ===88.enter===.";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }

        // Lenovo *******************************************************************************
        // Main switch (only for section 0).
        // If Section 0 is empty (for example, when creating a new course by hand or if moving text from Section 0
        // to the "Course summary" field, this code will be skipped (because Section 0 does not exist).
        if ($section_number ===0) {
            // Lenovo *******************************************************************************
            // Add "Course version", "Machine types", and "Duration" to "Course overview" section.
            //
            // First, 'Course version' field.
            // Load local variable with 'courseversion' from database (if it exists).
            // 04/17/17 - Removed (or commented-out) courseversion (not implementing now).
            // $record = $DB->get_record('course_format_options',  array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'courseversion'));
            
            if (isset($debug)) {
                debug_logmessage(print_r($section, true), 'detailed');
            }
            // if( !(empty($record))) {
            //     $courseversion = $record->value;
            // } else {
                // Ugly hack follows: Since the course "Current version" field exists in the "Course overview" section, but NOT in the database (yet),
                // attempt to load the current version from the text in $summarytext. An example follows:
                // <b>Current version:</b> 1.0<br />
                // This might not show well the first time, but since "machinetypes" have to be created and "courseversion" is a required field,
                // it will only show strangly for ONLY the first time the course is viewed.
                // $courseversion = substr($summarytext, strpos($summarytext, $overview_version) + strlen($overview_version), 20);
                
                //if (isset($debug)) {
                //    print("</br>About to print summarytext</br>");
                //    print($summarytext);                    
                //    print("</br>About to print courseversion</br>");
                //    print("courseversion is :=>$courseversion<=");
                //    die();
                //}
            //}

            // Lenovo *******************************************************************************
            // Next, 'Machine types' field.
            // Load local variable with 'machinetypes' from database (if it exists).
            $record = $DB->get_record('course_format_options',  array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'machinetypes'));
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                debug_logmessage(print_r($record, true), 'detailed');
            }
            // Lenovo *******************************************************************************
            
            if( !(empty($record))) {
                $machinetypes = $record->value;
            } else {
                $machinetypes = '';
            }
            
            // Lenovo *******************************************************************************
            // Next, 'Duration' field.
            // Load local variable with 'duration' from database (if it exists).
            $record = $DB->get_record('course_format_options',  array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'duration'));
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                debug_logmessage(print_r($record, true), 'detailed');
            }
            // Lenovo *******************************************************************************
            
            if( !(empty($record))) {
                $duration = $record->value;
            } else {
                $duration = '';
            }
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "About to print machinetypes ===88.1===.";
            //    print_r("==>$courseversion<==");
                $messages[] = print_r("==>$machinetypes<==", true);
                $messages[] = "Finished printing machinetypes ===88.1===.";
                $messages[] = "About to print duration ===88.1===.";
            //    print_r("==>$courseversion<==");
                $messages[] = print_r("==>$duration<==", true);
                $messages[] = "Finished printing duration ===88.1===.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            
            // Lenovo *******************************************************************************
            // Lock and load the course variables from $COURSE, $machinetypes, and $duration.
            $header_string_1 = str_replace("string-coursefullname", $COURSE->fullname, $header_string_1);
            $header_string_2 = str_replace("string-courseshortname", $COURSE->shortname, $header_string_2);
            
            // 04/17/17 - Removed (or commented-out) courseversion (not implementing now).
            // $header_string_3 = str_replace("string-currentversion", $courseversion, $header_string_3);
            
            $header_string_4 = str_replace("string-machinetypes", $machinetypes, $header_string_4);
            
            // 08/12/19
            $header_string_5 = str_replace("string-duration", $duration, $header_string_5);     // 01/06/20
            // $header_string_5 = sprintf($header_string_5, $duration);     // 01/06/20
            // Remember! No substituting necessary for string 6 ($header_string_6) and string 7 was already loaded above.
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "About to print custom strings AFTER substitution===88.2===.";
                $messages[] = "String 1 ==> $header_string_1";
                $messages[] = "String 2 ==> $header_string_2";
            //    print("String 3 ==> $header_string_3");
                $messages[] = "String 4 ==> $header_string_4";
                $messages[] = "String 5 ==> $header_string_5";
                $messages[] = "String 6 ==> $header_string_6";
                $messages[] = "String 7 ==> $header_string_7";
                $messages[] = "Finished printing custom strings AFTER substitution===88.2===.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "About to print summarytext BEFORE changing anything===88.3===.";
                $messages[] = print_r($summarytext, true);
                $messages[] = "Finished printing summarytext BEFORE changing anything===88.3===.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            
            // Critial substitution. Make sure this is correct.
            $newsummarytext = $header_string_1;
            $newsummarytext .= $header_string_2;            
            // 04/17/17 - Removed (or commented-out) courseversion (not implementing now).
            // $newsummarytext .= $header_string_3;            
            $newsummarytext .= $header_string_4;
            // 08/12/19 - Don't show course duration IF no duration is set. All courses will (eventually have a duration set).
            if( !(empty($duration))) {
                $newsummarytext .= $header_string_5;
            }
            $newsummarytext .= $header_string_6;
            $newsummarytext .= $header_string_7;
            
            // Add a couple of empty lines for spacing (between end of text and the first activity).
            $newsummarytext .= $header_string_emptyline;
            $newsummarytext .= $header_string_emptyline;
            
            // Lenovo *******************************************************************************
            if (isset($debug)) {
                $messages[] = "About to print newsummarytext AFTER changing===88.3===.";
                $messages[] = print_r($newsummarytext, true);
                $messages[] = "Finished printing newsummarytext AFTER changing===88.3===.";
                debug_logmessage($messages, 'detailed');
                unset($messages);
            }
            // Lenovo *******************************************************************************
            
            // Finally substitute $newsummarytext for $summarytext.
            $summarytext = $newsummarytext;
        }
        // Lenovo - End of Section 0 ************************************************************
        
        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;
        
        // Lenovo *******************************************************************************
        $text = format_text($summarytext, $section->summaryformat, $options);
        
        // Lenovo *******************************************************************************
        if (isset($debug)) {
            $messages[] = "Leaving format_summary_text in ebglmspracticalformat/renderer.php ===88.leaving===.";
            debug_logmessage($messages, 'both');
            unset($messages);
            
            $messages[] = "About to print text that will be returned.";
            $messages[] = print_r($text, true);
            $messages[] = "Finished printing text that will be returned.";
            debug_logmessage($messages, 'detailed');
            unset($messages);
        }
        // Lenovo *******************************************************************************
        return $text;
    }
}
