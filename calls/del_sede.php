<?php
/*****************************************************
 * get_sedi_cliente   RIMASTO QUA, DEVO MODIFICARE QUESTO SCRIPT                               *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$cid 		= (int) $_POST['cliente'];
$delme 		= (int) $_POST['sede'];
$newsede 	= (int) $_POST['newsede'];
$dbg = "";

// cancello da tab clienti_sedi 
if( $db->delete(DBTABLE_CLIENTI_SEDI, "WHERE id = '".$delme."'") ){
	
	// Qui sotto inserire logica per spsostare dati assegnati alla sede in questione ad un'altra... sembra che ve ne sia bisogno
	/*
	$tabelle = array(DBTABLE_GOMMISTI_X_SEDI, DBTABLE_SCHEDE_INTERVENTO, DBTABLE_STOCK_GOMME, DBTABLE_TARGHE);
	foreach($tabelle as $tabella){
		$campo = ($tabella == DBTABLE_STOCK_GOMME) ? "dislocazione" : "sede";
		$valori = array($campo => $newsede);
		$db->update($tabella, $valori, "WHERE ".$campo." = '".$delme."'");
		$dbg .= $db->getQuery()."\n";	
	}
	*/


	$output['result'] = true;
	$output['error'] = "";
	$output['dbg'] = $dbg;
	$output['msg'] = "";

	
}else{
	$output['error'] = "Impossibile cancellare sede!";
	$output['msg'] = "Errore durante cancellazione sede.<br>".$db->getError("msg")."<br>".$db->getQuery();

}


echo json_encode($output);

	
?>
