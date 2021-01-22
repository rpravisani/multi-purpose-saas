<?php

$tables = array(DBTABLE_SUBSCRIPTION_TYPES);

if(!empty($_POST['param_name'])){
	$safevalues['params'] = serialize(array_combine($safevalues['param_name'], $safevalues['param_value']));
	unset($safevalues['param_name']);
	unset($safevalues['param_value']);
}

// save in post_action
$show_permissions = (empty($safevalues['pshow'])) ? array() : $safevalues['pshow'];
$add_permissions = (empty($safevalues['padd'])) ? array() : $safevalues['padd'];
$edit_permissions = (empty($safevalues['pedit'])) ? array() : $safevalues['pedit'];
$copy_permissions = (empty($safevalues['pcopy'])) ? array() : $safevalues['pcopy'];
$delete_permissions = (empty($safevalues['pdel'])) ? array() : $safevalues['pdel'];
$activate_permissions = (empty($safevalues['pact'])) ? array() : $safevalues['pact'];
$readonly_permissions = (empty($safevalues['pread'])) ? array() : $safevalues['pread'];

unset($safevalues['pshow']);
unset($safevalues['padd']);
unset($safevalues['pedit']);
unset($safevalues['pcopy']);
unset($safevalues['pdel']);
unset($safevalues['pact']);
unset($safevalues['pread']);



?>