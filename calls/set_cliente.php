<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';


$cliente = (int) $_POST['cliente'];
$_SESSION['cliente'] = $cliente;
$ordine = (int) $_POST['ordine'];

$start = microtime(true);
$c = 0;


if(!empty($ordine)){
	// se ho un ordine settato aggiorno ordine e prezzi
	
	$old_cliente = $db->get1value("cliente", DBTABLE_ORDINI, "WHERE id = '".$ordine."'");
	
	$dettagli_cliente = $db->get1row(DBTABLE_CLIENTI, "WHERE id = '".$cliente."'");
	
	$sede = $db->get1value( "id", DBTABLE_CLIENTI_SEDI, "WHERE cliente = '".$cliente."' ORDER BY principale DESC LIMIT 1");
	
	$listino_new = $dettagli_cliente['listino'];
	$listino_old = $db->get1value("listino", DBTABLE_CLIENTI, "WHERE id = '".$old_cliente."'");
	
	$db->update(DBTABLE_ORDINI, array("cliente" => $cliente, "destinazione" => $sede, "forma_pagamento" => $dettagli_cliente['pagamento'], "spedizione" => $dettagli_cliente['spedizione']), "WHERE id = '".$ordine."'");
	
	if($listino_new != $listino_old){
		// update anche prezzi
		$articoli = $db->select_all(DBTABLE_ORDINI_DETTAGLI, "WHERE ordine = '".$ordine."'");
		$c = count($articoli);
		if($articoli){
			foreach($articoli as $row){
				$prezzo = $db->get1value("prezzo", DBTABLE_PRODOTTI_VARIANTI, "WHERE prodotto = '".$row['articolo']."' AND variante = '".$row['variante']."' AND listino = '".$listino_new."'");
				$db->update(DBTABLE_ORDINI_DETTAGLI, array("prezzo" => $prezzo), "WHERE ordine = '".$ordine."' AND articolo = '".$row['articolo']."' AND variante = '".$row['variante']."'");
			}
		}
	}
	
}

$end = microtime(true);
$diff = $end-$start;



$output['result'] = true;
$output['error'] =""; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['errorlevel'] = ""; // color of modal box
$output['qry'] = "";
$output['dbg'] = "";
$output['updated'] = $c;
$output['elapsed_time'] = $diff;

echo json_encode($output);

	
?>
