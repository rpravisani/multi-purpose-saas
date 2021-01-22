<?php
defined('_CCCMS') or die;
/******************************************
 *** MODEL                              ***
 *** filename: subscription-edit.php    ***
 *** Insert and edit subscription types ***
 ******************************************/

// get data if $_record is not empty
if(!empty($_record)){
	

	
	// Get the data from the table
	$_data = $db->get1row(DBTABLE_SUBSCRIPTION_TYPES, "WHERE id='".$_record."'");
	
	// Unserialize the params and restrictions of this subscription
	$subscription_params_data = (empty($_data['params'])) ? unserialize(DEFAULT_SUBSCRIPTION_PARAMS) : unserialize($_data['params']);
	
	/***********************************************************************************************************
	                                                      PAGE PERMISSIONS 
	************************************************************************************************************/
	// all pages in permission table

	$permitted_pages_data = $db->select_all(DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$_record."'");
	$permitted_pages = ( empty($permitted_pages_data) ) ? array() : array_column($permitted_pages_data, "page");
		
	$boxtitle = "Edit subscription type";
	
}else{
	
	$subscription_params_data = $permitted_pages = array();
	$boxtitle = "Insert new subscription type";
	
}

/*** PARAMS ***/
if(!empty($subscription_params_data)){
	$subscription_params = "<table width='100%' id='paramtable' class='table no-margin'>\n";
	$c=0;
	foreach($subscription_params_data as $k=>$v){
		$c++;
		$subscription_params .= '<tr><td>';
		$subscription_params .= '<input type="text" class="form-control" name="param_name['.$c.']" id="param_name_'.$c.'" value="'.$k.'">';
		$subscription_params .= '</td><td>';
		$subscription_params .= '<input type="text" class="form-control" name="param_value['.$c.']" id="param_value_'.$c.'" value="'.$v.'">';
		$subscription_params .= '</td>';
		$subscription_params .= '<td width="10"><div class="btn btn-block btn-danger btn-xs delrow"><i class="fa fa-fw fa-times"></i></div></td></tr>';
	}
	$subscription_params .= "</table>\n";
	$subscription_params .= "<div class='pull-right'><div class=\"btn btn-primary btn-xs\" id=\"addrow\"><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;Add Param.</div></div>\n";
}


/*** PAGE PERMISSIONS ***/
if(!empty($_record)){
	$page_tab = makeTable(0, 0, $permitted_pages_data);
}

