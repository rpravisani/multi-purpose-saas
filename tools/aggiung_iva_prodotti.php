<?php
session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);
// leggo file csv e lo importo in array

$c = 0;

if (($handle = fopen('prodotti_con_iva.csv', 'r')) !== FALSE) { // Check the resource is valid
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) { // Check opening the file is OK!
        
        if($c > 0) $csv[$data[0]] = $data[3];
		$c++;
    }
    fclose($handle);
}




// estrapolo prodotti da db e per ogni codice articolo cerco iva in array
$prodotti = $db->col_value("codice", "data_prodotti");

if($prodotti){
	foreach($prodotti as $prodotto){
		$iva = @$csv[$prodotto];
		if(!empty($iva) ){
			if($iva != '4'){
				$db->update("data_prodotti", array("iva" => $iva), "WHERE codice = '".$prodotto."'");
				echo "Aggiornato l'iva al $iva% per prodotto con codice $prodotto<br>";
			}else{ 
				echo "Iva $prodotto già a 4%<br>";
			}
		}else{
			echo "<span style='color: red'>Prodotto $prodotto non trovato</span><br>";
		}
	}
}



?>