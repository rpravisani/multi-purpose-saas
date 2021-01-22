<?php
/*
Loop vecchia tabella varianti_gruppi in cui c'è nella colonna varianti l'array serializzata con le varianti
deserializzo valore varianti, loop tale array ed inserisco in data_varianti_x_gruppi
*/
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);
$fields = array('gruppo', 'variante', 'insertedby');

$gruppi = $db->select_all('data_varianti_gruppi');

foreach($gruppi as $gruppo){
	$array = unserialize($gruppo['varianti']);
	
	foreach($array as $variante){
		
		$valori = array($gruppo['id'], $variante, '1');
		
		$db->insert('data_varianti_x_gruppi', $valori, $fields);
		
	}
}

?>
