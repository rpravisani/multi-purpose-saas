<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: user-list.php                       ***
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-subscr", $_modpid, "new-subscr"); ?>

        </div><!-- /.box-header -->
        <?php // echo $qrydbg; ?>
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("new-subscr", $_modpid, "new-subscr"); ?>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>