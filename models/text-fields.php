<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         
 *** filename: text-fields.php                   
 *** List of the dynamic text fieds used in project
 *** that the user can edit
 ***                                              
 *****************************************************/


$fields = array("id", "id", "CONCAT(label, '|', name)", "description", "html", "dynamic_fields", "active" );

$alias	= array("id", 
				"see", 
				"name", 
				"desc", 
				"html", 
				"dynamic", 
				"active-column-name");

$qrytmpl = "
	SELECT 
	%s
	FROM 
	`".DBTABLE_TEXT_FIELDS."`";




// sort on (use alias name)
$_table->sortme = "label";
$_table->sortdir = "asc"; // asc o desc

// dont' sort these columns (use alias name)
$_table->nosort[] = "vedi";


// row buttons
$_table->del = true;
$_table->copy = true;
$_table->eye = false;

/************************** FORMATTING COLUMN DATA **************************/
$_table->format['name'] = "labelName";
$_table->format['description'] = "truncate";
$_table->format['html'] = "check";
$_table->format['dynamic'] = "check";



// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);

$table = $_table->getTable(true);


?>