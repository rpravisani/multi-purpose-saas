<?php

class cc_dbconnect{
	
	private $conn, $qry, $result = false, $numrows = 0, $result_array = array(), $error = array(), $errorhandler;
	public $db, $overwriteQuery=true, $rows_affected = 0;
	private $system_filenames = array("cpanel.php", "required.php", "cc_mysqli.class.php", "cc_mssql.class.php");
	private $trackupdate = true; // if this is true insert and update will evoce utime() function which will perform touch() file with name of table
	
	// colonne di sistema in tabella data_ usato per escluderli da estrapolazioni automatizzate
	private $system_columns = array("id", "active", "insertedby", "updatedby", "ts");
	

	function __construct($db = false, $db_host = DB_HOST, $db_user = DB_USER, $db_pwd = DB_PWD){
		//if(!$db) die("Nessun DB definito!");
		$mysqli = new mysqli($db_host, $db_user, $db_pwd, $db);
		if($mysqli->connect_error) {
			switch($mysqli->connect_errno){
				case '1045':
					$this->customError("Wrong username or password!", "__construct", $mysqli->connect_errno);
					break;
				case '1049':
					$this->customError("Unknow DB selected!", "__construct", $mysqli->connect_errno);
					break;
				case '2002':
					$this->customError("Host not found!", "__construct", $mysqli->connect_errno);
					break;
				default:
					die('Connect Error (' . $mysqli->connect_errno . ') '. $mysqli->connect_error );
					break;
			}
			
		}else{
			
			$mysqli->set_charset("utf8");
			$this->conn = $mysqli;	
			$this->db = $db;

			if($this->execute_query("SET CHARACTER SET utf8")){
				//$this->execute_query("SET time_zone='Europe/Rome'");
				return true;
			}else{
				return false;
			}
			
		}
		
	}


	function __destruct(){
		if(is_resource($this->conn)){
			$this->conn->close();
		}
	}
	
	// returns false if conn is empty (thus error during connection in __construct) true otherwise
	public function checkConnection(){
		if(empty($this->conn)){
			return false;
		}else{
			return true;
		}
	}
	
	public function changeDB($newdb = false){
		// if empty go back
		if(empty($newdb) or !$this->checkConnection() ) return false;
		// make safe
		$newdb = $this->make_data_safe($newdb); 
		// try to connect to new db if fail (db does not exist) the old one will permain
		$this->conn->select_db($newdb);
		$result = $this->conn->query("SELECT DATABASE()");
		$row = $result->fetch_row();
		return (empty($row[0])) ? false : true;
		
		
		
	}
	
	// accetta array (up to two dim) o singola stringa
	public function make_data_safe($var){
		
		if(is_array($var)){
			$safe = array();
			foreach($var as $k=>$v){
				if(is_array($v)){
					foreach($v as $k2 => $v2){
						$v2 = $this->conn->real_escape_string($v2);
						$safe[$k][$k2] = $v2;
					}
				}else{
					$v = $this->conn->real_escape_string($v);
					$safe[$k] = $v;
				}
			}
			
			return $safe;
			
		}else if(is_string($var)){
			return $this->conn->real_escape_string($var);
		
		}else{
			return false;
		
		}
	}
	
	private function trackError($function="", $output = true){
		$this->error['num'] = $this->conn->errno;
		$this->error['rawmsg'] = $this->conn->error;
		$this->error['sqlstate'] = ""; // for compatibility with cc_mssql.class
		$this->error['qry'] = $this->qry;
		$this->error['function'] = $function;
		$this->error['time'] = time();
		
		/*** Use PHP's debug_backtrace() function to get the name of the function, the line and the filename ***
		 *** of where the mysql error originated and use that data to construct the error message.           ***/
		$msg = "";
		$dbg = debug_backtrace(); // memorize backtrace error
		if($dbg){
			array_reverse($dbg);
			// Iter through all the filenames that are tracked and get the name of the first file that is not the main 
			// framework file, required.php or this class file and records the filename, line and function that caused the error 
			foreach($dbg as $v){
				$fne = explode("/", $v['file']);
				$file = end($fne);
				if(in_array($file, $this->system_filenames) and !@DEBUG) continue;
                $msg  = "MYSQL ERROR in <strong>".$file."</strong> on line <strong>".$v['line']."</strong> (function <em>".$v['function']."</em>)<br>\n";
                $msg .= "#".$this->conn->errno." : ".$this->conn->error."<br>\n<em>".$this->qry."</em>";
			} // end foreach
		}
		$this->error['msg'] = (empty($msg)) ? $this->error['rawmsg'] : $msg;

		if($output){
			global $_errorhandler;
			if($_errorhandler){
				$_errorhandler->setError($msg, "danger");
			}
			//log error anyway
			$errorlog_file = FILEROOT."logs/mysql_errorlog.txt";
			$opentype = (file_exists($errorlog_file)) ? "a" : "w";
			$errorlog = fopen($errorlog_file, $opentype);
			if($errorlog){
				$msg = strip_tags($msg)."\n\n";
				$msg = date("Y-m-d H:i:s", time())."\n".$msg;
				fwrite($errorlog, $msg);
				fclose($errorlog);				
			}
			
		} // end if output
		
	} // end function

