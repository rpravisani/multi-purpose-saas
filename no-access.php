<?php
session_start();
set_include_path('required');
include_once 'variables.php';
include_once 'functions.php';
include_once 'classes/cc_mysqli.class.php';
include_once 'classes/cc_translations.class.php';
include_once 'classes/cc_phpbootstrap.class.php';
include_once 'classes/cc_errorhandler.class.php';
include_once 'classes/user_cookie.class.php';

error_reporting(E_ALL ^ E_NOTICE);
$_errorhandler = new cc_errorhandler();
set_error_handler(array($_errorhandler, 'regError'), E_ALL ^ E_NOTICE);

if(DEBUG) ini_set("display_errors", "1");

$db = new cc_dbconnect(DB_NAME);

// Translation. Get language from cookie (should be setted during login), if no cookie is found use default lang of browser
$usercookie = new user_cookie(); // creare cookie instance to get language code for user (if any)
$lang = ($usercookie->getLang()) ? $usercookie->getLang() : false; // get user lang else false so browser defined lang is used
$lang_code = $db->get1value("code", DBTABLE_LANGUAGES, "WHERE id = '".$lang."'"); // get lang code (ex. "en" or "it")
$_t = new cc_translate($db, "LOGIN", $lang_code); // create instance of translate class

// creating bootstrap object -- not compulsory, but can make things easier and faster
$bootstrap = new phpbootstrap();

if(empty($_SESSION['error_message'])){
	$message = $bootstrap->alert( "PAGINA CON ACCESSIBILE", "Questa pagine non è (più) accessibile.", "danger", false);
}else{
	$message = $bootstrap->alert( $_SESSION['error_title'], $_SESSION['error_message'], "danger", false);
	unset($_SESSION['error_title']);
	unset($_SESSION['error_message']);
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Picasso TPL | NO ACCESS!</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/ccextra.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="css/skins/_all-skins.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
    	<div class="content-wrapper content-wrapper-full-width">
            <section id="page_alerts" class="content-header">
                <?php echo $message; ?>
            </section>
            <div style="padding: 30vh 0; text-align: center;">
	            <img src="images/logo-picasso-gomme-50.png">
            
            </div>
        </div>
    </div>
</body>
</html>