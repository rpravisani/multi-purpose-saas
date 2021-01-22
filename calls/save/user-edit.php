<?php
$tables = array(LOGIN_TABLE);

if(empty($safevalues['password'])){
	unset($safevalues['password']);
}else{
	$safevalues['password'] = (ENCODE) ? encodePassword($safevalues['password']) :  $safevalues['password'];
}

// format date
$safevalues['subscription_date'] = cc_date_eu2us($safevalues['subscription_date']);
$safevalues['last_renew'] = cc_date_eu2us($safevalues['last_renew']);
$safevalues['expiry_date'] = cc_date_eu2us($safevalues['expiry_date']);

if(!empty($_POST['preferences_name'])){
	//$preferences_name = $db->make_data_safe($_POST['preferences_name']);
	//$preferences_value = $db->make_data_safe($_POST['preferences_value']);
	// combine keys and values
	$safevalues['preferences'] = serialize(array_combine($safevalues['preferences_name'], $safevalues['preferences_value']));
	unset($safevalues['preferences_name']);
	unset($safevalues['preferences_value']);
}





?>