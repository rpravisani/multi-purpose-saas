<?php

include_once '_head.php';

if(empty($_GET)){
	echo json_encode($output);
	die();  	
}

$export_dati_cliente = false;
$export_codice_iva = false;
$costo_riga_is_netto = true; // se true e peso netto non è vuoto ricalcolo costo riga usando peso netto

$ordine = (int) $_GET['ord'];

$campi_ordine = "
o.num_ordine, 
o.anno_ordine, 
o.data_ordine,  
";

$campi_cliente = "
c.Ragione_Sociale, 
c.Indirizzo, 
c.Cap, 
c.Localita, 
c.Prov, 
c.nazione, 
c.Email, 
c.Telefono, 
CAST(c.piva AS BINARY) AS partiva, 
CAST(c.Cod_Fiscale AS BINARY) AS Cod_Fiscale, 
";

$campi_dettagli = "
p.codice, 
p.descrizione, 
d.ordinato, 
d.um, 
d.costo_unit, 
d.ordinato * d.costo_unit AS costo_riga, 
p.iva, 
d.peso_lordo, 
d.peso_netto
";

if($export_codice_iva) $campi_dettagli .= ",
o.codice_iva";


// construct query
$qry = "SELECT ";
$qry .= $campi_ordine;
if($export_dati_cliente) $qry .= $campi_cliente;
$qry .= $campi_dettagli;
$qry .= "
FROM data_ordini AS o 
JOIN data_ordini_dettagli AS d ON (d.ordine = o.id) 
JOIN data_prodotti AS p ON (p.codice = d.prodotto) 
";
if($export_dati_cliente) $qry .= "JOIN data_fornitori AS c ON (c.id = o.cliente) ";
$qry .= "WHERE o.id = '".$ordine."'";


// get data
$dati = $db->fetch_array($qry);


// if no data is found return nothing
if(empty($dati)){
	$_SESSION['error_title'] = "L'ordine è vuoto";
	$_SESSION['error_message'] = "Nessun articolo presente nell'ordine";
	header('location: '.$_SERVER['HTTP_REFERER']);
	die();
}

// get array keys as fisrt row (header)
$intesta = array_keys($dati[0]);

// define field separator
$sep = ";";


/* OUTPUT (USING OUTPUT BUFFER)
***************************************************/
ob_start();
$out = fopen("php://output", 'w'); // open stream instead of file

// output first row / header
fputcsv($out, $intesta, $sep);

// cycle trhough data and output a row at the time
foreach($dati as $row){
	
	// format order date
	$dt = new DateTime($row['data_ordine']);
	$row['data_ordine'] = $dt->format("Y-m-d");
	if( $export_codice_iva and !empty($row['codice_iva']) ) $row['iva'] = 0;
	
	$row['peso_lordo'] = (float) $row['peso_lordo'];
	$row['peso_netto'] = (float) $row['peso_netto'];
	$row['ordinato']   = (float) $row['ordinato'];
	$row['costo_unit'] = (float) $row['costo_unit'];
	$row['costo_riga'] = (float) $row['costo_riga'];
	$row['iva']        = (float) $row['iva'];
	
	if(!empty($row['peso_netto']) and $costo_riga_is_netto){
		$row['costo_riga'] = $row['peso_netto'] * $row['costo_unit'];
	}
	
	// write row to stream as csv
	
	//fputcsv($out, $row, $sep);
	fputs($out, implode($sep, $row)."\n");
	
}

// close output stream
fclose($out);

// get output buffer 
$csv = ob_get_clean();

// close output buffer
ob_end_clean();

// recupero nome cliente e destinazione
$qry = "SELECT CONCAT(f.Ragione_Sociale, ' ', o.destinazione) AS string
FROM `data_ordini` as o 
JOIN data_fornitori AS f ON (f.id = o.cliente) 
WHERE o.id = '".$ordine."'";
$cli_dest = $db->fetch_array_row($qry);
if($cli_dest){
	$cd = mb_strtolower(trim($cli_dest['string']));
	$cd = preg_replace('/\./', "", $cd);
	$cd = preg_replace('/[^a-z0-9]/', "-", $cd);
	$cd = preg_replace('/-{2,}/', "-", $cd);	
}

// Define name of csv file
$filename = $helper->formatOrderNumber($dati[0]['num_ordine'], $dati[0]['anno_ordine'] );
$filename = str_replace("/", "-", $filename);
$filename = "ordine-".$cd."-".$filename.".csv";

// disable caching
$now = gmdate("D, d M Y H:i:s");
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
header("Last-Modified: {$now} GMT");

// force download  
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// disposition / encoding on response body
header("Content-Disposition: attachment;filename={$filename}");
header("Content-Transfer-Encoding: binary");

// finally output csv
echo $csv;


?>