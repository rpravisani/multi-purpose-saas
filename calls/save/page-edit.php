<?php
$tables = array(DBTABLE_PAGES);

// 'action' is a reserved key, so I had to use 'pageaction' in the module. Now setting it back to action
$safevalues['action'] = $safevalues['pageaction'];
unset($safevalues['pageaction']);

// 'view' is a reserved key, so I had to use 'pageaction' in the module. Now setting it back to action
$safevalues['view'] = $safevalues['pageview'];
unset($safevalues['pageview']);


/*** PAGE PERMISSION ***/
$permissions = $safevalues['permissions']; // will be used in post file
unset($safevalues['permissions']);
/*** END PAGE PERMISSION ***/


/*** PAGE ORDER LOGIC ***/
$ord = (int) $safevalues['order'];
$parent_page = $safevalues['parent']; // will be used in post file
$maxorder = $db->get_max_row("order", DBTABLE_PAGES, "WHERE parent='".$parent_page."'");
if(!$maxorder) $maxorder = 0;

$old_ord = ($action == "insert") ? (int) $maxorder+1 : (int) $db->get1value("`order`", DBTABLE_PAGES, "WHERE id = '".$recordid."'");

if($order > $maxorder){
	// ok the order of this page is higher than the max order value in db, but let's make sure it's maxorder+1
	$order = $maxorder + 1;
}else if($order < 1){
	// Make sure it's not lower then 1
	$order = 1;
}

if($ord < $old_ord){
	// new position is smaller than old position - moving to the left /  move inbetweens to the right (+)
	$update_qry = "UPDATE ".DBTABLE_PAGES." SET `order` = `order` + 1 
					WHERE parent='".$safevalues['parent']."' 
					AND `order` BETWEEN '".$ord."' AND '".$old_ord."'";
					
}else{
	// new position is larger than old position - moving to the right /  move inbetweens to the left (-)
	$update_qry = "UPDATE ".DBTABLE_PAGES." SET `order` = `order` - 1 
					WHERE parent='".$safevalues['parent']."' 
					AND `order` BETWEEN '".$old_ord."' AND '".$ord."'";
					
}

// update inbetween pages
$db->execute_query($update_qry);


/*** END PAGE ORDER LOGIC ***/

?>