function makeTable($parent = 0, $level = 0, $permitted_pages_data){
	global $db;
	
	// permissions
	$permissions = ( empty($permitted_pages_data) ) ? array() : array_column($permitted_pages_data, "page");
	if(!empty($permitted_pages_data)){
		foreach($permitted_pages_data as $row){
			$canshow[$row['page']] 	= (bool) $row['showmenu'];
			$canadd[$row['page']] 	= (bool) $row['canadd'];
			$canedit[$row['page']] 	= (bool) $row['canmod'];
			$cancopy[$row['page']]  = (bool) $row['cancopy'];
			$candel[$row['page']] 	= (bool) $row['candelete'];
			$canact[$row['page']] 	= (bool) $row['canactivate'];
			$readonly[$row['page']] = (bool) $row['readonly'];
		}			
	}
	
	$tabclass = "page_level_".$level;
	
	$qry_loop = "
	SELECT p.id, CONCAT(p.icon, ' ', p.icon_class) AS icon, p.name, p.active
	FROM ".DBTABLE_PAGES." AS p 
	WHERE p.parent = '".$parent."' AND system_page = '0' AND type != 'label'
	ORDER BY p.order
	";

	$loop = $db->fetch_array($qry_loop);

	if(!$loop){
		return false;
	}else{
		
		// start defining table 
		$tabella = "<table width='100%' class='table no-margin ".$tabclass."'>\n";
		// output thead only if level 0
		if($level == 0){
			$tabella .= "<thead>\n";
			$tabella .= "<tr>";
			
			// get column names
			foreach($loop[0] as $th => $dummy){
				if($th == 'id' or $th == 'active') continue; // skip id and active
				$thn = ucfirst($th); // format column name
				$tabella .= "<th>".$thn."<br>&nbsp;</th>"; // add to table html
			}
			// add checkboxes
			$tabella .= "<th style='text-align: center'>Show menu<br><input type='checkbox' class='selectall' data-col='pshow' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Readonly<br><input type='checkbox' class='selectall' data-col='pread' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Edit<br><input type='checkbox' class='selectall' data-col='pedit' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Add<br><input type='checkbox' class='selectall' data-col='padd' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Copy<br><input type='checkbox' class='selectall' data-col='pcopy' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Del.<br><input type='checkbox' class='selectall' data-col='pdel' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Act.<br><input type='checkbox' class='selectall' data-col='pact' value='0'></th>";

			$tabella .= "</tr>\n"; // close row
			$tabella .= "</thead>\n"; // close thead
		}

		$tabella .= "<tbody>\n"; // open tbody
		
		// loop found pages (rows)
		foreach($loop as $l){
			
			$tr_class = ($l['active']) ? "" : "not-in-menu";

			$tabella .= "<tr class='selectrow ".$tr_class."' id='".$l['id']."'>"; // add row to html
			$cols = count($l)+7; // col counter
			
			// loop columns
			foreach($l as $th => $td){
				
				if($th == 'id' or $th == 'active') continue; // skip id and active
				
				$align = "left"; // default alignment
				$width = ""; // default width param
				
				// if icon format cell width and content
				if($th == 'icon'){
					$width = "width='10'";
					$td = "<i class=\" fa fa-".$td."\"></i>";
				}
				// add cell to html
				$tabella .= "<td ".$width." align='".$align."'>".$td."</td>";

			} // end foreach $l (cols of qry_loop)
			
			// see if page's got children
			$figli = $db->col_value("id", "pages", "WHERE parent = '".$l['id']."'"); // di nuovo?

			// format class if page got children
			$class  = ($figli) ? "gotchildren " : "";
			$class .= "pcheck";

			// is page permitted for this subscription?
			$checked_show = ( $canshow[$l['id']]) ? "checked" : "";
			$checked_add  = ( $canadd[$l['id']]) ? "checked" : "";
			$checked_edit = ( $canedit[$l['id']]) ? "checked" : "";
			$checked_copy = ( $cancopy[$l['id']]) ? "checked" : "";
			$checked_del  = ( $candel[$l['id']]) ? "checked" : "";
			$checked_act  = ( $canact[$l['id']]) ? "checked" : "";
			$checked_read = ( $readonly[$l['id']]) ? "checked" : "";
			
			// add checkbox
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pshow[".$l['id']."]' id='pshow_".$l['id']."' value='1' ".$checked_show."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pread[".$l['id']."]' id='pread_".$l['id']."' value='1' ".$checked_read."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pedit[".$l['id']."]' id='pedit_".$l['id']."' value='1' ".$checked_edit."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='padd[".$l['id']."]' id='padd_".$l['id']."' value='1' ".$checked_add."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pcopy[".$l['id']."]' id='pcopy_".$l['id']."' value='1' ".$checked_copy."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pdel[".$l['id']."]' id='pdel_".$l['id']."' value='1' ".$checked_del."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pact[".$l['id']."]' id='pact_".$l['id']."' value='1' ".$checked_act."></td>";
			
			// close table row
			$tabella .= "</tr>\n";
			
			// guardo se questa voce ha figli, se sì richiamo in ricursivo questa funzione
			if($figli){
				$newLevel = $level+1; // increment level			
				$tabella .= "<tr>"; // add new row
				$tabella .= "<td colspan='".$cols."'>"; // new cell to contain nested table
				$tabella .= makeTable($l['id'], $newLevel, $permitted_pages_data); // get nested table
				$tabella .= "</td>"; // close cell
				$tabella .= "</tr>"; // close row		
			} // end if figli
		
		} // end foreach loop
		$tabella .= "</tbody>";	
		$tabella .= "</table>";	
		
		return $tabella;
		
	} // end if $loop

} // end function

