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

$dettagli_sede 	= $db->get1row(DBTABLE_CLIENTI_SEDI, "WHERE id = '".$id."'");
	
if(!empty($dettagli_sede['emails'])){
	$emails = unserialize($dettagli_sede['emails']);
	$email = reset($emails);
}else{
	$email = $db->get1value("email", DBTABLE_CLIENTI, "WHERE id = '".$dettagli_sede['cliente']."'");
	if(empty($email)) $email = "";
}

if(!empty($dettagli_sede['telefoni'])){
	$telefoni = unserialize($dettagli_sede['telefoni']);
	$telefono = reset($telefoni);
}else{
	$telefono = $db->get1value("telefono", DBTABLE_CLIENTI, "WHERE id = '".$dettagli_sede['cliente']."'");
	if(empty($telefono)) $telefono = "";
}

$indirizzo_sede  = "<div class='well'>\n";
$indirizzo_sede .= "<div class=''>\n";
$indirizzo_sede .= $dettagli_sede['indirizzo']."<br>\n";
$indirizzo_sede .= $dettagli_sede['cap']." ";
$indirizzo_sede .= ucfirst(strtolower($dettagli_sede['localita']));
$indirizzo_sede .= (!empty($dettagli_sede['prov'])) ? " (".strtoupper($dettagli_sede['prov']).")" : "";
$indirizzo_sede .= "<br>\n";
$indirizzo_sede .= (!empty($telefono)) ? "Tel.: ".$telefono : "";
$indirizzo_sede .= (empty($telefono) and empty($email)) ? "" : "<br>";
$indirizzo_sede .= (!empty($email)) ? "Email: ".$email : "";
$indirizzo_sede .= "</div>\n";
$indirizzo_sede .= "</div>\n";
	
// aggiorno cliente e sede in ordine
if($update and !empty($order)){
	$db->update(DBTABLE_ORDINI, array("destinazione" => $id), "WHERE id = '".$order."'");
}



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['indirizzo_sede'] = $indirizzo_sede;


echo json_encode($output);

	
?>
