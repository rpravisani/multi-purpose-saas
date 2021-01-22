<?php
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);



if( 
	$db->delete("translations", "WHERE section NOT IN (
    SELECT DISTINCT file_name from pages
    ) AND section != 'LOGIN' AND section != ''" ) 
){
	echo "OK";
}else{
	echo $db->getError("msg")."<br>\n".$db->getquery();
}


?>
