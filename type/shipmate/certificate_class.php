<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from view.php in mod/tracker
}

class shipmate_cert_main {
    public function __construct() {
        global $CFG, $COURSE, $USER, $pdf, $certificate, $certrecord;

        if ($course_item = grade_item::fetch_course_item($COURSE->id)) {
            $certrecord->certdate = time();
            if ($scorm_item = grade_item::fetch(array('courseid'=>$COURSE->id, 'itemtype'=>'mod'))) {
                if ($scormgrade = new grade_grade(array('itemid'=>$scorm_item->id, 'userid'=>$USER->id))) {
                    $certrecord->certdate = $scormgrade->timemodified;
                }
            }
            if ($grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$USER->id))) {
                $course_item->gradetype = GRADE_TYPE_VALUE;
                $coursegrade = grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE, 2);
            } else {
                $coursegrade = '-';
            }
        }

        
        // Print the code number
        $code = '';
        if($certificate->printnumber) {
            $code = $certrecord->code;
        }
        
        //Print the student name
        $studentname = '';
        $studentname = $certrecord->studentname;
        $classname = '';
        $classname = $certrecord->classname;
        //Print the credit hours
        if($certificate->printhours) {
            $credithours =  $strcredithours.': '.$certificate->printhours;
        } else {
            $credithours = '';
        }
        //Print the html text
        $customtext = $certificate->customtext;
        
        //Create new PDF document
    
        $pdf = new TCPDF('L', 'pt', 'Letter', true, 'UTF-8', false);
        //$pdf->SetProtection(array('print'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setHeaderFont(array('helvetica', '', 10));
        //$pdf->setLanguageArray($l); //set language items
        $pdf->AddPage();

        // Add images and lines
        $pdf->Image("$CFG->dirroot/mod/certificate/pix/borders/shipmate2.jpg", 10, 12, 770, 588);
        $pdf->Image("$CFG->dirroot/mod/certificate/pix/watermarks/certificate.jpg", 85, 120);
        
//         print_signature($pdf, $certificate, 320, 430, '', '');
        
//         $pdf->SetLineWidth(1);
//         $pdf->Line(320, 483, 460, 483); //sigsize 140 * 63
//         cert_printtext($pdf, 320, 493, 'L', 'FreeSerif', 'B', 10, 'Training Developer Signature');
    
//         $pdf->Line(130, 483, 270, 483);
//         cert_printtext($pdf, 130, 493, 'L', 'FreeSerif', 'B', 10, 'Employer Signature');
        
//         $pdf->SetFontSize(12);
//         $pdf->SetXY(540, 372);
//         $pdf->setFont("FreeSerif", "I", 12);
//         $pdf->Cell(200, 14, 'Developed and Authored by:', 0, 'J');
//         $pdf->SetXY(540, 406);
//         $pdf->setFont("FreeSerif", "B", 12);
//         $pdf->Cell(200, 14, 'ShipMate, Inc.', 0, 'J');
    
//         $pdf->setFont("FreeSerif", "", 12);
//         $address = "780 Buckaroo Trail, Suite D\nSisters, OR 97759-0787\n".
//                 "Tel: +1 (310) 370-3600\nFax: +1 (310) 370-5700\nhttp://www.shipmate.com/";
        
//         $pdf->SetXY(540, 420);
//         $pdf->MultiCell(200, 14, $address, 0, 'J');
        
//         // Add text
//         $pdf->Image("$CFG->dirroot/mod/certificate/pix/title.png", 115, 70);
        
//         $pdf->SetTextColor(0,0,0);
        
//         $pdf->SetXY(75, 158);
//         $pdf->setFont("FreeSerif", "", 28);
//         $pdf->MultiCell(640, 30, $classname, 0, 'C');
        
//         //cert_printtext(145, 150, 'C', 'FreeSerif', 'B', 30, $classname);
//     	cert_printtext($pdf, 145, 230, 'C', 'FreeSerif', 'I', 20, 'presented to');
//         cert_printtext($pdf, 145, 260, 'C', 'FreeSerif', 'B', 30, $studentname);
        
//         $timeformat = get_string('strftimedaydate');
//         $certificatedate = userdate($certrecord->certdate, $timeformat);
        
        
//         cert_printtext($pdf, 145, 310, 'C', 'FreeSerif', 'B', 20, $certificatedate);
        
//         cert_printtext($pdf, 145, 340, 'C', 'FreeSerif', 'B', 20, $this->expiry_date($certrecord->certdate, $timeformat));
        
//         cert_printtext($pdf, 145, 370, 'C', 'FreeSerif', 'B', 20, 'Score : '.$coursegrade);
//         cert_printtext($pdf, 145, 545, 'C', 'FreeSerif', '', 12, "Verification code :".$code);
    
//         cert_printtext($pdf, 150, 515, 'C', 'FreeSerif', 'I', 10, $customtext);
    }
    
    function expiry_date($certdate, $timeformat) {
        $expirydate = userdate($certdate + 3 * YEARSECS, $timeformat);
        return '(Certificate expires in 3 years on '.$expirydate.')';
    }
}
?>