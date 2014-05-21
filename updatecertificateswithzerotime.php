<?php
require_once('../../config.php');

require_once('lib.php');

$courses = $DB->get_records('course');

$recs = $DB->get_records_sql("SELECT ci.id, c.name, u.lastname, u.firstname, u.id AS userid, c.course,
c.printgrade FROM {certificate_issues} ci,
{certificate} c, {user} u
WHERE c.id = ci.certificateid
AND ci.userid = u.id
AND ci.timecreated =0");
foreach ($recs as $rec) {
    print_object($rec);
    $rec->modinfo = certificate_get_mod_grade($courses[$rec->course], $rec->printgrade, $rec->userid);
    $rec->modinfo->dategradedformatted = userdate($rec->modinfo->dategraded);
    if (80 > $rec->modinfo->points) {
        echo 'Deleting<br />';
        $DB->delete_records('certificate_issues', array('id' => $rec->id));
    } else {
        echo 'Updating<br />';
        $DB->set_field('certificate_issues', 'timecreated', $rec->modinfo->dategraded, array('id' => $rec->id));
    }
}
