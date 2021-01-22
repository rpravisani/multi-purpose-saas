<?php
session_start();
/* Attribuisco ad ogni articolo tutte le varianti del gruppo con prezzo standard di vendita come prezzo variante */
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);


$id_listino = 2;

// prezzi listino destinazione presi da tab prodotti_prezzi
$prezzi = $db->key_value("articolo", "prezzo", DBTABLE_PRODOTTI_PREZZI, "WHERE listino = '".$id_listino."' ORDER BY id");


$fields = array("prodotto", "variante", "listino", "prezzo", "insertedby");

$cont = $ins = $upd = 0;

if($prezzi){
	foreach($prezzi as $articolo => $prezzo){
		
		$varianti = $db->col_value("variante", "data_prodotti_varianti", "WHERE prodotto = '".$articolo."'");
		
		if($varianti){
			foreach($varianti as $variante){
				$values = array($articolo, $variante, $id_listino, $prezzo, $_SESSION['login_id']);
				$update = array_combine($fields, $values);
				$db->insert("data_prodotti_varianti", $values, $fields, $update);
				$insert_id = $db->get_insert_id();
				echo "<p>Art. ".$articolo." - listino ".$id_listino." - prezzo ".$prezzo." - insert id: ".$insert_id."</p>";
				$cont++;
				if(empty($insert_id)){ $upd++; }else{ $ins++; }
			}
		}
		
		
		
	
	}
}

echo "<h3>Looped ".$cont." records. Inserted ".$ins.", updated ".$upd."<h3>";


?>
