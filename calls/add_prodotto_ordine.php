<?php
/****
  AGGIUNGO PRODOTTO A TABELLA DETTAGLI ORDINI
  IN +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
   -prodotto (string) codice prodotto, campo codice in tabella prodotti
   -qta (float) le quantità
   -um (string) unità di misura (potrebbe essere stato forzato in ordine ero diverso da quanto memorizzato in tab prodotti)
   -ord (int) id tabella ordini
   
  OUT ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
  HTML CON LA RIGA TABELLA (TR) COMPOSTO DA: codice prodotto, descrizione prodotto, um, costo unitario, prezzo riga, icona canc e campo vuoto per annotazione peso
  
****/
include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

// sanify
$prodotto = $db->make_data_safe($_POST['prodotto']);
$qta = (float) $_POST['qta'];
$um = $db->make_data_safe($_POST['um']);
$ordine = (int) $_POST['ord'];
$listino = (int) $_POST['listino'];

$oggi = date("Y-m-d"); // serve per estrapoalre prezzo da prezzi registrati

// get prodotto
$data = $db->get1row("data_prodotti", "WHERE codice = '".$prodotto."'");

// recupero eventuale prezzo articolo da listino settimana
// se non c'è alcuna registrazione per il lunedì in questione cerco nella prima data più alta
if(empty($listino)){
	$prezzi = $helper->getPrezzoLunedi($oggi, $prodotto); // cerca prezzo registrato lunedì se non c'è prende prezzo ultimo 	
}else{
	$prezzi = $helper->getPrezzoListino($listino, $prodotto);
}


// fallback values
if(!$prezzi){
	$data['costo_unit'] = 0;
	$data['prezzo_vendita'] = 0;
}else{
	$data['costo_unit'] = $prezzi['acquisto'];
	$data['prezzo_vendita'] = $prezzi['vendita'];
}

if(empty($qta)) $qta = 1;
if(empty($um))  $um  = $data['um'];

$data['um'] = $um; // per feedback visivo


if(!empty($ordine)){
	
	$fields = array("ordine", "prodotto", "ordinato", "um", "costo_unit", "insertedby", "updatedby");
	$values = array($ordine, $prodotto, $qta, $um, $data['costo_unit'], $_SESSION['login_id'], $_SESSION['login_id']);
	
	if(!$db->insert("data_ordini_dettagli", $values, $fields)){
		$output['error'] = "insert"; // title of modal box
		$output['msg'] = "Errore durante inserimento riga"; // message inside modal box
		$output['qry'] = $db->getquery(); // message inside modal box
		$output['html'] = ""; // message inside modal box
		echo json_encode($output);
		die();
	}
	
	$row = $db->get_insert_id();
	$qta = $_user->number_format($qta, 2);
	
	// passando id ordine conto le qtà in dettagli ordine e aggiorno dato consolidato in tabella ordini
	$helper->updateTotQtaOrdine($ordine);
	
	
}

if($data){
	$data['row'] = $row;
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box
	$output['html'] = $helper->getOrderDetailRow($data, $row, $qta);
	//$output['html'] = "<tr><td>".$data['descrizione']."</td><td>".$data['descrizione']."</td><td>".$data['um']."</td><td>".$qta."</td><td align='center'><i class='fa fa-trash text-danger'></i></td></tr>\n";
}else{
	$output['error'] = "no-prodotto"; // title of modal box
	$output['msg'] = "Nessun prodotto trovato"; // message inside modal box
	$output['html'] = ""; // message inside modal box
	
}


echo json_encode($output);



?>