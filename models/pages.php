<?php
defined('_CCCMS') or die;
/*************************************
 *** MODEL                         ***
 *** filename: pages.php           ***
 *** a list of all the pages of    ***
 *** the framework                 ***
 *************************************/


function makeTable($parent = 0, $level = 0){
	global $db;
	
	$tabclass = "page_level_".$level;
	// tolto per ora checkbox (p.id AS chck)
	$qry_loop = "
	SELECT p.id as link, p.id, CONCAT(p.icon, ' ', p.icon_class) AS icon, p.name, 
			p.file_name, x.file_name AS modify_page, x.id AS link, p.type, p.view, p.action, p.order, p.active, p.id AS del, p.system_page 
	FROM ".DBTABLE_PAGES." AS p 
	LEFT JOIN ".DBTABLE_PAGES." AS x 
	ON x.id = p.modify_page
	WHERE p.parent = '".$parent."'
	ORDER BY p.order
	";

	$loop = $db->fetch_array($qry_loop);

	if(!$loop){
		return false;
	}else{
		
		$tabella = "<table class='table no-margin ".$tabclass."'>\n";
		$tabella .= "<thead>\n";
		$tabella .= "<tr>";

		foreach($loop[0] as $th => $dummy){
			if($th == 'file_name' or $th == 'system_page' or $th == 'modify_page') continue;
			if($th == 'link' or $th == 'chck') $th = "";
			$tabella .= "<th>".ucfirst($th)."</th>";
		}
		$tabella .= "</tr>\n";
		$tabella .= "</thead>\n";

		$tabella .= "<tbody>\n";
		
		foreach($loop as $l){
			$gotChilds = $db->col_value("id", DBTABLE_PAGES, "WHERE parent = '".$l['id']."'");
			$tabella .= "<tr id='".$l['id']."'";
			$tabella .= ($l['type'] == 'label') ? " class='trlabel'>" : ">";
			
			$cols = count($l)-1;
			
			foreach($l as $th => $td){
				if($th == 'file_name' or $th == 'system_page' or $th == 'modify_page') continue;
				
				if($th == 'active') $td = ($l['active'] == '1') ? "<i data-onoff=\"on\" class=\"fa onoff fa-toggle-on\"></i>" : "<i data-onoff=\"off\" class=\"fa onoff fa-toggle-off\"></i>";			
				if($th == 'chck') $td = "<input type='checkbox' data-page='".$td."' id='page".$td."' class='pcheck' value='1'>";			
				if($th == 'link') $td = "<i class=\"goto fa fa-search\" data-record=\"".$l['id']."\" data-pid=\"28\" data-action=\"update\" data-view=\"html\"></i>";			
				if($th == 'icon') $td = "<i class=\"fa fa-".$td."\"></i>";
				if($th == 'del'){
					$nodelTitle = ($l['system_page'] == '1') ? "System page" : "Parent";
					$deleteClass = empty($gotChilds) ? "delete-page" : "delete-children";
					//$td = ($l['system_page'] == '1' or !empty($gotChilds)) ? "<i title='".$nodelTitle." - cannot be deleted' class=\" text-muted fa fa-fw fa-trash\"></i>" : "<i class=\"puls delete fa fa-fw fa-trash\"></i>";
					$td = ($l['system_page'] == '1') ? "<i title='".$nodelTitle." - cannot be deleted' class=\" text-muted fa fa-fw fa-trash\"></i>" : "<i class=\"puls ".$deleteClass." fa fa-fw fa-trash\"></i>";
				}
				if($th == 'name' and !empty($l['file_name'])) $td .= "<br><small><em>(".$l['file_name'].")</em></small>";
				if($th == 'name' and !empty($l['modify_page'])) $td .= " <small><i data-link='".$l['link']."' data-toggle='tooltip' title='".$l['modify_page']." (".$l['link'].")' class='fa fa-link text-muted'></i></small>";
				
				$align = ($th == 'link' or $th == 'active' or $th == 'icon' or $th == 'del' or $th == 'chck') ? "center" : "left";
				
				$tdclass = strtolower(str_replace(" ", "-", $th));
				if($th == 'link') $tdclass = "see";
				if($th == 'active') $tdclass = "active-column-name";				
				
				$tabella .= "<td class='".$tdclass."' align='".$align."'>".$td."</td>";

			} // end foreach $l
			
			$tabella .= "</tr>\n";
			
			// guardo se questa voce ha figli, se sì richiamo in ricursivo questa funzione
			$figli = $db->col_value("id", "pages", "WHERE parent = '".$l['id']."'");
			if($figli){
				$newLevel = $level+1;
				$tabella .= "<tr>";
				$tabella .= "<td colspan='".$cols."'>";
				$tabella .= makeTable($l['id'], $newLevel);
				$tabella .= "</td>";
				$tabella .= "</tr>";				
			} // end if figli
		
		} // end foreach loop
		$tabella .= "</tbody>";	
		$tabella .= "</table>";	
		
		return $tabella;
		
	} // end if $loop

} // end function

$tabella = makeTable(0); 





?>