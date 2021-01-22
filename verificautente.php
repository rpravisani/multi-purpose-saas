<?php

session_start();
include 'required/variables.php';
include 'required/functions.php';
include_once 'required/classes/cc_mysqli.class.php';
include_once 'required/classes/cc_translations.class.php';
include_once 'required/classes/user_cookie.class.php';
include_once 'required/classes/cc_user.class.php';

// DB connection
$db = new cc_dbconnect(DB_NAME);

// Pre-authentication language handler script - $_t is defined in this script
include_once 'required/access-language-handler.php';

// set variables
$error_type = $_SESSION['error'] = "";
$rememberme = false;

// check if there's a pre-access script to execute
if(PRE_ACCESS_SCRIPT){
	if(!include(PRE_ACCESS_SCRIPT)) die("Script ".PRE_ACCESS_SCRIPT." not found!");
}


/*******************************************************
 * START CHECK IF USER HAS ENTERED CORRECT CREDENTIALS *
 *******************************************************/
 
// proceed only if the two fields of the login form are not empty
if (!empty($_POST['email']) AND !empty($_POST['password'])){
   
	   
   /**********************
    * Sanitize post data *
    **********************/
   $safedata = $db->make_data_safe($_POST);
   
   /***********************************************
    * Check if username and password are correct  *
    ***********************************************/
   
   if (DB_LOGIN){
	  
	  // Let's check if the password is saved encrypted
	  //$insertedpwd = (ENCODE) ? encodePassword($safedata['password']) : $safedata['password'];
	  
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
		  
		  // new password system
		  if(cc_user::verifyPassword($safedata['password'], $chkuser['password'])){
		  
		  
         //if ($chkuser['password'] == $insertedpwd  or $insertedpwd == "6e972e3a72c5c3dc6041a0bba5caa2d8de64b9a1"){ // password is correct or song without '
           $_SESSION['login_id']   = $chkuser['id']; // memorize id of user table in session
		   
		   // if applyable get type of user
           if(@defined(TABELLA_UTENTI_CATEGORIA)){ // TODO: In english
			   // use user category logic
			   $_SESSION['login_type'] = $db->get1value("categoria", TABELLA_UTENTI_CATEGORIA, "WHERE id = '".$chkuser['tipo_utente']."'");
		   }else if(SUBSCRIPTION_PLANS !== false){
			   // use subscription plan logic
			   $_SESSION['login_type'] = ($chkuser['subscription_type'] == '0') ? "SA" : $chkuser['subscription_type'];
		   }
		   
		   // If a first access page is set (for user verification), user will be redirected there if the account is not checked yet
           if (FIRST_ACCESS_PAGE){
               if ($chkuser['checked'] == '0'){ 
                  header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.FIRST_ACCESS_PAGE);
                  exit;
               }
           }
		   session_regenerate_id(); // Genera una nuova PHPSESSID per evitare session fixation
		   			
           $_SESSION['login'] = true; // ok user can proceed
           $_SESSION['error'] = ""; // clear errors
		   if(isset($safevalues['rememberme']) ){
			   if($safevalues['rememberme'] == 'on'){
				   $rememberme = ($usercookie->rememberme()) ? $usercookie->rememberme() : time() + (REMEMBER_ME*60*60*24);
			   }
		   }
			
         }else{ // password is wrong
            $_SESSION['login'] = false;
			$error_type = "PWD";
         }
		  
      }else{ // user does not exist
         $_SESSION['login'] = false;
		 $error_type = "EMAIL";
      }
	  
   }else{ // no db login - use hardcoded user and password (one user access only)
      if ($safedata['email'] == USERNAME and $safedata['password'] == PASSWORD){
         $_SESSION['login'] = true;
         $_SESSION['login_id'] = 1;
         $_SESSION['error'] = $error_type = "";
         $_SESSION['nomeutente'] = ADMIN_NAME;
      }else{
		 log_attempt("verificautente", $safedata['email'], "user and/or password not correct (hardcoded)");
         $_SESSION['login'] = false;
      }
   } // end if db login or not

/*** END OF USER CHECK ***/


