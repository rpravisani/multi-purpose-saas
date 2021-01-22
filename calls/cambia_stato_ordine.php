<?php
/*
richiamato da dashboard.js quando clicco su checkbox o su spunto per segnalare come consegnato 
*/
include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$ordine = (int) $_POST['ordine'];
$stato = $db->make_data_safe($_POST['stato']);

$consegnato = ($stato == 'Evaso') ? 1 : 0;

$update = array("stato" => $stato, "consegnato" => $consegnato);


if( $db->update("data_ordini", $update, "WHERE id = '".$ordine."'") ){
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
}else{
	$output['error'] = "Impossibile cambiare lo stato ordine"; // title of modal box
	$output['msg'] = "Errore durante cambio di stato dell'ordine: ".$db->getError("msg"); // message inside modal box		
}


echo json_encode($output);

?>