<?php

include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

$prezzo_acquisto =  (float) $_POST['prezzo_acquisto'];
$prezzo_vendita = (float) $_POST['prezzo_vendita'];
$fornitore = (int) $_POST['fornitore'];
$prodotto = (string) $_POST['prodotto'];
$data_ymd = (string) $_POST['data'];

$time = date("H:i:s");

// controlo data
try {
    $dt = new DateTime($data_ymd);    
} catch (Exception $e) {
    $trace = $e->getTrace();
    $input = $trace[0]['args'][0];
	$output['error'] = "invalid_date"; // title of modal box
	$output['msg'] = "<strong>" . $input . "</strong> non è una data valida"; // message inside modal box
	echo json_encode($output);
	die();  
}

if($prezzo_acquisto == 0){
	$output['error'] = "Prezzo acquisto Vuoto"; // title of modal box
	$output['msg'] = "Il prezzo d'acquisto è vuoto"; // message inside modal box
	echo json_encode($output);
	die();  	
}


if($fornitore == 0){
	$output['error'] = "Fornitore Vuoto"; // title of modal box
	$output['msg'] = "Non è stato settato alcun fornitore"; // message inside modal box
	echo json_encode($output);
	die();  	
}

if(empty($prodotto)){
	$output['error'] = "Prodotto vuoto"; // title of modal box
	$output['msg'] = "Non è stato settato alcun prodotto"; // message inside modal box
	echo json_encode($output);
	die();  	
}

// Tutto ok registro prezzo

$fields = array("date", "time", "fornitore", "prodotto", "prezzo_acquisto", "prezzo_vendita" );
$values = array($dt->format("Y-m-d"), $time, $fornitore, $prodotto, $prezzo_acquisto, $prezzo_vendita);
$update = array("time" => $time, "prezzo_acquisto" => $prezzo_acquisto, "prezzo_vendita" => $prezzo_vendita);

$next_id = $db->get_next_id(DBTABLE_PREZZI_GIORNO);

if( $db->insert(DBTABLE_PREZZI_GIORNO, $values, $fields, $update) ){
	
	$insertid = $db->get_insert_id();
	
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = "registrato"; // message inside modal box
	$output['action'] = ($insertid < $next_id) ? "update" : "insert";
	
}else{
	
	$output['error'] = "insert_error"; // title of modal box
	$output['msg'] = "Errore durante registrazione prezzo"; // message inside modal box
	$output['qry'] = $db->getquery();
	
}

echo json_encode($output);


?>