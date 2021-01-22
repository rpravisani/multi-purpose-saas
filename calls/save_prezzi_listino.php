<?php
/*****************************************************
 *  
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$fields = array("listino", "articolo", "prezzo", "valido_fino");
$dt = new DateTime();
$dt->modify("+30 days"); // serve per "valido_fino"


$listino  =  (int)  $db->make_data_safe($_POST['listino']);
$cliente  =  (int)  $db->make_data_safe($_POST['cliente']);
$ricarico = (float) $db->make_data_safe($_POST['ricarico']);

if(empty($listino)){
	$output['error'] = "nessun-listino";
	$output['msg'] = "Il listino passato non è valido";
	echo json_encode($output);
	die();
	
}

$righe = count($_POST['prezzi']);
$i = 0;

if(!empty($_POST['prezzi'])){
	
	// svuoto listini_prezzi
	$db->delete(DBTABLE_LISTINI_PREZZI, "WHERE listino = '".$listino."'");
	
	foreach($_POST['prezzi'] as $cod => $prezzo){
		
		$prezzo = (float) trim($prezzo);
		
		$values = array($listino, $cod, $prezzo, $dt->format("Y-m-d"));
		if(!$db->insert(DBTABLE_LISTINI_PREZZI, $values, $fields)){
			$output['error'] = "Errore inserimento";
			$output['msg'] = "Inserimento fermato dopo $i righe su $righe";
			echo json_encode($output);
			die();
			
		}
		$i++;
	}
	
}else{
	$output['error'] = "no-prezzi";
	$output['msg'] = "Non sono stati passati i prezzi";
	
}

	
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['dbg'] = $i;


echo json_encode($output);

	
?>
