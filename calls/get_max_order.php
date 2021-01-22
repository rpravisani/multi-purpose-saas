<?php
/*****************************************************
 * get_max_order                                     *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '_head.php';

// set specific translations for this script
//$_t->setSection("elenco-stock");

$condition = "";

// sanitize
$table 		= (string) $_POST['tab'];
$field 		= (string) $_POST['field'];
$value 		= (string) $_POST['value'];
$column 		= (string) $_POST['column'];

if(empty($table)){
	$output['error'] = "No table set";
	$output['msg'] = "No table variable was passed!";
	echo json_encode($output);
	die();
}

if(empty($column)) $column = "order";

if( !empty($field) and !empty($value) ){
	$condition = " WHERE `".$field."` = '".$value."'";
}

$order = $db->get_max_row($column, $table, $condition);

if(!$order) $order = 0;

$order++;


// output
$output['result'] = true;
$output['error'] = "";
$output['msg'] = "";
$output['value'] = $order;

echo json_encode($output);

	
?>
