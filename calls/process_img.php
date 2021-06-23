<?php
include_once '_head.php';

// uso PDO perché ha una sanificazione più evoluta per foto
$mySqlPDO = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PWD);
$mySqlPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Sanifico i dati post
$data = json_decode($_POST['data'], true);

$table = $field_name = $recid_field = $where = "";

// Sanifico
$pid    = (int) $db->make_data_safe($data['pid']); 
$record = (int) $db->make_data_safe($data['record']); 

if( empty($_FILES) ){
    $output['error'] = "no-file";
    $output['msg'] = "Nessun file passato";
    echo json_encode($output);
    die();
}

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

$path = FILEROOT."calls/image-processor/".$switch_file.".php";

if(!file_exists($path)){
    $output['error'] = "no-file";
    $output['msg'] = "Il file switch $path non esiste!";
    echo json_encode($output);
    die();    
}

// switch file for field
$img = file_get_contents($_FILES["img"]["tmp_name"]); 
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
$update = array( $field_name => $img );

// se in switchfile non ho definto una clausola where uso definizione standard che assume che $resod si riferisca al campo 'id' di $table
if(empty($where)) $where = "WHERE id = '".$record."'";


$query = "UPDATE ".$table." SET";
foreach(array_keys($update) as $field){
    $query .= " ".$field."=:".$field.",";
}
$query = substr($query, 0, -1);

// concludo quesry con clausola di restrizone sul singolo record di nostro interesse
$query .= " ".$where;    
    
// preparazione della query
$stmt = $mySqlPDO->prepare($query);
    
// sanificazione ed esecuzione della query
if( !$stmt->execute($update) ){
    $output['query'] = $db->getquery();
    $output['error'] = "update-error";
    $output['msg']   = "Impossibile aggiornare immagine";
    echo json_encode($output);
    die();
}

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['return'] = $return_message; // messaggio di ritorno che può essere passato a funzione callBack in js

echo json_encode($output);




?>