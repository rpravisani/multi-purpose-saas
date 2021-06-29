<?php

class table_engine{
	
	/*** PUBLIC VARIABLES - SET THEIR VALUES IN MODEL FILE ***/
	
	public $sortme  = ""; // column by which to order the table by default
	public $sortdir = "asc"; // order direction by which to order the table by default

	public $norec_text = "no-rec-found";  // Text (to translate) when no record is found, can be overwritten outside of class
	
	public $filters = array(); // Bi-dim array with the filters (ex. $filters['categories']['category 1] = 1) - values assigned outside of class
	public $default_filter_value = array(); // array with the default value of the various filters (ex. $default_filter_value['categories'] = 1)
			
	public $del = false;  // Switch to show Delete button at the end of the table row
	public $edit = false;  // Switch to show Edit button at the end of the table row - for inline edit
	public $copy = false;  // Switch to show Copy button at the end of the table row
	public $eye = false;  // Switch to show Eye button at the end of the table row - this calls the js function anteprima(record_id) which must be defined in the page javascript
	public $extra_btn = false;
	public $exclude_edit = array(); // array which holds the record ids of those record that cannot be edited or deleted
	public $exclude_edit_warning = "This record cannot be edited"; // text with tooltip text warnign user the record cannot be edited
	public $exclude_del_warning = "This record cannot be deleted"; // text with tooltip text warnign user the record cannot be deleted
	public $exclude_publish_warning = "This record cannot be unpubblished"; // text with tooltip text warnign user the record cannot be deleted
	
	/*** To create one or more buttons with limited use to a single page, use $extra_btn[key][icon|class|title] = value ***
		# key	 		:	is used for the id (key . "_btn" . $row['id']), 
		# icon			:  	font-awesome icon class
		# class			:  	class for function hook
		# title			:  	string used i title of the icon div (tooltip that appears when you position your mouse on the icon)
	    Ex: 
		$extr_btn['price']['icon'] = "fa-facebook";
		$extr_btn['price']['class'] = "setprice";
		$extr_btn['price']['title'] = "Click to set the price of this row";
	*/ 

	public $editablecols = array(); // To make fields in table editable. Array with key = column-name and value = type of field
	
	public $rawfields = false;  // Flag, if true, values will be showed as-is with no formating 
	public $disable_table = false;  // Flag, if true, table will be disabled 
	
	private $removeColumn = array(); // array with the columns to be deleted before creating query (see function constructFields). Values: colalias

	public $nosort = array("photo", "active-column-name", "select", "", "#", "sel"); // columns of the table not to be ordable (can be expanded in class extension)
	public $nosearch = array("photo", "active-column-name", "select", "", "sel"); // columns of the table not to be ordable (can be expanded in class extension)
	public $extra_td = array(); // used in the function  $this->createFormattedData : array with attributes for <td>. Ex. : $extra_td['name'] = "align='center'" where 'name' is a colalias
	public $format = array(); // used to format a field value: Ex.: $this->format['price'] = "money" calls the function formatMoney($value)
	public $thclass = array(); // used to add class to a th field - Ex.: $this->thclass['price'] = "class-name" 
		
	public $serverside = false; // If true a a serverside script will be used to fill the table, to use when dealing with lot's of data (>2000 rows) 

	public $noevid = array ("see", "id", "#", "active", "data", "ora", "file", "ord"); // Fields (colalias) on which not to add search higlighting span
	public $trdata = ""; // Used to ad a data- attribute to <tr> tag - must be an array

	/*** JAVASCRIPT VARIABLES ***/

	public $datatable_class = "datatable"; // class used to hookup javascript

	// Number of rows per table page, can be change with setPageLength(int)function
	private $pageLenght = NUMERO_DEFAULT_RIGHE_TABELLA;	
	
