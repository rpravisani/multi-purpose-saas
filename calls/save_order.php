<?php
/*****************************************************
  TODO : invio email non mette 5 di sconoto corretto, mette 50% anche per retail
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['id']; // id prodotto
$varianti = $db->make_data_safe($_POST['varianti']);
$ordine = (int) $_POST['ordine']; // id ordine, potrebbe essere 0
$cliente = (int) $_POST['cliente']; // ha valore solo se loggato come cliente, se no è 0
$agente = (int) $_POST['agente']; // ha valore solo se loggato come cliente, se no è 0

/*** VARIABLES ***/
$ninserted = 0; // contatore
// campi tabella ordini_dettagli
$fields_dettagli = array( "ordine", "articolo", "variante", "qta", "prezzo", "insertedby" );
$unita_articoli = "art."; // TODO: translate
$simbolo_valuta = "€"; // TODO: prendere da user


// If $ordine variable is empty (0 or not defined) create ordine
if(empty($ordine)){
	$anno = date("Y", time());
	$data = date("Y-m-d H:i:s", time());
	
	// dati cliente
	$dettagli_cliente = $db->get1row(  DBTABLE_CLIENTI, "WHERE id = '".$cliente."'");
	$sede = $db->get1value( "id", DBTABLE_CLIENTI_SEDI, "WHERE cliente = '".$cliente."' ORDER BY principale DESC LIMIT 1");
	
	// listino e sconto listino in base alla categoria cliente
	$listino = $dettagli_cliente['categoria'];
	$perc_sconto_listino = (float) $db->get1value("sconto_listino", DBTABLE_CLIENTI_CATEGORIE, "WHERE id = '".$listino."'");
	
	// Calcolo progressivo ordini
	$progressivo = $db->get_max_row("progressivo", DBTABLE_ORDINI, "WHERE anno = '".$anno."'");
	if(empty($progressivo)) $progressivo = 0;
	$progressivo++;
	
	$fields_ordine = array("anno", "progressivo", "data", "cliente", "destinazione", "sconto_listino", "sconto_cliente", "agente", "insertedby");
	$values_ordine = array($anno, $progressivo, $data, $cliente, $sede, $perc_sconto_listino, $dettagli_cliente['sconto'], $agente, $_SESSION['login_id']);
	if($db->insert(DBTABLE_ORDINI, $values_ordine, $fields_ordine)){
		$ordine = $db->get_insert_id();
	}else{
		$output['error'] = "Errore registrazione ordine"; // title of modal box
		$output['msg'] = "Errore durante l'insetiemnto dell'ordine:.<br>".$db->getError("msg")."<br>".$db->getQuery(); // message inside modal box					
		echo json_encode($output);
		die();
	}
}

// procedo solo se ho varianti (dovrebbe sempre essere così ma on si sa mai)
if(!empty($varianti)){
	// loop varianti
	foreach($varianti as $variante => $details){
		$qta = (int) $details['qta'];
		$prezzo = $details['prezzo']; // prezzo unitario
		
		// array con valori nuovi da inserire 
		$values_dettagli = array( $ordine, $id, $variante, $qta, $prezzo, $_SESSION['login_id'] ); // TODO: gestione ordine, per ora è 0
		
		// creo array per eventuale update - unique key ordine, articolo, variante
		$update = array( "qta" => $qta, "prezzo" => $prezzo, "updatedby" => $_SESSION['login_id'] );
		
		if(!$db->insert(DBTABLE_ORDINI_DETTAGLI, $values_dettagli, $fields_dettagli, $update)){
			$output['error'] = "Errore registrazione dettaglio ordine"; // title of modal box
			$output['msg'] = "Errore durante l'insetiemnto del dettaglio ordine:.<br>".$db->getError("msg")."<br>".$db->getQuery(); // message inside modal box	
			echo json_encode($output);
			die();			
		}else{
			$output['dbg'] = $db->getQuery();
		}
		$ninserted++;
		
	}
}

/*** RIEPILOGO ORDINE ***/
$qry_riepilogo_ordine = "
	SELECT o.id, COUNT(DISTINCT d.articolo) AS qta, SUM(d.qta) AS pezzi, SUM(d.prezzo * d.qta) AS prezzo 
	FROM ".DBTABLE_ORDINI." AS o, ".DBTABLE_ORDINI_DETTAGLI." AS d 
	WHERE d.ordine = o.id AND o.id = '".$ordine."' 
	GROUP BY o.id
";

$ordine_riepilogo = $db->fetch_array_row($qry_riepilogo_ordine); 

if($ordine_riepilogo){
	$output['totqta'] 		= (int) $ordine_riepilogo['qta'];
	$output['totpezzi'] 	= (int) $ordine_riepilogo['pezzi'];
	$output['totprezzo'] 	= $ordine_riepilogo['prezzo'];
}


$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['inserted'] = $ninserted;
$output['ordine'] = $ordine;


echo json_encode($output);

	
?>
