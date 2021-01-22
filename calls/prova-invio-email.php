<?php

include_once '_head.php';

$redirect_after = false;
$log_email = true;
$session_message = true;
$from_email = "portale@quota47.com";
$from_name 	= NO_REPLY_NAME;


$date = date("H:i:s");
$adresses = array("Antonio" => "antealdi@libero.it", "Antonio T." => "antealdi@yahoo.it");
$subject = "Invio email di prova delle ".$date;
$email_text = "Ciao come va tutto bene? Alla grande!";


$result = include '../required/send-email.php';
echo "uso ".$mail->Mailer."<br>\n";



/*** RETURN MESSAGES ***/
	
if(!$result){
	
	echo "ERROR:<br>\n";
	echo $_SESSION['error_title'];
	echo "<br>\n";
	echo $_SESSION['error_message'];
	
}else{

	echo "Inviato email con oggetto ".$subject."<br>\n";
	echo $_SESSION['success_title'];
	echo "<br>\n";
	echo $_SESSION['success_message'];	
	
}

?>