<?php
/*****************************************************
 * recupera eventuale listino cliente    
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$updates = array();
$ordine = 0;

$listino = (int) $db->make_data_safe($_POST['listino']);
$ids = $db->make_data_safe($_POST['ids']); // id tabella dettagli ordine

if(empty($ids) or !is_array($ids)){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "nessun id passato";

	echo json_encode($output);
	die();	
}

foreach($ids as $id){
	$row = $db->get1row("data_ordini_dettagli", "WHERE id = '".$id."'");
	
	if($listino === 0){
		// prezzo ultimo di vendita
		$prezzo_listino = $db->get1value("prezzo_vendita", "data_prezzi_giorno", "WHERE prodotto = '".$row['prodotto']."' ORDER BY date DESC ");		
		
	}else{
		$prezzo_listino = $db->get1value("prezzo", "data_listini_prezzi", "WHERE listino = '".$listino."' AND articolo = '".$row['prodotto']."'");		
	}
	
	if($prezzo_listino){
		
		//ordine
		$ordine = (int) $row['ordine'];
		
		// update db
		$db->update("data_ordini_dettagli", array("costo_unit" => $prezzo_listino), "WHERE id = '".$id."'");
		
		// per feedback
		$prezzo_riga = $row['ordinato'] * $prezzo_listino;		
		$updates[$id] = array("costo_unit" => "€ ".number_format($prezzo_listino, 2, ".", ""), "costo_riga" => "€ ".number_format($prezzo_riga, 2, ".", "") );
	}
}

// update per ora anche ordine con nuovo listino
if( !empty($ordine) ){
	$db->update("data_ordini", array("listino" => $listino), "WHERE id = '".$ordine."'");
}

$output['updates'] = $updates;
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";

echo json_encode($output);

	
?>
