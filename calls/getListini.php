<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$_t->setSection("tabella-listini");

$listini = getSelectOptions("id", "listino", DBTABLE_LISTINI, array(), "listino", "", false);

if($listini){
	$selectvalue = "<div class='row'><div class='col-md-10'><select id='listino-copy' class='select2'>".$listini."</select></div></div>";

	$output['result'] = true;
	$output['error'] = $_t->get('copia-prezzi-listino'); // title of modal box
	$output['errorlevel'] = "default"; // color of modal box	
	$output['msg'] = $_t->get('copia-prezzi-listino-message').$selectvalue; // translation in page specific translation
}else{
	$output['error'] = $_t->get('nessun-listino'); // title of modal box
	$output['msg'] = $_t->get('nessun-message'); // message inside modal box	
}


echo json_encode($output);


	
?>
