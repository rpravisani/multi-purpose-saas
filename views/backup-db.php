<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: elenco-impianti-clienti.php         ***
 *****************************************************/

ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <button class="backupbtn btn btn-success"><i class='fa fa-database'></i>&nbsp;Create backup</button>
        </div><!-- /.box-header -->
 		<div class="box-body table-responsive pad">
			
			
			<table width="100%" cellspacing="1" cellpadding="5" class="table table-bordered datatable" id="table-backups">
				<thead>
					<tr>
						<th class=' nosort nosearch'>&nbsp;</th>
						<th class=' sortme desc'>Ultima modifica</th>
						<th>File</th>
						<th>Dim.</th>
						<th class=' nosort nosearch'>Del.</th>
					</tr>
				</thead>
				<?php echo $tbody; ?>
			</table>
			
			
        </div>
		<div class="box-footer clearfix">
        <button class="backupbtn btn btn-success"><i class='fa fa-database'></i>&nbsp;Create backup</button>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>