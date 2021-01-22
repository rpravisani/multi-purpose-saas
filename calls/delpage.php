<?php
/********************************************************
 * DELETE A PAGE AND POSSIBLE CHILD-PAGES.              *
 * IF FLAG delchildren IS TRUE A LOOP IS PERFOMED       *
 * WHERE THE SCRIPT WILL FIND AL NON SYSTEM PAGES       *
 * THAT ARE CHILD OF THE PAGE TO BE DELETED.            *
 * # IN: pageid (INT), gotchildren (INT)                *
 * # OUT: LIST OF PAGE IDS TO BE REMOVED FROM ON-SCREEN *
 *        TABLE OR ERROR MESSAGE ($OUTPUT)              *
 ********************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
$_t->setSection("delpage"); // section to be made

// variables
$deleted 			= array();
$delete_page 		= true;
$delchildren 		= false;
$parent 				= 0;
$output['delall'] 	= false;

// Check if pageid is passed
if(empty($_POST['pageid'])){
	$output['error'] = $_t->get('nopid'); // translation in general section 
	$output['msg'] = $_t->get('nopid_message'); // translation in general section 
	echo json_encode($output);
	die();
}

// sanitize post values
$pid 			= (int) $_POST['pageid'];
$delchildren 	= (bool) $_POST['delchildren'];

// see if page exists
$check = $db->get1row(DBTABLE_PAGES, "WHERE id = '".$pid."'");
if(!$check){
	$output['error'] = $_t->get('nopage'); // translation in pages section 
	$output['msg'] = $_t->get('nopage_message'); // translation in pages section 
	echo json_encode($output);
	die();
}

$parent = (int) $check['parent'];
 
// determine if there are child pages to be deleted
if($delchildren){
	
	// let's see if the page's got children...
	$children = $db->select_all(DBTABLE_PAGES, "WHERE parent = '".$pid."'");
	
	if(!$children){
		$output['error'] = $_t->get('nochildren'); // translation in pages section 
		$output['msg'] = $_t->get('nochildren_message'); // translation in pages section 
		//$output['msg'] = $db->getQuery(); // translation in general section 
		echo json_encode($output);
		die();
	}
	
	// loop children and delete them if they are not system pages
	foreach($children as $child){
		if($child['system_page'] == '0'){
			if($db->delete(DBTABLE_PAGES, "WHERE id = '".$child['id']."'")){
				$deleted[] = $child['id'];
				// delete page-permissions
				$db->delete(DBTABLE_PAGE_PERMISSIONS, "WHERE page = '".$child['id']."'");
				// delete translations
				if(!empty($child['file_name'])) $db->delete(DBTABLE_TRANSLATIONS, "WHERE section = '".$child['file_name']."'");
				
			}
		}
	}
	
	// If the number of records in the array $deleted is different from the numer of child-pages found
	// there where some system pages or other issues and the $delete_page flag is set to false and an error_message is compiled
	if(count($deleted) != count($children) ){
		
		// There where some system pages or other issues
		$output['error'] = $_t->get('not-all-deleted'); // translation in pages section
		$output['msg'] = $_t->get('not-all-deleted-msg'); //translation in pages section
		$output['errorlevel'] = "warning"; // color of modal box
		$delete_page = false; // not realy necessary because of die() statement further down, but still set it to false
		$output['result'] = true; // still true because some child pages were probably deleted
		$output['delrows'] = $deleted; // the list of id's of the deleted pages
		echo json_encode($output);
		die();
		

	}
	
}

if($delete_page){
	
	if(!$db->delete(DBTABLE_PAGES, "WHERE id = '".$pid."'")){
		$output['error'] =  $_t->get('did-not-delete');
		$output['msg'] = $_t->get('did-not-delete-msg').$db->getError("msg")."<br>\n".$db->getQuery();
		echo json_encode($output);
		die();
	}
	
	// everything went fine, add id of page to the $deleted array
	$deleted[] = $pid;
	// ok give ok to delete the whole child page table view
	$output['delall'] = true;
	
	// delete page-permissions
	$db->delete(DBTABLE_PAGE_PERMISSIONS, "WHERE page = '".$pid."'");
	// delete translations
	if(!empty($check['file_name'])) $db->delete(DBTABLE_TRANSLATIONS, "WHERE section = '".$check['file_name']."'");
	
	// set result to true because page(s) was/where deleted...
	$output['result'] = true;

	
	/*** RESET PAGE ORDER ***/
	$page_order = (int) $check['order'];
	
	$qry = "UPDATE ".DBTABLE_PAGES." SET `order` = `order`-1 WHERE `order` > ".$page_order." AND parent = '".$parent."'";
	if($db->execute_query($qry)){
		// get new orders for ajax feedback
		$output['neworder'] = $db->key_value("id", "order", DBTABLE_PAGES, "WHERE parent = '".$parent."'");
		$output['error'] = "";
		$output['msg'] = $db->getQuery();
	}else{
		$output['error'] =  $_t->get('order');
		$output['msg'] = $_t->get('order-msg').$db->getError("msg")."<br>\n".$db->getQuery();
	}

}

$output['delrows'] = $deleted;

echo json_encode($output);

?>