	private function customError($err, $funzione="", $number = 0){
		$this->error['num'] = $number;
		$this->error['msg'] = $err;
		$this->error['sqlstate'] = "";
		$this->error['qry'] = "";
		$this->error['function'] = $funzione;
		$this->error['time'] = time();
	}
	
	private function clearError(){
		$this->error = array();
	}

	public function getError($filter=false, $clearErrors = false){
		
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
		if( !$this->checkConnection() ) return false;
		if(!empty($qry)) $this->setQuery($qry);
				
		if(empty($this->qry)){
			$this->customError("Query vuota", "execute_query");
			return false;
		}else{

			$result = $this->conn->query($this->qry);
			
			if(is_object($result)){
				$this->result = $result;
				$this->rows_affected = $this->conn->affected_rows;				
				return true;
				
			}else if($result === true){
				$this->result = true;
				$this->rows_affected = $this->conn->affected_rows;
				return true;
				
			}else if($result === false){				
				$this->trackError("execute_query");
				$this->result = false;
				return false;
				
			}		
		}
	}
	
	public function get_insert_id(){
		if($this->result){			
			return $this->conn->insert_id;
		}else{
			return false;
		}
	}

	public function get_next_id($table = false){
		if($table){
			$qry = "SELECT AUTO_INCREMENT FROM information_schema.tables WHERE table_name = '".$table."' AND table_schema = '".$this->db."';";
			$id = $this->fetch_array_row($qry, MYSQLI_NUM);
			return $id[0];
		}else{
			return false;
		}
	}
	
	public function set_auto_increment_value($table = false, $value = 0){
		$value = (int) $value;
		if(!$table or empty($value)) 
		$current_ai = (int) $this->get_next_id($table);
		//if($current_ai > $value) echo $current_ai .">". $value."\n";
		$qry = "ALTER TABLE ".$table." AUTO_INCREMENT = ".$value;
		$this->setquery($qry);
		$this->execute_query();
		return true;
	}

	public function get_num_rows($qry=false, $returnit=false){
		
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			$this->execute_query();
		}
		// controllo se c'è un risultato
		if(!$this->result) return false;
		
