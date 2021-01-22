<?php
/*****************************************************
 * get_modelli_pneumatici                            *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['ordine'];

// get dati ordine per feedback
$ordine = $db->get1row(DBTABLE_ORDINI, "WHERE id = '".$id."'");

if(!$ordine){
	$output['error'] = "L'ordine non esiste";
	$output['msg'] = "L'ordine n. ".$id." è innesistente!";
	echo json_encode($output);
	die();
}

$num_ordine = str_pad($ordine['progressivo'], 4, '0', STR_PAD_LEFT)."/".substr($ordine['anno'], 2,2); // TODO: parametrizzare il pad length
$data_ordine = cc_date_us2eu( substr( $ordine['data'], 0, 10) ) ;



$proceed = ($ordine['stato'] == "aperto" or $_user->getSubscriptionType() == "SA" or $_user->getSubscriptionType() == "1" or $_user->getSubscriptionType() == "4" ) ? true : false;


if($proceed ){

	// ELIMINA ORDINE E DETTGLIO ORDINE DA DB

	if( $db->delete(DBTABLE_ORDINI, "WHERE id = '".$id."'") ){

		if( $db->delete(DBTABLE_ORDINI_DETTAGLI, "WHERE ordine = '".$id."'") ){

			$_SESSION['success_title'] 		= "Ordine cancellato";
			$_SESSION['success_message'] 	= "L'ordine<strong> ".$num_ordine."</strong> del <strong>".$data_ordine."</strong> è stato correttamente cancellato.";
			$output['result'] = true;
			$output['error'] = "";
			$output['msg'] = "";
			$output['url'] = "cpanel.php?pid=61&v=fullscreen";

		}else{

			$output['error'] = "Impossibile eliminare ordine";
			$output['msg'] = "Errore durante la eliminazione del dettaglio ordine<br>(rif. #".$id.")";

		}

	}else{

		$output['error'] = "Impossibile eliminare ordine";
		$output['msg'] = "Errore durante la eliminazione dell'ordine<br>(rif. #".$id.")";

	}

	
}else{
	
	$output['error'] = "Requisiti non sufficienti!";
	$output['msg'] = "Non hai i permessi per eliminare quest'ordine";
	
}

echo json_encode($output);

?>
