<?php
/*******************************************************
 * CUSTOM METHODS ONLY RELATIVE TO THIS PROJECT.       *
 * WILL BE LOADED BY required.php IF THIS FILES EXISTS *
 *******************************************************/

class projectHelper{
	
	public $tipi_colli = array();
	private $totale_tabella_pesi = array();
	private $default_cat_id = '1'; // categorie merceologica di default che non potrà essere cancellata, modificata o disattivata
	
	// passando id ordine conto le quantità in dettagli orinde e aggiorno dato consolidato in tabella ordini
	public function updateTotQtaOrdine($ordine){
		
		global $db;
		
		$ordine = (int) $ordine;
		if(empty($ordine)) return false;
		
		$check = $db->get1row("data_ordini", "WHERE id = '".$ordine."'");
		if(!$check) return false;
		
		$totals = (float) $db->sum_column( "ordinato", "data_ordini_dettagli", "ordine = '".$ordine."'" );

		if($db->update("data_ordini", array("totale_pezzi" => $totals), "WHERE id = '".$ordine."'")){
			return true;
		}else{
			return false;
		}
		
	}
	
	
	// restituisce riga tabella (<tr>) con dettagli dell'ordine
	public function getOrderDetailRow($prodotto = array(), $row = 0, $qta = 1 , $disabled = false, $email = false, $tr = true){
		
		global $_user;
				
		$costo_riga = (empty($prodotto['costo_riga'])) ? $prodotto['costo_unit']*$qta : $prodotto['costo_riga'];
		
		$qta = $_user->number_format($qta, 2);
		$costo_unit = $_user->number_format($prodotto['costo_unit'], 2);
		$costo_riga = $_user->number_format($costo_riga, 2);
		
		$html = "";
		if($tr) $html .= "<tr data-row='".$row."'>\n";
		$html .= "<td>".$prodotto['codice']."</td>\n";
		$html .= "<td>".$prodotto['descrizione']."</td>\n";
		$html .= "<td class='modificabile um' title='Click per cambiare'><span>".$prodotto['um']."</span></td>\n";
		$html .= "<td class='modificabile qta_ordinato' title='Click per cambiare'>".$qta."</td>\n";
		$html .= "<td class='modificabile costo_unit'>€ ".$costo_unit."</td>\n";
		$html .= "<td class='costo_riga'>€ ".$costo_riga."</td>\n";
		if(!$disabled) $html .= "<td class='hide-col pulsanti' align='center'><i data-id='".$row."' class='fa fa-trash text-danger removerow'></i></td>\n";
		if(!$email) $html .= "<td class='print-only'>&nbsp;</td>\n";
		if($tr) $html .= "</tr>\n";
		
		return $html;
	}
	
	// restituisce riga tabella (<tr>) con dettagli dell'ordine
	public function getOrderPesiRow($riga){
		
		global $_user;
		
		$kg_ordinato = $_user->number_format($riga['ordinato'], 2);
		$peso_lordo = (empty($riga['peso_lordo'])) ? 0 : $_user->number_format($riga['peso_lordo'], 2);
		$peso_netto = (empty($riga['peso_netto'])) ? 0 : $_user->number_format($riga['peso_netto'], 2);
		$ncolli = (empty($riga['ncolli'])) ? 0 : $_user->number_format($riga['ncolli'], 0);		
		$tara = (empty($riga['tara'])) ? 0 : $_user->number_format($riga['tara'], 2);
		
		
		$costo_kg = (float) $riga['costo_unit'];
		$costo_kg_form = $_user->number_format($costo_kg, 2);
		
		$prezzo_riga = (empty($riga['peso_netto'])) ? $costo_kg * $kg_ordinato : $costo_kg * $riga['peso_netto'];
		$prezzo_riga = $_user->number_format($prezzo_riga, 2);
		
		if(empty($this->tipi_colli)){
			$this->tipi_colli = $this->get_tipi_colli();
		}
		
		$select_collo = "<select class='input_pesi tipo_collo'>\n";
		$select_collo .= "<option data-tara='0' value='0'>--</option>\n";
		foreach($this->tipi_colli as $tipo_collo){
			$selected = ($tipo_collo['id'] == $riga['tipo_collo']) ? "selected" : "";
			$select_collo .= "<option data-tara='".$tipo_collo['tara']."' ".$selected." value='".$tipo_collo['id']."'>".$tipo_collo['nome']." (tara ".$tipo_collo['tara']." Kg.)</option>\n";
		}
		$select_collo .= "</selected>\n";
		
		// inputs
		$input_peso_lordo = "<input type='text' class='input_pesi peso_lordo' value='".$peso_lordo."'>";
		$input_ncolli = "<input type='text' class='input_pesi ncolli' value='".$ncolli."'>";		
		$input_costo = "<input type='text' class='input_pesi costo' value='".$costo_kg_form."'>";		
		
		
		$html  = "<tr data-row='".$riga['id']."'>\n";
		$html .= "<td>".$riga['descrizione']."</td>\n";
		$html .= "<td align='right'><span>".$kg_ordinato."</span> Kg.</td>\n";
		$html .= "<td align='right'>".$input_peso_lordo." kg.</td>\n";
		$html .= "<td align='center'>".$input_ncolli."</td>\n";
		$html .= "<td align='center'>".$select_collo."</td>\n";
		$html .= "<td class='peso_netto' align='right'><span>".$peso_netto."</span> Kg.</td>\n";
		$html .= "<td class='costo_kg' align='right'>€ ".$input_costo."</td>\n";
		$html .= "<td class='prezzo_riga' align='right'>€ <span>".$prezzo_riga."</span></td>\n";
		$html .= "</tr>\n";
		
		return $html;
	}
	
