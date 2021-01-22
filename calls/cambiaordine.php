<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

if(isset($_POST['ids'])){
	$error = array();
	$db = new cc_dbconnect(DB_NAME);
	
	foreach($_POST['ids'] as $key=>$value){
		$valori = array();
		$ordine = $key+1;
		$exploded = explode("_", $value);
		$id = end($exploded);
		$valori['ordine'] = $ordine;
		$db->update(TABELLA_FOTO, $valori, "WHERE id='".$id."'");
	}
	
	
	
	if(count($error) > 0){
		$out['error'] = true;
		$out['msg'] = "Impossibile cambiare ordine a tutte le foto. Le seguenti query non sono andate a buon fine:\n";
		foreach($error as $err){
			$out['msg'] = $err."\n";
		}
	}else{
		reset($_POST['ids']);
		$out['result'] = true;
		$out['first'] = current($_POST['ids']);
	}
}

echo json_encode($out);
?>
