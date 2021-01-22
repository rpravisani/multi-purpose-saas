<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

// sanitize
$id     = (int) $_POST['id'];

$peso_lordo = (float) $_POST['peso_lordo'];
$peso_netto = (float) $_POST['peso_netto'];
$ncolli     = (int) $_POST['ncolli'];
$tara_collo = (float) $_POST['tara_collo'];
$tipo_collo = (int) $_POST['tipo_collo'];
$costo_unit = (float) $_POST['costo'];

$fields = array("peso_lordo", "peso_netto", "ncolli", "tara", "tipo_collo", "costo_unit");

$values = array($peso_lordo, $peso_netto, $ncolli, $tara_collo, $tipo_collo, $costo_unit);

$values = array_combine($fields, $values);

if($db->update("data_ordini_dettagli", $values, "WHERE id='".$id."'")){
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box
	$output['errorlevel'] = ""; // color of modal box
	
}else{
	$output['error'] = "Errore aggiornamento dettagli"; // title of modal box
	$output['msg'] = $db->getError("msg"); // message inside modal box
	$output['errorlevel'] = "danger"; // color of modal box
	$output['qry'] = $db->getquery();
	$output['dbg'] = "";
}

echo json_encode($output);


?>