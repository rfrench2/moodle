// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
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

$(document).ready(function() {

    // Generate relatedcourseslider and associate it with relatedcourseslidernav.

    $('.relatedcourseslider').each(function() {
        var instanceid = this.id;
        var relatedcourseslidernav = "#" + instanceid + "-nav";

        var navigationgallery = $(this).attr('data-navigationgallery');
        var navigationoption = $(this).attr('data-navigationoption');
        var numberofslides = parseInt($(this).attr('data-numberofslides'), 10);
        var vertical = $(this).attr('data-vertical');
        // SWTC ********************************************************************************.
        // Added Added "data-label-before" to each slide.
        // SWTC ********************************************************************************.
        var labelbefore = $(this).attr('data-label-before');
        var centermode = parseInt($(this).attr('data-centermode'), 10);
        var autoplayspeed = parseInt($(this).attr('data-autoplayspeed'), 10);
        var arrows = (navigationoption == 'Arrows' || navigationoption == 'Arrows and Radio buttons') ? true : false;
        var dots = (navigationoption == 'Radio buttons' || navigationoption == 'Arrows and Radio buttons') ? true : false;
        var relatedcoursenav = '';
        
        // SWTC ********************************************************************************.
        // Added "vertical" (True for vertical or False for horizontal).
        // SWTC ********************************************************************************.
        vertical = (vertical == 1) ? true : false;

        centermode = (centermode == 1) ? true : false;
        if (navigationgallery == '1') {
            numberofslides = 1;
            arrows = false;
            dots = false;
            centermode = false;
            relatedcoursenav = relatedcourseslidernav;
        }

        $(this).slick({
            swipeToSlide : true,
            infinite : true,
            slidesToShow : numberofslides,
            slidesToScroll : 1,
            arrows : arrows,
            prevArrow: '<button class="slick-prev" aria-label="Previous" type="button"></button>',
            nextArrow: '<button class="slick-next" aria-label="Next" type="button"></button>',
            dots : dots,
            autoplay : true,
            autoplaySpeed : autoplayspeed,
            focusOnSelect : true,
            vertical : vertical,
            centerMode : centermode,
            asNavFor : relatedcoursenav,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                        infinite: true,
                        dots: true
                    }
            },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
            },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
            }

                ]

        });
    });

    // Generate relatedcourseslidernav and associate it with relatedcourseslider.
    $('.relatedcourseslider-nav').each(function() {
        var instanceid = this.id;
        var relatedcourseslider = "#" + instanceid.slice(0, -4);

        var navigationgallery = $(this).attr('data-navigationgallery');
        var navigationoption = $(this).attr('data-navigationoption');
        var numberofslides = parseInt($(this).attr('data-numberofslides'), 10);
        var vertical = $(this).attr('data-vertical');         // 09/17/19
        var centermode = parseInt($(this).attr('data-centermode'), 10);
        var autoplayspeed = parseInt($(this).attr('data-autoplayspeed'), 10);
        var arrows = (navigationoption == 'Arrows' || navigationoption == 'Arrows and Radio buttons') ? true : false;
        var dots = (navigationoption == 'Radio buttons' || navigationoption == 'Arrows and Radio buttons') ? true : false;
        
        // SWTC ********************************************************************************.
        // Added "vertical" (True for vertical or False for horizontal).
        // SWTC ********************************************************************************.
        vertical = (vertical == 1) ? true : false;                      // 09/17/19

        centermode = (centermode == 1) ? true : false;

        if (navigationgallery == '1') {
            $(this).slick({
                swipeToSlide : true,
                infinite : true,
                slidesToShow : numberofslides,
                slidesToScroll : 1,
                arrows : arrows,
                dots : dots,
                autoplay : true,
                autoplaySpeed : autoplayspeed,
                centerMode : centermode,
                focusOnSelect : true,
                vertical : vertical,                // 09/17/19
                asNavFor : relatedcourseslider,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                            infinite: true,
                            dots: true
                        }
                },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                }

                    ]
            });
        }
    });

    // Add mouseenter response.
    $('.relatedcourseslider-course').mouseenter(function() {

        $(this).addClass('relatedcourseslider-course-hovered');

        $('.relatedcourseslider-course-image', this).addClass('relatedcourseslider-course-image-hovered');

        $('.relatedcourseslider-course-summary', this).addClass('relatedcourseslider-course-summary-hovered');

        $('.relatedcourseslider-course-name', this).addClass('relatedcourseslider-course-name-hovered');

    });

    // Add mouseleave leave.
    $('.relatedcourseslider-course').mouseleave(function() {
        $(this).removeClass('relatedcourseslider-course-hovered');

        $('.relatedcourseslider-course-image', this).removeClass('relatedcourseslider-course-image-hovered');

        $('.relatedcourseslider-course-summary', this).removeClass('relatedcourseslider-course-summary-hovered');

        $('.relatedcourseslider-course-name', this).removeClass('relatedcourseslider-course-name-hovered');

    });

    // Make courselider and relatedcourseslider-nav visible once they have loaded.
    $('.relatedcourseslider').addClass('relatedcourseslider-visible');
    $('.relatedcourseslider-nav').addClass('relatedcourseslider-nav-visible');

});

$(window).bind('resize', function(e) {
    var resizeEvt;
    $(window).resize(function() {
        clearTimeout(resizeEvt);
        resizeEvt = setTimeout(function() {
        }, 300);
    });
});
