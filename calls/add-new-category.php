<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$cat = $db->make_data_safe($_POST['value']);

$cat = ucwords(strtolower($cat));

$fields = array("nome", "_insertedby");
$values = array($cat, $_SESSION['login_id']);

if( $db->insert("data_categorie", $values, $fields ) ){
	$recordid = $db->get_insert_id();
	
	$output['options'] = $helper->getOptionsCategorie($recordid);
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['recordid'] = $recordid;
	
}else{
	
	$output['error'] = "Errore durante inserimento nuova categoria";
	$output['msg'] = $db->getError("msg")."<br>\n".$db->getquery();	
	
}

echo json_encode($output);

	
?>
