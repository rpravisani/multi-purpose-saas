<?php
/*****************************************************
  CALLS: backup-db                                 
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

$output['result'] = true;
$output['error'] = ""; // title of modal box
$output['msg'] = ""; // message inside modal box
$output['errorlevel'] = "success"; // color of modal box

$result = getDbBackup();

$filesize = $result['backup_file_size'];
if($filesize >= 1048576){ 
	$filesize = round($filesize/1048576,0); 
	$filesize .= " MB"; 
}else if($filesize > 1024){ 
	$filesize = round($filesize/1024,0); 
	$filesize .= " KB"; 
}else{
	$filesize .= " Bytes";
}


$output['tr'] = "<tr><td align='center'><i class='text-primary fa fa-download download-backup puls' title='Download file'></i></td><td>".$result['datetime']."</td><td>".$result['backup_file_name']."</td><td>".$filesize."</td><td align='center'><i class=\"puls text-red delete-backup fa fa-fw fa-trash\"></i></td></tr>\n";

echo json_encode($output);
die();


// dumps selected database, sanitizes data and returns textual backup with head and foot comments
function getDbBackup($compressed = false){
	
	$zipped = '0';
	$zipped_size = 0;
	$now = time();
	$date_time = date("d-m-Y-H-i-s", $now);
	$mydump = mysql_dump($db);
	$backup_path = FILEROOT."backup/";

	// sanitize output
	$nochar = array("à", "è", "é", "ì", "ò", "ù", "Á", "È", "É", "Í", "Ó", "Ù", "°");
	$substitute = array("&agrave;", "&egrave;", "&eacute;", "&igrave;", "&ograve;", "&ugrave;", "&Agrave;", "&Egrave;", "&Eacute;", "&Igrave;", "&Ograve;", "&Ugrave;", "&deg;");
	$mydump_safe = str_replace($nochar, $substitute, $mydump);

	// create output
	$backup  = "-- DB CENTRAL generated MySQL dump of ".$db." DB | Date and time : ".$date_time."\n\n";
	$backup .= $mydump_safe;
	$backup .= "-- END of  DB CENTRAL generated MySQL dump";
	
	// saving temp file in same folder as hubfile
	$filename = "backup-".DB_NAME."-".$date_time;
	$tmpfilename = $filename.".tmp";
	$tmpzipname = $filename.".tmpzip";
	$zipname = $filename.".zip";
	$filename = $filename.".sql";
	$handle = fopen($backup_path.$tmpfilename, "wb");
	if(!$handle) die("no handle!");
	
	if(!fwrite($handle, $backup)){
		// TODO error reporting
		echo "Error writing in backup file ".$backup_path.$tmpfilename."!";
		die();
	}
	
	fclose($handle);
	
	// zip file if necessary
	if($compressed){
		$zip = new ZipArchive();
		if ($zip->open($tmpzipname, ZipArchive::CREATE)!==TRUE) {
			// opening zipfile failed, go beyond...
		}else{
			$zip->addFile($tmpfilename, $filename);
			$zipped = '1';
		}
		$zip->close();
		$zipped_size = (file_exists($tmpzipname)) ? filesize($tmpzipname) : '0'; // save size of zipped file	
	}
	
	// preparing output to send back to central
	$out['backup_file_size'] = filesize($backup_path.$tmpfilename); // save size of file
	$out['datetime'] = date("Y-m-d H:i:s" ); // save timestamp
	$out['backup_file_name'] = ($zipped === '1') ? $zipname : $filename; // save filename
	$out['zipped'] = $zipped;
	$out['backup_file_size_zipped'] = $zipped_size;
	
	// copy tmp file to local folder specified in $destination_param
	
	if($zipped){
		copy ($tmpzipname, $backup_path.$zipname);
	}else{
		copy ($backup_path.$tmpfilename, $backup_path.$filename);
	}
	$out['result'] = true;
	
	
	// delete temp file 
	unlink($backup_path.$tmpfilename);
	if($zipped) unlink($tmpzipname);
	
	return $out;
	
	
}

/*** returns text backup of $db - does not add comments in head ***/
function mysql_dump() {
	
	global $db;
	
	$query = "";
	
	$table_list = $db->getTablenames();

	
	// loop tables
	for ($i = 0; $i < @count($table_list); $i++) {
		
		// get column
		//$results = $db->execute_query("DESCRIBE " . DB_NAME . "." . $table_list[$i]);
		
		// table creation part
		$query .= "DROP TABLE IF EXISTS `" . $table_list[$i] . "`;\n";
		$query .= "CREATE TABLE `". $table_list[$i] . "` (\n";
		
		$primary_keys = array();
		
		// loop table fields
		$fields = $db->get_table_info($table_list[$i]);
		foreach($fields as $row){
			
			$query .= "`" . $row['Field'] . "` " . $row['Type'];
			
			// see if field can be NULL
			if ($row['Null'] != "YES") { $query .= " NOT NULL"; }
			
			// check default values
			if ($row['Default'] != ""){ 
				if($row['Default'] == "CURRENT_TIMESTAMP"){
					$query .= " DEFAULT " . $row['Default'] ; 
				}else{
					$query .= " DEFAULT '" . $row['Default'] . "'"; 
				}
			}
			
			// Add extra data
			if ($row['Extra']) { $query .= " " . strtoupper($row['Extra']); }
			
			// if this field is primary key ad primary key definition to tmp variable
			if ($row['Key'] == "PRI") { $primary_keys[] = "`" . $row['Field'] . "`" ; }
			//if ($row['Key'] == "PRI") { $tmp = "primary key(" . $row['Field'] . ")"; }
			
			// add return carriage
			$query .= ",\n";
		} // end table field while loop
		
		// if primary_keys variable is not empty output it and close create table instruction
		if (!empty($primary_keys)){
			$pks = implode(",", $primary_keys);
			$query .= "PRIMARY KEY (" .$pks . ")\n);" . str_repeat("\n", 2);
		}else{
			$query = substr($query, 0, -2);  // remove last 2 car (,\n) from query string
			$query .= ");" . str_repeat("\n", 2); // close create table instruction and add return carriages
		}
		
		/*** GET TABLE CONTENT ***/
		$data = $db->select_all($table_list[$i]);
		
		//Counter of rows, print out "INSERT INTO..." only once every 100 rows
		$insert_row_number = 0;
		
		
		if(!empty($data)){
			// Get content row per row loop
			foreach($data as $row) {
				// lets's see if we've got to add insert instruction...
				if($insert_row_number == 0){
					$query .= "\nINSERT INTO `". $table_list[$i] ."` (";
					$keys = array();
				
					// get column names
					$keys = array_keys($row);
					// add keys to query
					$query .= "`".join($keys, '`, `') . "`) VALUES \n";
				}
				// get values
				$values = array();		
				while (list($key, $value) = @each($row)) { 
					$values[] = addslashes($value); 
				}
				// add values to query and utf8-encode it - encode it here because doing it on the whole file is memory consuming
				$query .= utf8_encode("('" . join($values, "', '") . "'),\n");
				
				// increment row counter, if it hits 100 add an other insert instruction by setting row count to 0
				$insert_row_number++;
				if($insert_row_number > 100){
					$query = substr($query, 0, -2); // get rid of cariage return and comma
					$query .= ";"; // add semi-colon to query
					$insert_row_number = 0; // forse to add insert instruction
				}

			} // end get content loop
			$query = substr($query, 0, -2); // get rid of cariage return and comma
			$query .= ";";
			$query .= str_repeat("\n", 3);
		} // end if $result

	} // end for loop tables

	return $query;
}
	
?>
