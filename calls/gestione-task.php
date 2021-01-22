<?php
/*****************************************************
 * Insert or edit task                           
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$report = (int) $_POST['report'];
$task   = (int) $_POST['task'];

$action = (empty($task)) ? "insert" : "update";

if($_user->getSubscriptionType() > 1){
    $output['error'] = "no-permission";
    $output['msg'] = "Non si è autorizzati ad inserire o mdificare una task";
    echo json_encode($output);
    die();
    
}
    
    
if($action == "insert"){
    
    // INSERT controllo che report esiste
    $gotReport = $db->get1row("data_reports", "WHERE id = '".$report."'");


    if(!$gotReport){    
        $output['error'] = "no-report";
        $output['msg'] = "Nessun report trovato";
        echo json_encode($output);
        die();
    }
    
    $task = 0; // per ogni evenienza
    $taskData = array();
    
    // get task number
    $taskNumber = $db->get_max_row("number", "data_tasks", "WHERE report = '".$report."'");

    if(!$taskNumber) $taskNumber = 0;

    $taskNumber++;
    
    $modal_title = "Registra nuovo task";
    
    
}else{

    // UPDATE controllo che task esiste
    $taskData = $db->get1row("data_tasks", "WHERE id = '".$task."'");

    if(!$taskData){    
        $output['error'] = "no-task";
        $output['msg'] = "Nessun retaskport trovato";
        echo json_encode($output);
        die();
    }
    
    $report = $taskData['report'];
    $taskNumber = $taskData['number'];
    
    $modal_title = "Modifica task";
}


// creo modulo html da mostrare per input

$type_options = getSelectOptions("id", "name", "data_task_types", $taskData['type'], "id", "WHERE active = '1'", true);

$html = "";

$html .= "<div class='row'>\n";
$html .= "  <div class='col-md-2'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label>N.</label>\n";
$html .= "      <input type='text' name='new_task_number' id='new_task_number' value='".$taskNumber."' readonly class='form-control'>\n";
$html .= "      <input type='hidden' name='new_task_id' id='new_task_id' value='".$task."' >\n";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "  <div class='col-md-4'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label for='new_task_type'>Tipo di task</label>\n";
$html .= "      <select name='new_task_type' id='new_task_type' class='form-control'>\n";
$html .=            $type_options;
$html .= "      </select>";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "</div>\n";

// descrizione della task
$html .= "<div class='row'>\n";
$html .= "  <div class='col-md-12'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label for='new_task_operation'>Descrizione task</label>\n";
$html .= "      <textarea name='new_task_operation' id='new_task_operation' rows='3' class='form-control'>".$taskData['operation']."</textarea>\n";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "</div>\n";

// descrizione della solzione / risultato
$html .= "<div class='row'>\n";
$html .= "  <div class='col-md-12'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label for='new_task_result'>Esito task</label>\n";
$html .= "      <textarea name='new_task_result' id='new_task_result' rows='3' class='form-control'>".$taskData['result']."</textarea>\n";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "</div>\n";

// versione vecchia e nuovo solo in caso di utilizzo
$html .= "<div class='row'>\n";
$html .= "  <div class='col-md-4'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label for='new_task_old_ver'>Vers. vecchia</label>\n";
$html .= "      <input type='text' name='new_task_old_ver' id='new_task_old_ver' value='".$taskData['old_version']."' class='form-control'>\n";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "  <div class='col-md-4'>\n";
$html .= "    <div class='form-group'>\n";
$html .= "      <label for='new_task_new_ver'>Vers. nuova</label>\n";
$html .= "      <input type='text' name='new_task_new_ver' id='new_task_new_ver' value='".$taskData['new_version']."' class='form-control'>\n";
$html .= "    </div>\n";
$html .= "  </div>\n";
$html .= "</div>\n";

// OUTPUT
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $html;
$output['title'] = $modal_title;



echo json_encode($output);

?>
