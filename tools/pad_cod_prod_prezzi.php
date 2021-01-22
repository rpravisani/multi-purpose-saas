<?php
session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);
$updated = 0;

$pg = $db->select_all("data_prezzi_giorno");

foreach($pg as $row){
	
	$prodotto = str_pad($row['prodotto'], 6, "0", STR_PAD_LEFT); 
	
	if($db->update("data_prezzi_giorno", array("prodotto" => $prodotto), "WHERE id = '".$row['id']."'")){
		$updated++;
		echo "Updated record ".$row['id']." - product code <strong>".$row['prodotto']."</strong> to <strong>".$prodotto."</strong><br>\n";
	}else{
		echo "Error during update";
		die();
	}
	
}
echo "updated ".$updated." recors total";

?>