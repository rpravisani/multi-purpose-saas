<?php
/*********************************************************************************************************
  COPY A RECORD - VERS. 3.0                                    
  WILL INCLUDE SWITCH SCRIPT THAT'S STORED IN THE COPY FOLDER AND MUST BE NAMED AS THE PAGE FILENAME 
  -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
  # IN: RECORD (INT), PAGE ID (INT)                  
  # OUT: TRUE OR ERROR MESSAGE ($OUTPUT)
  
  VARS
  -----
  $pid        = (int) $_POST['pid'] is the id of the page - seti in _head.php script
  $recid      = (int) $_POST['record'] is the id of the record to be copied
  
  $table      = (string) The name of the main table from/to which to copy the record
  $field      = (string) The name of the field of $table to which $recid must corrispond (default id)
  $exclude    = (array) columns of main table not to be copied (exclude default fields: id, ts, updatedby)
  $substitute = (array) Array holding association of field and values of main table to be replaced before copy
  $copyrow    = (array) Row holding the copied data (can be pre-written in switch file, else default logic will be applied)
    
  $relative   		   = (array) Array holding the names of extra tables (key) and fields (value) that depend of main record
  $relative_exclude    = (2dim array) fields of the relative tables not to be copied (exclude default fields: id, ts, updatedby)
  $relative_substitute = (2dim array) key holds fieldnames of the relative table to be altered, value holds key of copyrecord
                                      to be used to assign value or, if not found, the static value to be used to set thefield
  $relative_clause     = (array) Extra clause to be used in extrapolation of relative data
 *********************************************************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

/*** SET (DEFAULT) VARIABLES ***/
$table = false;
$field = "id"; // usually this is correct, no change needed
$exclude = $substitute = $copyrow = $relative = $relative_exclude = $relative_substitute = $relative_clause = array();

$unset = array(); // helper

// default fields to exclude
$donotcopy = array("id", "ts", "updatedby"); 

// debug string
$dbg = "";


/*** SETUP AND CHECKS ***/

// set specific translations for this script
$_t->setSection("copyrecord");

if(!$_user->canCopy() or !$_user->canAdd()){
	$output['error'] = $_t->get('nopermission'); // translation in general section 
	$output['msg'] = $_t->get('nopermission_message'); // translation in general section 
	echo json_encode($output);
	die();	
}

// sanitize post values
$recid 	= (int) $_POST['record'];

