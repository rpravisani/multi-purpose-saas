<?php

include_once '_head.php';

$safevalues = $db->make_data_safe($_POST['values']);

foreach($safevalues as $key=>$value){
	
	if(empty($value)){
		

		echo json_encode($output);
		die();
		
	}
	
}

$db->update(LOGIN_TABLE, $safevalues, "WHERE id = '".$_SESSION['login_id']."'");


if($_SESSION['login_type'] == '3'){
	
	$values = array("email" => $safevalues['email'], "telefono" => $safevalues['telephone']);
	$db->update(DBTABLE_CLIENTI, $values, "WHERE accesso = '".$_SESSION['login_id']."'");
	
}

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";	


echo json_encode($output);


