<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: elenco-modelli-veicoli.php          ***
 *** test file for now                             ***
 *** small project list                            ***
 *****************************************************/
//$_output = $table;


ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php if($canadd){ ?>
	        <button class="btn btn-primary new-row" id="new-row-top"><i class="fa fa-plus"></i>&nbsp;<?php echo $_t->get("new-row"); ?></button>
	    <?php } ?>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        <?php if($canadd){ ?>
	        <button class="btn btn-primary new-row" id="new-row-top"><i class="fa fa-plus"></i>&nbsp;<?php echo $_t->get("new-row"); ?></button>
	    <?php } ?>
     	</div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>