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
	   
	   // bypass per anellissimo
	   $user_field = "username";

	  if(MULTI_LOGIN){
	      $chkuser = cc_select_all(LOGIN_TABLE, "WHERE ".$user_field." ='".$safedata['email']."' AND ".MULTI_LOGIN_FIELD." = '".$safedata[MULTI_LOGIN_FIELD]."'");
		  $_SESSION['multi_login_id'] = $_POST[MULTI_LOGIN_FIELD];
	  }else{
		  $chkuser = $db->get1row(LOGIN_TABLE, "WHERE ".$user_field." = '".$safedata['email']."'");
	  }	       
		  
	  // THE USER EXISTS - CHECK IF TOKEN IS CORRECT

	 if(!empty($chkuser) and $safedata['token'] == $chkuser['reset_token']){

		 // TOKEN EXISTS - CHECK IF IT'S STILL VALID

		 if($chkuser['reset_limit'] > time()){
		 	

			 // EVERYTHING OK, PROCEED, GENERATE NEW TOKEN, CREATE URL AND REDIRECT USER TO LOSTPASSWORD PAGE 
			 //$newtoken = createToken();
			 //$datelimit = time()+(60*60*FORGOT_PASSWORD_HOUR_LIMIT); // 48 hours for now
			 //$db->update(LOGIN_TABLE, array("reset_token" => $newtoken, "reset_limit" => $datelimit), "WHERE id = '".$chkuser['id']."'");
			 $reset_url = HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=reset&fa=1&i=".$chkuser['id']."&t=".$safedata['token'];
			 header('location: '.$reset_url);
			 exit();

		 }else{
		 	
			 // TOKEN EXPIRED - REMOVE ACCOUNT AND SEND EMAIL TO ADMIN WHO HAS TO REACTIVATE ACCOUNT 

			 // SOLO PER GESTIONE CATALOGO - TOLGO LA SPUNTA ACCESSO
			 $customer_id = $db->get1value("id", DBTABLE_CLIENTI, "WHERE accesso = '".$chkuser['id']."'");
			 $db->update(DBTABLE_CLIENTI, array("accesso" => '0'), "WHERE id = '".$customer_id."'");

			 // Remove user account
			 $db->delete(LOGIN_TABLE, "WHERE id = '".$chkuser['id']."'");
			 
			 // url shortcut to the customers module
			 $shotcut_url = HTTP_PROTOCOL.HOSTROOT.SITEROOT."cpanel.php?pid=25&v=html&a=update&r=".$customer_id;

			// send email _ TODO: translation
			$subject = "Collegamento primo accesso per cliente ".$chkuser['rag_soc']." scaduto.";

			$email_text = "Buongiorno,<br>\n<br>\n";
			$email_text .= "Il cliente <strong>".$chkuser['surname']."</strong> ha tentato di effettuare il primo accesso, ma il collegamento per accedere non è più valido.<br>\n";
			$email_text .= "Cliccate sul seguente link ed inserite le vostre credenziali per accedere alla scheda di questo cliente.<br>\n<br>\n";
			$email_text .= $shotcut_url."<br>\n<br>\n";
			$email_text .= "Buona giornata<br>\nLo Staff di ".NOME_DITTA.".\n";

			$adresses = array( EMAIL_ADMIN ); 

			// do not redirect after email has been sent
			$redirect_after = false;

			// call send email script
			include 'required/send-email.php';

			unset($_SESSION['success_title']);
			unset($_SESSION['success_message']);
			unset($_SESSION['error_title']);
			unset($_SESSION['error_message']);
			 
			$_SESSION['error'] = "Collegamento non più valido!<br>Il link fornito per collegarvi non è più valido, contattate <strong>".NOME_DITTA."</strong> per richiederne uno nuovo.";

			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=first_access");

			exit;				 

		 } // end if exprity date

	 }else{
		 	
		 
		 // TOKEN OR USERNAME NOT CORRECT - CREATE NEW TOKEN AND SEND IT TO THE USER (OF TOKEN) BY EMAIL 

		 $token_user = $db->get1row(LOGIN_TABLE, "WHERE reset_token = '".$safedata['token']."'");

		 if($token_user){
			 

			// TOKEN IS CORRECT / USERNAME IS WRONG - WRITE NEW TOKEN TO DB, SEND EMAIL TO USER AND REDIRECT TO LOGIN STANDARD
			$newtoken = createToken();
			$newurl = HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=first_access&t=".$newtoken;
			 
			 $db->update(LOGIN_TABLE, array("reset_token" => $newtoken), "WHERE id = '". $token_user['id']."'");

			// send email _ TODO: translation
			$subject = "Nuovo link per primo accesso su ".NOME_DITTA;

			$email_text = "Buongiorno,<br>\n<br>\n";
			$email_text .= "Ricevi questa email perché un tentativo di primo accesso al portale ordini <strong>".NOME_DITTA."</strong> non è andato a buon fine.<br>\n";
			$email_text .= "Se non hai fatto alcun tentativo sei pregato di inoltrare questa communicazione all'indirizzo <a href='mailto:".SA_EMAIL."'>".SA_EMAIL."</a>.<br>\n"; 
			$email_text .= "Se invece hai tentato a collegarvi al portale ti ricordiamo che la username è la tua partita iva (solo numeri, senza simboli, lettere o spazi). Per effettaure un nuovo tentativo clicca sul seguente link; il vecchio link non è più valido.<br>\n<br>\n";
			$email_text .= $newurl."<br>\n<br>\n";
			$email_text .= "Buona giornata<br>\nLo Staff di ".NOME_DITTA.".\n";
			 
			//$email_user_token = $db->get1value("email", DBTABLE_CLIENTI, "WHERE accesso = '".$token_user['id']."'");
			 $email_user_token = $token_user['email'];

			$adresses = array( $email_user_token );

			// do not redirect after email has been sent
			$redirect_after = false;

			// call send email script
			include 'required/send-email.php';

			unset($_SESSION['success_title']);
			unset($_SESSION['success_message']);
			unset($_SESSION['error_title']);
			unset($_SESSION['error_message']);
			$_SESSION['error'] = "Credenziali non corrette!<br>Vi è stata inviata un'email contenente il nuovo link per effettuare il primo accesso.";

			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=first_access");

			exit;

		 }else{
			 			 
			 // THE TOKEN SENT IS NOT CORRECT - DON'T DO ANYTHING, JUST REDIRECT USER TO LOGIN STANDARD
			$_SESSION['error'] = "Username o token di accesso non corretto<br>Se avete fatto copia è incolla di un link controllate di aver copiato la stringa per intero.<br>Oppure contattate l'amministratore del sistema.";
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=first_access");
			exit;

		 } // end if token_user

	 } // end if sent token is correct
		  
   }else{
	   
	   // should never happen: no DB_login, let's die if we do make it here...
	   die("No DB Login!!!");
	   
   } // end if DB_LOGIN
	
}else{
	
	// empty post...
	$_SESSION['error'] = "Inserite la vostra partita iva";
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM."?action=first_access&t=".$_POST['token']);
	exit;
	
} // end if !empty post
		  
  
		  

?>
