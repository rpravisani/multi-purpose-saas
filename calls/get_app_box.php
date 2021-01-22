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


// connect db
$db = new cc_dbconnect(DB_NAME);
$imm = $cli = $agente = $tipo = $nota = $durata = "";
$h2 = "Nuovo appuntamento";

if(!empty($_POST['id'])){
	$h2 = "Modifica appuntamento";
	$app = $db->get1row(TABELLA_APPUNTAMENTI, "WHERE id = '".$_POST['id']."'");
	if($app){
		$imm = $app['immobile'];
		$cli = $app['cliente'];
		$agente = $app['agente'];
		$tipo = $app['tipo_visita'];
		$nota = $app['nota'];
		$durata = $app['durata'];
	}
}

$_s = strtotime($_POST['start']);
$_e = strtotime($_POST['end']);
$_time = date("d/m/Y", $_s)." alle ore ".date("H:i", $_s);
$h2 .= " ".$_time;

// elenco clienti
$qry_imm = "SELECT 
				i.id, 
				CONCAT ('Rif. ', i.rif , ' - ' , t.tipologia, ' ', l.Comune) AS immobile 
		FROM ".TABELLA_IMMOBILI." AS i, 
			 ".TABELLA_TIPOLOGIE." AS t, 
			 ".TABELLA_LOCALITA." AS l 
		WHERE i.tipologia = t.id 
		AND i.localita = l.id 
		AND i.attivo = '1' 
		ORDER BY immobile";
$immobili = getSelectOptionsAdv($qry_imm, "id", "immobile", $imm);
$clienti = getSelectOptionsAdv("SELECT id, CONCAT (cognome , ' ' , nome) AS nome FROM ".TABELLA_CLIENTI." WHERE attivo = '1' ORDER BY nome", "id", "nome", $cli);
$agenti = getSelectOptionsAdv("SELECT id, CONCAT (cognome , ' ' , nome) AS nome FROM ".TABELLA_AGENTI." WHERE attivo = '1' ORDER BY nome", "id", "nome", $agente);
$seltipo1 = ($tipo == '1') ? "selected" : "";
$seltipo2 = ($tipo == '2') ? "selected" : "";
$seltipo3 = ($tipo == '3') ? "selected" : "";

$output['html'] = "<h2>".$h2."</h2>";
$output['html'] .= "<input type='hidden' name='durata' id='durata' value='".$durata."' />";
$output['html'] .= "<div class='sx fifty'>";
$output['html'] .= "<div>";
$output['html'] .= "<label for='tipoapp'>Tipo di appuntamento</label><br>";
$output['html'] .= "<select name='tipoapp' id='tipoapp' class='appselect' data-placeholder='Seleziona tipologia...'>";
$output['html'] .= "<option ".$seltipo1." value='1'>Sopralluogo</option>";
$output['html'] .= "<option ".$seltipo2." value='2'>Visita con cliente</option>";
$output['html'] .= "<option ".$seltipo3." value='3'>Appuntamento in ufficio</option>";
$output['html'] .= "</select>";
$output['html'] .= "</div>";
$output['html'] .= "<div>";
$output['html'] .= "<label for='clienteapp'>Cliente</label><br>";
$output['html'] .= "<select name='clienteapp' id='clienteapp' class='appselect' data-placeholder='Seleziona cliente...'>";
$output['html'] .= $clienti;
$output['html'] .= "</select>";

if(!empty($_POST['id'])) $output['html'] .= "<div class='gotolink'><a href='scheda.php?mod=2&id=".$cli."'>Vai alla scheda cliente</a></div>";
	
$output['html'] .= "</div>";
$output['html'] .= "<div>";
$output['html'] .= "<label for='immobileapp'>Immobile</label><br>";
$output['html'] .= "<select name='immobileapp' id='immobileapp' class='appselect' data-placeholder='Seleziona immobile...'>";
$output['html'] .= $immobili;
$output['html'] .= "</select>";

if(!empty($_POST['id'])) $output['html'] .= "<div class='gotolink'><a href='scheda.php?mod=1&id=".$imm."'>Vai alla scheda immobile</a></div>";

$output['html'] .= "</div>";
$output['html'] .= "<div>";
$output['html'] .= "<label for='agenteapp'>Agente</label><br>";
$output['html'] .= "<select name='agenteapp' id='agenteapp' class='appselect' data-placeholder='Seleziona agente...'>";
$output['html'] .= $agenti;
$output['html'] .= "</select>";
$output['html'] .= "</div>";
$output['html'] .= "</div>";
$output['html'] .= "<div class='sx fifty rightsideapp' >";
$output['html'] .= "<label for='notaapp'>Nota</label><br>";
$output['html'] .= "<textarea id='notaapp' name='notaapp' class='medium'>".$nota."</textarea>";
$output['html'] .= "</div>";
$output['html'] .= "<br class='clear'>";



$output['result'] = true;
$output['error'] = "";
$output['msg'] = "OK";


echo json_encode($output);
	
?>
