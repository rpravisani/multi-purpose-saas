<?php 
session_start();

include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

include_once '../required/classes/cc_translations.class.php';
include_once '../required/classes/user_cookie.class.php';
include_once '../required/classes/cc_user.class.php';
include_once '../required/classes/cc_errorhandler.class.php';
include_once '../required/custom_token.php';


// set error object
error_reporting(E_ALL ^ E_NOTICE);
$_errorhandler = new cc_errorhandler();
set_error_handler(array($_errorhandler, 'regError'), E_ALL ^ E_NOTICE);

if(DEBUG) ini_set("display_errors", "1");

$db = new cc_dbconnect(DB_NAME);
$qry_where = "";

if(isset($_GET['t'])){
	$ord = customToken();
	if(!$ord){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
		exit();			
	}
}else{
	
	/*** ELEMENTARI ACCESS CONTROL - TODO: DA MIGLIORARE */
	if(!$_SESSION['login']){
		$_SESSION['error'] = "Access to resource denied!";
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
		exit();	
	}


	$_user = new cc_user($_SESSION['login_id'], $db);
	$lang_code = $_user->getLanguage();
	$_t = new cc_translate($db, "", $lang_code); // create instance of translate class



	switch($_user->getSubscriptionType()){
		case '2': // AGENTE
			// Posso solo vedere gli ordini che ho fatto io
			$qry_where = " AND agente = '".$_SESSION['login_id']."'";
			break;
		case '3': // CLIENTE
			// Posso solo vedere gli ordini che ho fatto io
			$idcliente = $db->get1value("id", DBTABLE_CLIENTI, "WHERE accesso = '".$_SESSION['login_id']."'");
			$qry_where = " AND cliente = '".$idcliente."'";
			break;
	}

	// get order id
	if(empty($_GET['ord'])){
		echo $_t->get("no-order-set");
		die();
	}

	$ord = (int) $_GET['ord']; 

}


// config
$config = $db->key_value("param", "value", "config");
$bctype = (empty($config['barcode_type'])) ? "ean13" : strtolower($config['barcode_type']);
$cache  = (empty($config['barcode_caching'])) ? 1 : (int) $config['barcode_caching'];



$ordine = $db->get1row("data_ordini", "WHERE id = '".$ord."'".$qry_where);

if(!$ordine){
	$_SESSION['error_title'] =  $_t->get("order-no-exist-title");
	$_SESSION['error_message'] =  $_t->get("order-no-exist");
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL."?pid=71&v=html");	
	exit();	
}

$nome_ordine = str_pad($ordine['progressivo'], 4, '0', STR_PAD_LEFT)."/".substr($ordine['anno'],2,2);
$dto = new DateTime($ordine['data']);
$data_ordine = $dto->format("d/m/Y");

$where = ( ($ordine['stato'] == 'evaso' or $ordine['stato'] == 'fatturato') and $ordine['salvato'] == '1') ? "spedito" : "qta";

$prod_qry = "
SELECT 
	p.id AS 'pid', 
    p.sku AS cod_art, 
    v.nome AS variante, 
    (CASE WHEN v.nome IS NULL THEN CONCAT(p.sku, '/-/-') ELSE CONCAT(p.sku, '/', v.nome, '/-') END ) AS barcode,
    m.file AS immagine 
FROM `data_ordini_dettagli` AS o 
JOIN data_prodotti AS p ON (o.articolo = p.id) 
LEFT JOIN data_varianti AS v ON (v.id = o.variante) 
LEFT JOIN media AS m ON (m.record = p.id AND m.page = '15')
WHERE o.ordine = '".$ord."' AND o.".$where." > 0
";


$prodotti = $db->fetch_array($prod_qry);



$tables = array();
$nprod = $nbcode = 0;

$thumb_size = '90'; // in pixels both height and width
$barcode_cols_varianti = 3; // from 1 to 12
$barcode_cols_no_varianti = 1; // from 1 to 12

// categories with no variants
$no_var_cat = array(4);
$tab_cols_no_variant = 2;

$p = 0;
$cols = 0;

