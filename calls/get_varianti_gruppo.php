<?php
/*****************************************************
 * Recupera le varianti di un gruppo varianti        *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$uselistini = ($_user->getParams('listini') or $_SESSION['login_type'] == "SA") ? true : false; 
$listini = $db->key_value("id", "listino", "data_listini", "ORDER BY id");


// sanitize
$gruppo = (int) $_POST['gruppo'];
$prezzi = $_POST['prezzi'];

if(empty($gruppo)){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = ""; 
	$output['html'] = ""; 
	echo json_encode($output);
	die();
}

// prima rigatabella con puls tutti/nessuno
$tab_varianti = "<tbody><tr><td colspan='2'><small>Seleziona <span id='select_all' class='text-blue' >Tutti</span> | <span id='select_none' class='text-blue' >Nessuno</span></td>\n";

// se posso usare listini aggungo il loro nome all'intesta della tabella (tutti i listini attivi)
if($uselistini){
	foreach($listini as $idlistino => $nome_listino){
		$tab_varianti .= "<td colspan='2'><strong>".$nome_listino."</strong></td>";
	}
}
// chiudo riga
$tab_varianti .= "</tr>\n";

// recupero tutti gli id delle varianti attive del gruppo selezionato 
$qry_varianti = "
SELECT x.variante 
FROM ".DBTABLE_VARIAZIONI_X_GRUPPI." AS x 
JOIN ".DBTABLE_VARIAZIONI." AS v ON (v.id = x.variante)
WHERE x.gruppo = ".$gruppo." 
AND v.active = '1'
";

$varianti_gruppo_attivi = $db->fetch_array($qry_varianti);
// creo array 1-dim con il solo valore della colonna variante
$varianti_gruppo = array_column($varianti_gruppo_attivi, 'variante');

// se non ne ho trovati: fallback su array vuoto
if(!is_array($varianti_gruppo)) $varianti_gruppo = array();
//appiatisco array per query
$varianti_gruppo_flat = implode(",", $varianti_gruppo);

// recupero tutte le info di tutte le varianti del gruppo
$varianti_all = $db->select_all(DBTABLE_VARIAZIONI, "WHERE id IN (".$varianti_gruppo_flat.")");

if($varianti_all){
	foreach($varianti_all as $vrow){
		// non c'è prezzo quindi non attribuita a prodotto
		$checked = ""; //checkbox not checked
		$prezzo = ""; // prezzo variante
		$disabled = ""; // disabled input
		$prezzo_diff = ""; // nessun prezzo differenza
		$inputTitle = "";
		// se ho una variazione prezzo per la variante imposto titolo del input "prezzo"
		if($vrow['variazione_prezzo'] != 0){
			$sign = ($vrow['variazione_prezzo'] < 0) ? "" : "+";
			$perc = $sign.number_format($vrow['variazione_prezzo'], 2, ",", ".")."%";
			$inputTitle = " title='".$perc."'";
		}
		
		// creo checkbox	    
		$checkbox = $bootstrap->checkbox("check_variante[".$vrow['id']."]", // nome con id della tab varianti 
										 false, // non spuntata
										 "", // nessun didascalia
										 false, // didascalia non precede checkbox
										 false, // Non disabilitato
										 false, // id non impostato
										 "activate-variante" // classe
										);
		
		// se attivi aggiungo i listini
		if($uselistini){
			$inputs = "";
			
			if($listini){
				foreach($listini as $idlistino => $listino){
					$input = createInput($vrow['id']."_".$idlistino, $prezzo, $prezzi[$idlistino], $idlistino, $inputTitle, $disabled, $vrow['variazione_prezzo']);
					$inputs .= "
						<td class='vinput'>".$input."</td>\n
						<td class='vdiff' data-listino='".$idlistino."'>".$prezzo_diff."</td>\n
						";
					
				}
			} // end if listini
			
		}else{
			$input = createInput($vrow['id'], $prezzo, $prezzi[0], 0, $inputTitle, $disabled, $vrow['variazione_prezzo']);
			$inputs = "
						<td class='vinput'>".$input."</td>\n
						<td class='vdiff' data-listino='0'>".$prezzo_diff."</td>\n
						";
		}
		
		
		$tab_varianti .= "
		<tr>\n
		<td class='vchk'>".$checkbox."</td>\n
		<td class='vname'>".$vrow['nome']."</td>\n".$inputs."
		</tr>\n";

	} // end foreach varianti

} // end if varianti_all
$tab_varianti .= "</tbody>\n";


$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";	
$output['html'] = $tab_varianti;	

echo json_encode($output);


function createInput($id, $prezzo_variante, $prezzo_prodotto, $listino, $title, $disabled, $variazione_prezzo){
	return "
			<div class=\"input-group\">
				<input name='prezzo_variante[".$id."]' ".$title." data-plus='".$variazione_prezzo."' data-listino='".$listino."' data-prezzo-prodotto='".$prezzo_prodotto."' data-prezzo-old='".$prezzo_variante."' ".$disabled." class=\"form-control varprice currency\" type=\"text\" value=\"".$prezzo_variante."\">
				<span class=\"input-group-addon\">€</span>
			</div>\n
			";	
}

?>
