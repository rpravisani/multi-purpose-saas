<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$asse = (int) $_POST['asse'];
$scheda = (int) $_POST['scheda']; 
$onoff = (int) $_POST['onoff']; 

$nome_asse = "assetto_asse_".$asse;
if($db->update(DBTABLE_SCHEDE_INTERVENTO, array($nome_asse => $onoff), "WHERE id = '".$scheda."'")){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
}else{
	$output['error'] = "Aggiornamento assetto assi"; // title of modal box
	$output['msg'] = "Errore durante l'aggiornamento del flag assetto assi.<br>".$db->getError("msg")."<br>".$db->getQuery(); // message inside modal box
}


echo json_encode($output);

	
?>
