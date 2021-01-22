<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: dashboard.php                       ***
 *** In this case as for now it allows to set      ***
 *** translations for the various active languages ***
 *****************************************************/


ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title"><?php echo $_t->get('translations') ?></h3>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
        	
            <div class="row">
              <div class="col-md-3">
                <p>     
                	<label for="language"><?php echo $_t->get('language-label') ?></label>
                    <select name="language" id="language" class="filter form-control select2 ">
                    <?php echo $languageOptions; ?>
                    </select>
                </p>    	              
              </div>
              <div class="col-md-3">
                <p>     
                	<label for="section"><?php echo $_t->get('section-label') ?></label>
                    <select name="section" id="section" class="filter form-control select2 ">
                    <?php echo $sectionOptions; ?>
                    </select>
                </p>    	
              </div>
              <div class="col-md-6"><p><br>
              <button id="add-section" class="btn btn-primary "><i class="fa fa-plus"></i>&nbsp;<?php echo $_t->get('add-section') ?></button>&nbsp;
              <button id="copy-section" class="btn btn-primary "><i class="fa fa-copy"></i>&nbsp;<?php echo $_t->get('copy-section') ?></button>
              <button id="sync-translations" class="btn btn-warning "><i class="fa fa-database"></i>&nbsp;<?php echo $_t->get('sync-translation') ?></button>
              <button id="clean-translations" class="btn btn-danger "><i class="fa fa-trash"></i>&nbsp;<?php echo $_t->get('clean-translation') ?></button>
              <button id="search-translation" class="btn btn-info "><i class="fa fa-search"></i>&nbsp;<?php echo $_t->get('search-translation') ?></button>
              </p></div>
              <div class="col-md-3"></div>
            </div>
             
			<div class="row" id="sync-interface" style="display: none;">
				<div class="col-md-8 col-md-offset-2">

					<div class="box box-warning">
						<div class="box-header with-border">
						  <h3 class="box-title">Sync translations with other DB</h3>
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
									<?php echo $bootstrap->checkbox("clearnotfound", true, "Clear not-found"); ?>       
								</div>
								<div class="col-md-3">
									<?php echo $bootstrap->select2("Languages", "languages", $languageOptions, false, false, true); ?>       
								</div>
								<div class="col-md-3"><br>
									<button id="syncnow" class="btn btn-primary" disabled ><i class="fa fa-refresh "></i>&nbsp;Sync now</button>
								</div>
							</div>
						</div>
					</div>
				
				
				</div>
			</div>


	
	
                            
            <div class="row bg-success" id="search" <?php echo $showsearch; ?>>
            	<div class="col-md-6 col-md-offset-3 " style=" padding-top: 8px; padding-bottom: 8px;">
                	<div class="input-group">
                    	<div class="input-group-addon" ><i class="fa fa-search"></i></div>
                		<input type="text" class="form-control" value="<?php echo $search; ?>" id="search-field" name="searchfield">
                    </div>
                </div>
            </div>
            <br>
                     
             <form id="translations-table" name="translations-table">                   
            	<?php echo $table; ?>
            </form>
        </div>
		<div class="box-footer clearfix">
            <button id="add-row" class="btn btn-primary "><i class="fa fa-plus"></i>&nbsp;<?php echo $_t->get('add-row') ?></button>&nbsp;<button class="btn btn-success pull-right" id="save"><i class="fa fa-check"></i>&nbsp;<?php echo $_t->get('save') ?></button>
        </div>

     </div>
     

<?php
$_output = ob_get_contents();
ob_end_clean();
?>