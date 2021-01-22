<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";
$output['dbg'] = "";
$error = array();
$i = 0;

if(!empty($_POST)){
	$db = new cc_dbconnect(DB_NAME);
	$campi = array("section", "string", "language", "translation");
	
	$section = $db->make_data_safe($_POST['name']);
	
	$languages = $db->key_value("code", "language", DBTABLE_LANGUAGES, "WHERE active = '1' ORDER BY id"); // get active languages
	if(empty($languages)){
		$lang_codes = array("en");
	}else{
		$lang_codes = array_keys($languages);
	}
	
	foreach($lang_codes as $lang_code){
		$valori = array($section, "new-string", $lang_code, "");
		if(!$db->insert(DBTABLE_TRANSLATIONS, $valori, $campi)) $error[] = $db->getError("msg")."\n".$db->getquery();
	}
	
	if(empty($error)){
		$option = "<option value='".$section."'>".$section."</option>";
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "";
		$output['option'] = $option;
	}else{
		// error handling
		$c = count($error);
		$msg = "Encountered ".$c." errors while inserting new section:\n";
		foreach($error as $e){
			$msg .= " - ".$e;
		}
		$output['result'] = false;
		$output['error'] = "insert";
		$output['msg'] = $msg;
	}
	
}

echo json_encode($output);
?>
