<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
	
<?php

	/** TEMPLATE TABELLA VALIDO SI PER I PRODOTTI CON VARIANTI CHE PER QUELLI SENZA ***/
$table_template = '
<table cellspacing="1" cellpadding="4" style="background-color: #aaaaaa; font-family: Helvetica, Arial, \'sans-serif\'" >
	<thead>{{thead}}</thead>
	<tbody>{{tbody}}</tbody>
</table>
';

/*** THEAD / INTESTAZIONE PRODOTTI SENZA VARIANTI ***/
$single_thead_template = '
		<tr>
			<th colspan="2" style="background-color: {{dark-bgcolor}}; color: {{color}}">{{sku}}</th>
			<th style="background-color: {{#bgcolor}}; color: {{color}}">IMPORTO</th>
		</tr>
';

	
/*** TBODY PRODOTTI SENZA VARIANTI - SOLO UNA RIGA CHE VERRà REPLICATA 3 VOLTE PER CREARE TBODY ***/
$single_tbody_row_template = '
		<tr>
			<td style="background-color: #ffffff; text-align: right">{{label}}</td>
			<td style="background-color: #ffffff; text-align: center">{{qta}}</td>
			<td style="background-color: #ffffff; text-align: right">{{importo}}</td>
		</tr>
';
	
	
/*** THEAD PRODOTTI CON VARIANTI - SEGNAPOSTO PER CODICE ARTICOLO, I DUE COLORI DI SFONDO ED I NOMI VARIANTEI (SOTTOTEMPLATE) ***/
$var_thead_template = '
		<tr>
			<th style="background-color: {{sku-bgcolor}}; color: {{sku-color}}">{{sku}}</th>
			{{varianti}}
			<th style="background-color: {{bgcolor}}; color: {{color}}">TOTALE</th>
			<th style="background-color: {{bgcolor}}; color: {{color}}">IMPORTO</th>
		</tr>
';

/*** PRODOTTI CON VARIANTE - SINGOLA COLONNA DI THEAD - PASSO COLORI SFONDO E NOME DELLA VARIANTE ***/
$var_thead_cols_template = '
	<th style="background-color: {{bgcolor}}; color: {{color}}">{{nome-variante}}</th>
';
	

/*** PRODOTTO CON VARIANTI - SINGOLA RIGA TBODY. SEGNAPOSTO PER LABEL, LE VARIANTI (SOTTO-TEMPLATE) E IMPORTO TOTALE ***/
$var_tbody_template = '
		<tr>
			<th style="background-color: #ffffff; text-align: right">{{label}}</th>
			{{varianti}}
			<th style="background-color: #ffffff; text-align: right">{{importo}}</th>
		</tr>
';	

/*** PRODOTTI CON VARIANTE - SINGOLA COLONNA DI TBODy - PASSO QTA (ANCHE IL TOTALE PEZZI) ***/
$var_thead_cols_template = '
	<td style="background-color: #ffffff; text-align: center">{{qta}}</td>
';
	
	
	

	
function template($template, $data, $leave_tags = false){	
	
	// tags ovvero: {{nome-tag}}
	$pattern = "/{{([a-zA-Z0-9_-]+)}}/i";
	preg_match_all($pattern, $template, $matches);
	
	// Se ho trovato almeno un tag li ciclo e cerco corrispondenza tra le chiavi dell'array $data
	if($matches){
		
		$keys = $matches[1]; // nome-tag
		$subs = $matches[0]; // {{nome-tag}}
		
		foreach($subs as $k => $search){
			$key = $keys[$k]; // $k è numerico
			// se il falg $leave_tags è true e non trovo corrispondenza in $data lascio il tag, se no, in caso di non corrispondeza rimuovo il tag lasciando vuoto
			$value = ($leave_tags) ? (array_key_exists($key, $data)) ? $data[$key] : $search : $data[$key];
			$template = str_replace($search, $value, $template);			
		}
	}
	
	return $template;
	
}

