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
 * Renderer for outputting the swtcpractical course format.
 *
 * @package   format_swtcpractical
 * @copyright 2021 SWTC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 *
 * History:
 *
 * 04/13/21 - Initial writing.
 *
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');

use \format_swtcpractical\output\htmlpage;

/**
 * Basic renderer for topics format.
 *
 * @copyright 2021 SWTC
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_swtcpractical_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_swtcpractical_renderer::section_edit_controls() only displays the 'Set current section'
        // control when editing mode is on we need to be sure that the link 'Turn editing mode on' is available
        // for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'swtcpractical'));
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
     * History:
     *
     * 04/13/21 - Initial writing.
     *
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $CFG, $USER, $SESSION;

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Entering section_edit_control_items in renderer.php==77.enter===.";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "About to print section.";
            $messages[] = print_r($section, true);
            $messages[] = "Finished printing section.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        if (!$this->page->user_is_editing()) {
            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $debug->logmessage("Leaving section_edit_control_items in renderer.php==77.exit1===.", 'both');
            }
            // SWTC ********************************************************************************.
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "About to print section number==77.1===.";
            // Yes, $section->section is the section number (0, 1, 2, ...).
            $messages[] = "Section number is :$section->section.";
            $messages[] = "Finished printing section number==77.1===.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
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
            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "edit array_key_exists==77.4===.";
                $messages[] = "Section number is :$section->section.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                // SWTC ********************************************************************************.
                // print("key is :$key. action follows:</br>");
                // print_object($action);
                // SWTC ********************************************************************************.
                if ($key == "edit") {
                    $merged[$key] = $action;
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "Leaving section_edit_control_items in renderer.php==77.exit2===.";
                $debug->logmessage($messages, 'both');
                unset($messages);

                $messages[] = "About to print merged:";
                $messages[] = print_r($merged, true);
                $messages[] = "Finished printing merged.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.
            return $merged;
        } else {
            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "Leaving section_edit_control_items in renderer.php==77.exit3===.";
                $debug->logmessage($messages, 'both');
                unset($messages);

                $tmp = array();
                $tmp = array_merge($controls, $parentcontrols);
                $messages[] = "About to print merged (tmp):";
                $messages[] = print_r($tmp, true);
                $messages[] = "Finished printing merged (tmp).";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.
            return array_merge($controls, $parentcontrols);
        }
    }

    /**
     * Copied from course/format/renderer.php and modified for SWTC's use.
     *
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     *
     * History:
     *
     *  04/13/21 - Initial writing.
     *
     */
    protected function format_summary_text($section) {
        global $COURSE, $DB, $CFG, $USER, $SESSION;

        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $swtcuser = swtc_get_user([
            'userid' => $USER->id,
            'username' => $USER->username]);
        $debug = swtc_get_debug();

        // Other SWTC variables.
        // Load section number; only do all of our custom processor for section 0 ("Course overview" section).
        $sectionnumber = $section->section;

        // Course overview section will consist of these five strings followed by the text currently stored in course.summary.
        $headerstring1 = get_string('overview_coursetitle_line1_formatting', 'format_swtcpractical');
        $headerstring2 = get_string('overview_coursecode_line2_formatting', 'format_swtcpractical');
        $headerstring4 = get_string('overview_machinetypes_line4_formatting', 'format_swtcpractical');
        $headerstring5 = get_string('overview_duration_line5_formatting', 'format_swtcpractical');
        $headerstring6 = get_string('overview_heading_line6_formatting', 'format_swtcpractical');
        $headerstring7 = $COURSE->summary;
        $headerstringemptyline = '<br />';

        $machinetypes = '';
        $duration = '';
        // SWTC ********************************************************************************.

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Entering format_summary_text in swtcpracticalformat/renderer.php ===88.enter===.";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "Section data follows ===88.enter===.";
            $messages[] = print_r("section number is :$section->section.", true);
            $messages[] = "Finished printing section data ===88.enter===.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }

        // SWTC ********************************************************************************.
        // Main switch (only for section 0).
        // If Section 0 is empty (for example, when creating a new course by hand or if moving text from Section 0
        // to the "Course summary" field, this code will be skipped (because Section 0 does not exist).
        if ($sectionnumber === 0) {
            // SWTC ********************************************************************************.
            // Add "Course version", "Machine types", and "Duration" to "Course overview" section.
            //
            // First, 'Course version' field.
            // Load local variable with 'courseversion' from database (if it exists).
            if (isset($debug)) {
                $debug->logmessage(print_r($section, true), 'detailed');
            }

            // SWTC ********************************************************************************.
            // Next, 'Machine types' field.
            // Load local variable with 'machinetypes' from database (if it exists).
            $record = $DB->get_record('course_format_options',
                array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'machinetypes'));

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $debug->logmessage(print_r($record, true), 'detailed');
            }
            // SWTC ********************************************************************************.

            if (!(empty($record))) {
                $machinetypes = $record->value;
            } else {
                $machinetypes = '';
            }

            // SWTC ********************************************************************************.
            // Next, 'Duration' field.
            // Load local variable with 'duration' from database (if it exists).
            $record = $DB->get_record('course_format_options',
                array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'duration'));

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $debug->logmessage(print_r($record, true), 'detailed');
            }
            // SWTC ********************************************************************************.

            if (!(empty($record))) {
                $duration = $record->value;
            } else {
                $duration = '';
            }

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print machinetypes ===88.1===.";
                $messages[] = print_r("==>$machinetypes<==", true);
                $messages[] = "Finished printing machinetypes ===88.1===.";
                $messages[] = "About to print duration ===88.1===.";
                $messages[] = print_r("==>$duration<==", true);
                $messages[] = "Finished printing duration ===88.1===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }

            // SWTC ********************************************************************************.
            // Lock and load the course variables from $COURSE, $machinetypes, and $duration.
            $headerstring1 = str_replace("string-coursefullname", $COURSE->fullname, $headerstring1);
            $headerstring2 = str_replace("string-courseshortname", $COURSE->shortname, $headerstring2);
            $headerstring4 = str_replace("string-machinetypes", $machinetypes, $headerstring4);
            $headerstring5 = str_replace("string-duration", $duration, $headerstring5);
            // Remember! No substituting necessary for string 6 ($headerstring6) and string 7 was already loaded above.

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print custom strings AFTER substitution===88.2===.";
                $messages[] = "String 1 ==> $headerstring1";
                $messages[] = "String 2 ==> $headerstring2";
                $messages[] = "String 4 ==> $headerstring4";
                $messages[] = "String 5 ==> $headerstring5";
                $messages[] = "String 6 ==> $headerstring6";
                $messages[] = "String 7 ==> $headerstring7";
                $messages[] = "Finished printing custom strings AFTER substitution===88.2===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print summarytext BEFORE changing anything===88.3===.";
                $messages[] = print_r($summarytext, true);
                $messages[] = "Finished printing summarytext BEFORE changing anything===88.3===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.

            // Critial substitution. Make sure this is correct.
            $newsummarytext = $headerstring1;
            $newsummarytext .= $headerstring2;
            $newsummarytext .= $headerstring4;

            if (!(empty($duration))) {
                $newsummarytext .= $headerstring5;
            }

            $newsummarytext .= $headerstring6;
            $newsummarytext .= $headerstring7;

            // Add a couple of empty lines for spacing (between end of text and the first activity).
            $newsummarytext .= $headerstringemptyline;
            $newsummarytext .= $headerstringemptyline;

            // SWTC ********************************************************************************.
            if (isset($debug)) {
                $messages[] = "About to print newsummarytext AFTER changing===88.3===.";
                $messages[] = print_r($newsummarytext, true);
                $messages[] = "Finished printing newsummarytext AFTER changing===88.3===.";
                $debug->logmessage($messages, 'detailed');
                unset($messages);
            }
            // SWTC ********************************************************************************.

            // Finally substitute $newsummarytext for $summarytext.
            $summarytext = $newsummarytext;
        }
        // SWTC ********************************************************************************.

        $options = new stdClass();
        $options->noclean = true;
        $options->overflowdiv = true;

        // SWTC ********************************************************************************.
        $text = format_text($summarytext, $section->summaryformat, $options);

        // SWTC ********************************************************************************.
        if (isset($debug)) {
            $messages[] = "Leaving format_summary_text in swtcpracticalformat/renderer.php ===88.leaving===.";
            $debug->logmessage($messages, 'both');
            unset($messages);

            $messages[] = "About to print text that will be returned.";
            $messages[] = print_r($text, true);
            $messages[] = "Finished printing text that will be returned.";
            $debug->logmessage($messages, 'detailed');
            unset($messages);
        }
        // SWTC ********************************************************************************.
        return $text;
    }

    /**
     * Defer to template..
     *
     * @param grading_app $app - All the data to render the grading app.
     */
    public function render_htmlpage(htmlpage $app) {
        $context = $app->export_for_template($this);
        return $this->render_from_template('format_swtcpractical/htmlpage', $context);
    }
}
