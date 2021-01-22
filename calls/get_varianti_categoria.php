<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("sposta_prodotti");

// sanitize
$categoria = (int) $_POST['categoria'];

if(empty($categoria)){
	$output['error'] = $_t->get("no-rows");
	$output['msg'] = $_t->get("no-rows-message"); 
	echo json_encode($output);
	die();
}

$gruppo = $db->get1value("varianti", DBTABLE_CATEGORIE, "WHERE id = '".$categoria."'");

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";	
$output['gruppo'] = $gruppo;

echo json_encode($output);

	
?>