if($prodotti){
	
	$table  = "<table class='table table-bordered table-sm'></li>";
	$table .= "<thead></li>";
	$table .= "<tr><th colspan='2'><h3>Barcode ordine n. ".$nome_ordine." del ".$data_ordine."</h3></th></tr></li>";
	$table .= "</thead></li>";
	$table .= "<tbody></li>";
	
	
	foreach($prodotti as $row){
		
		if($p != $row['pid'] ){
			
			/* PRIMA COLONNA CON FOTO */
			
			$media = $db->get1value("file", DBTABLE_MEDIA, "WHERE page = '15' AND record = '".$row['pid']."' AND `order` = '1'");
			$src = ($media) ? "../photo/".$media : "../images/dummy_image.png";
			$img = SITEROOT."required/img.php?file=".$src."&c=1&p=0&w=".$thumb_size."&h=".$thumb_size."&u=1&q=60&cache=1";
			
			// Se non è il primo articolo dell'ordine chiudo riga precedente
			if(!empty($p)){
				
				$table .= "</div>\n"; // chiudo div row
				$table .= "</td>"; // chiudo cella tabella
				$table .= "</tr>\n"; // chiudo riga tabella
			} 
			
			// apro nuova riga tabella
			$table .= "<tr>\n";
			
			// apro cella e piazzo immagine + codice articolo
			$table .= "<td align='center'>"; // apro cella
			$table .= "<img src='".$img."'><br>"; // metto miniatura
			$table .= "<strong>".$row['cod_art']."</strong>"; // scrivo codice articolo
			$table .= "</td>"; // chiudo cella
			
			// apro cella per barcodes
			$table .= "<td>";
			
			$p = $row['pid']; // setto variabile di check  cambio articolo
			$cols = 0; // imposto numero colonne barcode già scitte a zero
			$number_of_cols = ($row['variante'] != null) ? 3 : 4; // in base al fatto se l'articolo ha varianti o meno vario num colonne
			
		}
		/* SECONDA COLONNA CON BARCODES */
		
		// se non ho ancora outputato barcode apro riga
		if($cols == 0) $table .= "<div class='row no-gutters'>";

		// aggiungo barcode con wrapper div.col-*
		$table .= barcode($row['barcode'], $row['barcode'], $number_of_cols, "code128");
		
		// aumento numero di colonne scritte di 1
		$cols++;
		
		// se ho superato il numero di colonne per riga chiudo div.row e resetto cols a 0 pronto per una nuova riga
		if($cols >= $number_of_cols){
			$table .= "</div>";// chiudo div.row
			$cols = 0;
		}
		
	} // end foreach
	
	$table .= "</div>\n";
	$table .= "</td>";
	$table .= "</tr>\n";
	$table .= "</tbody>\n";
	$table .= "</table>\n";
	
}
	
