<?php
function expiry_date($certdate, $timeformat) {
    $expirydate = userdate($certdate + 2 * YEARSECS, $timeformat);
    return '(Certificate expires in 2 years on '.$expirydate.')';
}
require_once($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>