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
$consegnato = (isset($_POST['consegnato'])) ? (int) $_POST['consegnato'] : '1';

$check = $db->get1row("data_ordini", "WHERE id = '".$ordine."'");

// cambio lo stato all'ordine
$stato = (empty($consegnato)) ? "In Lavorazione" : "Evaso";

$dtcons = new DateTime($check['consegna']);
$output['postfix_ora'] = (date("YmdHi") > $dtcons->format("YmdHi")) ? "<i class='fa fa-exclamation-triangle text-danger ml-2'></i>" : "";


$update = array("consegnato" => $consegnato, "stato" => $stato);

if( $db->update("data_ordini", $update, "WHERE id = '".$ordine."'") ){
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
}else{
	$output['error'] = "Impossibile evadere ordine"; // title of modal box
	$output['msg'] = "Errore durante evasione ordine: ".$db->getError("msg"); // message inside modal box		
}


	


echo json_encode($output);



?>