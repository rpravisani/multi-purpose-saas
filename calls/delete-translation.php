<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";
$output['dbg'] = "";
$error = array();

if(!empty($_POST['id'])){
	$db = new cc_dbconnect(DB_NAME);
	$id = (int) $_POST['id'];
	
	if($db->delete(DBTABLE_TRANSLATIONS, "WHERE id = '".$id."'")){
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "ok";
	}else{
		$output['error'] = "delete";
		$output['msg'] = "Error during delete of translation.";
		$output['dbg'] = $db->getError("msg")."\n".$db->getquery();
	}
}

echo json_encode($output);
?>
