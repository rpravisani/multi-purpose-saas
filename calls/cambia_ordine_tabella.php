<?php
/**

 */
include_once '_head.php';

/*
vecchio: vecchio, nuovo: nuovo, id: id, what: what
*/

// Sanifico
$old = (int) $db->make_data_safe($_POST['vecchio']); // eventuale record id della domanda (solo se update)
$new = (int) $db->make_data_safe($_POST['nuovo']); 
$id  = (int) $db->make_data_safe($_POST['id']); 
$switch = $db->make_data_safe($_POST['what']); 

// defaults
$progetto = 0;
$row = false;
$campo_progetto = "id_progetto"; // Nome acmpo che memorizza l'id progetto

if(empty($id)){
    $output['error'] = "no-id";
    $output['msg']   = "Nessun id settato";
    echo json_encode($output);
    die();
}

switch($switch){
    case "programma":
    case "program":
        $table = "data_programma_items";
        $field = "id_programma"; // campo che ragruppa l'insieme di data su cui stabilire l'ordine    
        break;
    case "faculty":
        $table = "data_faculty";
        $field = "id_progetto"; // campo che ragruppa l'insieme di data su cui stabilire l'ordine        
        break;
    case "domanda":
        $table = "data_quiz_domande";
        $field = "id_quiz"; // campo che ragruppa l'insieme di data su cui stabilire l'ordine        
        break;
    case "risposta":
        $table = "data_quiz_risposte";
        $field = "id_domanda"; // campo che ragruppa l'insieme di data su cui stabilire l'ordine        
        break;
    case "sponsor":
        $table = "data_sponsor";
        $field = ""; // campo che ragruppa l'insieme di data su cui stabilire l'ordine 
        $row = $db->get1row($table, "WHERE id = '".$id."'");
        $where = "WHERE tipologia = '".$row['tipologia']."' AND id_progetto = '".$row['id_progetto']."'";
        break;
    case "risorsa":
        $table = "data_risorse_online";
        $field = ""; // campo che ragruppa l'insieme di data su cui stabilire l'ordine 
        $row = $db->get1row($table, "WHERE id = '".$id."'");
        $where = "WHERE categoria = '".$row['categoria']."' AND id_progetto = '".$row['id_progetto']."' AND tipo = '".$row['tipo']."'";
        break;
    default:
        $table = false;
        break;
}

if(empty($table)){
    $output['error'] = "no-section";
    $output['msg']   = "Nessuna sezione settata";
    echo json_encode($output);
    die();
}

// recupero la riga del record spostato
if(!$row) $row = $db->get1row($table, "WHERE id = '".$id."'");
$whereid = $row[$field];

// se nello switch non è stato valorizzata la variabile where genero valore default tramite $field e $whereid
if(empty($where)) $where = "WHERE ".$field." = '".$whereid."'";

// se nello switch non è stato definito l'id progetto lo recupero da riga record estrapolato
if(empty($progetto)) $progetto = $row[$campo_progetto];

// recupero valore massimo ordine
$max_ordine = $db->get_max_row("ordine", $table, $where);

// controllo che il nuovo ordine sia nei limiti
if($new > $max_ordine) $new = $max_ordine;
if($new < 1) $new = 1;

if($old < $new){
    //$output['dbg2'] = 'diminuisco';
    // Il nuovo ordine è maggiore di quello vecchio: diminuisco il valore di ordine dei record che hanno un ordine tra il vecchio e il nuovo ordine 
    $qry = "UPDATE ".$table." SET ordine = ordine - 1 ".$where." AND ordine BETWEEN ".$old." AND ".$new;
}else{
    //$output['dbg2'] = 'incremento';
    // Il nuovo ordine è minore di quello vecchio: aumento il valore di ordine dei record che hanno un ordine tra il nuovo e il vecchio ordine 
    $qry = "UPDATE ".$table." SET ordine = ordine + 1 ".$where." AND ordine BETWEEN ".$new." AND ".$old;
}
//$output['qry'] = $qry;
// cambio ordine dei record tra old e new
if(!$db->execute_query($qry) ){
    $output['error'] = "order-update";
    $output['msg']   = "Errore durante aggiornamento nuovo ordine altri elementi";
    echo json_encode($output);
    die();
}

// aggiorno ordine del record spostato
if(!$db->update($table, array("ordine" => $new), "WHERE id = '".$id."'")){
    $output['error'] = "item-update";
    $output['msg']   = "Errore durante aggiornamento nuovo ordine ";
    echo json_encode($output);
    die();
}

// Registro cambio avvenuto in tabella data_updates passando id progetto e sezione aggiornata. 3° param sottointeso "aggiornamento"
$output['dbg'] = $helper->logUpdate($progetto, $switch);


$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";


echo json_encode($output);

?>