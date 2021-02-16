<?php
/**
* Svariate funzioni usate ripetutamente nel progetto.
* Sono suddivise per tipologia
*/

function ccDateTime($date = "", $format = "d/m/Y", $onError = '---'){
    try {
        $date = new DateTime($d);
    } catch (Exception $e) {
        return $onError;
    
    }
    return $date->format($format);
}

function array2table($array, $prima_riga_intestazione = true, $table_params = array(), $td_class = array(), $rowkey_is_tr_id = false){
	if(!is_array($array)) return false;
	
    /* thead */
	if($prima_riga_intestazione){
		$ths = array_keys(reset($array));
		if(empty($ths)) return "";
		$thead = "<thead>\n<tr>\n";
		
		foreach($ths as $th){
			$thead .= "<th>".$th."</th>";
		}
		$thead .= "</tr>\n</thead>\n";
	}

    /* tbody */
	$tbody = "<tbody>\n";
	foreach($array as $rowkey => $tr){
        
		$tbody .= (!$rowkey_is_tr_id) ? "<tr>\n" : "<tr data-id='".$rowkey."'>\n";	
		
		foreach($tr as $i=>$td){
			$cc = $td_class[$i];
			if(!empty($cc)) $cc = " class='".$cc."'";
			$tbody .= "<td".$cc.">".$td."</td>\n";
		}
		
		$tbody .= "</tr>\n";
	}
	$tbody .= "</tbody>\n";
	
    /* table */
	$table = "<table";
	if(is_array($table_params)){
		foreach($table_params as $k=>$v){
			$table .= " ".$k."='".$v."'";
		}
	}
	$table .= ">\n";
	$table .= $thead.$tbody."</table>\n";
	
	return $table;
	
}


function disable_magic_quotes_gpc($valore){
    if (TRUE == function_exists('get_magic_quotes_gpc') && 1 == get_magic_quotes_gpc()){
        $mqs = strtolower(ini_get('magic_quotes_sybase'));

        if (TRUE == empty($mqs) || 'off' == $mqs){
            // we need to do stripslashes on $_GET, $_POST and $_COOKIE
			$valore = stripSlashes($valore);
        }
        else{
            // we need to do str_replace("''", "'", ...) on $_GET, $_POST, $_COOKIE
			$valore = str_replace("''", "'", $valore);
        }
    }
    // otherwise we do not need to do anything
	return $valore;
}

function cc_make_post_mysql_safe ($valore){
	$valore = disable_magic_quotes_gpc($valore);
	$valore = mysql_real_escape_string($valore);
	return $valore;
}

// Non verrà più usato, ma lascio per back compattibility 
function cc_make_post_safe ($valore, $strip=true){
   $safe_data = "";
   $open=0;
   if ($strip) {$valore = stripSlashes($valore); }   
   // Sostituisce i " " con un più inglese « »
   $ncar = strlen($valore);
   for ($i=0; $i<$ncar; $i++){
      $carattere = substr($valore, $i, 1);
      if ($carattere == '"'){
         if ($open==0){
            $safe_data = $safe_data."«";
            $open = 1;
         }else{
            $safe_data = $safe_data."»";
            $open = 0;
         }
      }else{
         $safe_data = $safe_data.$carattere;
      }
   }
   
   $car_proibiti = array("=", "$", "%", "<", ">", "#", "?", ":", "\\");
   $safe_data = str_replace($car_proibiti, "", $safe_data);
   $safe_data = htmlentities($safe_data, ENT_QUOTES);;
   return $safe_data;
} 

// Cambia formattzione data da quella europea a quella americana
function cc_date_eu2us ($data, $div = "-"){
   list ($giorno, $mese, $anno) = preg_split ('/[-\/.]/', $data);
   if (strlen($giorno)==2 and strlen($mese)==2 and strlen($anno)==4){
      $data = $anno.$div.$mese.$div.$giorno;
   }
   return $data;
}

// Cambia formattzione data da quella americana a quella europea
// LEGACY, USARE AL SUO POSTO new DateTime() O FUNCTION ccDateTime()
function cc_date_us2eu ($data, $div = "/"){
   list ($anno, $mese, $giorno) = preg_split ('/[-\/.]/', $data);
   if (strlen($giorno)==2 and strlen($mese)==2 and strlen($anno)==4){
      $data = $giorno.$div.$mese.$div.$anno;
   }
   return $data;
}

function cc_componi_data ($gg, $mm, $yyyy){
   $gg = str_pad($gg, 2, "0", STR_PAD_LEFT);
   $mm = str_pad($mm, 2, "0", STR_PAD_LEFT);
   if ($yyyy < 1000){
      $yyyy = $yyyy+2000;
   }
   $data_compilata = $yyyy."-".$mm."-".$gg;
   return $data_compilata;
} 

function cc_data_da_sql($data, $formato="it"){
	list($date, $time) = explode(' ', $data);
	list($yyyy, $mm, $gg) = explode('-', $date);
	list($hrs, $min, $sec) = explode(':', $time);
	if ($formato == "it"){
		$oggi = $gg."/".$mm."/".$yyyy;
	}else if ($formato == "us"){
		$oggi = $yyyy."/".$mm."/".$gg;
	}else{
		$oggi['gg'] = $gg;
		$oggi['mm'] = $mm;
		$oggi['aaaa'] = $yyyy;
		$oggi['hrs'] = $hrs;
		$oggi['min'] = $min;
		$oggi['sec'] = $sec;
	}
	return $oggi;
}

