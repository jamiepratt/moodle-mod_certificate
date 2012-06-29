<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}
$course_item = grade_item::fetch_course_item($COURSE->id);
$scorm_item = grade_item::fetch(array('courseid'=>$COURSE->id, 'itemtype'=>'mod', 'itemmodule'=>'scorm'));
$quiz_item = grade_item::fetch(array('courseid'=>$COURSE->id, 'itemtype'=>'mod', 'itemmodule'=>'quiz'));

// Date formatting - can be customized if necessary
$certificatedate = '';
if ($certrecord->timecreated > 0) {
    $certdate = $certrecord->timecreated;
} else {
    if ($quiz_item) {
        $quizgrade = new grade_grade(array('itemid'=>$quiz_item->id, 'userid'=>$USER->id));
    } else {
        $quizgrade = false;
    }
    if ($scorm_item) {
        $scormgrade = new grade_grade(array('itemid'=>$scorm_item->id, 'userid'=>$USER->id));
    } else {
        $scormgrade = false;
    }
    
    if ($scormgrade->timemodified || $quizgrade->timemodified) {
        if ((($quizgrade->timemodified && $scormgrade->timemodified)
                    && ($quizgrade->finalgrade > $scormgrade->finalgrade))
              || ($quizgrade->timemodified && !$scormgrade->timemodified)) {
            $certdate = $quizgrade->timemodified;
        } else {
            $certdate = $scormgrade->timemodified;
        }
    } else {
        $certdate = 0;
    }
}
if ($certificate->printdate > 0) {
    $timeformat = get_string('strftimedaydate');
    if ($certdate != 0) {
        $certificatedate = userdate($certdate, $timeformat);
    } else {
        $certificatedate = '-';
    }
}


if ($grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$USER->id))) {
    $course_item->gradetype = GRADE_TYPE_VALUE;
    $coursegrade = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, 2);
} else {
    $coursegrade = '-';
}


// Print the code number
$code = '';
if($certificate->printnumber) {
    $code = $certrecord->code;
}


//Print the credit hours
if($certificate->printhours) {
    $credithours =  $strcredithours.': '.$certificate->printhours;
} else {
    $credithours = '';
}

//Create new PDF document

$pdf = new TCPDF('L', 'pt', 'Letter', true, 'UTF-8', false);
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
certificate_print_text($pdf, 320, 493, 'L', 'freeserif', 'B', 10, 'Training Developer Signature');

$pdf->Line(130, 483, 270, 483);
certificate_print_text($pdf, 130, 493, 'L', 'freeserif', 'B', 10, 'Employer Signature');

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



certificate_print_text($pdf, 35, 310, 'C', 'freeserif', 'B', 20, $certificatedate);
if ($certdate !=0) {
    certificate_print_text($pdf, 35, 340, 'C', 'freeserif', 'BI', 20, expiry_date($certdate, $timeformat));
}
certificate_print_text($pdf, 35, 370, 'C', 'freeserif', 'B', 20, 'Score : '.$coursegrade);
certificate_print_text($pdf, 35, 540, 'C', 'freeserif', '', 12, "Verification code :".$code);

certificate_print_text($pdf, 30, 515, 'C', 'freeserif', 'I', 10, $certificate->customtext);

?>