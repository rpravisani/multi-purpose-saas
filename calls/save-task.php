<?php
/*****************************************************
 * save a task either insert or update based on $_POST['action'] value
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$report = (int) $_POST['report'];
$task = $db->make_data_safe( $_POST['task'] ); // array

$updateState = "";

if(!is_array($task)){
    $output['error'] = "not-an-array";
    $output['msg'] = "Errore dati inviati";
    echo json_encode($output);
    die();
}

// recupero id task (se action è insert sarà zero / vuota)
$taskId = $task['id'];
unset($task['id']);

$action = (string) $_POST['action']; // action or update

if($action == "insert"){
    
    // INSERT controllo che report esiste
    $gotReport = $db->get1row("data_reports", "WHERE id = '".$report."'");

    if(!$gotReport){    
        $output['error'] = "no-report";
        $output['msg'] = "Nessun report trovato";
        echo json_encode($output);
        die();
    } 
    
    if($gotReport['state'] == 'Da Fare'){
        $db->update("data_reports", array("state" => "In Corso"), "WHERE id = '".$report."'");
        $updateState = "In Corso";
    }
    
    
}else{

    // UPDATE controllo che task esiste
    $taskData = $db->get1row("data_tasks", "WHERE id = '".$taskId."'");

    if(!$taskData){    
        $output['error'] = "no-task";
        $output['msg'] = "Nessun retaskport trovato";
        echo json_encode($output);
        die();
    }
    
}


// aggiungo report a task
$task['report'] = $report;


if($action == "insert"){    

    // INSERT IN DB
    $fields = array_keys($task);

    if( !$db->insert("data_tasks", $task, $fields) ){
        $output['error'] = "insert-error";
        $output['msg'] = "Errore durante registrazione task";
        $output['dbg'] = $db->getError("msg");
        $output['qry'] = $db->getQuery();
        echo json_encode($output);
        die();    
    }

    $taskId = $db->get_insert_id();
}else{
    
    // UPDATE
    if( !$db->update("data_tasks", $task, "WHERE id = '".$taskId."'") ){
        $output['error'] = "update-error";
        $output['msg'] = "Errore durante aggiornamento task";
        $output['dbg'] = $db->getError("msg");
        $output['qry'] = $db->getQuery();
        echo json_encode($output);
        die();    
    }    
    
}
$task_type = $db->get1row("data_task_types", "WHERE id = '".$task['type']."'");

// genero html tr

$html = "";

if($action == "insert") $html .= "<tr id='task".$task."'>\n";
$html .= "  <td class=\"see text-center\" width=\"10\"><i data-record='".$taskId."' class=\"fa fa-search\"></i></td>";
$html .= "  <td class=\"text-right\" style=\"width: 7%\">".$task['number']."</td>";
$html .= "  <td class=\"text-center\" style=\"width: 10%\"><span class=\"label\" style=\"background-color: ".$task_type['color']."\"><i class=\"".$task_type['icon']." mr-2\"></i>".$task_type['name']."</span></td>";
$html .= "  <td>".$task['operation']."</td>\n";
$html .= "  <td>".$task['result']."</td>\n";
$html .= "  <td class='text-center' style=\"width: 7%\">".$task['old_version']."</td>\n";
$html .= "  <td class='text-center' style=\"width: 7%\">".$task['new_version']."</td>\n";
$html .= "  <td class='text-center' width='10'><i data-task=\"".$taskId."\" class=\"puls delete fa fa-fw fa-trash\"></i></td>\n";
if($action == "insert") $html .= "</tr>\n";



// OUTPUT
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['reportState'] = $updateState;
$output['tr'] = $html;


echo json_encode($output);

?>
