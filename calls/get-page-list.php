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
$systemonly = ($_POST['systemonly'] == '1') ? true : false;

$other_db = new cc_dbconnect($db_name, $db_host, $db_user, $db_pwd);

// get all the current pages
$current_pages = $db->col_value("name", "pages", "", true);
$current_pages_flat = implode("', '", $current_pages);

// get page list
$parent_clause = "WHERE parent = '0' AND name NOT IN ('".$current_pages_flat."')";
if($systemonly) $parent_clause .= " AND system_page = '1'";
$order = " ORDER BY `order`";

$parents_qry = "SELECT id, name, icon, icon_class, system_page, active FROM pages ".$parent_clause.$order;
$parents = $other_db->fetch_array($parents_qry);

if($parents){
	$ul  = "<div id='page-listing'>\n";
	$ul .= "<ul class='list-unstyled'>\n";
	foreach($parents as $i=>$parent){
		$ul .= "<li class='parentpage'><input type='checkbox' id='page".$parent['id']."' data-page='".$parent['id']."'> ".$parent['name']." <i class='fa fa-".$parent['icon']." ".$parent['icon_class']."'></i>\n";
		if($parent['system_page'] == '1') $ul .= "<i class='fa fa-cogs text-muted'></i>";
		
		// let's see if we've got any children...
		$children_qry = "SELECT id, name, icon, icon_class, active, system_page FROM pages WHERE parent = '".$parent['id']."' AND name NOT IN ('".$current_pages_flat."')".$order;
		$children = $other_db->fetch_array($children_qry);
		if($children){
			$ul .= "<ul class='list-unstyled'>\n";
			foreach($children as $child){
				$ul .= "<li><input type='checkbox' id='page".$child['id']."' data-page='".$child['id']."'> ".$child['name']." <i class='fa fa-".$child['icon']." ".$child['icon_class']."'></i>";
				if($child['system_page'] == '1') $ul .= "<i class='fa fa-cogs text-muted'></i>";
				$ul .= "</li>\n";

			}
			$ul .= "</ul>\n";			
		}
		$ul .= "</li>\n";

	}
	$ul .= "</ul>\n";
	$ul .= "</div>\n";
}else{
	// no parents
	$ul = "No page found!";
}




$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['list'] = $ul;
$output['dbg'] = $clause;
$output['title'] = "Page list";


echo json_encode($output);

	
?>
