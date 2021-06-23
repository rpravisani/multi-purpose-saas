<?php
/**
 */
include_once '_head.php';

// campi sovrascrivibili in switch file
$table = $return_message = $where = "";

// Sanifico
$field = $db->make_data_safe($_POST['field']); // eventuale record id della domanda (solo se update)
$value = $db->make_data_safe($_POST['value']); 
//$pid   = (int) $db->make_data_safe($_POST['pid']); 
$record = (int) $db->make_data_safe($_POST['record']); 



if(empty($record)){
    $output['error'] = "no-record";
    $output['msg'] = "Numero record vuoto";
    echo json_encode($output);
    die();    
}

if(empty($pid)){
    $output['error'] = "no-page-id";
    $output['msg'] = "Nessun id pagina passato";
    echo json_encode($output);
    die();
    
}

$switch_file = $db->get1value("file_name", "pages", "WHERE id = '".$pid."'");
if(empty($switch_file)){
    $output['error'] = "wrong-pid";
    $output['msg'] = "Id pagina non valido";
    echo json_encode($output);
    die();
}

$path = FILEROOT."calls/auto-update/".$switch_file.".php";

if(!file_exists($path)){
    $output['error'] = "no-file";
    $output['msg'] = "Il file switch $path non esiste!";
    echo json_encode($output);
    die();    
}

if(strpos($field, ".") === false){
    $prefix = false;
    $field_name = $field;
}else{
    list($prefix, $field_name) = explode(".", $field);
}

include $path;


if(empty($table)){
    $output['error'] = "no-table-set";
    $output['msg'] = "Nessuna tabella valida settata!";
    echo json_encode($output);
    die();    
}

// controllo che esiste il campo
$field_list = $db->get_column_names($table);
if(!in_array($field_name, $field_list)){
    $output['error'] = "invalid-field";
    $output['msg'] = "Nome $field_name campo non valido";
    echo json_encode($output);
    die();       
}

// definisco array per l'update
$update = array( $field_name => $value );

// se in switchfile non ho definto una clausola where uso definizione standard che assume che $resod si riferisca al campo 'id' di $table
if(empty($where)) $where = "WHERE id = '".$record."'";



// aggiorno campo
if(!$db->update($table, $update, $where)){
    $output['error'] = "update-errore";
    $output['msg'] = "Errore durante aggiornamento record!";
    echo json_encode($output);
    die();        
}

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['return'] = $return_message; // messaggio di ritorno che può essere passato a funzione callBack in js

echo json_encode($output);

?>