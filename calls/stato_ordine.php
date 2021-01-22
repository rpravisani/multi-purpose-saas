<?php
/*****************************************************
 *                    *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['ordine'];

// get dati ordine per feedback
$ordine = $db->get1row(DBTABLE_ORDINI, "WHERE id = '".$id."'");

if(!$ordine){
	$output['error'] = "L'ordine non esiste";
	$output['msg'] = "L'ordine n. ".$id." è innesistente!";
	echo json_encode($output);
	die();
}

$num_ordine = str_pad($ordine['progressivo'], 4, '0', STR_PAD_LEFT)."/".substr($ordine['anno'], 2,2); // TODO: parametrizzare il pad length
$data_ordine = cc_date_us2eu( substr( $ordine['data'], 0, 10) ) ;

$stato_precedente = $ordine['stato_precedente'];
$stato_attuale = $ordine['stato'];
$stato_new = $db->make_data_safe($_POST['stato']);

// se riattivo devo ripristinare lo stato precedente dell'ordine 
if($stato_new == 'reactivate') $stato_new = $stato_precedente;

$proceed = false;

// decido se posso effettuare tale modifica
switch($stato_new){
	case "aperto":
		$icona = "fa-exclamation";
		$color_class = "yellow";
		// da decidere se solo così o anche in altre occasioni
		if($_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" ) $proceed = true;
		break;
	case "elaborato":
		$icona = "fa-check";
		$color_class = "green";
		// posso impostarlo su elaborato solo se era aperto o se sono un admin
		if( $stato_attuale == "aperto" or $_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" ) $proceed = true;
		break;
	case "evaso":
		$icona = "fa-truck";
		$color_class = "green";
		// solo SA, admin e collaboratori
		if($_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" or $_user->getSubscriptionType() == "4" ) $proceed = true;
		break;
	case "fatturato":
		$icona = "fa-usd";
		$color_class = "green";
		// solo SA, admin e collaboratori
		if($_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" or $_user->getSubscriptionType() == "4" ) $proceed = true;
		break;
	case "annullato":
		$icona = "fa-times";				
		$color_class = "red";
		// posso impostarlo su annullato solo se era elaborato o se sono un admin
		if( $stato_attuale == "elaborato" or $_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" ) $proceed = true;
		break;
	default:
		$output['error'] = "Stato ordine non permesso!";
		$output['msg'] = "Stai provando ad impostare un stato (".$stato_new.") non permesso";
		echo json_encode($output);
		die();	
		break;
}

if(!$proceed){
		$output['error'] = "Requisiti non sufficienti!";
		$output['msg'] = "Non hai i permessi per cambiare lo stato a quest'ordine";
		echo json_encode($output);
		die();	
}


$update = array("stato" => $stato_new, "stato_precedente" => $stato_attuale );
if($stato_new == "annullato") $update['data_annullato '] = date("Y-m-d");
if($stato_attuale == "annullato") $update['data_annullato '] = "";

if( $db->update(DBTABLE_ORDINI, $update, "WHERE id = '".$id."'") ){
	
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['errorlevel'] = "";
	$output['icona'] = $icona;
	$output['classe'] = $color_class;
	$output['stato'] = ucfirst($stato_new);
	$output['precedente'] = $stato_precedente;	
	
}else{
	
	$output['error'] = "Errore durante cambio stato ordine";
	$output['msg'] = "C'è stato un errore durante l'aggiornamento dello stato dell'ordine:<br>".$db->getError('msg')."<br>".$db->getquery();
	
}


echo json_encode($output);

?>
