<?php
/* Rimappo i prezzi corretti a tutti gli articoli / varianti in dettagli ordine */

session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$ordine = 12; // id tab ordini

$id_listino = 1; // il listino


$qry = "
	SELECT od.id, pv.prodotto, pv.variante, pv.prezzo 
	FROM data_prodotti_varianti AS pv 
	JOIN data_ordini_dettagli AS od ON (pv.variante = od.variante AND pv.prodotto = od.articolo) 
	WHERE od.ordine = '".$ordine."' and pv.listino = '".$id_listino."'
";

$prezzi = $db->fetch_array($qry);

$update = 0;

if($prezzi){
	
	$output = "";
	
	foreach($prezzi as $row){
		
		if($db->update("data_ordini_dettagli", array("prezzo" => $row['prezzo']), "WHERE id = '".$row['id']."'")){
			$update++;
			$output .= $row['id']." (Prod. ".$row['prodotto']." Var. ".$row['variante'].") => € ".$row['prezzo']."<br>\n";
		}
		
		
	}
	
}

echo "aggiornati ".$update." prezzi:<br>".$output;



?>
