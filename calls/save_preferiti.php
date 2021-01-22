<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$articolo 	= (int) $_POST['articolo'];
$cliente 	= (int) $_POST['cliente'];

if(empty($articolo) or empty($cliente)){	
	echo json_encode($output);
	die();	
}

$check = $db->get1value("id", "data_preferiti", "WHERE cliente = '".$cliente."' AND articolo = '".$articolo."'");

if(empty($check)){
	$db->insert("data_preferiti", array($cliente, $articolo), array("cliente", "articolo"));
}else{
	$db->delete("data_preferiti", "WHERE id = '".$check."'");	
}

$output['result'] = true;
$output['error'] = ""; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['errorlevel'] = ""; // color of modal box

echo json_encode($output);

	
?>
