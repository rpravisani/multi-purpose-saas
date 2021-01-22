<?php
/*****************************************************************************************************
  Allineo i prezzi delle varianti in tab prodotti_varianti - richiamato principalmente dopo salvataggio
  griglia prezzi articolo, ma può ovviamente essere richiamato anche da altri script.
  In base al valore del parametro mode può comportarsi in uno dei seguenti modi:
  - ASIS : Imposta il prezzo di tutte le varianti come il prezzo di listino standard
  - DIFF  : Calcola il delta tra prezzo vecchio e prezzo nuovo e incrementa o diminuisce 
            il prezzo variante in base al delta
  - VARI  : Prende il nuovo prezzo e per ogni variante ricalcola il prezzo in base al valore della %
            definita nel campo variazione_prezzo nella tabella varianti
  
  ### POST : prezzi (array id tab prodotti_prezzi => prezzo nuovo), 
             prezzi_vecchi (array id tab prodotti_prezzi => prezzo vecchio), 
			 mode (text)
			 
  ### OUT : $output (json encoded)
 *****************************************************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$_t->setSection("prezzi-articoli");

if(empty($_POST['prezzi'])){
	$output['error'] = $_t->get('no-new-price'); // translation in general section 
	$output['msg'] = $_t->get('no-new-price-message'); // translation in general section 
	echo json_encode($output);
	die();	
}


$mode = $db->make_data_safe($_POST['mode']);
$prezzi = $db->make_data_safe($_POST['prezzi']);
$prezzi_vecchi = $db->make_data_safe($_POST['prezzi_vecchi']);


foreach($prezzi as $id => $prezzo_new){
	$prezzo_new = currency_safe($prezzo_new, $_user->getCurrencyDecimals());
	$prezzo_vecchio = currency_safe($prezzi_vecchi[$id], $_user->getCurrencyDecimals());
	
	$pp = $db->get1row(DBTABLE_PRODOTTI_PREZZI, "WHERE id = '".$id."'");
	
	$listino  = $pp['listino'];
	$articolo = $pp['articolo'];
	$where = " WHERE prodotto = '".$articolo."' AND listino = '".$listino."'";
	
	
	switch($mode){
		case "DIFF":
			// ricalcolo il prezzo variante in base al delta tra nuovo e vecchio prezzo listino
			$delta = $prezzo_new - $prezzo_vecchio;
			$qry = "UPDATE ".DBTABLE_PRODOTTI_VARIANTI." SET prezzo = prezzo + ".$delta.$where;
			$db->execute_query($qry);
			break;
		case "VARI":
			// recupero l'elenco di tutt le varianti attribuite all'articolo, la variazione nominale in % e il corrispettivo
			// id nella tabella (filtrato per listino)
			$qry = "SELECT p.id, p.variante, v.variazione_prezzo 
					FROM ".DBTABLE_PRODOTTI_VARIANTI." AS p 
					JOIN ".DBTABLE_VARIAZIONI." AS v ON (p.variante = v.id)
					WHERE p.prodotto = '".$articolo."' AND p.listino = '".$listino."'";
			
			$var = $db->fetch_array($qry);
			
			if($var){
				
				foreach($var as $idpv => $row){
					$prezzo = $prezzo_new + (($prezzo_new/100)*$row['variazione_prezzo']);
					$db->update(DBTABLE_PRODOTTI_VARIANTI, array("prezzo" => $prezzo), "WHERE id = '".$row['id']."'");
				}
				
			}else{
				// non ho varianti...
			}
				
			break;
		default:
			// forzo le varianti ad avere lo stesso prezzo del prezzo listino standard
			$db->update(DBTABLE_PRODOTTI_VARIANTI, array("prezzo" => $prezzo_new), $where);
			break;
	}
	
}


$output['result'] = true;
$output['error'] = $_t->get('prices-updated'); // title of modal box
$output['errorlevel'] = "success"; // color of modal box	
$output['msg'] = $_t->get('prices-updated-message'); // translation in page specific translation

echo json_encode($output);


	
?>
