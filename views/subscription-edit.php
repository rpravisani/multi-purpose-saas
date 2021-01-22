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

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Name</label>
                          <input type="text" name="name" id="name" value="<?php echo $_data['name']; ?>" placeholder="Name of the subscription..." class="form-control" required>
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Monthly cost</label>
                          <input type="text" name="monthly_cost" id="monthly_cost" value="<?php echo $_data['monthly_cost']; ?>" placeholder="The cost of the subscription..." class="form-control">
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Subscription length</label>
                          <input type="text" name="length" id="length" value="<?php echo $_data['length']; ?>" placeholder="Lenght in days" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Level</label>
                          <input type="number" name="level" id="level" value="<?php echo $_data['level']; ?>" placeholder="The level of permission..." class="form-control" required>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-12">        
                        <div class="form-group">
                          <label>Description</label>
                          <textarea class="form-control" placeholder="A description of the subscription..." rows="5" name="description" id="description"><?php echo $_data['description']; ?></textarea>
                        </div>
                    </div>

                </div>

                
                <?php if(!empty($_record)){ ?>
                <div class="row">
                    <div class="col-md-4">        

                        <div class="box box-info collapsed-box">
                            <div class="box-header with-border">
                            	<h3 class="box-title">Parameters</h3>
                            	<div class="box-tools pull-right">
                            		<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                            	</div><!-- /.box-tools -->
                            </div><!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                            	<?php echo $subscription_params; ?>
                            </div><!-- /.box-body -->
                        </div>

                    </div>                    

                    <div class="col-md-8">        

                        <div class="box box-info collapsed-box">
                            <div class="box-header with-border">
                            	<h3 class="box-title">Page permissions </h3>
                            	<div class="box-tools pull-right">
                            		<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                            	</div><!-- /.box-tools -->
                            </div><!-- /.box-header -->
                            <div class="box-body" style="display: none;">
                            	<?php echo $page_tab; ?>
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