function makeTableOri($parent = 0, $level = 0, $permissions, $canwrite, $candelete){
	global $db;
	
	$tabclass = "page_level_".$level;
	if(empty($permissions)) $permissions = array();
	
	$qry_loop = "
	SELECT p.id, CONCAT(p.icon, ' ', p.icon_class) AS icon, p.name
	FROM ".DBTABLE_PAGES." AS p 
	WHERE p.parent = '".$parent."' AND system_page = '0' AND type != 'label'
	ORDER BY p.order
	";

	$loop = $db->fetch_array($qry_loop);

	if(!$loop){
		return false;
	}else{
		
		// start defining table 
		$tabella = "<table width='100%' class='table no-margin ".$tabclass."'>\n";
		// output thead only if level 0
		if($level == 0){
			$tabella .= "<thead>\n";
			$tabella .= "<tr>";
			
			// get column names
			foreach($loop[0] as $th => $dummy){
				if($th == 'id') continue; // skip id
				$thn = ucfirst($th); // format column name
				$tabella .= "<th>".$thn."<br>&nbsp;</th>"; // add to table html
			}
			// add checkboxes
			$tabella .= "<th style='text-align: center'>Perm.<br><input type='checkbox' class='selectall' data-col='pp' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Write / Show<br><input type='checkbox' class='selectall' data-col='pw' value='0'></th>";
			$tabella .= "<th style='text-align: center'>Del.<br><input type='checkbox' class='selectall' data-col='pd' value='0'></th>";
			$tabella .= "</tr>\n"; // close row
			$tabella .= "</thead>\n"; // close thead
		}

		$tabella .= "<tbody>\n"; // open tbody
		
		// loop found pages (rows)
		foreach($loop as $l){

			$tabella .= "<tr id='".$l['id']."'>"; // add row to html
			$cols = count($l)+3; // col counter
			
			// loop columns
			foreach($l as $th => $td){
				
				if($th == 'id' ) continue; // skip id
				
				$align = "left"; // default alignment
				$width = ""; // default width param
				
				// if icon format cell width and content
				if($th == 'icon'){
					$width = "width='10'";
					$td = "<i class=\"fa fa-".$td."\"></i>";
				}
				// add cell to html
				$tabella .= "<td ".$width." align='".$align."'>".$td."</td>";

			} // end foreach $l
			
			// see if page's got children
			$figli = $db->col_value("id", "pages", "WHERE parent = '".$l['id']."'"); // di nuovo?

			// format class if page got children
			$class  = ($figli) ? "gotchildren " : "";
			$class .= "pcheck";

			// is page permitted for this subscription?
			$checked_perm = ( in_array($l['id'], $permissions) ) ? "checked" : "";
			$checked_write = ( $canwrite[$l['id']] == '1' ) ? "checked" : "";
			$checked_del = ( $candelete[$l['id']] == '1' ) ? "checked" : "";
			
			// add checkbox
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pp[".$l['id']."]' id='pp_".$l['id']."' value='1' ".$checked_perm."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pw[".$l['id']."]' id='pw_".$l['id']."' value='1' ".$checked_write."></td>";
			$tabella .= "<td width='80' align='center'><input type='checkbox' class='".$class."' name='pd[".$l['id']."]' id='pd_".$l['id']."' value='1' ".$checked_del."></td>";
			// close table row
			$tabella .= "</tr>\n";
			
			// guardo se questa voce ha figli, se sì richiamo in ricursivo questa funzione
			if($figli){
				$newLevel = $level+1; // increment level			
				$tabella .= "<tr>"; // add new row
				$tabella .= "<td colspan='".$cols."'>"; // new cell to contain nested table
				$tabella .= makeTable($l['id'], $newLevel, $permissions, $canwrite, $candelete); // get nested table
				$tabella .= "</td>"; // close cell
				$tabella .= "</tr>"; // close row		
			} // end if figli
		
		} // end foreach loop
		$tabella .= "</tbody>";	
		$tabella .= "</table>";	
		
		return $tabella;
		
	} // end if $loop

} // end function
?>