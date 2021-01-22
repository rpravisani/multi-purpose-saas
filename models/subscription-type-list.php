<?php
defined('_CCCMS') or die;
/***********************************************
 *** MODEL                                   ***
 *** filename: subscription-type-list.php    ***
 *** list of all the types of subscription   ***
 ***********************************************/

$fields 	= array("id", "id", "id", "name", "monthly_cost", "length", "level", "active");
$alias	= array("id", "see", "progr", "name", "monthly_cost", "duration", "level", "active-column-name");

$qrytmpl = "
	SELECT 
	%s
	FROM 
		".DBTABLE_SUBSCRIPTION_TYPES;
	

// column values format
$_table->format['level'] = "inlinelabel";
$_table->format['monthly_cost'] = "lang";


// sort on (use alias name)
$_table->sortme = "name";
$_table->sortdir = "asc"; // asc o desc

// dont' sort these columns (use alias name)
//$_table->nosort[] = "mm";

// row buttons
$_table->del = true;
$_table->copy = false;
$_table->eye = false;


// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);

$table = $_table->getTable(true);



?>