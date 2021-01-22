<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$ordine = 14; // id tabella ordini
$sep = ";";

$csv = "";
$totale_ordine = 0;

$qry = "
SELECT d.articolo, p.sku, p.varianti, d.qta, d.variante
FROM data_ordini_dettagli AS d 
JOIN data_prodotti AS p ON (p.id = d.articolo)
WHERE d.ordine = ".$ordine." 
ORDER BY p.varianti ASC, p.sku ASC, d.variante ASC
";

$dettagli = $db->fetch_array($qry);

if($dettagli){
	
	$varianti = -1;
	
	foreach($dettagli as $d){
		
		// se cambio variante intesto
		if($d['varianti'] != $varianti){
			
			if($varianti != -1){
				
				$totriga = ($varianti == 0) ? false : true;
				
				// output contenuto
				$csv .= toCsv($out, $ev, $sep, $totriga);
	
				// aggiungo riga vuota
				$csv .= "\n";

				// libero array dei dettagli
				$out = array();
				
			}
			
			if($d['varianti'] == 0){
				$elenco_varianti = array("Pezzi"); // TODO: translate
				$ev = array(0);
			}else{
				$elenco_varianti = $db->col_value("v.nome", "data_varianti AS v", 
												  "JOIN data_varianti_x_gruppi AS x ON (x.variante = v.id) 
												  WHERE x.gruppo = '".$d['varianti']."' ORDER BY v.nome");
				$ev = $db->col_value("v.id", "data_varianti AS v", 
												  "JOIN data_varianti_x_gruppi AS x ON (x.variante = v.id) 
												  WHERE x.gruppo = '".$d['varianti']."' ORDER BY v.nome");
				/** solo per anellissimi***/
				foreach($elenco_varianti as $i => $nome_variante){
					$split = explode(" ", $nome_variante);
					$elenco_varianti[$i] = $split[1];
				}
				
			}
			
			array_unshift($elenco_varianti, "", ""); // prepend two empty cells
			if($d['varianti'] != 0){
				$elenco_varianti[] = "";
				$elenco_varianti[] = "Totale";
				
			}
			
			//$intesta[$d['varianti']] = implode($sep, $elenco_varianti);
			$csv .= implode($sep, $elenco_varianti)."\n";
			//echo implode($sep, $elenco_varianti);
			$varianti = $d['varianti'];
			
		}
		
		$qta = (int) $d['qta'];


		if($d['varianti'] != 0) $totale_ordine += $qta; // nel totale ordine non conto le quantità dei pezzi a taglia unica
		if(empty($qta)) $qta = "";
		
		$out [$d['articolo']] ['sku'] = $d['sku'];
		$out [$d['articolo']] ['pezzi'] [$d['variante']] = $qta;
	
		
	}
	$totriga = ($d['varianti'] == 0) ? false : true;
	$csv .= toCsv($out, $ev, $sep, $totriga);
	$csv .= "\n";
	$csv .= "TOT PEZZI:;".$totale_ordine;
	
	
}else{
	echo "no ordine!";
}
echo "<pre>";
echo $csv;
echo "</pre>";



function toCsv($data, $head = array(), $sep = ";", $tot = true){
	
	$csv = "";
	
	foreach($data as $item){
		
		$row = array($item['sku']);
		$row[] = "";
		
		foreach($head as $variante){
		
		//foreach($item['pezzi'] as $pezzo){
			$row[] = $item['pezzi'][$variante];
		}
		
		if($tot){
			$totpezzi = array_sum($item['pezzi']);
			$row[] = "";
			$row[] = $totpezzi;			
		}
		
		$csv .= implode($sep, $row)."\n";
		
	}
	
	return $csv;
	
}


?>
