<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

if(!empty($_POST['form'])){
	$db = new cc_dbconnect(DB_NAME);
	$insert = $update = array();
	
	foreach($_POST['form'] as $field){
		$name_exploded = explode("-", $field['name']);
		$value = $db->make_data_safe($field['value']);
		$firstbit = $name_exploded[0];
		$i = (int) end($name_exploded);
		
		if($name_exploded[0] == "new"){
			$insert[$i][$name_exploded[1]] = $value;
		}else if($firstbit == "string" or $firstbit == "translation"){
			$update[$i][$firstbit] = $value;
		}else{
		}	
	}
	$results = 0;
	if(!empty($update)){
		foreach($update as $id=>$row){
			$db->update(DBTABLE_TRANSLATIONS, $row, "WHERE id = '".$id."'");
			$results++;
		}
	}

	if(!empty($insert)){
		$fields = array("string", "translation", "language", "section");
		foreach($insert as $row){
			$row['language'] = $_POST['lang'];
			$row['section'] = $_POST['section'];
			$db->insert(DBTABLE_TRANSLATIONS, $row, $fields);
			// delete from translations_lost
			$db->delete(DBTABLE_TRANSLATIONS_LOST, "WHERE string = '".$row['string']."' AND file = '".$row['section']."' AND lang = '".$row['language']."'");
		}
	}
	
	$out['result'] = true;
	
}

echo json_encode($out);
?>
