<?php

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$output = array();
$output['result'] = false;
$output['error'] = "No post"; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['proceed'] = true; // if true the calling script will proceed otherwise it will stop
$output['errorlevel'] = "danger"; // color of modal box
$output['qry'] = "";
$output['dbg'] = "";


if(empty($_POST['id'])){
	$output['error'] = "No id!";
	$output['proceed'] = false;
	echo json_encode($output);
	die();
}


$start = microtime(true);

$id = (int) $_POST['id'];

$segnaposto = $db->get1row("segnaposti", "WHERE id = '".$id."'");
$indirizzo = $segnaposto['indirizzo']." ".$segnaposto['cap']." ".$segnaposto['localita']." ".$segnaposto['prov'];
$indirizzo = mb_strtolower($indirizzo);
$indirizzo_enc = preg_replace('/[^a-z0-9 ]/', '', $indirizzo);
$indirizzo_enc = preg_replace('/\s/', '+', $indirizzo_enc);

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
		if(!$db->insert("geocodes", $values, $fields, $updates) ){
			$output['dbg1'] = $db->getQuery();
		}else{
			$output['dbg1'] =  $db->getQuery();
		}
		
		// update segnaposti - imposto trovato a 1
		if(!$db->update("segnaposti", array("trovato" => "1"), "WHERE id = '".$id."'") ){
			$output['dbg2'] = $db->getQuery();;
		}else{
			$output['dbg2'] = "updated!";
		}
		
		$output['lng'] = $coords->lng;
		$output['lat'] = $coords->lat;
		$output['error'] = "";
		$output['result'] = true;

	}else{
		$output['error'] = "Indirizzo ".$indirizzo." non trovato!";
		$output['lng'] = "-";
		$output['lat'] = "-";

	} // end if status not ok

}else{
	$output['error'] = "Nessuna risposta da google per l'indirizzo ".$indirizzo."!";
	$output['lng'] = "-";
	$output['lat'] = "-";
	
} // end if json

$end = microtime(true);
$diff = $end-$start;

$output['elapsed_time'] = round($diff, 5);

echo json_encode($output);
exit();

function fetchURL($url) {
	if (!function_exists('curl_init')){ 
		return file_get_contents($url);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}


?>
