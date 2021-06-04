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
 * Renderer definitions for SWTC related course slider block.
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
defined ( 'MOODLE_INTERNAL' ) || die ();

/**
 * Related course slider block renderer implementation.
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
class block_swtc_relatedcourses_slider_renderer extends plugin_renderer_base {

    /**
     * Returns the css file in link tag.
     *
     * @param string $filerelativepath
     * @return string
     */
    public function block_swtc_relatedcourses_slider_css_file_as_html($filerelativepath) {
        global $CFG;
        return '<link rel="stylesheet" type="text/css" href="' . new moodle_url ( $CFG->wwwroot . $filerelativepath ) . '" />';
    }

    /**
     * Returns the js file in script tag.
     *
     * @param string $filerelativepath
     * @return string
     */
    public function block_swtc_relatedcourses_slider_js_file_as_html($filerelativepath) {
        global $CFG;
        return '<script type="text/javascript" src="' . new moodle_url ( $CFG->wwwroot . $filerelativepath ) . '"></script>';
    }

    /**
     * Returns the course slider and the navigation gallery css in style tag.
     *
     * @param string $instancecss
     * @return string
     */
    public function block_swtc_relatedcourses_slider_instance_css_as_html($instancecss) {
        return html_writer::tag ( 'style', $instancecss );
    }

    /**
     * Returns the course slider and the navigation gallery css.
     *
     * @param string   $instancecssid
     * @param stdClass $displayoptionscss
     * @return string
     *
     * History:
     *
     * 08/07/19 - Added this header; changed "courselider" to "relatedcourseslider".
     *
     */
    public function block_swtc_relatedcourses_slider_add_instance_css($instancecssid, $displayoptionscss) {
        $instancecss = '';

        $backgroundcolorcss = 'background-color:' . $displayoptionscss->backgroundcolor . ';';

        $colorcss = 'color:' . $displayoptionscss->color . ';';

        $bordercolorcsscourse = 'border-color:' . $displayoptionscss->backgroundcolor . ';';
        $bordercolorcssname = 'border-color:' . $displayoptionscss->color . ';';

        $borderradiuscss = 'border-radius:' . $displayoptionscss->borderradius . ';';
        $borderstylecss = 'border-style:' . $displayoptionscss->borderstyle . ';';
        $borderwidthcss = 'border-width:' . $displayoptionscss->borderwidth . ';';

        $imagedivheightcss = 'height:' . $displayoptionscss->imagedivheight . 'px;';
        $imagedivheightgallerycss = 'height:' . ($displayoptionscss->imagedivheight / 2) . 'px;';

        $darkerbackgroundcolor = $this->block_swtc_relatedcourses_slider_alter_brightness ( $displayoptionscss->backgroundcolor, - 50 );

        $backgroundcolorcssbutton = 'background-color:' . $darkerbackgroundcolor . ';';

        $boxshadowcss = 'box-shadow: 0.06em 0.06em 0.06em 0.06em ' . $darkerbackgroundcolor . ';';

        // Add css image height.
        $instancecss .= "\n" . $instancecssid . ' .relatedcourseslider-course-image-div{' . $imagedivheightcss . '}';
        $instancecss .= "\n" . $instancecssid . '-nav' . ' .relatedcourseslider-course-image-div{' . $imagedivheightgallerycss . '}';

        // Add css for slick-arrow.
        $instancecss .= "\n" . $instancecssid . ' .slick-arrow{' . $backgroundcolorcssbutton . '}';
        $instancecss .= "\n" . $instancecssid . '-nav' . ' .slick-arrow{' . $backgroundcolorcssbutton . '}';

        // Add css for .slick-prev:before, .slick-next:before.
        $fontfamily = 'font-family: FontAwesome;';
        $navigationarrowprevcss = 'content:\'\\' . $displayoptionscss->navigationarrowprev . '\';';
        $navigationarrownextcss = 'content:\'\\' . $displayoptionscss->navigationarrownext . '\';';
        $instancecss .= "\n" . $instancecssid . ' .slick-prev:before{' . $fontfamily . $navigationarrowprevcss . ';}';
        $instancecss .= "\n" . $instancecssid . ' .slick-next:before{' . $fontfamily . $navigationarrownextcss . ';}';
        $instancecss .= "\n" . $instancecssid . '-nav' . ' .slick-prev:before{' . $fontfamily . $navigationarrowprevcss . ';}';
        $instancecss .= "\n" . $instancecssid . '-nav' . ' .slick-next:before{' . $fontfamily . $navigationarrownextcss . ';}';

        // Add css for relatedcourseslider and relatedcourseslider-nav border color. Leave it to user to make it visible
        // by editing the custom css of the instance.
        $instancecss .= "\n" . $instancecssid . '{' . $bordercolorcsscourse . '}';
        $instancecss .= "\n" . $instancecssid . '-nav' . '{' . $bordercolorcsscourse . '}';

        // Add css for relatedcourseslider-course,relatedcourseslider-course:hover,relatedcourseslider-course-nav.
        $instancecoursecss = $bordercolorcsscourse . $borderradiuscss . $borderstylecss . $borderwidthcss . $boxshadowcss;
        $instancecss .= "\n" . $instancecssid . ' .relatedcourseslider-course{' . $instancecoursecss . '}';
        $instancecss .= "\n" . $instancecssid . ' .relatedcourseslider-course:hover{' . $backgroundcolorcss . '}';
        $instancecss .= "\n" . $instancecssid . '-nav' . ' .relatedcourseslider-course-nav{' . $instancecoursecss . '}';

        // Add css for relatedcourseslider-course-name.
        $instancenamecss = $backgroundcolorcss . $bordercolorcssname . $colorcss . $borderradiuscss .
                $borderstylecss . $borderwidthcss;
        $instancecss .= "\n" . $instancecssid . ' .relatedcourseslider-course-name{' . $instancenamecss . '}';

        // Add css for relatedcourseslider-course-summary.
        $instancesummarycss = $colorcss;
        $instancecss .= "\n" . $instancecssid . ' .relatedcourseslider-course-summary{' . $instancesummarycss . '}';

        // print_object($instancecss);

        return $instancecss;
    }

    /**
     * Returns the course slider and the navigation gallery one after the other in division tag.
     *
     * @param array        $mappedcourses
     * @param array        $coursesorder
     * @param string       $instancecssid
     * @param stdClass     $displayoptionshtml
     * @return string
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function block_swtc_relatedcourses_slider_relatedcourseslider_as_html($mappedcourses, $coursesorder, $instancecssid, $displayoptionshtml) {
        global $CFG, $COURSE, $USER;
        $relatedcourseslider = '';
        $relatedcourseslidernav = '';

        $coursehtml = '';
        $coursenavhtml = '';

        $coursepictures = '';
        $coursesummary = '';
        $summary_trunc_length = 200;
        $coursename = '';
        $courseurl = '';
        $coursecount = 0;

        foreach ($coursesorder as $id) {
            if (array_key_exists ( $id, $mappedcourses )) {
                $course = $mappedcourses [$id];

                $coursehtml = '';
                $coursenavhtml = '';
                $clickparams = array();

                $clickparams['action'] = 'click';
                $clickparams['type'] = 'related';
                $clickparams['parentcourseid'] = $COURSE->id;
                $clickparams['clickedcourseid'] = $id;                      // 08/14/19
                $clickparams['clickeduserid'] = $USER->id;

                // For debugging.
                // print_object($clickparams);
                $swtc = base64_encode(json_encode((object)$clickparams));
                // print_object($swtc);

                $courseid = 'relatedcourseslidercourse' . $instancecssid . $coursecount;
                $courseurl = new moodle_url ( '/course/view.php', array (
                        'id' => $course->id,
                        'swtc' => $swtc,
                ) );

                // $course = new course_in_list ( $course );        // Moodle 3.6
                $course = new \core_course_list_element($course);

                // Add Course overview images in content.
                $coursepictures = $this->block_swtc_relatedcourses_slider_pictures_as_html ( $course );
                $coursehtml .= $coursepictures;
                if ($displayoptionshtml->navigationgalleryflag) {
                    $coursenavhtml .= $coursepictures;
                }

                // Add course summary in content.
                if ($displayoptionshtml->coursesummaryflag && $course->has_summary ()) {
                    $coursesummary = strip_tags ( $course->summary );

                    // SWTC ********************************************************************************.
                    // If for some reason the length requested is equal to or is greater than the current length
                    // of the summary, return the entire summary. If not, return only what was requested.
                    // SWTC ********************************************************************************.
                    $sumlength = core_text::strlen($coursesummary);
                    if ($summary_trunc_length >= $sumlength) {
                        // Leave $coursesummary as it is.
                    } else {
                        // shorten_text is in /lib/moodlelib.php.
                        $coursesummary = shorten_text($coursesummary, $summary_trunc_length);
                        // 09/20/19 - Added "read more" control.
                    }
                    // print_object($coursesummary);
                    // $coursesummary = $course->summary;

                    $coursehtml .= $this->block_swtc_relatedcourses_slider_summary_as_html ( $coursesummary );
                }

                // Add course name in content.
                if ($displayoptionshtml->coursenameflag) {
                    $coursename = strip_tags ( $course->fullname );
                    $coursename = $course->shortname . ' ' . $coursename;

                    $coursehtml .= $this->block_swtc_relatedcourses_slider_name_as_html ( $coursename );
                    if ($displayoptionshtml->navigationgalleryflag) {
                        $coursenavhtml .= $this->block_swtc_relatedcourses_slider_name_nav_as_html ( $coursename, $courseurl );
                    }
                }

                $coursehtml = html_writer::tag ( 'div', $coursehtml, array (
                        'class' => 'relatedcourseslider-course',
                        'id' => $courseid
                ) );
                if ($displayoptionshtml->navigationgalleryflag) {
                    $coursenavhtml = html_writer::tag ( 'div', $coursenavhtml, array (
                            'class' => 'relatedcourseslider-course-nav'
                    ) );
                }

                // Enclose the course in anchor.
                $coursehtml = html_writer::link ( $courseurl, $coursehtml, array (
                        'class' => 'relatedcourseslider-course-anchor'
                ) );

                $coursecount ++;

                $relatedcourseslider .= $coursehtml;
                if ($displayoptionshtml->navigationgalleryflag) {
                    $relatedcourseslidernav .= $coursenavhtml;
                }
            }
        }

        // print_object($relatedcourseslider);

        $relatedcourseslider = html_writer::tag ( 'div', $relatedcourseslider, array (
                'class' => 'relatedcourseslider',
                'id' => 'relatedcourseslider' . $instancecssid,
                'data-navigationgallery' => $displayoptionshtml->navigationgalleryflag,
                'data-numberofslides' => $displayoptionshtml->numberofslides,
                'data-vertical' => $displayoptionshtml->verticalflag,       // 09/17/19
                'data-centermode' => $displayoptionshtml->centermodeflag,
                'data-navigationoption' => $displayoptionshtml->navigationoptions,
                'data-autoplayspeed' => $displayoptionshtml->autoplayspeed
        ) );
        if ($displayoptionshtml->navigationgalleryflag) {
            $relatedcourseslidernav = html_writer::tag ( 'div', $relatedcourseslidernav, array (
                    'class' => 'relatedcourseslider-nav',
                    'id' => 'relatedcourseslider' . $instancecssid . '-nav',
                    'data-navigationgallery' => $displayoptionshtml->navigationgalleryflag,
                    'data-numberofslides' => $displayoptionshtml->numberofslides,
                    'data-vertical' => $displayoptionshtml->verticalflag,       // 09/17/19
                    'data-centermode' => $displayoptionshtml->centermodeflag,
                    'data-navigationoption' => $displayoptionshtml->navigationoptions,
                    'data-autoplayspeed' => $displayoptionshtml->autoplayspeed
            ) );
        }

        return $relatedcourseslider . $relatedcourseslidernav;
    }

    /**
     * Returns name of course in paragraph tag for the course slider.
     *
     * @param string $coursename
     * @return string
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function block_swtc_relatedcourses_slider_name_as_html($coursename) {
        $coursenamehtml = '';

        $coursenamehtml .= html_writer::tag ( 'p', $coursename, array (
                'class' => 'relatedcourseslider-course-name relatedcourseslider-truncate'
        ) );

        return $coursenamehtml;
    }

    /**
     * Returns name of course in anchor tag for the navigation gallery.
     *
     * @param string     $coursename
     * @param moodle_url $courseurl
     * @return string
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function block_swtc_relatedcourses_slider_name_nav_as_html($coursename, $courseurl) {
        $coursenamehtmlnav = '';

        $coursenamehtmlnav .= html_writer::link ( $courseurl, $coursename, array (
                'class' => 'relatedcourseslider-course-name relatedcourseslider-truncate'
        ) );

        return $coursenamehtmlnav;
    }

    /**
     * Returns summary of course in paragraph tag.
     *
     * @param string $coursesummary
     * @return string
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function block_swtc_relatedcourses_slider_summary_as_html($coursesummary) {
        $coursesummaryhtml = '';
        $summarydisplay = '';

        $coursesummaryhtml .= html_writer::tag ( 'p', $coursesummary, array (
                'class' => 'relatedcourseslider-course-summary relatedcourseslider-truncate'
        ) );

        return $coursesummaryhtml;
    }

    /**
     * Returns all pictures in course one after the other, each in image tag.
     *
     * @param course $course
     * @return string
     *
     * History:
     *
     * 05/24/21 - Added this header.
     *
     */
    public function block_swtc_relatedcourses_slider_pictures_as_html($course) {
        global $CFG;

        require($CFG->dirroot.'/blocks/swtc_relatedcourses_slider/settings/definitions.php');

        $picturesrc = '';
        $coursepictures = '';
        foreach ($course->get_course_overviewfiles () as $file) {
            $isimage = $file->is_valid_image ();
            $picturesrc = file_encode_url ( "$CFG->wwwroot/pluginfile.php", '/' .
                    $file->get_contextid () . '/' . $file->get_component () . '/' . $file->get_filearea () .
                    $file->get_filepath () . $file->get_filename (), ! $isimage );
            if ($isimage) {
                $coursepictures .= html_writer::empty_tag ( 'img', array (
                        'src' => $picturesrc,
                        'class' => 'relatedcourseslider-course-image'
                ) );
            }
        }

        if (empty ( $coursepictures )) {

            $context = context_system::instance ();
            $fs = get_file_storage ();
            $files = $fs->get_area_files ( $context->id, 'block_swtc_relatedcourses_slider', 'defaultimage', false, '', false );
            $file = reset ( $files );

            // If a default picture has been uploaded in settings, retrieve it.
            if ($file) {
                $defaultimageurl = moodle_url::make_pluginfile_url ( $file->get_contextid (),
                        $file->get_component (), $file->get_filearea (), $file->get_itemid (), $file->get_filepath (),
                        $file->get_filename () );
            } else {
                // If no default image is set in config, add manually from one in plugin directory.
                // SWTC ********************************************************************************.
                // Changed "defaultimage" to "alldefaultimage" (for all courses except PA courses) and added
                // "padefaultimage" (for PA courses).
                // SWTC ********************************************************************************.
                if (substr($course->shortname, 0, 2) === 'PA') {
                    $defaultimageurl = $CFG->wwwroot . '/blocks/swtc_relatedcourses_slider/pix/course-default-pa-picture.png';
                } else {
                    $defaultimageurl = $CFG->wwwroot . '/blocks/swtc_relatedcourses_slider/pix/course-default-all-picture.png';
                }
            }

            $coursepictures .= html_writer::empty_tag ( 'img', array (
                    'src' => $defaultimageurl,
                    'class' => 'relatedcourseslider-course-image'
            ) );
        }

        return html_writer::tag ( 'div', $coursepictures, array (
                'class' => 'relatedcourseslider-course-image-div'
        ) );
    }

    /**
     * Returns a colour in HEX format.
     *
     * @param string $colourstr
     * @param string $steps
     * @return string
     */
    public function block_swtc_relatedcourses_slider_alter_brightness($colourstr, $steps) {
        $colourstr = str_replace ( '#', '', $colourstr );

        $rhex = substr ( $colourstr, 0, 2 );
        $ghex = substr ( $colourstr, 2, 2 );
        $bhex = substr ( $colourstr, 4, 2 );

        $r = hexdec ( $rhex );
        $g = hexdec ( $ghex );
        $b = hexdec ( $bhex );

        $r = dechex ( max ( 0, min ( 255, $r + $steps ) ) );
        $g = dechex ( max ( 0, min ( 255, $g + $steps ) ) );
        $b = dechex ( max ( 0, min ( 255, $b + $steps ) ) );

        $r = str_pad ( $r, 2, "0" );
        $g = str_pad ( $g, 2, "0" );
        $b = str_pad ( $b, 2, "0" );

        $rgbhex = '#' . $r . $g . $b;

        return $rgbhex;
    }
}
