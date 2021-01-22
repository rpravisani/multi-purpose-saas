<?php
/**********************************************
 * INCLUDES AND STANDARD SETUP FOR CALL FILES *
 **********************************************/
session_start();
include_once '../required/variables.php';
include_once '../required/functions.php';
include_once '../required/classes/cc_mysqli.class.php';
include_once '../required/classes/cc_translations.class.php';
include_once '../required/classes/user_cookie.class.php';
include_once '../required/classes/cc_user.class.php';
include_once '../required/classes/cc_errorhandler.class.php';
include_once '../required/classes/cc_phpbootstrap.class.php';

// db connection
$db = new cc_dbconnect(DB_NAME);


// set error object
error_reporting(E_ALL ^ E_NOTICE);
$_errorhandler = new cc_errorhandler();
set_error_handler(array($_errorhandler, 'regError'), E_ALL ^ E_NOTICE);

// load configs from DB
$_configs = $db->key_value("param", "value", DBTABLE_CONFIG);


if(!empty($_configs['debug'])) ini_set("display_errors", "1");


// load project helper class
if(file_exists(FILEROOT."required/projectHelper.class.php")){
	include_once FILEROOT."required/projectHelper.class.php";
	$helper = new projectHelper();
}

// Translation. Get language from cookie (should be setted during login), if no cookie is found use default lang of browser
$usercookie = new user_cookie(); // creare cookie instance to get language code for user (if any)
$lang = ($usercookie->getLang()) ? $usercookie->getLang() : false; // get user lang else false so browser defined lang is used
$lang_code = $db->get1value("code", DBTABLE_LANGUAGES, "WHERE id = '".$lang."'"); // get lang code (ex. "en" or "it")
$_t = new cc_translate($db, "", $lang_code); // create instance of translate class

// set user object
$_user = new cc_user($_SESSION['login_id'], $db);

// set local settings and timezone
date_default_timezone_set($_user->getTimezone);

$bootstrap = new phpbootstrap();

// if page id is passed memorize it in $pid and set $canwrite and $candelete switches. If no page id is passed always set canwrite and candelte to true
if(!empty($_POST['pid'])){
	$pid = (int) $_POST['pid'];
	unset($_POST['pid']);
	// get switches for write and delete permissions
	$_user->setPagePermissions($pid);
	//$canwrite 	= $_user->canWrite($pid); // obsolete, use $_user->can...()
	//$candelete 	= $_user->canDelete($pid); // obsolete, use $_user->can...()
}else{
	//$candelete = $canwrite = true;
}


// Set default vars
$output = array();
$output['result'] = false;
$output['error'] = $_t->get('nopost'); // title of modal box
$output['msg'] = $_t->get('nopost_message'); // message inside modal box
$output['errorlevel'] = "danger"; // color of modal box
$output['qry'] = "";
$output['dbg'] = "";

?>
