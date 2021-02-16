<?php
session_start();
include_once 'variables.php';
include_once 'functions.php';
include_once 'classes/cc_mysqli.class.php';
include_once 'classes/cc_menu.class.php';
include_once 'classes/cc_translations.class.php';
include_once 'classes/cc_phpbootstrap.class.php';
include_once 'classes/cc_errorhandler.class.php';
include_once 'classes/user_cookie.class.php';
include_once 'classes/media_upload.class.php';

if(file_exists(FILEROOT."required/projectHelper.class.php")){
	include_once "projectHelper.class.php";
	$helper = new projectHelper();
}



/*** ERROR SETTINGS ***/
error_reporting(E_ALL ^ E_NOTICE);
$_errorhandler = new cc_errorhandler();
set_error_handler(array($_errorhandler, 'regError'), E_ALL ^ E_NOTICE);
ini_set("display_errors", "1");

/*** ALWAYS MEMORIZE THE CURRENT URL LOCATION ***/
$_SESSION['location'] = $_SERVER['REQUEST_URI'];

/*** CONNECT TO DATABASE ***/
$db = new cc_dbconnect(DB_NAME);

/*** CHECK IF IP IS BLACKLISTED AND IN CASE IT IS SEND IT TO banned.php ***/
$blacklist = $db->col_value( "IP", "system_blacklist" );
$ip = $_SERVER[ 'REMOTE_ADDR' ];

// if I'm here by error resend me back to panel and consequantially to login if not logged in 
if ( in_array( $ip, $blacklist ) and !LOCALHOST  ) {
  header( 'location: ' . HTTP_PROTOCOL . HOSTROOT . SITEROOT . "banned.php" );
  exit();
}


/*** GET CONFIG VALUES. TODO : move this and next block in separte script or in variables.php ***/
$_configs = $db->key_value("param", "value", DBTABLE_CONFIG);


/*** DEFINE SOME CONSTANTS (WHERE IN variables.php) ***/
define ('MAINTENANCE', $_configs['maintenance_mode']);
if($_configs['debug'] == '0'){ define ('DEBUG', false); }else{ define ('DEBUG', true); } 
if($_configs['isdemo'] == '0'){ define ('ISDEMO', false); }else{ define ('ISDEMO', true); } 


// LET'S SEE IF WE HAVE TO SWITCH-OFF DISPLAY ERRORS
if(!DEBUG) ini_set("display_errors", "0");

// Pre-authentication language handler script
include_once 'required/access-language-handler.php';

/********************************************
 * Check if system is in maintenance mode   *
 * if that is the case set message and      *
 * redirect the user back to login page     *
 ********************************************/
 
if(MAINTENANCE == 'on' and $_SESSION['login_type'] != "SA"){ // TODO cambiare e far sì che utilizzo abbonamento di sottoscrizione
	$_SESSION['login'] = false;
	//$_SESSION['error'] = $_t->get('maintenance-mode');
	$_SESSION['error'] = "Portale in fase di manutenzione, non è possibile accedere al momento";
	$_SESSION['location'] = $_SERVER['REQUEST_URI'];
	header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'login.php');
	exit;
}

/************************************************************
 * PARSE GET VALUES                                         *
 * Takes $_GET values (like $pid, $_record, $_view etc...), *
 * santizises them and prepares javascript output           *
 ************************************************************/
include_once 'parsegets.php'; 

/*** Creating bootstrap object -- not compulsory, but can make things easier and faster ***/
$bootstrap = new phpbootstrap();

$usercookie = new user_cookie(); // need it for rememberme param value


