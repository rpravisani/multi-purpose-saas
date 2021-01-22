<?php
$tables = array(DBTABLE_TICKETS);

unset($safevalues['date']);
unset($safevalues['page']);
unset($safevalues['url']);
unset($safevalues['message']);

$datum = cc_date_eu2us($safevalues['date_update']);
$safevalues['ts'] = $datum." ".$safevalues['time_update'];

unset($safevalues['date_update']);
unset($safevalues['time_update']);


?>