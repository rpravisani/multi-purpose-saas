<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: user-list.php                       ***
 *****************************************************/
ob_start();
?>
      <div class="box">
        <div class="box-header with-border">
        <?php echo $bootstrap->newButton("new-user", $_modpid, "new-user"); ?>
        <div class="pull-right">
	        <form id="tabfilter">
                <label>Subscription Type</label>&nbsp;
                <select name="filter_subscription" id="filter_subscription">
                    <?php
                    foreach($subscription_types as $stid=>$subscription_type){
						$select = ($_filter['subscription'] == $stid) ? "selected" : "";
                        echo "<option ".$select." value=\"".$stid."\">".$subscription_type."</option>\n";
                    }
                    ?>
                </select><br>
	        </form>
        </div>
        </div><!-- /.box-header -->
        <?php // echo $qrydbg; ?>
 		<div class="box-body table-responsive pad">
			<?php echo $table; ?>
        </div>
		<div class="box-footer clearfix">
        <?php echo $bootstrap->newButton("new-user", $_modpid, "new-user"); ?>
        </div>

     </div>

<?php
$_output = ob_get_contents();
ob_end_clean();

?>