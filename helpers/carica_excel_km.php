<?php
defined('_CCCMS') or die;
/********************************************************
 *** HELPER                                           ***
 *** filename: carica_excel_km.php                    ***
 *** Qua dentro ci sono sia le funzioni da eseguire   ***
 *** al caricamento (p.e. esempio per fare controlli  *** 
 *** o pulizie) sia le funzioni ausiliariere e        ***
 *** che non sono strettamente legate all'operazioni  ***
 *** che deve effettuare lo script principale.        *** 
 ********************************************************/

// funzione da eseguire all'avvio - viene richiamato da required.php
function on_ready($param = array()){
}


// funzione da eseguire quando azione è upload - viene richiamato da required.php
function on_ready_upload($param = array()){
	// Estrapolo nome e data ultimo upload (TODO parametro cliente)
	global $pid, $db, $page_alerts, $bootstrap;
	$cliente = '1';
	$last_upload = $db->get1row(DBTABLE_UPLOADS, "WHERE cliente = '".$cliente."' AND pid = '".$pid."' ORDER BY id DESC LIMIT 1");
	if($last_upload){
		$d = explode(" ", $last_upload['ts']);
		$data = cc_date_us2eu($d[0]);
		$contenuto = "Nome file: <strong>".$last_upload['filename_ori']."</strong> il <strong>".$data."</strong>";
		$page_alerts[] = $bootstrap->alert( "ULTIMO CARICAMENTO", $contenuto, "info", true);
	}
	
}
?>