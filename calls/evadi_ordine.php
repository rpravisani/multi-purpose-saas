<?php
/*****************************************************
 * evadi_ordine                           
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize data
$ordine = (int) $_POST['ordine'];

// table fields for insert
$fields = array("ordine", "articolo", "variante", "qta", "spedito", "prezzo", "added", "insertedby");

// dividi gli articoli nuovi da quelli già presenti in ordine
$update = $db->make_data_safe($_POST['data']['update']);
$insert = $db->make_data_safe($_POST['data']['insert']);

// flag se l'ordine deve essere evaso o meno
$evadi = (empty($_POST['evadi'])) ? false : true;

// setto contatori
$updated = $inserted = 0;

// aggiorno le quantità di "spedito" in tabella ordini_dettagli
if(!empty($update)){
	
	// loop array con le quantita "spedito"
	foreach($update as $id=>$spedito){
		// aggiorno quantità spedito e aggiorno campo updatedby con uder_id di chi effettua l'aggiornamento
		if($db->update(DBTABLE_ORDINI_DETTAGLI, array("spedito" => $spedito, "updatedby" => $_SESSION['login_id']), "WHERE id = '".$id."'")){
			$updated++;
		}else{
			$errors[] = $db->geterror("msg");
		}
	}
}

// gestione articoli nuovi
if(!empty($insert)){
	foreach($insert as $row){
		// inserisco id articolo, id variante, ordinato a 0, le qtà spedite, il prezzo di listino, added, user id per campo inserted 
		$values = array( $ordine, $row['articolo'], $row['variante'], 0, $row['spedito'], $row['prezzo'], 1, $_SESSION['login_id'] );
		if($db->insert(DBTABLE_ORDINI_DETTAGLI, $values, $fields)){
			$inserted++;
			$dbg[] = $db->getquery();
		}else{
			$errors[] = $db->geterror("msg");
		}
	}
}

// imposto flag salvato a 1 a prescindere
$update_ordine = array("salvato" => "1");

// Se l'ordine viene evaso setto anche lo stato su "evaso"
if($evadi){
	
	$update_ordine['stato'] = "evaso";
	$_SESSION['success_title'] = "Ordine evaso"; // TODO translation
	$_SESSION['success_message']	= "L'ordine è stato evaso. Non sarà più possibile apportare modifiche. E' stata invaita un email di avvenuta evasione al cliente."; // TODO translation
	
		
	
	/*** INVIO EMAIL VERRA' EFFETTUATO ATTRAVERSO UNA CHIAMATA AJAX A invio-email-ordine.php ***/
	
}

// aggiorno ordine
$db->update(DBTABLE_ORDINI, $update_ordine, "WHERE id = '".$ordine."'");

// feedback ajax
$output['result'] = true;
$output['error'] = "Modifiche salvate";
$output['msg'] = "Le quantità spedite sono state correttamente aggiornate.";


echo json_encode($output);

?>