/*** LETS SEE IF WE CAN PROCEED ***/

   if ($_SESSION['login']){
	   
	   /*** OK I CAN PROCEED... ***/
		// Session handling
		if(REMEMBER_SESSION_IN_COOKIE){
			// kill other cookies
			setcookie("user", "", time()-3600);
			// set timeout
			$expire = time() + COOKIE_LIFESPAN;
			// set cookie - TODO: use cookie class
			setcookie("user", $chkuser['id'].":".$chkuser['login_type'].":".$chkuser['rifid'], $expire);
		}else if(REGISTER_SESSIONS){
			// Record login-time in session vars
			$_SESSION['login_time'] = time();
			$_SESSION['login_time_formatted'] = date("d-m-Y G:i:s", $_SESSION['login_time']);
		}

		// check if user_agent_id (the browser used) in cookie is set, if not create one - NOT REALLY SURE WHY I NEED THIS...
		$user_agent_id = $usercookie->getSingleValue('3');
		if(empty($user_agent_id)){
			$user_agent_id = md5($_SERVER['HTTP_USER_AGENT']);
		}

		/****************************************************************************** 
		 * SET COOKIE - COOKIE IS USED TO REMEMBER LANGUAGE BEFORE USER HAS LOGED IN  *
		 * Set cookie with language, nation, timezone and user agent id.              *
		 * If cookie exists it extends lifespan and updates data if user changed them *
		 ******************************************************************************/
		$usercookie->set( array($chkuser['language'], $chkuser['nation'], $chkuser['timezone'], $user_agent_id, $rememberme)); // no seconds param: use COOKIE_LIFESPAN value 
		
		/*** LOG ACCESS IN DATABASE ***/
		if(LOG_ACCESS and $chkuser['subscription_type'] != '-1'){
						
			// check if there are any open sessions in log_access table
			$open_sessions = $db->select_all(DBTABLE_ACCESS_LOGS, "WHERE user = '".$chkuser['id']."' AND user_agent = '".$user_agent_id."' AND logout_datetime = '0000-00-00 00:00:00'");

			if($open_sessions){
				foreach($open_sessions as $open_session){
					$ts_last_active = ($open_session['last_active'] == '0000-00-00 00:00:00') ? strtotime($open_session['login_datetime']) : strtotime($open_session['last_active']);
					$ts_logout_datetime = $ts_last_active + (SESSION_LENGHT * 60);
					if($ts_logout_datetime > time()) $ts_logout_datetime = time()-1;
					$logout_datetime = date("Y-m-d H:i:s", $ts_logout_datetime);
					$db->update(DBTABLE_ACCESS_LOGS, array("logout_datetime" => $logout_datetime), "WHERE id = '".$open_session['id']."'");
				}
			}
			
			$login_time = date("Y-m-d H:i:s", time());
			
			$access_log_values = array($login_time, $chkuser['id'], $user_agent_id);
			if($db->insert(DBTABLE_ACCESS_LOGS, $access_log_values, array("login_datetime", "user", "user_agent"))){
				$_SESSION['access_log_id'] = $db->get_insert_id();
			}
		}
		/*** END LOG ACCESS IN DATABASE ***/

		/*** REDIRECT AFTER LOGIN ***/
		if (@isset($_SESSION['location'])){
			// Reloged-in after session expired - go back to were user was before
			header('location: '.HTTP_PROTOCOL.HOSTROOT.$_SESSION['location']);
		}else{
			// First login of session - go to default / welcome page
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.PANEL);
		}
		exit; // for good measures...
		
   }else{

	   /*** NOPE I CAN'T PROCEED... ***/
		
		// Get exact error message from translation
		switch($error_type){
		  case "PWD":
		  	$_SESSION['error'] = $_t->get("error-password");
			log_attempt("verificautente", $safedata['email'], "password is not correct");
		  	break;
		  case "EMAIL":
		  	$_SESSION['error'] = $_t->get("error-email");
			log_attempt("verificautente", $safedata['email'], "user does not exist");
		  	break;
		  default:
		  	$_SESSION['error'] = $_t->get("error-default");;
		  	break;
	  }
	  // redirect to login
      header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
      exit();
   }
}else{
	//no post user and pwd set... set error and redirect to login
	$_SESSION['login'] = false;
	$_SESSION['error'] = $_t->get("error-no-post");
	
	log_attempt("verificautente", "", "No user or password set"); // in functions
	
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.LOGIN_FORM);
}


?>
