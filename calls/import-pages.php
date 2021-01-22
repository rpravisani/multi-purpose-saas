<?php
/*****************************************************
 * search-translation                                *
 * Search for translated string in DB                *
 * IN: (POST) searchfor (string to search),          *
 *            language (in xx format)                *
 * OUT: section (string) and id of tranlation table  *
 *      or error in case non found                   *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';
$output['errorcode'] = "";

// set specific translations for this script
$_t->setSection("translate");

// santitise
$db_host = $db->make_data_safe($_POST['host']);
$db_user = $db->make_data_safe($_POST['user']);
$db_pwd = $db->make_data_safe($_POST['pwd']);
$db_name = $db->make_data_safe($_POST['db']);
$page_list = $db->make_data_safe($_POST['pages']);

// connecrt to other DB
$other_db = new cc_dbconnect($db_name, $db_host, $db_user, $db_pwd);

// get pages from other DB
$page_list_flat = implode("', '", $page_list);
$pages = $other_db->select_all("pages", "WHERE id IN ('".$page_list_flat."')");

// get order of last parent page in this DB
$last_order = (int) $db->get_max_row("order", "pages", "WHERE parent = '0'");


if($pages){
	$map = array();
	$npages = count($pages);
	foreach($pages as $page){
		
		/*** REMAP CHILD'S PARENT AND MODIFY_PAGE PROCEDURE ***/
		$id = $page['id'];

		// Remove id and ts - we will use the ones of this db
		unset($page['id']);
		unset($page['ts']);
		
		// remap parent if child
		if($page['parent'] != '0') $page['parent'] = $map[$page['parent']];
		
		// remap order if parent
		if($page['parent'] == '0') { $last_order++; $page['order'] = $last_order; };
		
		
		/*** INSERT IN THIS DB ***/

		// get fields from page array (i'm lazy!)
		$fields = array_keys($page);

		// insert row in this DB
		$db->insert("pages", $page, $fields);
		
		// get insert id
		$map[$id] = $db->get_insert_id();
		
	}
	// update modify_page
	foreach($map as $oldid=>$newid){
		$row = $db->get1row("pages", "WHERE id = '".$newid."'");
		if($row['modify_page'] != '0'){
			$new_mod_page = $map[$row['modify_page']];
			$db->update("pages", array("modify_page" => $new_mod_page), "WHERE id = '".$newid."'");
		}
		
	}

	$_SESSION['success_title'] 	= "Pages imported correctly";
	$_SESSION['success_message'] 	= "A total of <strong>".$npages."</strong> pages where correctly imported from<strong> ".$db_name."</strong> database";
	
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['dbg'] = $clause;
}else{
	// something went wrong!
	$output['error'] = "No page found";
	$output['msg'] = "Could not find any page!";
	$output['dbg'] = $other_db->getQuery();
}






echo json_encode($output);

	
?>
