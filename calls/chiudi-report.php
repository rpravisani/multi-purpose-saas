<?php
/*****************************************************
 * Recupero il primo numero progressivo del progetto
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$report = (int) $_POST['report'];

// TODO verifica che il progetto sia del cliente
$report_data = $db->get1row("data_reports", "WHERE id = '".$report."'");

if(empty($report_data)){
    $output['error'] = "no-report";
    $output['msg'] = "Il report non esiste";
    echo json_encode($output);
    die();    
}

if( $db->update("data_reports", array("state" => "Completato"), "WHERE id = '".$report."'") ){
    $output['result'] = true;
    $output['error'] = "";
    $output['msg'] = "";
    
}else{
    $output['error'] = "update";
    $output['msg'] = "Impossibile aggiornare lo stato del report";    
    $output['dbg'] = $db->getError("msg");    
    $output['qry'] = $db->getquery();    
}


echo json_encode($output);

?>
