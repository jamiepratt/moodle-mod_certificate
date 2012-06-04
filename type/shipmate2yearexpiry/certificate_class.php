<?php
require_once($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_class.php');
    
class shipmate_cert_2_year_expiry extends shipmate_cert_main {
    function expiry_date($certdate, $timeformat) {
        $expirydate = userdate($certdate + 2 * YEARSECS, $timeformat);
        return '(Certificate expires in 2 years on '.$expirydate.')';
    }
}
?>