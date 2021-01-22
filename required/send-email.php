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
if(!isset($session_message)) $session_message = true;


if(empty($from_email)) 	$from_email = NO_REPLY;
if(empty($from_name)) 	$from_name 	= NO_REPLY_NAME;

if(empty($adresses)){
	$_SESSION['error_title'] 	= "Impossibile inviare Email"; // TODO translation
	$_SESSION['error_message'] 	= "Nessun indirizzo destinatario settato!"; // TODO translation
	if($redirect_after){ 
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return false;
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
		return false;
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
		return false;
	}
}
/*** Fine controlli e settaggi valori ***/


// ricupero classe per invio email
require_once 'PHPMailer/PHPMailerAutoload.php';

// creo oggetto
$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               	// Enable verbose debug output

// config
$mail->isSMTP();
$mail->Host 		= 'out.postassl.it';  				// Specify main and backup SMTP servers
$mail->SMTPAuth 	= true;                             // Enable SMTP authentication
$mail->SMTPSecure 	= 'ssl';                            // Enable TLS encryption, accepted values: `ssl` e `ssl`
$mail->Port 		= 465; 

$mail->Username 	= 'portale@quota47.com';	// SMTP username
$mail->Password 	= 'anellissimo47';          // SMTP password
//$mail->Username 	= 'postmaster@quota47.com';	// SMTP username
//$mail->Password 	= 'gyw5XZJ6P3';          // SMTP password

$mail->Sender = 'portale@quota47.com';

$mail->From 		= $from_email;
$mail->FromName 	= $from_name;
$mail->CharSet		= "utf-8";

/*** ADD NORMAL EMAIL ADRESS TO RECIPIENTS ***/
foreach($adresses as $recipient_name => $recipient_email){
	if(is_numeric($recipient_name)){
		$mail->addAddress($recipient_email);
	}else{
		$mail->addAddress($recipient_email, $recipient_name);
	}
}


/*** ADD CC EMAIL ADRESS TO RECIPIENTS ***/
if(!empty($cc_adresses)){
	foreach($cc_adresses as $cc_recipient_name => $cc_recipient_email){
		if(is_numeric($cc_recipient_name)){
			$mail->addCC($cc_recipient_email);
		}else{
			$mail->addCC($cc_recipient_email, $cc_recipient_name);
		}
	}	
}else{
	$cc_adresses = array(); // For logging
}

/*** ADD BCC EMAIL ADRESS TO RECIPIENTS ***/
if(!empty($bcc_adresses)){
	foreach($bcc_adresses as $bcc_recipient_name => $bcc_recipient_email){
		if(is_numeric($bcc_recipient_name)){
			$mail->addBCC($bcc_recipient_email);
		}else{
			$mail->addBCC($bcc_recipient_email, $bcc_recipient_name);
		}
	}	
}else{
	$bcc_adresses = array(); // For logging
	
}

/*** ADD REPLYTO EMAIL ADRESS TO RECIPIENTS ***/
if(empty($replyto_adresses) and DEFAULT_REPLYTO_ADRESS) $replyto_adresses = array(DEFAULT_REPLYTO_ADRESS);
if(!empty($replyto_adresses)){
	foreach($replyto_adresses as $replyto_recipient_name => $replyto_recipient_email){
		if(is_numeric($replyto_recipient_name)){
			$mail->addReplyTo($replyto_recipient_email);
		}else{
			$mail->addReplyTo($replyto_recipient_email, $replyto_recipient_name);
		}
	}	
}


/*** ADD ATTACHMENTS ***/
if(!empty($attachments)){
	foreach($attachments as $attachment){
		$mail->addAttachment($attachment);
	}
}


/*** PREPARE DATA FOR LOG IN DB ***/
$log_adresses = serialize(array_merge($adresses, $cc_adresses, $bcc_adresses));
$log_script = $_SERVER['PHP_SELF'];
$log_subject = $db->make_data_safe($subject);
$log_body = $db->make_data_safe($email_text);
$log_attachments = (empty($attachments)) ? "" : serialize($attachments);
if(!isset($log_scheda)) $log_scheda = "";



/*** SEND PROCESS ***/
$mail->isHTML(true);                                  // Set email format to HTML
//$mail->XMailer = "\r";

$mail->Subject = $subject;
$mail->Body    = $email_text;
$mail->AltBody = strip_tags($email_text);

if(!$mail->send()) {
	$_SESSION['error_title'] = "Errore durante invio email"; // TODO translation
	$_SESSION['error_message'] = "Impossibile inviare email!<br>".$mail->ErrorInfo; // TODO translation
	if($log_email and DBTABLE_EMAIL_LOGS){
		$db->insert(DBTABLE_EMAIL_LOGS, array($log_adresses, $log_subject, $log_body, $log_attachments, $log_scheda, $log_script, "0", $mail->ErrorInfo), array("adresses", "subject", "body", "attachments", "scheda", "script", "sent", "error"));
	}	
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return false;
	}
}else{
	if($session_message){
		$_SESSION['success_title'] = "Email inviata correttamente"; // TODO translation
		$_SESSION['success_message']	= "L'email è stata inviata con successo"; // TODO translation
	}
	unset($_SESSION['error_title']);
	unset($_SESSION['error_message']);
	if($log_email and DBTABLE_EMAIL_LOGS){
		$db->insert(DBTABLE_EMAIL_LOGS, array($log_adresses, $log_subject, $log_body, $log_attachments, $log_scheda, $log_script), array("adresses", "subject", "body", "attachments", "scheda", "script"));
	}	
	if($redirect_after){
		header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		exit();
	}else{
		return true;
	}
}


?>