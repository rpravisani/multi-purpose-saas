<?php
session_start();
include 'required/variables.php';

// aggiorno access log
if(LOG_ACCESS and !empty($_SESSION['access_log_id'])){
	include_once 'required/classes/cc_mysqli.class.php';
	$db = new cc_dbconnect(DB_NAME);
	$values['logout_datetime'] = date("Y-m-d H:i:s", time()); 
	$values['manual_logout'] = '1'; 
	$db->update(DBTABLE_ACCESS_LOGS, $values, "WHERE id = '".$_SESSION['access_log_id']."'");
}

// cancello tutti i record dell'utente in page_locks
$db->delete("page_locks", "WHERE user = '".$_SESSION['login_id']."'");

session_unset();
session_destroy(); 
header('location:'.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
?>
