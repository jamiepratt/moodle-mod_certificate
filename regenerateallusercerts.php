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
require_once('regeneratelib.php');
require_once("$CFG->libdir/pdflib.php");

set_time_limit(0);

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$since = optional_param('since', 0, PARAM_INT);
if (!$id) {
    require_capability('mod/certificate:manage', context_system::instance());
    $sql = 'SELECT cm.id, c.fullname AS coursename, ce.name FROM {course_modules} cm, {modules} m, {certificate} ce, {course} c '.
    'WHERE cm.module = m.id AND ce.id = cm.instance AND m.name = \'certificate\' AND cm.course = c.id '.
    'ORDER BY c.fullname';
    $certs = $DB->get_records_sql($sql);
    echo "<ul>";
    foreach ($certs as $cert) {
        $certurl = $CFG->wwwroot.'/mod/certificate/regenerateallusercerts.php?id='.$cert->id;
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

$gradedmodule = get_coursemodule_from_id('', $certificate->printgrade);
$gradeitem = grade_get_grade_items_for_activity($gradedmodule);
$gradeitem = array_pop($gradeitem);
$grades = array();
if ($graderecords = $DB->get_records('grade_grades', array("itemid" => $gradeitem->id))) {
    foreach ($graderecords as $graderecord) {
        $grades[$graderecord->userid] = new grade_grade($graderecord, false);
    }
}


foreach ($grades as $grade) {
    print_object(array('finalgrade' => $grade->finalgrade,
                       'overridden' => $grade->overridden?userdate($grade->overridden):0,
                       'timecreated' => $grade->timecreated?userdate($grade->timecreated):0,
                       'timemodified' => $grade->timemodified?userdate($grade->timemodified):0));
    if ($grade->finalgrade >= 80) {
        certificate_generate_from_grade($context, $certificate, $grade);
    } else {
        $USER = $DB->get_record('user', array('id' => $grade->userid));
        if (isset($certrecords[$grade->userid])) {
            unset($certrecords[$grade->userid]);
            $DB->delete_records('certificate_issues', array('userid' => $certrecord->userid,
                                                           'certificateid' =>$certificate->id));
            echo "Removed certificaterecord for user \"".fullname($USER)."\".<br />\n";
        }
        flush();
        continue;
    }
}
if (count($certrecords)) {
    $certrecordids = array();
    foreach ($certrecords as $certrecord) {
        $certrecordids[] = $certrecord->id;
    }
    list($sql, $params) = $DB->get_in_or_equal($certrecordids);
    $DB->delete_records_select('certificate_issues', "id $sql", $params);
}

