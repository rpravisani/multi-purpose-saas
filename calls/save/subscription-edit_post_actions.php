<?php
/*** PAGE PERMISSIONS ***/
$all_pages = $db->col_value("id", DBTABLE_PAGES);

if(!empty($all_pages)){
	
	$already_permitted = $db->key_value("id", "page", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$recordid."'");
	if(empty($already_permitted)) $already_permitted = array();
	
	foreach($all_pages as $ppid){
		
		$write2db = ($show_permissions[$ppid] + $add_permissions[$ppid] + $edit_permissions[$ppid] + $copy_permissions[$ppid] + $delete_permissions[$ppid] + $activate_permissions[$ppid] + $readonly_permissions[$ppid] == 0) ? false : true;
				
		// if the page is already peritted delete value from $already_permitted so that in the end only  
		// the pages that are no longer permitted remain
		if( in_array($ppid, $already_permitted) ){

			// get the key of the page_permissions record
			$key = array_search($ppid, $already_permitted);

			// update write and delete permissions
			$db->update(DBTABLE_PAGE_PERMISSIONS, array("showmenu" => (int) $show_permissions[$ppid], "canadd" => (int) $add_permissions[$ppid], "canmod" => (int) $edit_permissions[$ppid] , "cancopy" => (int) $copy_permissions[$ppid], "candelete" => (int) $delete_permissions[$ppid], "canactivate" => (int) $activate_permissions[$ppid] , "readonly" => (int) $readonly_permissions[$ppid] ), "WHERE id = '".$key."'" );
			
			unset($already_permitted[$key]);
		}else{
			// add the page to the permitted pages
			$db->insert(DBTABLE_PAGE_PERMISSIONS, array(
                $ppid, $recordid, (int) $show_permissions[$ppid], (int) $add_permissions[$ppid], 
                (int) $edit_permissions[$ppid], (int) $copy_permissions[$ppid], (int) $delete_permissions[$ppid], 
                (int) $activate_permissions[$ppid], (int) $readonly_permissions[$ppid]), 
                        array("page", "subscription", "showmenu", "canadd", "canmod", "cancopy", "candelete", "canactivate", "readonly") );
		}
	}
	
	// if $already_permitted is not empty I need to eliminate these record from the table
	if(!empty($already_permitted)){
		foreach($already_permitted as $apid => $dummy){
			$db->delete(DBTABLE_PAGE_PERMISSIONS, "WHERE id = '".$apid."'");
		}
	}
}else{
	// empty.. could be that I haven't select any page, delete any entry in page_permissions
	$db->delete(DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$recordid."'");
}


?>