/*** If a token is set, check if exists, then set variables as according ***/
if(!empty($_token)){
	// for precaution reset page vars
	$pid = $_record = $_action = $_view = ""; 
	
	// do not proceed until clearing
	$_token_proceed = false;
	
	// get params of the token
	$_token_param = $db->get1row(DBTABLE_TOKENS, "WHERE token = '".$_token."'");
	
	if($_token_param){ // ok got token...
	
		// ok token exists, let's see if it's still valid
		$first_acces_date = $_token_param['data'];		
		
		if($first_acces_date == '0000-00-00'){
			// if not set, get currente date
			$first_acces_date = date( "Y-m-d", time() );
			// update table
			$db->update(DBTABLE_TOKENS, array("data" => $first_acces_date ), "WHERE id = '".$_token_param['id']."'");
		} // end first access date empty 
		
		// calculate time limit (date first access + days after which it's not valid anymore
		$token_time_limit = strtotime($first_acces_date) + ($_token_param['durata'] * 60 * 60 * 24 );
		
		// if token time limit is bigger than now it's still valid, else set $token_due to false script will not proceed, user will be sent to no-access page
		if($token_time_limit > time()){
			
			$token_valid = date("d/m/Y", $token_time_limit);
			
			// set page id, action, view and record (override if others are set)
			$pid 		= $_token_param['page'];
			$_record 	= $_token_param['record'];
			$_action 	= $_token_param['action'];
			$_view 		= $_token_param['view'];
			
			// check for user
			$_token_param['user'] = (int) $_token_param['user'];
			if(empty($_token_param['user'])){				
				$_SESSION['login_id'] = 1;
				$_SESSION['login_type'] = "SA";
				
			}else{
				$_SESSION['login_id'] = (int) $_token_param['user'];
				$_SESSION['login_type'] = $db->get1value("subscription_type", LOGIN_TABLE, "WHERE id = '".$_token_param['user']."'");
				
			}
			
			// for now: set session variables to grant access - TODO: other way!
			$_SESSION['login'] = true;
			$_SESSION['login_time'] = time();
			
			$_token_proceed = true; // ok 
			
			// set warning on when this token is due - TODO: translation
			if($_token_param['view_message'] == '1'){
				$page_alerts[] = $bootstrap->alert( "PAGINA CON ACCESSO LIMITATO NEL TEMPO", "La seguente pagine sarà disponibile fino al <strong>".$token_valid."</strong><br>\nDopo tale data non sarà più accessibile.", "warning", true);
			}
			
			
		}else{
			
			// delete record from table
			$db->delete(DBTABLE_TOKENS, "WHERE id = '".$_token_param['id']."'");			
			
		} // end time limit check
		
		
	} // end if token_param
	
	// if token_proceed is false redirect to no access page
	if(!$_token_proceed){
		// redirect to no access page
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'no-access.php');
		exit();
	
	}
}
 
/**********************************
 * Check that user has logged-in  *
 * else send user to login page   *
 **********************************/
