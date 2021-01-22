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

// set specific translations for this script
$_t->setSection("translate");

// santitise
$lang = (string) $db->make_data_safe( $_POST['language'] );
$lang = strtolower($lang);
$check_lang = $db->get1row(DBTABLE_LANGUAGES, "WHERE code = '".$lang."'");
if(!$check_lang){
	$output['error'] = $_t->get('nolang'); // translation in translate section 
	$output['msg'] = $_t->get('nolang_message'); // translation in translate section 
	$output['dbg'] = $db->getQuery(); 
	echo json_encode($output);
	die();
}

$searchfor = (string) $db->make_data_safe( $_POST['searchfor'] );
if(empty($searchfor)){
	$output['error'] = $_t->get('no_translate_search_string'); // translation in translate section 
	$output['msg'] = $_t->get('no_translate_search_string_message'); // translation in translate section 
	echo json_encode($output);
	die();
}

$gettranslation = $db->get1row(DBTABLE_TRANSLATIONS, "WHERE language = '".$lang."' AND translation LIKE '%".$searchfor."%'");

if($gettranslation){
	$output['result'] = true;
	$output['error'] = "";
	$output['msg'] = "";
	$output['section'] = $gettranslation['section'];
	$output['field'] = $gettranslation['id'];
	
}else{
	$output['error'] = $_t->get('transation_not_found'); // translation in translate section 
	$output['msg'] = $_t->get('transation_not_found_message'); // translation in translate section 
}

echo json_encode($output);

	
?>
