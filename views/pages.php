<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: gestione-gommista.php               ***
 *** TODO                                          ***
 *****************************************************/

ob_start();
?>
     
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-page", $_modpid, "new-page"); ?>
	        <div class="pull-right">
	        	<button id='import-pages' class='btn btn-warning'><i class='fa fa-download'></i>&nbsp;Import Pages</button>
	        	<button id='clean-pages' title="Clean files" data-toggle="tooltip" class='btn btn-danger'><i class='fa fa-shower'></i></button>
			</div>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
		<div class="row" id="import-interface" style="display: none;">
			<div class="col-md-8 col-md-offset-2">

				<div class="box box-warning">
					<div class="box-header with-border">
					  <h3 class="box-title">Copy pages other DB</h3>
					</div><!-- /.box-header -->
					<div class="box-body">
						<div class="row">
							<div class="col-md-3">
								<?php echo $bootstrap->inputText("User", "user", DB_USER, "Username source DB..."); ?>       
							</div>
							<div class="col-md-3">
								<?php echo $bootstrap->inputText("Password", "pwd", DB_PWD, "Password source DB..."); ?>       
							</div>
							<div class="col-md-3">
								<?php echo $bootstrap->inputText("Host", "host", DB_HOST, "Host source DB..."); ?>       
							</div>
							<div class="col-md-3"><br>
								<button id="connect-db" class="btn btn-danger"><i class="fa fa-plug"></i>&nbsp;Connect</button>
							</div>
						</div>
						<div class="row">
							<div class="col-md-3">
								<?php echo $bootstrap->select2("Database", "db", ""); ?>       
							</div>
							<div class="col-md-3"><br>
								<?php echo $bootstrap->checkbox("systemonly", true, "System pages only"); ?>       
							</div>
							<div class="col-md-3"><br>
								<button id="getpages" class="btn btn-primary" disabled ><i class="fa fa-download "></i>&nbsp;Get Page List</button>
							</div>
						</div>
					</div>
				</div>


			</div>
		</div>
			<?php echo $tabella; ?>
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("newpage", $_modpid, "new-page"); ?>
        </div>

     </div>

          

<?php
$_output = ob_get_contents();
ob_end_clean();

?>