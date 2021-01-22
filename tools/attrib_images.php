<?php
/* Attribuisco ad ogni articolo tutte le varianti del gruppo con prezzo standard di vendita come prezzo variante */
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$pid = 15;

?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Attbib images to articoli</title>
<link rel="stylesheet" href= "../plugins/select2/select2.min.css">
	<script src="../plugins/jQuery/jQuery-2.1.4.min.js"></script>
	<script src= "../plugins/select2/select2.full.min.js"></script>
	<script src= "../plugins/select2/i18n/it.js"></script>
	<script type="text/javascript"> 
		$(function () {
			//Initialize Select2 Elements
			$(".select2").select2();
		});
	</script>
</head>

<body>
<?php

if(!empty($_POST['articolo'])){
	$updated = 0;
	foreach($_POST['articolo'] as $idmedia => $idarticolo){
		if(!empty($idarticolo)){
			$updatevalues = array( "page" => $pid, "record" => $idarticolo, "order" => '1' ); // todo per versione in framework: order
			$db->update("media", $updatevalues, "WHERE id = '".$idmedia."'");
			$updated++;
		}
	}
	echo "Aggiornati ".$updated." record in tab media<br>\n";
}

$c = 0;
// recupero da DB tutte i file media con record e pagina = 0
$media = $db->select_all("media", "WHERE page = '0' and record = '0' ORDER BY name ASC");
if(!$media) die("No media found");

// tutti gli articoli
$articoli = $db->key_value("id", "sku", "data_prodotti", "ORDER BY sku");
if(!$articoli) die("No articoli found");


echo "<form method='post'>\n";
echo "<table width='50%' cellpadding='5' cellspacing='0' border='1'>\n";

foreach($media as $row){
	$c++;
	$searchfor = $row['name'];
	$id = array_search($searchfor, $articoli);
	$dbg = "";
	if(!$id){
		$searchfor = strtolower($row['name']);
		$dbg .= $searchfor." • ";
		$id = array_search($searchfor, $articoli);
	}
	if(!$id){
		$searchfor = strtoupper($row['name']);
		$id = array_search($searchfor, $articoli);
		$dbg .= $searchfor." • ";
	}
	if(!$id){
		$searchfor = str_replace(" ", "", $row['name']);
		$id = array_search($searchfor, $articoli);
		$dbg .= $searchfor." • ";
	}
	if(!$id){
		$searchfor = strtolower(str_replace(" ", "", $row['name']));
		$id = array_search($searchfor, $articoli);
		$dbg .= $searchfor." • ";
	}
	if(!$id){
		$searchfor = strtoupper(str_replace(" ", "", $row['name']));
		$id = array_search($searchfor, $articoli);
		$dbg .= $searchfor." • ";
	}
	if(!$id){
		$id = 0;
	}
		
	
	$options = array2options($articoli, $id);
	$select = "<select class='select2' name='articolo[".$row['id']."]' id='articolo_".$row['id']."'>\n".$options."\n</select>\n";
	$flag = (empty($id)) ? " <span style='color: red'>X</span>" : "";
	echo "<tr>\n";
	echo "<td width='20' align='center'>".$c."</td>";
	echo "<td width='150' align='center'><img src='../photo/".$row['file']."' width='80'><br>".$row['name']."</td>";
	echo "<td><label>SKU</label><br>".$select.$flag."</td>";
	echo "</tr>\n";
	
}
echo "</table>";
echo "<input type='submit' value='Conferma'>";
echo "</form>";


function array2options($array, $selected = false, $first_empty = true){
	$option = ($first_empty) ? "<option></option>" : "";
	if(!is_array($array)) return $option;
	foreach($array as $k => $v){
		$s = ($selected == $k) ? "selected" : "";
		$option .= "<option ".$s." value='".$k."'>".$v."</option>";
	}
	return $option;
	
}


?>
</body>
</html>
