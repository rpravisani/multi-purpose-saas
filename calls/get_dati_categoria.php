<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';


$categoria = (int) $_POST['categoria'];


$dati = $db->get1row("data_clienti_categorie", "WHERE id = '".$categoria."'");

$output['dati'] = $dati;

$output['result'] = true;
$output['error'] = ""; // title of modal box
$output['msg'] = "ok"; // message inside modal box
$output['errorlevel'] = ""; // color of modal box



echo json_encode($output);

	
?>
