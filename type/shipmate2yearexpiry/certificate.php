<?php
if (!function_exists('expiry_date')) {
    function expiry_date($certdate, $timeformat) {
    $expirydate = userdate($certdate + 2 * YEARSECS, $timeformat);
    return 'Certificate expires in 2 years on '.$expirydate;
}
}
require($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>