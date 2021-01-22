<?php
$joomla = false;
include_once 'cc_mysqli.class.php';
if($joomla){
	
	include_once '../../../configuration.php';
	

	$variables = new JConfig();

	$db = new cc_dbconnect($variables->db, $variables->host, $variables->user, $variables->password);

	$table_prefix = $variables->dbprefix;
	
}else{
	
	include_once '../../required/variables.php';
	

	$db = new cc_dbconnect(DB_NAME);

	$table_prefix = "qqdrv_";
	
}

$tab_segnaposti = $table_prefix."ecmaps_segnaposti_new";
$tab_geocodes   = $table_prefix."ecmaps_geocodes_new";


$output = array();
$output['result'] = false;
$output['error'] = "No post"; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['proceed'] = true; // if true the calling script will proceed otherwise it will stop
$output['errorlevel'] = "danger"; // color of modal box
$output['qry'] = "";
$output['dbg'] = "";

$curlError = "x";


if(empty($_POST['id'])){
	$output['error'] = "No id!";
	$output['proceed'] = false;
	echo json_encode($output);
	die();
}
$nazione = empty($_POST['nazione']) ? false : true;

$start = microtime(true);

$id = (int) $_POST['id'];

$segnaposto = $db->get1row($tab_segnaposti, "WHERE id = '".$id."'");
$indirizzo = $segnaposto['indirizzo']." ".$segnaposto['cap']." ".$segnaposto['localita']." ".$segnaposto['prov'];

// se flag nazione è vero aggiungo la nazione alla stinga di ricerca indirizzo
$indirizzo_enc = ($nazione) ? $indirizzo." ".$segnaposto['nazione'] : $indirizzo;
$indirizzo_enc = mb_strtolower($indirizzo_enc);
$indirizzo_enc = trim($indirizzo_enc);

// indirizzo codificato per ricerca tramite api
$indirizzo_enc = preg_replace('/[^a-z0-9 ]/', '', $indirizzo_enc);
$indirizzo_enc = preg_replace('/\s/', '+', $indirizzo_enc);

// aggungo sempre e comunque la nazione alla stringa adress che vine epoi utilizzato come chiave di ricerca
$indirizzo .= " ".$segnaposto['nazione'];
$indirizzo = mb_strtolower($indirizzo);
$indirizzo = trim($indirizzo);

// per non sprecare richieste giornaliere controllo se l'indirizzo è già presente in tab geocodes
$check = $db->get1row($tab_geocodes, "WHERE adress = \"".$indirizzo."\"");
if($check){
	
	// update segnaposti - imposto trovato a 1
	if(!$db->update($tab_segnaposti, array("trovato" => "1"), "WHERE id = '".$id."'") ){
		$output['dbg2'] = $db->getQuery();;
	}else{
		$output['dbg2'] = "updated!";
	}
	
	$end = microtime(true);
	$diff = $end-$start;

	$output['elapsed_time'] = round($diff, 5);
	$output['lng'] = $check['lon'];
	$output['lat'] = $check['lat'];
	$output['error'] = "doppione";
	$output['result'] = true;
	echo json_encode($output);
	die();
}


$url = sprintf('http:/maps.google.com/maps/api/geocode/json?sensor=false&address=%s', $indirizzo_enc);
$json = fetchURL($url);
		
if($json){
	$result = json_decode($json);
	if($result->status == "OK"){
		$coords = $result->results[0]->geometry->location;

		$fields = array("adress", "lon", "lat");
		$values = array($indirizzo, $coords->lng, $coords->lat);
		$updates = array("lon" => $coords->lng, "lat" => $coords->lat);
		
		// insert / update geocodes
		if(!$db->insert($tab_geocodes, $values, $fields, $updates) ){
			$output['dbg1'] = $db->getQuery();
		}else{
			$output['dbg1'] =  "inserted";
		}
		
		// update segnaposti - imposto trovato a 1
		if(!$db->update($tab_segnaposti, array("trovato" => "1"), "WHERE id = '".$id."'") ){
			$output['dbg2'] = $db->getQuery();;
		}else{
			$output['dbg2'] = "updated!";
		}
		
		$output['lng'] = $coords->lng;
		$output['lat'] = $coords->lat;
		$output['error'] = "";
		$output['result'] = true;

	}else{
		$output['error'] = "Indirizzo ".$indirizzo_enc." non trovato!";
		$output['lng'] = "-";
		$output['lat'] = "-";

	} // end if status not ok

}else{
	//$output['error'] = "Nessuna risposta da google per l'indirizzo ".$indirizzo."!";
	$output['error'] = $curlError;
	
	$output['lng'] = "-";
	$output['lat'] = "-";
	
} // end if json

$end = microtime(true);
$diff = $end-$start;

$output['elapsed_time'] = round($diff, 5);

echo json_encode($output);
exit();

function fetchURL($url) {
	global $curlError;
	if (!function_exists('curl_init')){ 
		return file_get_contents($url);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);
	$output = curl_exec($ch);
	curl_close($ch);
	if(!$output) $curlError = "curl_error: ".curl_error($ch);
	if(!$output) $output = file_get_contents($url);
	if(!$output) $curlError .= "\nfile_get_contents fails also";
	return $output;
}


?>
