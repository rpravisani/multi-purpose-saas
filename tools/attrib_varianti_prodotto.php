<?php
/* Attribuisco ad ogni articolo tutte le varianti del gruppo con prezzo standard di vendita come prezzo variante */
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$varianti_gruppi = $db->col_value("id", DBTABLE_VARIAZIONI_GRUPPI);

foreach($varianti_gruppi as $vg){
	$variante_gruppo[$vg] = $db->col_value("variante", DBTABLE_VARIAZIONI_X_GRUPPI, "WHERE gruppo = '".$vg."'");
}

$prodotti = $db->select_all(DBTABLE_PRODOTTI);

if($prodotti){
	$c = 0;
	$fields = array("prodotto", "variante", "prezzo", "insertedby");
	foreach($prodotti as $prodotto){
		foreach($variante_gruppo[$prodotto['varianti']] as $variante){
			$values = array($prodotto['id'], $variante, $prodotto['prezzo_vendita'], 1);
			$db->insert(DBTABLE_PRODOTTI_VARIANTI , $values, $fields);
			$c++; 
		}
	}
}
echo "Inserito ".$c." varianti";


?>
