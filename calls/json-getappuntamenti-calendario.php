<?php
session_start();
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(DEBUG) ini_set("display_errors", "1");

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';
include_once '../required/classes/cc_user.class.php';

$db = new cc_dbconnect(DB_NAME);
$utente = new cc_user($_SESSION['login_id'], $_SESSION['login_type'], $_SESSION['rifid'], $db);
$t = time()-60*60*24*30;

$qry = "SELECT a.id, 
				a.datatime, a.durata, 
				a.nota, 
				CONCAT(c.cognome, ' ', c.nome) AS nome, 
				CONCAT ('Rif. ', i.rif , ' - ' , t.tipologia, ' ', l.Comune) AS immobile, 
				CONCAT ( x.cognome , ' ' , x.nome) as agente,  
				a.tipo_visita 
		FROM ".TABELLA_CLIENTI." as c, ".
				TABELLA_IMMOBILI." AS i, ".
				TABELLA_TIPOLOGIE." AS t, ".
				TABELLA_LOCALITA." AS l, ". 
				TABELLA_APPUNTAMENTI." AS a ".
				"LEFT JOIN ".TABELLA_AGENTI." AS x ON(x.id = a.agente) 
		WHERE a.cliente = c.id 
		AND a.immobile = i.id 
		AND i.localita = l.id 
		AND i.tipologia = t.id 
		AND a.datatime BETWEEN ".$_GET['start']." AND ".$_GET['end'];

$editable = true;
$title = "";

$pianificati = $db->fetch_array($qry);

if($pianificati){
	foreach($pianificati as $p){
		$da = $p['datatime'];
		$a = $da + $p['durata'] * 60;
		//$title = "(".$p['id'].")";
		$title = $p['nome']."\n".$p['immobile'];

		$color = $_colori_calendario[$p['tipo_visita']];
		$agente = ($p['agente']) ? "Agente: ".$p['agente'] : "";
		
		$tmp = array(	
				'id' => $p['id'],
				'start' => $da, 
				'end' => $a,
				'title' => $title,
				'hoverText' => $agente,
				
				'color' => $color,
				'allDay' => false,
				'editable' => $editable,
				'className' => "appuntamento",
				'description' => $p['nota']
			);
			$jsonarray[] = $tmp;
	
		
	}
}


echo json_encode($jsonarray);


?>
