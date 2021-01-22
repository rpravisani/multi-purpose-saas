<?php
defined('_CCCMS') or die;
/*****************************************************
 *** VIEW                                          ***
 *****************************************************/

ob_start();


?>


<div class="row">
	
	<div class="col-md-4">
	
		<div class="box box-primary">
			
            <div class="box-body box-profile">
				
              <img class="profile-user-img img-responsive img-circle" src="avatars/<?php echo $avatar; ?>" alt="User profile picture">

              <h3 class="profile-username text-center"><?php echo $displayname; ?></h3>

              <p class="text-muted text-center">Nome utente: <strong><?php echo $_data['username']; ?></strong></p>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>Account creato</b> <span class="pull-right"><?php echo $subscription_date; ?></span>
                </li>
                <li class="list-group-item">
                  <b>Ultimo accesso</b> <span class="pull-right"><?php echo $last_active; ?></span>
                </li>
              </ul>

              <a href="logout.php" class="btn btn-danger btn-block"><b>Disconnetti</b></a>
            </div>
            <!-- /.box-body -->
          </div>	
	
	</div>
	<div class="col-md-4">
	
		<div class="box box-primary">
			
            <div class="box-body box-profile">
				


              <h3 class="profile-username text-center">Dati Anagrafici</h3>
	
              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>Email</b> <span data-name='email' class="pull-right editable "><?php echo $_data['email']; ?></span> 
                </li>
                <li class="list-group-item">
                  <b>Telefono</b> <span data-name='telephone' class="pull-right editable"><?php echo $_data['telephone']; ?></span>
                </li>
              </ul>

              <button data-action='edit' id='change-user-data' class="btn btn-primary btn-block"><b>Modifica</b></button>
            </div>
            <!-- /.box-body -->
          </div>	
	
		
	</div>
	<div class="col-md-4"></div>
</div>


<?php
$_output = ob_get_contents();
ob_end_clean();
?>