function sec2hms ($sec, $padHours = false) 
  {

    // start with a blank string
    $hms = "";
    
    // do the hours first: there are 3600 seconds in an hour, so if we divide
    // the total number of seconds by 3600 and throw away the remainder, we're
    // left with the number of hours in those seconds
    $hours = intval(intval($sec) / 3600); 

    // add hours to $hms (with a leading 0 if asked for)
    $hms .= ($padHours) 
          ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
          : $hours. ":";
    
    // dividing the total seconds by 60 will give us the number of minutes
    // in total, but we're interested in *minutes past the hour* and to get
    // this, we have to divide by 60 again and then use the remainder
    $minutes = intval(($sec / 60) % 60); 

    // add minutes to $hms (with a leading 0 if needed)
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

    // seconds past the minute are found by dividing the total number of seconds
    // by 60 and using the remainder
    $seconds = intval($sec % 60); 

    // add seconds to $hms (with a leading 0 if needed)
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

    // done!
    return $hms;
    
  }



// restituisce la data di oggi nel formato desiderato: it (giorno/mese/anno), us (mese/giorno/anno) oppure in array separato.
function cc_data_oggi($formato=""){
   $ora = time();

   // data
   $gg = date("d",$ora);
   $mm = date("m",$ora);
   $yyyy = date("Y",$ora);
   
   // ora
   $hh = date("H",$ora);
   $min = date("i",$ora);
   $ss = date("s",$ora);

   if ($formato == "it"){
      $oggi = $gg."/".$mm."/".$yyyy;
   }else if ($formato == "us"){
      $oggi = $yyyy."/".$mm."/".$gg;
   }else{
   	$oggi['gg'] = $gg;
   	$oggi['mm'] = $mm;
   	$oggi['aaaa'] = $yyyy;
   	$oggi['hh'] = $hh;
   	$oggi['min'] = $min;
   	$oggi['ss'] = $ss;
   }
   return $oggi;
}       

function cc_loremipsum ($numpar, $paragraf=true){
   $lorem = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce quam magna, feugiat eget, convallis eu, interdum cursus, tellus. Quisque vitae leo non tortor iaculis posuere. Donec neque. Ut est urna, tempor ac, mollis sit amet, rutrum ut, nisi. Nullam augue elit, luctus at, iaculis eget, accumsan ac, urna. Morbi iaculis rhoncus ligula. Donec magna leo, ornare et, accumsan sed, faucibus vitae, leo. Sed imperdiet ultricies leo. Sed venenatis, justo et egestas auctor, neque enim euismod quam, laoreet commodo odio urna sed justo. Sed mattis, nibh et imperdiet blandit, nibh leo feugiat turpis, at sollicitudin arcu ante pulvinar arcu. Aliquam erat volutpat. Aliquam pretium quam nec est. Maecenas pulvinar. Sed lobortis fringilla arcu. Nulla facilisi.";
   $filltxt = "";
   if ($paragraf){
      $testo = "<p>".$lorem."</p>";
   }else{
      $testo = $lorem."<BR>";
   }
   if (!isset($numpar)){
      $numpar = 1;
   }
   for ($x=0; $x<$numpar; $x++){
      $filltxt .= $testo;
   }
   return $filltxt;
}

function cc_filter_number ($testo){
   $nummero='';
   preg_match_all('/\d+/', $testo, $number);
   foreach ($number[0] as $element){
      $nummero .= $element;
   }
   return $nummero;
}

function cc_get_euro($valore, $decimali=2){
	if ($valore != "" and $valore!=0){
		$cifra = (float) $valore;
		$euro = number_format($cifra, $decimali, ',', '.');
		$euro = "€&nbsp;".$euro;
		return $euro;
	}else{
		return "";
	}
}

/***********************
 * Permalink functions *
 ***********************/
function cc_format4permalink($stringa){
   $lc = strtolower($stringa);
   $output = str_replace(" ", "-", $lc);
   $output = str_replace("'", "_", $output);
   return $output;
}

function cc_decode_permalink($stringa){
   $output = str_replace("-", " ", $stringa);
   $output = str_replace("_", "'", $output);
   return $output;
}

function cc_create_session_id($id){
	$ora = time();
	$rnd = rand();
	$ora = $ora+$id+$rnd;
	return md5($ora);
}

function cambiaOrdine($tabella, $id=false, $neworder=-9999, $extra=false){
	if ($extra){
		$condizione1 = " AND ".$extra;
		$condizione2 = " WHERE ".$extra;
	}else{
		$condizione1 = "";
		$condizione2 = "";
	}
	
	//ricupera ordine vecchio
	if ($id){
		$oldorder = cc_get1value ("ordine", $tabella, "WHERE id='".$id."'");
	}
	
	// ricupera max order;
	$maxorder = maxorder($tabella, $condizione2);
	
	if ($neworder != -9999){		
		if ($neworder < 1) $neworder=1;
		if ($neworder > $maxorder) $neworder=$maxorder;
	}else{
		$neworder = $maxorder;
	}
	
	if($neworder<$oldorder){
	   $qry_update_altri = "UPDATE ".$tabella." SET ordine=ordine+1 WHERE ordine BETWEEN '".$neworder."' AND '".$oldorder."'".$condizione1;
	   if(!mysql_query($qry_update_altri)){
		   return false;
		   exit();
	   }
	   $qry_update_record = "UPDATE ".$tabella." SET ordine=".$neworder." WHERE id = ".$id;
	   if(!mysql_query($qry_update_record)){
		   return false;
		   exit();
	   }
	}else{
	   $qry_update_altri = "UPDATE ".$tabella." SET ordine=ordine-1 WHERE ordine BETWEEN '".$oldorder."' AND '".$neworder."'".$condizione1;
	   if(!mysql_query($qry_update_altri)){
		   return false;
		   exit();
	   }
	   $qry_update_record = "UPDATE ".$tabella." SET ordine=".$neworder." WHERE id = ".$id;
	   if(!mysql_query($qry_update_record)){
		   return false;
		   exit();
	   }
	}
	return true;
}

function resetOrder($tabella, $condizione, $start=1){
	$start = intval($start);	
	if(!strpos($condizione, "ORDER BY")) $condizione .= " ORDER BY ordine";
	$ids = cc_get_col_value ("id", $tabella, $condizione);
	if($ids){
		foreach($ids as $id){
			$qry = "UPDATE ".$tabella." SET ordine='".$start."' WHERE id = '".$id."'";
			if(!mysql_query($qry)) return false;
			$start++;
		}
	}
	return true;
}

