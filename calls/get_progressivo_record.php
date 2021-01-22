<?php
/*****************************************************
 * Recupero il primo numero progressivo del progetto
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$project = (int) $_POST['project'];

// TODO verifica che il progetto sia del cliente
$project_data = $db->get1row("data_projects", "WHERE id = '".$project."'");

if(empty($project_data)){
    $output['error'] = "no-prject";
    $output['msg'] = "Il progetto non esiste";
    echo json_encode($output);
    die();    
}

$progressivo = (int) $db->get_max_row("number", "data_reports", "WHERE project = '".$project."'");
if(empty($progressivo)) $progressivo = 0;

$progressivo++;


// OUTPUT
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['progressivo'] = str_pad($progressivo, 5, "0", STR_PAD_LEFT);


echo json_encode($output);

?>
