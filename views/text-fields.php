<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: text-fields.php                   ***
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-field", $_modpid, "new-field"); ?>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("new-field", $_modpid, "new-field"); ?>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>