	// TODO: function to alter values of config params and multi dim array handling in getJs func
	private $datatable_options = array(
										"paging" => "true", 
										"stateSave" => "true", 
										"lengthChange" => "false",
										"searching" => "true",
										"ordering" => "true",
										"info" => "true",
										"autoWidth" => "false",										
										"order" => "[[ $('th.sortme').index(), \"%s\" ]]",
										"aoColumnDefs" => "[ { \"bSortable\": false, \"aTargets\": [ 'nosort' ] }, 
															{ \"bSearchable\": false, \"aTargets\": [ 'nosearch' ] },
															{\"sSortDataType\": \"datum\",  \"aTargets\": [ 'datum' ], \"sType\": 'numeric'}, 
															{\"sSortDataType\": \"bytesize\",  \"aTargets\": [ 'bytesize' ], \"sType\": 'numeric'} ]"

									);									
	private $datatable_lang_file = false; // use default (english) text of main js file
	public $forcejs = false;
	
	/*** PRIVATE VARIABLES - FOR INTERNAL USE ***/

	private $qryTpl = ""; // Query template (with %s)
	private $columns = array();  // columns of the table to extract (value assigned through instance)
	private $colalias = array(); // alias of the DB table columns - values assigned outside of class 
	
	private $pageid = 0; // page id (not used for now)
	private $pagename = ''; // pagename (file_name field in pages db.table)
	private $mid = false; // page id of modify / new module
	private $db = false; // DB Object
	private $_t; // Holds translation object
	private $user; // Holds user object
	private $canshow = false; // flag if the user can look at details of  records. Used to print out delete magnifying glass icon
	private $canwrite = false; // LEGACY flag if the user can modify records. Used to print out magnifying glass button
	private $canedit = false; // flag if the user can edit records. Used to print out magnifying glass button
	private $canadd = false; // flag if the user can add records. Used to print out new button and other
	private $candelete = false; // flag if the user can delete records. Used to print out delete button
	private $cancopy = false; // flag if the user can modify copy records. Used to print out copy button
	private $canactivate = false; // flag if the user can activate or disactivate records. Used to print out on/off button
	private $readonly = false; // flag if the user can only view the records. Used to print out magnifying glass button
	
	private $raw_result = false; // array with the raw results extracted from DB
	private $qry = ""; // Holds the full query based on qryTpl with the adition of $columns and $colalias
	private $qry_count = "";  // Queury constructed on qryTpl used to count number of records
	private $oldQryTmp = "";  // Holds backup of query template
	private $oldQry = "";  // Holds backup of full query 
	private $qry_filtered = ""; // Only used in serverside scripting. If defined this querywill be used to filter data / searching
	private $totrecords = -1; // Holds tot number of found records
	public $error = array(); // Holds execution errors
	
	private $table_fields = array(); // Array that holds al data ($this->table_fields[row][column] => value), is populated by $this->getQryResult()
	private $totcols = 0; // Total number of columns (without see and del/copy/eye column)
	
	private $row = array(); // Holds all the td values row per row, column per column (see function $this->createFormattedData() )
	private $attr = array(); // Holds all the td attributes row per row, column per column passed on by $this->extra_td[$key] (see above)
	private $class = array(); // Holds the class values for every td, row per row, column per column
	
	private $show_unread = false; // Switch to set if unread rows must be bold
    
    private $locked_records = array();
	
	/*** END VARIABLES ***/
	
	/*** PUBLIC FUNCTIONS ***/
	
	// Class creation passing page id, page name, id of modify page ,database object and translation object
	public function __construct($pageid, $pagename, $mid, $db, $_t){
		global $_user;
		
		$this->pageid 		= $pageid;
		$this->pagename 	= $pagename;
		$this->mid 			= $mid;
		$this->db 			= $db;
		$this->_t 			= $_t;
		$this->user 		= $_user;
		$this->canshow 		= $_user->canShow();
		$this->canwrite	 	= $canwrite;
		$this->canedit	 	= $_user->canEdit();
		$this->canadd	 	= $_user->canAdd();
		$this->candelete 	= $_user->canDelete();
		$this->cancopy	 	= $_user->canCopy();
		$this->canactivate	= $_user->canActivate();
		$this->readonly	    = $_user->isReadOnly();
        
        $this->locked_records = $this->getLockedRecords($mid);
	}	
    
    public function getLockedRecords($mid){
        global $db;
        
        $qry = "
        SELECT l.record, CONCAT(u.name, ' ', u.surname) AS name 
        FROM page_locks AS l 
        JOIN users AS u ON (u.id = l.user) 
        WHERE l.pid = '".$mid."'
        ";
        
        $locked = $db->fetch_array_indexed($qry, "record"); // default index is id
        
        return ($locked) ? $locked : array();

    }

/*** PROCESS CHAIN ***/

	/*** STEP 1.  ***/
	
	// Takes array with db field names, array with aliases and query template and creates full query ($this->qry)
	// returns nothing
	public function createQuery($columns = array(), $aliases = array(), $qrytpl){
		$this->qryTpl = $qrytpl;
		$this->columns = $columns;
		$this->colalias = $aliases;
		
		// if $this->canactivate is false (cannot write on modify_page) add the active-column-name column to the removeColumn array (which is then used in constructFields function below )
		if(!$this->canactivate)  $this->removeColumn[] = "active-column-name";
		
		if($this->qryTpl != ""){
			// launch function to unite columns ad aliases in csv with fields of DB
			$_fields = $this->constructFields();
			// construct full query
			$this->qry = sprintf ($this->qryTpl, $_fields);
			// contruct count query
			$this->qry_count = sprintf ($this->qryTpl, "COUNT(*)");
			// eliminate group clause from count query if presente
			$pattern = "/GROUP BY[a-zA-Z\., ]+/";
			$this->qry_count = preg_replace($pattern, "", $this->qry_count);
		}else{
			// Let's see if at least $this->qry is set.
			if($this->qry != ""){
				if($this->qry_count == "") die("Error, no COUNT query set");
			}else{
				die("Error, no query set!");
			}
		}
		
	}

	// Combine thead with td and create csv part with which substitute %s in query template
	// called in process chain by createQuery above
	private function constructFields(){
		if(!empty($this->columns)){
			// columns is not empty
			$_cols = array();
			// loop columns
			foreach($this->columns as $k=>$v){ // $k is numeric
				// if alias exist, add " AS..." else just use column name
				if($this->colalias[$k]){
					// if the column is in the removeColumn array I don't add it to the query
					if( in_array($this->colalias[$k], $this->removeColumn) ){
						unset($this->colalias[$k]);
					}else{
						$_cols[$k] = $v." AS '".$this->colalias[$k]."'";
					}
				}else{
					$_cols[$k] = $v;
				}
			}
			// return comma separted
			return implode(" , ", $_cols);
		}else{
			// return select all....
			return "*";
		}
	}
	
	/*** STEP 2 ***/
	
	// Function to get complete table html. To be called after createQuery
	public function getTable($translate = true, $width = '100%', $cellspacing = '1', $cellpadding = '5', $class = 'table-bordered ', $hilite = false){
		// check for cached data...
		$cachedData = $this->getCachedData();	
		if( $cachedData ) return cachedData;
		// if no valid cached data is available proceed as always
		$tbody = $this->getTbody($hilite);
		$thead = $this->getThead($translate);
		$class .= "table ".$this->datatable_class;
		$table = "<table data-page-length=\"".$this->pageLenght."\" width=\"".$width."\" cellspacing=\"".$cellspacing."\" cellpadding=\"".$cellpadding."\" class=\"".$class."\" id=\"table-".$this->pagename."\">\n";
		$table .= $thead;
		$table .= $tbody;
		$table .= "</table>\n";
		return $table;
	}

	// Function to get full thead code with translated th. To be called after createQuery
	public function getThead($translate = true){
		
		$thead = "<thead>\n<tr>\n";

		if ($this->totrecords > 0){ // There are records
						
			// get array of al the column aliases (non translated)
			$ths = $this->getAliases();
			
			// if sortme variable is empty set it to the third alias (normaly first = id and second = see)
			if(empty($this->sortme)) $this->sortme = $ths[2];
	
			foreach ($ths as $th){
				// empty class holder string and alignment of th field
				$thclass = $thalign = "";
				
				// if magnifying glass column but don't have show permissions skip this loop iteration
				if($th == "see" and !$this->canshow) continue;
				
				if ($th != "id" and substr($th, 0, 1) != "_"){
				
					if(substr(strtolower($th), 0, 4) == "data") $thclass = "datum";
					if($th == "ord") $thclass = "order";
					if($th == "dim") $thclass = "bytesize";
					// if the variable $this->thclass[$th] is not empty, the classname will be passed to th, overwrite the default ones
					if(!empty($this->thclass[$th])) $thclass = $this->thclass[$th];
					
					// if is the see column leave th blank
					if($th == "see") $th = "";
					
					// see if there's an alias enclosed by parentheses, in that case remove parentheses
					if (preg_match("/^\(\w+\)$/", $th)) {
						$th = substr($th, 1, -1);
					}

					if($th == "active" or $th == "checkbtn") $thalign = " align='center'";
					if($th == "checkbtn") $th = "sel";
					
					if(in_array($th, $this->nosort)){
						$thclass .= " nosort";
					}else if($th == $this->sortme){
						$thclass .= " sortme ".$this->sortdir;					
					}
					if(in_array($th, $this->nosearch)){
						$thclass .= " nosearch";
					}
					
					// translate th
					$th = ($translate and !empty($th) and $th != "#") ? $this->_t->get($th) : $th;					
					
					if($thclass != ""){
						$thead .= "<th class='".$thclass."'".$thalign.">".$th."</th>";
					}else{
						$thead .= "<th".$thalign.">".$th."</th>";
					}
									 
				} // end $th != "id" and substr($th, 0, 1) != "_"			
		
			} // end foreach $ths
	
			if($this->del or $this->copy or $this->extra_btn or $this->edit){
				if($this->copy and $this->cancopy) $lastcol[] = $this->_t->get('table-engine-th-copy');
				if($this->edit and $this->canedit) $lastcol[] = $this->_t->get('table-engine-th-edit');
				if($this->eye) $lastcol[] = $this->_t->get('table-engine-th-eye');
				if($this->del and $this->candelete ) $lastcol[] = $this->_t->get('table-engine-th-delete');
				if($this->extra_btn){
					foreach($this->extra_btn as $k=>$v){
						$lastcol[] = ($translate) ? $this->_t->get('table-engine-extra-button-'.$k) : $k;
					}
				}
				if(!empty($lastcol)){
					$thead .= "<th class='nosort' align='center'>";
					$thead .= (implode(" / ", $lastcol));
					$thead .= "</th>\n";
				}
			}
	
		}else{
			// no records found
			$thead .= "<td class='nosort text-red' align='center'><em><strong>".$this->_t->get("no-rows-found")."</strong></em></td>\n";
		}
        $thead .= "</tr>\n</thead>\n";
		return $thead;

	}

	// Function to get full tbody code with all the formatted data. To be called after createQuery
	// Calls in cascade getFormattedData("table") > createFormattedData() > getQryResult() > execQry()
	public function getTbody($hilite = false){
		$tbody = $this->getFormattedData("table", $hilite);
		return "<tbody>\n".$tbody."\n</tbody>\n";
	}

	// creates ready for output string in various formats: raw (just the array), table (without thead) (default)
	// Calls in cascade createFormattedData() > getQryResult() > execQry()
	public function getFormattedData($what="table", $hilite = false){
		
		// if not already called, call createFormattedData to get ready formatted data
		if(empty($this->row)) $this->createFormattedData(); 
		
		// got data...
		if($this->row){
			// let's see how to output it
			switch($what){
				default:				
					// the $this->row array as-is
					return $this->row;
					break;
				case "table":
					// la riga completa da scrivere direttamente a video
					$out = "";
					
					// see if unread rows must be marked (DA VEDERE) => creates array with al the unread rows (ids)
					if($this->show_unread){ 
						$read = $this->db->col_value ("record", TABELLA_VISTI, "WHERE modulo = '".$this->modulo."' AND user = '".$_SESSION['login_id']."'");
						if(!$read) $read = array();
					}
					
					/*** LOOP ROWS ***/
					
					foreach($this->row as $r=>$td){
	
						/*** OPEN <TR> ***/
						$out .= "<tr";
						if($this->show_unread){
							// if record is unread and record is not entered by current user, add unread class to tr 
							if(!in_array($td['DT_RowId'], $read) and $td['owner'] != $_SESSION['login_id']) $td['DT_RowClass'] .= " unread";
						}
						if(!empty($td['DT_RowId'])) $out .= " id=\"".$td['DT_RowId']."\""; // Write id in tr TODO add tr els it's just a number...
						if(!empty($td['DT_RowClass'])) $out .= " class=\"".$td['DT_RowClass']."\""; // Write tr class
						
						// let's see if we've got to ad a data- attribute to the tr tag ($this->trdata must be an array)
						if($this->trdata){
							foreach($this->trdata as $tdk=>$tdv){ // tdv = name of variable to enter as data (see below)
								$out .= " data-".$tdk."='".$$tdv."'";
							}
						}
						$out .= ">\n";
						
						
						/*** LOOP COLUMNS ***/
						foreach($td as $key=>$value){
							// filter out system columns
							if($key != "DT_RowId" and $key != "DT_RowClass" and $key != "owner"){
								// open td
								$out .= "  <td";
								
								// let's see if we need a data-order attrib
								if($value != $this->raw[$r][$key]){
									$out .= "  data-order='".$this->raw[$r][$key]."'";
								}
								
								
								// ad attributes
								if(!empty($this->attr[$r][$key])) $out .= " ".$this->attr[$r][$key];
								$extra_td_class = (empty($this->editablecols[$key])) ? "" : " inline-edit-field";
								if(!empty($this->editablecols[$key])) $out .= " data-inline-type=\"".$this->editablecols[$key]."\"";
								if(!empty($this->class[$r][$key])) $out .= " class=\"".$this->class[$r][$key].$extra_td_class."\"";
								$out .= ">";
								$out .= $value;
								$out .= "</td>\n";
							}
						}
						/*** CLOSE <TR> ***/
						$out .= "</tr>\n";
					}
					return $out;
					break;
					
				case "array":
					// array for server side without the extra data
					$out = array();
					foreach($this->row as $r=>$campi){
						foreach($campi as $key=>$value){
							if($key != "DT_RowId" and $key != "DT_RowClass"){
								$out[$r][] = $value;
							}else{
								$out[$r][$key] = $value;
							}
						}
					}
					return $out;
					break;
					
				case "fullarray":
					// array for server side with extra data 
					$out = array();
					// loop begin					
					foreach($this->row as $r=>$campi){
						$col = -1;
						foreach($campi as $key=>$value){
							
							if($key != "DT_RowId" and $key != "DT_RowClass"){
								if($hilite and !in_array($key, $this->noevid)){
									if($key == "Email"){
										$value = $_ie = strip_tags($value);
										$pattern = '/('.$hilite.')/i';
										$replacement = '<span class="hilite">$1</span>';
										$value = preg_replace($pattern, $replacement, $value);
										$value = "<a href='".$_ie."'>".$value."</a>";								
									}else if(strip_tags($value) == $value){
										$pattern = '/('.$hilite.')/i';
										$replacement = '<span class="hilite">$1</span>';
										$value = preg_replace($pattern, $replacement, $value);								
									}else{
									}
								}
								
								$out[$r][$col]['data'] = $value;
								$out[$r][$col]['attr'] = $this->attr[$r][$key];
								$out[$r][$col]['cssclass'] = $this->class[$r][$key];
								$col++;
							}else{
								$out[$r][$key] = $value;
							}
							
						}
					}
					return $out;
					break;
			}
			
		}else{
			// don't have any data... return no record found message (defined outside class)
			if(DEBUG){
				return $this->error['debug_msg'];
			}else{
				return $this->error['safe_msg'];
			}
		}
	}	


	/*************************************************************************************************************************
	 * Loops $this->table_fields and populates al variables and arrays with the necessary info and formats values of td cells
	 * Populates:
	 * $this->row[row][column] 		=> holds all the td values row per row, column per column
	 * $this->attr[row][column] 	=> holds all the td attributes row per row, column per column
	 * $this->class[row][column] 	=> holds the class values for every td, row per row, column per column
	 * Calls in cascade getQryResult() > execQry()
	*************************************************************************************************************************/
	private function createFormattedData(){
		
		if($this->totcols == 0) $result = $this->getQryResult(); // try to elaborate query results (see function below) 
		
		if($result){
			$previous = 0;
			
			// loop through rows
			for ($r=0; $r<$this->totrecords; $r++){
				
				$row = $this->table_fields[$r];
				
				/*** ROW / TR CLASS AND ID ***/
				// Row (tr) class
				$rowclass = "";
				if(!empty($row['_tr'])) $rowclass .= "trclass_".$row['_tr'];  // se è definito il campo alias _tr prendo il suo valore e lo aggiungo insieme al prefisso trclass_ alla riga della tabella				
				
				// Assign class to tr
				if(!empty($rowclass)) $this->row[$r]['DT_RowClass'] = $rowclass;
				
				// Assign the record id to <tr> tag
				if(!empty($row['id'])) $this->row[$r]['DT_RowId'] = $row['id'];

				// Assign record owner to tr
				$this->row[$r]['owner'] = (!empty($row['_user'])) ? $row['_user'] : "";
				
				/*** LOOP THROUGH COLUMNS ***/
				foreach ($row as $key=>$value){
					
					if($key == "see" and !$this->canshow) continue;
					
					// initiate empty string for tr attributes
					$attr = "";
					
					$rawvalue = $value;
					
					// let's see if there are td attributes to be set (align, valign etc)
					if(!empty($this->extra_td[$key])){ // extra_td is defined outside class
						$attr = $this->extra_td[$key];
					}
					
					// exclude id field and fields that begin with underscore '_'
					if ($key != "id" and substr($key, 0, 1) != "_"){
						
						// serialized value? DA VEDERE....
						if (preg_match("/^\(\w+\)$/", $key)) {						
							
							$uv = unserialize($value);
							if($uv){
								if(is_array($uv)){
									$value = $uv[0];
								}else{
									$value = $uv;
								}	
							}
							$key = substr($key, 1, -1);
							$key = ucfirst($key);
						}
						
						/*** FORMAT DATA BASED ON THE COLUMN NAME (STANDARD FIELDS LIKE active, checkbox etc) ***/
						// active button DA VEDERE
						if ($key == "active-column-name"){
							if($attr == "") $attr = "align='center'";
							$_af = $this->formatOnoff($value, array("record" => $row['id']));
							$value = $_af['value'];
							
						}
						
						// fields that start with date. DA VEDERE per formattazione data in base a nazione
						if (substr($key, 0, 4) == "date"){
							if($value){
								
								if(is_int($value)){
									//is epoch
									$value = date('d/m/y', $value);
								}else{
									
									$tmp = $this->formatDate($value, array());
									$value = $tmp['value'];
									unset($tmp);
									
									//$value = ccDateTime($value); // function in functions (other args are format and string to return on error)
								}
							}else{
								$value = "---";
							}							
						}
						
						// fields that start with time.
						if (substr($key, 0, 4) == "time"){
							$hr = explode(":", $value);
							if($hr[0] == '00'){
								$value = "--";
							}else{
								$value = $hr[0].":".$hr[1];
							}
						}
						
						// if the name of the field is email and the flag rawfields is false and value is not null				
						if ($key == "email" and !$this->rawfields and !empty($value)) $value = "<a href='mailto:".strtolower($value)."'>".strtolower($value)."</a>";
						if ($key == "emails" and !$this->rawfields and !empty($value)){
							$email = $this->formatUnserialize($value, array());
							$attr  = $email['attr'];
							$value = "<a href='mailto:".strtolower($email['value'])."'>".strtolower($email['value'])."</a>";
						}
						
												
						if ($key == "star"){
							if($attr == "") $attr = "align='center'";
							
							if($value == "1"){
								$value = '<button class="btn btn-xs star active" data-id="'.$row['id'].'" data-status="active"><i class="fa fa-fw fa-star"></i></button>';
							}else{
								$value = '<button class="btn btn-xs star nonactive" data-id="'.$row['id'].'" data-status="nonactive2><i class="fa fa-fw fa-star"></i></button>';
							}
						}
						
						// The order column
						if ($key == "ord") { 
							$value = "<input type='text' class='ordine' name='ordine[".$row[id]."]' id='ordine[".$row[id]."]' value='".$value."' onchange='cambiaOrdine(".$row[id].", this);'>";
							if($attr == "") $attr = "align='center'";
						}
						
						// create checkbutton FORSE DA SPOSTARE IN FUNZIONI
						if($key == "checkbtn"){
							$checked = $disabled = "";
							$value = '<input type="checkbox" name="check['.$value.']" id="check_'.$value.'" class="rowcheck" value="'.$value.'" '.$disabled.' '.$checked.'>';
							if($attr == "") $attr = "align='center'";
						}
						
						// magnifying glass in table - fundamental
						if($key == "see"){
							$value = '<i data-view="html" data-action="update" data-pid="'.$this->mid.'" data-record="'.$row['id'].'" class="goto fa fa-search"></i>';
                            if(!empty($this->locked_records)){
                                
                                if( in_array($row['id'], array_keys($this->locked_records) ) ){
                                    $locker_name = $this->locked_records[$row['id']]['name'];
                                    $value = '<i class="fa fa-lock text-red" title="Record bloccato da utente '.$locker_name.'" data-toggle="tooltip" data-placement="right" ></i>';
                                }
                                
                            }
							if($attr == "") $attr = "align='center'";
						}
						
						/*** FORMAT FUNCTION DYNAMIC CALL ***/						
						if( !empty($this->format[$key]) ){
							if( is_array( $this->format[$key] ) ){
								$_func = $this->format[$key]['func'];
								$_params = $this->format[$key]['params']; // this must be an array
								if(!is_array($_params)) $_params = array();
							}else{
								$_func = $this->format[$key];
								$_params = array();
							}
							// Check if function exists, some may be present in class extension
							$_ff = ucfirst( strtolower( $_func ) );
							$formatFunction = 'format'.$_ff;
							if (method_exists($this, $formatFunction)){	
								// call function which returns the formatted value and td attribute
								$format = $this->$formatFunction($value, $_params);
								$value = $format['value'];
								$attr  = $format['attr'];
								if(isset($format['raw'])) $rawvalue = $format['raw'];
							}
						}
						
						// default attribute value (if none is previously set)
						if($attr == "") $attr = "align='left'";
						
						// the td class
						//$tdclass = $this->keyToClass($key);
						$tdclass = $key;
												
						$this->raw[$r][$key]  = $rawvalue; 						
						$this->row[$r][$key]  = $value; 						
						$this->attr[$r][$key] = $attr;
						$this->class[$r][$key] = $tdclass;																
					}

				} // end foreach column
				
				
				/*** Add delete / copy or extra button column DA RIVEDERE ***/
				if($this->del or $this->copy or $this->eye or $this->extra_btn or $this->edit){
					$attr = "align='center'";
					$eb = array();
					
					if($this->copy){
						//$eb[] = '<button class="btn btn-xs btn-primary copy" data-id="'.$row['id'].'"><i class="fa fa-fw fa-files-o"></i></button>';
						if($this->cancopy ) $eb[] = '<i class="puls copy fa fa-fw fa-files-o text-light-blue"></i>';
					}
					if($this->edit){
						//$eb[] = '<button class="btn btn-xs btn-primary copy" data-id="'.$row['id'].'"><i class="fa fa-fw fa-files-o"></i></button>';
						if($this->canedit) $eb[] = (in_array($row['id'], $this->exclude_edit)) ? '<i data-toggle="tooltip" title="'.$this->exclude_edit_warning.'" class="fa fa-fw fa-pencil text-muted cursor-not-allowed"></i>' : '<i class="puls inline-edit fa fa-fw fa-pencil text-green"></i>';
					}
					if($this->eye){
						//$eb[] = '<button class="btn btn-xs btn-info eye" data-id="'.$row['id'].'"><i class="fa fa-fw fa-eye"></i></button>';
						$eb[] = '<i class="puls eye fa fa-fw fa-eye text-muted"></i>';
					}	
					if($this->del){
						// add delete button only if the user has delete permissions
						if($this->candelete) $eb[] = (in_array($row['id'], $this->exclude_edit)) ? '<i data-toggle="tooltip" title="'.$this->exclude_del_warning.'" class="fa fa-fw fa-trash text-muted cursor-not-allowed"></i>' : '<i class="puls delete fa fa-fw fa-trash"></i>';
					}
					if($this->extra_btn){
						// expect $this->extra_btn to be array : $this->extra_btn['name'] = title
						
						foreach($this->extra_btn as $ebid=>$ebtitle){
							$eb[] = '<button class="btn btn-xs btn-default '.$ebid.'Btn" data-id="'.$row['id'].'" title="'.$ebtitle.'"><i class="fa fa-fw"></i></button>';
						}
					}
					if(!empty($eb)){
						$this->row[$r]['_btns'] = implode("&nbsp;", $eb);
						$this->attr[$r]['_btns'] = $attr;
						$this->class[$r]['_btns'] = "";
					}
					
				}
				
			} //fine for ($r=0; $r<$this->totrecords; $r++)
			
			return true;	
		
		}else{
			return false;  // query non a buon fine
		}
	}



	/*** get data from db and returns array with results 
		 $this->table_fields[row][column] => value;
		 $this->totrecords => Number of records / rows
		 $this->totcols => total number of columns (without del/copy/extra button column)
	***/
	// Called in process chain by createFormattedData above
	// Calls in cascade execQry()
	
	public function getQryResult($return= false){
		if (!$this->raw_result) $this->execQry(); // calls execQry which extracts data from DB, if not already called
		
		// if I've got a result...
		if ($this->raw_result){
			// get total records
			$this->totrecords = count($this->raw_result);
			
			if(!empty($this->totrecords)){ // not 0
				
				// loop rows
				for ($r=0; $r<$this->totrecords; $r++){
					
					// loop columns / fields
					foreach ($this->raw_result[$r] as $col=>$value){
						
						// system columns are being filtered out the rest is memorized in array[row][column]
						if ($key != "ts") $this->table_fields[$r][$col] = $value;
						
					}
				}
				
				// get total number of columns
				$this->totcols = count($this->table_fields[0]);
				
				if($return){
					return $this->table_fields;
				}else{
					return true;
				}
					
				
			}else{
				// totrecords = 0 - return no record found message DA VEDERE (TRADUZIONE?)
				$this->error['type'] = "0-rec";
				$this->error['debug_msg'] = "No record found for query <em>".$this->getQry(false)."</em>";
				$this->error['safe_msg'] = $this->_t->get('table-engine-no-record-found');
				return false;
			}
			
		}else{
			// no raw result
			$this->error['type'] = "no-result";
			return false;
			
		}
	}


	// Executes the query and memorizes the raw results (as-is from DB) in $this->raw_result
	// called in process chain by getQryResult above)
	// createQuery must have been called before calling this function
	public function execQry(){	
		if(empty($this->qry)){
			$this->error['debug_msg'] = "<p style='color: #f00; font-weight: bold; margin-bottom: 10px;'>function execQry() : No query set!</p>\n<p style='font-style: italic;'><strong>Query:</strong> ".$this->qry."</p>\n";
			$this->error['safe_msg'] = "<p style='color: #f00; font-weight: bold;'>".$this->_t->get('table-engine-no-query-set')." (Err #: TE-".__LINE__.")<p>";
			return false;
		}
		$this->raw_result = $this->db->fetch_array($this->qry, MYSQLI_ASSOC);
		if($this->raw_result){
			return true;
		}else{
			// something went wrong...
			$this->error['sqlstatus'] = $this->db->getError('sqlstate');
			if(!empty($this->error['sqlstatus'])){
				$this->error['debug_msg'] = "<p style='color: #f00; font-weight: bold; margin-bottom: 10px;'>".$this->db->getError('msg')."</p>\n<p style='font-style: italic;'><strong>Query:</strong> ".$this->qry."</p>\n";
				$this->error['safe_msg'] = "<p style='color: #f00; font-weight: bold;'>".$this->_t->get('table-engine-no-result')." (Cod. Err: TE-".__LINE__.")<p>";
			}else{
				$this->error['debug_msg'] = "";
				$this->error['safe_msg'] = "";
			}
			return false;
		}
	}
	

/*** END PROCESS CHAIN ***/


	// returns query o query template depending on value of param
	public function getQry($template=true){
		if($template){
			return (empty($this->qryTpl)) ? false : $this->qryTpl;
		}else{
			return (empty($this->qry)) ? false : $this->qry;
		}
	}

	// returns count query 
	public function getCQry(){
		return (empty($this->qry_count)) ? false : $this->qry_count;
	}
	
	// bypass normal count query creation, passing count query by hand
	public function setCQry($qry){
		$this->qry_count = $qry;
	}
	
	// returns string eith filterd query (used in serverside)
	public function getFilterQry(){
		return (empty($this->qry_filtered)) ? false : $this->qry_filtered;
	}
	
	// overwrite existing query, if $template = true, we presume $qry is the query template and constructQry will be called
	public function setQry($qry, $template=true, $backup=true){
		if(!empty($qry)){
			if($template){ // $qry = template
				if($backup and !empty($this->qryTpl)) $this->oldQryTpl = $this->qryTpl;
				$this->qryTpl = $qry;
				$this->constructQry();
			}else{
				if($backup and !empty($this->qry)) $this->oldQry = $this->qry;
				$this->qry = $qry;
			}
			return true;
		}else{
			return false;
		}
	}
	

	// returns total number of records
	public function getTotRecs(){ 
		if($this->totrecords != -4){ // hack to make it always generate
			if($this->qry_count != ""){
				$output = $this->db->fetch_array_row($this->qry_count, MYSQLI_NUM);
				if($output){
					$this->totrecords = $output[0];
					return $this->totrecords;
				}else{
					return "Error: ".$this->db->getError()."<br>\n".$this->qry_count;
				}
			}else{
				$this->totrecords = count($this->table_fields);
				return $this->totrecords;
			}
		}else{
			return $this->totrecords;
		}
	}
	
	// returns an array with all the config params
	public function getParams(){
		$out = array(
			"del" 					=> $this->del,
			"del" 					=> $this->del,
			"copy" 					=> $this->copy,
			"eye" 					=> $this->eye,
			"extra_btn" 			=> $this->extra_btn,
			"sortme" 				=> $this->sortme,
			"sortdir" 				=> $this->sortdir,
			"rawfields" 			=> $this->rawfields,
			"disable_table" 		=> $this->disable_table,
			"filters" 				=> $this->filters,
			"default_filter_value" 	=> $this->default_filter_value,
			"nosort" 				=> $this->nosort,
			"extra_td" 				=> $this->extra_td,
			"norec_text" 			=> $this->norec_text, 
			"format" 				=> $this->format, 
			"thclass" 				=> $this->thclass, 
			"noevid" 				=> $this->noevid, 
			"trdata" 				=> $this->trdata, 
			"serverside" 			=> $this->serverside
		);
		return $out;
	}


	// get aliases (defined outside of class) . Optional translated and/or filtered
	public function getAliases($translate = false, $filter = "all"){
		if(!empty($this->colalias)){
			if($filter == "all"){
            	if($translate){
                    foreach($this->colalias as $alias){
                        $translated[] = $_t->get($alias);
                    }
					return $translated;
                }else{
                return $this->colalias;
                }
			}else{
				return ($translate) ? $_t->get($this->colalias[$filter]) : $this->colalias[$filter];
			}
		}else{
			return false;
		}
	}

	// returns db query fields
	public function getFields(){
		if(!empty($this->columns)){
			return $this->columns;
		}else{
			return false;
		}
	}


/*** DATA-CACHE FUNCTIONS ***/

	private function getCachedData(){
		return false; // TODO!
	}
	
	
/*** JS FUNCTIONS ***/

	public function getJs(){
		if($this->totrecords < 1 && !$this->forcejs) return false;
		$js  = "$(function () {\n";	
		$js .= "  dtable = $('.".$this->datatable_class."').DataTable({\n";
		foreach($this->datatable_options as $k=>$v){
			if($k == "order") $v = sprintf ($v, $this->sortdir);
			$js .= "    \"".$k."\": ".$v.",\n";
		}
		if($this->datatable_lang_file){
			$js .= "    \"language\": { \"url\": \"".$this->datatable_lang_file."\" },\n";
		}
		$js = substr($js, 0, -2)."\n";
		$js .= "  });\n";
		$js .= "});\n";
		return $js;
	}
	
	// overwrite default value of an option printed in script section
	public function setOption($key, $value){	
		$this->datatable_options[$key] = $value;
		return true;
	}
	
	// change language of table. INPUT: 2-letter lang code. Sets name of language file if it exists, else default lang
	public function setLang($lang){
		$lang_code = ($lang == "en") ? "GB" : strtoupper($lang);
		$filename = $lang."_".$lang_code.".txt";		
		if(file_exists(FILEROOT.PATH_DATATABLE_JS."languages/".$filename)){
			$this->datatable_lang_file = SITEROOT.PATH_DATATABLE_JS."languages/".$filename;
		}
	}
	
	// NEW removeColumns FUNCTION (mind the s at the end!): queues the columns to be deleted - accepts array
	public function removeColumns($cols){
		if(empty($cols)) return false;
		if(!is_array($cols)) $cols = array($cols);
		foreach($cols as $col){
			$this->removeColumn[] = $col;
		}
		return true;
	}

	/*** THESE 2 DON'T WORK --  HAVE TO RESEE LOGIC OF QUERY GENERATION
	// Remove a column from the table (before execution of query) based on the alias
	public function removeColumn($search){
		if(in_array($search, $this->colalias)){
			$key = array_search($search, $this->colalias);
			unset ($this->colalias[$key]);
			unset ($this->colonne[$key]);
			return true;
		}else{
			return false;
		}
	}

	// Rename alias column
	public function renameColumn($oldname, $newname){
		if(in_array($oldname, $this->colalias)){
			$key = array_search($oldname, $this->colalias);
			$this->colalias[$key] = $newname;
		}else{
			return false;
		}
	}
	 ***/
		

/*** HELP FUNCTIONS ***/
	
	// Sanitizes string to use as classname
	private function keyToClass($key){
		$chars_to_delete = array(".", ",", "#");
		$chars_to_substitute = array(" ", "_");
		$class = str_replace($chars_to_delete, "", $key);
		$class = str_replace($chars_to_substitute, "-", $class);
		return strtolower($class);
	}
	
	
	// returns the query part of a url associative array (used by urlQuery function below)
	private function dissectUrlQry($url, $whole = true){
		// set $whole = true if $url is compelte url and not just the query (after '?') part
		if($whole){
			$parts = explode("?", $url);
			if(count($parts) == 1) return false;
			$disect = $parts[1];
		}else{
			$disect = $url;
		}
		$qryelems = explode("&", $disect);
		foreach($qryelems as $qrypart){
			list($k[], $v[]) = explode("=",$qrypart);
		}
		$out['key'] = $k;
		$out['value'] = $v;
		return $out;
	}
	
	// constructs url
	private function urlQuery($url){
		if( empty($_SERVER['QUERY_STRING']) ){
			return "";
		}else{
			$newurl = $this->dissectUrlQry($url); // returns bi-dim array
			if($newurl){
				$nk = $newurl['key'];
				$urlstring = "";
			}else{
				$nk = array();
				$urlstring = "?";
			}
			
			$oldurl = $this->dissectUrlQry($_SERVER['QUERY_STRING'], false);
			foreach($oldurl['key'] as $i=>$k){
				if( !in_array($k, $nk) ) $ue[] = $k."=".$oldurl['value'][$i];
			}
			if(empty($ue)) return "";
			$urlstring .= implode("&", $ue);
			return $urlstring;
		}
		
	}
	
	public function setStateSave($param){
		if($param === true){
			$this->datatable_options['stateSave'] = "true"; // must return a string value for output
		}else{
			$this->datatable_options['stateSave'] = "false"; // must return a string value for output
		}
	}

	// returns checkboxes wrapped in pull-right div to quickly filter the table (no reload)
	public function quickFilters($filters){
		if(empty($filters)) return "";
		$out = "";
		foreach($filters as $k=>$v){
			// key = id, value = label. Id must be column name (td class)
			$out .= "<span class=\"checkbox icheck\">\n";
			$out .= "<input type=\"checkbox\" data-col=\"".$k."\" value='1' checked >\n";
			$out .= "&nbsp;".$v."\n";
			$out .= "</span>\n&nbsp;";
		}
		$out = "<div class='pull-right quickfilters'>".$out."</div>";
		return $out;
	}
	
	// change the numer of orws per table page. Min 2, max 100
	public function setPageLength($l){
		$l = (int) $l;
		if($l < 2 or $l > 100) return false;
		$this->pageLenght = $l;
	}
	
	
	
/*** FORMAT FUNCTIONS ***
***  # The names of the functions must always be formed by 'format' plus the name of the format with capital first letter
***  # They must always have 2 parameters: $value and $params, the second one must be defined in the function but my not be used in the function itself
***  # They msut always return an array with the keys 'value' and 'attr' - the first is the content of td the second is his attribute (ex. align='right')
***/	
	
	// leaves value as is, adds attribs via $params
	protected function formatBypass($value, $params){
		$out['value'] = $value;
		$out['attr']  = (empty($params['attr'])) ? "align='left'" :  $params['attr'];
		return $out;
	}
	
	// equivalent of PHP str_pad with default: space to the left. Length mandatory
	protected function formatPadString($value, $params){
		
		if(!isset($params['length'])) return $value;
		$length = (int) $params['length'];
		
		$string = (!isset($params['string'])) ? ' ' : (string) $params['string'];
		
		$side = (!isset($params['side'])) ? 'left' : (string) $params['side'];
		$side = strtolower($side);
		
		switch($side){
			case 'right':
				$side = STR_PAD_RIGHT;
				break;
			case 'both':
				$side = STR_PAD_BOTH;
				break;
			case 'left':
			default: 
				$side = STR_PAD_LEFT;
				break;
		}		
		
		$out['value'] = str_pad($value, $length, $string, $side);
		$out['attr']  = (empty($params['attr'])) ? "align='left'" : (string) $params['attr'];
		return $out;
	}

	

	// sets on/off button - to be used for publishing / unpublishing of record
	protected function formatOnoff($value, $params){
		$class = "onoff";
		$onoff = ($value === '1') ? "on" : "off";
				
		$out['value'] = (in_array($params['record'], $this->exclude_edit)) ? "<i data-toggle='tooltip' title='".$this->exclude_publish_warning."' class=\"fa fa-toggle-on text-muted cursor-not-allowed\"></i>" : "<i data-onoff=\"".$onoff."\" class=\"fa fa-toggle-".$onoff." ".$class."\"></i>";
		
		$out['attr'] =  "align='center'";
		return $out;
	}

	// on/off button for custom purposes (NOT for publishing / unpublishing  of record)
	protected function formatSwitch($value, $params){
		$title = $class = "switcher";
		if(!empty($params)){
			$class .= " ".trim($params['class']);
			$title = ($value === '1') ? $params['title_on'] : $params['title_off'];
			if(!empty($params['class_on']) and $value === '1') $class .= " ".$params['class_on'];
			if(!empty($params['class_off']) and $value === '0') $class .= " ".$params['class_off'];
		}
		if(!empty($title)) $title = "title='".$title."' data-toggle='tooltip'";
		$onoff = ($value === '1') ? "on" : "off";
		
		$out['value'] = "<i ".$title." data-onoff=\"".$onoff."\" class=\"fa fa-toggle-".$onoff." ".$class."\"></i>";
		
		$out['attr'] =  "align='center'";
		return $out;
	}

	// display check if true empty if not
	protected function formatCheck($value, $params){
		$out['value'] = ($value !== '0') ? "<i class=\"fa fa-check\"></i>" : "";
		$out['attr'] =  "align='center'";
		return $out;
	}

	// leaves value as is, adds data- attribs (params must be an array)
	protected function formatInjectdata($value, $params){
		$out['value'] = $value;
		foreach($params as $k=>$v){
			$attr[] = "data-".$k."=\"".$v."\"";
		}
		$out['attr']  = implode(" ", $attr);
		return $out;
	}
	
	// truncates the content of td according to $params['leng'], keeps original length text in invisible span
	protected function formatTruncate($value, $params){
		$leng   = (!isset($params['leng'])) ? '70' : $params['leng'];
		
		if(!isset($params['strip'])) $value = strip_tags($value, "br");
		
		if(strlen($value) > $leng) {
			$all = $value;
			$value = substr($value, 0, $leng)."...";
			$value .= "<span style='display: none'>".$all."</span>";
		}
		
		$out['value'] = $value;
		$out['attr']  = "";
		return $out;
		
	}
	
	// link to a custom (internal) page
	protected function formatCustomSee($value, $params){
		
		$values = explode("|", $value);
		if(empty($values) or !is_array($values)) return "";
		
		$pid = (int) $params['pid'];
		if(empty($pid)) return "";
		
		$view = (empty($params['view'])) ? "html" : $params['view'];
		$action = (empty($params['action'])) ? "" : $params['action'];
		$fraction = (empty($params['fraction'])) ? "" : $params['fraction'];
		
		$keys = $params['keys'];
		if(empty($keys) or !is_array($keys)) $keys = array("r");
		
		$url = "cpanel.php?pid=".$pid;
		if(!empty($view)) $url .= "&v=".$view;
		if(!empty($action)) $url .= "&a=".$action;
		
		foreach($keys as $n => $key){
			$url .= "&".$key."=".$values[$n];
		}
		
		if(!empty($fraction)) $url .= "#".$fraction;
			
		$out['value'] = "<a href=\"".$url."\"><i class=\"fa fa-search text-primary\"></i></a>\n";	
		
		$out['attr']  = "align='center'";
		return $out;
		
	}
	
	// formats number for money display
	protected function formatMoney($value, $params){
		$ndec   = (!isset($params['dec'])) ? '2' : $params['dec'];
		$ddec   = (!isset($params['sepdec'])) ? ',' : $params['sepdec'];
		$dmil   = (!isset($params['sep1000'])) ? '.' : $params['sep1000'];
		$symbol = (!isset($params['symbol'])) ? '€' : $params['symbol'];
		$value = number_format( (float) $value, $ndec, $ddec, $dmil);
		$out['value'] = $symbol." ".$value;
		$out['attr']  = "align='right'";
		return $out;
	}

	// Creates thumbnail with lightbox click
	protected function formatPhoto($value, $params){
		$photo = $this->db->col_value ($params['filename-column'], $params['photo-table'], "WHERE ".$params['id-column']. " = '".$valore."' ORDER BY ".$params['order']);
		if($photo){
			$nphoto = count($photo);
			$first_photo = "<div class='overlay'></div><a class='lightbox' rel='gal_".$value."' href='../".PATH_FOTO.$photo[0]."'><img src='' class='nascosto' data-prova='required/img.php?file=".JOOMLA_FILEROOT."foto/".$foto[0]."&w=59&h=59&fc=fff' /></a><br />\n";
			$first_photo .= "<small>Tot. ".$nphoto." foto</small>\n";
			for($i=1; $i<$nphoto; $i++){						
				$first_photo .= "<a class='lightbox' rel='gal_".$value."' href='../".PATH_FOTO.$photo[$i]."'></a>\n";
			}
			$out['value'] = $first_photo;
		}else{
			$out['value'] = "No photo";
		}
		
		$out['attr']  = "align='center'";
		return $out;
	}
	
	// create a checkbox. Value is used to determine if checked
	protected function formatCheckbox($value, $params){
		$cb = "<input type='checkbox' value='1' class='".$params['class']."'";
		if(!empty($value)) $cb .= "checked";
		$cb .= ">";
		$out['value'] = $cb;
		$out['attr']  = "align='center'";
		return $out;
	}

	// creates a x-numbered star for valuation, value determines how many stars are on, 
	// params are: 'max' number of stars, 'inactive' to activate o disactivate stars
	protected function formatMultistar($value, $params){
		$tot = ($params['max']) ? (int) $params['max'] : 5;
		$selected = (int) $value;
		if($selected > $tot) $selected = $tot;
		$unselected = (int) $tot - $selected;
		$c = 1;
		$class = "starholder";
		if($params['inactive']) $class .= "-inactive";
		$o = "<div class='".$class."'>\n";
		for($s=0;$s<$selected;$s++){
			$o .= "<div data-val='".$c."' data-stato='on' class='sx stella stella-on'></div>\n";
			$c++;		
		}
		for($s=0;$s<$unselected;$s++){
			$o .= "<div data-val='".$c."' data-stato='off' class='sx stella'></div>\n";
			$c++;		
		}
		$o .= "<br class='clear'><div>\n";
		$out['value'] = $o;
		$out['attr']  = "align='center'";
		return $out;
	}
	
	
	// create an inline label which uses the value minus space for the class and value with spaces for the content
	protected function formatInlineLabel($value, $params){
		if(empty($params['class'])){
			$class = strtolower($value);
			$class = preg_replace("/[^A-Za-z0-9 ]/", '', $class);
			$class = str_replace(" ", "-", $class);
		}else{
			$class = (string) $params['class'];
		}
		$out['value'] = '<span class="label '.$class.'">'.$value.'</span>';
		$out['attr']  = "align='center' valign='center'";
		return $out;
	}

	// convert english float format to italian float format
	// no params
	protected function formatFloatIta($value, $params){
		$decimals = (isset($params['dec'])) ? (int) $params['dec'] : 2;
		$out['value'] = number_format($value, $decimals, ",", ".");
		$out['attr']  = "align='center'";
		return $out;
	}

	// convert english float format to formatted number based on $param
	protected function formatNumber($value, $params){
		$dec = (int) (!isset($params['dec'])) ? 2 : $params['dec'];
		$sep = (empty($params['sep'])) ? "." : $params['sep'];
		$thousand = (empty($params['thousand'])) ? "," : $params['thousand'];
		$align = (empty($params['attr'])) ? "right" : $params['align'];
		$out['value'] = number_format($value, $dec, $sep, $thousand);
		$out['attr']  = "align='".$align."'";
		return $out;
	}
	
	// insert smile icon, value can be 1 (smile) or 0 (no smile)
	// no params
	protected function formatSmile($value, $params){
		$class = "smile ";
		$class .= ($value == '1') ? "smile-on" : "smile-off";
		$state = ($value == '1') ? "on" : "off";
		$out['value'] = "<div data-state='".$state."' class='".$class."'></div>\n";
		$out['attr']  = "align='center'";
		return $out;
	}
	
	protected function formatLink($value, $params){
		$out['value'] = "<a href='cpanel.php?pid=".$params['page']."&v=".$params['view']."&a=".$params['action']."&r=".$value."'>".$value."</a>";		
		$out['attr']  = (empty($params['attr'])) ? "align='center'" :  $params['attr'];
		return $out;
	}

	protected function formatUrl($value, $params){
		$value = trim($value);
		$pattern = '/^(https?:\/\/)?(www\.)?([a-z09-_]{3,}\.[a-z]{2,3}|localhost)?(\/[a-z0-9-_\/]+\.php)(\??[a-z0-9=&]*)(#?[a-z0-9]*)/';
		preg_match($pattern, $value, $matches); 
		// $matches ->  [0] : complete url, [1] : optional http(s):// part, [2] : optional www. part (with final dot) 
		// 				[3] : domain name with extension or localhost, [4] : optional page(s) starting with /
		//              [5] : optional query part without ? (p.e. p=9&r=123&t=somestring), [6] : optional page section after #
		if(!empty($matches[0])){
			$http = ( empty($matches[1]) ) ? "http://" : $matches[1];
			$www  = $matches[2];
			$domain = $matches[3];
			$pages = $matches[4];
			$query = ( empty($matches[5]) ) ? "" : $matches[5];
			$section = ( empty($matches[6]) ) ? "" : $matches[6];
			$url = $http.$www.$domain.$pages.$query.$section; // safe and complete...
			
			$len = count($matches); // 7
			$startWith = (empty($params['start_with'])) ? 3 : $params['start_with'];
			$display_url = "";
			
			for($s=$startWith; $s<$len; $s++){
				$display_url .= $matches[$s];
			}			
			
			$value = "<a href='".$url."'>".$display_url."</a>";			
		}
		$out['value'] = $value;		
		$out['attr']  = "align='left'";
		return $out;
	}
	
	protected function formatUnserialize($value, $params){
		$unserialized = unserialize($value);
		if(is_array($unserialized)){
			$i = (empty($params['index'])) ? 1 : (int) $params['index']; // index can only be number
			$out['value'] = $unserialized[$i];
		}else{
			// not unserializable, return value as is
			$out['value'] = $value;
		}
		$out['attr']  = (empty($params['attr'])) ? "align='left'" :  $params['attr'];
		return $out;
	}

	protected function formatDate($value, $params){
		$ts = strtotime($value);
		$format = (DATE_FORMAT) ? DATE_FORMAT : DEFAULT_DATE_FORMAT; // DATE_FORMAT defined in cc_user.class
		$date = date($format, $ts);
		if($params['time'] or $params['secs']){
			$date .= ($params['secs']) ? " ".date("H:i:s", $ts) : " ".date("H:i", $ts);
		}
		$out['value'] = $date;	
		$out['attr']  = "";
		return $out;
	}

	protected function formatPercent($value, $params){
		$value = (float) $value;
		if($value > 100) $value = 100;
		if($value < 0) $value = 0;
		$dec = (empty($param['decimals'])) ? 2 : $param['decimals'];
		$sep = (empty($param['sep'])) ? "," : $param['sep']; // european as standard decimal separator
		$out['value']  = "<span data-raw='".$value."'>";
		$out['value'] .= number_format($value, $dec, $sep, ""); // last param empty because percent is max 100
		$out['value'] .= "%</span>";
		$out['attr']  = "align='right'";
		return $out;
	}

	protected function formatColorSwatch($value, $params){
		if( empty($value) ){
			$div = "";
		}else{
			$style[] = (empty($params['border-color'])) ? "border-color: #000;" : "border-color: ".$params['border-color'].";";
			$style[] = (empty($params['border-size'])) ? "border-width: 0px;" : "border-width: ".$params['border-size'].";";
			$style[] = (empty($params['width'])) ? "width: 30px;" : "width: ".$params['width'].";";
			$style[] = (empty($params['height'])) ? "height: 30px;" : "height: ".$params['height'].";";
			$style[] = "background-color: ".$value.";";
			$style_flat = implode(" ", $style);
			$div = "<span data-color='".$value."' class='color-swatch' data-toggle='tooltip' data-placement='left' title='".$value."' style='".$style_flat."'></span>";
		}
		$out['value'] = $div;	
		$out['attr']  = "align='center'";
		return $out;
	}

	protected function formatArrayToLabel($value, $params){
		$unser = @unserialize($value);
		if(!is_array($unser)){
			$out['value'] = $value;	
			$out['attr']  = "align='left'";
			return $out;
		}
		$items = "";	
				
		// return values picked from other table else return the unserialized array as csv
		if(!empty($params['tab']) and !empty($params['key']) or !empty($params['field']) ){ 
			
			foreach($unser as $r){
				$v = $this->db->get1value($params['field'], $params['tab'], "WHERE ".$params['key']." = '".$r."'");
				if(empty($v)) $v = $r;
				$split = explode("|", $v);
				$label_value = $split[0];
				$label_color = $split[1];
				$style = (empty($label_color)) ? "" : "style='background-color: ".$label_color.";'";
				$items .= "<span ".$style." data-raw='".$r."' class=\"label\">".$label_value."</span> "; 
			}
			
			
			
			$out['value'] = "<span data-tab='".$params['tab']."' data-key='".$params['key']."' data-field='".$params['field']."'>".$items."</span>";	
						
		}else{
			foreach($unser as $r){
				$items .= "<span data-raw='".$r."' class=\"label\">".$r."</span> "; 
			}
			$out['value'] = $items;	
		}
		
			
		$out['attr']  = "align='left'";
		return $out;
	}

}

?>