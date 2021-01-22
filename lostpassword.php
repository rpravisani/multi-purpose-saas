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
 
// proceed only if the email field is not empty
if ( !empty($_POST['email']) ){   
	   
   /**********************
    * Sanitize post data *
    **********************/
   $safedata = $db->make_data_safe($_POST);
   
   /*********************************
    * Check if username is correct  *
    *********************************/
   
   if (DB_LOGIN){
	  	  
	  // check if user entered an email or a username
	  if(stripos($safedata['email'], "@")){
		  $user_field = "email";
	  }else{
		  $user_field = "username";
	  }

	  if(MULTI_LOGIN){
	      $chkuser = cc_select_all(LOGIN_TABLE, "WHERE ".$user_field." ='".$safedata['email']."' AND ".MULTI_LOGIN_FIELD." = '".$safedata[MULTI_LOGIN_FIELD]."'");
		  $_SESSION['multi_login_id'] = $_POST[MULTI_LOGIN_FIELD];
	  }else{
		  $chkuser = $db->get1row(LOGIN_TABLE, "WHERE ".$user_field." = '".$safedata['email']."' AND active = '1'");
	  }
	  
      if ($chkuser){ // The user exists
		  
		  // if no email is set give error
		  if(empty($chkuser['email'])){
			  $_SESSION['error'] = $_t->get("no_email");
			  
			  log_attempt("lostpassword", $safedata['email'], "no email is set for this user");
			  
			  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=lost");
			  exit;
		  }
		  
		  // create token and date limit for password reset
		  $token = bin2hex(openssl_random_pseudo_bytes(ENCODE_BYTES));
		  $datelimit = time()+(60*60*FORGOT_PASSWORD_HOUR_LIMIT); // 48 hours for now
		  
		  // update user table entre token and limit
		  $db->update(LOGIN_TABLE, array("reset_token" => $token, "reset_limit" => $datelimit), "WHERE id = '".$chkuser['id']."'");
		  
		  $reset_url = HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=reset&i=".$chkuser['id']."&t=".$token;
		  
		  // send email _ TODO: translation
		  $subject = "Richiesta di recupero password accesso per Portale Ordini ".NOME_DITTA;
		  
		  $email_text = "Buongiorno,<br>\n<br>\n";
		  $email_text .= "E' stata effettuate una richiesta di reset password per il tuo account su Portale Ordini <strong>".NOME_DITTA."</strong>.<br>\n";
		  $email_text .= "Se non hai fatto alcuna richiesta o nel frattempo hai recuperato la password, ignora questa email. In caso contrario clicca il seguente link per inserire un nuova password<sup>*</sup>:<br>\n<br>\n";
		  $email_text .= $reset_url."<br>\n<br>\n";
		  $email_text .= "<small><em><sup>*</sup> Per motivi di sicurezza la tua vecchia password è memorizzata criptata e non recuperabile, va dunque sostituita nel caso in cui non la si ricorda.</small><br>\n<br>\n";
		  $email_text .= "Buona giornata<br>\nLo Staff di ".NOME_DITTA.".\n";
		  
		  $adresses = array( $chkuser['email'] );
		  
		  // do not redirect after email has been sent
		  $redirect_after = false;
		  		  
		  // call send email script
		  include 'required/send-email.php';
		  
		  if(empty($_SESSION['error_title'])){
			  unset($_SESSION['success_title']);
			  $_SESSION['success_message']	= "Richiesta reset password inviata correttamente<br>Le abbiamo inviato un email le istruzioni su come cambiare la sua la sua password. Controlli la sua casella di posta elettronica."; // TODO translation
		  }else{
			  $_SESSION['error'] = $_SESSION['error_message'];
			  unset($_SESSION['error_title']);
			  unset($_SESSION['error_message']);
		  }
		  
		  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
		  
		  exit;
		  
	  }else{
		  
		  // user does not exist
		  $_SESSION['error'] = $_t->get("no_user");
		  
		  log_attempt("lostpassword", $safedata['email'], "user dows not exist");
		  
		  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=lost");
		  exit;
	  }
   
   }else{
	   // should never happen: no DB_login, let's die if we do make it here...
	   die("No DB Login!!!");
   }
   
}else{
	// empty post...
	$_SESSION['error'] = $_t->get("user_empty");
	log_attempt("lostpassword"); // in functions
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=lost");
	exit;
}
		  

?>
