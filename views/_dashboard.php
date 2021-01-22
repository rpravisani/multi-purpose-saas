<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: dashboard.php                       ***
 *****************************************************/

ob_start();
?>
<div class="row">

	<div class="col-lg-6">
        
        <div class="box box-danger collapsed-box">
            
            <div class="box-header with-border">
            
                <i class="fa fa-car"></i>
                <h3 class="box-title">Tabella esempio</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red" title="" data-toggle="tooltip" data-original-title="<?php echo $nrecs; ?> Record"><?php echo $nrecs; ?></span>
                	<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                </div>
                
            </div> <!--box-header end-->
       
            <div class="box-body">
            
                <?php echo $tabella_esempio; ?>
    
            </div>
                    
        </div>
        
    </div>
	<?php if( $user->getSubscriptionType() === '0' and $lostt){ ?>
	<div class="col-lg-6">
        
        <div class="box box-warning collapsed-box">
            
            <div class="box-header with-border">
            
                <i class="fa fa-wrench"></i>
                <h3 class="box-title">Translations Lost</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red" title="" data-toggle="tooltip" data-original-title="<?php echo count($lostt); ?> Records"><?php echo count($lostt); ?></span>
                	<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                </div>
                
            </div> <!--box-header end-->
       
            <div class="box-body">
                
                <?php echo $tabella_lostt; ?>
    
            </div>
                    
        </div>
        
    </div>
    <?php } ?>

	<div class="col-lg-6">
        
        <div class="box box-warning collapsed-box">
            
            <div class="box-header with-border">
            
                <i class="fa fa-wrench"></i>
                <h3 class="box-title">Pod per ora vuota</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red" title="" data-toggle="tooltip" id="badgeSerraggi" data-original-title="0 Records"><?php echo 0; ?></span>
                	<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-plus"></i></button>
                </div>
                
            </div> <!--box-header end-->
       
            <div class="box-body">
                
                <?php echo $pod_vuoto; ?>
    
            </div>
                    
        </div>
        
    </div>
    
</div> 
   

<?php
$_output = ob_get_contents();
ob_end_clean();

?>