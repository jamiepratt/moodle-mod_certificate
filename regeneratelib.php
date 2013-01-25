<?php

function certificate_cache_cert_records($certificateid, $grade) {
    global $DB;
    static $certrecords = null;
    if ($certrecords === null) {
        $certrecords = $DB->get_records('certificate_issues', array('certificateid'=> $certificateid), '',
            'userid, certificateid, id, timecreated, code');
    }
    if (isset($certrecords[$grade->userid])) {
        $certrecord = $certrecords[$grade->userid];
        unset($certrecords[$grade->userid]);
        $certrecord->timecreated = $grade->timemodified?$grade->timemodified:time();
        $DB->update_record('certificate_issues', $certrecord);
    } else {
        $certrecord = new stdClass();
        $certrecord->certificateid = $certificateid;
        $certrecord->userid = $grade->userid;
        $certrecord->code = certificate_generate_code();
        $certrecord->timecreated =  $grade->timemodified;
        $certrecord->id = $DB->insert_record('certificate_issues', $certrecord);
    }
    return $certrecord;
}

function certificate_generate_from_grade($course, $context, $certificate, $grade) {
    global $CFG, $DB;
    $certrecord = certificate_cache_cert_records($certificate->id, $grade);
    $USER = $DB->get_record('user', array('id' => $certrecord->userid));
    // Load the specific certificatetype
    require("$CFG->dirroot/mod/certificate/type/$certificate->certificatetype/certificate.php");
    // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename
    $certname = rtrim($certificate->name, '.');
    $filename = clean_filename("$certname.pdf");
    if ($certificate->savecert == 1) {
        // PDF contents are now in $file_contents as a string
        $file_contents = $pdf->Output('', 'S');
        certificate_save_pdf($file_contents, $certrecord->id, $filename, $context->id);
    }
    echo "Successfully generated certificate for user \"".fullname($USER)."\".<br />\n";
    flush();
}


function certificate_generate_all_new_from_grade() {
    global $DB;
    set_time_limit(0);
    $certificates = $DB->get_records('certificate');
    $certificatelastgenerate = get_config('certificate', 'last_generate');
    foreach ($certificates as $certificate) {

        $cm = get_coursemodule_from_instance('certificate', $certificate->id);

        $context = context_module::instance($cm->id);

        $course = $DB->get_record('course', array('id'=> $cm->course));

        if ($gradedmodule = get_coursemodule_from_id('', $certificate->printgrade)) {
            $gradeitem = grade_get_grade_items_for_activity($gradedmodule);
            $gradeitem = array_pop($gradeitem);
            $grades = array();
            if ($graderecords = $DB->get_records_select('grade_grades', "itemid = :itemid AND timemodified > :last",
                array("itemid" => $gradeitem->id, "last" => $certificatelastgenerate))) {
                foreach ($graderecords as $graderecord) {
                    $grades[$graderecord->userid] = new grade_grade($graderecord, false);
                }
                foreach ($grades as $grade) {
                    certificate_generate_from_grade($course, $context, $certificate, $grade);
                }
            } else {
                echo "Could not find grades for certificate \"".$certificate->name."\".<br />\n";
            }
        } else {
            echo "Could not find grade item for certificate \"".$certificate->name."\".<br />\n";
        }

    }
    set_config('last_generate', time(), 'certificate');

}