// genera html per mostrare barcode
function barcode($code, $didascalia, $cols = '6', $bctype = "ean13", $cache = false){
	
	// num di barcode generati
	global $nbcode;
	$nbcode++;
	
	$code = urlencode($code);
	
	$size = (strtolower($bctype) == "ean13") ? 6 : 75;
	$cache = ($cache === true) ? 1 : 0;
	
	$cols = (int) $cols;
	if($cols > 12 or $cols < 1) $cols = 6; // max n di colonne è 6
	
	$colclass = round(12/$cols);
	
	$div  = "<div class='text-center col-md-".$colclass."'>\n";
	//$div  = "<div class='text-center my-1' style='width: 25%;'>\n";
	$div .= "<div class='p-1'>\n";
	
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
		if($table){
			//echo implode("<br>\n", $tables);
			echo $table;
		}
		?>
		
	</div>	
	
	<div class="modal fade" id="print-instructions" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" >Modal title</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  <div class="modal-body">
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-primary" id='close-modal' data-dismiss="modal">Chiudi e ricordamelo la prossima volta</button>
			<button type="button" class="btn btn-danger" id="close-cookie">Chiudi e non mostrare più</button>
			<button type="button" class="btn btn-success" id="stampa"><i class="fas fa-print mr-2"></i>Stampa</button>
		  </div>
		</div>
	  </div>
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
				
				var cookie = getCookie('dontshowprintinstructions');
				
				if(cookie){
					window.print();
					return false;
				}
				
				var nav = navigator.sayswho;
				var be = nav.split(" ");
				var browser = be[0].toLowerCase();
				
				var openInst = (browser == "firefox") ? "Menu del browser > Stampa" : "CTRL + p";
				var help = "Per un risultato di stampa ottimale andare nelle impostazioni di stampa del browser (<em>"+openInst+"</em>) ed impostare:<br>";
				
				switch(browser){
					case "chrome":
						help += "<ul><li>Margini: minimi</li><li>Nessuna intestazione pagina</li></ul>";
						break;
					case "firefox":
						help += "<ul>";
						help += "<li>Spuntare la voce 'Adatta alla larghezza del foglio'</li>";
						help += "<li>Impostare tutti i margini a 1</li>";
						help += "<li>Impostare tutti i parametri di intestazione e pié di pagina su'--vuoto--'</li>";
						help += "</ul>";
						break;
					case "edge":
						help += "<ul>";
						help += "<li>Scala : 'Riduci e adatta'</li>";
						help += "<li>Margini: stretti</li>";
						help += "<li>Intestazione e pié di pagina: 'Disattivati'</li>";
						help += "</ul>";
						break;
					case "safari":
						help += "<ul>";
						help += "<li>Spuntare la voce 'Adatta alla larghezza del foglio'</li>";
						help += "<li>Impostare tutti i margini a 1</li>";
						help += "<li>Impostare tutti i parametri di intestazione e pié di pagina su '--vuoto--'</li>";
						help += "</ul>";
						break;
					case "ie":
						help = "Abbiamo rilevato che come browser state utilizzando Internet Explorer<br>";
						help += "Questo browser usa una tecnologia datata che non permette un ottimale utilizzo di questo modulo.</li>";
						help += "<br>Si è pregati di utilizzare un browser più moderno come <a title='Scarica Chrome' href='https://www.google.com/chrome/' target='_blank'>Chrome</a> o <a title='Scarica Firefox' href='https://www.mozilla.org/it/firefox/new/' target='_blank'>Firefox</a>.";
						break;
					case "opera":
						help += "<ul>";
						help += "<li>Spuntare la voce 'Adatta alla larghezza del foglio'</li>";
						help += "<li>Impostare tutti i margini a 1</li>";
						help += "<li>Impostare tutti i parametri di intestazione e pié di pagina su'--vuoto--'</li>";
						help += "</ul>";
						break;
					default:
						help = "Il tuo browser ("+be[0]+") non è stato riconosciuto.<br>";
						help += "Per un risultato di stampa ottimale consigliamo l'utilizzo di <a title='Scarica Chrome' href='https://www.google.com/chrome/' target='_blank'>Chrome</a> o <a title='Scarica Firefox' href='https://www.mozilla.org/it/firefox/new/' target='_blank'>Firefox</a><br>";
						help += "Se non avete nessuno di questi installati sul vostro sistema potete provare con il presente browser, ma non possiamo garantire un risultato ottimale.";
						help += "<ul>";
						help += "- Impostare tutti i parametri di intestazione e pié di pagina su vuoto</li>";
						help += "- Impostare tutti i margini al numero più basso possibile</li>";
						help += "- Attivare un opzione che adatti il contenuto alla larghezza della pagina stampabile o se non presente impostare una percentuale di zoom che dia il miglior risultato.</li>";
						help += "</ul>";
						break;
				}
				modal("Istruzioni per la stampa", help, "print-instructions");
				//window.print(); 
			})
			
			
			
		});
		
		$(document).ready(function() {
			
			$(document).on("click", "#stampa", function(){
				$("#close-modal").trigger("click");				
				window.print(); 
			});
			
			$(document).on("click", "#close-cookie", function(){
				setCookie("dontshowprintinstructions", "1", "90");
				$("#close-modal").trigger("click");
				window.print(); 
				
			})
						
		});	
		
		function setCookie(cname, cvalue, exdays) {
			var d = new Date();
			d.setTime(d.getTime() + (exdays*24*60*60*1000));
			var expires = "expires="+ d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}
		
		function getCookie(cname) {
			var name = cname + "=";
			var decodedCookie = decodeURIComponent(document.cookie);
			var ca = decodedCookie.split(';');
			for(var i = 0; i <ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') {
					c = c.substring(1);
				}
				if (c.indexOf(name) == 0) {
					return c.substring(name.length, c.length);
				}
			}
			return "";
		}		
		
		function modal(title, text, modal){
			
			var m = $("#"+modal);
			m.find(".modal-title").text(title);
			m.find(".modal-body").html(text);
			m.modal();
			
		}
		
		navigator.sayswho= (function(){
			var ua= navigator.userAgent, tem, 
			M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
			if(/trident/i.test(M[1])){
				tem=  /\brv[ :]+(\d+)/g.exec(ua) || [];
				return 'IE '+(tem[1] || '');
			}
			if(M[1]=== 'Chrome'){
				tem= ua.match(/\b(OPR|Edge)\/(\d+)/);
				if(tem!= null) return tem.slice(1).join(' ').replace('OPR', 'Opera');
			}
			M= M[2]? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
			if((tem= ua.match(/version\/(\d+)/i))!= null) M.splice(1, 1, tem[1]);
			return M.join(' ');
		})();		
		
	</script>

	
	
</body>
</html>