<?php
defined('_CCCMS') or die;
/********************************************
 *** MODEL                                ***
 *** filename: page-edit.php ***
 *** Inserisci e modifica lo stock        ***
 *** gomme del cliente (singola gomma)    ***
 ********************************************/

// get data if $_record is not empty
if(!empty($_record)){
	// date the data from the table
	$_data = $db->get1row(DBTABLE_PAGES, "WHERE id='".$_record."'");
	
	$checked_home = ($_data['home'] == '1') ? "checked" : "";
	
	$pageorder = $_data['order'];
	$pagetype = $_data['type'];
	$pageview = $_data['view'];
	 
	$boxtitle = "Modify page";
	
	// page permissions 
	$permissions = $db->col_value("subscription", DBTABLE_PAGE_PERMISSIONS, "WHERE page = '".$_record."'");
	
	if(!$permissions) $permissions = array();
	
}else{
	$_data = $permissions = array();
	$checked_home = "";
	$boxtitle = "Insert new page";
	
	// get last order of pages with no parent
	$pageorder = $db->get_max_row("order", DBTABLE_PAGES, "WHERE `parent` = '0'");
	if(!$pageorder) $pageorder = 0;
	$pageorder++;
	
	$pagetype = "custom";
	$pageview = "html";
	
}

$types 		= $db->get_enum_values(DBTABLE_PAGES, "type");
$views 		= $db->get_enum_values(DBTABLE_PAGES, "view");
$actions 	= $db->get_enum_values(DBTABLE_PAGES, "action");

$parents 		= $db->key_value("id", "name", DBTABLE_PAGES, "WHERE parent = 0 AND id != '".$_record."' ORDER BY `order`");
$subscriptions 	= $db->key_value("id", "name", DBTABLE_SUBSCRIPTION_TYPES, "WHERE active = 1 ORDER BY level DESC");

$options_type = $options_view = $checks_subscription = "";
$options_related = $options_parent = "<option value='0'></option>\n";
$options_action = $options_icons = "<option></option>\n";


foreach($types as $type){
	$select = ($type == $pagetype) ? "selected" : "";
	$options_type .= "<option ".$select." value='".$type."'>".$type."</option>\n";
}

foreach($views as $view){
	$select = ($view == $pageview) ? "selected" : "";
	$options_view .= "<option ".$select." value='".$view."'>".$view."</option>\n";
}

foreach($actions as $action){
	$select = ($action == $_data['action']) ? "selected" : "";
	$options_action .= "<option ".$select." value='".$action."'>".$action."</option>\n";
}

foreach($parents as $idparent =>$parent){
	$select = ($idparent == $_data['parent']) ? "selected" : "";
	$options_parent .= "<option ".$select." value='".$idparent."'>".$parent."</option>\n";

	// options for related
	
	// get childs
	$related_name 	= $db->key_value("id", "name", DBTABLE_PAGES, "WHERE parent = '".$idparent."' AND id != '".$_record."' ORDER BY `order`");
	$related_file 	= $db->key_value("id", "file_name", DBTABLE_PAGES, "WHERE parent = '".$idparent."' AND id != '".$_record."' ORDER BY `order`");
	if($related_name){
		$options_related .= "<optgroup label='".$parent."'>\n";
		foreach($related_name as $idrel =>$relname){
			$select = ($idrel == $_data['modify_page']) ? "selected" : "";
			$related_name_file = (empty($related_file[$idrel])) ? $relname : $relname." (".$related_file[$idrel].")";
			$options_related .= "<option ".$select." value='".$idrel."'>".$related_name_file."</option>\n";
		}
		$options_related .= "</optgroup>\n";
	}else{
		$select = ($idparent == $_data['modify_page']) ? "selected" : "";
		$options_related .= "<option ".$select." value='".$idparent."'>".$parent."</option>\n";
	}
		
	
}

// Permissions
foreach($subscriptions as $idsub =>$namesub){
	$checked = (in_array($idsub, $permissions)) ? "checked" : "";
	$checks_subscription .= "<input type='checkbox' ".$checked." name='permissions[".$idsub."]' id='permissions_".$idsub."' value='".$idsub."'>&nbsp;<small>".$namesub."</small>&nbsp;&nbsp;\n";
}

// icone fontawesome
if( ini_get('allow_url_fopen') ){
	//$url_fontawesome = 'http://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css';
	$fa_content = @file_get_contents(FONT_AWESOME_URL);
}else{
	// could not connect to url_fontawesome, get cached version
	if(file_exists(PATH_CACHE_FILES."font-awesome.css")){
		$fa_handle = fopen(PATH_CACHE_FILES."font-awesome.css", "r");
		$fa_content = fread($fa_handle, filesize(PATH_CACHE_FILES."font-awesome.css"));
		fclose($fa_handle);
	}
}





if($fa_content){
	preg_match_all("/\.fa-([a-z0-9-]+):before/", $fa_content, $fa_name_list_preg);
}

if(!empty($fa_name_list_preg)){
	$fa_name_list = $fa_name_list_preg[1];
	sort($fa_name_list);

	foreach($fa_name_list as $fa_name){
		$select = ($fa_name == $_data['icon']) ? "selected" : "";
		$options_icons .= "<option ".$select." value='".$fa_name."'>".$fa_name."</option>\n";
	}
}






?>