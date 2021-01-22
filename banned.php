<?php
session_start();
include_once 'required/variables.php';
include_once 'required/functions.php';
include_once 'required/classes/cc_mysqli.class.php';
include_once 'required/classes/cc_translations.class.php';
include_once 'required/classes/user_cookie.class.php';

// create DB object
$db = new cc_dbconnect( DB_NAME );

// get blacklist
$blacklist = $db->col_value( "IP", "system_blacklist" );
$ip = $_SERVER[ 'REMOTE_ADDR' ];

// if I'm here by error resend me back to panel and consequantially to login if not logged in 
if ( !in_array( $ip, $blacklist ) or LOCALHOST ) {

  header( 'location: ' . HTTP_PROTOCOL . HOSTROOT . SITEROOT . PANEL );
  exit();

}


// Pre-authentication language handler script
include_once 'required/access-language-handler.php';

// get languages options
$lang_options = getSelectOptions( "code", "language", DBTABLE_LANGUAGES, $lang_code, false, "WHERE active = '1'", false );

// intercept gets
unset( $_GET );

// eliminate session values that are not needed
unset( $_SESSION[ 'login' ] );
unset( $_SESSION[ 'login_id' ] );
unset( $_SESSION[ 'login_type' ] );
unset( $_SESSION[ 'login_time' ] );
unset( $_SESSION[ 'login_time_formatted' ] );
unset( $_SESSION[ 'access_log_id' ] );


?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo strip_tags(NOME_DITTA_LOGO_L); ?> | <?php echo $_t->get("page-title"); ?></title>
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">

<!-- Bootstrap 3.3.5 -->
<link rel="stylesheet" href="css/bootstrap.min.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
<!-- Ionicons -->
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="css/AdminLTE.css">
<!-- Theme style -->
<link rel="stylesheet" href="css/ccextra.css">
<!-- iCheck -->
<link rel="stylesheet" href="plugins/iCheck/square/blue.css">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries --> 
<!-- WARNING: Respond.js doesn't work if you view the page via file:// --> 
<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo"> 
    <!--<a href=""><?php echo NOME_DITTA_LOGO_L; ?></a>--> 
    <img style="width: 100%;" src='images/logo.png'> </div>
  <!-- /.login-logo -->
  
  <div class="login-box-body ">
      <h2 class='text-center'><i class="fa fa-ban fa-3x text-danger"></i></h2>
      <h3 class='text-danger text-center'>YOUR IP <strong><?php echo $ip; ?></strong> HAS BEEN BLACKLISTED.</h3>
      <h4>This is due to too many failed login attempts. If you are a legitimate user of this platform and think there's been an error, please contact the provider of this services (<?php echo NOME_DITTA_LOGO_L; ?>).</h4>
      
  </div>
  <!-- /.login-box-body --> 
</div>
<!-- /.login-box --> 

<!-- jQuery 2.1.4 --> 
<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script> 
<!-- Bootstrap 3.3.5 --> 
<script src="js/bootstrap.min.js"></script> 
<!-- iCheck --> 
<script src="plugins/iCheck/icheck.min.js"></script> 
<script type="text/javascript">
	
</script>
</body>
</html>
