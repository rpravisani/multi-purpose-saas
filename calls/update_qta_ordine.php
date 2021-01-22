<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$value = (int) $_POST['value'];
$id = (int) $_POST['row'];
$order = (int) $_POST['order'];

// recupero prezzo riga
$costo_unit = $db->get1value("costo_unit", "data_ordini_dettagli", "WHERE id = '".$id."'");
if($costo_unit === false) $costo_unit = (float) 0;
$costo_riga = $value*$costo_unit;

$update = array("ordinato" => $value);

if( $db->update("data_ordini_dettagli", $update, "WHERE id = '".$id."'") ){
	$helper->updateTotQtaOrdine($order); // aggiorno totale_pezzi in tabella ordini
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
	$output['qta'] = $_user->number_format($value, 2); // message inside modal box	
	$output['costo_riga'] = $_user->number_format($costo_riga, 2); // message inside modal box	
}else{
	$output['error'] = "Impossibile aggiornare quantità"; // title of modal box
	$output['msg'] = "Errore durante l'aggiornamento delle quantità della riga ordine: ".$db->getError("msg"); // message inside modal box		
}


	


echo json_encode($output);



?>