function multirow_template($data = array(), $template = ''){
	
	$html = "";
	
	if(empty($template)) return "";
	
	if(!is_array($data)) return $template;
	
	foreach($data as $row){
		
		if(is_array($row)){
			
			$html .= template($template, $row);
			
		}
		
	}
	
	return $html;
	
}
	
?>
	
<table cellspacing="1" cellpadding="4" style="background-color: #aaa; font-family: 'Gill Sans', 'Gill Sans MT', 'Myriad Pro', 'DejaVu Sans Condensed', Helvetica, Arial, 'sans-serif'" >
	<thead>
		<tr>
			<th colspan="2" style="background-color: #b5bbc8; color: #000000">PAN 003</th>
			<th style="background-color: #d2d6de; color: #000000">IMPORTO</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="background-color: #ffffff; text-align: right">Ordinato</td>
			<td style="background-color: #ffffff; text-align: center">1</td>
			<td style="background-color: #ffffff; text-align: right">€ 39,00</td>
		</tr>
		<tr>
			<td style="background-color: #ffffff; text-align: right">Spedito</td>
			<td style="background-color: #ffffff; text-align: center">1</td>			
			<td style="background-color: #ffffff; text-align: right">€ 39,00</td>
		</tr>
		<tr>
			<td style="background-color: #ffffff; text-align: right">Diff.</td>
			<td style="background-color: #ffffff; text-align: center">0</td>			
			<td style="background-color: #ffffff; text-align: right">€ 0,00</td>
		</tr>
	</tbody>
</table>


<table cellspacing="1" cellpadding="4" style="background-color: #888; font-family: 'Gill Sans', 'Gill Sans MT', 'Myriad Pro', 'DejaVu Sans Condensed', Helvetica, Arial, 'sans-serif'" >
	<thead>
		<tr>
			<th style="background-color: #357ca5; color: #ffffff">ACC 003</th>
			<th style="background-color: #3c8dbc; color: #ffffff">Misura 08</th>
			<th style="background-color: #3c8dbc; color: #ffffff">Misura 10</th>
			<th style="background-color: #3c8dbc; color: #ffffff">Misura 12</th>
			<th style="background-color: #3c8dbc; color: #ffffff">TOTALE</th>
			<th style="background-color: #3c8dbc; color: #ffffff">IMPORTO</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th style="background-color: #ffffff; text-align: right">Ordinato</th>
			<td style="background-color: #ffffff; text-align: center">0</td>
			<td style="background-color: #ffffff; text-align: center">2</td>
			<td style="background-color: #ffffff; text-align: center">3</td>
			<td style="background-color: #ffffff; text-align: center">5</td>
			<td style="background-color: #ffffff; text-align: right">€ 115,00</td>
		</tr>
		<tr>
			<th style="background-color: #ffffff; text-align: right">Spedito</th>
			<td style="background-color: #ffffff; text-align: center">0</td>
			<td style="background-color: #ffdddd; text-align: center">1</td>
			<td style="background-color: #ffffff; text-align: center">3</td>
			<td style="background-color: #ffffff; text-align: center">4</td>
			<td style="background-color: #ffffff; text-align: right">€ 92,00</td>
		</tr>
		<tr>
			<th style="background-color: #ffffff; text-align: right">Diff</th>
			<td style="background-color: #ffffff; text-align: center">0</td>
			<td style="background-color: #ffffff; text-align: center"><span style="color: #dd0000;">-1</span></td>
			<td style="background-color: #ffffff; text-align: center">0</td>
			<td style="background-color: #ffffff; text-align: center"><span style="color: #dd0000;">-1</span></td>
			<td style="background-color: #ffffff; text-align: right"><span style="color: #dd0000;">€ -23,00</span></td>
		</tr>
	</tbody>
</table>
	
	
</body>
</html>