<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(DEBUG) ini_set("display_errors", "1");

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

// default vars
$output = array();
$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";
$output['dbg'] = "";
$error = "";


// connect db
$db = new cc_dbconnect(DB_NAME);


if(empty($_POST['id'])){
	echo json_encode($output);
	die();
}

if($db->delete(TABELLA_APPUNTAMENTI, "WHERE id = '".$_POST['id']."'")){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "OK";
}else{
	$output['error'] = "nodel";
	$output['msg'] = "Impossibile cancellare l'appuntamento";
	$output['dbg'] = $db->getError("msg")." -- ".$db->getquery();
}


echo json_encode($output);
	
?>
