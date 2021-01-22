<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: dashboard.php                       ***
 *** In this case as for now it allows to set      ***
 *** translations for the various active languages ***
 *****************************************************/

// get all active languages
$languages = $db->key_value("code", "language", DBTABLE_LANGUAGES, "WHERE active = '1' ORDER BY id"); 
// only the codes (eg. "en" or "it")
$lang_codes = array_keys($languages); 

// get all the available sections - last true = distinct
$sections = $db->col_value("section", DBTABLE_TRANSLATIONS, "ORDER BY section", true); 

// add filename pages to sections
$pages = $db->col_value("file_name", DBTABLE_PAGES, "ORDER BY 'order'", true);

$sections = array_merge($sections, $pages ); // add pages to

// $_GET VALUES
$section = $_GET['section'];
if(empty($section)) $section = " ";

$language = $_GET['lang'];
if(empty($language)) $language = $lang_code; 

// HIGHLIGHT THE SEARCH FIELD
$id_field = $search = "";
$showsearch = "style=\"display: none;\"";
if(!empty($_GET['field'])){
	$id_field = (int) $_GET['field'];
	$search = (string) $_GET['s'];
	$showsearch = "";
	
}


// RETRIEVE TRANSLATION STRINGS FOR SECTION AND LANGUAGE
$qry = "SELECT id, string, translation 
       FROM ".DBTABLE_TRANSLATIONS." 
	   WHERE language = '".$language."' AND section = '".$section."' ORDER BY string";

$translations = $db->fetch_array($qry);
if(empty($translations)) $translations = array();

foreach($translations as $c=>$row){
	$highlight_field = ($id_field == $row['id']) ? "class=\"highlight\"" : "";
	$t['id'] = $c+1;
	$t['string'] = "<input style=\"width: 100%\" ".$highlight_field." type=\"text\" id=\"string-".$row['id']."\" name=\"string-".$row['id']."\"  value=\"".$row['string']."\">";
	$t['translation'] = "<input style=\"width: 100%\" ".$highlight_field." type=\"text\" id=\"translation-".$row['id']."\" name=\"translation-".$row['id']."\"  value=\"".$row['translation']."\">";
	$t['del'] = "<button data-id=\"".$row['id']."\" class=\"btn btn-block btn-danger btn-xs delrow\"><i class=\"fa fa-fw fa-times\"></i></button>";
	$tbody[$c] = $t;
}

$thead = array("#", $_t->get('string-column-name'), $_t->get('translation-column-name'), $_t->get('delete-column-name')); // column definition
$tabconfigs[1]["width"] = "5%"; 
$tabconfigs[1]["align"] = "center";
$tabconfigs[2]["width"] = "25%";
$tabconfigs[4]["width"] = "5%"; 
$tabconfigs[4]["align"] = "center";
$table = $bootstrap->table($thead, $tbody, "bordered", "translations", $tabconfigs); // create bootstrap table html


// RETRIEVE OPTIONS FOR SELECT
$sections = array_combine($sections, $sections); // ???

$languageOptions = $bootstrap->getSelectOptions($languages, $language, false);
$sectionOptions = $bootstrap->getSelectOptions($sections, $section, false);


?>