<?php
// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '../calls/_head.php';

include_once '../required/classes/cc_translations.class.php';
include_once '../required/classes/user_cookie.class.php';

// make post safe
$lang = $db->make_data_safe($_POST['lang']);

 // create cookie instance to get language code for user (if any) 
$usercookie = new user_cookie();

// check if user_agent_id (the browser used) in cookie is set, if not create one - NOT REALLY SURE WHY I NEED THIS...
$user_agent_id = $usercookie->getSingleValue('3');
if(empty($user_agent_id)){
	$user_agent_id = md5($_SERVER['HTTP_USER_AGENT']);
}

$nation = $usercookie->getNation();
if(empty($nation)){
	$nation = DEFAULT_NATION;
}

$timezone = $usercookie->getTimezone();
if(empty($timezone)){
	$timezone = DEFAULT_TIMEZONE;
}

$usercookie->set(array($lang, $nation, $timezone, $user_agent_id)); // no seconds param: use COOKIE_LIFESPAN value 

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";

echo json_encode($output);

?>