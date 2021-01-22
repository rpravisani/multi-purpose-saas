<?php

class grid{
	
	// variabili obbligatori
	public $query = ""; // query su cui basare tutto
	
	public $colonne = ""; // nome colonna db da usare per l'intestazione colonna
	public $righe   = ""; // nome colonna db da usare per l'intestazione riga
	public $celle   = ""; // nome colonna db da usare per il valore delle cella
	public $id      = ""; // nome colonna db da usare per identificare record cella

	// variabili faccoltativi
	public $legenda = ""; // Didascalia per la legenda collocata nella cella A0
	public $tmpl_colonna = "%s"; // template per intestazione colonne (verrà utilizzato sprintf, quindi deve avere %s)
	public $tmpl_riga = "%s";// template per intestazione riga (verrà utilizzato sprintf, quindi deve avere %s)
	public $tmpl_cell = "%s"; // template per cella (verrà utilizzato sprintf, quindi deve avere %s)
	public $tmpl_val = "%s"; // template per il valore (verrà utilizzato sprintf, quindi deve avere %s)
	public $inputclass = ""; // extra class da dare al input
	public $tmpl_mod_col_btn = "<button type='button' class='btn btn-xs btn-default btn-flat modify-column' data-colname='%s' data-toggle=\"popover\" title=\"Modifica tutti i prezzi di questo listino\" data-placement=\"bottom\" data-html=true>Mod. tutti i valori <i class='fa fa-caret-down'></i></button>";

	// variabili funzionali
	protected $error = array(), $rawdata = array(), $th = array(), $rows = array(), $cell = array();
	protected $thead = "", $tbody = "";
	protected $nrecords = -1, $nrows = 0;
	
	private function setError($msg){
		$this->error[] = trim($msg);
	}
	private function getErrors(){
		$output = implode("<br>\n", $this->error);
		return $output;
	}
	
	public function getRecordsNumber(){
		return $this->$nrecords;
	}
	public function getRows(){
		return $this->$nrows;
	}
	
	
	private function checkVars(){
		if(empty($this->query)){ $this->setError("No query set!"); return false; }
		if(empty($this->colonne)){ $this->setError("Manca la variabile colonne!"); return false; }
		if(empty($this->righe)){ $this->setError("Manca la variabile righe!"); return false; }
		if(empty($this->celle)){ $this->setError("Manca la variabile celle!"); return false; }
		if(empty($this->id)){ $this->setError("Manca la variabile id!"); return false; }
		return true;
	}
	
	public function getGrid($active = true, $vertical_first = true){
		
		// controllo che siano compilati i campi obbligatori
		if(!$this->checkVars()) return $this->getErrors();
		
		$data = $this->setData();
		if(empty($this->rawdata)) return "no data!<br><pre>".$this->query."</pre>";
		
		$this->setThead();
		$this->setTbody($active, $vertical_first);
		
		return $this->thead.$this->tbody;
		
	}	
	
	public function setData(){
		
		global $db;
		
		// controllo che siano compilati i campi obbligatori
		if(!$this->checkVars()) return false;
		
		$this->rawdata = $db->fetch_array($this->query);
		if(empty($this->rawdata)){ $this->$nrecords = 0; return false; }

		$this->count = count($this->rawdata);
		
		foreach($this->rawdata as $dbrow){
			foreach($dbrow as $key=>$value){
				
				$this->rows[$dbrow[$this->righe]][$dbrow[$this->colonne]] = $dbrow[$this->id];
				$this->cell[$dbrow[$this->id]] = $dbrow[$this->celle];
				
				// se la chiave = il campo colonna setto raccolgo varore colonna
				if($key == $this->colonne){ 
					$this->th[$value] = true; // p.e. $elenco_colonne['nord'] = true; fatto così per avere valori DISTINCT
				}
				
			} // end foreach $dbrow
			
		} // end foreach rawdata
				
	} // fine func
	
	
	public function setThead(){
		
		if(!$this->checkVars()) return false;
		
		$this->thead = "
			<thead>
				<tr>
					<td>".$this->legenda."</td>
		
		";
       
		$this->ths = array_keys($this->th);
		foreach($this->ths as $th){
			$colname = $this->colname_format($th);			
			$modbtn = (empty($this->tmpl_mod_col_btn)) ? "" : sprintf($this->tmpl_mod_col_btn, $colname);
			
			$th = (empty($this->tmpl_colonna)) ? $th : sprintf($this->tmpl_colonna, $th);
			$this->thead .= "<th scope=\"col\">".$th.$modbtn."</th>";
		}

		$this->thead .= "
				</tr>
			</thead>		
		";
		
 	} // end func
	
	public function setTbody($active = true, $vertical_first = true){
		
		$tabindex = $countrows = $current_col = 0;
		$nrows = count($this->rows);
		$ncols = count($this->th);
		
		$this->tbody = "<tbody>\n";

		foreach($this->rows as $throw=>$row){
			
			$countrows++;
			
			
			$throw = (empty($this->tmpl_riga)) ? $throw : sprintf($this->tmpl_riga, $throw);
			$this->tbody .= "  <tr>\n";
			$this->tbody .= "  	<th scope='row'>".$throw."</th>\n";
			
			foreach($this->ths as $th){
				$cellid = $row[$th];
				$value = $rawvalue = $this->cell[$cellid];
				
				$colname = $this->colname_format($th);				
				
				// FORMAT THE VALUE INSIDE THE INPUT FIELD WITH FORMAT FUNCTIONS
				if( !empty($this->format) ){
					if( is_array( $this->format ) ){
						$_func = $this->format['func'];
						$_params = $this->format['params']; // this must be an array
						if(!is_array($_params)) $_params = array();
					}else{
						$_func = $this->format;
						$_params = array();
					}
					// Check if function exists, some may be present in class extension
					$_ff = ucfirst( strtolower( $_func ) );
					$formatFunction = 'format'.$_ff;
					if (method_exists($this, $formatFunction)){	
						// call function which returns the formatted value and td attribute
						$format = $this->$formatFunction($rawvalue, $_params);
						$value = $format['value'];
						$attr  = $format['attr'];
					}
				}
				
				// INPUT CLASS
				//$readonly = ($active) ? "" : "readonly=\"readonly\"";
				$disabled = ($active) ? "" : "disabled=\"disabled\"";
				$class = $this->inputclass;
				
				// TABINDEX
				if($vertical_first){
					
					$tabindex = $countrows + ($current_col * $nrows);
				}else{
					$tabindex++;					
				}
				
				
				// DEFINE INPUT HTML
				$td = "<input tabindex='".$tabindex."' type='text' ".$disabled." name='cell[".$cellid."]' id='cell_".$cellid."' class='grid-cell ".$class."' value='".$value."' data-value='".$rawvalue."' data-ori-value='".$rawvalue."' data-colname='".$colname."' />";
				$td = (empty($this->tmpl_cell)) ? $td : sprintf($this->tmpl_cell, $td);
				$this->tbody .= "  	<td>".$td."</td>\n";

				$current_col++;
				if($current_col >= $ncols) $current_col = 0;
				
			} // end foreach ths
			
			$this->tbody .= "  </tr>\n";
			
			
		} // end foreach rows
		
		$this->tbody .= " </tbody>\n";
		
 	} // end func
	

	protected function colname_format($value){
		$colname = trim(strtolower($value));
		$colname = preg_replace('/\W/', "_", $colname);
		return $colname;
	}
	
	protected function formatCurrency($value, $params){
		$value = (float) $value;
		$value = number_format($value, 2, ",", "");
		$output['value'] = $value;
		$output['param'] = "";
		return $output;
		
	}
	
}

?>