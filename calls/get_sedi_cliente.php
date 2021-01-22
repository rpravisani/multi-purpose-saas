<?php
/*****************************************************
 * get_sedi_cliente                                  *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$cid 		= (int) $_POST['cliente'];
$sid 		= (int) $_POST['sede'];

// set default value of options
$options = (empty($_POST['noempty'])) ? "<option></option>\n" : ""; 

// get list of sedi
$elenco_sedi = $db->key_value("id", "nome_sede", DBTABLE_CLIENTI_SEDI, "WHERE active = '1' AND cliente = '".$cid."' AND id != '".$sid."' ORDER BY nome_sede");

// if found, create html block for options
if($elenco_sedi){
	foreach($elenco_sedi as $sid=>$sede){
		$options .= "<option value=\"".$sid."\">".$sede."</option>\n";
	}
}

// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $options;

echo json_encode($output);

	
?>
