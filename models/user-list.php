<?php
defined('_CCCMS') or die;
/***********************************************
 *** MODEL                                   ***
 *** filename: user-list.php                 ***
 *** list of all the users of the framework  ***
 ***********************************************/

$fields 	= array("u.id", "u.id", "u.id", "u.username", "CONCAT(u.name, ' ', u.surname)", "u.email", "l.code", "u.timezone", "s.name", "u.expiry_date", "u.checked", "u.active");
$alias	= array("id", "see", "progr", "user", "fullname", "email", "lang", "tzone", "subscription", "date", "checked", "active-column-name");

$qrytmpl = "
	SELECT 
	%s
	FROM 
		".LOGIN_TABLE." AS u,  
		".DBTABLE_SUBSCRIPTION_TYPES." AS s, 
		".DBTABLE_LANGUAGES." AS l 
	WHERE s.id = u.subscription_type 
	AND l.code = u.language
";


// column values format
$_table->format['checked'] = "check";
$_table->format['lang'] = "lang";


// sort on (use alias name)
$_table->sortme = "user";
$_table->sortdir = "asc"; // asc o desc

// dont' sort these columns (use alias name)
//$_table->nosort[] = "mm";

// row buttons
$_table->del = true;
$_table->copy = false;
$_table->eye = false;


/*** CREATING FILTER DATA / ADD CLAUSES TO QUERY BASED ON ACTIVE FILTERS ***/

// subscription types
$subscription_types = $db->key_value("id", "name", DBTABLE_SUBSCRIPTION_TYPES, "WHERE active = '1' ORDER BY name");
$subscription_types[0] = "Tutti";

$qrytmpl .= ( empty($_filter['subscription']) ) ? "" : " AND u.subscription_type = '".$_filter['subscription']."'";


// Creating the query...
$_table->createQuery($fields, $alias, $qrytmpl);

$qrydbg = $_table->getQry(false);


$table = $_table->getTable(true);



?>