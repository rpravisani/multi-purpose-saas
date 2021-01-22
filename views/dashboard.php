<?php
/*****************************************************
 *** VIEW                                          ***
 *** filename: dashboard.php                       ***
 *****************************************************/

ob_start();
?>

<?php if( $_user->getSubscriptionType() == '0'){ ?>

<!-- SUPERADMIN ONLY ----------------------------------------------------------------------------->
<div class="row">
	<?php if( !empty($tickets_open)){ ?>

	<div class="col-md-6">
        <div class="box box-warning ">
            
            <div class="box-header with-border">
            
                <i class="fa fa-ticket"></i>
                <h3 class="box-title">Open or Pending Tickets</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red" title="<?php echo count($tickets_open); ?> Tickets" data-toggle="tooltip" ><?php echo count($tickets_open); ?></span>
                	<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-minus"></i></button>
                </div>
                
            </div> <!--box-header end-->
       
            <div class="box-body">
                
                <?php echo $tabella_ticket; ?>
    
            </div>
                    
        </div>
	</div>
	<?php } ?>
	<?php if($lostt){ ?>
	<div class="col-md-6">

        <div class="box box-warning collapsed-box">
            
            <div class="box-header with-border">
            
                <i class="fa fa-language"></i>
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
	
	<?php } // END IF lostt ?>
				
</div>
        
<?php } // END IF superadmin ?>

<!--Ticket utente aperti ------------------------------------------------------------------------>
<?php if( $_user->getSubscriptionType() != '0' and !empty($tickets_user_open)){ ?>

<div class="row">

	<div class="col-lg-6">
        <div class="box box-warning " id="ticket-box">
            
            <div class="box-header with-border">
            
                <i class="fa fa-ticket"></i>
                <h3 class="box-title">Richieste assistenza aperte</h3>
                <div class="box-tools pull-right">
                    <span class="badge bg-red" title="<?php echo count($tickets_user_open); ?> richieste" data-toggle="tooltip" ><?php echo count($tickets_user_open); ?></span>
                	<button data-widget="collapse" class="btn btn-box-tool"><i class="fa fa-minus"></i></button>
                </div>
                
            </div> <!--box-header end-->
       
            <div class="box-body">
                
                <?php echo $tabella_ticket_user; ?>
    
            </div>
                    
        </div>
	</div>

	<div class="col-lg-6" id="chat-room">

        
	</div>
		
</div>

<?php } ?>                  
<!-- Fine Ticket utente aperti ------------------------------------------------------------------------>


<div class="row">

    <?php if(!$sono_cliente){ ?>
    <div class="col-md-3 col-sm-6 col-xs-12">
    
      <div class="info-box">
        <span class="info-box-icon bg-blue"><i class="fa fa-user"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Numero Totale Clienti</span>
          <span class="info-box-number"><?php echo $tot_numero_clienti; ?></span>
        </div><!-- /.info-box-content -->
      </div><!-- /.info-box -->
                
    </div>   
    <?php } ?>
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        
        
      <div class="info-box">
        <span class="info-box-icon bg-fuchsia"><i class="fa fa-folder"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Numero Totale Progetti</span>
          <span class="info-box-number"><?php echo $tot_numero_progetti; ?></span>
        </div><!-- /.info-box-content -->
      </div><!-- /.info-box -->
                
    </div>    
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        
      <div class="info-box">
        <span class="info-box-icon bg-purple"><i class="fa fa-clipboard"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Numero Totale Report</span>
          <span class="info-box-number"><?php echo $tot_numero_report; ?></span>
        </div><!-- /.info-box-content -->
      </div><!-- /.info-box -->
        
    </div>    
    
    <div class="col-md-3 col-sm-6 col-xs-12">
        
      <div class="info-box">
        <span class="info-box-icon bg-teal"><i class="fa fa-tasks"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Numero Totale Task</span>
          <span class="info-box-number"><?php echo $tot_numero_task; ?></span>
        </div><!-- /.info-box-content -->
      </div><!-- /.info-box -->
        
    </div>    
    
    
</div>

<div class="row">
        
    <div class="col-md-6">
        
              <!-- DONUT CHART -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Stati report</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div>
                <div class="box-body">
                    <canvas id="pieChart" style="height:250px"></canvas>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
        
        
    </div>
    
    <div class="col-md-6">
        
              <!-- BAR CHART -->
              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title">Tasks per tipologia</h3>
                  <div class="box-tools pull-right">
                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="chart">
                    <canvas id="barChart" style="height:230px"></canvas>
                  </div>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
        
        
    </div>
</div>


<script type="text/javascript">

    var reportTypeLabels = ["<?php echo $report_type_labels_flat; ?>"];
    var reportTypeValues = [<?php echo $report_type_values_flat; ?>];
    var reportTypeColors = [<?php echo $report_type_colors_flat; ?>];

    var taskLabels = ["<?php echo $task_labels_flat; ?>"];
    var taskValues = [<?php echo $task_numbers_flat; ?>];
    var taskColors = ["<?php echo $task_colors_flat; ?>"];
   
</script>




<?php
$_output = ob_get_contents();
ob_end_clean();

?>