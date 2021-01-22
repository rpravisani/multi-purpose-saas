<?php
/*****************************************************
 * delete_missing_translation                        *
 * Does not actually delete the entry from the table *
 * but flags it to make the system ignore it         *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("del_missing_translation");

// controllo che sia impostato un modulo
if(empty($_POST['id']) ){
	echo json_encode($output);
	die();
}

// sanitize
$id 		= (int) $_POST['id'];



if(!$db->update(DBTABLE_TRANSLATIONS_LOST, array("ignore" => '1'), "WHERE id = '".$id."'")){
//if(!$db->delete(DBTABLE_TRANSLATIONS_LOST, "WHERE id = '".$id."'")){
	$output['error'] = "Could not delete missing translation";
	$output['msg'] = "Error during delete of missing traslation string in database<br>".$db->getError("msg")."<br>".$db->getQuery();
	echo json_encode($output);
	die();	
}

// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";


echo json_encode($output);

	
?>
