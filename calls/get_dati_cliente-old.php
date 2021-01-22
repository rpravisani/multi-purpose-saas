<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id 	= (int) $_POST['id'];
$order 	= (int) $_POST['order'];
$update = (isset($_POST['update'])) ? $_POST['update'] : false;


$dettagli_cliente 	= $db->get1row(DBTABLE_CLIENTI, "WHERE id = '".$id."'");
// prende sede con principale = 1. Come fallback il primo in ordine di inserimento
$dettagli_sede 		= $db->get1row(DBTABLE_CLIENTI_SEDI, "WHERE cliente = '".$id."' AND active = '1' ORDER BY principale DESC, id ASC LIMIT 1"); 

if(empty($dettagli_sede)){
	// non ho sedi (attive) - indirizzo sede attribuisco quello del cliente e disabilito il select sede
	$dettagli_sede = $dettagli_cliente; 
	$elenco_sedi = "<option></option>";
	$disabled_sede = true;
	$sede = 0;
}else{
	$sede = $dettagli_sede['id'];
	$elenco_sedi = getSelectOptions("id", "nome_sede", DBTABLE_CLIENTI_SEDI, $sede, false, "WHERE cliente = '".$id."' AND active = '1'", false);
	$disabled_sede = false;
}

// recupero email principale (la prima) da sede, se vuota prendo email da tab cliente, se anche questo non è presente setto campo vuoto
if(!empty($dettagli_sede['emails'])){
	$emails = unserialize($dettagli_sede['emails']);
	$email = reset($emails);
}elseif(!empty($dettagli_sede['email'])){
	$email = $dettagli_sede['email'];
}else{
	$email = "";
}

// recupero telefono principale (il prima) da sede, se vuota prendo email da tab cliente, se anche questo non è presente setto campo vuoto
if(!empty($dettagli_sede['telefoni'])){
	$telefoni = unserialize($dettagli_sede['telefoni']);
	$telefono = reset($telefoni);
}elseif(!empty($dettagli_sede['telefono'])){
	$telefono = $dettagli_sede['telefono'];
}else{
	$telefono = "";
}

if(!empty($id)){

	$indirizzo_cliente  = "<div class='well'>\n";
	$indirizzo_cliente .= $dettagli_cliente['indirizzo']."<br>\n";
	$indirizzo_cliente .= $dettagli_cliente['cap']." ";
	$indirizzo_cliente .= ucfirst(strtolower($dettagli_cliente['localita']));
	$indirizzo_cliente .= (!empty($dettagli_cliente['prov'])) ? " (".strtoupper($dettagli_cliente['prov']).")" : "";
	$indirizzo_cliente .= "<br>\n";
	$indirizzo_cliente .= (!empty($dettagli_cliente['piva'])) ? "P.iva ".$dettagli_cliente['piva'] : "";
	$indirizzo_cliente .= (!empty($dettagli_cliente['cod_fisc'])) ? " • C.F. ".$dettagli_cliente['cod_fisc'] : "";
	$indirizzo_cliente .= "</div>\n";

	$indirizzo_sede  = "<div class='well'>\n";
	$indirizzo_sede .= $dettagli_sede['indirizzo']."<br>\n";
	$indirizzo_sede .= $dettagli_sede['cap']." ";
	$indirizzo_sede .= ucfirst(strtolower($dettagli_sede['localita']));
	$indirizzo_sede .= (!empty($dettagli_sede['prov'])) ? " (".strtoupper($dettagli_sede['prov']).")" : "";
	$indirizzo_sede .= "<br>\n";
	$indirizzo_sede .= (!empty($telefono)) ? "Telefono ".$telefono : "";
	$indirizzo_sede .= (!empty($email)) ? " • Email ".$email : "";
	$indirizzo_sede .= "</div>\n";
}

// aggiorno cliente e sede in ordine
if($update and !empty($order)){
	$db->update(DBTABLE_ORDINI, array("cliente" => $id, "destinazione" => $sede), "WHERE id = '".$order."'");
}



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['indirizzo_cliente'] = $indirizzo_cliente;
$output['indirizzo_sede'] = $indirizzo_sede;
$output['sede'] = $sede;
$output['sedi'] = $elenco_sedi;
$output['disabled_sede'] = $disabled_sede;



echo json_encode($output);

	
?>