function cc_getNextAi($tablename){
	$next_increment 	= 0;
	$qShowStatus 		= "SHOW TABLE STATUS LIKE '$tablename'";
	$qShowStatusResult 	= mysql_query($qShowStatus);
	if($qShowStatusResult){
		$row = mysql_fetch_assoc($qShowStatusResult);
		return $row['Auto_increment'];
	}else{
		return false;
	}
}

function makeGetQuery($args = false){
	if($args){
		if(is_array($args)){
			foreach ($args as $key=>$value){
				$compiled[] = $key."=".$value;
			}
			$output = implode("&", $compiled);
			$output = "?".$output;
			return $output;
		}else{
			return false;
		}			
	}else{
		return false;
	}
}

function cc_word_trim($text, $nwords, $suffix=""){
	$words = explode(" ", $text);
	$truncated = array_slice($words, 0, $nwords);
	return implode(" ", $truncated).$suffix;
}

/**
 * Creo options da array. key => value option, value => option display
 * 
 * @param array (array) array con i valori
 * @param selected (int|string) valore che dovrà risultare selezionato
 * @param first_empty (bool) flagse generare come primo option un valore vuoto
 * @param orderby (false|key|value) false: lascia invariata l'array, ket, ordina array per chiave, value: ordine array per valore
 */
function array2selectOptions($array, $selected = '', $first_empty = true,  $orderby = false ){
	if(!is_array($array)) return "<option disabled>Nessun dato passato</option>";
	
	if($orderby == 'key'){
		ksort($array);
	}else if($orderby == 'value'){
		sort($array);		
	}
	
	$options = "";
	if($first_empty) $options = "<option value=''>&nbsp;</option>\n";
	
	foreach($array as $key => $value){
		$s = ($key == $selected) ? "selected" : "";
		$options .= "<option ".$s." value= '".$key."'>".$value."</option>\n";
	}
	
	return $options;
	
}


function getSelectOptions($chiave, $valore, $tabella, $ids=array(), $order=false, $condizione="", $first_empty=true){
	global $db;
	if(!is_array($ids)) $ids = array($ids);
	if(!$order) $order = $valore;
	$options = "";
	if($first_empty) $options .= "<option value=\"\">&nbsp;</option>\n";
	$kv = $db->key_value($chiave, $valore, $tabella, $condizione." ORDER BY ".$order);
	if($kv){
		foreach($kv as $key=>$value){
			$selected = (in_array($key, $ids)) ?  "selected=\"selected\"" : "";
			$options .= "<option ".$selected." value=\"".$key."\">".$value."</option>\n";
		}
		//return $options;
	}else{
		//return false;
	}
	return $options;
}

function outputCSV($data) {
    $outstream = fopen("php://output", 'w');
    function __outputCSV(&$vals, $key, $filehandler) {
        fputcsv($filehandler, $vals, ',', '"');
    }
    array_walk($data, '__outputCSV', $outstream);
    fclose($outstream);
}

function array2csv($data, $nomefile, $sep=';', $enc='"', $output='download'){

	if($output == "download"){
		header("Content-type: text/csv");  
		header("Cache-Control: no-store, no-cache");  
		header('Content-Disposition: attachment; filename="'.$nomefile.'"');  
		
		$outstream = fopen("php://output",'w');  
	
	}else if($output == "file"){
		
		if(!is_dir(FILEROOT.PATH_CSV)){
			if(!mkdir(FILEROOT.PATH_CSV)) die("Impossibile creare cartella");
		}
		$nomefile = FILEROOT.PATH_CSV.$nomefile;
		$outstream = fopen($nomefile, 'w');
		if(!$outstream) return false;
	}
	  
	if(PRIMA_RIGA_INTESTA){
		$k = array_keys(reset($data));
		if(!fputcsv($outstream, $k, $sep, $enc)) die("errore fputcsv durante scrittura prima riga");
	}
	foreach( $data as $row ){  
		if(!fputcsv($outstream, $row, $sep, $enc)) die("errore fputcsv : ".implode(", ", $row));
	}  
	  
	fclose($outstream);
	return true;		

}

function qry2csv($qry, $nomefile, $prima_riga_intestazione = false, $sep=';', $enc='"', $download = false){
	
	$db = new cc_dbconnect(DB_NAME);
	$rows = $db->fetch_array($qry, MYSQLI_ASSOC);
		
	if($rows){
		if($download){
			header('Content-type: text/csv');  
			header('Cache-Control: no-store, no-cache');  
			header('Content-Disposition: attachment; filename="'.$nomefile.'"');  
		}
		  
		$outstream = fopen("php://output",'w');
		
		if($prima_riga_intestazione){
			$pr = array_keys($rows[0]);
			fputcsv($outstream, $pr, $sep, $enc);
		}
		
		foreach($rows as $row){

			fputcsv($outstream, $row, $sep, $enc); 
			
		}
	  
		fclose($outstream);
		
		return true;
		
	}else{
		return false;
		
	}

}


function getcsv($file, $prima_riga_intestazione = true, $sep = ","){

	$row = 0;
	$csv = array();
	
	if (($handle = fopen($file, "r")) !== FALSE) {
		
		while (($data = fgetcsv($handle, 10000, $sep)) !== FALSE) {
			
			$emptycheck = implode("", $data);
			
			if(!empty($emptycheck)){
				$col = 0;
				
				foreach($data as $value){
					if($prima_riga_intestazione and $row==0){
						$keys[$col] = $value;
					}else{
						if($prima_riga_intestazione){
							$csv[$row][$keys[$col]] = $value;
						}else{
							$csv[$row][$col] = $value;
						}
					}
					$col++;
				}
				$row++;
			}
		}
		fclose($handle);
		
		return $csv;
		
	}else{
		return false;
	}

}

