<?php
session_start();
/* Attribuisco ad ogni articolo tutte le varianti del gruppo con prezzo standard di vendita come prezzo variante */
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$id_listino = 2;
$percent = 10;

$prezzi = $db->key_value("id", "prezzo_vendita", "data_prodotti", "ORDER BY id");

$fields = array("articolo", "prezzo", "listino", "insertedby");

$cont = $ins = $upd = 0;

if($prezzi){
	foreach($prezzi as $articolo => $prezzo){
		$prezzo = $prezzo + ( ($prezzo/100)*$percent );
		
		$values = array($articolo, $prezzo, $id_listino, $_SESSION['login_id']);
		$update = array_combine($fields, $values);
		
		
		$db->insert(DBTABLE_PRODOTTI_PREZZI, $values, $fields, $update);
		$insert_id = $db->get_insert_id();
		echo "<p>Art. ".$articolo." - listino ".$id_listino." - prezzo ".$prezzo." - insert id: ".$insert_id."</p>";
		if(empty($insert_id)){ $upd++; }else{ $ins++; }
		$cont++;
	}
}

echo "<h3>looped ".$cont." records. Inserted ".$ins.", updated ".$upd."<h3>";


?>
