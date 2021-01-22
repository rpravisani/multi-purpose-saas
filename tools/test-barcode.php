<?php 
session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$template_qry_variante = "
SELECT v.id, v.nome 
FROM data_varianti AS v 
JOIN `data_prodotti_varianti` as x ON (x.variante = v.id) 
WHERE x.prodotto= '%s' 
AND x.active = '1' 
AND x.listino = '1'
";

$tables = array();

$thumb_size = '120'; // in pixels both height and width
$barcode_cols_varianti = 3; // from 1 to 12
$barcode_cols_no_varianti = 1; // from 1 to 12

// categories with no variants
$no_var_cat = array(4);
$tab_cols_no_variant = 2;

// filter
$cat = 4; // if not 0 filter for category - is overrun if $sku is not 0
$sku = 0; // if not 0 filter for product 



// recupero tutti i prodotti e li suddivido per categoria

if(!empty($sku)){
	$cat = $db->get1value("categoria", "data_prodotti", "WHERE id = '".$sku."'");
	$prod_clause = " AND id = '".$sku."'";
}else{
	$prod_clause = "";
}

$cat_clause = (empty($cat)) ? "" : " AND id = '".$cat."'";
$categorie = $db->key_value("id", "nome", "data_categorie", "WHERE active = '1' ".$cat_clause." ORDER BY ordine");

if($categorie){
	
	foreach($categorie as $idcat => $categoria){
		
		
		$noVarianti = (in_array($idcat, $no_var_cat)) ? true : false;
		
		
		$thcolspan = ($noVarianti) ? $tab_cols_no_variant*2 : 2;
		
		$table  = "<table class='table table-bordered'>\n";
		$table .= "<thead>\n";
		$table .= "<tr><th colspan='".$thcolspan."'><h3>".$categoria."</h3></th></tr>\n";
		$table .= "</thead>\n";
		
		$prodotti = $db->select_all("data_prodotti", "WHERE categoria = '".$idcat."' ".$prod_clause." AND active = '1' ORDER BY sku");
		
		if($prodotti){
			
			$table .= "<tbody>\n";
			$switch = 0;
			
			foreach($prodotti as $i => $prodotto){
				
				$media = $db->get1value("file", "media", "WHERE page = '15' AND record = '".$prodotto['id']."' AND `order` = '1'");
				
				$src = ($media) ? "../photo/".$media : "../images/dummy_image.png";
				$img = SITEROOT."required/img.php?file=".$src."&c=1&p=0&w=".$thumb_size."&h=".$thumb_size."&u=1&q=60&cache=1";
				
				if($noVarianti) $switch = $i%$tab_cols_no_variant;
				
				if(empty($switch)) $table .= "<tr>";
				
				
				// thumbnail col
				$table .= "<td align='center'>";
				$table .= "<img src='".$img."'><br>";
				$table .= "<strong>".$prodotto['sku']."</strong>";
				$table .= "</td>";
				
				// barcodes col
				$table .= "<td>";
				$table .= "<div class='row'>";
				
				$number_of_cols = ($noVarianti) ? $barcode_cols_no_varianti : $barcode_cols_varianti;
				
				if(empty($prodotto['varianti'])){
					
					// NO VARIANTI
					$code = createCode($prodotto['id'], '0');
					$table .= barcode($code, $prodotto['sku'], $number_of_cols);
					
				}else{
										
					$qry_variante = sprintf($template_qry_variante, $prodotto['id']);

					$varianti = $db->fetch_array($qry_variante);
					
					foreach($varianti as $variante){
						$code = createCode($prodotto['id'], $variante['id']);
						$table .= barcode($code, $variante['nome'], $number_of_cols);
						
					}
					
				}
				
				$table .= "</div>";
				$table .= "</td>";
				
				if(!$noVarianti or $switch == $tab_cols_no_variant-1) $table .= "</tr>";
				
			}
			
			$table .= "</tbody>\n";
			
		} // end if prodotti
		
		$table .= "</table>\n";
		
		$tables[] = $table;
		
	} // end foreach categorie
	
} // end if categorie
	

function barcode($code, $didascalia, $cols = '6'){
	
	$cols = (int) $cols;
	if($cols > 12 or $cols < 1) $cols = 6;
	
	$colclass = round(12/$cols);
	
	$div  = "<div class='text-center my-1 col-sm-".$colclass."'>\n";
	$div .= "<div class='p-2 border'>\n";
	
	$div .= "<img width='100%' src='output.php?code=".$code."&size=6'><br>";
	
	$div .= "<strong>".$didascalia."</strong>\n";					
	$div .= "</div>";
	$div .= "</div>";
	
	return $div;
	
}

function createCode($prodotto, $variante, $tipo = "EAN13"){
	
	$prodotto = (int) trim($prodotto);
	$variante = (int) trim($variante);
	
	switch($tipo){
		case "EAN13":
			// length 12 > 6 digits for $prodotto, 6 digits for $variante
			$prodotto = str_pad($prodotto, 6, "0", STR_PAD_LEFT);
			$variante = str_pad($variante, 6, "0", STR_PAD_LEFT);
			$code = $prodotto.$variante;
			break;
	}
	return $code;
	
}
	
	
?>


<!doctype html>
<html lang="it">
  <head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Test Barcode</title>
	<!-- Bootstrap 4.1.1 -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<!-- Font Awesome 5 -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

	<style type="text/css">

	/* ----------------------------------------------------------------
		MEDIA QUERIES
	-----------------------------------------------------------------*/

	/* < 1200px */
	@media (max-width: 1199px) {

	}

	/* > 991px < 1200px */
	@media (min-width: 992px) and (max-width: 1199px) {

	}

	/* < 992px */
	@media (max-width: 991px) {

	}

	/* > 767px < 992px */
	@media (min-width: 768px) and (max-width: 991px) {
	}

	/* < 768px */
	@media (max-width: 767px) {

	}

	/* > 479px < 768px */
	@media (min-width: 480px) and (max-width: 767px) {

	}

	/* < 480px */
	@media (max-width: 479px) {

	}
	  
  </style>
</head>

<body>

	<div class="container-fluid ">
	  <!-- Content here -->
		
		<?php
		if($tables){
			echo implode("<br>\n", $tables);
		}
		?>
		
	</div>	
	
	<!-- jQuery 3 -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<!-- popper -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<!-- Bootstrap 4.1.1 -->
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	
	<script type="text/javascript">
		
		$(document).ready(function() {
			
			
			
		});
		
	</script>

	
	
</body>
</html>