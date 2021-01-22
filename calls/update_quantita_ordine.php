<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id 	= (int) $_POST['id'];
$qta 	= (int) $_POST['qta'];

if(empty($id)){
	$output['error'] = "No post!";
	$output['msg'] = "Impossibile aggiornare l'ordine manca id della variante!";
	echo json_encode($output);
	die();
}

if(empty($qta)) $qtà = 0;

if($db->update(DBTABLE_ORDINI_DETTAGLI, array("qta" => $qta), "WHERE id = '".$id."'")){
	$output['result'] 	= true;
	$output['error'] 	= "";
	$output['msg'] 		= "";
}else{
	$output['error'] 	= "Impossibile Aggiornare Ordine";
	$output['msg'] 		= "Errore durante l'aggiornamento dell'ordine:<br>".$db->getError("msg")."<br>Query: ".$db->getQuery();
}

echo json_encode($output);

	
?>
