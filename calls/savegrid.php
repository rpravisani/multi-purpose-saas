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
$output['qry'] = "";
$error = "";
$d = array();

// controllo che sia impostato un modulo
if(empty($_POST['modulo'])){
	$output['error'] = "nomod";
	$output['msg'] = "Nessun modulo impostato!";
	echo json_encode($output);
	die();
}

// controllo che sia impostato un modulo
if(empty($_POST['action'])){
	$output['error'] = "noaction";
	$output['msg'] = "Nessuna azione impostata!";
	echo json_encode($output);
	die();
}

// controllo che sia impostato un modulo
if(empty($_POST['cid'])){
	$output['error'] = "nocid";
	$output['msg'] = "Nessuna cella impostata!";
	echo json_encode($output);
	die();
}

// controllo che sia impostato un modulo
if(empty($_POST['formdata'])){
	$output['error'] = "nodata";
	$output['msg'] = "Nessun valore impostata!";
	echo json_encode($output);
	die();
}

$db = new cc_dbconnect(DB_NAME);
$sv = $db->make_data_safe($_POST['formdata']);

// ricupero nome file da modulo
$nome_file = $db->get1value("nome_file", TABELLA_MODULI, "WHERE id='".$_POST['modulo']."'");

if(!$nome_file){
	$output['error'] = "moduloinesistente";
	$output['msg'] = "Il modulo <strong>".$_POST['modulo']."</strong> non esiste!";
	echo json_encode($output);
	die();
}
$nome_file .= ".php";

if(file_exists("../grid/".$nome_file)){
	include_once "../grid/".$nome_file;
}else{
	$output['error'] = "noswitch";
	$output['msg'] = "Nessun switch modulo trovato!";
	echo json_encode($output);
	die();
}

/*** fine controlli ***/
foreach($sv as $k=>$v){
	$key = substr($k, 2);
	$safevalues[$key] = $v;
}
$data = formatTd($safevalues);


if($_POST['action'] == "update"){
	if($db->update($cell_tab, $safevalues, "WHERE id='".$_POST['id']."'")){
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "";
		$output['qry'] = $db->getquery();
		$output['insertid'] = $_POST['id'];
		$output['data'] = $data;
	}else{
		$output['result'] = false;
		$output['error'] = "update";
		$output['msg'] = "Errore durante aggiornamento record.";
		$output['qry'] = "";
		$output['insertid'] = "";
	}
}else if($_POST['action'] == "new"){
	$_colonne = array_keys($campi);
	preg_match_all('/^r(\d+)c(\d+)$/', $_POST['cid'], $preg_matches);
	$_colonne[] = $row_rif;
	$_colonne[] = $col_rif;
	$safevalues[]  =  $preg_matches[1][0];
	$safevalues[]  =  $preg_matches[2][0];
	
	if($db->insert($cell_tab, $safevalues, $_colonne)){
		$output['result'] = true;
		$output['error'] = "";
		$output['msg'] = "";
		$output['qry'] = "";
		$output['insertid'] = $db->get_insert_id();			
		$output['data'] = $data;
	}else{
		$output['result'] = false;
		$output['error'] = "insert";
		$output['msg'] = "Errore durante inserimento record.\n".$db->getError("msg");
		$output['qry'] = $db->getquery();
		$output['insertid'] = "";
	}
}else{
		$output['result'] = false;
		$output['error'] = "action";
		$output['msg'] = "Azione non contemplata.";
		$output['qry'] = "";
		$output['insertid'] = "";
}

echo json_encode($output);
	


?>