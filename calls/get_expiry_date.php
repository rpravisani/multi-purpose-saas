<?php
/*********************************************************
 * get_expiry_date for user based on subscription length *
 *********************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$subscription = (int) $_POST['subscription'];
$last_renew = cc_date_eu2us($_POST['last_renew']);

// Get length (duration) of subscription in days
$length = $db->get1value("length", DBTABLE_SUBSCRIPTION_TYPES, "WHERE id = '".$subscription."'");

if(!$length){
	$output['error'] = "No duration set";
	$output['msg'] = "This subscription has no duration set!";
	echo json_encode($output);
	die();
	
}

// transform to seconds
$last_renew_ts = strtotime($last_renew);
// if last renew fails get current timestamp
if(!$last_renew_ts) $last_renew_ts = time();

// calculate expiry date in seconds from epoch
$expiry_date_ts = $last_renew_ts + ($length * 60 * 60 * 24);


// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['expiry_date'] = date("d/m/Y", $expiry_date_ts);

echo json_encode($output);

	
?>