	public function getOrderPesiTotal($order, $table = false){
		
		global $db, $_user;
		
		$order = (int) $order;
		if(empty($order)) return "";
		
		$qry = "
			SELECT 
			SUM(ordinato) AS kg_ordinato, 
			SUM(peso_lordo) AS peso_lordo, 
			SUM(peso_netto) AS peso_netto, 
			SUM(ncolli) AS ncolli, 
			SUM(tara*ncolli) AS tara, 
			SUM(costo_unit) AS costo_kg 
			FROM `data_ordini_dettagli` 
			WHERE ordine = '".$order."'
		";
		
		$totals = $db->fetch_array_row($qry);
		
		$totals['prezzo_totale'] = (empty($totals['peso_netto'])) ? $totals['costo_kg'] * $totals['kg_ordinato'] : $totals['costo_kg'] * $totals['peso_netto'];
		
		if($table){
			
			$prezzo_totale = $_user->number_format($totals['prezzo_totale'], 2);

			$kg_ordinato = (empty($totals['kg_ordinato'])) ? 0 : $_user->number_format($totals['kg_ordinato'], 2);
			$peso_lordo  = (empty($totals['peso_lordo']))  ? 0 : $_user->number_format($totals['peso_lordo'], 2);
			$peso_netto  = (empty($totals['peso_netto']))  ? 0 : $_user->number_format($totals['peso_netto'], 2);
			$ncolli 	 = (empty($totals['ncolli'])) 	   ? 0 : $_user->number_format($totals['ncolli'], 0);		
			$tara 		 = (empty($totals['tara'])) 	   ? 0 : $_user->number_format($totals['tara'], 2);
			$costo_kg	 = (empty($totals['costo_kg']))    ? 0 : $_user->number_format($totals['costo_kg'], 2);


			$html  = "<tr>\n";
			$html .= "<th class='text-left'>TOTALE</td>\n";
			$html .= "<th class='text-right'><span id='totale_kg_ordinato'>".$kg_ordinato."</span> Kg.</th>\n";
			$html .= "<th class='text-right'><span id='totale_peso_lordo'>".$peso_lordo."</span> kg.</th>\n";
			$html .= "<th class='text-center'><span id='totale_ncolli'>".$ncolli."</span></th>\n";
			$html .= "<th class='text-right'><span id='totale_tara'>".$tara."</span> Kg.</th>\n";
			$html .= "<th class='text-right'><span id='totale_peso_netto'>".$peso_netto."</span> Kg.</th>\n";
			$html .= "<th class='text-right'>€ <span id='totale_costo_kg'>".$costo_kg."</span></th>\n";
			$html .= "<th class='text-right'>€ <span id='totale_prezzo'>".$prezzo_totale."</span></th>\n";
			$html .= "</tr>\n";

			return $html;
			
		}else{
			return $totals;
		}
		
		
	}
	
	public function get_tipi_colli(){
		global $db;
		return $db->select_all("data_tipo_colli", "WHERE active = '1' ORDER BY nome");
	}
	
