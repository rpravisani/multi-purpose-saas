<?php

class cc_dbconnect{
	
	private $conn, $qry, $result = false, $numrows = 0, $result_array = array(), $error;
	public $db, $overwriteQuery=true, $rows_affected = 0;
	
	function __construct($db = false){
		if(!$db) die("Nessun DB definito!");
		if(!defined("SERVER_NAME")) die("Nessuna connessione definita!");
		$connectionInfo = array("Database"=>$db, 'ReturnDatesAsStrings'=>true);

		$this->conn = sqlsrv_connect( SERVER_NAME, $connectionInfo);
		if( $this->conn === false ){
			 echo "Unable to connect.</br>";
			 die( print_r( sqlsrv_errors(), true));
		}
		
		$this->db = $db;
		return true;
	}


	function __destruct(){
		if(is_resource($this->conn)){
			sqlsrv_close( $this->conn);
		}
	}
	
	private function trackError($funzione=""){
		
		$errArray = sqlsrv_errors();
	
		$this->error['num'] = $errArray[0]['code'];
		$this->error['msg'] = $errArray[0]['message'];
		$this->error['sqlstate'] = $errArray[0]['SQLSTATE'];
		$this->error['qry'] = $this->qry;
		$this->error['function'] = $funzione;
		$this->error['time'] = time();
	}

	private function customError($err, $funzione=""){
		$this->error['num'] = 0;
		$this->error['msg'] = $err;
		$this->error['sqlstate'] = "";
		$this->error['qry'] = $this->qry;
		$this->error['function'] = $funzione;
		$this->error['time'] = time();
	}

	public function getError($filter=false, $clearErrors = false){
		if(empty($this->error)) return false;
		if($filter){
			if (array_key_exists($filter, $this->error)) {
				$err = $this->error[$filter];
			}else{
				$err = false;
			}
		}else{
			if(empty($this->error)){
				$err = false;
			}else{
				$err = $this->error;
			}
		}
		if($clearErrors) unset($this->error);
		return $err;
	}
	
	public function setquery($qry){
		if(empty($qry)){
			$this->customError("Query vuota", "setquery");
			return false;
		}else{
			$this->qry = $qry;
			return true;
		}
	}

	public function getquery(){
		if(empty($this->qry)){
			$this->customError("Query vuota", "getquery");
			return false;
		}else{
			return $this->qry;
		}
	}

	public function execute_query($qry=false){
		if(!empty($qry)) $this->setQuery($qry);
		
		if(empty($this->qry)){
			$this->customError("Query vuota", "execute_query");
			return false;
		}else{
			
			$result = sqlsrv_query( $this->conn, $this->qry);
			if($result){
				$this->result = $result;
				$this->rows_affected = sqlsrv_rows_affected( $result);
				return true;
			}else{
				$this->trackError("execute_query");
				return false;
			}		
		}
	}
	
	public function get_insert_id(){
		if($this->result){
			$qry = "SELECT SCOPE_IDENTITY() AS [SCOPE_IDENTITY]";
			$insertid = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
			return $insertid[0];
		}else{
			return false;
		}
	}

	public function get_next_id($table = false){
		if($table){
			$qry = "SELECT IDENT_CURRENT('".$table."')";
			$id = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
			return $id[0]+1;
		}else{
			return false;
		}
	}
	
	public function get_num_rows($qry=false, $returnit=false){
		
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			$this->execute_query();
		}
		// controllo se c'è un risultato
		if(!$this->result) return false;
		
