<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: gestione-milite.php                 ***
 *****************************************************/

ob_start();
?>
    <div class="box">
        
        <?php echo $bootstrap->moduleBoxHeader(false, $boxtitle, "", $_pagename, $_record, $pid, $action); // switch copy button to true use global vars for all others ?>
        
            <div class="box-body">
                       	
                        	
                <div class="row">
                    <div class="col-md-3">
                    	<?php echo $bootstrap->inputText("Date / Time ticket", "date", $datum, "", false, false, true, false, 0, false, "<i class='fa fa-clock-o'></i>", true); ?>       
                    </div>  
                    <div class="col-md-3">        
                    	<?php echo $bootstrap->inputText("Page", "page", $_data['pagename'], "", false, false, true, false, 0, false, "ID: ".$_data['pid'], true); ?>       
                    </div>  
                    <div class="col-md-6">

						<div class="form-group">
							<label>Url</label>
							<div class="input-group">
								<input type="text" name="url" id="gotourl" value="<?php echo $pageurl; ?>" class="form-control " readonly>
								<span data-toggle="tooltip" data-placement="left" title="Goto the page"  class="input-group-addon gotourl-btn"><i class="fa fa-globe"></i></span>
							</div>
						</div>						
                    </div>  
                                      
                </div>

                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
						<label>Screenshot</label>
                   		<?php echo $img; ?>
                    </div>  
                    <div class="col-md-4">                         
                    	<?php echo $bootstrap->textarea("Message", "message", $_data['message'], "", 9, false, false, true, false, false, ""); ?>
                    </div> 
                     
                    <div class="col-md-4" style="margin-top: 24px;"> 
                         
						<div class="small-box bg-aqua">
							<div class="inner">
								<h3><?php echo $username ?></h3>
								<p>User agent:<br><strong><?php echo $_data['user_agent'] ?></strong></p>
							</div>
							<div class="icon">
								<i class="ion ion-person"></i>
							</div>
							<a href="#" class="small-box-footer"><em>IP:<?php echo $_data['ip'] ?></em></a>
						</div>
								
					</div>  
                                      
                </div>
               
                <div class="row">
                
                    <div class="col-md-6">
                    	<div class="row">
                    	
							<div class="col-md-6"> 
								<div class="form-group">
									<label>State</label><br>
									<select name='state' id='stato' class='select2 form-control'>
										<?php echo $states; ?>
									</select>
								</div>
							</div>  
							<div class="col-md-6"> 
								<?php echo $bootstrap->datepicker("Update date/time", "date_update", $datum_update); ?> 
								<?php echo $bootstrap->timepicker(false, "time_update", $time_update, true); ?> 
								<em>Last update: <strong class='updatetime'><?php echo $datum_last_update ?></strong></em>
							</div>  
							<div class="col-md-12">
								<?php echo $bootstrap->textarea("End Solution", "solution", $_data['solution'], "How did you resolve this problem...?", 5, false, 1, false, false, false, ""); ?>       
							</div>  
                    	
                    	
						</div>
					</div> 
					
                    <!-- Start Chat / Replies-->
                    <div class="col-md-6">
                    
						<div class="box box-warning direct-chat direct-chat-warning">
							<div class="box-header with-border">
								<h3 class="box-title">Replies</h3>
								<div class="box-tools pull-right">
									<span data-toggle="tooltip" title="<?php echo $num_new_messages; ?> New Messages" class="badge bg-yellow"><?php echo $num_new_messages; ?></span>
									<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
								</div>
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<!-- Conversations are loaded here -->
								<div class="direct-chat-messages">
								
								<?php echo $reply_html; ?>


								</div>
								<!--/.direct-chat-messages-->

								<!-- /.direct-chat-pane -->
							</div>
							<!-- /.box-body -->
							<div class="box-footer">
								<div class="input-group">
									<input data-ticket="<?php echo $_record; ?>" data-user="<?php echo $_SESSION['login_id']; ?>" id="reply-message" placeholder="Type Message ..." class="form-control" type="text">
									<span class="input-group-btn">
										<button id="send-reply" type="button" class="btn btn-warning btn-flat">Reply</button>
									</span>
								</div>
							</div>
							<!-- /.box-footer-->
						</div>
                    
                    
                    

					</div>
                    <!-- End Chat / Replies-->
					
				</div> 
                                      
                                      
             </div> <!--box-body end-->
             
		<?php echo $bootstrap->moduleBoxFooter("", "", true, true, false, true); // use all global vars ?>

	</div> <!--box end-->
	

<?php
$_output = ob_get_contents();
ob_end_clean();

?>