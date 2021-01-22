<?php
/*****************************************************
 * add_prodotto                           *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$id = (int) $_POST['prodotto'];
$listino = '1';
$varianti = array();
$thead = $tr1 = $tr2 = "";

$table_tmpl = "<table class=\"table table-bordered table-condensed %s\">";
$td_tmpl = "<td class=\"spedito\">%s</td>";
$input_tmpl = "<input min=\"0\" data-ordinato=\"0.00\" data-id=\"0\" data-articolo=\"%s\" data-variante=\"%s\" data-prezzo=\"%s\" class=\"form-control qta %s\" value=\"0\" type=\"text\">";


$prodotto = $db->get1row(DBTABLE_PRODOTTI, "WHERE id = '".$id."'");

if(!empty($prodotto['varianti'] )){
	$qry_varianti = "
		SELECT v.id, v.nome, x.prezzo 
		FROM `data_prodotti_varianti` AS x 
		LEFT JOIN data_varianti AS v ON (v.id = x.variante) 
		WHERE listino = '".$listino."'	AND x.prodotto = '".$id."'
	";
	$varianti = $db->fetch_array($qry_varianti);
}


if(!empty($varianti)){
	
	$nome_tabella = "table-varianti";
	
	$thead = "<th class=\"bg-green-active articolo\">".$prodotto['sku']."</th>";
	$tr1   = "<th>Ordinato</th>";
	$tr2   = "<th>Spedito</th>";
	
	$i = 0;
	 
	
	foreach($varianti as $variante){
		$first = ($i == 0) ? "first-field" : "";
		$input  = sprintf($input_tmpl, $prodotto['id'], $variante['id'], $variante['prezzo'], $first);
		$thead .= "<th class=\"bg-green\">".$variante['nome']."</th>";
		$tr1   .= "<td class=\"ordinato text-center\">0</td>";
		$tr2   .= "<td class=\"spedito\">".$input."</td>";
		$i++;
	}
	
	// totali
	$thead .= "<th class=\"bg-green\">TOTALI</th>";
	$thead .= "<th class=\"bg-green\">IMPORTO</th>";
	
	$tr1 .= "<td class=\"text-center\">0</td>";
	$tr1 .= "<td class=\"text-right\">€ 0,00</td>";
	
	$tr2 .= "<td class=\"pezzi-spedito text-center\">0</td>";
	$tr2 .= "<td class=\"importo-spedito text-right\" data-importo=\"0.00\">€ 0,00</td>";
	
}else{
	
	$nome_tabella = "table-prodotti";
	
	// prezzo
	$prezzo = $db->get1value("prezzo", "data_prodotti_prezzi", "WHERE articolo = '".$prodotto['id']."' AND listino = '".$listino."'");
	
	$input = sprintf($input_tmpl, '0', $prodotto['id'], $prezzo, "first-field");
	
	$thead  = "<th colspan=\"2\" class=\"bg-green-active articolo\">".$prodotto['sku']."</th>";
	$thead .= "<th class=\"bg-green\">IMPORTO</th>";
	
	$tr1  = "<td>Ordinato</td>";
	$tr1 .= "<td class=\"ordinato text-center\">0</td>";
	$tr1 .= "<td class=\"text-right\">€ 0,00</td>";
	
	$tr2  = "<td>Spedito</td>";
	$tr2 .= "<td class=\"spedito\">".$input."</td>";
	$tr2 .= "<td data-importo=\"0\" class=\"importo-spedito text-right\">€ 0,00</td>";
	
}

$html = sprintf($table_tmpl, $nome_tabella);
$html .= "<thead><tr>".$thead."</tr></thead>\n";
$html .= "<tbody><tr>".$tr1."</tr>\n<tr>".$tr2."</tr></tbody>\n";
$html .= "</table>\n";


$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $html;



echo json_encode($output);

?>
