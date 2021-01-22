<?php
/************************************************************************************************************
   QUANDO CONFERMO ORDINE. VIENE RICHIAMATO DA FUNZIONE notifica_cliente IN JS SCRIPT gestione-ordine.js
   INVIA EMAIL CON RIEPILOGO ORDINE SIA A ADMIN CHE A CLIENTE (SE PROVVISTO DI INDIRIZZO EMAIL)
 *************************************************************************************************************/
include_once '_head.php';

if(empty($_POST)){
	echo json_encode($output);
	die();  	
}

// vars
$tabella_dettagli = "";

// sanify
$id = (int) $_POST['ordine'];

// get head ordine
$ordine = $db->get1row("data_ordini", "WHERE id = '".$id."'");

// controlli : esiste ordine? Lo stato è diverso da aperto e annullato? Ha articoli (mettere dopo riga successiva)?

if(!$ordine){
	$output['error'] = "no-ordine"; // title of modal box
	$output['msg'] = "Non è stato trovato l'ordine ".$id; // message inside modal box
	echo json_encode($output);
	die();  		
}

if($ordine['stato'] == "Aperto" or $ordine['stato'] == "Annullato"  ){
	$output['error'] = "stato-ordine"; // title of modal box
	$output['msg'] = "Lo stato dell'ordine ".$id." è «".$ordine['stato']."» - non è permesso inviare notifica cliente"; // message inside modal box
	echo json_encode($output);
	die();  		
}


// dati cliente 
$cliente = $db->get1row("data_fornitori", "WHERE id = '".$ordine['cliente']."'");

if(!$cliente){
	$output['error'] = "no-cliente"; // title of modal box
	$output['msg'] = "Il cliente attribuito all'ordine non esiste (più)!"; // message inside modal box
	echo json_encode($output);
	die();  			
}

// DETTAGLI ORDINE (TABELLA PRODOTTI E QTA') IN ORDINE DI INSERIMENTO DAL PIU' RECENTE AL PIU' VECCHIO
$qry = "
	SELECT o.id, p.codice, p.descrizione, o.um, o.ordinato, o.costo_unit 
	FROM data_ordini_dettagli AS o
	JOIN data_prodotti AS p ON (p.codice = o.prodotto) 
	WHERE o.ordine = '".$id."' 
	ORDER BY o.id DESC
";	
$dettagli = $db->fetch_array($qry);

// Genero tabella dettagli
if($dettagli){	
	$tabella_dettagli = "<table width='100%' border='1' cellspacing='0' cellpadding='4'>";
	$tabella_dettagli .= "<thead><tr><th>Cod.</th><th>Descrizione</th><th>U.m.</th><th>Q.tà</th><th>Costo unit.</th><th>Prezzo</th><tr></thead>";
	$tabella_dettagli .= "<tbody>";
	foreach($dettagli as $dettaglio){
		$qta_ordinato = $_user->number_format($dettaglio['ordinato'], 2);			
		$tabella_dettagli .= $helper->getOrderDetailRow($dettaglio, $dettaglio['id'], $qta_ordinato, true, true);
	}
	$tabella_dettagli .= "</tbody>";
	$tabella_dettagli .= "</table>";
}else{
	// notifica errore e esci
	$output['error'] = "Nessun articolo!"; // title of modal box
	$output['msg'] = "L'ordine non ha articoli!"; // message inside modal box
	echo json_encode($output);
	die();  		
}

// formatto date ordine e di consegna
$dto = new DateTime($ordine['data_ordine']);
$dtc = new DateTime($ordine['consegna']); // ora e data consegna sono un campo unico, sotto però li divido

$data_ordine   = $dto->format("d/m/Y");
$data_consegna = $dtc->format("d/m/Y");
$ora_consegna  = $dtc->format("H:i");

// Num ordine
$num_ordine = $helper->formatOrderNumber($ordine['num_ordine'], $ordine['anno_ordine']);
$order_title = "Ordine n. ".$num_ordine." del ".$data_ordine;

/*** DATI GENERICI PER EMAIL ***/

$redirect_after = false;

$riepilogo_ordine = "Ordine n. <strong>{{num_ordine}}</strong> del <strong>{{data_ordine}}</strong><br>\n";
$riepilogo_ordine .= "Data di consegna previsto : <strong>{{data_consegna}}</strong><br>\n";
if(!empty($ordine['destinazione'])) $riepilogo_ordine .= "Destinazione merce : <strong>{{destinazione}}</strong><br>\n<br>\n";
$riepilogo_ordine .= "<h4>Dettagli ordine</h4>\n";
$riepilogo_ordine .= "{{tabella_dettagli}}<br>\n<br>\n";

$firma_email .= "<img src='".HTTP_PROTOCOL.HOSTROOT.SITEROOT."images/logo.png' height='60'>\n";

$data_email = array();
$data_email['rag_soc'] = $cliente['Ragione_Sociale'];
$data_email['data_consegna'] = $data_consegna;
$data_email['destinazione'] = $ordine['destinazione'];
$data_email['tabella_dettagli'] = $tabella_dettagli;
$data_email['num_ordine'] = $num_ordine;
$data_email['data_ordine'] = $data_ordine;
$data_email['utente'] = $_user->getName();


// DATI PER ADMIN
$subject = "Nuovo ordine su gestionale online - n. ".$num_ordine." del ".$data_ordine;

$email_text = "Buongiorno,<br>\n<br>\nE' stato affettuato un nuovo ordine sul gestionale online effettuato dall'utente: {{utente}}<br>\n<br>\n";
$email_text .= "Dati ordine:<br>\n";
$email_text .= $riepilogo_ordine;
$email_text .= "Buona giornata<br>\n";
$email_text .= $firma_email;

// templating
$email_text = template2($email_text, $data_email);

// Set adresses
$adresses = array('essepi.ortofrutta@gmail.com');

// invio email a admin
include("../required/send-email.php");


// DATI PER CLIENTE (SE C'E')
if(empty($cliente['Email'])){
	
	$output['error'] = "Nessun contatto"; // title of modal box
	$output['msg'] = "Il cliente <strong>".$cliente['Ragione_Sociale']."</strong> non ha attrbuito nessun indirizzo email; non è stato possibile inviare notifica.<br>L'ordine è stato tuttavia correttamente confermato."; // message inside modal box
	$output['errorlevel'] = "warning"; 
	echo json_encode($output);
	die();  
	
	//$cliente['Email'] = "antealdi@libero.it";
}

$subject = "Conferma ordine Essepi Ortofrutta n. ".$num_ordine." del ".$data_ordine;

$email_text = "Buongiorno {{rag_soc}},<br>\n<br>\n";

$email_text .= "Inviamo conferma del suo ordine presso Essepi Ortofrutta.<br>\n<br>\n";

$email_text .= $riepilogo_ordine;
$email_text .= "Le auguriamo una buona giornata<br>\n";
$email_text .= $firma_email;

// templating
$email_text = template2($email_text, $data_email);

// set adresses
$adresses = array($cliente['Email']);

// INVIO EMAIL CLIENTE
include("../required/send-email.php");


if(empty($_SESSION['error_title'])){
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = "L'ordine è stato confermato ed è stata inviata una notifica al cliente via email"; // message inside modal box
}else{
	$output['error'] = $_SESSION['error_title']; // title of modal box
	$output['msg'] =$_SESSION['error_message']; // message inside modal box	
	$output['errorlevel'] = "warning"; 
}


echo json_encode($output);



?>