	// restituisci array o html degli options con le unità di misura
	public function getUm($return_options = false, $selected = ''){
		
		$array_um =  array( "KG", "N.", "GR", "PZ", "COLLI", "LT" );
		if(!$return_options) return $array_um;
		
		// return select options
		$options_um = "";
		foreach($array_um as $um){
			$select = ($um == $selected) ? "selected" : "";
			$options_um .= "<option ".$select." value='".$um."'>".$um."</option>";
		}		
		return $options_um;
		
	}
	
	// restituisci array o html degli options con le unità di misura
	public function getIva($return_options = false, $selected = ''){
		
		$array_iva =  array( 4, 5, 10, 22);
		if(!$return_options) return $array_iva;
		
		// return select options
		$options_iva = "";
		foreach($array_iva as $iva){
			$select = ($iva == $selected) ? "selected" : "";
			$options_iva .= "<option ".$select." value='".$iva."'>".$iva."</option>";
		}		
		return $options_iva;
		
	}
	
	// crea blocco options di tutte le categorie merceologiche iniziando on il valore defualt seguito poi dagli altri in ordine alfabetico
	public function getOptionsCategorie($ids=array(), $only_with_products = false, $first_empty = false){
		global $db;
		if(!is_array($ids)) $ids = array($ids);
		
		$selected = (in_array($this->default_cat_id, $ids) or empty($ids)) ?  "selected=\"selected\"" : "";
		
		$default = $db->get1value("nome", "data_categorie", "WHERE id = '".$this->default_cat_id."'");
		
		$options  = ($first_empty) ? "<option ".$selected." value=\"0\">&nbsp;</option>\n" : "";
		$options .= "<option ".$selected." value=\"1\">".$default."</option>\n";
		$options .= "<option disabled>-----</option>\n";
		
		$where_addon = ($only_with_products) ? 'AND id IN (SELECT DISTINCT categoria FROM data_prodotti WHERE id != "'.$this->default_cat_id.'" AND active = "1")' : '';
		
		$kv = $db->key_value("id", "nome", "data_categorie", "WHERE id != '".$this->default_cat_id."' AND active = '1' ".$where_addon." ORDER BY nome");
		if($kv){
			foreach($kv as $key=>$value){
				$selected = (in_array($key, $ids)) ?  "selected=\"selected\"" : "";
				$options .= "<option ".$selected." value=\"".$key."\">".$value."</option>\n";
			}
			//return $options;

		}
		return $options;
	}
	
	
	public function formatOrderNumber($num, $anno){
		$num = (int) $num;
		$num = str_pad($num, 4, '0', STR_PAD_LEFT);
		$anno = (int) $anno;
		$anno = substr($anno, 2, 2);
		
		return $num."/".$anno;
	}
	
	public function formatLabelStatoOrdine($stato){
		$classe = strtolower($stato);
		$classe = str_replace(" ", "-", $classe);
		return "<span class='badge ".$classe." ml-2'>".$stato."</span>";
	} 
	
	// restiuisce il prezzo prodotto così come registrato l'ultimo lunedì oppure ultima data se lunedì è vuota
	public function getPrezzoLunedi($data = "", $prodotto, $quale = ''){
		global $db;
		
		$prezzi = false;
		
		if($quale != 'acquisto' and $quale != 'vendita') $quale = '';
		
		// recupero data lunedì scorso
		$lunedi = $this->getLunedi($data); // se $data è vuoto: oggi
		
		// recupero prezzo articolo così com'era lunedì scorso. Se non fosse stato registrato alcun prezzo $prezzi sarà false e proseguo ricerca sotto
		$prezzi = $this->getPrezzoGiorno($lunedi, $prodotto, $quale);
		
		// niente di lunedì prendiamo la data più recente 
		if(!$prezzi){
			
			// la data più vicina a lunedì tra lunedì e oggi			
			$closest_date = $db->get_min_row("date", "data_prezzi_giorno", "WHERE  prodotto = '".$prodotto."' AND date > '".$lunedi."'");
			if($closest_date){
				// ok ho trovato un a data tra lunedì e oggi
				$prezzi = $this->getPrezzoGiorno($closest_date, $prodotto, $quale);
				$prezzi['datum'] = $closest_date;
				
			}else{
				// nessuna data tra lunedì e oggi prendo ultimo inserimento. Se non trovo nulla $prezzi sarà false
				$max_date = $db->get_max_row("date", "data_prezzi_giorno", "WHERE  prodotto = '".$prodotto."'");
				if($max_date) $prezzi = $this->getPrezzoGiorno($max_date, $prodotto, $quale);
				if($max_date) $prezzi['datum'] = $max_date;
			}
						
		}else{
			 $prezzi['datum'] = $lunedi;
		}
		
		// restituisco array con 
		return $prezzi;
		
	}
	
