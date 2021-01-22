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

$db = new cc_dbconnect(DB_NAME);

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

if($_POST['action'] == "update"){
	$d = $db->get1row($cell_tab, "WHERE id = '".$_POST['id']."'");
}

preg_match_all('/^r(\d+)c(\d+)$/', $_POST['cid'], $preg_matches);
$output['riga'] =  $preg_matches[1][0];
$output['colonna'] =  $preg_matches[2][0];
$form = "<input type='hidden' name='gcol' id='gcol' value='".$output['colonna']."'>";
$form .= "<input type='hidden' name='grow' id='grow' value='".$output['riga']."'>";
$form .= "<table border='0' class='tdtab'>";
foreach($campi as $campo=>$label){
	if($_POST['action'] == "update"){
		$val = $d[$campo];
	}else{
		$val = "";
	}
	$form .= "<tr>";
	$form .= "<td><label for='".$campo."'>".$label."</label></td>\n";
	$form .= "<td><input type='text' class='medium' name='g_".$campo."' id='g_".$campo."' value='".$val."'></td>\n";
	
	$form .= "</tr>\n";
}
$form .= "</table>\n";
$output['form'] = $form;
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['qry'] = "";

echo json_encode($output);
	


?>