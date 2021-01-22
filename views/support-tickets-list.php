<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: elenco-automezzo.php                ***
 *** Elenco degli automezzi (ambulanze e non)      ***
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>