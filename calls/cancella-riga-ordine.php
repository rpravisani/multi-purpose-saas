<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$record = (int) $_POST['record'];

$ordine = $db->get1value("ordine", "data_ordini_dettagli", "WHERE id = '".$record."'");

if( $db->delete("data_ordini_dettagli", "WHERE id = '".$record."'") ){
	$helper->updateTotQtaOrdine($ordine);
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
}else{
	$output['error'] = "Impossibile cancellare riga"; // title of modal box
	$output['msg'] = "Errore durante cancellazione riga ordine: ".$db->getError("msg"); // message inside modal box		
}


echo json_encode($output);



?>