<?php
session_start();
include 'required/variables.php';
include 'required/functions.php';
include_once 'required/classes/cc_mysqli.class.php';
include_once 'required/classes/cc_translations.class.php';
include_once 'required/classes/user_cookie.class.php';

// DB connection
$db = new cc_dbconnect(DB_NAME);

// Pre-authentication language handler script - $_t is defined in this script
include_once 'required/access-language-handler.php';

// set variables
$error_type = $_SESSION['error'] = "";


/*******************************************************
 * START CHECK IF USER HAS ENTERED CORRECT CREDENTIALS *
 *******************************************************/
 
// proceed only if the password field is not empty
if ( !empty($_POST['password']) and !empty($_POST['token']) ){
   
	   
   /**********************
    * Sanitize post data *
    **********************/
   $safedata = $db->make_data_safe($_POST);
   
   /***********************************************
    * Check if username and password are correct  *
    ***********************************************/
   
   if (DB_LOGIN){
	   
	   // check we passed a valid token and get id of user
	   $chkuser = $db->get1row(LOGIN_TABLE, "WHERE reset_token = '".$safedata['token']."'");
	   
	   if(!$chkuser){
		  $_SESSION['error'] = $_t->get("no_email");
		  
		  log_attempt("resetpassword", $safedata['token'], "token not found");
		  
		  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
		  exit;
	   }
	   
	   $id = (int) $chkuser['id'];
	   
	   // encode password
	   $encoded_password = encodePassword($safedata['password']);
	   
	   // set update array
	   $values = array( "password" => $encoded_password, "reset_token" => "", "reset_limit" => "");
	   
	   // if this is password attribution from first access set expiry date, checked and active
	   if($safedata['fa']){
		   $values['last_renew'] = date('Y-m-d'); // actually first login date
		   $values['expiry_date'] = date('Y-m-d', time()+60*60*24*365*10); // ten years in the future or something
		   $values['checked'] = '1'; // it's checked
		   $values['active'] = '1'; // it's active
	   }
	   
	   
	   // update db
	   $db->update(LOGIN_TABLE, $values, "WHERE id = '".$id."'");	   
	   
	  // send email _ TODO: translation
	  $subject = ($safedata['fa']) ? "Password impostata con successo | ".NOME_DITTA : "Password reimpostata con successo | ".NOME_DITTA;
	  
	  $email_text = "Buongiorno ".$chkuser['surname'].",<br>\n<br>\n";
	  $email_text .= "La password per il tuo account su Portale Ordini <strong>".NOME_DITTA."</strong> è stata correttamente ";
	  $email_text .= ($safedata['fa']) ? "attribuita" : "cambiata";
	  $email_text .= "<br>\n";
	  $email_text .= "Puoi effettuare il login con le tue nuove credenziali all'url: ".HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM.".<br>\n<br>\n";
	  $email_text .= "Buona giornata<br>\nLo Staff di ".NOME_DITTA.".\n";
	  
	  $adresses = array( $chkuser['email'] );
	  
	  // do not redirect after email has been sent
	  $redirect_after = false;
	  
	  // call send email script
	  include 'required/send-email.php';
		  
	  if(empty($_SESSION['error_title'])){
		  unset($_SESSION['success_title']);
		  $_SESSION['success_message']	= ($safedata['fa']) ? "Password impostata<br>Entra con le tue nuove credenziali." :  "Password reimpostata<br>Entra con le tue nuove credenziali."; // TODO translation
	  }else{
		  $_SESSION['error'] = $_SESSION['error_message'];
		  unset($_SESSION['error_title']);
		  unset($_SESSION['error_message']);
	  }
	  
	  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
	  
	  exit;
		     
   }else{
	   
	   // should never happen: no DB_login, let's die if we do make it here...
	   die("No DB Login!!!");
   }
   
}else{
	
	// empty post...
	$_SESSION['error'] = $_t->get("user_empty");
	
	log_attempt("resetpassword"); // in functions
	
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
	exit;
}
		  

?>