if(RESTRICTED_ACCESS){	
	
	if (@$_SESSION['login'] !== true and $_view != "pdf"){
		/*** NO ACCESS - GOTO LOGIN ***/
		
		if(REMEMBER_SESSION_IN_COOKIE){ // TODO: migliorare e usare classe cookie, anche se non serve
			if(!empty($_COOKIE['user'])){
				$cookie_values = explode(":", $_COOKIE['user']);
				$_SESSION['login'] = true;
				$_SESSION['login_id'] = $cookie_values[0];
				$_SESSION['login_type'] = $cookie_values[1];
				$_SESSION['rifid'] = $cookie_values[2];
			}
		}
		
		// remember where you were and redirect to login	
		$_SESSION['location'] = $_SERVER['REQUEST_URI'];
		header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'login.php');
		exit;
	   
	}else{
		
		/*** GOT ACCESS ***/
		
		if (REGISTER_SESSIONS and $_view != "pdf"){
			
			// Check if is account still valid
			$account_valid = $db->get1value("ts", LOGIN_TABLE, "WHERE id = '".$_SESSION['login_id']."' AND active = '1'");
			if(!$account_valid){
				$_SESSION['error'] = $_t->get('account-not-valid-anymore');
				$_SESSION['login'] = false;
			}

			
			$checktime = time();
			
			if (isset($_SESSION['login_time'])){
				
				
				// calculate time
				$timepassed = $checktime-$_SESSION['login_time'];
				$maxtime = SESSION_LENGHT*60;
				if($usercookie->rememberme() > time()) $timepassed = $maxtime - 1; // if remember me param set was set bypass timeout functionality
				if ($timepassed>$maxtime){ 
					// TIME-OUT!
					$_SESSION['login'] = false; // do not grant access
					$_SESSION['error'] = $_t->get('error-timeout'); // register error msg to display in login form
					$_SESSION['location'] = $_SERVER['REQUEST_URI']; // remember where user was
					
					// update access log DB table
					if(LOG_ACCESS and !empty($_SESSION['access_log_id'])){
						$alv['logout_datetime'] = date("Y-m-d H:i:s", time()); 
						$db->update(DBTABLE_ACCESS_LOGS, $alv, "WHERE id = '".$_SESSION['access_log_id']."'");
						unset($_SESSION['access_log_id']);
					}
					
					if(REMEMBER_SESSION_IN_COOKIE){
						setcookie("user", "", time()-3600); // TODO use class
					}
					
					// redirect
					header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'login.php');
					exit;
				
				}else{					
					
					// Stil on time, shift time-out
					$_SESSION['login_time'] = $checktime;

					// log last active
					if(LOG_ACCESS and !empty($_SESSION['access_log_id'])){
						$db->update(DBTABLE_ACCESS_LOGS, array("last_active" => date("Y-m-d H:i:s", time())), "WHERE id = '".$_SESSION['access_log_id']."'");
					}
				}
				
			}else{ 
			
				// logintime not set - do not grant access and redirect to login page
				$_SESSION['login'] = false;
				$_SESSION['error'] = $_t->get('error-timeout');
				$_SESSION['location'] = $_SERVER['REQUEST_URI'];
				header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'login.php');
				exit;
				
			}  // end if (isset($_SESSION['login_time']))
		
		}  // end if (REGISTER_SESSIONS)
		
		
		/*****************************
		*** ALL OK WE CAN PROCEED ***
		*****************************/
				
		/*** USER MANAGEMENT ***/
		include_once 'classes/cc_user.class.php';   
		$_user = new cc_user($_SESSION['login_id'], $db);

		if(!$_user) die("Utente tipo '".$_SESSION['login_type']."' non definito!"); // TODO : USE SUBSCRIPTION LOGIC and TRANSLATE
	   
		// set language of translation based on user preferences -- should be the same as cookie value, but you never know
		$_t->setLanguage($_user->getLanguage(), false); // 2nd param (false): do not reload translations

		// set local settings and timezone
		setlocale(LC_ALL, $_user->getLanguageCode()); 
		date_default_timezone_set($_user->getTimezone);

			  
		// get an array with the id's of the pages the user can access and the ones that must apprear in menu (and are writable)
		$permitted_pages = $_user->getPermittedPages(); // as an array
		$show_pages = $_user->getShowPages(); // as an array
				
		// if empty $permitted pages
		if(empty($permitted_pages)){
			$_SESSION['login'] = false;
			$_SESSION['error']	= "Il suo account non dispone di alcuna autorizzazione per accedere alle pagine!"; // TODO: translate
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'login.php');
			exit();
		}
			   
		// get the id of the page to show and stoere it in $pid
		if (empty($pid)) $pid = $_user->getDefaultPage();		

		// let's see if the user can access this page
		if(!in_array($pid, $permitted_pages)){
			$_SESSION['error_title']		= "PAGINE NON ACCESSIBILE";
			$_SESSION['error_message']	= "Non si dispone l'autorizzazione per accedere alla pagine richiesta";
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.'cpanel.php');
			exit();
		}

		// let's see at this stage if there's a subscription plan and if the subscription of the user is still valid
		if(SUBSCRIPTION_PLANS){
			$subscription_expiry_in_days = ceil ( ( $_user->getExpiryDate(false) - time() ) / (60*60*24) );
			if($subscription_expiry_in_days < 1){
				// subscription has expired, change page id to the renewal page, can't go anywhere else...
				$pid = RENEWAL_PAGE_ID;
			}
		}
		
		
		// geting page data
		if(!$page = $db->get1row(DBTABLE_PAGES, "WHERE id='".$pid."'")) die ("Could not find the requested page ".$pid."!");
		$_pagetype 		= $page['type'];
		$_pageaction 	= $page['action'];
		$_pagename 		= $page['file_name'];
		$_modpid 		= (empty($page['modify_page'])) ? '0' : $page['modify_page'];

		
		// get switches for write and delete permissions
		
		$_user->setPagePermissions($pid, $_modpid);

		$canshow 	 = $_user->canShow(); // only for table, determines if user can click on details / magnifying glass icon
		
		$canadd 	 = $_user->canAdd(); // only for table, determines if user can click on details / magnifying glass icon
		$canedit 	 = $_user->canEdit(); // only for table, determines if user can click on details / magnifying glass icon
		
		// legacy...
		//$canwrite 	 = $_user->canWrite($pid, $_pagetype, $_modpid); // switch in function when table (determined by $_pagetype)
		
		$candelete 	 = $_user->canDelete(); // for both tables and modules straight foward logic
		$cancopy	 = $_user->canCopy(); // for now alias of canwrite, but being a var it can be changed on page base
		$canactivate = $_user->canActivate(); // only takes values of this page or modify_page (for table)
		
		
		// if user has no writing permissions set $readonly var to readonly attrib
		$readonly = ($canedit) ? "" : "readonly=\"readonly\"";
		
		
		// Creating menu object
		$menu = new cc_menu($pid, $show_pages, $db, false); // second param was $permitted_pages
				
		/*** CREATING ARRAY WITH LAST VISITED PAGES  -- NOT YET USED ***/
		$navigation = (empty($_SESSION['navlog'])) ? $navigation = array() : $_SESSION['navlog'];		
		$_this_page = $_SERVER['REQUEST_URI'];
		// add current page to navigation history (at the beginning of the array) if not already there
		if($_this_page != $navigation[0]) array_unshift($navigation, $_this_page); 
		// if history is longer than 5 pages forget the oldest page
		if(count($navigation) > 10) array_pop($navigation); 
		// make navigation array available throughout sessions
		$_SESSION['navlog'] = $navigation; 
		// free memory
		unset($navigation); 
		/*** END CREATING ARRAY WITH LAST VISITED PAGES ***/
		
		/*** RECORDSET ***/
		if(RECORDSET){
			$rs_names = $db->key_value("id", "recordset", "recordset");
			$rs_icons = $db->key_value("id", "icon", "recordset");
		}
		
		/*** Stylesheets and javascript assets queue TODO: inserire tutti quelli standard qua dentro ***/
		$css_assets = $js_assets = array();
        $inline_js = ""; // will be outputted inside scritp tags at the end of cpanel
			   
	}  // end if (@$_SESSION['login'] !== true)
	
}else{
	/*** THE SITE IS FREELY ACCESSIBLE ***/
	
	include_once 'classes/cc_nouser.class.php';

	// (no)user object -- returns mainly dummy data
	$_user = new cc_user($db);
	
	// handling gets
	if (empty($pid)){
		$pid = 1;
	}
	
	// geting page data
	if(!$page = $db->get1row(DBTABLE_PAGES, "WHERE id='".$pid."'")) die ("Could not find the requested page ".$pid."!");
	$_pagename = $page['file_name'];
	
	// getting menu data
	$menu = new cc_menu($pid, $permitted_pages, $db, false);

}


