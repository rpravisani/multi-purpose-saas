<?php
/***************************************************************
 * ACTIVATE O DISACTIVATE A RECORD.                            *
 * WILL INCLUDE SWITCH SCRIPT THAT'S STORED IN THE             *
 * PUBLISH FOLDER AND MUST BE NAMED AS THE PAGE FILENAME       *
 * # IN: RECORD (INT), PAGE ID (INT) AND ON/OFF SWITCH (BOOL)  *
 * # OUT: TRUE OR ERROR MESSAGE ($OUTPUT)                      *
 ***************************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("onoff");

if(!$_user->canActivate()){
	$output['error'] = $_t->get('nopermission'); // translation in general section 
	$output['msg'] = $_t->get('nopermission_message'); // translation in general section 
	echo json_encode($output);
	die();
	
}
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
// Check if on/off switch is passed
if(empty($_POST['onoff'])){
	$output['error'] = $_t->get('noswitch');;
	$output['msg'] = $_t->get('noswitch_message');;
	echo json_encode($output);
	die();
}

// sanitize
$recid 	= (int) $_POST['record'];
$switch 	= $_POST['onoff'];

$page = $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'");

// if page is not found send error
if(!$page){
	$output['error'] = $_t->get('pagenotset');;
	$output['msg'] = sprintf ($_t->get('pagenotset_message'), $pid);
	echo json_encode($output);
	die();
}

$page = $page.".php";

if($switch == "off"){
	// it's off, turn it on...
	$active = 1;
}else if ($switch == "on"){
	// it's on, turn it off...
	$active = 0;
}else{
	$output['error'] = $_t->get('novalidswitch');;
	$output['msg'] = sprintf ($_t->get('novalidswitch_message'), $switch);
	echo json_encode($output);
	die();
}

// set value of table column
$update = array("active"=>$active);

// include switchfile if it exists, else send error
if(file_exists("publish/".$page)){
	include_once "publish/".$page;
}else{
	$output['error'] = $_t->get('noswitch_file'); // translation in general section 
	$output['msg'] = sprintf( $_t->get('noswitch_file_message'), $page ); // translation in general section 
	echo json_encode($output);
	die();
}

// $table must be an array with the tablename as key and the index value as value
if(!is_array($table)) $table = array( $table => "id" );	

if(!$stop){ // stop is defined in switch file
	// update tables
	foreach($table as $t => $k){
		if($db->update($t, $update, "WHERE ".$k." = '".$recid."'")){ // var $table is set in included file
			$output['result'] = true;
			$output['error'] = false;
			$output['msg'] = "";
		}else{
			$output['errorlevel'] = "warning"; // color of modal box
			$output['error'] = $_t->get('update_fail'); // translation in general section
			$output['msg'] = sprintf ($_t->get('update_fail_message'), $db->getquery()); // translation in general section
		}
		
	}
}else{
		$output['errorlevel'] = "warning"; // color of modal box
		$output['error'] = $_t->get('cant_switch_onoff'); // translation in general section
		$output['msg'] = sprintf ($_t->get('cant_switch_onoff_message'), $db->getquery()); // translation in switch page
}

echo json_encode($output);

?>
