<?php
/*****************************************************
 * Recupera tutti i clienti / fornitori e li restituisce in base al valore $_POST['returnme']    
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$returnme = $db->make_data_safe($_POST['returnme']);
$selected = (int) $db->make_data_safe($_POST['selected']);
$args = $db->make_data_safe($_POST['args']);

if(!empty($args)){
	
	$selected = (int) $db->get1value($args['field'], $args['table'], "WHERE id = '".$args['id']."'");
	$output['dbg'] = $db->getquery();
	$output['dbg2'] = $selected;
}


$clienti = $helper->get_clienti($returnme, $selected);

if(!empty($clienti)){
	
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['list'] = ($returnme == 'options' or $returnme == 'select') ? array() : $clienti;
	$output['html'] = ($returnme == 'options' or $returnme == 'select') ? $clienti : "";	
	
}else{
	$output['error'] = "no-cliente";
	$output['msg'] = "Nessun cliente trovato!";
	
}


echo json_encode($output);

	
?>
