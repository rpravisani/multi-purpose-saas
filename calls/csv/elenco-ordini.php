<?php
/******************************
 * INCLUDED IN EXPORT2CSV.PHP *
   
 ******************************/

$separator = ";";

$record = (is_array($records)) ? $records[0] : $records;
$filename_tmpl = "{{custom}}";
$prefix_name = "packing_list_ordine_";
$clean_old_ones = true; // if true cleans all files that start with $prefix_name from folder

// HELPERS
$imgurl = HTTP_PROTOCOL.HOSTROOT.SITEROOT."photo/";
$unita_misura = "pz";

$data['packinglist'] = array();

/*
FIELDS
•	sku
•	nome
•	descrizione
•	nome variante
•	qtà
•	prezzo listino
•	prezzo_acquisto (listino - sconto listino - sconto cliente : calcolato, non estrapolato)
•	prezzo_riga (prezzoacquisto * qta: calcolato, non estrapolato)
•	unità misura (per ora pz, poi da config)
•	barcode 
•	url imagine
*/

/*** CAMBIATO - SOSTITUITO qta CON spedito ***/
$ordine = $db->get1row("data_ordini", "WHERE id = '".$record."'");

// Decido da quale campo della tabella data_ordini_dettagli estrarre le quantità. In caso di ordine salvato e evaso o fatturato il campo spedito, se no il campo qta
$campo_qta = ( ($ordine['stato'] == 'evaso' or $ordine['stato'] == 'fatturato') and $ordine['salvato'] == '1' ) ? "spedito" : "qta";

// quelli vuoti ('') servono come segnaposti
$qry = "
SELECT 
	p.sku AS cod_art, 
	p.nome, 
	p.descrizione, 
	v.nome AS variante, 
	o.".$campo_qta." AS 'quantita_pz', 
	o.prezzo AS 'prezzo_pubblico_eur', 
	'' AS prezzo_listino_riga, 
	'' AS 'prezzo_netto_sconto_eur', 
	'' AS prezzo_acquisto_riga, 
	CONCAT(p.sku, '/', v.nome, '/-') AS barcode, 
	'".$unita_misura."' AS unit, 
	m.file AS immagine 
FROM `data_ordini_dettagli` AS o 
JOIN data_prodotti AS p ON (o.articolo = p.id) 
LEFT JOIN data_varianti AS v ON (v.id = o.variante) 
LEFT JOIN media AS m ON (m.record = p.id AND m.page = '15')
WHERE o.ordine = '".$record."' AND o.".$campo_qta." > 0
";


// DATI ORDINE PER NOME FILE, SCONTO LISTINO E SCONTO CLIENTE (CONSOLIDATI)
$custom_name = $prefix_name . str_pad($ordine['progressivo'], 5, '0', STR_PAD_LEFT)."_".$ordine['anno']."_".date("Ymd_His");

// SCONTI CONSOLIDATI IN ORDINE
$sconto_listino_perc = (float) $ordine['sconto_listino'];
$sconto_cliente_perc = (float) $ordine['sconto_cliente'];


$righe = $db->fetch_array($qry);
if($righe){
	foreach($righe as $riga){
		
		unset($riga['nome']);
		unset($riga['prezzo_listino_riga']);
		unset($riga['prezzo_acquisto_riga']);
		unset($riga['pz']);
		
		$qta = (float) $riga['quantita_pz'];
		
		$prezzo_listino = (float) $riga['prezzo_pubblico_eur'];
		
		$sconto_listino  = (float) ($prezzo_listino/100)*$sconto_listino_perc;
		$prezzo_acquisto = (float) $prezzo_listino-$sconto_listino;

		if(!empty($sconto_cliente_perc)){
			$sconto_cliente = ($prezzo_acquisto/100)*$sconto_cliente_perc;
			$prezzo_acquisto -= $sconto_cliente;
		}
		
		$prezzo_listino_riga  = (float) $prezzo_listino  * (int) $qta;
		$prezzo_acquisto_riga = (float) $prezzo_acquisto * (int) $qta;
		
		
		// RIMAP VALORI NUMERICI
		$riga['quantita_pz'] = number_format($qta, 2, ",", ".");
		$riga['prezzo_pubblico_eur']  = number_format($prezzo_listino, 2, ",", ".");
		//$riga['prezzo_listino_riga']  = number_format($prezzo_listino_riga, 2, ",", ".");
		$riga['prezzo_netto_sconto_eur'] = number_format($prezzo_acquisto, 2, ",", ".");
		//$riga['prezzo_acquisto_riga'] = number_format($prezzo_acquisto_riga, 2, ",", ".");
		
		// BARCODE
		/*
		$bc = explode("|", $riga['barcode']);
		$riga['barcode'] = createCode($bc[0], $bc[1]);
		*/
		
		
		// MAP IMMAGINE
		$riga['immagine'] = $imgurl.$riga['immagine'];
		
		// MAP TO DATA
		$data['packinglist'][] = $riga;
		
		
	}
} // fine if righe

// crea codice numerico barcode. PER ORA ANCORA SISTEMA MIO EAN13 - DA CAMBIARE IN CODE128
function createCode($prodotto, $variante, $tipo = "EAN13"){
	
	$prodotto = (int) trim($prodotto);
	$variante = (int) trim($variante);
	
	switch($tipo){
		case "EAN13":
			
			// length 12 => 6 digits for $prodotto, 6 digits for $variante
			$prodotto = str_pad($prodotto, 5, "0", STR_PAD_LEFT);
			$variante = str_pad($variante, 5, "0", STR_PAD_LEFT);
			// per ora prefisso 10 al codice poi magari mettere codice definito da cliente
			$code = '10'.$prodotto.$variante;
			$code .= calcCheckdigit($code);
			break;
	}
	return $code;
	
}

function calcCheckdigit($code, $type = "EAN13"){
	switch($type){
		case "EAN13":
			
			if( strlen($code ) != 12 or !is_int($code) ) return false;
			
			$even = $odd = 0;
			
			// loop all numbers of the code and sum the odds and even positioned numbers together
			for($a=0; $a < 12; $a++){
				
			    $char = substr($code, $a, 1); // get single char
			    
			    if($a%2){
			        $even += (int) $char;
			    }else{
			       $odd += (int) $char;
			    }
			}
			
			$even = $even * 3;

			$tot = $even + $odd;

			// round up to nearest multiple of 10 (p.e.116 => 120) 
			$rounded = ceil($tot/10)*10;

			$checkdigit = $rounded - $tot;

			break;
	}
	
	return $checkdigit;
	
}



?>