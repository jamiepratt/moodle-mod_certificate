<?php
$expirydatefunc =  function ($certdate, $timeformat) {
    $expirydate = userdate($certdate + 3 * YEARSECS, $timeformat);
    return 'Certificate expires in 3 years on '.$expirydate;
};


require($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>