/*********************** 
*  END ACCESS CONTROL  *
************************/


/***************************** 
*    LOADING FUNDAMENTALS    *
******************************/
define("_CCCMS", true); // used to prevent direct access to pages

$_version = versioning();

// DEFINING INCLUDE FILES
$php_file = ($_view == "pdf") ? $_pagename."_pdf.php" : $_pagename.".php";
$css_file = ($_view == "pdf") ? $_pagename."_pdf.css" : $_pagename.".css";
$js_file = ($_view == "pdf") ? $_pagename."_pdf.js" : $_pagename.".js";
$js_file_versioned = ($_view == "pdf") ? $_pagename."_pdf?vers=".$_version.".js" : $_pagename."?vers=".$_version.".js";

// CREATE A PAGE HASH - WILL BE RECREATED EVERTYTIME THE PAGE IS LOADED
$_pagehash = sha1($_pagename . time());
// add pagehash to js variables
$_js_gets .= "paghash = \"".$_pagehash."\";\n"; 


// get translation of this section 
$_t->setSection($_pagename);

// get cpanel default translations (title, and subtitle) of the page
$_cpanel_title 		= ( !$_t->get('title') ) ? $page['title'] : $_t->get('title');
$_cpanel_subtitle 	= ( !$_t->get('subtitle') ) ? $page['subtitle'] : $_t->get('subtitle');


