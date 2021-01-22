<?php
session_start();
include_once 'required/variables.php';
include_once 'required/functions.php';
include_once 'required/classes/cc_mysqli.class.php';
include_once 'required/classes/cc_translations.class.php';
include_once 'required/classes/user_cookie.class.php';

// create DB object
$db = new cc_dbconnect(DB_NAME);

// IS USER BLACKLISTED?
$blacklist = $db->col_value( "IP", "system_blacklist" );
$ip = $_SERVER[ 'REMOTE_ADDR' ];

// if I'm here by error resend me back to panel and consequantially to login if not logged in 
if ( in_array( $ip, $blacklist ) and !LOCALHOST  ) {

  header( 'location: ' . HTTP_PROTOCOL . HOSTROOT . SITEROOT . "banned.php" );
  exit();

}


// From where did I get here?
log_attempt("login", "", "Landed on login page");

// Pre-authentication language handler script
include_once 'required/access-language-handler.php';

// get languages options
$lang_options = getSelectOptions("code", "language", DBTABLE_LANGUAGES, $lang_code, false, "WHERE active = '1'", false);

// intercept gets
$gets = $db->make_data_safe($_GET);
unset($_GET);

// eliminate session values that are not needed
unset($_SESSION['login']);
unset($_SESSION['login_id']);
unset($_SESSION['login_type']);
unset($_SESSION['login_time']);
unset($_SESSION['login_time_formatted']);
unset($_SESSION['access_log_id']);

// password lost and reset options
$form_url = "verificautente.php";
$lost_pwd = $reset_pwd = $first_access = $action = false;
$form_title = $_t->get("signin");
$send_button = $_t->get("signin-button");
$glyphicon = "glyphicon-envelope";
$form_placeholder = $_t->get("user-placeholder");
$disabled_btn = ""; 

// let's see if deviation from standard fucntion has to be made, based on the action value passed with get
if(!empty($gets['action'])){
	$action = $db->make_data_safe($gets['action']);
	switch($action){
		case "first_access":
			//if(!empty($gets['t'])){
				$first_access = true; 
				$form_url = "firstaccess.php";
				$form_title = $_t->get("first_access_form_title");
				$form_placeholder = $_t->get("first_access_form_placeholder");
				$send_button = $_t->get("first_access_send_button");
				$glyphicon = "glyphicon-th";				
			//}
			break;
		case "lost":
			// form to request reset password
			$lost_pwd = true;
			$form_url = "lostpassword.php";
			$form_title = $_t->get("lost_password_form_title");
			$send_button = $_t->get("lost_password_send_button");
			break;
		case "reset":
			// check token and id
			if(check_reset_token($gets['t'])){
				$reset_pwd = true;
				$form_url = "resetpassword.php";
				//$form_title = (isset($gets['fa'])) ? "Crea la tua password" : "Imposta la nuova password"; // TODO translation
				//$send_button = (isset($gets['fa'])) ? "Imposta" : "Cambia"; // TODO translation
				$form_title = (isset($gets['fa'])) ? "Crea la tua password" : "Imposta la nuova password"; // TODO translation
				$send_button = (isset($gets['fa'])) ? "Imposta" : "Cambia"; // TODO translation
				$disabled_btn = "disabled";
			}else{
				// set error message token not valid - TODO translate
				//$_SESSION['error'] = "Password reset token not valid or not valid anymore.<br>If you have copy and pasted the url please double-check you have copied the whole string. In any other case please request an other password reset.";
				$_SESSION['error'] = "Il gettone per il recupero password non è (più) valido.<br>Se hai fatto copia & incolla dell'url controlla che tu abbia copiato tutta la stringa. Eventualmente effettua un nuovo reset password.";
				
				log_attempt("login", $gets['t'], "Token not valid (anymore)");
				
			}
			break;
		default:
			break;
	}
}



// multi login - currently not used
if(MULTI_LOGIN){
	if(!isset($gets[MULTI_LOGIN_FIELD])){
		if(!isset($_SESSION['multi_login_id'])){
			header('location: '.HTTP_PROTOCOL.HOSTROOT.SITEROOT.ADMINSECTION.NOLOGIN); // No access page;
			die();
		}else{
			$multi_login_id = $_SESSION['multi_login_id'];
		}
	}else{
		$multi_login_id = $gets[MULTI_LOGIN_FIELD];
	}
}

