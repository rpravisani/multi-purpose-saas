<?php
/*********************************************
 *** filename: upload.php                  ***
 *********************************************/

ob_start();
?>
	<div class="row">
    	<div class="col-md-6 col-md-offset-3" style="margin-top: 10vh">
        
            <div class="box box-primary">
                
                <div class="box-header with-border">
                    <i class="fa fa-upload"></i>
                    <h3 class="box-title">Carica file...</h3>            
                </div> <!--box-header end-->
                
                <form enctype="multipart/form-data" class="" id="<?php echo $page['file_name']; ?>-form" action="calls/upload.php" method="post">
                    <input type="hidden" name="pid" id="pid" value="<?php echo $pid; ?>" />
                    <input type="hidden" name="cliente" id="cliente" value="1" />
                    <input type="hidden" name="rename" id="rename" value="1" />        
                
                    <div class="box-body">
                        
                        <div class="row">
                            <!-- left column -->
                            <div class="col-md-12">
                                
                                <div class="form-group">
                                    <label for="uploadfile">Seleziona file da caricare</label>
                                    <input required accept=".xls" type="file" name="uploadfile" id="uploadfile">
                                    <p class="help-block"><?php echo $page['subtitle'] ?></p>
                                </div>    
                                    
                            </div>                    
                        </div>
                                      
                     </div> <!--box-body end-->
                     
                     <div class="box-footer">
                        <button name="save" value="upload" class="btn btn-success"><i class="fa fa-upload"></i>&nbsp;&nbsp;Carica</button>&nbsp;              
                     </div> <!--box-footer end-->
                </form>
        
            </div> <!--box end-->
        
        
        </div>
    </div>
    
    
<?php
$_output = ob_get_contents();
ob_end_clean();

?>