//load standard assets
$css_assets[] = "plugins/datepicker/datepicker3.css";
$js_assets[] = "plugins/datepicker/bootstrap-datepicker.js";
$js_assets[] = "plugins/datepicker/locales/bootstrap-datepicker.it.js";


/*** CALL DEPENDENCIES BASED ON PAGE TYPE ***/
//'custom','table','module','grid','label','wizard','survey'
switch($_pagetype){
	case "inline-table":
	case "table":
		include_once 'classes/table_engine.class.php';
		$_table_class_file = FILEROOT."models/tables/".$_pagename.".class.php"; // extends table_engine.class
		// if the custum table engine extender file exists include it and initiate
		if(file_exists($_table_class_file)){
			include_once $_table_class_file;
			$_classname = str_replace("-", "_", $_pagename)."_table";
			$_table = new $_classname($pid, $_pagename, $_modpid, $db, $_t);
		}else{
			$_table = new table_engine($pid, $_pagename, $_modpid, $db, $_t);
		}
		// get table translation from lang file (plugins/datatables/languages/xx_XX.txt)
		$_table->setLang($lang_code);
		break;
	case "module":
		// get pid of relative table (if any) so we can turn back. If none or too many are found set to 0, javascript will output error
		if(empty($gb2)) $gb2 = $db->get1value("modify_page", DBTABLE_PAGES, "WHERE id = '".$pid."'");
		$gobackto = (empty($gb2)) ? $_t->get("cantgoback") : $gb2;
		$_js_gets .= "gb = \"".$gobackto."\";\n"; // send to javascript
		break;
	case "grid":
		include_once 'classes/grid.class.php';
		$_grid = new grid();
		$css_assets[] = "css/grid.css";
		$js_assets[] = "js/grid.js";
		break;
	case "label":
		break;
	case "wizard":
		break;
	case "survey":
		$css_assets[] = "css/survey.css";
		$js_assets[] = "js/survey.js";
		break;
	default:
		break;
}

// for copied element only
if(!empty($_SESSION['copied'])){
	$copied_label = $_SESSION['copied'];
	unset($_SESSION['copied']);
}

/*** Load helper file -- onready and other usefull but non related functions ***/
if(file_exists(FILEROOT."helpers/".$php_file)){
	include_once 'helpers/'.$php_file;
	// let's see if the onready function exists
	$onready_function = "on_ready";
	if($_action == "upload") $onready_function .= "_upload";
	if( function_exists ( $onready_function ) ){
		call_user_func_array($onready_function, array());
	}
}