		$this->numrows = sqlsrv_num_rows( $this->result );
		if($returnit) return $this->numrows;  
		
	}


	public function fetch_object($qry = false){
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			$this->execute_query();
		}
		// controllo se c'è un risultato
		if(!$this->result) return false;
		
		while( $obj = sqlsrv_fetch_array( $this->result) ) {
			  $this->result_array[] = $obj;
		}
		
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	public function fetch_array_row($qry = false, $type=SQLSRV_FETCH_ASSOC){ // in alternativa SQLSRV_FETCH_NUMERIC o SQLSRV_FETCH_ASSOC
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			$this->execute_query();
		}
		// controllo se c'è un risultato
		if(!$this->result) return "nessun risultato";
		
		$this->result_array = sqlsrv_fetch_array( $this->result, $type);
			
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	public function fetch_array($qry = false, $type=SQLSRV_FETCH_ASSOC){ // in alternativa SQLSRV_FETCH_NUMERIC o SQLSRV_FETCH_BOTH
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		
		if(!empty($qry)){
			$this->setquery($qry);
			$this->execute_query();
		}
		
		// controllo se c'è un risultato
		if(!$this->result) return false;
		
		while( $row = sqlsrv_fetch_array( $this->result, $type) ) {
			  $result_array[] = $row;
		}
		
		return (empty($result_array)) ? false : $result_array;
				
	}

	public function fetch_array_indexed($qry = false, $index='id', $type=SQLSRV_FETCH_ASSOC){ // in alternativa MYSQLI_NUM o MYSQLI_BOTH
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		
		if(!empty($qry)){
			$this->setquery($qry);
			if(!$this->execute_query()){
				echo $this->getError("msg")." :: ".$this->getError("qry");
				return false;
			}
		}
		
		// controllo se c'è un risultato
		if(!$this->result) return false;
		
		unset($this->result_array);
		while( $row = sqlsrv_fetch_array( $this->result, $type) ) {
			  $this->result_array[$row[$index]] = $row;
		}
		
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	
	// esegue un inserimento su $table, passando un array o stringa correttamente formattata con i valori ed 
	// un eventuale array o stringa correttamente formattata con i campi da aggiornare.
	// restiruisce true o false;
	public function insert($table, $valori, $campi="" ){
		if(empty($valori)){
			$this->customError("Valori vuota", "insert");
			return false;
		}
		if(is_array($valori)) $valori = implode("','", $valori);
		$valori = "'".$valori."'";
		$qry = "INSERT INTO ".$table;
		if(!empty($campi)){
			if(is_array($campi)) $campi = implode(",", $campi);
			$qry .= " (".$campi.")";
		}
		$qry .= " VALUES (".$valori.")";
		return $this->execute_query($qry);
		
	}

	// restiruisce true o false;
	// esegue un aggioranmento su $table, passando array con chiave assocciativa = campo e valore = nuovo valore campo, in base a $condizione
	public function update($table, $valori, $condizione=""){
		if(empty($valori)){
			$this->customError("Array valori vuota", "update");
			return false;
		}
		
		if(empty($table)){
			$this->customError("Variabile table vuota", "update");
			return false;
		}
		
		if(!is_array($valori)){
			$this->customError("Valori non è un array", "update");
			return false;
		}
		
		$qry = "UPDATE ".$table." SET ";
		foreach($valori as $key=>$valore){
			$kv[] = $key."='".$valore."'";
		}
		$qry .= implode(",", $kv);
		if(!empty($condizione)) $qry .= " ".$condizione;
		
		return $this->execute_query($qry); 
		
	}
	

	public function delete($table, $condizione=false ){
		if(!$condizione){
			$this->customError("Nessuna condizione settata!", "delete");
			return false;
		}
		
		if(empty($table)){
			$this->customError("Variabile table vuota!", "delete");
			return false;
		}
		$qry = "DELETE FROM ".$table." ".$condizione;
		return $this->execute_query($qry);
		
	}



	public function get_table_info($table) {
		$qry = "SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('dbo.".$table."')";
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array($qry, SQLSRV_FETCH_ASSOC);
		return (empty($return)) ? false : $return;
	}

	public function get_column_names($table) {
		$s = explode(".", $table); // p.e. rubrica.dbo.clienti
		$qry = "SELECT COLUMN_NAME FROM ".$s[0].".INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$s[2]."' AND TABLE_SCHEMA='".$s[1]."'";
		
		
		if ($this->overwriteQuery) $this->qry = $qry;

		if(!$this->execute_query($qry)){
			//error handling
			$this->trackError("get_column_names");
			return false;
		}else{
			while( $output = sqlsrv_fetch_array( $this->result, SQLSRV_FETCH_ASSOC) ) {
				$return[] = $output['COLUMN_NAME'];
			}
			return $return;
		}
		
	}

	// Ricupera tutti le tabelle del DB definito durante la costruzione della classe 
	// Restituisce array 
	public function getTablenames () {
		$qry = "select name from ".$this->db."..sysobjects where xtype = 'U';";
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array($qry, SQLSRV_FETCH_ASSOC);
		return (empty($return)) ? false : $return;
	}
	
	public function get_max_row($column, $table, $condizione="", $returnit=true){
		$qry = "SELECT MAX(".$column.") FROM ".$table." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
		return (empty($return)) ? false : $return[0];	
	}
	
	// Ricupera tutti i valori $tabella in base a $condizione 
	// Restituisce array multi-dimensionalre avente come prima chiave un progressivo e come seconda chiave il nome della colonna
	public function select_all ($tabella, $condizione="", $returnit = true) {
		$qry = "SELECT * FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array($qry, SQLSRV_FETCH_ASSOC);
		return (empty($return)) ? false : $return;
	} 

	// Ricupera il valore di $campo_valore della $tabella in base a $condizione 
	// Restituisce array avente come chiave con valore $campo_chiave e come valore $campo_valore
	public 	function key_value ($campo_chiave, $campo_valore, $tabella, $condizione="", $distinct=false) {
		if($distinct){
			$qry = "SELECT DISTINCT ";
		}else{
			$qry = "SELECT ";
		}
		$qry .= $campo_chiave.", ".$campo_valore." FROM ".$tabella;
		
		if(!empty($condizione)){
		   $qry .= " ".$condizione;
		}
		
		if(!$this->execute_query($qry)){
			//error handling
			$this->trackError("key_value");
			return false;
		}else{
			while( $output = sqlsrv_fetch_array( $this->result, SQLSRV_FETCH_ASSOC) ) {
				$key = $output[$campo_chiave];
				$value = $output[$campo_valore];
				$resultvalue[$key] = $value;
			}
			return $resultvalue;
		}
	}

	// Ricupera il valore di $colonna della $tabella in base a $condizione - switch se distinct o meno
	// Restituisce array con valori
	public function col_value ($colonna, $tabella, $condizione="", $distinct=false, $returnit=true) {
		if ($distinct){
			$qry = "SELECT DISTINCT ".$colonna." FROM ".$tabella." ".$condizione;
		}else{
			$qry = "SELECT ".$colonna." FROM ".$tabella." ".$condizione;
		}
		if ($this->overwriteQuery) $this->qry = $qry;
		
		if(!$this->execute_query($qry)){
			//error handling
			$this->trackError("col_value");
			return false;
		}else{
			$i=0;
			while( $output = sqlsrv_fetch_array( $this->result, SQLSRV_FETCH_ASSOC) ) {
				$resultvalue[$i] = $output[$colonna];
				$i++;
			}
			return $resultvalue;
		}		
	}
	
	// Ricupera il valore di $campo della $tabella in base a $condizione
	// Restituisce stringa con singolo valore
	public function get1value ($campo, $tabella, $condizione="", $returnit=true) {
		$qry = "SELECT ".$campo." FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
		return (empty($return)) ? false : $return[0];	
		
	}
	
	// Ricupera una singola riga da $tabella in base a $condizione, se condizione è vuota, estrapola la prima riga
	// Restituisce array avente chiave il nome della colonna
	public function get1row ($tabella, $condizione="", $returnit=true) {
		$qry = "SELECT * FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, SQLSRV_FETCH_ASSOC);
		return (empty($return)) ? false : $return;	
	}

	// Ricupera il valore di $campo della $tabella in base a $condizione
	// Restituisce stinga con valori separati da virgola 
	public function field2string($campo, $tabella, $condizione, $returnit=true){
		$qry = "SELECT ".$campo." FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;

		if(!$this->execute_query($qry)){
			//error handling
			
			$this->trackError("field2string");
			return false;
		}else{
			while( $output = sqlsrv_fetch_array( $this->result, SQLSRV_FETCH_NUMERIC) ) {
				$outvalue[] = $output[0];
			}
			return implode(",", $outvalue);;
		}

	}
	
	/*********************************
	Funzioni particolari non generiche 
	**********************************/
	
	// Ricupero un valore a caso da una tabella
	public function getRndRow($colonna, $tabella, $condizione= false){
		$result = $this->col_value($colonna, $tabella, $condizione);
		if($result){
			$rnd = rand(0,count($result)-1);
			return $result[$rnd];
		}else{
			return false;
		}
		
	}
	
	public function countRecords($tabella, $condizione = false, $campo = false){
		if(!$campo) $campo = "*";
		$qry = "SELECT COUNT(".$campo.") FROM ".$tabella;
		if($condizione) $qry .= " ".$condizione;
		$result = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
		return $result[0];
	}

	public function getNextId($tabella= false){
		if(!$tabella) return false;
		
		$qry = "SELECT IDENT_CURRENT('".$tabella."') + IDENT_INCR('".$tabella."')";
		$result = $this->fetch_array_row($qry, SQLSRV_FETCH_NUMERIC);
		return $result[0];
	}
	
	public function make_data_safe($data){
		if(is_array($data)){
			foreach($data as $k=>$v){
				if(is_string($v)){
					$v = htmlspecialchars($v, ENT_QUOTES);
				}
				$out[$k] = $v;
			}
			return $out;
		}else{
			return htmlspecialchars($data, ENT_QUOTES);
		}
	}

}

?>