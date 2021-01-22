<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$id = (int) $_POST['id'];

$dettagli = $db->get1row("data_ordini_dettagli", "WHERE id = '".$id."'");

if(!$dettagli){
	$output['error'] = "no-data";
	$output['msg'] = "Nessun dettaglio ordine trovato!";
	echo json_encode($output);
	die();
}


$dettagli['codice'] = $dettagli['prodotto'];
$dettagli['descrizione'] = $db->get1value("descrizione", "data_prodotti", "WHERE codice = '".$dettagli['codice']."'");
$row = $helper->getOrderDetailRow($dettagli, $id, $dettagli['ordinato'], false, false, false);

if($row){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['html'] = $row;
}else{
	$output['error'] = "no-row";
	$output['msg'] = "Nessun dettaglio ordine trovato!";	
}

echo json_encode($output);

	
?>
