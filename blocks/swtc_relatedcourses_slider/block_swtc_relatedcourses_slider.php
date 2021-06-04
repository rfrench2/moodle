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
 *
 * SWTC related course slider block.
 *
 * Version details
 *
 * @package   block_swtc_relatedcourses_slider
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/24/21 - Initial writing; based off of Adaptable block_course_slider.
 *
 */

defined('MOODLE_INTERNAL') || die();

// SWTC ********************************************************************************.
// Include SWTC LMS user and debug functions.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/swtc_userlib.php');
require_once($CFG->dirroot . '/blocks/swtc_relatedcourses_slider/lib.php');
// SWTC ********************************************************************************.
// Include SWTC relatedcourseslib.
// SWTC ********************************************************************************.
require_once($CFG->dirroot.'/local/swtc/lib/relatedcourseslib.php');

/**
 * Related course slider block implementation class.
 *
 * @package   block_swtc_relatedcourses_slider
 * @copyright  2021 SWTC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * History:
 *
 * 05/24/21 - Initial writing; based off of Adaptable block_course_slider.
 *
 */
class block_swtc_relatedcourses_slider extends block_base {

    /**
     * Adds title to block instance.
     */
    public function init() {
        $this->title = get_string ( 'block_swtc_relatedcourses_slider', 'block_swtc_relatedcourses_slider');
    }

    /**
     * Calls functions to load js and css and returns block instance content.
     */
    public function get_content() {
        return $this->content;
    }

