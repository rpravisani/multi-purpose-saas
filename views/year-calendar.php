<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: elenco-militi.php                   ***
 *** Elenco dei militi                             ***
 *****************************************************/

ob_start();
?>
          
      <div class="box">
        <div class="box-header with-border">
        	<div class="row">
        		<div class="col col-md-3">
					<?php echo $bootstrap->select2("Milite", "milite", $militi_options); ?>
				</div>
			</div>
        </div><!-- /.box-header -->
		<div class="box-body table-responsive pad">
			<div id="year-calendar"></div>
        </div>
		<div class="box-footer clearfix">
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>