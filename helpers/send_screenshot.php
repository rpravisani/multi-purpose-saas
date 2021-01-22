<?php
/*****************************************************
 * SEND EMAIL WITH SCREENSHOT AND USER MESSAGE       *
 *****************************************************/

// include default includes, definitions ($output) and objects ($db, $_t) 
include_once '../calls/_head.php';
include_once '../required/classes/cc_user.class.php';
session_start();

$_user = new cc_user($_SESSION['login_id'], $db);

// set specific translations for this script
//$_t->setSection("elenco-stock");

// sanitize
$pid 			 = (int) $_POST['page'];
$url 			 = $db->make_data_safe($_POST['url']);
$message		 = nl2br($_POST['msg']);
$screenshot_name = (string) $_POST['screenshot'];
$screenshot 	 = SCREENSHOT_PATH.$screenshot_name;

$pagename = ($pid !== 0) ? $db->get1value("file_name", DBTABLE_PAGES, "WHERE id = '".$pid."'") : "Dashboard";

$date = date("Y-m-d H:i:s", time());
$email_text = "";

$ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : "-";
$user_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "unknown";

/*
ALTER TABLE `logs_tickets`  ADD `ip` VARCHAR(45) NOT NULL COMMENT 'IPV4 or IPV6'  AFTER `user`,  ADD `user_agent` VARCHAR(255) NOT NULL COMMENT 'Browser and device'  AFTER `ip`;
*/

// write to logs_tickets table in DB
$fields = array("date", "pagename", "pid", "url", "screenshot", "message", "user", "ip", "user_agent");
$values = array($date, $pagename, $pid, $url, $screenshot_name, $message, $_SESSION['login_id'], $ip, $user_agent);
$db->insert(DBTABLE_TICKETS, $values, $fields);

$id_ticket = $db->get_insert_id();
$pid_ticket = $db->get1value("id", DBTABLE_PAGES, "WHERE file_name = 'support-ticket'");
$ticket_url = HTTP_PROTOCOL.HOSTROOT.SITEROOT."cpanel.php?pid=".$pid_ticket."&v=html&a=update&r=".$id_ticket;

if($_configs['testing'] == '1') $email_text .= "*** TESTING FASE ***<br>";

// set email content
$email_text .= SCREENSHOT_EMAIL_TEXT;
$email_text .= "Pagina: <strong>".$pagename." (".$pid.")</strong><br>";
$email_text .= "URL pagina segnalazione: <a href='".$url."'>".$url."</a></strong><br>";
$email_text .= "User: <strong>".$_user->getName()." (".$_SESSION['login_id'].")</strong><br>";
$email_text .= "IP & device: <strong>".$ip." | ".$user_agent."</strong><br><br>";

$email_text .= "URL ticket: <a href='".$ticket_url."'>".$ticket_url."</a></strong><br><br>";
$email_text .= "Messaggio:<br><em><strong>".$message."</strong></em><br>";

//$subject = "Segnalazione Errore da Portale TPL del ".date("d/m/Y H:i:s", time());
$subject = sprintf(SCREENSHOT_EMAIL_SUBJECT, date("d/m/Y H:i:s", time()) );

// send to superadmin
$adresses = array(SA_EMAIL);

// use real email of admin instead of noreply adress
$from_email = $_user->getEmail();
if(empty($from_email) or $_configs['testing'] == '1') $from_email = SUPERVISOR_EMAIL;

// attach screenshot
$attachments[] = $screenshot;

// do not redirect after send
$redirect_after = false;

include '../required/send-email.php';

if(empty($_SESSION['success_message'])){
	// got error	
	$output['error'] = $_SESSION['error_title']; // title of modal box
	$output['msg'] = $_SESSION['error_message']; // message inside modal box
	$output['errorlevel'] = "danger"; // color of modal box
}else{
	$output['result'] = true;
	$output['error'] = ""; // title of modal box
	$output['msg'] = "Grazie per la sua segnalazione!<br>Prenderemo in carico il problema segnalato al più presto e la contatteremo."; // message inside modal box
	$output['errorlevel'] = "success"; // color of modal box
}

unset($_SESSION['error_title']);
unset($_SESSION['error_message']);
unset($_SESSION['success_title']);
unset($_SESSION['success_message']);

echo json_encode($output);

?>
