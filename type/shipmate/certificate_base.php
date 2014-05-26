<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}
//Create new PDF document

$pdf = new PDF('L', 'pt', 'Letter', true, 'UTF-8', false);

$timeformat = get_string('strftimedaydate');

//$pdf->SetProtection(array('print'));
$pdf->SetTitle($certificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();
$pdf->SetAutoPageBreak(false, 0);

// Add images and lines
$pdf->Image("$CFG->dirroot/mod/certificate/pix/borders/shipmate2.jpg", 10, 12, 770, 588);
$pdf->Image("$CFG->dirroot/mod/certificate/pix/watermarks/certificate.jpg", 85, 120);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, 320, 430, '', '');

$pdf->SetLineWidth(1);
$pdf->Line(320, 483, 460, 483); //sigsize 140 * 63
certificate_print_text($pdf, 320, 487, 'L', 'freeserif', 'B', 10, 'Training Developer Signature');  //XY changed to 320/487

$pdf->Line(70, 483, 210, 483);
certificate_print_text($pdf, 70, 487, 'L', 'freeserif', 'B', 10, 'Employer Signature');  //XY changed to 70/487

$pdf->SetFontSize(12);
$pdf->SetXY(540, 372);
$pdf->setFont("freeserif", "I", 12);
$pdf->Cell(200, 14, 'Developed and Authored by:', 0, 'J');
$pdf->SetXY(540, 406);
$pdf->setFont("freeserif", "B", 12);
$pdf->Cell(200, 14, 'ShipMate, Inc.', 0, 'J');

$pdf->setFont("freeserif", "", 12);
$address = "780 Buckaroo Trail, Suite D\nSisters, OR 97759-0787\n".
        "Tel: +1 (310) 370-3600\nFax: +1 (310) 370-5700\nhttp://www.shipmate.com/";

$pdf->SetXY(540, 420);
$pdf->MultiCell(200, 14, $address, 0, 'J');

// Add text
$pdf->Image("$CFG->dirroot/mod/certificate/pix/title.png", 115, 70, 0, 0);

$pdf->SetTextColor(0,0,0);

$pdf->SetXY(75, 158);
$pdf->setFont("freeserif", "", 28);
$pdf->MultiCell(640, 30, $course->fullname, 0, 'C');

certificate_print_text($pdf, 35, 230, 'C', 'freeserif', 'I', 20, 'presented to');
certificate_print_text($pdf, 35, 260, 'C', 'freeserif', 'B', 30, fullname($USER));

$modinfo = certificate_get_mod_grade($course, $certificate->printgrade, $USER->id);

if ($certrecord->timecreated == 0 && $modinfo = certificate_get_mod_grade($course, $certificate->printdate, $USER->id)) {
    $certdate = $modinfo->dategraded;
    $certrecord->timecreated = $modinfo->dategraded;
} else {
    $certdate = $certrecord->timecreated;
}

certificate_print_text($pdf, 35, 310, 'C', 'freeserif', 'B', 20, userdate($certdate, $timeformat));
if ($certdate !=0) {
    certificate_print_text($pdf, 35, 340, 'C', 'freeserif', 'BI', 20, $expirydatefunc($certdate, $timeformat));
}
certificate_print_text($pdf, 35, 370, 'C', 'freeserif', 'B', 20, 'Score : '.$modinfo->percentage);
certificate_print_text($pdf, 35, 530, 'C', 'freeserif', '', 11,
                                                "Verification code: ".certificate_get_code($certificate, $certrecord));  //XY changed to 35/530

certificate_print_text($pdf, 35, 505, 'C', 'freeserif', 'I', 10, $certificate->customtext); //XY changed to 35/505, changed font size to 10

?>
