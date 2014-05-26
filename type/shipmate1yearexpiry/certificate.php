<?php
$expirydatefunc = function ($certdate, $timeformat) {
    $expirydate = userdate($certdate + YEARSECS, $timeformat);
    return 'Certificate expires in 1 year on '.$expirydate;
};
require($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>