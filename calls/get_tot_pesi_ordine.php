<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$id = (int) $_POST['id'];

$row = $helper->getOrderPesiTotal($id, true);


if($row){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['html'] = $row;
}else{
	$output['error'] = "no-row";
	$output['msg'] = "Nessun dettaglio ordine trovato!";	
}

echo json_encode($output);

	
?>
