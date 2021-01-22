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

// vars
$default_email_tag = "<i style='margin-left: 4px; cursor: help' data-toggle='tooltip' class='fa fa-paper-plane text-green' title='Indirizzo a cui verrà inviata copia ordine'></i>";
$costo_spedizione = 0;

$dettagli_cliente 	= $db->get1row(DBTABLE_CLIENTI, "WHERE id = '".$id."'");
// prende sede con principale = 1. Come fallback il primo in ordine di inserimento
$dettagli_sede 		= $db->select_all(DBTABLE_CLIENTI_SEDI, "WHERE cliente = '".$id."' AND active = '1' ORDER BY principale DESC, id ASC"); 

if(empty($dettagli_sede)){
	// non ho sedi (attive) - indirizzo sede attribuisco quello del cliente e disabilito il select sede
	$dettagli_sede = $dettagli_cliente; 
	
	$elenco_sedi = "<option></option>";
	$disabled_sede = true;
	$sede = 0;
}else{
	// HO SEDI
	$sede = $dettagli_sede[0]['id'];
	$elenco_sedi = getSelectOptions("id", "nome_sede", DBTABLE_CLIENTI_SEDI, $sede, false, "WHERE cliente = '".$id."' AND active = '1'", false);
	$disabled_sede = (count($dettagli_sede) == 1) ? true : false;
}


/*** EMAIL CLIENTE (SENZA SEDE) / AMMINISTRAZIONE ***/
if(!empty($dettagli_cliente['email'])){
	$email = $dettagli_cliente['email'].$default_email_tag;
}else{
	$email = "";
}

if(!empty($dettagli_cliente['telefono'])){
	$telefono = $dettagli_cliente['telefono'];
}else{
	$telefono = "";
}


/*** EMAIL SEDE (PRINCIPALE) ***/
// recupero email principale (la prima) da sede, se vuota prendo email da tab cliente, se anche questo non è presente setto campo vuoto
if(!empty($dettagli_sede[0]['emails'])){
	$emails_sede = unserialize($dettagli_sede[0]['emails']);
	$email_sede = reset($emails_sede);
	if(empty($email)) $email_sede .= $default_email_tag; // se email amministrativa è vuota aggiunto tag a questa email per indicare che è principale
}elseif(!empty($dettagli_cliente['email'])){
	$email_sede = $dettagli_cliente['email'];
}else{
	$email_sede = "";
}

// recupero telefono principale (il prima) da sede, se vuota prendo email da tab cliente, se anche questo non è presente setto campo vuoto
if(!empty($dettagli_sede[0]['telefoni'])){
	$telefoni_sede = unserialize($dettagli_sede[0]['telefoni']);
	$telefono_sede = reset($telefoni_sede);
}elseif(!empty($dettagli_cliente['telefono'])){
	$telefono_sede = $dettagli_cliente['telefono'];
}else{
	$telefono_sede = "";
}



