<?php
if (!function_exists('expiry_date')) {
    function expiry_date($certdate, $timeformat) {
    return '';
}
}

require($CFG->dirroot.'/mod/certificate/type/shipmate/certificate_base.php');

?>