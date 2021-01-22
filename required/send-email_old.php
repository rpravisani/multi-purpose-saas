<?php

/****************************************************************************************************************
 * SCRIPT CHE INVIA EMAIL.                                                                                      *
 * Non auto-sufficiente; dev'essere inclusa in altri script                                                     *
 * che dovranno settare:                                                                                        *
 * - $from_email (optional): email mittente, se lasciato vuoto prende valore default da variables.php           *
 * - $from_name (optional): Nome mittente, se lasciato vuoto prende valore default da variables.php             *
 * - $adresses (obbligatorio): array con email (chiave array) e nome (valore array) dei vari destinatiari       *
 * - $attachments (optional): array con nome degli allegati                                                     *
 * - $subject (obbligatorio): testo dell'oggetto dell'email                                                     *
 * - $email_text (obbligatorio): html con il corpo dell'email                                                   *
 * - $redirect_after (obbligatorio): true o false. (deaful: true) Se true reindirizza script concluso invio     *
 ****************************************************************************************************************/

/*** Start controlli e settaggi valori ***/
if(!isset($redirect_after)) $redirect_after = true;
if(!isset($log_email)) $log_email = true;

if(empty($from_email)) 	$from_email 	= NO_REPLY;
if(empty($from_name)) 	$from_name 	= NO_REPLY_NAME;

if(empty($adresses)){
	$_SESSION['error_title'] 	= "Impossibile inviare Email"; // TODO translation
	$_SESSION['error_message'] 	= "Nessun indirizzo destinatario settato!"; // TODO translation
	if($redirect_after){ 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return;
	}
}
if(!is_array($adresses)) $adresses = array( $adresses );

if(!empty($attachments)){
	if(!is_array($attachments)) $attachments = array( $attachments );
}

if(empty($subject)){
	$_SESSION['error_title'] 	= "Impossibile inviare Email"; // TODO translation
	$_SESSION['error_message'] 	= "Nessun oggetto settato!"; // TODO translation
	if($redirect_after){ 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return;
	}
}
$subject = strip_tags($subject);

if(empty($email_text)){
	$_SESSION['error_title'] 	= "Impossibile inviare Email"; // TODO translation
	$_SESSION['error_message'] 	= "Il corpo del messaggio è vuoto!"; // TODO translation
	if($redirect_after){ 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return;
	}
}
/*** Fine controlli e settaggi valori ***/


// ricupero classe per invio email
require_once 'PHPMailer/PHPMailerAutoload.php';

// creo oggetto
$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               	// Enable verbose debug output

// config
$mail->isSMTP();                                  		// Set mailer to use SMTP
$mail->Host 		= 'out.postassl.it';  	// Specify main and backup SMTP servers
$mail->SMTPAuth 	= true;                               	// Enable SMTP authentication
$mail->Username 	= 'portale@quota47.com';		// SMTP username
$mail->Password 	= 'anellissimo47';                           	// SMTP password
$mail->SMTPSecure 	= 'ssl';                            	// Enable TLS encryption, `ssl` also accepted
$mail->Port 		= 465;                                  // TCP port to connect to

$mail->From 		= $from_email;
$mail->FromName 	= $from_name;

foreach($adresses as $recipient_name => $recipient_email){
	if(is_numeric($recipient_name)){
		$mail->addAddress($recipient_email);
	}else{
		$mail->addAddress($recipient_email, $recipient_name);
	}
}
//$mail->addAddress('antealdi@yahoo.it');               // Name is optional
//$mail->addReplyTo('info@creativechaos.it', 'Information');
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');
if(!empty($attachments)){
	foreach($attachments as $attachment){
		$mail->addAttachment($attachment);
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	}
}

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $subject;
$mail->Body    = $email_text;
$mail->AltBody = strip_tags($email_text);

if(!$mail->send()) {
	$_SESSION['error_title'] = "Errore durante invio email"; // TODO translation
	$_SESSION['error_message'] = "Impossibile inviare email!<br>".$mail->ErrorInfo; // TODO translation
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}
}else{
	$_SESSION['success_title'] = "Email inviata correttamente"; // TODO translation
	$_SESSION['success_message']	= "L'email è stata inviata con successo"; // TODO translation
	unset($_SESSION['error_title']);
	unset($_SESSION['error_message']);
	if($log_email and DBTABLE_EMAIL_LOGS){
		$log_adresses = serialize($adresses);
		$log_script = $_SERVER['PHP_SELF'];
		$log_subject = $db->make_data_safe($subject);
		$log_body = $db->make_data_safe($email_text);
		$log_attachments = (empty($attachments)) ? "" : serialize($attachments);
		if(!isset($log_scheda)) $log_scheda = "";
		$db->insert(DBTABLE_EMAIL_LOGS, array($log_adresses, $log_subject, $log_body, $log_attachments, $log_scheda, $log_script), array("adresses", "subject", "body", "attachments", "scheda", "script"));
	}	
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}	
}




?>