function elencoFiles($directory, $file_type="all", $sort=true){
	$t=0;
	if (is_dir($directory)) {
		if ($dh = opendir($directory)) {
			while (($cart = readdir($dh)) !== false) {
				$add = false;
				if (filetype($directory . $cart)=="file") {
					$t++;
					 if($file_type != "all"){
						 $split = explode(".", $cart);
						 $estensione = $split[count($split)-1];
						 if(strtolower($estensione) == strtolower($file_type)){
							 $add = true;
						 }
					 }else{
						 $add = true;
					 }
					 if($add){
						$files[] = $cart;
					 }
				 }
		   }
		   closedir($dh);
	   }
	   if (count($files)!=0){
			if($sort){
				sort ($files);
			}
			return $files;
	   }else{
		   return false;
	   }
	}else{
		return false;
	}
}

function elencoFilesEvo($directory, $file_type="all"){
	$t=0;
	if (is_dir($directory)) {
		if ($dh = opendir($directory)) {
			while (($cart = readdir($dh)) !== false) {
				$add = false;
				if (filetype($directory . $cart)=="file") {
					$t++;
					 if($file_type != "all"){
						 $split = explode(".", $cart);
						 $estensione = $split[count($split)-1];
						 if(strtolower($estensione) == strtolower($file_type)){
							 $add = true;
							 $tf['File'] = $cart;
							 $tf['Data'] = filemtime($directory . $cart);
							 $tf['Dim'] = filesize($directory . $cart);
							 
						 }
					 }else{
						 $tf['File'] = $cart;
						 $tf['Data'] = filemtime($directory . $cart);
						 $tf['Dim'] = filesize($directory . $cart);
						 $add = true;
					 }
					 if($add){
						$files[] = $tf;
					 }
				 }
		   }
		   closedir($dh);
	   }
	   if (count($files)!=0){
			return $files;
	   }else{
		   return false;
	   }
	}else{
		return false;
	}
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('byte', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

function getDevice(){

	//Detect special conditions devices
	$iPod = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
	$iPhone = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
	$iPad = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
	if(stripos($_SERVER['HTTP_USER_AGENT'],"Android") && stripos($_SERVER['HTTP_USER_AGENT'],"mobile")){
			$Android = true;
	}else if(stripos($_SERVER['HTTP_USER_AGENT'],"Android")){
			$Android = false;
			$AndroidTablet = true;
	}else{
			$Android = false;
			$AndroidTablet = false;
	}
	$webOS = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
	$BlackBerry = stripos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
	$RimTablet= stripos($_SERVER['HTTP_USER_AGENT'],"RIM Tablet");
	
	
	//do something with this information
	if( $iPod || $iPhone ){
			return "iphone";
	}else if($iPad){
			return "ipad";
	}else if($Android){
			return "android-phone";
	}else if($AndroidTablet){
			return "android-tablet";
	}else if($webOS){
			return "webOS";
	}else if($BlackBerry){
			return "blackberry";
	}else if($RimTablet){
			return "rim-tablet";
	}else{
			return false;
	}
}

function dataItaliana($ts = false){
	global $giorno_settimana, $mese_anno;
	
	if(!$ts) $ts = time();
	$giorno = ucfirst($giorno_settimana[date("w", $ts)]);
	$giorno .= " ".date("d", $ts);
	$giorno .= " ".ucfirst($mese_anno[date("n", $ts)]);
	$giorno .= " ".date("Y", $ts);
	
	return $giorno;

}

function domani($ts){
	$h = 23-date("H", $ts);
	$m = 59-date("i", $ts);
	$s = 60-date("s", $ts);
	$secs = ($h*60*60)+($m*60)+$s;
	$domani = $ts+$secs;
	return $domani;
}

function oggi($ts){
	$h = date("H", $ts);
	$m = date("i", $ts);
	$s = date("s", $ts);
	$secs = ($h*60*60)+($m*60)+$s;
	$oggi = $ts-$secs;
	return $oggi;
}

function jstime2phptime($ts){
	$ts = ceil($ts/1000);
	//$ts = $ts+(60*60*24); // non so perché ma risulta sempre un giorno indietro
	return $ts;
}

function getSelectOptionsAdv($qry, $chiave, $valore, $id, $empty_first = true){ // in questa versione $viene passato una query, oltre che il nome della colonna chiave e valore - solo per picasso...
	global $db;
	$options = "";
	//echo $qry."<br>\n";
	$sa = $db->fetch_array($qry, MYSQLI_ASSOC);
	if($sa){
		if($empty_first) $options = "<option value=\"\"></option>\n";
		foreach($sa as $row){
			$d = array();
			$key = $row[$chiave];
			$value = $row[$valore];
			unset($row[$chiave]);
			unset($row[$valore]);
			if(empty($row)){
				$data = "";
			}else{
				foreach($row as $k=>$v){
					$d[] = "data-".$k."=\"".$v."\"";
				}
				$data = implode(" ", $d);
			}
			if(is_array($id)){
				$selected = (in_array($key, $id)) ? "selected" : "" ;
			}else{
				$selected = ($key==$id) ? "selected" : "" ;
			}
			$options .= "<option ".$selected." ".$data." value=\"".$key."\">".$value."</option>\n";
		}
		return $options;
	}else{
		return false;
	}
}

function pulisciFoto($immobile = "0"){
	global $db;
	// ricupera tutte le foto
	//$elenco_foto = cc_get_col_value ("nome_file", TABELLA_FOTO, "WHERE immobile='".$immobile."'");
	$elenco_foto = $db->col_value ("nome_file", TABELLA_FOTO, "WHERE immobile='".$immobile."'");
	if($elenco_foto){
		
		foreach($elenco_foto as $nome_foto){
			$foto = JOOMLA_FILEROOT.PATH_FOTO.$nome_foto;
			if(file_exists($foto)){
				if(!unlink($foto)){
					echo "Impossibile eliminare foto principale ".$nome_foto."<br>\n";
					$ok = false;
				}else{
					$ok = true;
				}
			}else{
				// magari è stata cancellata o rinominata, vado cmq avanti?
				$ok = true;
			}
		}
			
		if($ok){
			if(!$db->delete(TABELLA_FOTO, "WHERE immobile='".$immobile."'" )){
				echo "Impossibile eliminare la foto <strong>".$nome_foto."</strong> dal database foto.<br><br>\n";
				return false;
			}else{
				return true;
			}
		}else{
			return false;
		}
	}else{
		// Nessuna foto trovata in DB;
		return true;
	}
}

// restituisce un inseiem di radiobuttons in base ad una query
function getRadioButtons($name, $chiave, $valore, $tabella, $select, $class=false, $senso="hor", $order=false, $lingua=false, $where=false){
	if(!$order) $order = $valore;
	$lingua=false; // accrocchio veloce : per ora no gestione lingua...
	$condizione = "";
	$radios = "";
	if($where) $cond[] = $where;
	if($lingua) $cond[] = " lingua='".$lingua."' ";
	if(count($cond) > 0){
		$condizione = implode(" AND ", $cond);
		if(!stripos($where, "WHERE")) $condizione = " WHERE ".$condizione;
	}

	$kv = cc_mysql_key_value($chiave, $valore, $tabella, $condizione."ORDER BY ".$order);
	if($kv){
		foreach($kv as $key=>$value){
			if($key==$select){
				$selected = "checked=\"checked\" ";
			}else{
				$selected = "";
			}
			
			if($class){
				$classe = "class=\"".$class."\" ";				
			}else{
				$classe = "";
			}
			
			$radios .= "<input type=\"radio\" name=\"".$name."\" id=\"".$name."-".strtolower($value)."\" value=\"".$key."\" ".$selected." ".$classe."/>&nbsp;".$value;
			if($senso == "hor"){
				$radios .= "&nbsp;&nbsp";
			}else{
				$radios .= "<br />\n";
			}
		}
		return $radios;
	}else{
		return false;
	}
}

/****************************************************************
 *  Cancella foto da cartella delle foto (prodotti / articoli)  *
 *      e dalle sottocartelle thumb e medium se esistono        *
 *   ATTENZIONE assicurarsi che siano definite le costanti:     *
 *  - FOTO_PRODOTTI_UPLOAD_DIR                                  *
 *  - FOTO_PRODOTTI_MEDIUMUPLOAD_DIR                            *
 *  - FOTO_PRODOTTI_THUMBUPLOAD_DIR                             *
 * Output success = false, output fail = errormsg               *
 ****************************************************************/
function cc_cancella_foto($foto){
	//controlla che sia definita la costante che definisce la cartella principale delle foto;
	if (!defined('FOTO_PRODOTTI_UPLOAD_DIR')){
		return "Cartella foto non definita";
		exit;
	}
	// cancella foto da cartella principale
	if (file_exists(FOTO_PRODOTTI_UPLOAD_DIR.$foto)){
		if (!unlink (FOTO_PRODOTTI_UPLOAD_DIR.$foto)){
			return "Impossibile eliminare la foto dalla cartella".FOTO_PRODOTTI_UPLOAD_DIR;
			exit;
		}
	}else{
		return "Impossibile trovare la foto ".$foto." nella cartella".FOTO_PRODOTTI_UPLOAD_DIR;
		exit;
	}
	
	// cancella foto da cartella medium
	if (defined('FOTO_PRODOTTI_MEDIUMUPLOAD_DIR')){
		if (file_exists(FOTO_PRODOTTI_MEDIUMUPLOAD_DIR.$foto)){
			if (!unlink (FOTO_PRODOTTI_MEDIUMUPLOAD_DIR.$foto)){
				return "Impossibile eliminare la foto dalla cartella".FOTO_PRODOTTI_MEDIUMUPLOAD_DIR;
				exit;
			}
		}else{
			return "Impossibile trovare la foto ".$foto." nella cartella".FOTO_PRODOTTI_MEDIUMUPLOAD_DIR;
			exit;
		}
	}
	
	// cancella foto da cartella thumb
	if (defined('FOTO_PRODOTTI_THUMBUPLOAD_DIR')){
		if (file_exists(FOTO_PRODOTTI_THUMBUPLOAD_DIR.$foto)){
			if (!unlink (FOTO_PRODOTTI_THUMBUPLOAD_DIR.$foto)){
				return "Impossibile eliminare la foto dalla cartella".FOTO_PRODOTTI_THUMBUPLOAD_DIR;
				exit;
			}
		}else{
			return "Impossibile trovare la foto ".$foto." nella cartella".FOTO_PRODOTTI_THUMBUPLOAD_DIR;
			exit;
		}
	}
	return false;
}

function object2array($object){
	if(!is_object($object)){
		return false;
	}else{
		foreach($object as $key=>$value){
			$array[$key] = $value;
		}
		return $array;
	}
}

// ricupera substring tra substring $start e $end - p.e. se $stinga="#456_Quello prima è il numero record" lanciando get_string_between($stringa, "#", "_") restituisce "456"
function get_string_between($string, $start, $end){
	$string = " ". $string;
	$ini = strpos($string,$start); //Find position of first occurrence of a string
	if ($ini == 0){
		return "";
	}else{
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini; // 3° param strpos = offset (The optional offset parameter allows you to specify which character in haystack to start searching. The position returned is still relative to the beginning of haystack.)
		return substr($string, $ini, $len);
	}
}

// ricupera l'esatto url della pagina attuale
function cc_getCurrentUrl() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

// recupera il contenuto di file di testo avente un determinata estension partendo da una cartella e scandagliando tutti i file e sotto cartelle in 
function get_deep_content($dir, $extension = false, &$content = "", &$count = 0){
	$files = scandir($dir);
	
	if($files){
		foreach($files as $file){
			$path = $dir."/".$file;
			if($file == "." or $file == "..") continue;
			if(is_dir($path)){
				//echo "<p>".$path." ".$count."</p>";
				ccscan($path, $extension, $content, $count);
			}else{
				if($extension){
					$e = explode(".", $file);
					$file_ext = $e[ count($e)-1 ];
					if(is_array($extension)){
						if(!in_array($file_ext, $extension)) continue;
					}else if (is_string($extension)){
						if($file_ext != $extension) continue;
					}
					
				}
				$count++;
				$content .= file_get_contents($path);
			}
		}
	}
	return $content;
}

function calculate_average($arr) {
	if(!is_array($arr)) return false;
    $count = count($arr); //total numbers in array
	$total = array_sum($a); // total amount
    $average = ($total/$count); // get average value
    return $average;
}

// inserisce $insertstring in $intostring alla posizione $offset (0 = inizio)
function str_insert($insertstring, $intostring, $offset) {
   $part1 = substr($intostring, 0, $offset);
   $part2 = substr($intostring, $offset);
  
   $part1 = $part1 . $insertstring;
   $whole = $part1 . $part2;
   return $whole;
}

// inserisce $insertstring in $intostring tra $start e $stop. Se $all == true la stringa da sostituire comprende anche $start e $stop (più sicuro) se no solo quello che c'è in mezzo
function str_insert_between($insertstring, $intostring, $start, $end, $all=true) {
	$to_replace = get_string_between($intostring, $start, $end);
	if($all){
		$to_replace = $start.$to_replace.$end;
	}
	$result = str_replace($to_replace, $insertstring, $intostring);
	return $result;
}

// trasforma data yyyymmdd in dd/mm/yyyy o altro formato leggibile
function formatCcDate($date, $div = "/", $format = "it"){
	$y = substr($date, 0, 4);
	$m = substr($date, 4, 2);
	$d = substr($date, 6, 2);
	if($format == "it"){
		$d = $d.$div.$m.$div.$y;
	}else if ($format == "sql"){
		$d = $y.$div.$m.$div.$d;
	}else{
		$d = $m.$div.$d.$div.$y;
	}
	
	return $d;
}

// da epoch a yyyymmdd
function ts2ccdate($ts){
	return date("Ymd", $ts);
}

function copiaFoto($record, $piantina=0){
	global $db;
	$elenco_foto = $db->col_value ("nome_file", TABELLA_FOTO, "WHERE immobile='".$record."' ORDER BY ordine");
	if($elenco_foto){
		foreach($elenco_foto as $k=>$oldfoto){
			$ordine = $k+1;
			$fn = explode(".", $oldfoto);
			$crypted = sha1($fn[0].time());
			$extension = end($fn);
			$nomefile = $crypted.".".$extension;
			
			$campi = array("nome_file", "immobile", "piantina", "ordine");
			$valori = array($nomefile, '0', $piantina, $ordine);
			
			if($db->insert(TABELLA_FOTO, $valori, $campi) ){
				$entries[] = $db->get_insert_id();
				if(!copy(JOOMLA_FILEROOT."/".PATH_FOTO.$oldfoto, JOOMLA_FILEROOT."/".PATH_FOTO.$nomefile)) $err .= "Impossible copiare la foto ".$oldfoto."<br>\n";
			}

		}
		
		if(empty($err)){
			return $entries;
		}else{
			echo "Attenzione, errore durante copia<br>\n".$err;
			return false;
		}
	}else{
		return false;
	}
}

//This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)  
function convertPHPSizeToBytes($sSize){  
    if ( is_numeric( $sSize) ) {
       return $sSize;
    }
    $sSuffix = substr($sSize, -1);  
    $iValue = substr($sSize, 0, -1);  
    switch(strtoupper($sSuffix)){  
	// incascata poiché non c'è break... geniale
    case 'P':  
        $iValue *= 1024;  
    case 'T':  
        $iValue *= 1024;  
    case 'G':  
        $iValue *= 1024;  
    case 'M':  
        $iValue *= 1024;  
    case 'K':  
        $iValue *= 1024;  
        break;  
    }  
    return $iValue;  
}  

function getMaximumFileUploadSize()  {  
    return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));  
}


