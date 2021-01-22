<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";
$output['dbg'] = "";
$error = "";
$i = 0;

if(!empty($_POST)){
	$db = new cc_dbconnect(DB_NAME);
	
	$get = $db->select_all(DBTABLE_TRANSLATIONS, "WHERE section = '".$_POST['section']."' AND language = '".$_POST['fromlang']."'");
	
	if(!empty($get)){
		$campi = array("section", "string", "language", "translation");
		
		foreach($get as $row){
			$check = $db->get1row(DBTABLE_TRANSLATIONS, "WHERE  string='".$row['string']."' AND section = '".$_POST['section']."' AND language = '".$_POST['tolang']."'");
			if(!$check){
				$valori = array($_POST['section'], $row['string'], $_POST['tolang'], $row['translation']);
				if(!$db->insert(DBTABLE_TRANSLATIONS, $valori, $campi)){
					$error .= "error: ".$db->getError("msg")."\n".$db->getquery()."\n";
				}else{
					$i++;
				}
			}
			
		}
	}
	
	if(!empty($error)){
		$output['error'] = "insert";
		$output['msg'] = $error;
	}else{
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = $i." records have been copied in ";
	}
	
	
}

echo json_encode($output);
?>
