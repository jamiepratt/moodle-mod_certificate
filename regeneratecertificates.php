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
 * Handles viewing a certificate
 *
 * @package    mod
 * @subpackage certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once("$CFG->libdir/pdflib.php");

set_time_limit(0);

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
if (!$id) {
    require_capability('mod/certificate:manage', context_system::instance());
    $sql = 'SELECT cm.id, c.fullname AS coursename, ce.name FROM {course_modules} cm, {modules} m, {certificate} ce, {course} c '.
        'WHERE cm.module = m.id AND ce.id = cm.instance AND m.name = \'certificate\' AND cm.course = c.id '.
        'ORDER BY c.fullname';
    $certs = $DB->get_records_sql($sql);
    echo "<ul>";
    foreach ($certs as $cert) {
        $certurl = $CFG->wwwroot.'/mod/certificate/regeneratecertificates.php?id='.$cert->id;
        echo "<li><a href='{$certurl}'>{$cert->coursename} - {$cert->name}</a></li>";
    }
    echo "</ul>";
    die;
}

if (!$cm = get_coursemodule_from_id('certificate', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
    print_error('course is misconfigured');
}
if (!$certificate = $DB->get_record('certificate', array('id'=> $cm->instance))) {
    print_error('course module is incorrect');
}

require_login($course->id, false, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_capability('mod/certificate:manage', $context);

$realuser = fullclone($USER);

$certrecords = $DB->get_records('certificate_issues', array('certificateid' => $certificate->id));

foreach ($certrecords as $certrecord) {
    $USER = $DB->get_record('user', array('id' => $certrecord->userid));
    // Create new certificate record, or return existing record

    // Load the specific certificatetype
    require("$CFG->dirroot/mod/certificate/type/$certificate->certificatetype/certificate.php");

    if ($certdate !== 0) {
        // Remove full-stop at the end if it exists, to avoid "..pdf" being created and being filtered by clean_filename
        $certname = rtrim($certificate->name, '.');
        $filename = clean_filename("$certname.pdf");
        if ($certificate->savecert == 1) {
            // PDF contents are now in $file_contents as a string
            $file_contents = $pdf->Output('', 'S');
            certificate_save_pdf($file_contents, $certrecord->id, $filename, $context->id);
        }
        echo "Successfully generated certificate for user \"".fullname($USER)."\".<br />\n";
        //$certrecord->timecreated = $certdate;
        //$DB->update_record('certificate_issues', $certrecord);
    } else {
        echo "<strong>Error.</strong> Cannot find date and grade for \"".fullname($USER)."\".<br />\n";
        print_object(compact('USER', 'certrecord', 'quizgradeobj', 'quizgrade', 'scormgradeobj', 'scormgrade', 'certdate'));
    }
    flush();
    //print_object(compact('USER', 'certrecord', 'quizgradeobj', 'quizgrade', 'scormgradeobj', 'scormgrade', 'certdate'));
    unset($pdf);
}
session_set_user($realuser);



