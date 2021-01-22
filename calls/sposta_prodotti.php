<?php
/*****************************************************
 * sposta_prodotti da una categoria all'altra        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("sposta_prodotti");

// sanitize
$cid 	= (int) $_POST['category'];
$rows 	= $db->make_data_safe($_POST['rows']);

if(empty($rows)){
	$output['error'] = $_t->get("no-rows");
	$output['msg'] = $_t->get("no-rows-message"); 
	echo json_encode($output);
	die();
}

$rows_flat = implode("', '", $rows);

if($db->update(DBTABLE_PRODOTTI, array("categoria" => $cid), "WHERE id IN ('".$rows_flat."')")){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";	
}else{
	$output['error'] = $_t->get("update_error");
	$output['msg'] =  $_t->get("update_error_message"); //"Errore durante spostamento di categoria prodotto.<br>".$db->getError("msg")."<br>".$db->getQuery();
	
}


echo json_encode($output);

	
?>
