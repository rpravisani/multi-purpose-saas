<?php
/*****************************************************
 * DELETE A RECORD.                                  *
 * WILL INCLUDE SWITCH SCRIPT THAT'S STORED IN THE   *
 * DEL FOLDER AND MUST BE NAMED AS THE PAGE FILENAME *
 * # IN: RECORD (INT), PAGE ID (INT)                 *
 * # OUT: TRUE OR ERROR MESSAGE ($OUTPUT)            *
 * TODO: MULTI-RECORD DELETE?                        *
 *****************************************************/

/*****************************************************
  DEPENDENCIES. 
  SONO QUELLE TABELLE CORRELATE AL RECORD CHE VOGLIAMO CANCELLARE, COME PER ESEMPIO TABELLE DI UN ORDIN
  QUANDO CANCELLIAMO UN AGENTE O UN CLIENTE. CANCELLANDO IL RECORD ALLE TABELE IN QUESTIONE MANCHEREBBE
  UN JOIN.
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("delrecord");

// default vars
$stop = false; // if true no record will be deleted, value of this var can be changed in include switch file
$stop_msg = $_t->get('default_stop_message'); // get from translations default stop message, actual message should be set in switch file
$hasfoto = false; // // TODO if true photographs will be deleted from $photodir (defined in switch file)
$photodir = ""; // directory of photographs -- TODO
$aggregator = false; // only used in portale immobiliare
$unpublish = false; // if true turns off record instead of deleting it
$extra_clause = array(); // variable that can be set in switchfile (i.e. $extra_clause['table_name'] = "AND column = 'value') 
$reset_order = false; // TODO if true data will be reordered in table (table must have order column and $order_clause must not be empty)
$order_clause = ""; // TODO must contain clause to be used in reorder records if $reset_order is true
$dependencies = array();
$delete_dependencies = false;

$reassign = array(); // array avente chiave tabella e come valore array degli id a cui riassgnare un nuovo valore
$reassign_values = array(); // array con valori per creare select da cui scegliere nuovo valore da attribuire
$reassign_field = array(); // array avente chiave tabella e come valore una stringa che indica il campo della tabella da cambiare
$value2reassign = false; // the new value to reassign to the table that has a dependency with the record to delete

$output['errornum'] = 0;
$multidel = false;
$records_deleted = array();

// Check if record id is passed
if(empty($_POST['record'])){
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

// sanitize post values
$recids = $db->make_data_safe($_POST['record']);

if($_POST['disable'] == 'true') $unpublish = true;
if($_POST['deldependencies'] == 'true' and !$unpublish) $delete_dependencies = true;
if(!empty($_POST['reassignvalue'])) $value2reassign = $_POST['reassignvalue'];

// let's see if current user has permission to delete a record from this page
if( !$_user->canDelete() ){
	$output['error'] = $_t->get('nodelpermission'); // translation in general section 
	$output['msg'] = $_t->get('nodelpermission_message'); // translation in general section 
	echo json_encode($output);
	die();
}

if(is_array($recids)){
	// if $recids is an array I used checkboxes. To avoid complexity only records that have no stop or dependencies can be deleted or disabled
	$delete_dependencies = false;
	$multidel = true;
}else{
	$recids = array($recids);
}

$recids_flat = implode(",", $recids); // for any case, p.e. in switch file...

// get page name
$page = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");

// if page is not set: send error
if(!$page){
	$output['error'] = $_t->get('pagenotset'); // translation in general section 
	$output['msg'] = sprintf ($_t->get('pagenotset_message'), $pid); // translation in general section 
	echo json_encode($output);
	die();
}

// get translations specific for switch file (basically the stop_message)
$_t->setSection($page, true); // second param is true so the translations of the section will be added to the existing ones

// set filename
$page = $page.".php";

// include switchfile if it exists, else send error
if(file_exists("del/".$page)){
	include_once "del/".$page;
}else{
	$output['error'] = $_t->get('noswitch_file'); // translation in general section 
	$output['msg'] = sprintf($_t->get('noswitch_file_message'), $page); // translation in general section 
	echo json_encode($output);
	die();
}


// if stop === true script stops and won't delete anything
if($stop and !$multidel){
	if(empty($stop_msg)) $stop_msg = $_t->get('default_stop_message');
	$output['error'] = $_t->get('stop'); // translation in general section  
	$output['msg'] = $stop_msg;
	echo json_encode($output);
	die();
}

// if switch file has revealed  interrupt script and report back to user giving him an option to interrupt deleting
if(!empty($reassign)){
	
	if(empty($value2reassign)){
		// restituisco select da cui scegliere il nuovo valore
		$output['errornum'] = 2; // when errornum == 2 the js script will produce a popup with the options to  choose new value from - after the choice is made it will turn back to this script
		$output['errorlevel'] = "warning"; // color of modal box
		$output['error'] = $_t->get('reassign'); // translation in delrecord section 


		$selectnewvalue = getSelectOptions($reassign_values['key'], $reassign_values['value'], 
										   $reassign_values['table'], array(), $reassign_values['order'], 
										   $reassign_values['clause'], false);


		$selectnewvalue = "<div class='row'><div class='col-md-10'><select id='reassign-select' class='select2'>".$selectnewvalue."</select></div></div>";
		$output['msg'] = $_t->get('reassign-message').$selectnewvalue; // translation in page specific translation
		echo json_encode($output);
		die();
		
	}else{
		// ho un nuovo valore da impostare
		foreach($reassign as $table2reassign => $ids2reassign){
			$ids2reassign_flat = implode(", ", $ids2reassign);
			
			$updatefield = $reassign_field[$table2reassign];
			
			$db->update($table2reassign, array($updatefield => $value2reassign), "WHERE id IN (".$ids2reassign_flat.")");
			
		}
	}
	
}

// if switch file has revealed dependencies interrupt script and report back to user giving him an option to interrupt deleting
if(!empty($dependencies) and !$unpublish and !$delete_dependencies){
	$output['errornum'] = 1; // when errornum == 1 the js script will produce a popup with the options to delete dependencies or to just disable the main record - ether choise will turn back to this script
	$output['errorlevel'] = "warning"; // color of modal box
	$output['optionDisable'] = $_t->get('option-disable-btn'); // text of disable button
	$output['optionDeleteDependencies'] = $_t->get('option-delete-dependencies-btn'); // text of delete dependencies
	$output['error'] = $_t->get('askdependencies'); // translation in delrecord section  
	$output['msg'] = $_t->get('askdependencies-message'); // translation in page specific translation
	echo json_encode($output);
	die();
}


// only for portale immobiliare
if($aggregator){
	$table[TABELLA_RICERCA_LIBERA] = 'immobile';
}

/*** RUN DELETE OR UNPUBLISH QUERY ON ALL TABLES ***/
// $table must be an array with the tablename as key and the (primary) key as value
if(!is_array($table)) $table = array( $table => "id" );	