// If there are no login-errors check if maintenance mode is active
if(empty($_SESSION['error'])){
	// get flag from config table
	$maintenance_mode = $db->get1value("value", DBTABLE_CONFIG, "WHERE param = 'maintenance_mode'");
	if($maintenance_mode == 'on'){
		//$_SESSION['error'] = $_t->get('maintenance-mode');
		$_SESSION['error'] = "Portale in fase di manutenzione, non è possibile accedere al momento";
	}
}

$rememberme = ($usercookie->rememberme() > time()) ? "checked" : "";

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
   
   <?php if(MULTI_LANG){ ?>
    <div class="login-header">
    	<div class="pull-right">
        	<select id="langswitch">
            	<option value="">Select Language</option>
            	<?php echo $lang_options; ?>
            </select>
        </div>
    </div>
    <?php } ?>
    
    <div class="login-box">
      <div class="login-logo">
        <!--<a href=""><?php echo NOME_DITTA_LOGO_L; ?></a>-->
		  <img style="width: 100%;" src='images/logo.png'>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg"> <?php echo $form_title; ?></p>
        <?php if (@$_SESSION['login'] == false and @$_SESSION['error'] != ""){ ?>
        <div class="callout callout-warning">
            <p><?php echo $_SESSION['error']; ?></p>
        </div>
        <?php 
		}
		unset($_SESSION['error']);
		?>
        <?php if (@$_SESSION['login'] == false and @$_SESSION['success_message'] != ""){ ?>
        <div class="callout callout-success">
            <p><?php echo $_SESSION['success_message']; ?></p>
        </div>
        <?php 
		}
		unset($_SESSION['success_message']);
		?>

        <form action="<?php echo $form_url; ?>" <?php if($reset_pwd) echo "onSubmit='validateForm()'"; ?> method="post">
         
          <?php if($reset_pwd or $first_access){ // if first access or reset password pass token value to hidden field ?>
          <input type="hidden" name="token" value="<?php echo $gets['t']; ?>">
          <?php } ?>
          
          <?php if(isset($gets['fa'])){ ?>
          <input type="hidden" name="fa" value="1">
          <?php } ?>
        	
          <?php if(!$reset_pwd){ // if action is not reset password print out email/username field ?>
          <div class="form-group has-feedback">
            <input id="email" name="email" autofocus="autofocus" type="text" class="form-control" placeholder="<?php echo $form_placeholder; ?>">
            <span class="glyphicon <?php echo $glyphicon; ?> form-control-feedback"></span>
          </div>
          <?php } // end if not reset_pwd ?>
          
          <?php if(!$lost_pwd and !$first_access){ // if action is not first access and not reset password print out password field ?>
          <div class="form-group has-feedback">
            <input id="password" name="password" type="password" class="form-control" placeholder="<?php echo $_t->get("password-placeholder"); ?>">
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <?php } // end if not lost_password and not fist access ?>
          
          <?php if($reset_pwd){ // if action is rest password print out repeat password field ?>
          <div class="form-group has-feedback">
            <input id="password2" name="password2" type="password" class="form-control" placeholder="<?php echo $_t->get("repeat-password-placeholder"); ?>">
            <span class="glyphicon form-control-feedback"></span>
          </div>
          <?php } // end if reset_password ?>
          
          <div class="row">
            <div class="col-xs-8">
			<?php if(REMEMBER_ME and !$action){ // if standard login and remember me flag is true print out remember me checkbox ?>
              <div class="checkbox icheck">
                <label>
                  <input type="checkbox" name="rememberme" id="rememberme" <?php echo $rememberme; ?> > <?php echo $_t->get("remember-me"); ?>
                </label>
              </div>
         	<?php } ?>
            </div><!-- /.col -->
            <div class="col-xs-4">
              <button id="submitbtn" type="submit" <?php echo $disabled_btn; ?> class="btn btn-primary btn-block btn-flat"><?php echo $send_button; ?></button>
            </div><!-- /.col -->
          </div>
        </form>

		<?php if(!empty($social_auth_links)){ // allow user to access / register true socialnetworks login?>
        <div class="social-auth-links text-center">
          <p>- <?php echo strtoupper($_t->get("or")); ?> -</p>
          <?php if(in_array("facebook", $social_auth_links)){ ?>
          <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> <?php echo $_t->get("signin-fb"); ?></a>
          <?php } ?>
          <?php if(in_array("google", $social_auth_links)){ ?>
          <a href="#" class="btn btn-block btn-social btn-google btn-flat"><i class="fa fa-google-plus"></i> <?php echo $_t->get("signin-google"); ?></a>
          <?php } ?>
        </div><!-- /.social-auth-links -->
        <?php } ?>

		<?php if(FORGOT_PASSWORD and !$action){ // if standard login and forgot password flag is true print out link for forgot password ?>
        <a href="login.php?action=lost"><?php echo $_t->get("forgot-password"); ?></a><br>
        <?php } ?>
        
        <?php if(SUBSCRIPTION_PLANS and SHOW_REGISTER){ // if subscription plans are active and the allow user to register flag is true print out the link to the registeruser page ?>
        <a href="register.php" class="text-center"><?php echo $_t->get("new-membership"); ?></a> <!--TODO: big button under form-->
        <?php } ?>
        
		<?php if($reset_pwd or $lost_pwd){ // if action is rest password print out return to login link ?>
        <a href="login.php"><?php echo $_t->get("back-to-login"); ?></a><br>
      	<?php } ?>      

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="js/bootstrap.min.js"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js"></script>
	<script type="text/javascript">
	$( document ) . on( "change", "#langswitch", function () {
		var newLang = $( this ) . val();
		$ . post(
			"helpers/change_lang_cookie.php", {
				lang: newLang
			},
			function ( response ) {
				if ( response . result ) {
					location . reload();
				}
			},
			"json"
		);

	} );
		
	<?php if($reset_pwd){ ?>

	$( document ) . on( "keyup", "#password", function () {
		var span = $( "#password2" ) . closest( "div" ) . find( "span" );
		var p1 = $( this ) . val();
		var p2 = $( "#password2" ) . val();
		if ( p2 != "" ) {
			if ( p2 == p1 ) {
				// password2 is the same as password1 put check and activte submit button
				span . removeClass( "glyphicon-remove text-red" );
				span . addClass( "glyphicon-ok text-green" );
				$( "#submitbtn" ) . prop( "disabled", false );

			} else {
				span . removeClass( "glyphicon-ok text-green" );
				span . addClass( "glyphicon-remove text-red" );
				$( "#submitbtn" ) . prop( "disabled", true );
			}
		}
	} );

	$( document ) . on( "keyup", "#password2", function () {
		var span = $( this ) . closest( "div" ) . find( "span" );
		var p1 = $( "#password" ) . val();
		var p2 = $( this ) . val();
		if ( p2 == p1 ) {
			// password2 is the same as password1 put check and activte submit button
			span . removeClass( "glyphicon-remove text-red" );
			span . addClass( "glyphicon-ok text-green" );
			$( "#submitbtn" ) . prop( "disabled", false );

		} else {
			span . removeClass( "glyphicon-ok text-green" );
			span . addClass( "glyphicon-remove text-red" );
			$( "#submitbtn" ) . prop( "disabled", true );
		}
	} );
	<?php } ?>

	$( function () {
		$( 'input' ) . iCheck( {
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		} );
	} );

	<?php if(!$reset_pwd){ ?>
	window.onload = function(){
		// document getElementById( 'email' ).focus(); // Non servirebbe perché realtà c'è "autofocus" in campo email
	}
	<?php } ?>

	function validateForm() {
		var p1 = $( "#password" ) . val();
		var p2 = $( "#password2" ) . val();
		p1 = p1 . trim();
		p2 = p2 . trim();
		if ( p1 != p2 || p1 == "" || p2 == "" ) return false;
	}
</script>    
  </body>
</html>
