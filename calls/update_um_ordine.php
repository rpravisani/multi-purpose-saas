<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$value = $db->make_data_safe($_POST['value']);
$id    = (int) $_POST['row'];
$order = (int) $_POST['order'];

$update = array("um" => $value);

if( $db->update("data_ordini_dettagli", $update, "WHERE id = '".$id."'") ){
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box	
}else{
	$output['error'] = "Impossibile aggiornare unità di misura"; // title of modal box
	$output['msg'] = "Errore durante l'aggiornamento dell'unità di misura: ".$db->getError("msg"); // message inside modal box		
}


echo json_encode($output);



?>