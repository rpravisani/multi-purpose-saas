<?php
/*******************************************************
 * CUSTOM METHODS ONLY RELATIVE TO THIS PROJECT.       *
 * WILL BE LOADED BY required.php IF THIS FILES EXISTS *
 *******************************************************/

class projectHelper{
		
	/********************************************** 
	 ***             PUBLIC METHODS             ***	
	 **********************************************/ 
	
	/*** MILITI ***/
	
	// restituisce solamente gli autisti attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità
	public function getAutisti($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, true, false, false, false, true, $options, $selected, $usciti, $exclude, $stato );
	}
	
	// restituisce solamente i caposquadra attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità
	public function getCapisquadra($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, false, true, false, false, true, $options, $selected, $usciti, $exclude, $stato );
	}
	
	// restituisce solamente i monitori attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità 
	public function getMonitori($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, false, false, true, false, true, $options, $selected, $usciti, $exclude, $stato );
	}
	
	// restituisce solamente i servizi civili attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità 
	public function getServiziCivili($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, false, false, false, true, true, $options, $selected, $usciti, $exclude, $stato );
	}
	
	// restituisce solamente i militi che non escono attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità 
	public function getNoEsci($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array() ){
		return $this->militi($data, $fascia, false, false, false, false, false, $options, $selected, $usciti, $exclude );
	}

	// restituisce i militi semplici (che non fanno alcun flag) attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità 
	public function getMiliti($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, false, false, false, false, true, $options, $selected, $usciti, $exclude, $stato );
	}
	
	// restituisce tutti i militi attivi (switch se mostrare anche assenti) exclude solo per options, stato = stato disponibilità 
	public function getAllMiliti($options = true, $selected = "", $data = false, $fascia = false, $usciti = true, $exclude = array(), $stato = false ){
		return $this->militi($data, $fascia, null, null, null, null, true, $options, $selected, $usciti, $exclude, $stato );
	}


	/* SQUADRE */
	// restituisce array con i militi di una squadra urgenze in base a data (yyyy-mm-dd) e fascia (ddaa)
	public function getSquadra($data = false, $fascia = false, $includi_usciti = false){
		global $db;
		if($includi_usciti){
			$ua = $uc = $um1 = $um2 = "";
		}else{
			$ua = " AND a.uscito = '0'";
			$uc = " AND c.uscito = '0'";
			$um1 = " AND m1.uscito = '0'";
			$um2 = " AND m2.uscito = '0'";
			
		}
		$qry = "
			SELECT 
			s.id, 
			a.id AS autista, 
			CONCAT(a.cognome, ' ', a.nome) AS autista_nome, 
			c.id AS caposquadra, 
			CONCAT(c.cognome, ' ', c.nome) AS caposquadra_nome, 
			m1.id AS milite1, 
			CONCAT(m1.cognome, ' ', m1.nome) AS milite1_nome, 
			m2.id AS milite2, 			
			CONCAT(m2.cognome, ' ', m2.nome) AS milite2_nome
			FROM 
            ".DBTABLE_SQUADRE_AGENDA." AS s 
			LEFT JOIN ".DBTABLE_MILITI." AS a ON (a.id = s.autista".$ua.") 
			LEFT JOIN ".DBTABLE_MILITI." AS c ON (c.id = s.caposquadra".$uc.") 
			LEFT JOIN ".DBTABLE_MILITI." AS m1 ON (m1.id = s.milite1".$um1.") 
			LEFT JOIN ".DBTABLE_MILITI." AS m2 ON (m2.id = s.milite2".$um2.") 
			WHERE 
			 s.data = '".$data."' 
			AND s.fascia = '".$fascia."'		
		";
		$result = $db->fetch_array_row($qry);;
		
		return $result;
	}
	
	
	
	/* PAZIENTI */
	
	// restituisce solo i pazienti attivi che non sono sociali o privati
	public function getPazienti($options = true, $selected = "", $assenti = false){
		return $this->pazienti(false, false, $options, $selected, $assenti);
	}
	
	// restituisce solo i pazienti attivi che sono sociali
	public function getSociali($options = true, $selected = "", $assenti = false){
		return $this->pazienti(true, false, $options, $selected, $assenti);
	}

	// restituisce solo i pazienti attivi che sono privati
	public function getPrivati($options = true, $selected = "", $assenti = false){
		return $this->pazienti(false, true, $options, $selected, $assenti);
	}

	// restituisce tutti i pazienti attivi
	public function getAllPazienti($options = true, $selected = "", $assenti = false){
		return $this->pazienti(null, null, $options, $selected, $assenti);
	}


	/* STRUTTURE & REPARTI */
	
	// restituisce solo le strutture ospedaliere attive
	public function getOspediali($options = true, $selected = ""){
		return $this->strutture(true, $options, $selected);
	}
	
	// restituisce solo le strutture non ospedaliere attive
	public function getStrutture($options = true, $selected = ""){
		return $this->strutture(false, $options, $selected);
	}

	// restituisce tutti le strutture attiva
	public function getAllStrutture($options = true, $selected = ""){
		return $this->strutture(null, $options, $selected);
	}

	// restituisce tutti i reparti attivi
	public function getReparti($options = true, $selected = ""){
		return $this->reparti($options, $selected);
	}


	/* AUTOMEZZI */
	
	// restituisce tutti gli automezzi attivi
	public function getAutomezzi($options = true, $selected = "", $assenti = false){
		return $this->automezzi(null, $options, $selected, $assenti);
	}

	// restituisce solo gli automezzi per strade strette attivi
	public function getAutomezziStretti($options = true, $selected = "", $assenti = false){
		return $this->automezzi(true, $options, $selected, $assenti);
	}

	// restituisce solo gli automezzi per strade larghe attivi
	public function getAutomezziLarghi($options = true, $selected = "", $assenti = false){
		return $this->automezzi(false, $options, $selected, $assenti);
	}


	/* FASCE */
	// restituisce  array con le fasce in base all'abbreviazione del giorno
	public function getfasceGiorno($giorno, $which = false, $format = false){
		global $gg_settimana;
		$giorno = (string) strtolower($giorno);
		if(!in_array($giorno, $gg_settimana)) return false;
		$giorni = array($giorno);	
		$out = $this->fasce($giorni, $which, $format);
		return $out[$giorno];
	}
	
	// restituisce la fascia in base all'orario (epoch) passato, se $id è true resistuisce id invece che fascia, in tal caso $format viene ignorato
	public function getfasciaOrario($time = false, $id = false, $format = false){
		if(!$time) $time = time();
		$giorno = strtolower(strftime("%a", $time));
		$giorni = array($giorno);
		$hr = date("H", $time);
		$result = $this->fasce($giorni, $hr, $format);	
		$fascia = $result[$giorno];
		if($id){
			$out = array_keys($fascia);
			$out = $out[0];
		}else{
			$out = reset($fascia);
			
		}
		return $out;
	}
	
	public function getfasceFeriali($which = false, $format = false){
		$giorni = array("lun", "mar", "mer", "gio", "ven");
		return $this->fasce($giorni, $which, $format);
	}
	
	public function getfasceFestivi($which = false, $format = false){
		$giorni = array("sab", "dom");		
		return $this->fasce($giorni, $which, $format);
	}

	public function getfasce($which = false, $format = false){
		$giorni = array();
		return $this->fasce($giorni, $which, $format);
	}

	public function getOptionsfasceFestivi($which = false, $record = false){
		$options = "<option></option>\n";
		$fasce = $this->getfasceFestivi($which, true);
		if($fasce){
			$oldday = "";
			foreach($fasce as $giorno=>$order){
				foreach($order as $id=> $fascia){
					if($oldday != "" AND $oldday != $giorno) $options .= "<option disabled>─────</option>";
					$oldday = $giorno;
					$select = ($id == $record) ? "selected" : "";
					$options .= "<option ".$select." value='".$id."'>".strtoupper($giorno)." ".$fascia."</option>\n";
				}
			}
		}
		return $options;
	}
	
	public function getOptionsfasceFeriali($which = false, $record = false){
		$options = "<option></option>\n";
		$fasce = $this->getfasceFeriali($which, true);
		if($fasce){
			$oldday = "";
			foreach($fasce as $giorno=>$order){
				foreach($order as $id=> $fascia){
					if($oldday != "" AND $oldday != $giorno) $options .= "<option disabled>─────</option>";
					$oldday = $giorno;
					$select = ($id == $record) ? "selected" : "";
					$options .= "<option ".$select." value='".$id."'>".strtoupper($giorno)." ".$fascia."</option>\n";
				}
			}
		}
		return $options;
	}

	public function getOptionsfasceAll($which = false, $record = false){
		$options = "<option></option>\n";
		$fasce = $this->getfasce($which, true);
		if($fasce){
			$oldday = "";
			foreach($fasce as $giorno=>$order){
				foreach($order as $id=> $fascia){
					if($oldday != "" AND $oldday != $giorno) $options .= "<option disabled>─────</option>";
					$oldday = $giorno;
					$select = ($id == $record) ? "selected" : "";
					$options .= "<option ".$select." value='".$id."'>".strtoupper($giorno)." ".$fascia."</option>\n";
				}
			}
		}
		return $options;
	}

	// Restituisce string con le options con le fasce di un specifico giorno (abbrev.)
	
	public function getOptionsfasceGiorno($giorno = false, $record = false){
		$options = "<option></option>\n";
		if($giorno){
			$fasce = $this->getfasceGiorno($giorno, false, true);
			if($fasce){
				foreach($fasce as $id=> $fascia){
					$select = ($id == $record) ? "selected" : "";
					$options .= "<option ".$select." value='".$id."'>".$fascia."</option>\n";
				}
			}			
		}
		return $options;
	}

	/* ALTRI / VARIE */
	public function getLegendaMiliti(){
		$out = "
        	<div id=\"legenda\">
				<em>
					Legenda: <span class=\"label autista\">Autista</span>
					<span class=\"label caposquadra\">Caposquadra</span>
					<span class=\"label monitore\" >Monitore</span>
					<span class=\"label milite\">Milite</span>
					<span class=\"label servizio-civile\">Servizio Civile</span>
				</em>
			</div>
			";
			return $out;
	}
	
	public function weekday($date){
		// date is epoch
		if(!preg_match("/^1\d{9}$/", $date)) return $date;
		return strtolower(strftime("%a", $date));
	}

	public function getTipiMiliti(){
		return array("autista", "caposquadra", "milite0", "milite1", "milite2");
	}

	// restituisce array con le festività di un certo anno (questo serve esclusivamente per pasqua) se formatted come d-m se no md
	public function getFestivita($year = false, $formatted = false){
		// se non è stato passato un anno prendo anno attuale
		if(!$year or !preg_match('/^(19|20)\d{2}$/', $year)) $year = date("Y", time());
		// formato d-m
		$feste_italiane_form = array("01-01", "06-01", "25-04", "01-05", "02-06", "15-08", "01-11", "08-12", "25-12", "26-12");
		// formato md
		$feste_italiane_nonform = array("0101", "0106", "0425", "0501", "0602", "0815", "1101", "1208", "1225", "1226");
		// in base a $formatted attribuisco un array o l'altra
		$feste_italiane = ($formatted) ? $feste_italiane_form : $feste_italiane_nonform;
		// recupero pasqua e pasquetta
		$pasqua = easter_date( $year );
		$pasquetta = $pasqua + (60*60*24);
		// formatto pasqua e pasquetta in base a $formatted
		$format = ($formatted) ? "d-m" : "md";
		// aggiungo pasqua e pasquetta ad array feste_italiane
		$feste_italiane[] = date($format, $pasqua);
		$feste_italiane[] = date($format, $pasquetta);
		// se non formattato riordino in modo crescente array
		if(!$formatted) sort($feste_italiane);
		
		return $feste_italiane;
	}
	
	// dice se $data (epoch o yyyy-mm-dd) è festività (true) o meno (false)
	public function isFesta($date){
		if(preg_match("/^2\d{3}-[0-1]\d-[0-3]\d$/", $date)){
			$date = strtotime($date);
		}
		$form = date("md", $date);
		$year = date("Y", $date);
		$feste = $this->getFestivita($year);
		return (in_array($form, $feste)) ? true : false;
	}
	


	/*********************************************** 
	 ***             PRIVATE METHODS             ***	
	 ***********************************************/ 

	// Funzione che restitusce una array o una stringa con le options con i militi
	// default: tutti i militi attivi che escono restituiti come options
	// $data in formato yyyy-mm-dd o XXX - $fascia nel formato ddaa, data nel
	private function militi($data = false, $fascia = false, $autista = null, $caposquadra = null, $monitori = null, $servizio_civile = null, $esce = null, $options = true, $selected = "", $includi_usciti = true, $exclude = array(), $stato = false ){
		global $db, $gg_settimana;

		/*** solo quelli disponibili (solo se $data non è false) ***/
		if(!empty($data)){
			
			// $data può essere sia una data in formato yyyy-mm-dd, sia l'abbreviazione del giorno della settimana (p.e. lun)
			
			if(preg_match("/^2\d{3}-[0-1]\d-[0-3]\d$/", $data)){
				// è una data yyyy-mm-dd quindi recupero epoch e poi recupero giorno
				$ts = strtotime($data);
				$giorno = ($this->isFesta($data)) ? "dom" : strtolower(strftime("%a", $ts)); // p.e. "lun"
				
			}else{
				// in alternativa vedo se è un'abbreviazione di giorno, se no false
				$giorno = strtolower($data);
				$giorno = ( in_array($giorno, $gg_settimana) ) ? $data : false;
				$data = false; // non ho a disposizione una data precisa quindi ignoro tabella assenze
			}
		}else{
			$giorno = false;
		}
		
		// verifico se ho vincolo dai giorno o data
		if($giorno){
			// Ho aggiunto le colonna m.autista, m.caposquadra, m.monitore, d.fascia, ma per ora NON vengono usate
			// fascia ora è optional. se non c'è estrapola tutti i militi del giorno / data.
			$qry = "
			SELECT m.id, CONCAT(m.cognome, ' ', m.nome) AS nomemilite, m.autista, m.caposquadra, m.monitore, d.fascia 
			FROM ".DBTABLE_MILITI." AS m,  
			".DBTABLE_DISPONIBILITA_MILITE." AS d 
			WHERE d.milite = m.id AND d.giorno = '".$giorno."' ";
			$qry .= (empty($fascia)) ? "" : "AND d.fascia = '".$fascia."' ";
			$qry .= (!$stato) ? "AND stato != 'assente' " : "AND stato = '".$stato."'";
			$qry .= "AND m.active = '1'";
			
			// è stata passata una data precisa inserisco sub-query per le assenze. fascia in questo caso è optional.
			if($data){
				$qry .= " AND m.id NOT IN (SELECT milite FROM data_militi_assenze WHERE data = '".$data."' ";
				$qry .= (empty($fascia)) ? "" : "AND fascia = '".$fascia."'";
				$qry .= ")";
			}
			
		}else{
			// Nessun vincolo di dato o giorno
			$qry = "
			SELECT m.id, CONCAT(m.cognome, ' ', m.nome) AS nomemilite FROM ".DBTABLE_MILITI." AS m 
			WHERE m.active = '1'
			";			
		}
		
		if(!$includi_usciti) $qry .= " AND m.uscito = '0'";
		
		
		if(!$options and !empty($exclude)){
			$exclude_flat = implode(",", $exclude);
			$qry .= " AND m.id NOT IN (".$exclude_flat.")";			
		}

		// eventuale limitazione ad un ruolo specifico
		if($autista !== null) $qry .= " AND m.autista = '".(int) $autista."'";
		if($caposquadra !== null) $qry .= " AND m.caposquadra = '".(int) $caposquadra."'";
		if($monitori !== null) $qry .= " AND m.monitore = '".(int) $monitori."'";
		if($servizio_civile !== null) $qry .= " AND m.servizio_civile = '".(int) $servizio_civile."'";
		if($esce !== null) $qry .= " AND m.esce = '".(int) $esce."'";
		
		// order by name
		$qry .= " ORDER BY nomemilite";
						
		//$result = $db->key_value ("m.id", "CONCAT(m.cognome, ' ', m.nome) AS nomemilite", DBTABLE_MILITI, $qry);
		$result = $db->fetch_array($qry); // returns $result[x] = array("id" => id, "nomemilite" => nome_del_milite)
		
		if($result){
			// transform $result[x] = array("id" => id, "nomemilite" => nome_del_milite) to $array[id] = nome_del_milite
			$k = array_column($result, 'id');
			$v = array_column($result, 'nomemilite');
			
			$array = array_combine($k, $v); // NEW, vedere se funziona bene
		}else{
			$array = array();
		}


		if($options){
			return $this->arrayToOptions($array, $selected, $exclude);
		}else{
			return $array;
		}
	}

	private function pazienti($servizi_sociali = null, $privati = null, $options = true, $selected = "", $assenti = false){
		global $db;
		$clause = " WHERE active = '1'";
		if($servizi_sociali !== null) $clause .= " AND servizi_sociali = '".(int) $servizi_sociali."'";
		if($privati !== null) $clause .= " AND privato = '".(int) $privati."'";
		$order = " ORDER BY paziente";
		//$qry = "SELECT id, CONCAT(cognome, ' ', nome) AS paziente FROM ".DBTABLE_PAZIENTI.$clause.$order;
		//$result = $db->fetch_array($qry);
		$result = $db->key_value ("id", "CONCAT(cognome, ' ', nome) AS paziente", DBTABLE_PAZIENTI, $clause.$order);
		if($options){
			return $this->arrayToOptions($result, $selected);
		}else{
			return $result;
		}
	}

	private function strutture($ospedaliera = null, $options = true, $selected = ""){
		global $db;
		$clause = " WHERE active = '1'";
		if($ospedaliera !== null) $clause .= " AND ospedaliera = '".(int) $ospedaliera."'";
		$order = " ORDER BY nome";
		//$qry = "SELECT id, nome AS struttura FROM ".DBTABLE_STRUTTURE.$clause.$order;
		//$result = $db->fetch_array($qry);
		$result = $db->key_value ("id", "nome", DBTABLE_STRUTTURE, $clause.$order);
		if($options){
			return $this->arrayToOptions($result, $selected);
		}else{
			return $result;
		}
	}

	private function reparti($options = true, $selected = ""){
		global $db;
		$clause = " WHERE active = '1'";
		$order = " ORDER BY nome";
		//$qry = "SELECT id, nome AS reparto FROM ".DBTABLE_REPARTI.$clause.$order;
		//$result = $db->fetch_array($qry);
		$result = $db->key_value ("id", "nome", DBTABLE_REPARTI, $clause.$order);
		if($options){
			return $this->arrayToOptions($result, $selected);
		}else{
			return $result;
		}
	}

	private function automezzi($strade_strette = null, $options = true, $selected = "", $assenti = false){
		global $db;
		$clause = " WHERE active = '1'";
		if($strade_strette !== null) $clause .= " AND strade_strette = '".(int) $strade_strette."'";
		$order = " ORDER BY mezzo";
		$result = $db->key_value ("id", "CONCAT(numero_radio, '/', targa) AS mezzo", DBTABLE_AUTOMEZZI, $clause.$order);
		/*
		if($options){
			$qry = "SELECT id, CONCAT(numero_radio, '/', targa, '%s') AS mezzo FROM ".DBTABLE_AUTOMEZZI.$clause.$order;
		}else{
			$qry = "SELECT id, CONCAT(numero_radio, '/', targa) AS mezzo FROM ".DBTABLE_AUTOMEZZI.$clause.$order;
		}
		$result = $db->fetch_array($qry);
		*/
		if($options){
			return $this->arrayToOptions($result, $selected);
		}else{
			return $result;
		}
	}

	private function fasce($giorni = array(), $which = false, $format = false){
		global $db;
		$which = strtolower($which);
		$c = array();
		$clause = "";
		if(!empty($giorni)){
			$gg = implode("', '", $giorni);
			$c[] = " giorno IN ('".$gg."')";
		}
		$order = "`order`";
		$limit = "";
		switch($which){
			case "diurni":
				$c[] = " da < 20";
				break;
			case "notturni":
				$c[] = " da > 20";
				break;
			default:
				if(is_numeric($which)){
					$which = (int) $which;
					$hr = ($which < 7) ? $which+24 : $which;
					$c[] = "da <= ".$hr;
					$order = "da DESC";
					$limit = " LIMIT 1";
				}
				break;				
		}
		
		if(!empty($c)){
			$clause = implode(" AND ", $c);
			$clause = " WHERE ".$clause;		
		}
		$clause .= " ORDER BY ".$order;
		$clause .= $limit;

		$result = $db->select_all(DBTABLE_FASCE, $clause);
		
		if(!$result) return false;
		
		$fasce = array();
		foreach($result as $f){
			$fasce[ $f['giorno'] ][ $f['id'] ] = ($format) ? $f['da']."-".$f['a'] : $f['da'].$f['a'];
		}
		
		return $fasce;
		
	}


	private function arrayToOptions($array, $selected, $exclude = array() ){
		$options = "<option value=\"\"></option>\n";
		if(!is_array($array) or empty($array)) return $options;
			
		foreach($array as $key=>$value){
			$s = ($selected == $key) ? "selected=\"selected\"" : "";
			$s = (in_array($key, $exclude)) ? "disabled" : $s; // if excluded it can't be selected...
			$options .= "<option ".$s." value=\"".$key."\">".$value."</option>\n";
		}
		
		return $options;
	}
	
	
}






?>