function myErrorHandler($errno, $errstr, $errfile, $errline){
	$err = "errno: ".$errno."<br>\n";
	$err .= "errstr: ".$errstr."<br>\n";
	$err .= "errfile: ".$errfile."<br>\n";
	$err .= "errline: ".$errline."<br>\n";
	return $err;
}

function setToken($tokenize = false, $page = 0, $record = 0, $view = 'pdf', $action = 'view', $user = '0', $view_message = '1',  $duration = DAYS_TOKEN){
	global $db;
	if(empty($page) or empty($view) ){
		$_SESSION['error_token'] = __LINE__." - page: ".$page." view: ".$view." action: ".$action;
		return false;
	}
	$token = (empty($tokenize)) ? createToken() : sha1($tokenize);
	//$url = HTTP_PROTOCOL.HOSTROOT.SITEROOT.$url;
	$fields = array("token", "durata", "page", "record", "view", "action", "user", "view_message");
	$values = array($token, $duration, $page, $record, $view, $action, $user, $view_message);
	if($db->insert(DBTABLE_TOKENS, $values, $fields)){
		return $token;
	}else{
		$_SESSION['error_token'] = __LINE__." - Errore inserimento in tabella tokens: ".$db->getError("msg")."<br>\nQuery: ".$db->getQuery();
		return false;
	}
	
}