/*** LOAD MODEL & VIEW -- IF PAGEACTION != UPLOAD ***/
if($_action == "upload"){
	include_once 'upload.php';
}else{
	$_output = false; 
	$_data = array();
	$_jsscripts = array(); // variable in which to memorize extra javascript files defined in model;
	// Load Model...
	if(file_exists(FILEROOT."models/".$php_file)){
		include_once 'models/'.$php_file;
	}else{
		// Model not found - set error message in to display in cpanel
		$_errorhandler->setError("MODEL FILE <strong>".$php_file."</strong> NOT FOUND!", "danger");
	}
	
	// integrate data returned from savevalues
	if(!empty($_SESSION['savevalues'])){
		foreach($_SESSION['savevalues'] as $_svk => $_svv){
			if( stripos($_svk, "|") ){
				$_svkexp = explode("|", $_svk);
				$_svk = $_svkexp[1];
			}
			if(empty($_data[$_svk])) $_data[$_svk] = $_svv; 
		}
		unset($_SESSION['savevalues']);
	}
	
	// if write2db / switchfile ha sencountered some problems on some fields these will be stored in $__wrongfields array
	// which is used in phpbootstrap.class to add a error class to those fields
	$_wrongfields = array();
	if(!empty($_SESSION['wrongfields'])){
		foreach($_SESSION['wrongfields'] as $_wf){
			$_wrongfields[] = $_wf;
		}
		unset($_SESSION['wrongfields']);
	}
	
	
	// if action = update and record is set but no data is found set canwrite to false (no save buttons) and insert pagealert with warning
	if( !empty($_record) and empty($_data) and $_action == 'update'){
		$page_alerts[] = $bootstrap->alert($_t->get('record-not-found'), $_t->get('record-not-found-msg'), "danger", false);
		$canedit = false;
	}
	
	// Load View... expecting the $_data array()
	if(file_exists(FILEROOT."views/".$php_file)){
		include_once 'views/'.$php_file;
	}else{
		// view not found - set error message in to display in cpanel
		$_errorhandler->setError("VIEW FILE <strong>".$php_file."</strong> NOT FOUND!", "danger");
	}
}

// notify if $_output is empty
if(!$_output) $_errorhandler->setError("ERROR: _output variable is empty! Make sure you declare it in the view!", "danger");

// let's see if there some extra javascript files to be loaded TODO
if(!empty($_jsscripts)){
	foreach($_jsscripts as $_jsscript){
	}
}


/*** ALERTS ***/

// 1. Subscription renewal due
// TODO translation 
if(SUBSCRIPTION_PLANS){
	if($subscription_expiry_in_days < 0) {
		$page_alerts[] = $bootstrap->alert("SUBSCRIPTION EXPIRED!", "Your subscription has expired on <strong>".$_user->getExpiryDate(true)."</strong>. Please renew your subscription to continue working with our platform.", "danger", true);
	}else if($subscription_expiry_in_days < DAYS_SUBSCRIPTION_ALERT) {
		$page_alerts[] = $bootstrap->alert("SUBSCRIPTION RENEWAL", "Your subscription expires in <strong>".$subscription_expiry_in_days." days</strong>, please renew in time to continue working. Go to profile or click <a href='#'>here</a> to renew.", "warning", true);
	}
}
// 2. Errors
if(!empty($_SESSION['error_title']) or !empty($_SESSION['error_message'])){
	if(is_array($_SESSION['error_title'])){
		$_et = $_SESSION['error_title'];
	}else{
		$_et = array($_SESSION['error_title']);
	}
	if(is_array($_SESSION['error_message'])){
		$_em = $_SESSION['error_message'];
	}else{
		$_em = array($_SESSION['error_message']);
	}
	// Create an alert box for every error occurred
	foreach($_em as $_ei => $_error_message){
		$_error_title = $_et[$_ei];
		$page_alerts[] = $bootstrap->alert( $_error_title, $_error_message, "danger", true);
	}
	unset($_SESSION['error_message']);
	unset($_SESSION['error_title']);
}


