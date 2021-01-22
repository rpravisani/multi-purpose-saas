<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: __template.php                      ***
 *** Use this as a starting point for              ***
 *** edit / insert modules                         ***
 *****************************************************/

ob_start();
?>
    <div class="box">
        
        <div class="box-header with-border">
	        <i class="fa fa-edit"></i>
        	<h3 class="box-title">Box Title</h3>
        </div> <!--box-header end-->
        
        <form class="nosend" id="<?php echo $page['file_name']; ?>-form">
                
            <div class="box-body">
            
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-6">
        
                        <p>left side</p>
        
                    </div>
                    
                    <!-- right column -->
                    <div class="col-md-6">
        
                        <p>right side</p>
        
                    </div>
                    
                </div>
                
             </div> <!--box-body end-->
         
             <div class="box-footer">
                    <button class="btn btn-success"><i class="fa fa-floppy-o"></i>&nbsp;&nbsp;Salva</button>&nbsp;              
                    <button class="btn btn-success"><i class="fa fa-times"></i>&nbsp;&nbsp;Salva & chiudi</button>&nbsp;             
                    <button class="btn btn-success"><i class="fa fa-file-o"></i>&nbsp;&nbsp;Salva & nuovo</button>                
             </div> <!--box footer end-->
         
		</form>

	</div> <!--box end-->

<?php
$_output = ob_get_contents();
ob_end_clean();

?>