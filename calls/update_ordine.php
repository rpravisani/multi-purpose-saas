<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$order 	= (int) $_POST['order'];

$values = array();

if(isset($_POST['pagamento'])){
	$pagamento = (int) $_POST['pagamento'];
	$values["forma_pagamento"] = $pagamento;
}

if(isset($_POST['spedizione'])){
	$spedizione = (int) $_POST['spedizione'];
	$values["spedizione"] = $spedizione;
	
	// get costo spedizione
	$output['costo'] = $db->get1value("costo", DBTABLE_SPEDIZIONI, "WHERE id = '".$spedizione."'");
}

if(isset($_POST['note'])){
	$testo = $db->make_data_safe($_POST['note']);
	$values["note"] = $testo;	
}


if($db->update(DBTABLE_ORDINI, $values, "WHERE id = '".$order."'")){
	$output['result'] 	= true;
	$output['error'] 	= "";
	$output['msg'] 		= "";
}else{
	$output['error'] 	= "Impossibile Aggiornare Ordine";
	$output['msg'] 		= "Errore durante l'aggiornamento dell'ordine:<br>".$db->getError("msg")."<br>Query: ".$db->getQuery();
}

echo json_encode($output);

	
?>
