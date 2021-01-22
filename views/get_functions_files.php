<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: elenco-agenti.php                   ***
 *****************************************************/

ob_start();
?>
      <div class="box">
 		<div class="box-body table-responsive pad">
			<h4>PATH: <strong><?php echo $path; ?></strong></h4>
			<?php echo $functions_list; ?>
        </div>
		<div class="box-footer clearfix">
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>