// 3. Profile Errors / not checked etc
// todo translate
if(!$_user->isChecked()) $page_alerts[] = $bootstrap->alert("CHECK PROFILE", "You haven't confirmed you email yet, please check your email inbox our email and click on the link you find inthere to confirm your email.", "warning", false);

// 4. Page level messages (ex. "Page incomplete!" or "Remember to always select a customer") - dismissable
if(!empty($_SESSION['page_message_title']) or !empty($_SESSION['page_message'])){
	$page_alerts[] = $bootstrap->alert( $_SESSION['page_message_title'], $_SESSION['page_message'], "warning", true);
	unset($_SESSION['page_message_title']);
	unset($_SESSION['page_message']);
}

// 5. Generic messages
if(!empty($_SESSION['success_title']) or !empty($_SESSION['success_message'])){
	$page_alerts[] = $bootstrap->alert( $_SESSION['success_title'], $_SESSION['success_message'], "success", true);
	unset($_SESSION['success_title']);
	unset($_SESSION['success_message']);
}

// 6. Instruction messages
if(!empty($_instructions)){
	$page_alerts[] = $bootstrap->alert( 'Istruzioni', $_instructions, "white", true);

}

// SET DEVICE LOGIC - TODO IMPOSTAZONI IN CONFIG OPURE PAGE-BASED, P.E. view=fullscreen-tablet vulle dire che 
$_device = getDevice(); // in functions-php
switch($_device){
	case "android-tablet":
	case "ipad":
		//$_mobile_full_screen = ($_view == "fullscreen-tablet") ? true : false; // devo pero aggiungere fullscreen-tablet ai valori enum permessi in tabella pages
		$_mobile_full_screen = true;
		break;
	default:
		$_mobile_full_screen = false;
		break;
}

/*** NOTIFICATION FILES ***/
$all_notification_files = new FilesystemIterator(FILEROOT."calls/notifications", FilesystemIterator::SKIP_DOTS);
$num_notification_files = iterator_count($all_notification_files);


function versioning(){
	global $db;
	$addversion = false;
	// current version of framework
	$version = $db->get1value("value", "config", "WHERE param = 'version'");
	
	// get date of js and css file in version table
	$oldversions = $db->key_value("file", "datetime", "versioning");
	if(empty($oldversions)) $oldversions = array();
	
	// scan javascripts
	$jsversions[] 			= scandir(FILEROOT."js");
	$jsversions['pages'] 	= scandir(FILEROOT."js/pages");
		
	if($jsversions){
		foreach($jsversions as $p=>$filenames){
			foreach($filenames as $filename){
				if($filename == "." or $filename == ".." or $filename == "index.html" or is_dir($filename)) continue;
				
				$subpath = (empty($p)) ? "" : $p."/";
				$filepath = FILEROOT."js/".$subpath.$filename;
				$filedatetime = filemtime($filepath); // epoch
				$filesize = filesize($filepath); // in bytes
				
				$oldfiledatetime = $oldversions[$filename];
				
				if(empty($oldfiledatetime)){
					// save to db
					$db->insert("versioning", array($filename, $filedatetime, $filesize), array("file", "datetime", "filesize"));
					$addversion = false;
				}else{
					if($oldfiledatetime !=  $filedatetime) $addversion =  true;
					$db->update("versioning", array("datetime" => $filedatetime, "filesize" => $filesize), "WHERE file = '".$filename."'");
				} // end if empty
			} // end foreach
		} // end foreach
	} //end if $jsversions
	
	if($addversion){
		$v = explode(".", $version);
		$v[1]++;
		$version = $v[0].".".$v[1];
		$db->update("config", array("value" => $version), "WHERE param = 'version'");
	}
	
	return $version;

} // end function

?>
