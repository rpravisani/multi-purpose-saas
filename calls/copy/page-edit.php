<?php
/******************************
 * INCLUDED IN COPYRECORD.PHP *
 ******************************/
$table = DBTABLE_PAGES;

// manually extract data (need value of 'parent' field for order below)
$copyrecord = $db->get1row($table, "WHERE id = '".$recid."'");

// set order of page, shift it to the end
$maxorder = $db->get_max_row("order", DBTABLE_PAGES, "WHERE parent='".$copyrecord['parent']."'");
if(!$maxorder) $maxorder = 0;
$maxorder++;

// system page flag only set by hand directly in db
$exclude = array("system_page");

// add "copy of" to name of the page, force copied page to end of parent section and deactivated esle it will show directy up in menu
$substitute = array("name" => "Copia di %s", "order" => $maxorder, "active" => '0');

// copy page permissions
$relative[DBTABLE_PAGE_PERMISSIONS] = 'page';

?>