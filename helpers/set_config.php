<?php
// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '../calls/_head.php';

$param = $db->make_data_safe($_POST['param']);
$value = $db->make_data_safe($_POST['value']);

// get current param state
$exists = $db->get1row(DBTABLE_CONFIG, "WHERE param = '".$param."'");

if(!$exists){
	$output['error'] = "Param doesn't exist!";
	$output['msg'] = "The parameter you want to set is not defined!";		
	echo json_encode($output);
	die();
}

if($value === $exists['value']){
	$output['error'] = "Value not changed!";
	$output['msg'] = "The parameter value you passed (".$value.") is already set (".$exists['value'].")!";		
	echo json_encode($output);
	die();
}

if($db->update(DBTABLE_CONFIG, array("value" => $value), "WHERE param = '".$param."'")){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['html'] = "ok";
}else{
	$output['error'] = "Not updated!";
	$output['msg'] = "The parameter value could not be updated!";		
	$output['msg'] .= $db->getError("msg");
	$output['msg'] .= "<br>\n";
	$output['msg'] .= $db->getQuery();
}

echo json_encode($output);

?>