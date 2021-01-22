<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$value = (float) $_POST['value'];
$qta = (float) $_POST['qta'];
$id = (int) $_POST['row'];
$order = (int) $_POST['order'];

$costo_riga = $value*$qta;


$update = array("costo_unit" => $value);

if( $db->update("data_ordini_dettagli", $update, "WHERE id = '".$id."'") ){
	//$helper->updateTotQtaOrdine($order); // aggiorno totale_pezzi in tabella ordini
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
	$output['costo_unit'] = $_user->number_format($value, 2); // message inside modal box	
	$output['costo_riga'] = $_user->number_format($costo_riga, 2); // message inside modal box	
}else{
	$output['error'] = "Impossibile aggiornare quantità"; // title of modal box
	$output['msg'] = "Errore durante l'aggiornamento delle quantità della riga ordine: ".$db->getError("msg"); // message inside modal box		
}


	


echo json_encode($output);



?>