    /**
     * Generates block instance content.
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function specialization() {
        global $DB, $CFG, $COURSE;
        require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

        // SWTC ********************************************************************************.
        // SWTC swtcuser and debug variables.
        $debug = swtc_get_debug();

        // Other SWTC variables.
        $relatedcourses = array();
        // SWTC ********************************************************************************.

        if (isset($debug)) {
            $messages[] = "In /blocks/swtc_relatedcourses_slider/block_swtc_relatedcourses_slider.php ===specialization.enter===";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        $content = '';
        $this->content = new stdClass ();
        $this->content->text = '';

        // SWTC ********************************************************************************.
        // We're going to set the title later based on where the block is and the user's access type.
        // SWTC ********************************************************************************.

        // Initiate caching.
        $cache = cache::make('block_swtc_relatedcourses_slider', 'blockdata');

        // Get system time.
        $timenow = time();

        // Get instance ID.
        $instanceid = $this->instance->id;

        // Add relatedcourseslider ID in configuration page.
        $instancecssid = '#relatedcourseslider' . $instanceid;

        if (isset($this->config->instancecssid)) {
            $this->config->instancecssid = $instancecssid;
        }

        $timetolive = $timenow;

        // This if statement allows users to reset cache by setting it to 0.
        if (isset($this->config->cachetime)) {
            $cachetime = $this->config->cachetime;
            // If timetolive has not passed yet, return the cached block content.
            if (intval($cachetime) != 0) {
                if ($timenow <= $cache->get ('timetolive' . $instanceid)) {
                    $this->content = $cache->get ('blockcontent' . $instanceid);
                    return;
                }
            }

            // Prepare new timetolive.
            $timetolive = $timenow + intval($cachetime);
        }

        // Prepare new content.
        $renderer = $this->page->get_renderer ('block_swtc_relatedcourses_slider');
        $displayoptionshtml = new stdClass();
        $displayoptionscss = new stdClass();

        // SWTC *******************************************************************************.
        // Set the title of the block.
        // SWTC *******************************************************************************.
        $this->title = get_string('title_typical_user', 'block_swtc_relatedcourses_slider', sprintf('%s', $COURSE->shortname));

        // SWTC *******************************************************************************.
        // Get all the related courses.
        // SWTC *******************************************************************************.
        $relatedcourses = relatedcourses_get_courses($this->page->course->id);

        // SWTC *******************************************************************************.
        // Get all the courses the user has access to (based on current permissions).
        // SWTC *******************************************************************************.
        $allcourses = core_course_category::get(0, IGNORE_MISSING, true)->get_courses(array('recursive' => true));

        // Get all the course id's for all the courses.
        foreach ($allcourses as $course) {
            $allcoursesids[$course->id] = $course->id;
        }

        // SWTC *******************************************************************************.
        // If a related course is NOT in the course id's the user has access to, remove it from being viewed.
        // Reminder: Don't remove it from the course format options (other user types should be able to view it).
        // SWTC *******************************************************************************.
        foreach ($relatedcourses as $courseid) {
            if (!array_key_exists($courseid, $allcoursesids)) {
                unset($relatedcourses[$courseid]);
            }
        }

        if (isset($debug)) {
            $messages[] = "In /blocks/swtc_relatedcourses_slider/block_swtc_relatedcourses_slider.php ===specialization.3===";
            $debug->logmessage($messages, 'both');
            unset($messages);
        }

        // SWTC *******************************************************************************.
        // Always overlay the relatedcourses used by this plugin with our relatedcourses.
        // if (isset($this->config->relatedcourses) && ! empty ( $this->config->relatedcourses)) {
        // SWTC *******************************************************************************.
        if ((isset($relatedcourses) && !empty($relatedcourses))) {

            $coursesorder = $relatedcourses;

            $relatedcourses = $DB->get_records_list('course', 'id', $relatedcourses);

            // Add courses to the content in html format.

            // Prepare CSS display options.
            $displayoptionscss->backgroundcolor = get_config('block_swtc_relatedcourses_slider', 'backgroundcolor');

            if (empty($displayoptionscss->backgroundcolor)) {
                $displayoptionscss->backgroundcolor = $defaultblocksettings['backgroundcolor'];
            }

            $displayoptionscss->color = get_config('block_swtc_relatedcourses_slider', 'color');

            if ((empty($displayoptionscss->color))) {
                $displayoptionscss->color = $defaultblocksettings['color'];
            }

            if (isset($this->config->borderradius)) {
                $displayoptionscss->borderradius = $this->config->borderradius;
            } else {
                $displayoptionscss->borderradius = $defaultblocksettings['borderradius'];
            }

            if (isset($this->config->borderstyle)) {
                $displayoptionscss->borderstyle = $this->config->borderstyle;
            } else {
                $displayoptionscss->borderstyle = $defaultblocksettings['borderstyle'];
            }

            if (isset($this->config->borderwidth)) {
                $displayoptionscss->borderwidth = $this->config->borderwidth;
            } else {
                $displayoptionscss->borderwidth = $defaultblocksettings['borderwidth'];
            }

            if (isset($this->config->imagedivheight)) {
                $displayoptionscss->imagedivheight = intval ( $this->config->imagedivheight);
            } else {
                $displayoptionscss->imagedivheight = $defaultinstancesettings['imagedivheight'];
            }

            if (empty($displayoptionscss->imagedivheight)) {
                $displayoptionscss->imagedivheight = $defaultinstancesettings['imagedivheight'];
            } else {
                $displayoptionscss->imagedivheight = $displayoptionscss->imagedivheight;
            }

            if (empty($displayoptionscss->navigationarrownext)) {
                $displayoptionscss->navigationarrownext = $defaultinstancesettings['navigationarrownext'];
            }

            $displayoptionscss->navigationarrowprev = $fontawesomeiconunicodes
                    [$fontawesomematchprev[$displayoptionscss->navigationarrownext]];
            $displayoptionscss->navigationarrownext = $fontawesomeiconunicodes[$displayoptionscss->navigationarrownext];

            // Prepare HTML display options.
            if (isset($this->config->coursenameflag)) {
                $displayoptionshtml->coursenameflag = (($this->config->coursenameflag == $hiddenvisible['Visible']) ? 1 : 0);
            } else {
                $displayoptionshtml->coursenameflag = ($defaultinstancesettings
                       ['coursenameflag'] == $hiddenvisible['Visible'] ? 1 : 0);
            }

            if (isset($this->config->coursesummaryflag)) {
                $displayoptionshtml->coursesummaryflag = (($this->config->coursesummaryflag == $hiddenvisible['Visible']) ? 1 : 0);
            } else {
                $displayoptionshtml->coursesummaryflag = ($defaultinstancesettings
                       ['coursesummaryflag'] == $hiddenvisible['Visible'] ? 1 : 0);
            }

            if (isset($this->config->numberofslides)) {
                $displayoptionshtml->numberofslides = $this->config->numberofslides;
            } else {
                $displayoptionshtml->numberofslides = $defaultinstancesettings['numberofslides'];
            }

            // SWTC *******************************************************************************.
            // Added "verticalflag" (True for vertical or False for horizontal).
            // SWTC *******************************************************************************.
            if (isset($this->config->verticalflag)) {
                $displayoptionshtml->verticalflag = (($this->config->verticalflag == 'ON') ? 1 : 0);
            } else {
                $displayoptionshtml->verticalflag = ($defaultinstancesettings['verticalflag'] == 'ON' ? 1 : 0);
            }

            if (isset($this->config->navigationgalleryflag)) {
                $displayoptionshtml->navigationgalleryflag = (($this->config->navigationgalleryflag == $onoff['ON']) ? 1 : 0);
            } else {
                $displayoptionshtml->navigationgalleryflag = ($defaultinstancesettings
                       ['navigationgalleryflag'] == $onoff['ON'] ? 1 : 0);
            }

            if (isset($this->config->navigationoptions)) {
                $displayoptionshtml->navigationoptions = $this->config->navigationoptions;
            } else {
                $displayoptionshtml->navigationoptions = $defaultinstancesettings['navigationoptions'];
            }

            if (isset($this->config->centermodeflag)) {
                $displayoptionshtml->centermodeflag = (($this->config->centermodeflag == 'ON') ? 1 : 0);
            } else {
                $displayoptionshtml->centermodeflag = ($defaultinstancesettings['centermodeflag'] == 'ON' ? 1 : 0);
            }

            if (isset($this->config->autoplayspeed)) {
                $displayoptionshtml->autoplayspeed = intval($this->config->autoplayspeed);
            } else {
                $displayoptionshtml->autoplayspeed = $defaultinstancesettings['autoplayspeed'];
            }

            if (empty($displayoptionshtml->autoplayspeed)) {
                $displayoptionshtml->autoplayspeed = $defaultinstancesettings['autoplayspeed']
                    * BLOCK_RELATEDCOURSESLIDER_MILLISECONDS;
            } else {
                // 07/24/19 SWTC
                // $displayoptionshtml->autoplayspeed = $this->config->autoplayspeed * BLOCK_RELATEDCOURSESLIDER_MILLISECONDS;
                $displayoptionshtml->autoplayspeed = $displayoptionshtml->autoplayspeed * BLOCK_RELATEDCOURSESLIDER_MILLISECONDS;
            }

            // Generate relatedcoursesliders based on configuration settings.
            $content .= $renderer->block_swtc_relatedcourses_slider_relatedcourseslider_as_html($relatedcourses, $coursesorder,
                        $instanceid, $displayoptionshtml);

            // Add instance css.
            if (isset($this->config->instancecssid)) {
                $this->config->instancecssid = $instancecssid;
            }

            $instancecss = $renderer->block_swtc_relatedcourses_slider_add_instance_css($instancecssid, $displayoptionscss);
            // Add content found in instance custom css textarea.
            if (isset($this->config->instancecustomcsstextarea)) {
                $instancecss .= $this->config->instancecustomcsstextarea;
            }

            $content .= $renderer->block_swtc_relatedcourses_slider_instance_css_as_html($instancecss);

            // Notice: js and css files do not load for all instances if are not added in the content of the block as done below.

            // Load css files.
            $content .= $this->block_swtc_relatedcourses_slider_load_css();

            // Load js files.
            $content .= $this->block_swtc_relatedcourses_slider_load_js();
        }

        $this->content->text .= $content;

        $cache->set('timetolive' . $instanceid, $timetolive);
        $cache->set('blockcontent' . $instanceid, $this->content);
    }

    /**
     * Allows multiple instances of the block.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables block instnace configuration.
     */
    public function has_config() {
        return true;
    }