foreach($recids as $recid){ // records loop
	
	if($multidel and in_array($recid, $stop) ) continue;
	
	foreach($table as $tab=>$key){ // tables loop
		
		if($unpublish){
			$qry = "UPDATE ".$tab." SET active='0' WHERE ".$key."='".$recid."'";
		}else{
			$qry = "DELETE FROM ".$tab." WHERE ".$key."='".$recid."'";
		}
		if(!empty($extra_clause[$tab])) $qry .= " " . $extra_clause[$tab];
		$output['qry'] .= $qry."\n"; // for debug...

		if(!$db->execute_query($qry)){
			$output['error'] = $_t->get('qryerror');  
			$output['msg'] = sprintf ($_t->get('qryerror_message'), $db->getquery());
			$output['errorlevel'] = "warning"; 	
			echo json_encode($output);
			die();		
		} // end if !$db->execute_query($qry)
		
	}
	$records_deleted[] = $recid;
	
	// dependencies should have the table name as key and an array of id's to delete
	if($delete_dependencies and !empty($dependencies)){
		
		foreach($dependencies as $deptab => $deprecs){
			$ids_to_delete = implode("', '", $deprecs); 
			$db->delete($deptab, "WHERE id IN ('".$ids_to_delete."')");
			
		}
	}
	
}  // end foreach

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['deleted'] = $records_deleted;

if($multidel){
	if(empty($records_deleted) ){
		$output['error'] = $_t->get('no_record_deleted'); // in delrecord 
		$output['msg'] = $_t->get('no_record_deleted_message'); // page specific
	}else{
		$output['error'] = $_t->get('some_records_deleted'); // in delrecord 
		$output['msg'] = sprintf($_t->get('some_records_deleted_message'), count($records_deleted), count($recids));		
	}
}

echo json_encode($output);

	
?>
