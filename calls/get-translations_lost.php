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
$lang = (string) $db->make_data_safe( $_POST['lang'] );
$section = (string) $db->make_data_safe( $_POST['section'] );
$lang = strtolower($lang);

$clause = "WHERE lang = '".$lang."' AND `ignore` = '0'";

if(!empty($section)) $clause .= " AND file = '".$section."'";

if(!empty($_POST['exclude'])) $clause .= " AND string NOT IN ('".implode("', '", $_POST['exclude'])."')";
//$exclude = (empty($_POST['exclude'])) ? "" : " AND string NOT IN ('".implode("', '", $_POST['exclude'])."')";

$getlostt = $db->select_all(DBTABLE_TRANSLATIONS_LOST, $clause);
if(!$getlostt){
	$output['errorcode'] = "nolost"; // translation in translate section 
	$output['error'] = $_t->get("nolost"); // translation in translate section 
	$output['msg'] = $_t->get("nolost.msg");; // translation in translate section 
	echo json_encode($output);
	die();
}

$tabella_lostt  = "<div class='inner-modal'>\n";
$tabella_lostt .= "<table class='table no-margin'>\n";
$tabella_lostt .= "<thead>\n";
$tabella_lostt .= "<tr>";

$tabella_lostt .= "<th>String</th><th><em>Filename</em></th><th>Add</th>\n";

$tabella_lostt .= "</tr>\n";
$tabella_lostt .= "</thead>\n";

$tabella_lostt .= "<tbody>\n";

foreach($getlostt as $lostt_row){

	$btn = '<button class="btn btn-block btn-success btn-xs addlost icon-only" data-field="'.$_POST['field'].'"><i class="fa fa-fw fa-plus"></i></button>';
	$tabella_lostt .= "<tr>";
	$tabella_lostt .= "<td class='string' align='left'>".$lostt_row['string']."</td>";
	$tabella_lostt .= "<td align='left'><em>".$lostt_row['file']."</em></td>";
	$tabella_lostt .= "<td align='center' style='width: 30px'>".$btn."</td>";
	
	$tabella_lostt .= "</tr>";
}
$tabella_lostt .= "</tbody>\n";
$tabella_lostt .= "</table>\n";	
$tabella_lostt .= "</div>\n";	

$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['html'] = $tabella_lostt;
$output['dbg'] = $clause;
$output['title'] = "Translations lost";


echo json_encode($output);

	
?>
