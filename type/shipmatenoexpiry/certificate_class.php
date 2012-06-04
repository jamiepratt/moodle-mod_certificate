<?php
require_once($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_class.php');
    
class shipmate_cert_no_expiry extends shipmate_cert_main {
    function expiry_date($certdate, $timeformat) {
        return '';
    }
}
?>