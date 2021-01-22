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
$order 		= (int) $_POST['order'];

if(empty($articolo)){
	$output['error'] = "No post!";
	$output['msg'] = "Impossibile eliminare l'articolo dall'ordine: manca l'id dell'articolo!";
	echo json_encode($output);
	die();
}

if(empty($order)){
	$output['error'] = "No post!";
	$output['msg'] = "Impossibile eliminare l'articolo dall'ordine: manca l'id dell'ordine!";
	echo json_encode($output);
	die();
}


if($db->delete(DBTABLE_ORDINI_DETTAGLI, "WHERE articolo = '".$articolo."'")){
	$output['result'] 	= true;
	$output['error'] 	= "";
	$output['msg'] 		= "";
}else{
	$output['error'] 	= "Impossibile Eliminare Articolo da Ordine";
	$output['msg'] 		= "Errore durante la cancellazione dell'artciolo dall'ordine:<br>".$db->getError("msg")."<br>Query: ".$db->getQuery();
}

echo json_encode($output);

	
?>
