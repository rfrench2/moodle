<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * letter_non_embedded certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Lenovo history:
 *
 *  04/16/17 - Added machinetypes line to certificate form (located in course_format_options.machinetypes); standardized spacing for cleaner look.
 *
 */

defined('MOODLE_INTERNAL') || die();

// Lenovo ********************************************************************************
// Variables begin...
// $ebg_debug = 99;
$ebg_debug = 0;

// Initialize (in case machinetypes doesn't exist yet). This holds the actual machinetypes data AND is the line
// that is printed on the certificate (after the course name line).
$machinetypes = '';

// Spacing constants. $y starts at 125 (landscape mode). Will print all lines on certificate equally spaced using this value.
// $new_y is the new y value that is incremented throughout the printing; $large_spacing is for spacing between large strings (size 30); 
// $small_spacing is for all other spacing.
$new_y = 0;
$large_spacing = 40;
$small_spacing = 30;
$very_small_spacing = 15;

// Variables end...
// Lenovo ********************************************************************************

$pdf = new PDF($certificate->orientation, 'pt', 'LETTER', true, 'UTF-8', false);

// Load local variable with 'machinetypes' from database (if it exists).
$record = $DB->get_record('course_format_options',  array('courseid' => $COURSE->id, 'format' => $COURSE->format, 'sectionid' => 0, 'name' => 'machinetypes'));

if ($ebg_debug) {
    // print_object($record);
}
if ( !(empty($record))) {
    $machinetypes = $record->value;
} else {
    $machinetypes = '';
}

if ($ebg_debug) {
    print("About to print machinetypes ===78.1===.<br />"); // Lenovo
    print_r("==>$machinetypes<=="); // Lenovo
    print("<br />Finished printing courseversion and machinetypes ===78.1===.<br />"); // Lenovo
}

$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 28;
    $y = 125;
    $sealx = 590;
    $sealy = 425;
//    $sigx = 130;  Lenovo
	$sigx = 100;
//    $sigy = 440; Lenovo
	$sigy = 400;
    $custx = 133;
    $custy = 440;
    $wmarkx = 100;
    $wmarky = 90;
    $wmarkw = 600;
    $wmarkh = 420;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 792;
    $brdrh = 612;
    $codey = 505;
} else { // Portrait
    $x = 28;
    $y = 170;
    $sealx = 440;
    $sealy = 590;
    $sigx = 85;
    $sigy = 580;
    $custx = 88;
    $custy = 580;
    $wmarkx = 78;
    $wmarky = 130;
    $wmarkw = 450;
    $wmarkh = 480;
    $brdrx = 10;
    $brdry = 10;
    $brdrw = 594;
    $brdrh = 771;
    $codey = 660;
}

// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame_letter($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Lenovo ********************************************************************************
// Format of certificate_print_text is:
// certificate_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $text, $width = 0)
// To test print location and size:
// certificate_print_text($pdf, $x, $y + $spacing, 'C', 'Helvetica', '', 15, "y is :$new_y " . get_string('title', 'certificate'));
//****************************************************************************************
// Add text
$pdf->SetTextColor(0, 0, 120);
certificate_print_text($pdf, $x, $y, 'C', 'Helvetica', '', 30, get_string('title', 'certificate'));
$pdf->SetTextColor(0, 0, 0);

// Lenovo ********************************************************************************
// Moving all fields before machinetypes a little higher on the certificate to make room if machinetypes wraps.
// certificate_print_text($pdf, $x, $y + 105, 'C', 'Helvetica', '', 30, fullname($USER));
$new_y = $y + $large_spacing; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Times', '', 20, get_string('certify', 'certificate')); // Lenovo

$new_y = $new_y + $small_spacing; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Helvetica', '', 30, fullname($USER)); // Lenovo

$new_y = $new_y + $large_spacing; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Times', '', 20, get_string('statement', 'certificate')); // Lenovo

$new_y = $new_y + $small_spacing; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Helvetica', '', 20, format_string($course->shortname));	// Lenovo

$new_y = $new_y + $small_spacing; // Lenovo
// $coursename = $course->shortname . " " . $course->fullname; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Helvetica', '', 20, format_string($course->fullname));	// Lenovo

// Add blank line after the course fullname in case it wraps.
$new_y += $very_small_spacing;

$new_y = $new_y + $small_spacing; // Lenovo
certificate_print_text($pdf, $x, $new_y, 'C', 'Helvetica', '', 15, format_string($machinetypes));	// Lenovo
//****************************************************************************************

certificate_print_text($pdf, $x, $y + 255, 'C', 'Helvetica', '', 14, certificate_get_date($certificate, $certrecord, $course));
certificate_print_text($pdf, $x, $y + 283, 'C', 'Times', '', 10, certificate_get_grade($certificate, $course));
certificate_print_text($pdf, $x, $y + 311, 'C', 'Times', '', 10, certificate_get_outcome($certificate, $course));
if ($certificate->printhours) {
    certificate_print_text($pdf, $x, $y + 339, 'C', 'Times', '', 10, get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
}
certificate_print_text($pdf, $x, $codey, 'C', 'Times', '', 10, certificate_get_code($certificate, $certrecord));
$i = 0;
if ($certificate->printteacher) {
    $context = context_module::instance($cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            certificate_print_text($pdf, $sigx, $sigy + ($i * 12), 'L', 'Times', '', 12, fullname($teacher));
        }
    }
}

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);
