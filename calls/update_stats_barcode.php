<?php
/*****************************************************
 * update_stats_barcode                              *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$value  = (int) $_POST['value'];
$filter = (string) $_POST['filter'];

if(empty($value)){

	$senzavar = $db->count_rows( DBTABLE_PRODOTTI, "WHERE active = '1' AND varianti = '0'" );
	$convar = $db->count_rows( DBTABLE_PRODOTTI, "WHERE active = '1' AND varianti != '0'" );

	$nvariants = $db->count_rows( DBTABLE_PRODOTTI_VARIANTI, "WHERE active = '1' AND listino = '1'" );
	$output['senzavar'] = $senzavar;
	$output['convar'] = $convar;
	$output['nvariants'] = $nvariants;

	$nprod = $senzavar + $convar;

	$nbcode = $nvariants + $senzavar;

	$npag = ($senzavar/12) + $convar/2;

}else{	

	if($filter == "sku"){

		$nvariants = $db->count_rows( DBTABLE_PRODOTTI_VARIANTI, "WHERE prodotto = '".$value."' AND active = '1' AND listino = '1'" );
		$nbcode = (empty($nvariants)) ? 1 : $nvariants;
		$npag = $nprod = 1;




	}else if($filter == "cat"){

		if($value == '4'){

			$nprod = $nbcode = $db->count_rows(DBTABLE_PRODOTTI, "WHERE categoria = '4'");
			$npag = ceil($nprod/12);

		}else{

			$clause = " AND prodotto IN (SELECT id FROM data_prodotti WHERE categoria = '".$value."')";

			$qry = "
				SELECT prodotto, COUNT(id) as tot FROM `data_prodotti_varianti` 
				WHERE listino = '1' ".$clause."
				GROUP BY prodotto	
			";


			$prodotti = $db->fetch_array($qry);
			$tots = array_column($prodotti, "tot");


			$nprod = count($tots);
			$nbcode = array_sum($tots);
			$factor = $nbcode/$nprod;
			$npag = ($factor <= 12 ) ? $nprod/2 : $nprod;

		}

	}
	
}

$output['result'] = true;
$output['error'] = ""; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['errorlevel'] = ""; // color of modal box

$output['nprod'] = number_format($nprod, 0, ",", "."); // color of modal box
$output['nbcode'] = number_format($nbcode, 0, ",", "."); // color of modal box
$output['npag'] = number_format($npag, 0, ",", "."); // color of modal box


echo json_encode($output);




?>