function createToken(){
	if(function_exists("openssl_random_pseudo_bytes")){
		return bin2hex(openssl_random_pseudo_bytes(ENCODE_BYTES));		
	}else if(function_exists("random_bytes")){
		return bin2hex(random_bytes(ENCODE_BYTES));				
	}else{
		// NOT SO SECURE, BUT IF THE OTHER 2 FUNCTIONS AREN'T AVAILABLE IT'S THE NEXT BEST THING
		return md5(uniqid(mt_rand(), true));
	}
}

function encodePassword($string){
	return encodeString($string);
}

function encodeString($string){
	return sha1($string);
	// return bin2hex(openssl_random_pseudo_bytes(ENCODE_BYTES));
}

// Converts number to mysql safe floating number 
function currency_safe_old($value, $ndec = 2){
	$value = trim($value);
	$value = str_replace(" ", "", $value);
	$parts = preg_split("/[,. ]/", $value);
	if(count($parts) > 1){
		$dec = array_pop($parts);
		$int = implode("", $parts);
		$number = $int.".".$dec;
	}else{
		$number = $parts[0];
	}

	return number_format($number, $ndec, ".", "");
	
}

// Converts number to mysql safe floating number 
function currency_safe($value, $ndec = 2, $sep = "."){
	$value = preg_replace('/[^0-9., ]/', "", $value); // remove anything that's not a number, a comma, a dot or a space
	$value = trim($value); // remove whitespaces at start and end
	$value = preg_replace('/[., ]/', $sep, $value); // replace commas, dots and spaces (in between) with a separator sign
	$p = explode($sep, $value); // split string by separator sign
	
	if(count($p) > 1){
		$dec = array_pop($p); // get decimals 
		$dec = myRound($dec, $ndec); // round and truncate decimals to two (p.e. 2345 becomes 24)
		$int = implode("", $p);
		$number = $int.".".$dec;
	}else{
		$number = $parts[0];
	}
	
	return number_format($number, $ndec, ".", "");
}