		$this->numrows = $this->result->num_rows;
		if($returnit) return $this->numrows;  
		
	}


	public function fetch_object($qry = false){
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			if(!$this->execute_query()){
				echo $this->getError("msg")." :: ".$this->getError("qry");
				return false;
			}
		}
		// controllo se c'è un risultato
		if($this->result->num_rows == 0) return false;
		
		unset($this->result_array);
		while ($obj = $this->result->fetch_object()) {
			  $this->result_array[] = $obj;
		}
		
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	public function fetch_array_row($qry = false, $type=MYSQLI_ASSOC){ // in alternativa MYSQLI_NUM o MYSQLI_BOTH
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		if(!empty($qry)){
			$this->setquery($qry);
			if(!$this->execute_query()){
				echo $this->getError("msg")." :: ".$this->getError("qry");
				return false;
			}
		}
		// controllo se c'è un risultato
		if($this->result->num_rows == 0) return false;
		
		unset($this->result_array);
		$this->result_array = $this->result->fetch_array($type);
			
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	public function fetch_array($qry = false, $type=MYSQLI_ASSOC){ // in alternativa MYSQLI_NUM o MYSQLI_BOTH
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		
		if(!empty($qry)){
			$this->setquery($qry);
			if(!$this->execute_query()){
				//echo $this->getError("msg")." :: ".$this->getError("qry");
				return false;
			}
		}
		
		// controllo se c'è un risultato
		if($this->result->num_rows == 0) return false;
		
		unset($this->result_array);
		while( $row = $this->result->fetch_array( $type) ) {
			  $this->result_array[] = $row;
		}
		
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}

	public function fetch_array_indexed($qry = false, $index='id', $type=MYSQLI_ASSOC){ // in alternativa MYSQLI_NUM o MYSQLI_BOTH
		// se passo qry aggiorno variabile interna e rilancio esecuzione
		
		if(!empty($qry)){
			$this->setquery($qry);
			if(!$this->execute_query()){
				echo $this->getError("msg")." :: ".$this->getError("qry");
				return false;
			}
		}
		
		// controllo se c'è un risultato
		if($this->result->num_rows == 0) return false;
		
		unset($this->result_array);
		while( $row = $this->result->fetch_array( $type) ) {
			  $this->result_array[$row[$index]] = $row;
		}
		
		return (empty($this->result_array)) ? false : $this->result_array;
				
	}
	
	// restiruisce true o false;
	// esegue un inserimento su $table, passando un array o stringa correttamente formattata con i valori ed 
	// un eventuale array o stringa correttamente formattata con i campi da aggiornare.
	public function insert($table, $valori, $campi="", $update = array() ){
		if(empty($valori)){
			$this->customError("Valori vuota", "insert");
			return false;
		}
		
		if(is_array($valori)){
			$valori = "'".implode("','", $valori)."'";
		}		
		
		$qry = "INSERT INTO ".$table;
		if(!empty($campi)){
			if(is_array($campi)){
				foreach($campi as $campo){
					$c[] = "`".$campo."`";
				}
				$campi = implode(",", $c);
			}
			$qry .= " (".$campi.")";
		}
		$qry .= " VALUES (".$valori.")";
		// if the array $update is not empty add ON DUPLICATE KEY UPDATE clause
		// ALTER TABLE `votes` ADD UNIQUE `unique_index`(`user`, `email`, `address`);

		if(!empty($update)){
			$qry .= " ON DUPLICATE KEY UPDATE ";
			foreach($update as $ukey=>$uvalue){
				
				$kv[] = "`".$ukey."`"." = '".$uvalue."'";
			}
			$qry .= implode(",", $kv);
		}

		
		if( $this->execute_query($qry)){			
			
			if($this->trackupdate){
				$this->setUtime($table);
			}
			$insid = $this->conn->insert_id; 
			// if $insid in this fase is false must mean the table is non standard and does not have an AUTO_INCREMENT primary key, in thata case just return true
			return (!$insid) ? true: $insid;
		}else{
			return false;
		}
		
	}

	// restiruisce true o false;
	// esegue un aggioranmento su $table, passando array con chiave assocciativa = campo e valore = nuovo valore campo, in base a $condizione
	public function update($table, $valori, $condizione=""){
		if(empty($valori)){
			$this->clearError();
			$this->customError("Array valori vuota", "update");
			return false;
		}
		
		if(empty($table)){
			$this->clearError();
			$this->customError("Variabile table vuota", "update");
			return false;
		}
		
		if(!is_array($valori)){
			$this->clearError();
			$this->customError("Valori non è un array", "update");
			return false;
		}
		
		$qry = "UPDATE ".$table." SET ";
		foreach($valori as $key=>$valore){
			
			$kv[] = "`".$key."`"." = '".$valore."'";
		}
		$qry .= implode(",", $kv);
		if(!empty($condizione)) $qry .= " ".$condizione;
		
		$result = $this->execute_query($qry);
		
		if(!$result){
			return false;
		}else{
			if($this->trackupdate){
				$this->setUtime($table);
			}
			return true;
			
		}
		
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

	public function truncate($table){
		if(!$table){
			$this->customError("Nessuna tabella settata!", "truncate");
			return false;
		}		
		$qry = "TRUNCATE TABLE ".$table;
		return $this->execute_query($qry);
		
	}
	
	public function drop($table){
		if(!$table){
			$this->customError("Nessuna tabella settata!", "drop");
			return false;
		}		
		$qry = "DROP TABLE ".$table;
		return $this->execute_query($qry);
		
	}
	

	public function get_table_info($table) {
		if(empty($table)) return false;
		$qry = "SHOW FIELDS FROM ".$table;
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array($qry, MYSQLI_ASSOC);
		return (empty($return)) ? false : $return;
	}
	

	/**
	 * Recupero i nome dei campi/colonne di una tabella. Posso escludere o meno i campi di sistema(id, active, ts etc)
	 *
	 * @param table (string) nome dalle tabella
	 * @param exclude_system (bool) Flag se estrapolare o meno i campi di sistema (vedi array $this->system_columns)
	 *
	 * @return array con nome dei campi in ordine di apparizionein tabella
	 */
	public function get_column_names($table, $exclude_system = true) {
		$tableinfo = $this->get_table_info($table);
		if(empty($tableinfo)) return false;
		
		foreach($tableinfo as $output){
			
			// se campo di sistema vedo se evo escluderlo o meno
			if($exclude_system and in_array($output, $this->system_columns)) continue;
			
			$return[] = $output['Field'];
		}
		
		return $return;
		
	}

	// Restituisce array 
	// Ricupera tutti le tabelle del DB definito durante la costruzione della classe 
	public function getTablenames ($starts_with = false) {
		$qry = "SHOW TABLES";
		if ($this->overwriteQuery) $this->qry = $qry;
		$tabs = $this->fetch_array($qry, MYSQLI_NUM);
		if(empty($tabs)) return false;
		$swl = ($starts_with) ? strlen($starts_with) : 0;
		if($starts_with) $starts_with = (string) $starts_with;
		foreach($tabs as $tab){
			if(!$starts_with){
				$return[] = $tab[0];				
			}else{
				if( substr($tab[0], 0, $swl) == $starts_with ) $return[] = $tab[0];
			}
		}
		return (empty($return)) ? false : $return;
	}
	
	public function get_max_row($column, $table, $condizione="", $returnit=true){
		$qry = "SELECT MAX(`".$column."`) FROM ".$table." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, MYSQLI_NUM);
		return (empty($return)) ? false : $return[0];	
	}

	public function get_min_row($column, $table, $condizione="", $returnit=true){
		$qry = "SELECT MIN(`".$column."`) FROM ".$table." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, MYSQLI_NUM);
		return (empty($return)) ? false : $return[0];	
	}
	
	// Ricupera tutti i valori $tabella in base a $condizione 
	// Restituisce array multi-dimensionalre avente come prima chiave un progressivo e come seconda chiave il nome della colonna
	public function select_all ($tabella, $condizione="", $returnit = true) {
		$qry = "SELECT * FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array($qry, MYSQLI_ASSOC);
		return (empty($return)) ? false : $return;
	} 

	// come select_all, ma con l'aggiunta di una var $index che indica il campo da utilizzare com indice invece che una numerazione progressiva
	public function select_all_indexed ($index='id', $tabella, $condizione="", $returnit = true) {
		$qry = "SELECT * FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		$return = $this->fetch_array_indexed($qry, $index, MYSQLI_ASSOC);
		return (empty($return)) ? false : $return;
	} 

	// Ricupera il valore di $campo_valore della $tabella in base a $condizione 
	// Restituisce array avente come chiave con valore $campo_chiave e come valore $campo_valore
	public function key_value ($campo_chiave, $campo_valore, $tabella, $condizione="", $distinct=false) {
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		 
		$resultvalue = array();
		// let's see if there's an 'AS xxxx' declaration in campo_valore - if so get xxxx
		preg_match('/\s+AS\s+(\w+)/', $campo_valore, $matches);
		if($matches){
			$cv = $matches[1];
		}else{
			$cv = $campo_valore;
			$campo_valore = "`".$campo_valore."`";
		}

		if($distinct){
			$qry = "SELECT DISTINCT ";
		}else{
			$qry = "SELECT ";
		}
		$qry .= "`".$campo_chiave."`, ".$campo_valore." FROM ".$tabella;
		
		if(!empty($condizione)){
		   $qry .= " ".$condizione;
		}
		
		if(!$this->execute_query($qry)){
			//error handling
			$this->trackError("key_value");
			return false;
		}else{
			while( $output = $this->result->fetch_array(MYSQLI_ASSOC) ) {
				$key = $output[$campo_chiave];
				$value = $output[$cv];
				$resultvalue[$key] = $value;
			}
			return $resultvalue;
		}
	}

	// Ricupera il valore di $colonna della $tabella in base a $condizione - switch se distinct o meno
	// Restituisce array con valori
	public function col_value ($colonna, $tabella, $condizione="", $distinct=false, $convert2=false, $variant=false) {
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		
		if($convert2) $colonna = $this->convert($colonna, $convert2, $variant);
		
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
			$resultvalue = array();
			while( $output = $this->result->fetch_array( MYSQLI_NUM ) ) {
				$resultvalue[$i] = $output[0];
				$i++;
			}
			return $resultvalue;
		}		
	}
	
	// Restituisce stringa con singolo valore
	// Ricupera il valore di $campo della $tabella in base a $condizione
	public function get1value ($campo, $tabella, $condizione="", $returnit=true) {
		
		$qry = "SELECT ".$campo." FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, MYSQLI_NUM);
		return (empty($return)) ? false : $return[0];	
		
	}
	
	// Ricupera una singola riga da $tabella in base a $condizione, se condizione è vuota, estrapola la prima riga
	// Restituisce array avente chiave il nome della colonna
	public function get1row($tabella, $condizione="", $returnit=true) {
		$qry = "SELECT * FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;
		
		$return = $this->fetch_array_row($qry, MYSQLI_ASSOC);
		return (empty($return)) ? false : $return;	
	}

	// Ricupera il valore di $campo della $tabella in base a $condizione
	// Restituisce stinga con valori separati da virgola 
	public function field2string($campo, $tabella, $condizione, $returnit=true){
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		
		$qry = "SELECT ".$campo." FROM ".$tabella." ".$condizione;
		if ($this->overwriteQuery) $this->qry = $qry;

		if(!$this->execute_query($qry)){
			//error handling
			
			$this->trackError("field2string");
			return false;
		}else{
			while( $output = $this->result->fetch_array( MYSQLI_NUM ) ) {
				$outvalue[] = $output[0];
			}
			return implode(",", $outvalue);;
		}

	}

	public function get_enum_values( $table, $field ){
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		
		$row = $this->fetch_array_row( "SHOW COLUMNS FROM ".$table." WHERE Field = '".$field."'" );
		$type = $row['Type'];
		preg_match('/^enum\((.*)\)$/', $type, $matches);
		foreach( explode(',', $matches[1]) as $value )
		{
			 $enum[] = trim( $value, "'" );
		}
		return $enum;
	}

	public function count_rows( $table, $where = "", $field = "id" ){
		$row = $this->fetch_array_row( "SELECT COUNT(".$field.") FROM ".$table." ".$where, MYSQLI_NUM );
		if($row){
			return $row[0];
		}else{
			return false;
		}
	}
	
	public function sum_column( $field = "", $table = "", $where = "", $group_by = "" ){
		if( empty($table) or empty($field) ) return false;
		
		$where = trim($where);
		if(!empty($where)){
			if(stripos($where, "WHERE") === false) $where = "WHERE ".$where;
		}
		
		if(!empty($group_by)) $group_by = " GROUP BY ".$group_by;
		
		$row = $this->fetch_array_row("SELECT SUM(".$field.") FROM ".$table." ".$where.$group_by, MYSQLI_NUM );
		if($row){
			return $row[0];
		}else{
			return false;
		}
	}
	
	public function get_primary_key($table){
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		$row = $this->fetch_array_row( 'SHOW INDEX FROM '.$table.' where Key_name = "PRIMARY"' );
		return $row['Column_name'];	
	}

	public function get_qry_fields ($qry = false) {
		if( !$this->checkConnection() ) return false; // per semplificare lo aggiungo
		$out = array();
		if(!$qry) $qry = $this->getquery();
		$result = $this->conn->query($qry);
		$finfo = $result->fetch_fields();
		foreach ($finfo as $val) {
			$out[] = $val->name;
		}
		return $out;
	}	
	
	public function merge_fields ($tabella = false, $campi = array(), $condizione = "", $div = " ") {
		if(!$tabella) return false;
		if(!is_array($campi)) return false;
		
		$n = count($campi);
		$c = 0;
		$concat = "";
		foreach($campi as $campo){
			$c++;
			$concat .= $campo;
			if($c < $n) $concat .= ",'".$div."',";
		}
		
		$qry = "SELECT CONCAT(".$concat.") AS unito FROM ".$tabella." ".$condizione;
 		$array = $this->fetch_array_indexed($qry, "unito");
		if($array){
			return $array;
		}else{
			return false;
		}
	}	
	
	public function enum2options ($column, $table, $selected="", $order=false) {
		if( !$this->checkConnection() ) return "<option>NOT CONNECTED TO ANY DB!</option>"; // per semplificare lo aggiungo
		if(empty($column)) return "<option>NO COLUMN SET!</option>";
		if(empty($table)) return "<option>NO TABLE SET!</option>";
		$values = $this->get_enum_values($table, $column);
		if(!$values) return "<option>NOTHING FOUND!</option>";
		$options = "";
		if($order) asort($values);
		foreach($values as $value){
			$s = ($selected == $value) ? "selected" : "";
			$options .= "<option ".$s." value='".$value."'>".$value."</option>\n"; 
		}
		return $options;
	}
	
	/*** PRIVATE FUNCTIONS FOR UPDATE TIEM OF TABLES (USEFUL FOR DATA-CACHING) ***/
	private function setUtime($table){
		$ufile = FILEROOT."system/".$table.".utime";
		if( file_exists( $ufile) ){
			touch($ufile); // update last modified date & time
		}else{
			// create file
			$content = "/*** USE FILE TIMESTAMP TO GET THE  UPDATE TIME OF THE TABLE ".$table.". -- DO NOT DELETE!!!  ***/";
			if( file_exists(FILEROOT.'system') ){
				file_put_contents($ufile, $content);
			}else{
				$this->customError("Cannot create utime file, the dir system does not exist!", "_utime");
			}
		}
		
	}

	private function getUtime($table = false){
		// get utime of single table - returns string (epoch)
		if($table){
			$ufile = FILEROOT."system/".$table.".utime";
			if( file_exists( $ufile) ){
				return filemtime($ufile); 
			}else{
				return false;
			}
		}else{
			// get utime of all tables - returns array ($out[table_name] = epoch)
			$out = array();
			$pattern = FILEROOT."system/*.utime";
			$files = glob($pattern); // get all filenames that end with .utime in system folder 
			if($files){
				foreach($files as $file){
					$utime = filemtime($file);
					$ufile = end( explode("/", $file) );
					$out[$ufile] = $utime;
				}			
			}
			return $out;
		}
		
	}
	
	// Takes a field name, field type to convert to and an optional charset. Returns string with CONVERT(field, type [USING])
	private function convert($field, $convert2, $variant = false){
		
		if(empty($field)) return false;
		if(empty($convert2)) return $field;
		
		$convert2 = strtoupper($convert2);
		
		switch($convert2){
			case "INT":
			case "SIGNED":
				$type = "SIGNED";
				break;
			case "UNSIGNED":
				$type = "UNSIGNED";
				break;
			case "BINARY":
				$type = "BINARY";
				if($variant){
					$variant = int($variant);
					if(!empty($variant)) $type .= "(".$variant.")";
				}
				break;
			case "CHAR":
			case "VARCHAR":
				$type = "CHAR";
				if($variant){
					$variant = int($variant);
					if(!empty($variant)) $type .= "(".$variant.")";
				}
				break;
			case "NCHAR":
				$type = "NCHAR";
				if($variant){
					$variant = int($variant);
					if(!empty($variant)) $type .= "(".$variant.")";
				}
				break;
			case "DECIMAL":
			case "FLOAT":
				$type = "DECIMAL";
				if($variant){
					$v = split(",", $variant);
					$m = (int) $v[0];
					$n = (int) $v[1];
					if(!empty($m)) $type .= "(".$m.", ".$n.")";
				}
				break;
			case "DATE":
				$type = "DATE";
				break;
			case "DATETIME":
				$type = "DATETIME";
				break;
			case "TIME":
				$type = "TIME";
				break;
			default:
				$type = false;
				break;
		}
		
		if($type){
			return "CONVERT(".$field.", ".$type.")";			
		}else{
			return $field; 
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
	

}

?>
