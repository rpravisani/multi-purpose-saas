<?php
/*****************************************************
 *** VIEW                                          ***
 *****************************************************/

ob_start();
?>
    <div class="box">
        
        <?php echo $bootstrap->moduleBoxHeader(true); // switch copy button to true use global vars for all others ?>
        
            <div class="box-body">
            	
                <div class="row">
                    <div class="col-md-5">        
                    	<?php echo $bootstrap->inputText($arg_nome); ?>                              
                    </div>                    
                    <div class="col-md-5">        
                    	<?php echo $bootstrap->inputText($arg_cognome); ?>                              
                    </div>                    
                </div>
                <div class="row">
                    <div class="col-md-3">        
                    	<?php echo $bootstrap->inputText($arg_username); ?>                              
                    </div>                    
                    <div class="col-md-4">        
                    	<?php echo $bootstrap->inputText($arg_password); ?>                              
                    </div>                    
                    <div class="col-md-2">        
                    	<?php echo $bootstrap->select2($arg_ruolo); ?><br>
						
                    </div>                    
                    <div class="col-md-3">   
						<label>Descrizione ruolo</label><br>
                    	<small id="didascalia_tipo_utente"><?php echo $didascalia_tipo_utente; ?></small>                              
                    </div>                    
                </div>

                <div class="row">
                    <div class="col-md-4">        
                    	<?php echo $bootstrap->inputEmail($arg_email); ?>                              
                    </div>                    
                    <div class="col-md-4">        
                    	<?php echo $bootstrap->inputText($arg_tel); ?>                              
                    </div>                    
                </div>

             </div> <!--box-body end-->
             
			<?php echo $bootstrap->moduleBoxFooter(); // use all global vars ?>

	</div> <!--box end-->

<?php
$_output = ob_get_contents();
ob_end_clean();

?>