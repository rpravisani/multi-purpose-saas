<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: support-tickets-list.php            ***
 *** A list of the support tickets       		   ***
 *****************************************************/

// DA aggiungere una colonna concat con flag se barrellato, pesante etc e fare la stessa cosa che ho fatto per nome milite (icona di fianco a nome)

$fields 	= array(
				"t.id", 
				"t.id", 
				"t.ts", 
				"t.pagename", 
				"t.url", 
				"t.message", 
				"t.state", 
				"u.username");

$alias	= array("id", "see", "Data", "Page", "Url", "Message", "State", "User");

$qrytmpl = "SELECT %s FROM ".DBTABLE_TICKETS." AS t, ".LOGIN_TABLE." AS u WHERE u.id = t.user";


$_table->del = true;
$_table->edit = false;
$_table->copy = false;
$_table->eye = false;

//$_table->editablecols = array("valore" => "number", "denominazione" => "text");

// sort on (use alias name) TODO: in table engine far sì che se non è definito sortme prenda una colonna a caso.
$_table->sortme = "Data";
$_table->sortdir = "desc"; // asc o desc TODO: idem come sopra

$_table->format['Data'] = array("func" => "date", "params" => array("secs" => true) );;
$_table->format['Url'] = "url";
$_table->format['Message'] = "truncate";
$_table->format['State'] = "inlineLabel";


// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);


$table = $_table->getTable(true);


?>