<?php 
session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

// config
$config = $db->key_value("param", "value", "config");
$bctype = (empty($config['barcode_type'])) ? "ean13" : strtolower($config['barcode_type']);
$cache  = (empty($config['barcode_caching'])) ? 1 : (int) $config['barcode_caching'];



$template_qry_variante = "
SELECT v.id, v.nome 
FROM ".DBTABLE_VARIAZIONI." AS v 
JOIN `".DBTABLE_PRODOTTI_VARIANTI."` AS x ON (x.variante = v.id) 
WHERE x.prodotto= '%s' 
AND x.active = '1' 
AND x.listino = '1'
";

$tables = array();
$nprod = $nbcode = 0;

$thumb_size = '120'; // in pixels both height and width
$barcode_cols_varianti = 3; // from 1 to 12
$barcode_cols_no_varianti = 1; // from 1 to 12

// categories with no variants
$no_var_cat = array(4);
$tab_cols_no_variant = 2;

// filter
$cat = (empty($_GET['cat'])) ? 0 : (int) $_GET['cat']; // if not 0 filter for category - is overrun if $sku is not 0
$sku = (empty($_GET['sku'])) ? 0 : (int) $_GET['sku'];; // if not 0 filter for product 


// recupero tutti i prodotti e li suddivido per categoria

if(!empty($sku)){
	$cat = $db->get1value("categoria", DBTABLE_PRODOTTI, "WHERE id = '".$sku."'");
	$prod_clause = " AND id = '".$sku."'";
}else{
	$prod_clause = "";
}

$cat_clause = (empty($cat)) ? "" : " AND id = '".$cat."'";
$categorie = $db->key_value("id", "nome", DBTABLE_CATEGORIE, "WHERE active = '1' ".$cat_clause." ORDER BY ordine");

if($categorie){
	
	foreach($categorie as $idcat => $categoria){		
		
		$noVarianti = (in_array($idcat, $no_var_cat)) ? true : false;		
		
		$thcolspan = ($noVarianti) ? $tab_cols_no_variant*2 : 2;
		
		$table  = "<table class='table table-bordered table-sm'>\n";
		$table .= "<thead>\n";
		$table .= "<tr><th colspan='".$thcolspan."'><h3>".$categoria."</h3></th></tr>\n";
		$table .= "</thead>\n";
		
		$prodotti = $db->select_all(DBTABLE_PRODOTTI, "WHERE categoria = '".$idcat."' ".$prod_clause." AND active = '1' ORDER BY sku");
		
		if($prodotti){
			
			$table .= "<tbody>\n";
			$switch = 0;
			
			foreach($prodotti as $i => $prodotto){
				
				$nprod++;
				
				$media = $db->get1value("file", DBTABLE_MEDIA, "WHERE page = '15' AND record = '".$prodotto['id']."' AND `order` = '1'");
				
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
					$pkey = ($bctype == "ean13") ? "id" : "sku";
					$var  = ($bctype == "ean13") ? "0" : "-";
					
					$code = createCode($prodotto[$pkey], $var, $bctype);
					$table .= barcode($code, $prodotto['sku'], $number_of_cols, $bctype);
					
				}else{
										
					$qry_variante = sprintf($template_qry_variante, $prodotto['id']);

					$varianti = $db->fetch_array($qry_variante);
					
					// se EAN13 il codice dev'essere numerico se no metto sku e nome variante
					$pkey = ($bctype == "ean13") ? "id" : "sku";
					$vkey = ($bctype == "ean13") ? "id" : "nome";
					
					
					foreach($varianti as $variante){
						$code = createCode($prodotto[$pkey], $variante[$vkey], $bctype);
						$table .= barcode($code, $variante['nome'], $number_of_cols, $bctype);
						
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
	
// genera html per mostrare barcode
function barcode($code, $didascalia, $cols = '6', $bctype = "ean13", $cache = true){
	
	// num di barcode generati
	global $nbcode;
	$nbcode++;
	
	$code = urlencode($code);
	
	$size = (strtolower($bctype) == "ean13") ? 6 : 75;
	$cache = ($cache === true) ? 1 : 0;
	
	$cols = (int) $cols;
	if($cols > 12 or $cols < 1) $cols = 6; // max n di colonne è 6
	
	$colclass = round(12/$cols);
	
	$div  = "<div class='text-center my-1 col-sm-".$colclass."'>\n";
	$div .= "<div class='p-2 border'>\n";
	
	$div .= "<img width='100%' src='output.php?code=".$code."&type=".$bctype."&cache=".$cache."&size=".$size."'><br>";
	
	if($bctype == 'ean13') $div .= "<strong>".$didascalia."</strong>\n";					
	$div .= "</div>";
	$div .= "</div>";
	
	return $div;
	
}

// crea codice da tradurre in barre in base ai parametri prodotto, variantee tipo di barcode
function createCode($prodotto, $variante, $tipo = "ean13"){
	
	$prodotto = trim($prodotto);
	$variante = trim($variante);
	
	switch($tipo){
		case "ean13":
			// length 12 > 6 digits for $prodotto, 6 digits for $variante
			$prodotto = str_pad($prodotto, 6, "0", STR_PAD_LEFT);
			$variante = str_pad($variante, 6, "0", STR_PAD_LEFT);
			$code = $prodotto.$variante;
			break;
		case "code128":
			// tolgo "Misura" da variante lasciando solo il numero, dietro specifica richesta di Pier in data 18/03/19
			$variante = str_replace("Misura ", "", $variante);
			if(empty($variante)) $variante = "-";
			$code = $prodotto."/".$variante."/-"; // p.e. "ARS 001/Misura 8/-"
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
	<title>Catalogo Barcode</title>
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
	
		#preloader{
			position: fixed;
			width: 100%;
			height: 100%;
			background-color: rgba(255,255,255,0.90);
			z-index: 100;
		}
		
		#preloader > div{
			width: 50%;
			height: 150px;
			margin: 40vh auto;
		}
	  
  </style>
</head>

<body>

	<div class="container-fluid ">
	  <!-- Content here -->
		
		<div id="preloader">
		
			<div class="text-center">
				<i class="fa fa-spin fa-sync fa-5x text-success"></i><br>
				<h4 class='text-secondary mt-3'><strong>Attendere generazione barcode ed immagini in corso...</strong></h4>
			</div>
		
		</div>
		
		<?php
		//echo "nprod: ".$nprod." - nbcode: ".$nbcode."<br>\n";
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
		
		window.addEventListener('load', function(){
			
			$("#preloader").fadeOut("fast", function(){
				$(this).remove();
				window.print(); 
			})
			
			
			
		});
		
	</script>

	
	
</body>
</html>