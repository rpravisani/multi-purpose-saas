<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: gestione-cliente.php                ***
 *****************************************************/

ob_start();
?>
    <div class="box">
        
        <?php echo $bootstrap->moduleBoxHeader(true); // switch copy button to true use global vars for all others ?>
        
            <div class="box-body">
            	
                <div class="row">
                    <div class="col-md-6">        
                    	<?php echo $bootstrap->inputText("Label", "label", $_data['label'], "Text field label...", true, 1); ?>                              
                    	<?php echo $bootstrap->inputText("Name", "name", $_data['name'], "Name of field", true, 2); ?>                              
                    	<?php echo $bootstrap->checkbox("html", $_data['html'], "Check if field is HTML"); ?>                              
					</div>                    
                    <div class="col-md-6">        
                    	<?php echo $bootstrap->textarea("Description", "description", $_data['description'], "Description of filed...", 7, true, 3); ?>                              
                    </div>                    

				</div>

                
             </div> <!--box-body end-->
             
			<?php echo $bootstrap->moduleBoxFooter(); // use all global vars ?>

	</div> <!--box end-->
	
<?php
$_output = ob_get_contents();
ob_end_clean();

?>