// used in currency_safe function
function myRound($number, $length){
    $add = 0;
    $int = substr($number, 0, $length); // get the part that we want to output
    $dec = substr($number, $length); // get the rest
    $digits = str_split($dec); // put all the digits of the rest in an array as separate value
    $digits = array_reverse($digits); // reverse the array
    // loop the array and decide if add will be 1 or 0
    foreach($digits as $i=>$digit){
        $digit += $add; // add $add to the value of the digit
        $add = ($digit > 4) ? 1 : 0; // if the digit is larger than 4 then add will be 4
    }
    return $int+$add; // add $add to the part we want to output and return it
}


function check_reset_token($token){
	global $db;
	// sanitize token
	$id = $db->make_data_safe($token);
	// check token
	$lc = (int) ENCODE_BYTES*2;
	$check_token = (preg_match("/^[a-z0-9]{".$lc."}$/", $token));
	if(empty($id) or empty($check_token)) return false;
	// check in db for token and time
	if( $db->get1value("reset_limit", LOGIN_TABLE, "WHERE reset_token = '".$token."' AND reset_limit > '".time()."'") ){
		return true;
	}else{
		return false;
	}
}

// Funzione non specifica per questo script: fornendo un valore ed un array resitituisce il valore inferiore più prossimo all'interno dell'array
function getClosest($search, $arr) {
    $closest = null;
    if($search == reset($arr)) return end($arr); // se $search == primo valore dell'array, restituisce ultimo valore dell'array (array circolare)
    foreach ($arr as $item) {
      if ($closest === null || abs($search - $closest) > abs($item - $closest)) {
         $closest = $item;
      }
   }
   return $closest;
}

function getDateInterval($start, $end, $days = 1, $array = false){
	if(empty($start)) $start = date("Y-m-d", time());
	if(empty($end)) $end = date("Y-m-d", time());
	$startDate = new DateTime($start);
	$endDate = new DateTime($end);
	
	$interval = new DateInterval('P'.$days.'D'); //funziona così: inizia sempre con P poi il numero e l'unità in questo caso D sta per giorno quindi un intervallo di 1 giorno

	// add a day to the end date else it will be excluded
	$endDate->add($interval); 
	$period = new DatePeriod($startDate, $interval, $endDate);
	

	if($array){
		$out = array();
		if($period){
			foreach ($period as $date) {
				$out[] = $date->format("Y-m-d");
			}
		}
		return $out;
	}else{
		return $period;		
	}
	
}

function cc_strtolower($string){
	if( function_exists('mb_strtolower') ){
		return mb_strtolower($string);
	}else{
		return strtolower($string);
	}
}


function parseUrl($url, $startWith = 3){
	$url = trim($url);
	$parts = array();

	$pattern = '/^(https?:\/\/)?(www\.)?([a-z09-_]{3,}\.[a-z]{2,3}|localhost)?(\/[a-z0-9-_\/]+\.php)(\??[a-z0-9=&]*)(#?[a-z0-9]*)/';
	preg_match($pattern, $url, $matches); 
	// $matches map
	// [0] : complete url, [1] : optional http(s):// part, [2] : optional www. part (with final dot) 
	// [3] : domain name with extension or localhost, [4] : optional page(s) starting with /
	// [5] : optional query part without ? (p.e. p=9&r=123&t=somestring), [6] : optional page section after #
	if(!empty($matches[0])){
		$parts = array();
		$parts['http'] 	 	 = ( empty($matches[1]) ) ? "http://" : $matches[1]; 
		$parts['www']  	 	 = $matches[2];
		$parts['domain'] 	 = $matches[3];
		$parts['pages']  	 = $matches[4];
		$parts['breadcrumb'] = ( empty($parts['pages']) ) ? array() : explode("/", $parts['pages']);
		$parts['script'] 	 = ( empty($parts['breadcrumb']) ) ? "" : end($parts['breadcrumb']);
		$parts['query']  	 = ( empty($matches[5]) ) ? "" : $matches[5];
		$parts['section'] 	 = ( empty($matches[6]) ) ? "" : $matches[6];
		$parts['url'] 		 = $parts['http'].$parts['www'].$parts['domain'].$parts['pages'].$parts['query'].$parts['section']; // safe and complete...

		array_shift($parts['breadcrumb']); // first item of array is empty
		$len = count($matches); // 7
		$display_url = "";

		for($s=$startWith; $s<$len; $s++){
			$display_url .= $matches[$s];
		}
		
		$parts['display_url'] = $display_url;
		$parts['link'] = "<a href='".$parts['url']."'>".$display_url."</a>";			
	}else{
		$parts['url'] = $url;			
	}	
	
	return $parts;
	
}

