<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: gestione-stock.php                  ***
 *****************************************************/

ob_start();
?>
    <div class="box">
        
        <div class="box-header with-border">
	        <i class="fa fa-edit"></i>
        	<h3 class="box-title"><?php echo $boxtitle; ?></h3>
                    <?php
					if(!empty($copied_label)){
						echo "&nbsp;&nbsp;<small class=\"label bg-teal\"><em>".$copied_label."</em></small>";
					}
					?>
                <?php if(!empty($_record)){ ?>
                <!-- new and copy buttons (only if edit) -->
                <div class="pull-right">
                    <button id="newRecordBtn" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo $_t->get('new'); ?></button>                
                    <button id="copyRecordBtn" class="btn btn-info btn-sm"><i class="fa fa-copy"></i>&nbsp;&nbsp;<?php echo $_t->get('copy'); ?></button>                
                </div>
                <?php } ?>
            
        </div> <!--box-header end-->
        
        <form class="" id="<?php echo $page['file_name']; ?>-form" action="required/write2db.php" method="post">
        	<input type="hidden" name="pid" id="pid" value="<?php echo $pid; ?>" />
        	<input type="hidden" name="action" id="action" value="<?php echo $_action; ?>" />
        	<input type="hidden" name="record" id="recrod" value="<?php echo $_record; ?>" />        
        
            <div class="box-body">
            	
                <div class="row">

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Name</label>
                          <input type="text" name="name" id="name" value="<?php echo $_data['name']; ?>" placeholder="Name..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Surname</label>
                          <input type="text" name="surname" id="surname" value="<?php echo $_data['surname']; ?>" placeholder="Surname..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Username</label>
                          <input type="text" name="username" id="username" value="<?php echo $_data['username']; ?>" placeholder="The username..." class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Password</label>
                          <input type="text" name="password" id="password" value="" placeholder="<?php echo $pwd_title ?>" class="form-control" <?php echo $pwd_mandatory ?>>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Email</label>
                          <input type="email" name="email" id="email" value="<?php echo $_data['email']; ?>" placeholder="User email..." class="form-control" <?php echo $email_mandatory; ?>>
                        </div>
                    </div>

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Telephone</label>
                          <input type="text" name="telephone" id="telephone" value="<?php echo $_data['telephone']; ?>" placeholder="A telephone number..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Vat</label>
                          <input type="text" name="vatnumber" id="vatnumber" value="<?php echo $_data['vatnumber']; ?>" placeholder="The vat number..." class="form-control">
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-5">        
                        <div class="form-group">
                          <label>Nation</label>
                          <select class="form-control select2" id="nation" name="nation" required>
                          <?php echo $nations; ?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-5">        
                        <div class="form-group">
                          <label>City</label>
                          <input type="text" name="city" id="city" value="<?php echo $_data['city']; ?>" placeholder="City name..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>P.O. Box</label>
                          <input type="text" name="pobox" id="pobox" value="<?php echo $_data['pobox']; ?>" placeholder="P.O. Box.." class="form-control">
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Address</label>
                          <input type="text" name="address1" id="address1" value="<?php echo $_data['address1']; ?>" placeholder="Address..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>&nbsp;</label>
                          <input type="text" name="address2" id="address2" value="<?php echo $_data['address2']; ?>" placeholder="Additional address info..." class="form-control">
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Language</label>
                          <select class="form-control select2" id="language" name="language" required>
                          <?php echo $languages; ?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Timezone</label>
                          <select class="form-control select2" id="timezone" name="timezone" required>
                          <?php echo $timezones; ?>
                          </select>
                        </div>
                    </div>

                </div>
                
                <div class="row">

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Subscription Type</label>
                          <select class="form-control select2" id="subscription_type" name="subscription_type" required>
                          <?php echo $subscription_types; ?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Subs. date</label>
                          <input type="text" name="subscription_date" id="subscription_date" value="<?php echo $subscription_date; ?>" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Last renew</label>
                          <input type="text" name="last_renew" id="last_renew" value="<?php echo $last_renew; ?>" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Expiry date</label>
                          <input type="text" name="expiry_date" id="expiry_date" value="<?php echo $expiry_date; ?>" class="form-control">
                        </div>
                    </div>


                </div>
                
                <div class="row">
                    
                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Payment method</label>
                          <select class="form-control select2" id="payment_method" name="payment_method" required>
                          <?php echo $payment_methods; ?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Avatar</label>
                          <select class="form-control select2" id="avatar" name="avatar" >
                          <?php echo $avatar_options; ?>
                          </select>
                        </div>
                    </div>

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Checked </label>
                          <div>
                              <input type="hidden" name="checked" id="checked" value="0">
                              <input type="checkbox" name="checked" id="checked" value="1" <?php echo $checked; ?>>&nbsp;
                              <small>Check if user has confirmed his email or is trusted</small>
                          </div>
                        </div>
                    </div>
                    
                </div>
                
                <?php if(!empty($_record)){ ?>
                <div class="row">
                    <div class="col-md-6">        

                        <div class="box box-info collapsed-box">
                            <div class="box-header with-border">
                            	<h3 class="box-title">Pagine permesse</h3>
                            	<div class="box-tools pull-right">
                            		<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                            	</div><!-- /.box-tools -->
                            </div><!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                            	<?php echo $page_tab; ?>
                            </div><!-- /.box-body -->
                        </div>

                    </div>   

                    <div class="col-md-6">        

                        <div class="box box-info collapsed-box">
                            <div class="box-header with-border">
                            	<h3 class="box-title">Preferences</h3>
                            	<div class="box-tools pull-right">
                            		<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                            	</div><!-- /.box-tools -->
                            </div><!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                            	<?php echo $user_preferences; ?>
                            </div><!-- /.box-body -->
                        </div>

                    </div>                    
                                     
                </div>
                <?php } ?>
                
                              
             </div> <!--box-body end-->
             
             <div class="box-footer">
             	<?php if($_action == "insert" or $_action == "update"){ ?>
                <button name="save" value="stay" data-after="stay"  class="saveRecordBtn btn btn-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $_t->get('save'); ?></button>&nbsp;              
                <button name="save" value="close" data-after="close" class="saveRecordBtn btn btn-success"><i class="fa fa-times"></i>&nbsp;&nbsp;<?php echo $_t->get('save-close'); ?></button>&nbsp;             
                <button name="save" value="new" data-after="new" class="saveRecordBtn btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo $_t->get('save-new'); ?></button>&nbsp;
                <?php } ?>
                <button id="cancelRecordBtn" class="btn btn-danger"><i class="fa fa-times"></i>&nbsp;&nbsp;<?php echo $_t->get('cancel'); ?></button>                
             </div> <!--box-footer end-->
		</form>

	</div> <!--box end-->

<?php
$_output = ob_get_contents();
ob_end_clean();

?>