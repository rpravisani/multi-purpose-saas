<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(DEBUG) ini_set("display_errors", "1");

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

// default vars
$output = array();
$output['result'] = false;
$output['error'] = "nopost";
$output['msg'] = "Nessun dato inviato";
$output['dbg'] = "";
$error = "";

// controllo che sia impostato un modulo
if(empty($_POST['id']) ){
	echo json_encode($output);
	die();
}

// connect db
$db = new cc_dbconnect(DB_NAME);
$dd = (int) $_POST['dayDelta'];
$md = (int) $_POST['minuteDelta'];

$ts = $db->get1value("durata", TABELLA_APPUNTAMENTI, "WHERE id = '".$_POST['id']."'");
if(!$ts) {
	$output['result'] = false;
	$output['error'] = "no-data";
	$output['msg'] = "Nessun data prevista trovata!";
	$output['dbg'] = $db->getquery();;
	echo json_encode($output);
	die();
}

$gg = $dd *  60 * 24;
$min = $md ;
$ts += $gg + $min;

$valori = array("durata" => $ts);

if($db->update(TABELLA_APPUNTAMENTI, $valori, "WHERE id = '".$_POST['id']."'")){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "OK";
	$output['dbg'] = date("d-m-Y H:i:s", $ts)." (".$ts.")";
}else{
	$output['result'] = false;
	$output['error'] = "update";
	$output['msg'] = "Errore durante aggiornamento data-ora!";
	$output['dbg'] = $db->getError("msg")."\n".$db->getquery();
	
}


echo json_encode($output);
	
?>
