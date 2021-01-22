<?php 

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$redirect_after = false;
$session_message = false;


/*** SANITIZE ***/
$id_ordine = (int) $_POST['ordine'];


$ordine = $helper->getOrderInfo($id_ordine);

$dettagli_ordine = $helper->getOrderDetails($id_ordine);


$cliente = $db->get1row(DBTABLE_CLIENTI, "WHERE id = '".$ordine['cid']."'");

// Create token per accesso a riepilogo ordine 
$page = 73;
$view = "fullscreen";
$action = "view";

// Genera token e registralo in tabella tokens (ultimi 2: falg mostra messaggio e durata in gg)
$token = setToken("", $page, $id_ordine, $view, $action, $cliente['accesso'], '0', '90'); // in functions.php

// url completo per poter visionare riepilogo ordine
$data['url_ordine'] = HTTP_PROTOCOL.HOSTROOT.SITEROOT."cpanel.php?t=".$token;


$diff = ($ordine['salvato'] == '1') ? (int) array_sum(array_column($dettagli_ordine, "diff")) : 0;
$order_number = $helper->formatOrderNumber($ordine);

$data['titolo'] = "Buongiorno,";
$data['numero_ordine'] = "Ordine n. ".$order_number;

$template = $_configs['email_tmpl_conferma_ordine'];
$template .= "?btn=1&titolo=0"; // mostra pulsante riepilogo ordine / spedito

if($ordine['stato'] == "evaso" or $ordine['stato'] == "fatturato"){
	
	// Crea token per accedere a barcode e memorizzalo in db. Restituisce url con token
	$data['url_barcode'] = $output['bcode'] = $helper->setTokenBarcode($id_ordine, $cliente['accesso']);
	
	// allega packing list come csv
	$csv = callFile("calls/export2csv.php", array("pid" => '71', "recs" => $id_ordine)); // in functions.php - equivalente php di post in jquery
	if($csv){
		
		$attachments = FILEROOT.$csv['file_list'][0]; // $result['file_list'] is array	
		
	}
	
	$subject = "Conferma ordine n. ".$order_number;	
	$testo = getContent("email_evasione_ordine"); // in functions for now
	
	
	$template .= "&btn2=1"; //mostra pulsante barcode
	
}else{
	
	if(empty($diff)){
		
		$subject = "Riepilogo Ordine n. ".$order_number;
		$testo  = getContent("riepilogo_ordine");
		
	}else{
		
		$subject = "Ordine modificato. Ord n. ".$order_number;		
		$testo  = getContent("email_ordine_modificato");
	}				
		 
}

//$subject .= " Portale Quota47";
$output['dbg'] = $subject;

//$testo = ccstrip_replace($testo, array('p', 'div'), array('', '<br>'));
$data['testo'] = template2($testo, $data, false, "[*]");

// preparo email
$adresses = array($cliente['email']);
 
$output['debug'] = json_encode($dettagli_ordine);

$template = file_get_contents(HTTP_PROTOCOL.HOSTROOT.SITEROOT.'templates/'.$template);

$email_text = template2($template, $data);

$inviata = include '../required/send-email.php';

if($inviata){
	
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = ""; // message inside modal box
	$output['errorlevel'] = ""; // color of modal box
	
}else{

	$output['result'] = false;
	$output['error'] = $_SESSION['error_title']; // title of modal box
	$output['msg'] = $_SESSION['error_message']; // message inside modal box
	$output['errorlevel'] = "danger"; // color of modal box
	
}
                                                

echo json_encode($output);


?>