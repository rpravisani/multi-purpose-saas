<?php
/*****************************************************
 *                          *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// $values sarà un array con chiave 'cell_xxxx', dove xxx è id tab prodotti_prezzo, e come valore il prezzo impostato
$values = $db->make_data_safe($_POST['values']);

// get page name
$page = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");

// if page is not set: send error
if(!$page){
	$output['error'] = $_t->get('pagenotset'); // translation in general section 
	$output['msg'] = sprintf ($_t->get('pagenotset_message'), $pid); // translation in general section 
	echo json_encode($output);
	die();
}

// get translations specific for switch file (basically the stop_message)
$_t->setSection($page, true); // second param is true so the translations of the section will be added to the existing ones

// set filename
$page = $page.".php";

// include switchfile if it exists, else send error
if(file_exists("grid/".$page)){
	include_once "grid/".$page;
}else{
	
	$output['error'] = $_t->get('noswitch_file'); // translation in general section 
	$output['msg'] = sprintf($_t->get('noswitch_file_message'), $page); // translation in general section 
	echo json_encode($output);
	die();
}

// in switchfile vengono definiti: $table, $field (il campo da aggiornare) e $key (il campo da user in clausola where)
foreach($values as $cell=>$value){
	
	// il prezzo arriva dalla grigia con la virgola come separatore decimale, lo trasformo in formato US
	$value = currency_safe($value, $_user->getCurrencyDecimals()); // TODO metter in file switch
	
	$update = array();
	
	$update[$field] = $value; // Field definito in file switch
	$id = substr($cell, 5); // elimino il refisso 'cell_'
	
	// aggiorno tabella (definita in file switch)
	if($db->update($table, $update, "WHERE ".$key." = '".$id."'")){
		$results[$cell] = $value;
	}else{
		$results[$cell] = "KO";
		
	}
}

$output['result'] = true;
$output['updated'] = $results; // per debug, elenco dei record aggiornati
$output['error'] = "";
$output['msg'] = "";
$output['inserted'] = $ninserted;
$output['ordine'] = $ordine;


echo json_encode($output);

	
?>
