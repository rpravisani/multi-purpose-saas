<?php

/*** PAGE PERMISSION ***/

if(!empty($permissions)){
	$old_permissions = $db->col_value("subscription", DBTABLE_PAGE_PERMISSIONS, "WHERE page = '".$recordid."'");

	if(empty($old_permissions)){
		// don't have any permissions set insert them now
		foreach($permissions as $subadd){
			$db->insert(DBTABLE_PAGE_PERMISSIONS, array($recordid, $subadd), array("page", "subscription"));
		}
	}else{
        $add = array_diff($permissions, $old_permissions);
        $remove = array_diff($old_permissions, $permissions);
		
		if(!empty($add)){
			foreach($add as $subadd){
				$db->insert(DBTABLE_PAGE_PERMISSIONS, array($recordid, $subadd), array("page", "subscription"));
			}
		}

		if(!empty($remove)){
			$remove_flat = implode(",", $remove);
			$db->delete(DBTABLE_PAGE_PERMISSIONS, "WHERE page = '".$recordid."' AND subscription IN (".$remove_flat.")");
		}
		
	}

}

/*** END PAGE PERMISSION ***/

/*** PAGE ORDER GAP REMOVING ***/
$neworder = 1;
$pages_ordered = $db->col_value("id", DBTABLE_PAGES,  "WHERE parent='".$parent_page."' ORDER BY `order`");
foreach($pages_ordered as $pageid){
	$db->update(DBTABLE_PAGES, array("order" => $neworder), "WHERE id = '".$pageid."'");
	$neworder++;
}

?>