// make a cURL call to a file - php equivalent to jquery $_post
function callFile($url = "", $posts = array(), $json = true){

	if(empty($url)) return false;
	
	if(function_exists('curl_version')){
		
		$url = HTTP_PROTOCOL.HOSTROOT.SITEROOT.$url;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // make sure output from called file is retunr to variable and not outputted
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_POST, true); // assicuro che usi $_POST
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $posts); // l'array da inviare come post

		$output = curl_exec($ch);

		curl_close($ch);
		
	}else{
		
		$output = array();
		$output['result'] = false;
		$output['error'] = "No cURL"; // title of modal box
		$output['msg'] = "CURL function not found on this system!"; // message inside modal box
		$output['errorlevel'] = "danger"; // color of modal box
		
	}
	
	return ($json) ? json_decode($output, true) : $output;
	
}

// Log failed login attempts to log_login_attempts table in DB
function log_attempt($script = "verificautente", $user = "", $reason = "empty post" ){
	
	global $db;    
	
	$ip = $_SERVER['REMOTE_ADDR']; 
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	$http_referer = @$_SERVER['HTTP_REFERER']; 
	$server_data = serialize($_SERVER); 
    
    // check if whielisted of if localhost
    $whitelist = $db->col_value("IP", "system_whitelist");
    if(in_array($ip, $whitelist) or LOCALHOST) return true;
    
	
	$fields = array("user", "ip", "useragent", "reason", "script", "http_referer", "server_data");
	$values = array($user, $ip, $useragent, $reason, $script, $http_referer, $server_data);
	
	$db->insert("logs_login_attempts", $values, $fields);
    
    return true;

}

// create (csv) file in /csv folder and filling it with $data
function createAttachment($data, $filename){
	$filename = FILEROOT.PATH_CSV.$filename;
	$outstream = fopen($filename, 'w');
	fwrite($outstream, $data);
	fclose($outstream);		
	return $filename;
}

// loads static tempalte file and fills al tags enclosed by {{}} with corrisponding data from $data
function template($template, $data, $leave_tags = false){
	
	// check if template file exists
	if(!file_exists($template)) return false;
	
	// load html of template. Can be done better with file_get_contents
	ob_start();
	include($template);
	$html = ob_get_contents();
	ob_end_clean();
	
	// get all the tags from tempalte and puyt them in $matches. 
	$pattern = "/{{([a-zA-Z0-9_-]+)}}/i";
	preg_match_all($pattern, $html, $matches);
	
	// if found tags divdem them by tag with {{}} and tags without
	if($matches){
		$keys = $matches[1];
		$subs = $matches[0];
		
		// loop all tags found.
		foreach($subs as $k =>$search){
			$key = $keys[$k];
			// if a corresponding key in array is found replace tag with {{}} with value in array. if not found leave tag or emapty based on value of flag $leave_tags
			$value = ($leave_tags) ? (array_key_exists($key, $data)) ? $data[$key] : $search : $data[$key];
			$html = str_replace($search, $value, $html);
			
		}
	}
	// return filled out template
	return $html;
	
}

// same as abova but without the loading template part. the template is fed directy to the function as html
function template2($html, $data, $leave_tags = false, $wrapper = "{{*}}"){
	
	if(empty($wrapper) or strpos($wrapper, "*") === false) $wrapper = "{{*}}";
	list($wb, $wa) = explode("*", $wrapper);
	$wb = quotemeta ( $wb ); //  Adds a backslash character before every character that is among these: . \ + * ? [ ^ ] ( $ )
	$wa = quotemeta ( $wa ); //  Adds a backslash character before every character that is among these: . \ + * ? [ ^ ] ( $ )
	
	// tags
	$pattern = "/".$wb."([a-zA-Z0-9_-]+)".$wa."/i";
	preg_match_all($pattern, $html, $matches);
	
	if($matches){
		$keys = $matches[1];
		$subs = $matches[0];
		
		foreach($subs as $k =>$search){
			$key = $keys[$k];
			$value = ($leave_tags) ? (array_key_exists($key, $data)) ? $data[$key] : $search : $data[$key];
			$html = str_replace($search, $value, $html);
			
		}
	}
	
	return $html;
	
}

// recupero testi editabili da tabella texts fornendo il nome del campo e la lingua
function getContent($field, $lang = 'it'){
	
	global $db;
	
	// query che in base al nome del campo e la lingua recupera il testo. 
	// t.lang = in subquery ON perché se messa in in WHERE non estrapolerebbe nulla in caso in texts 
	// non ci fosse il record o non ci fosse il testo nella lingua prescelta
	$qry = "SELECT t.text 
	FROM `data_text_fields` AS f 
	LEFT JOIN data_texts AS t ON (t.field = f.id AND t.lang = '".$lang."') 
	WHERE f.name = '".$field."' 
	AND f.active = '1'";
	
	$result = $db->fetch_array_row($qry);
	
	return $result['text'];
	
}

// strips out only tags passed in array $tags and if replace (array with 2 value) if not empty replace with strings inside array
function ccstrip_replace($string, $tags = array(), $replace = array()){
    
    if(empty($tags) or empty($string)) return $string;
    
    if(!is_array($tags)) $tags = array($tags);
    
    if(empty($replace)) $replace = array('', '');
    if(count($replace) > 2) array_splice($replace, 2); 
    if(count($replace) == 1) $replace = array( $replace[0], '' );
    
    foreach($tags as $tag){
        if(!is_string($tag)) continue;
        $tag = trim($tag);
        
        $pattern[] = "/<".$tag." *[a-zA-Z0-9=\-_ '\"#@:;]*\/?>/";
        $pattern[] = "/<\/".$tag.">/";
        
        $s = "<".$tag.">";
        $e = "</".$tag.">";
        $string = preg_replace($pattern, $replace, $string);
    }
    
    return $string;
}
	
?>