    /**
     * Core function, specifies where the block can be used.
     * @return array
     */
    public function applicable_formats() {
        // This block should NOT be placed on the site home pge.
        return array('course-view' => true, 'site-index' => false);
    }

    /**
     * Makes block instnace header visible.
     */
    public function hide_header() {
        return false;
    }

    /**
     * Calls functions to load slick slider and course slider css.
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    private function block_swtc_relatedcourses_slider_load_css() {
        global $CFG;
        require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

        $renderer = $this->page->get_renderer('block_swtc_relatedcourses_slider');

        $css = '';

        // Load slick slider css.
        $css .= $renderer->block_swtc_relatedcourses_slider_css_file_as_html($defaultfilerelativepaths['slickcss']);
        $css .= $renderer->block_swtc_relatedcourses_slider_css_file_as_html ($defaultfilerelativepaths['slickthemecss']);

        // Load course slider css.
        $css .= $renderer->block_swtc_relatedcourses_slider_css_file_as_html($defaultfilerelativepaths['relatedcourseslidercss']);
        $css .= $renderer->block_swtc_relatedcourses_slider_css_file_as_html($defaultfilerelativepaths['fontawesomecss']);
        $css .= $renderer->block_swtc_relatedcourses_slider_css_file_as_html(
            get_config('block_swtc_relatedcourses_slider', 'customcssfile'));
        return $css;
    }

    /**
     * Calls functions to load slick slider and course slider js.
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    private function block_swtc_relatedcourses_slider_load_js() {
        global $CFG;
        require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

        $renderer = $this->page->get_renderer('block_swtc_relatedcourses_slider');

        $js = '';

        // Load slick slider js.
        $js .= $renderer->block_swtc_relatedcourses_slider_js_file_as_html($defaultfilerelativepaths['slickjs']);

        // Load course slider css.
        $js .= $renderer->block_swtc_relatedcourses_slider_js_file_as_html($defaultfilerelativepaths['fontawesomejs']);
        $js .= $renderer->block_swtc_relatedcourses_slider_js_file_as_html($defaultfilerelativepaths['relatedcoursesliderjs']);
        $customjs = get_config('block_swtc_relatedcourses_slider', 'customjsfile');

        if ($customjs) {
            $js .= $renderer->block_swtc_relatedcourses_slider_js_file_as_html($customjs);
        }

        return $js;
    }
}
