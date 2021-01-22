<?php
/*****************************************************
  VIEW                                          
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-utente", $_modpid, "new-utente"); ?>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("new-utente", $_modpid, "new-utente"); ?>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>