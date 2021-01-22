<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$qry_ts_updated = "
ALTER TABLE `%s`  
ADD `active` BOOLEAN NOT NULL DEFAULT TRUE ,  
ADD `insertedby` INT NOT NULL COMMENT 'id tab users' ,  
ADD `updatedby` INT NOT NULL COMMENT 'id tab users' ,  
ADD `ts` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Changes with updates'
";

$qry_ts_not_updated = "
ALTER TABLE `%s`  
ADD `active` BOOLEAN NOT NULL DEFAULT TRUE ,  
ADD `insertedby` INT NOT NULL COMMENT 'id tab users' ,  
ADD `updatedby` INT NOT NULL COMMENT 'id tab users' ,  
ADD `ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Does not change with updates'
";

if( !empty($_POST) ){
	
	foreach($_POST as $table){
		
		$qry = sprintf($qry_ts_updated, $table);
		$db->execute_query($qry);
	}
	
}


$alltables = $db->getTablenames("data_");

$system_fields = array("active", "insertedby", "updatedby", "ts");


if(!empty($alltables)){
	foreach($alltables as $i => $table){
		$fields = $db->get_column_names($table);
		$check = array_diff($system_fields, $fields);
		if( !empty($check) ) $tables[$i] = $table;
		if( !empty($check) ) $missingfields[$i] = implode(" ", $check);
	}
}

if(empty($tables)){

	$out = "<h2>Tutte le tabelle data_ hanno già attribuiti i campi di sistema</h2>\n";
	
}else{

	$out  = "<h3>Seleziona la/le tabella/e a cui aggiungere i campi di sistema</h3>\n";
	$out .= "<form name='addfields' method='post' action='add_system_fields_in_table.php'>\n";
	foreach($tables as $i => $table){
		$out .= "<input type='checkbox' name='".$i."' id='".$table."' value='".$table."' data-table='".$table."'>";
		$out .= " ".$table." <small><em>".$missingfields[$i]."</em></small><br>\n";
	}
	$out  .= "<button>Conferma</button>\n";
	$out  .= "</form>\n";
	
	
}


?>


<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Add system fields to table</title>
</head>

<body>
<?php
	
echo $out;
	
?>
</body>
</html>