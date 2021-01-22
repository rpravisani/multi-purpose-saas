<?php
/*
Loop vecchia tabella varianti_gruppi in cui c'è nella colonna varianti l'array serializzata con le varianti
deserializzo valore varianti, loop tale array ed inserisco in data_varianti_x_gruppi
*/
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);


// leggo indirizzi da tabella segnaposti
$segnaposti = $db->select_all("segnaposti", "WHERE trovato = '0' LIMIT 10");
$num_segnaposti = count($segnaposti);
$trovati = 0;
$tab_non_trovati = "";

if($segnaposti){
	foreach($segnaposti as $segnaposto){
		
		$indirizzo = $segnaposto['indirizzo']." ".$segnaposto['cap']." ".$segnaposto['localita']." ".$segnaposto['prov'];
		$indirizzo = strtolower($indirizzo);
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
				
				$db->insert("geocodes", $values, $fields, $updates);
				
				$db->update("segnaposti", array("trovato" => "1", "WHERE id = '".$segnaposto['id']."'"));
				
				$trovati++;
			}else{
				
				$tab_non_trovati .= "<tr><td>".$segnaposto['id']."</td><td>".$indirizzo."<br><small>(".$indirizzo_enc.")</small></td></tr>\n";
				
			} // end if status not ok
			
		} // end if json
		
	} // end foreach
	
} // end if segnaposti

echo "Rilevati ".$num_segnaposti." segnaposti. Trovati ".$trovati." coordinate.";
if(!empty($tab_non_trovati)){
	
	echo "<table width='100%' border='1' cellspacing='0' cellpadding='5'>";
	echo "<thead><tr><td>ID</td><td>Indirizzo</td></tr></thead>\n";
	echo "<tbody>\n";
	echo $tab_non_trovati;
	echo "</tbody>\n";
	echo "</table>\n";
	
}

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
