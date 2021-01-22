<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: test-wizard.php                     ***
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-agente", $_modpid, "new-agente"); ?>
        </div><!-- /.box-header -->
 		<div class="box-body pad wizard">
			
            <div class="step" data-step="1">
            	<h3>TIPO USCITA <span class='chosen-option'></span></h3>
                
                <div class="row">
                
				<?php foreach($tipo_uscite as $tid => $tnome){ ?>
                    <div class="col-md-4">        
                        <div data-step='1' data-option='tipo_uscita' data-value='<?php echo $tid ?>' data-next='2' class="step-1 <?php echo strtolower(str_replace(" ", "-", $tnome) ); ?> step-option ">
                            <h1><?php echo strtoupper($tnome); ?></h1>
                        </div>
                    </div>                    
                <?php } ?>
                    
                </div>

            </div>

            <div class="step" data-step="2">
            	<h3>AUTOMEZZO <span class='chosen-option'></span></h3>
                
                <div class="row">

				<?php foreach($automezzi as $wrow){ ?>
                    <div class="col-md-4">        
                        <div data-step='2' data-option='automezzo' data-value='<?php echo $wrow['id'] ?>' data-next='3' class="step-2 <?php echo ($wrow['strade_strette']) ? "strette" : ""; ?> step-option ">
                            <h1><?php echo strtoupper($wrow['codice']); ?></h1>
                            <p><?php echo ($wrow['strade_strette']) ? "Per strade strette" : "&nbsp;"; ?></p>
                        </div>
                    </div>                    
                <?php } ?>

                    
                </div>

            </div>

            <div class="step" data-step="3">
            
            	<h3>AUTISTA <span class='chosen-option'></span></h3>
                
                <div class="row">

				<?php foreach($autisti as $tid => $tnome){ ?>
                    <div class="col-md-4">        
                        <div data-step='3' data-option='autista' data-value='<?php echo $tid ?>' data-next='4' class="step-3 <?php echo strtolower($tnome); ?> step-option ">
                            <h1><?php echo strtoupper($tnome); ?></h1>
                        </div>
                    </div>                    
                <?php } ?>
                    
                </div>

            </div>

            <div class="step" data-step="4">
            
            	<h3>CAPOSQUADRA <span class='chosen-option'></span></h3>
                
                <div class="row">

				<?php foreach($caposquadra as $tid => $tnome){ ?>
                    <div class="col-md-4">        
                        <div data-step='4' data-option='caposquadra' data-value='<?php echo $tid ?>' data-next='5' class="step-4 <?php echo strtolower($tnome); ?> step-option ">
                            <h1><?php echo strtoupper($tnome); ?></h1>
                        </div>
                    </div>                    
                <?php } ?>                   

                </div>

                <div class="row">

                    <div class="col-md-6 col-md-offset-3">        
                        <div data-step='4' data-option='caposquadra' data-value='' data-next='5' class="step-4 salta step-option ">
                        	<h1>SALTA</h1>
                        </div>
                    </div>                    
                    
                </div>

            </div>

            <div class="step" data-step="5">
            
            	<h3>MILITE 1 <span class='chosen-option'></span></h3>
                
                <div class="row">

				<?php foreach($militi as $tid => $tnome){ ?>
                    <div class="col-md-4">        
                        <div data-step='5' data-option='milite1' data-value='<?php echo $tid ?>' data-next='6' class="step-5 <?php echo strtolower($tnome); ?> step-option ">
                            <h1><?php echo strtoupper($tnome); ?></h1>
                        </div>
                    </div>                    
                <?php } ?>   

                </div>
                
                <div class="row">
                                
                    <div class="col-md-6 col-md-offset-3">        
                        <div class="step-5 scegli step-option ">
                        	<h1>SCEGLI...</h1>                      
                        </div>
                    </div>                    

                    
                </div>

            </div>

            <div class="step" data-step="6">
            
            	<h3>MILITE 2 <span class='chosen-option'></span></h3>
                
                <div class="row">

				<?php foreach($militi as $tid => $tnome){ ?>
                    <div class="col-md-4">        
                        <div data-step='6' data-option='milite2' data-value='<?php echo $tid ?>' data-next='7' class="step-6 <?php echo strtolower($tnome); ?> step-option ">
                            <h1><?php echo strtoupper($tnome); ?></h1>
                        </div>
                    </div>                    
                <?php } ?>   

                </div>

                <div class="row">

                    <div class="col-md-6">        
                        <div class="step-6 scegli step-option ">
                        	<h1>SCEGLI...</h1>                      
                        </div>
                    </div>                    

                    <div class="col-md-6">        
                        <div data-step='6' data-option='milite2' data-value='' data-next='7' class="step-6 salta step-option ">
                        	<h1>SALTA</h1>
                        </div>
                    </div>                    
                    
                </div>

            </div>
            
            <div class="step" data-step="7">
            
            	<h3>CONFERMA</h3>
                
                <div class="row">

                    <div class="col-md-8 col-md-offset-2" style="margin-bottom: 20px">        
                        <div id="result">
                        </div>
                    </div>                    

                    <div class="col-md-6 col-md-offset-3">        
                        <div  data-step='7'  data-next='8' class="step-7 conferma step-option ">
                        	<h1>CONFERMA INSERIMENTO</h1>
                            <p>Controlla i selezionati qua sopra</p>
                        </div>
                    </div>                    

                    
                </div>

            </div>
            
           
                
            <div class="row" id="fine-wizard" style="display: none">

                <div class="col-md-8 col-md-offset-2" style="margin-bottom: 20px">
                	<h3>Fono salvato con successo!</h3>
                </div>                    

                <div class="col-md-6 col-md-offset-3">        
                    <div class="fono">
                        <p>Numero Fono:</p>
                        <h1><?php echo date("y", time())."/0003"; ?></h1>
                    </div>
                </div>                    

                
            </div>

           
            
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("new-agente", $_modpid, "new-agente"); ?>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

// $_output = "view: ".$_view."<br>action: ".$_action."<br>\ntype: ".$_type;
?>