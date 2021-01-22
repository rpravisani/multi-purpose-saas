<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: dashboard.php                       ***
 *****************************************************/

$dt = new DateTime( );

$sono_cliente = (($_user->getSubscriptionType() < 2)) ? false : true;
if($sono_cliente){
    
    $codcli = $db->get1value("id", "data_clienti", "WHERE accesso = '".$_user->getUserId()."'");
    $where_progetti = " AND cliente = '".$codcli."'";
    
    $where_tasks = "WHERE report IN (".$report_clienti_flat.")";
        
}else{
    $where_progetti = $where_tasks = "";
}
/**
 * Riepilogo totali
 */

$tot_numero_clienti = $db->count_rows( "data_clienti", "WHERE active = '1'" );

$active_projects = $db->col_value("id", "data_projects", "WHERE active = '1'" . $where_progetti);
$active_projects_flat = implode(", ", $active_projects);

$active_reports = $db->col_value("id", "data_reports", "WHERE project IN (".$active_projects_flat.")");
$active_reports_flat = implode(", ", $active_reports);

$active_tasks = $db->col_value("id", "data_tasks", "WHERE report IN (".$active_reports_flat.")");
$active_tasks_flat = implode(", ", $active_tasks);

$tot_numero_progetti = count($active_projects);
$tot_numero_report = count($active_reports);
$tot_numero_task = count($active_tasks);

/**
 * Numero di reports suddiviso per stato. 
 * Prendo solo i report con progetti attivi e se utente = cliente filtrati per cliente
 * I dati verrano usati per creare grafico a ciambella
 */

$qry_num_reports = "SELECT state AS label, COUNT(id) AS value
FROM `data_reports`
WHERE project IN (".$active_projects_flat.")
GROUP BY state";

$num_reports = $db->fetch_array($qry_num_reports);
$reports_qty = array();
$reports_qty['In Corso'] = 0;
$reports_qty['Completato'] = 0;
$reports_qty['Da Fare'] = 0;
$reports_qty['Sospeso'] = 0;

if($num_reports){
    foreach($num_reports as $i => $row){
        
        $reports_qty[$row['label']] = $row['value'];
       
    }
    $report_type_labels_flat  = implode('", "',array_keys($reports_qty));
    $report_type_values_flat  = implode(', ', $reports_qty);
    $report_type_colors_flat  = '"#f39c12", "#00a65a", "#dd4b39", "#cccccc" ';
}

$num_reports_json = json_encode($num_reports);

/**
 * Task per tipologia
 * I dati verrano usati per creare grafico o barre
 */
$qry_task_types = "SELECT x.name AS label, COUNT(t.id) AS value, x.color 
FROM `data_task_types` AS x 
LEFT JOIN data_tasks AS t ON (t.type = x.id AND t.report IN (".$active_reports_flat.")) 
GROUP BY x.id 
ORDER by x.id";



$task_types = $db->fetch_array($qry_task_types);

$task_labels = $task_numbers = array();

if($task_types){
    
    foreach($task_types as $task_type){
        $task_labels[]  = $task_type['label'];
        $task_numbers[] = $task_type['value'];
        $task_colors[] = $task_type['color'];
    }
    
    $task_labels_flat  = implode('", "', $task_labels);
    $task_numbers_flat = implode(', ', $task_numbers);
    $task_colors_flat  = implode('", "', $task_colors);
    
}


// incorporo javascript per creazione grafici
$js_assets[] = "plugins/chartjs/Chart.min.js";
?>