// Check if record id is passed
if(empty($recid)){
	$output['error'] = $_t->get('norec'); // translation in general section 
	$output['msg'] = $_t->get('norec_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// Check if page id is passed
if(empty($pid)){
	$output['error'] = $_t->get('nopage'); // translation in general section 
	$output['msg'] = $_t->get('nopage_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// get page file_name
$page = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");

// if page is not found send error
if(!$page){
	$output['error'] = $_t->get('pagenotset'); // translation in general section 
	$output['msg'] = sprintf ($_t->get('pagenotset_message'), $pid); // translation in general section 
	echo json_encode($output);
	die();
}

// set filename
$page = $page.".php";

// include switchfile if it exists, else send error
if(file_exists("copy/".$page)){
	include_once "copy/".$page;
}else{
	$output['error'] = $_t->get('noswitch_file'); // translation in general section 
	$output['msg'] = sprintf($_t->get('noswitch_file_message'), $page); // translation in general section 
	echo json_encode($output);
	die();
}

// if table is not set exit with error
if(!$table){
	$output['error'] = $_t->get('no_copy_table'); // translation in general section 
	$output['msg'] = $_t->get('no_copy_table_message'); // translation in general section 
	echo json_encode($output);
	die();
}	


/*** MAIN ROUTINE - COPY MASTER RECORD ***/

// If switchfile has not already extracted record use THE standard method
if(empty($copyrecord)){	
	$copyrecord = $db->get1row($table, "WHERE ".$field." = '".$recid."'");	
}

// if no data is retrieved exit with error
if(empty($copyrecord)){
	// If no data is found exit
	$output['error'] = $_t->get('no_copy_data'); 
	$output['msg'] = $_t->get('no_copy_data_message').$db->getQuery();
	echo json_encode($output);
	die();
}

// let's exclude some fields
if(!in_array($field, $exclude)) $exclude[] = $field; // add the $field to the list of fields to unset if not already there
$unset = array_merge($donotcopy, $exclude); // add custom fields to default fields
$unset = array_flip($unset); // invert values and keys

// remove from $copyrecord all elements with key in $unset
$copyrecord = array_diff_key($copyrecord, $unset);

// Substitute fields
if(array_key_exists("insertedby", $copyrecord)) $copyrecord["insertedby"] = $_SESSION['login_id'];

// if custom substitute must be done
if(!empty($substitute)){
	foreach($substitute as $f => $v){
		if(array_key_exists($f, $copyrecord)) $copyrecord[$f] = sprintf($v, $copyrecord[$f]); // if the value has '%s' insert original value in string
	}
}

// insert new record
$copyrecord = $db->make_data_safe($copyrecord);
if(!$db->insert($table, $copyrecord, array_keys($copyrecord))){
	$output['error'] = $_t->get('insert_failed'); 
	$output['msg'] = sprintf ($_t->get('insert_failed_message'), $db->getquery());  
	
	echo json_encode($output);
	die();	
}

$insertid = $db->get_insert_id();

/*** END MAIN ROUTINE ***/


/*** ROUTINE 2 - REALTIVE RECORDS ***/

// got realtive tables to manage
if(!empty($relative)){
	
	// start loop over the reltive tables
	foreach($relative as $relative_table => $relative_field){
		
		// get all the relative data form the table
		$relative_data = $db->select_all($relative_table, "WHERE ".$relative_field." = '".$recid."'".$relative_clause[$relative_table]);		
		
		// getthe list of fields to exclud from copy	
		$unset_relative = (empty($relative_exclude[$relative_table])) ? $donotcopy : array_merge($donotcopy, $relative_exclude[$relative_table]);
		$unset_relative = array_flip($unset_relative); // invert values and keys
		
		// if data is found proceed with the routine
		if($relative_data){
			
			$dbg .=  $relative_table." -> ".count($relative_data)." rows - ";
			
			// loop every row of extracted data
			foreach($relative_data as $row){
				
				// exclude fields from row
				$row = array_diff_key($row, $unset_relative);

				// let's see if we must substitute some data
				if(!empty($relative_substitute[$relative_table])){
					
					// loop over each field thatmust be subsituted
					foreach($relative_substitute[$relative_table] as $subsfield => $subsvalue){
						
						// if the value of the array corrisponds to a key of the amin copied record use that value else consider it a staic value as is
						if(array_key_exists($subsvalue, $copyrecord)) $subsvalue = $copyrecord[$subsvalue];
						
						// substitute value in $row
						$row[$subsfield] = $subsvalue;
						
					}
					
				}
				
				// substitute field record with insertid of main record
				$row[$relative_field] = $insertid;
				if(array_key_exists("insertedby", $row)) $row["insertedby"] = $_SESSION['login_id'];
				
				// insert row in table
				if(!$db->insert($relative_table, $row, array_keys($row))){
					$output['error'] = "reltive insert failed!"; 
					$output['msg'] = $db->getQuery();
					echo json_encode($output);
					die();
				}
				
			} // end loop over rows of extracted data
			
		} // end if found data
		
	} // end loop over tables
	
} // end if relative

/*** END ROUTINE 2 ***/

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['record'] = $insertid;
$output['dbg'] = "relative tables: ".count($relative)." | ".$dbg;


$_SESSION['success_title'] = $_t->get('copy_success'); // set message to display on page reload
$_SESSION['success_txt'] = $_t->get('copy_success_message'); // set message to display on page reload
$_SESSION['copied'] = $_t->get('label_record_copiato'); // set message to display on page reload


echo json_encode($output);

	
?>
