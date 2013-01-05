<?php
if (!function_exists('expiry_date')) {
    function expiry_date($certdate, $timeformat) {
        $expirydate = userdate($certdate + 3 * YEARSECS, $timeformat);
        return 'Certificate expires in 3 years on '.$expirydate;
    }
}


require($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>