	// recupero prezzo di un determinato prodotto in una data specifica. Se in tale data ci sono più prezzi (fornitore diverso) calcolo prezzo medio
	public function getPrezzoGiorno($data, $prodotto, $quale = ''){
		global $db;
		
		if($quale != 'acquisto' and $quale != 'vendita') $quale = '';
		
		// recupero tutti i prezzi del prodotto in data. Se in tale data ci sono più prezzi (fornitore diverso) calcolo prezzo medio
		$prezzi = $db->select_all("data_prezzi_giorno", "WHERE prodotto = '".$prodotto."' AND date = '".$data."'");
		if(!$prezzi){
			//return ($quale == '') ? array("acquisto" => 0, "acquisto" => 0) : 0;
			return false;
		}
		
		if(count($prezzi) > 1){
			
			// prezzo medio
			$prezzo_medio_acquisto = $prezzo_medio_vendita = 0;
			foreach($prezzi as $row){
				
				$prezzo_medio_acquisto += $row['prezzo_acquisto'];
				$prezzo_medio_vendita += $row['prezzo_vendita'];
			}
			$prezzo_medio_acquisto = round($prezzo_medio_acquisto / count($prezzi), 2);
			$prezzo_medio_vendita  = round($prezzo_medio_vendita  / count($prezzi), 2);
			
			if(empty($quale)){
				return array("acquisto" => $prezzo_medio_acquisto, "vendita" => $prezzo_medio_vendita, "medio" => true);
			}else{
				return ($quale == 'acquisto') ? $prezzo_medio_acquisto : $prezzo_medio_vendita;
			}
			
			
		}else{
			// prezzo singolo
			return ($quale == '') ? array("acquisto" => $prezzi[0]['prezzo_acquisto'], "vendita" => $prezzi[0]['prezzo_vendita'], "medio" => false) : $prezzi[0]['prezzo_'.$quale];
		}
	}
	
	public function getPrezzoListino($listino = 0, $articolo = ''){
		global $db;
		
		$prezzi = array("acquisto" => 0, "vendita" => 0);
		
		if(!empty($listino and !empty($articolo))) $prezzi['acquisto'] = (float) $db->get1value("prezzo", DBTABLE_LISTINI_PREZZI, "WHERE listino = '".$listino."' AND articolo = '".$articolo."'");		
		
		return $prezzi;
	}
	
	
	// recupero tutti i clienti attivi e restituisco stringa con gli optios o arra co ciave id record
	public function get_clienti($returnme = 'options', $selected = 0){
		
		global $db;
		
		// sanify
		$returnme = $db->make_data_safe($returnme);
		$selected = (int) $selected;
		
		$html = "";
		$list = array();		
		
		$clienti = $db->select_all("data_fornitori", "WHERE active = '1'");

		if(!empty($clienti)){

			if($retrunme == "select") $html .= "<select name='cliente' id='cliente'>\n";

			foreach($clienti as $cliente){

				switch($returnme){
					case "options":
					case "select":
						$s = ($selected == $cliente['id']) ? "selected" : "";
						$html .= "<option ".$s." value='".$cliente['id']."'>".$cliente['Ragione_Sociale']."</option>\n";
						break;
					default:
						$list[$cliente['id']] = $cliente['Ragione_Sociale'];
						break;
				} //end switch

			} // end foreach

			if($retrunme == "select") $html .= "</select>\n";

		}	
		
		return ($returnme == 'options' or $returnme == 'select') ? $html : $list;
		
	}
	
	// IN: data in formato Y-m-d. se non passo nulla prendo data oggi
	// OUT: data del primo lunedì passato in formato Y-m-d
	public function getLunedi($data = ""){
		if(empty($data)) $data = date("Y-m-d");
		$dt = new DateTime($data);
		$oggi = $dt->format("N"); // 1 (for Monday) through 7 (for Sunday)
		$back = $oggi - 1;
		$dt->modify("-".$back." days");
		return $dt->format("Y-m-d");
		
	}
	
	// recupera l'id categoria prodotto default 
	public function getDefaultCatId(){
		return $this->default_cat_id;
	}
}

?>