if(!empty($id)){

	$indirizzo_cliente  = "<div class='well'>\n<div>\n";
	$indirizzo_cliente .= $dettagli_cliente['indirizzo']."<br>\n";
	$indirizzo_cliente .= $dettagli_cliente['cap']." ";
	$indirizzo_cliente .= ucfirst(cc_strtolower($dettagli_cliente['localita']));
	$indirizzo_cliente .= (!empty($dettagli_cliente['prov'])) ? " (".strtoupper($dettagli_cliente['prov']).")" : "";
	$indirizzo_cliente .= "<br>\n";
	$indirizzo_cliente .= (!empty($telefono)) ? "Tel.: ".$telefono : "";
	$indirizzo_cliente .= (empty($email) and empty($telefono)) ? "" : "<br>";
	$indirizzo_cliente .= (!empty($email)) ? "Email: ".$email : "";
	$indirizzo_cliente .= (empty($email) and empty($telefono)) ? "" : "<br>";
	$indirizzo_cliente .= (!empty($dettagli_cliente['piva'])) ? "P.iva ".$dettagli_cliente['piva'] : "";
	$indirizzo_cliente .= (!empty($dettagli_cliente['cod_fisc'])) ? " • C.F. ".$dettagli_cliente['cod_fisc'] : "";
	$indirizzo_cliente .= "</div>\n</div>\n";

	$indirizzo_sede  = "<div class='well'>\n";
	$indirizzo_sede .= "<div class=''>\n";
	$indirizzo_sede .= $dettagli_sede[0]['indirizzo']."<br>\n";
	$indirizzo_sede .= $dettagli_sede[0]['cap']." ";
	$indirizzo_sede .= ucfirst(cc_strtolower($dettagli_sede[0]['localita']));
	$indirizzo_sede .= (!empty($dettagli_sede[0]['prov'])) ? " (".strtoupper($dettagli_sede[0]['prov']).")" : "";
	$indirizzo_sede .= "<br>\n";
	$indirizzo_sede .= (!empty($telefono_sede)) ? "Tel.: ".$telefono_sede : "";
	$indirizzo_sede .= (empty($telefono_sede) and empty($email_sede)) ? "" : "<br>";
	$indirizzo_sede .= (!empty($email_sede)) ? "Email: ".$email_sede : "";
	$indirizzo_sede .= "</div>\n";
	$indirizzo_sede .= "</div>\n";
	
	// se non sono cliente...
	if($_user->getSubscriptionType() != '3'){
		// Recupero options pagamenti da tab cliente
		$elenco_pagamenti = getSelectOptions("id", "nome", DBTABLE_FORME_PAGAMENTO, $dettagli_cliente['pagamento'], false, "WHERE active = '1'", true);
		$html_pagamento = $bootstrap->select2("Forma di Pagamento", "pagamento", $elenco_pagamenti, false);

		// Recupero elenco spedizioni per radioboxes
		$selected_spedizione = (empty($dettagli_cliente['spedizione'])) ? '1' : $dettagli_cliente['spedizione'];		
		$qry_sped = "SELECT costo, IF(costo > 0, CONCAT(nome, ' <em>(+', costo, '€)</em>'), nome) as label, id FROM ".DBTABLE_SPEDIZIONI." WHERE active = '1'";
		$spedizioni = $db->fetch_array($qry_sped);
		if($spedizioni){
			foreach($spedizioni as $spedizione){
				if($selected_spedizione == $spedizione['id']) $costo_spedizione = $spedizione['costo'];
				$elenco_spedizioni[$spedizione['label']] = $spedizione['id'];
			}
		}else{
			$elenco_spedizioni = array();
		}		

		$html_spedizione = $bootstrap->radioboxes("Tipo di Spedizione", "spedizione", $elenco_spedizioni, $selected_spedizione);
		
	}
	
}

// aggiorno cliente e sede in ordine
if($update and !empty($order)){
	$old_cliente = $db->get1value("cliente", DBTABLE_ORDINI, "WHERE id = '".$order."'");
	
	$listino_old = $db->get1value("listino", DBTABLE_CLIENTI, "WHERE id = '".$old_cliente."'");
	$listino_new = $dettagli_cliente['listino'];
	
	$db->update(DBTABLE_ORDINI, array("cliente" => $id, "destinazione" => $sede, "forma_pagamento" => $dettagli_cliente['pagamento'], "spedizione" => $dettagli_cliente['spedizione']), "WHERE id = '".$order."'");
	
	// aggiorno prezzi se ce n'è bisogno
	$listino_new = $dettagli_cliente_new['listino'];
	


}



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['indirizzo_cliente'] = $indirizzo_cliente;
$output['indirizzo_sede'] = $indirizzo_sede;
$output['pagamento'] = $html_pagamento;
$output['spedizione'] = $html_spedizione;
$output['sede'] = $sede;
$output['sedi'] = $elenco_sedi;
$output['disabled_sede'] = $disabled_sede;
$output['sconto_cliente'] = $dettagli_cliente['sconto'];
$output['costo_spedizione'] = $costo_spedizione;



echo json_encode($output);

	
?>
