<?php
/*****************************************************
 * get_select_nuovo_prodotto                           *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['ordine'];

// get dati ordine per feedback
$articoli_ordine = $db->col_value("articolo", DBTABLE_ORDINI_DETTAGLI, "WHERE ordine = '".$id."'", true);


if(!$articoli_ordine){
	$output['error'] = "L'ordine non esiste";
	$output['msg'] = "L'ordine n. ".$id." è innesistente!";
	echo json_encode($output);
	die();
}

$flat = implode(",", $articoli_ordine);


$articoli = $db->key_value("id", "sku", "data_prodotti", "WHERE active = '1' AND id NOT IN (".$flat.")");
$select = "";
if($articoli){
	
	$options = "<option value=''></option>\n";
	foreach($articoli as $articolo => $sku){
		$options .= "<option value='".$articolo."'>".$sku."</option>\n";
	}
	if(!empty($options)){
		$select = "<select style='width: 180px' id='articolo-da-aggiungere' class='select2'>".$options."</select>\n";
	}
}else{
	$select = "Nessun articolo trovato";
}

$select = "<div class='row'>\n<div class='col-sm-12'><label for='articolo-da-aggiungere'>Seleziona articolo<br>".$select."</label></div>\n</div>\n";


$output['result'] = true;
$output['error'] = "Aggiungi Articolo";
$output['msg'] = $select;



echo json_encode($output);

?>
