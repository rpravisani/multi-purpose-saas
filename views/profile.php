<?php
defined('_CCCMS') or die;
/*****************************************************
 *** VIEW                                          ***
 *****************************************************/

ob_start();
?>

<div class="row">
	
	<div class="col-md-4">
	
		<div class="box box-warning">
			
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

              <h3 class="profile-username text-center">Account</h3>
	
              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                    <b>Tipo utente</b> <span data-name='subscription_type' class="pull-right "><?php echo $subscription; ?> </span> 
                </li>
                <li class="list-group-item">
                    <b>Password</b> <span data-name='password' class="pull-right ">******** <sup id="modifica-password">Modifica</sup> </span> 
                </li>
              </ul>

              <button data-action='edit' id='change-user-account' disabled class="btn btn-primary btn-block"><b>Modifica</b></button>
            </div>
            <!-- /.box-body -->
          </div>	
	
	</div>
	<div class="col-md-4">
	    
		<div class="box box-info">
			
            <div class="box-body box-profile">

              <h3 class="profile-username text-center">Dati Anagrafici</h3>
	
              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <b>Nome</b> <span data-name='name' class="pull-right editable "><?php echo $_data['name']; ?></span> 
                </li>
                <li class="list-group-item">
                  <b>Cognome</b> <span data-name='surname' class="pull-right editable "><?php echo $_data['surname']; ?></span> 
                </li>
                <li class="list-group-item">
                  <b>Email</b> <span data-name='email' class="pull-right editable "><?php echo $_data['email']; ?></span> 
                </li>
                <li class="list-group-item">
                  <b>Telefono</b> <span data-name='telephone' class="pull-right editable vuoto"><?php echo $_data['telephone']; ?></span>
                </li>
              </ul>

              <button data-action='edit' id='change-user-data' class="btn btn-primary btn-block"><b>Modifica</b></button>
            </div>
            <!-- /.box-body -->
          </div>	
	    
	</div>
</div>

<div id="pwd-modal" class="modal modal fade ">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content">

            <div class="modal-header">
                <a type="button" class="close" data-dismiss="modal" aria-label="Chiudi"><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">Modifica password <span></span></h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-md-12">
                        <?php echo $bootstrap->field($args_pwd); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                        <?php echo $bootstrap->field($args_pwd_repeat); ?>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="pwd-cancel" class="btn btn-danger btn-sm pull-right" data-label="Annulla">Annulla</button>
                <button type="button" id="pwd-save" class="btn btn-success btn-sm pull-right" data-label="Conferma" >Conferma</button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<?php
$_output = ob_get_contents();
ob_end_clean();
?>