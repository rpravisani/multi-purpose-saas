<?php
defined('_CCCMS') or die;
/*****************************************
 *** MODEL                             ***
 *** filename: user-edit.php           ***
 *** Inserisci e modifica utenti       ***
 *****************************************/
$email_mandatory = "required"; // TODO move to config table
$email_mandatory = ""; // TODO move to config table

// get data if $_record is not empty
if(!empty($_record)){
	// date the data from the table
	$_data = $db->get1row(LOGIN_TABLE, "WHERE id='".$_record."'");
	
	unset($_data['password']);
	
	$lid 	= $_data['language'];
	$nid 	= $_data['nation'];
	$tid 	= $_data['timezone'];
	$stid 	= $_data['subscription_type'];
	$pmid 	= $_data['payment_method'];
	$subscription_date 	= cc_date_us2eu($_data['subscription_date']);
	$last_renew 		= cc_date_us2eu($_data['last_renew']);
	$expiry_date 		= cc_date_us2eu($_data['expiry_date']);
	
	$pwd_mandatory = "";
	$pwd_title = "Leave empty to keep the old one...";
	$checked = ($_data['checked'] == '1') ? "checked" : ""; 

	$user_preferences_data = (empty($_data['preferences'])) ? unserialize(DEFAULT_USER_PREFS) : unserialize($_data['preferences']);

	// Estrapolazione tutte le pagine
	$all_pages = $db->select_all(DBTABLE_PAGES, "WHERE active = '1'");

	// Estrapolazione pagine permesse
	$user_pages = $db->col_value("page", DBTABLE_PAGE_PERMISSIONS, "WHERE subscription = '".$_data['subscription_type']."'");
	
	$boxtitle = "Modifica utente";
}else{
	$lid = $nid = $tid = $stid = $pmid = '0';
	$pwd_mandatory = "required";
	$pwd_title = "Enter password...";
	$boxtitle = "Inserisci un nuovo utente";
	$subscription_date = $last_renew = date("d/m/Y", time() );
	$exiry_date = date("d/m/Y", time() * 60*60*24*365);
	$checked = "";
}

// get list of languages
$languages = getSelectOptions("code", "language", DBTABLE_LANGUAGES, $lid, false, "WHERE active = '1'", true);

// get list of nations
$nations = getSelectOptions("id", "name", DBTABLE_NATIONS, $nid, false, "WHERE active = '1'", true);

// get list of timezones
$timezones = getSelectOptions("zone_name", "zone_name", 'timezones', $tid, false, "", true);

// get list of subscription types
$subscription_types = getSelectOptions("id", "name", DBTABLE_SUBSCRIPTION_TYPES, $stid, false, "WHERE active = '1'", true);

// get list of payment methods 
$payment_methods = getSelectOptions("id", "name", DBTABLE_PAYMENT_METHODS, $pmid, false, "WHERE active = '1'", true);


/*** PREFERENCES ***/
if(!empty($user_preferences_data)){
	$user_preferences = "<table width='100%' id='preferencestable' class='table no-margin'>\n";
	$c=0;
	foreach($user_preferences_data as $k=>$v){
		$c++;
		$user_preferences .= '<tr><td>';
		$user_preferences .= '<input type="text" class="form-control" name="preferences_name['.$c.']" id="preferences_name_'.$c.'" value="'.$k.'">';
		$user_preferences .= '</td><td>';
		$user_preferences .= '<input type="text" class="form-control" name="preferences_value['.$c.']" id="preferences_value_'.$c.'" value="'.$v.'">';
		$user_preferences .= '</td>';
		$user_preferences .= '<td width="10"><div class="btn btn-block btn-danger btn-xs delrow"><i class="fa fa-fw fa-times"></i></div></td></tr>';
	}
	$user_preferences .= "</table>\n";
	$user_preferences .= "<div class='pull-right'><div class=\"btn btn-primary btn-xs\" id=\"addrow\"><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;Add Prefer.</div></div>\n";
}

/*** PAGE PERMISSIONS ***/
if(!empty($user_pages)){
	$page_tab = makeTable(0,0,$user_pages);
}

/*** AVATARS ***/
$avatar_path = FILEROOT."avatars/";
$avatar_options = "";
$files_in_avatar_folder = scandir($avatar_path);
if(!empty($files_in_avatar_folder)){
	foreach($files_in_avatar_folder as $file_in_avatar_folder){
		if($file_in_avatar_folder == "." or $file_in_avatar_folder == ".." ) continue;
		$fiaf = explode(".", $file_in_avatar_folder);
		$ext = end($fiaf);
		if($ext == 'png' or $ext == 'jpg' or $ext == 'jpeg'){
			$selected = ($_data['avatar'] == $file_in_avatar_folder) ? "selected" : "";
			$avatar_options .= "<option ".$selected." value='".$file_in_avatar_folder."'>".$file_in_avatar_folder."</option>\n";
		}
	}
}

function makeTable($parent = 0, $level = 0, $permissions){
	global $db;
	
	$tabclass = "page_level_".$level;
	if(empty($permissions)) $permissions = array();
	
	$qry_loop = "
	SELECT p.id, CONCAT(p.icon, ' ', p.icon_class) AS icon, p.name
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
		
		$tabella = "<table width='100%' class='table no-margin ".$tabclass."'>\n";
		if($level == 0){
			$tabella .= "<thead>\n";
			$tabella .= "<tr>";
	
			foreach($loop[0] as $th => $dummy){
				if($th == 'id') continue;
				$thn = ucfirst($th);
				$tabella .= "<th>".$thn."</th>";
			}
			$tabella .= "<th>&nbsp;</th>";
			$tabella .= "</tr>\n";
			$tabella .= "</thead>\n";
		}

		$tabella .= "<tbody>\n";
		
		foreach($loop as $l){
			$gotChilds = $db->col_value("id", DBTABLE_PAGES, "WHERE parent = '".$l['id']."'");
			$tabella .= "<tr id='".$l['id']."'>";
			$cols = count($l)+1;
			
			
			foreach($l as $th => $td){
				
				if($th == 'id' ) continue;
				
				$align = "left";
				$width = "";
				
				if($th == 'icon'){
					$width = "width='10'";
					$td = "<i class=\"fa fa-".$td."\"></i>";
				}
				
				$tabella .= "<td ".$width." align='".$align."'>".$td."</td>";

			} // end foreach $l
			
			$figli = $db->col_value("id", "pages", "WHERE parent = '".$l['id']."'");

			$class  = ($figli) ? "gotchildren " : "";
			$class .= "pcheck";

			$checked = ( in_array($l['id'], $permissions) ) ? "<i class=\"fa fa-check\"></i>" : "&nbsp;";
			$tabella .= "<td width='80' align='center'>".$checked."</td>";
			
			$tabella .= "</tr>\n";
			
			// guardo se questa voce ha figli, se sì richiamo in ricursivo questa funzione
			if($figli){
				$newLevel = $level+1;
				$tabella .= "<tr>";
				$tabella .= "<td colspan='".$cols."'>";
				$tabella .= makeTable($l['id'], $newLevel, $permissions);
				$tabella .= "</td>";
				$tabella .= "</tr>";				
			} // end if figli
		
		} // end foreach loop
		$tabella .= "</tbody>";	
		$tabella .= "</table>";	
		
		return $tabella;
		
	} // end if $loop

} // end function
?>