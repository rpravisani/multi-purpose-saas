<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: page-edit.php                  ***
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

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Name*</label>
                          <input type="text" name="name" id="name" value="<?php echo $_data['name']; ?>" placeholder="Name of the page..." class="form-control" required>
                        </div>
                    </div>                    

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>File name</label>
                          <input type="text" name="file_name" id="file_name" value="<?php echo $_data['file_name']; ?>" placeholder="File name without extension..." class="form-control" >
                        </div>
                    </div>                    

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Parent</label>
                          <select class="form-control select2" name="parent" id="parent">
                          <?php echo $options_parent; ?>
                          </select>
                        </div>
                    </div>                    
                    
                </div>

                <div class="row">

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Type*</label>
                          <select class="form-control select2" name="type" id="type" required>
                          <?php echo $options_type; ?>
                          </select>
                        </div>
                    </div>       
                                 
                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>View*</label>
                          <select class="form-control select2" name="pageview" id="pageview" required>
                          <?php echo $options_view; ?>
                          </select>
                        </div>
                    </div>                    

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Action</label>
                          <select class="form-control select2" name="pageaction" id="pageaction" >
                          <?php echo $options_action; ?>
                          </select>
                        </div>
                    </div>    
                                    
                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Fraction</label>
                          <div class="input-group">
			                  <span class="input-group-addon">#</span>
							  <input type="text" name="fraction" id="fraction" value="<?php echo $_data['fraction']; ?>" placeholder="Section to jump to (optional)" class="form-control" >
						  </div>
                        </div>
                    </div>       
                                                            
                </div>

                <div class="row">

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Modify page</label>
                          <select class="form-control select2" name="modify_page" id="modify_page" >
                          <?php echo $options_related; ?>
                          </select>
                        </div>
                    </div>   
                    
                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Title</label>
                          <input type="text" name="title" id="title" value="<?php echo $_data['title']; ?>" placeholder="Page title..." class="form-control" >
                        </div>
                    </div>       
                                 
                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Subtitle</label>
                          <input type="text" name="subtitle" id="subtitle" value="<?php echo $_data['subtitle']; ?>" placeholder="Page subtitle..." class="form-control" >
                        </div>
                    </div>                    
                    
                </div>

                <div class="row">

                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Icon</label>
                          <?php if(empty($fa_name_list)){ ?>
                          <input type="text" name="icon" id="icon-input" value="<?php echo $_data['icon']; ?>" placeholder="Icon class without fa-..." class="form-control" >
                          <?php }else{ ?>
                          <select class="form-control select2" name="icon" id="icon-select" >
                          <?php echo $options_icons; ?>
                          </select>
                          <?php } ?>
                        </div>
                    </div>                    

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Icon class</label>
                          <input type="text" name="icon_class" id="icon_class" value="<?php echo $_data['icon_class']; ?>" placeholder="Extra icon class..." class="form-control" >
                        </div>
                    </div>
                           
                    <div class="col-md-1">
                    	<div id="anteprima-icona">
	                    	<i class="fa fa-<?php echo $_data['icon']; ?> <?php echo $_data['icon_class']; ?> fa-2x fa-border"></i>
                        </div>
                    </div>       

                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Tag</label>
                          <input type="text" name="tag" id="tag" value="<?php echo $_data['tag']; ?>" placeholder="Label tag..." class="form-control" >
                        </div>
                    </div>       
                                 
                    <div class="col-md-3">        
                        <div class="form-group">
                          <label>Tag class</label>
                          <input type="text" name="tag_class" id="tag_class" value="<?php echo $_data['tag_class']; ?>" placeholder="Tag class..." class="form-control" >
                        </div>
                    </div>                    

                    
                </div>

                <div class="row">


                    <div class="col-md-2">        
                        <div class="form-group">
                          <label>Order*</label>
                          <input type="number" name="order" id="order" value="<?php echo $pageorder; ?>" class="form-control" required>
                        </div>
                    </div>                    

                    <div class="col-md-4">        
                        <div class="form-group">
                          <label>Home</label><br>
                          <input type="checkbox" name="home" id="home" value="1" <?php echo $checked_home; ?> >&nbsp;
                          <small>Check this to set this page as the homepage of this project</small>
                        </div>
                    </div>                    

                    <div class="col-md-6">        
                        <div class="form-group">
                          <label>Page permissions</label>
                          <div>
	                          <?php echo $checks_subscription; ?>
                          </div>

                        </div>
                    </div>   
                    
                </div>
                              
             </div> <!--box-body end-->
             
             <div class="box-footer">
                <button name="save" value="stay" data-after="stay"  class="saveRecordBtn btn btn-success"><i class="fa fa-check"></i>&nbsp;&nbsp;<?php echo $_t->get('save'); ?></button>&nbsp;              
                <button name="save" value="close" data-after="close" class="saveRecordBtn btn btn-success"><i class="fa fa-times"></i>&nbsp;&nbsp;<?php echo $_t->get('save-close'); ?></button>&nbsp;             
                <button name="save" value="new" data-after="new" class="saveRecordBtn btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;<?php echo $_t->get('save-new'); ?></button>&nbsp;
                <button id="cancelRecordBtn" class="btn btn-danger"><i class="fa fa-times"></i>&nbsp;&nbsp;<?php echo $_t->get('cancel'); ?></button>                
             </div> <!--box-footer end-->
		</form>

	</div> <!--box end-->

<?php
$_output = ob_get_contents();
ob_end_clean();

?>