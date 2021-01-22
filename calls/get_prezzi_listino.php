<?php

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$html = "";
$oggi = new DateTime();
$output['old'] = array();


// sanify
$idlistino = (int) $db->make_data_safe($_POST['listino']);
$modalita  = $db->make_data_safe($_POST['modalita']); // se readonly o new

// get info relativo al listino
$listino = $db->get1row("data_listini", "WHERE id = '".$idlistino."'");

// nessun listino trovato...
if(empty($listino)){
	$output['error'] = "no-listino";
	$output['msg'] = "Nessun listino trovato!";
	echo json_encode($output);
	die();
}

// feedback la ricarico ed eventuale cliente attribuito
$output['ricarico'] = (float) $listino['ricarica'];
$output['cliente']  = (int)   $listino['cliente'];

// array delle categorie prodotto
$categorie = $db->key_value("id", "nome", "data_categorie", "WHERE active = '1' 
AND id IN (SELECT DISTINCT categoria FROM data_prodotti WHERE active = '1') ORDER BY nome");

// recupero tutti i prodotti attivi con annessa categoria (sia none che id), um, codice iva e valore iva ordino per nome categoria poi nome prodotto
$prodotti = $db->fetch_array("
SELECT c.nome AS nome_categoria, p.* FROM `data_prodotti` AS p 
LEFT JOIN `data_categorie` AS c ON (c.id = p.categoria)
WHERE p.active = '1' 
ORDER BY c.nome, p.descrizione
");

// Recupero tutti i prezzi già codificati precedentemente in questo listino (e che non sono scaduti, ovvero valido_fino non è ancora passato)
$prezzi_vecchi = $db->key_value("articolo", "prezzo", DBTABLE_LISTINI_PREZZI, "WHERE listino = '".$idlistino."' AND valido_fino >= '".$oggi->format('Y-m-d')."'");

// Definisco classi e nomi intestazione tabella
$ths = array();
$ths['th_cod'] = "Cod.";
$ths['th_desc'] = "Descrizione";
if($modalita == 'new') $ths['th_price'] = "Prezzo";
$ths['th_old'] = "Prezzo listino";
if($modalita == 'new') $ths['th_old'] .= " vecchio";
if($modalita != 'new') $ths['th_iva'] = "Iva";
if($modalita != 'new') $ths['th_um']  = "U.m.";

// genero intestazione tabella
$thead = "";
foreach($ths as $thclass => $thname){
	$thead .= "<th class='".$thclass."'>".$thname."</th>";
}
$thead = "<tr>".$thead."</tr>\n";


$ncols = count($ths); 

// se ho prodotti in archivio proseguo
if($prodotti){
	
	$c = 0;
	$old_cat = "";
	
	// creo header tabella
	$html .= "<table class='table table-bordered'>\n";
	$html .= "<thead>\n";
	$html .= $thead;
	$html .= "</thead>\n";
	$html .= "<tbody>\n";
	
	// loop prodotti in archivio
	foreach($prodotti as $prodotto){
		
		// class riga
		$trclass = 'riga_prezzo';
		$icon_color = "";
		
		
		// recupero il prezzo prodotto così come registrato l'ultimo lunedì oppure ultima data se ultimo lunedì è vuoto
		// se proprio non trovato in tabella data_prezzi_giorno sarà false
		$prezzi = $helper->getPrezzoLunedi($oggi->format("Y-m-d"), $prodotto['codice']);		
		
		if($prezzi === false){
			// mai registrato come prezzo, lo metto in listino
			$prezzo = (float) 0;
			$title_prezzo = "Prezzo mai registrato";
			if($modalita == 'new') $trclass .= ' prezzo-zero';
		}else{
			/* ok ho prezzo in tabella prezzi_giorno, controllo data ultima registrazione (ultimo lunedì, tra oggi e lunedì o più vecchio di lunedì) */
			
			$dt = new DateTime($prezzi['datum']); // data del prezzo (ultimo lunedì, tra oggi e lunedì o più vecchio di lunedì)			
			$prezzo = (float) $prezzi['acquisto']; 			
			$display_date = $dt->format("d/m/Y");
			
			// calcolo dati trascorsi tra oggi e data registrazione prezzo
			$interval = $oggi->diff($dt);
			$days = (int) $interval->format('%a');
			
			// se la data ultima registrazione prezzo è maggiore di 30gg e non è presente in $prezzi_vecchi (ovvero non era presente nel listino precedente) lo metto tra gli articoli stagionali che possono poi essere aggiunti a mano a listino 
			if($days > 30 and !in_array($prodotto['codice'], array_keys($prezzi_vecchi))){
				if($modalita == 'new') $output['old'][] = array("codice" => $prodotto['codice'], "descrizione" => $prodotto['descrizione'], "data" => $dt->format("d/m/Y"), "prezzo" => (float) $prezzi['acquisto']);
				$trclass = 'riga_scaduto'; // aggiungo riga a tabella, ma non è visibile
				$icon_color = "text-red";
			}
			
			$title_prezzo = "Prezzo registrato il ".$display_date." : € ".number_format($prezzo, 2, ",", ".");
		}
		
		// calcolo il prezzo listino ricaricando il prezzo registrato con la percentual di ricarico attribuito al listino
		$prezzo = $prezzo * ($output['ricarico'] / 100 + 1);
		$prezzo = number_format($prezzo, 2, ".", "");
		
		// prezzo di listino vecchio ovvero com'era memorizzato in prezzi_listino fino a questo momento. Se non c'era metto 0 o ND in base a modalità
		if(isset($prezzi_vecchi[$prodotto['codice']])){
			// ok ho un prezzo di listino vecchio
			if($modalita != 'new' and (int) $prezzi_vecchi[$prodotto['codice']] == 0) $trclass .= ' prezzo-zero';
			$listprice = number_format($prezzi_vecchi[$prodotto['codice']], 2, ".", ""); // usato per switchare tra prezzo calcolato e prezzo vecchio listino in modifica / nuovo listino
			$prezzo_vecchio = "€ ".number_format($prezzi_vecchi[$prodotto['codice']], 2, ",", ".");
		}else{
			$listprice = "0.00"; // usato per switchare tra prezzo calcolato e prezzo vecchio listino in modifica / nuovo listino
			
			if($modalita == 'new'){
				$prezzo_vecchio = "ND";
			}else{
				$prezzo_vecchio = "€ 0,00";
				if($trclass != 'riga_scaduto') $trclass .= ' prezzo-zero';
			}
		}		
		
		
		$price = "
			<div class=\"input-group prezzo\" data-prezzo-calc='".$prezzo."' data-prezzo-listino='".$listprice."' data-mostro='calc' >
				<span class=\"input-group-addon\">€</span>
				<input type=\"text\" name=\"prezzo['".$prodotto['codice']."']\" id=\"prezzo_".$prodotto['codice']."\" data-codice='".$prodotto['codice']."' value=\"".$prezzo."\" class=\"form-control prezzo\">
				<span class=\"input-group-addon d-print-none suffisso\"><small class='icon-calc'><i class='fa fa-calculator ".$icon_color." ml-2' data-toggle='tooltip' title='".$title_prezzo."'></i></small><small style='display: none' class='icon-listino text-primary'><i class='fa fa-list ml-2' data-toggle='tooltip' title='Prezzo vecchio listino'></i></small></span>
			</div>				
		";
		
		$c++;
		
		// descrizione prodotto
		$descrizione_prodotto = $prodotto['descrizione'];
		$iva = $prodotto['iva'];
		$um = $prodotto['um'];
		if($trclass == 'riga_scaduto') $descrizione_prodotto = "<em>".$descrizione_prodotto." <sup><i title='Stagionale' data-toggle='tooltip' class='fa fa-sun-o'></i></sup></em>";
		
		// riga categoria, se prima o cambio scrivo il nome categoria
		if($old_cat != $prodotto['categoria']){
			$html .= "<tr>\n";
			$html .= "<th colspan=".$ncols." class='riga-categoria'>".$prodotto['nome_categoria']."</th>\n";
			$html .= "</tr>\n";			
			$old_cat = $prodotto['categoria'];
		}
		// scrivo body tabella
		$html .= "<tr data-cod='".$prodotto['codice']."' class='".$trclass."'>\n";
		$html .= "<td>".$prodotto['codice']."</td>\n";
		$html .= "<td>".$descrizione_prodotto."</td>\n";
		if($modalita == 'new') $html .= "<td align='right'>".$price."</td>\n";
		$html .= "<td align='right'>".$prezzo_vecchio."</td>\n";
		if($modalita != 'new') $html .= "<td class='td_iva' align='right'>".$iva."%</td>\n";
		if($modalita != 'new') $html .= "<td class='td_um' align='center'>".$um."</td>\n";
		$html .= "</tr>\n";
	}
	$html .= "</tbody>\n";
	$html .= "</table>\n";
	
}

	
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $html;
	



echo json_encode($output);

	
?>
