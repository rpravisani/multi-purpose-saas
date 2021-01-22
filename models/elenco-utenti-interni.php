<?php
defined('_CCCMS') or die;
/*****************************************************
 *** MODEL                                         ***
 *** filename: elenco-clienti.php                  ***
 *** elenco dei clienti                            ***
 ***                                               ***
 *****************************************************/


$fields = array("u.id", "u.id", "CONCAT(u.name, ' ', u.surname)", "u.username", "CONCAT(s.name, '|', s.description)", "u.email ", "u.telephone", "u.active");
$alias	= array("id", "see", "Nome", "Username", "Tipo", "email", "Telefono", "active-column-name");


$qrytmpl = "
	SELECT 
	%s
	FROM 
		".LOGIN_TABLE." AS u 
	JOIN `subscription_types` AS s ON (s.id = u.subscription_type)
	WHERE u.subscription_type > 1
	";




// column values format

// sort on (use alias name)
$_table->sortme = "Nome";
$_table->sortdir = "asc"; // asc o desc

// dont' sort these columns (use alias name)
//$_table->nosort[] = "mm";

// row buttons
$_table->del = true;
$_table->copy = false;
$_table->eye = false;

$_table->format['Tipo'] = "tipoUtente";

// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);


$table = $_table->getTable(true);




?>