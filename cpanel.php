<?php error_reporting(E_ALL ^ E_NOTICE);
ini_set("display_errors", "1"); ?>
<?php include_once 'required/required.php'; ?>
<?php
// select assets to load
$timepicker = $bootstrap->loadTimePicker();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo strip_tags(NOME_DITTA_LOGO_L); ?> | <?php echo $page['title'] ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
      
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
      
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome -->
    <?php if(LOCALHOST){ ?>
    <link rel="stylesheet" href="css/cache/font-awesome.min.css"> <!-- offline fallback -->
    <?php }else{ ?>
    <link rel="stylesheet" href="<?php echo FONT_AWESOME_URL; ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <?php } ?>

	<!-- Select2 -->
    <link rel="stylesheet" href= "plugins/select2/select2.min.css">    
    <?php if($timepicker){ ?>
 	<!-- Bootstrap time Picker -->
    <link rel="stylesheet" href="plugins/timepicker/bootstrap-timepicker.min.css">
	<?php } ?>
 	<!-- Bootstrap date Picker -->
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">    
	<?php if($_pagetype == "table" or $_pagetype == "inline-table" ){  ?>
    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap.css">
	<?php }  ?>
    <!-- Theme style -->
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="css/ccextra.css?v=<?php echo filemtime(FILEROOT.'css/ccextra.css') ?>">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="css/skins/_all-skins.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/square/blue.css">

    <?php if(file_exists(FILEROOT."css/project-style.css")){ ?>
    <!-- project specific css -->
    <link rel="stylesheet" href="css/project-style.css">
	<?php } ?>
    
	<?php if(@$_upload){ ?>
    <!-- dropzone.js css -->
    <?php echo $_upload->getCss(); ?>
	<?php } ?>

	<?php 
	if(!empty($css_assets)){ 
		foreach($css_assets as $css_asset){
	?>
	<link rel="stylesheet" href="<?php echo $css_asset; ?>">
    <?php
		}
	}
	?>
    <?php if(file_exists(FILEROOT."css/pages/".$css_file)){ ?>
	<?php $css_page_md = filemtime(FILEROOT."css/pages/".$css_file); ?>
    <!-- page specific css -->
    <link rel="stylesheet" href="css/pages/<?php echo $css_file."?v=".$css_page_md; ?>">
	<?php } ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition skin-blue sidebar-mini page<?php echo $pid; if($_view == 'fullscreen' or $_mobile_full_screen ) echo " sidebar-collapse"; echo " ".$_device; ?>">
    <!-- Site wrapper -->
    <div class="wrapper">
    
	<?php if($_view != "pdf"){ ?>
      <header class="main-header">
        <!-- Logo -->
        <a href="cpanel.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><img src='images/logo-xs.png' width="35"><?php //echo NOME_DITTA_LOGO_S; ?></span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg"><img src='images/logo-sm.png' style='max-width: 100%' ><?php //echo NOME_DITTA_LOGO_L; ?></span>
        </a>
        
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Attiva/Disattiva navigazione</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
            	<li id="screenshot-wia" class="dropdown" style="display: none;">
                	<a class="bg-red" id="attendere">
                  		Attendere, sto registrando schermata
                	</a>
                </li>
                <?php if($_SESSION['login_type'] == "SA"){ ?>
            	<li class="dropdown messages-menu">
                	<a class="puls" data-maintenance='<?php echo MAINTENANCE; ?>' id="maintenance" title="Maintenance mode <?php echo strtoupper(MAINTENANCE); ?>">
                  		<i class="fa fa-wrench maintenance-<?php echo MAINTENANCE; ?>"></i>
                	</a>
                </li>
                <?php } ?>
            	<li class="dropdown messages-menu">
                	<a class="puls" id="screenshot" title="Segnala un errore o problema allo sviluppatore">
                  		<i class="fa fa-bullhorn"></i>
                	</a>
                </li>
          
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="avatars/<?php echo $_user->getAvatar(); ?>" class="user-image" alt="User Image">
                  <span class="hidden-xs"><?php echo $_user->getName("asis"); ?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- User image -->
                  <li class="user-header">
                    <img src="avatars/<?php echo $_user->getAvatar(); ?>" class="img-circle" alt="User Image">
                    <p>
                      <?php echo $_user->getName("asis"); ?>
                      <?php
					  	$user_subscription_month = strftime("%b.", $_user->getSubscriptionDate(true));
					  	$user_subscription_year = date("Y", $_user->getSubscriptionDate(true));
						$subscription_name = $_user->getSubscription("name");
					  ?>
                      <small><?php echo $_t->get('subscription')." <strong>".$subscription_name . "</strong><br>\n" . $_t->get('member-since')." ".$user_subscription_month." ".$user_subscription_year; ?></small>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="cpanel.php?pid=68&v=html" class="btn btn-default btn-flat"><?php echo $_t->get('profile') ?></a>
                    </div>
                    <div class="pull-right">
                      <a href="logout.php" class="btn btn-default btn-flat"><?php echo $_t->get('logout') ?></a>
                    </div>
                  </li>
                </ul>
              </li>
              <!-- Control Sidebar Toggle Button -->
             <!-- <li>
                <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
              </li>-->
            </ul>
          </div>
        </nav>
      </header>

      <!-- =============================================== -->

      <!-- Left side column. contains the sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
          <!-- Sidebar user panel -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="avatars/<?php echo $_user->getAvatar(); ?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p><?php echo $_user->getName("asis"); ?></p>
              <?php   //if($_user->getSubscription() === 0) echo "<small><em>DB vers. <strong>".DB_NAME."</strong></em></small>"; ?>
              <?php   //if($_user->getSubscription() === 0) echo "<small><em>PHP vers. <strong>".phpversion()."</strong></em></small>"; ?>
              <?php   if($_user->getSubscription() === 0){
                          echo "<small><em>PHP vers. <strong>".phpversion()."</strong></em></small>";                           
                      }else{
                          echo "<small>Account <strong>".$_user->getSubscription('name')."</strong></small>"; 
                      }
              ?>
              <!--<a href="#"><i class="fa fa-circle text-success"></i> Online</a>-->
            </div>
          </div>
          <?php if(SEARCH_IN_SIDEBAR){ ?>
          <!-- search form -->
          <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="<?php echo $_t->get('search') ?>...">
              <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>
          <!-- /.search form -->
          <?php } ?>
          <!-- sidebar menu: : style can be found in sidebar.less -->
          <ul class="sidebar-menu">
            <li class="header"><?php echo strtoupper($_t->get('navigation')); ?></li>
            
            <?php
			$menu->prepareMenu();
			$menu->outputMenu();
			?>
            
         
          </ul>
        </section>
        <!-- /.sidebar -->
      </aside>
      
      <?php } // end if $_view != pdf ?>

      <!-- =============================================== -->

      <!-- Content Wrapper. Contains page content -->
      <div id="content-wrapper" class="content-wrapper <?php if($_view == "pdf") echo "content-wrapper-full-width"; ?>">
        <!-- Content Header (Page header) -->
        
		<?php if($_view != "pdf"){ ?>
        <section class="content-header">
          <h1>
            <?php echo $_cpanel_title; ?>
            <small><?php echo $_cpanel_subtitle; ?></small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="cpanel.php"><i class="fa fa-dashboard"></i> <?php echo strip_tags(NOME_DITTA_LOGO_L); ?></a></li>
            <?php if( $_user->getDefaultPage() != $pid ) echo $menu->getBreadcrumb(); ?>
          </ol>
        </section>
		<?php } // end if $_view != pdf ?>
        
        <?php
		
		if(!empty($page_alerts)){
			echo "<section id=\"page_alerts\" class=\"content-header\">\n";
			foreach($page_alerts as $page_alert){
				echo $page_alert;
			}
			echo "</section>\n";
		}
		
		
		if(!empty($_errorhandler->errors)){
			echo "<section id=\"page_errors\" class=\"content-header\">\n";
			echo $_errorhandler->getErrors();
			echo "</section>\n";
		}
		
		?>

        <!-- Main content -->
        <section <?php if($_view != "pdf") echo 'class="content"'; ?>>
        
        <?php echo $_output; // defined in view ?>        

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

	  <?php if($_view != "pdf"){ ?>	
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          CMR framework <strong title='DB: <?php echo DB_NAME; ?>'>Version</strong> <?php echo $_version; ?>
        </div>
        <strong>Developed by <a href="http://saasonthebeach.com">SaaSonthebeach</a>.</strong> All rights reserved.
      </footer>
      <?php } // end if $_view != pdf ?>

      <!-- Control Sidebar -->
      <!--HERE HTML Control Sidebar-->
      <!-- /.control-sidebar -->
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
    
    <div id="page-loader">
    	<i class="fa fa-refresh fa-spin"></i>
    </div>

    <!--Modal dialog box -- initally hidden, use display : block to show-->
    <div id="message-modal">
        <div class="modal "> <!-- Class modal-* missing -- will be added by javascript -->
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo $_t->get('close') ?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Modal dialog box</h4>
              </div>
              <div class="modal-body">
                <div>Message</div>
              </div>
              <div class="modal-footer">
                <button type="button" id="modal-close" class="btn btn-outline btn-sm pull-right" data-label="<?php echo $_t->get('close') ?>" data-dismiss="modal"><?php echo $_t->get('close') ?></button>
                <button type="button" id="modal-cancel" class="btn btn-danger btn-sm pull-right" data-label="<?php echo $_t->get('cancel') ?>" data-dismiss="modal"><?php echo $_t->get('cancel') ?></button>
                <button type="button" id="modal-save" class="btn btn-success btn-sm pull-right" data-label="<?php echo $_t->get('save') ?>" ><?php echo $_t->get('save') ?></button>               
              </div>
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
     </div><!-- /#message-modal -->



    <!-- jQuery 2.1.4 -->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <!-- jquery-ui-1.11.4.custom -->
    <script src="js/jquery-ui/jquery-ui.min.js"></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="js/bootstrap.min.js"></script>
	<?php if($_pagetype == "table" or $_pagetype == "inline-table"){  ?>
    <!-- DataTables -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="plugins/datatables/dataTables.dateUkTypeDetect.js"></script>
    <script src="plugins/datatables/dataTables.dateUk.js"></script>
	<?php } ?>
    <!-- SlimScroll -->
    <script src="plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <!-- FastClick -->
    <script src="plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="js/app.min.js?vers=<?php echo $_version; ?>"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="js/demo.js?vers=<?php echo $_version; ?>"></script>
	<!-- InputMask -->
    <script src= "plugins/input-mask/jquery.inputmask.js"></script>
    <script src= "plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src= "plugins/input-mask/jquery.inputmask.extensions.js"></script>
	<!-- Select2 -->
    <script src= "plugins/select2/select2.full.min.js"></script>
    <script src= "plugins/select2/i18n/it.js"></script>
    <?php if($timepicker){ ?>
	<!-- bootstrap time picker -->
    <script src="plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <?php } ?>
	<!-- bootstrap date-picker -->
    <script src="plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="plugins/datepicker/locales/bootstrap-datepicker.it.js"></script>
	<!-- take screenshot of page -->
    <script src="plugins/html2canvas.js"></script>
    <!-- iCheck -->
    <script src="plugins/iCheck/icheck.min.js"></script>
	
	<?php if(@$_upload){ ?>
    <!-- dropzone.js  -->
    <?php echo $_upload->getCoreJs(); ?>
	<?php } ?>

    <!-- Generic javascript functions -->
    <script src="js/general.js?vers=<?php echo $_version; ?>"></script>
    
	<?php 
	if(!empty($js_assets)){ 
		foreach($js_assets as $js_asset){
	?>
    <script src="<?php echo $js_asset."?vers=".$_version; ?>"></script>
    <?php
		}
	}
	?>
    
    <?php if(file_exists(FILEROOT."js/pages/".$js_file)){ ?>
    <!-- page specific js -->
    <script src="js/pages/<?php echo $js_file."?vers=".$_version; ?>"></script>
	<?php } ?>
    <!-- page script -->
	<script type="text/javascript">    
    <?php echo $_js_gets; // make parsed get values available in javascript (see parsegets.php) ?>
	<?php 
	if($_pagetype == "table" or $_pagetype == "inline-table"){ 
		echo $_table->getJs();
	 } 
	?>
	<?php if(@$_upload){ ?>
    <!-- dropzone setup  -->
    <?php echo $_upload->setup(); ?>
	<?php } ?>
	
	window.onbeforeunload = function () {
	   if (!okayToLeave) {
		   return "<?php echo $_t->get("notok2leave"); ?>";
	   }
	}
	$(function () {
		
		<?php if($num_notification_files > 0){ ?>
		getNotifications();
		window.setInterval(getNotifications, <?php echo $_configs['notification_interval']; ?>);
		<?php } ?>
		
        //Initialize Select2 Elements
        $(".select2").select2({ "language": "it-IT" });
		
        $(".select2a").select2({
			"language": { 
				"noResults": function(){ 
					return "Nessun risultato trovato <div class='btn btn-primary btn-xs pull-right add-select2-value'><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;Inserisci.</div>";
				}
			}, 
			escapeMarkup: function (markup) { 
				return markup;
			}			
		});

        //Datemask dd/mm/yyyy
        $(".datemask").inputmask("dd/mm/yyyy", {"placeholder": "gg/mm/aaaa"});
		
		// date picker
		$('.datepicker').datepicker({
			language: 'it', autoclose: true
		});
		
		<?php if($timepicker) echo $timepicker; ?>
		
	});
	
    </script>
     
  </body>
</html>
