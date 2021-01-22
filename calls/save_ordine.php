<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$id_ordine 			 = (int) $_POST['id'];
$cliente 			 = (int) $_POST['cliente'];
$data 				 = (string) $_POST['data'];
$stato 				 = (string) $_POST['stato'];
$documento_richiesto = (string) $_POST['documento_richiesto'];
$data_consegna 		 = (string) $_POST['data_consegna'];
$ora_consegna 		 = (string) $_POST['ora_consegna'];
$destinazione 		 = $db->make_data_safe($_POST['destinazione']);
$codice_iva 		 = (int) $_POST['codice_iva'];
$listino 		 	 = (int) $_POST['listino'];
$nota 				 = $db->make_data_safe($_POST['nota']);

// CONVERTO DATA ORDINE PER GESTIONE IN DB
$d = explode("/", $data);
$d = array_reverse($d);
$data = implode("-", $d);
$dt = new DateTime($data);

// CONVERTO DATA CONSEGNA PER GESTIONE IN DB
$dc = explode("/", $data_consegna);
$dc = array_reverse($dc);
$data_consegna = implode("-", $dc);
// aggiungo orario consegna a data consegna
$consegna = $data_consegna." ".$ora_consegna;

$fields = array("cliente", "data_ordine", "stato", "documento_richiesto", "consegna", "destinazione", "codice_iva", "listino", "note_ordine", "insertedby", "updatedby");
$values = array($cliente, $data, $stato, $documento_richiesto, $consegna, $destinazione, $codice_iva, $listino, $nota, $_SESSION['login_id'], $_SESSION['login_id']);

// se id ordine è vuoto inserisco se no aggiorno
if(empty($id_ordine)){
	
	// GET NUMERO ORDINE / ANNO
	$anno_ordine = date("Y"); 
	$num_ordine = $db->get_max_row("num_ordine", "data_ordini", "WHERE anno_ordine = '".$anno_ordine."'");
	if(!$num_ordine) $num_ordine = 0;
	$num_ordine++;
	
	// label ordine
	$label = $helper->formatLabelStatoOrdine($stato);
	
	// aggiungo anno e numero ordine progressivo calcolato
	$fields[] = "num_ordine";
	$fields[] = "anno_ordine";

	
	$values[] = $num_ordine;
	$values[] = $anno_ordine;


	if(!$db->insert("data_ordini", $values, $fields)){
		$output['error'] = "insert"; // title of modal box
		$output['msg'] = "Errore durante inserimento ordine"; // message inside modal box
		$output['qry'] = $db->getquery(); // message inside modal box
		$output['html'] = ""; // message inside modal box
	}else{
		$output['result'] = true;
		$output['action'] = "insert";		
		$output['error'] = ""; // title of modal box
		$output['msg'] = ""; // message inside modal box
		$output['id'] = $db->get_insert_id(); 
		$output['num_ordine'] = $num_ordine; 
		$output['anno_ordine'] = $anno_ordine; 
		$output['intesta_ordine'] = "Ordine n. ".$helper->formatOrderNumber($num_ordine, $anno_ordine)." del ".$dt->format("d/m/Y").$label; 
		
	}
	
}else{
	
	$update = array_combine($fields, $values);

	if(!$db->update("data_ordini", $update, "WHERE id = '".$id_ordine."'")){
		$output['error'] = "update"; // title of modal box
		$output['msg'] = "Errore durante aggiornamenti ordine"; // message inside modal box
		$output['html'] = ""; // message inside modal box
	}else{
		$output['result'] = true;
		$output['action'] = "update";
		$output['error'] = ""; // title of modal box
		$output['msg'] = $db->getquery(); // message inside modal box
	}

}

echo json_encode($output);

?>