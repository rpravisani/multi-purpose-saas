<?php

/***
SCRIPT PER SVUOTARE TABELLE E CREARE UN INSTALL VUOTO
***/

session_start();

include_once 'required/variables.php';
include_once 'required/functions.php';
include_once 'required/classes/cc_mysqli.class.php';

$db = new cc_dbconnect(DB_NAME);

$colclass= "4";

/*** TABELLE DA SVUOTARE ***/
$truncate = array(
	"logs_access",
	"logs_email",
	"logs_error",
	"logs_login_attempts",
	"logs_subscription",
	"logs_tickets",
	"reports",
	"tickets",
	"tickets_followups",
	"tokens"
);

$truncate_table = "<table id='truncate' class='table table-bordered table-sm'>\n<tbody>";
foreach($truncate as $tab){
	
	$table_info = $db->get_table_info($tab);
	if(!$tab) continue;
	
	$nrecs = $db->count_rows($tab);	
	
	$check = "<input type='checkbox' checked value='".$tab."' id='truncate_".$tab."'>";
	$truncate_table .= "<tr>";
	$truncate_table .= "<td align='center'>".$check."</td>";
	$truncate_table .= "<td>".$tab."</td>";
	$truncate_table .= "<td class='result'>".$nrecs." recs.</td>";
	$truncate_table .= "</tr>\n";
}

$truncate_table .= "</tbody>\n</table>\n";


/*** DATA TABLES DA ELIMINARE ***/
$data_tables = $db->getTablenames ('data_');

if($data_tables){
	
	$colclass= "3";
	
	$data_tables_table = "<table id='data-tables' class='table table-bordered table-sm'>\n";
	$data_tables_table .= "<thead>\n";
	$data_tables_table .= "<tr><td>Del.</td><td>Trunc.</td><td></td><td></td></tr>\n";
	$data_tables_table .= "</thead>\n";
	$data_tables_table .= "<tbody>\n";
	
	
	foreach($data_tables as $data_table){
		
		$nrecs = $db->count_rows($data_table);	
		
		$drop_check  = "<input type='checkbox' class='dt_drop' checked value='".$data_table."' id='dt_".$data_table."'>";
		$trunc_check = "<input type='checkbox' class='dt_trunc' value='".$data_table."' id='truncate_".$data_table."'>";
		
		$data_tables_table .= "<tr>";
		$data_tables_table .= "<td align='center'>".$drop_check."</td>";
		$data_tables_table .= "<td align='center'>".$trunc_check."</td>";
		$data_tables_table .= "<td>".$data_table."</td>";
		$data_tables_table .= "<td class='result'>".$nrecs." recs.</td>";
		$data_tables_table .= "</tr>\n";
	}
	
	$data_tables_table .= "</tbody>\n</table>\n";
		
}


/*** CARTELLE DA SVUOTARE ***/
$empty = array(
	"backup",
	"cache",
	"csv",
	"logs",
	"photo",
	"reports",
	"screenshots",
	"uploads",
	"xls"	
);

$dir_table = "<table id='dirs' class='table table-bordered table-sm'>\n<tbody>";
foreach($empty as $dir){
	
	$path = FILEROOT.$dir."/";

	$filecount = 0;
	$files = glob($path . "*");
	if ($files){

		$filecount = count($files);
	}
	
	$check = "<input type='checkbox' checked value='".$dir."' id='dir_".$dir."'>";
	$dir_table .= "<tr>";
	$dir_table .= "<td align='center'>".$check."</td>";
	$dir_table .= "<td>".$dir."</td>";
	$dir_table .= "<td class='result'>".$filecount." files</td>";
	$dir_table .= "</tr>\n";
}

$dir_table .= "</tbody>\n</table>\n";


/*** PAGINE DA ELIMINARE ***/
$page_table = "";

getPages(0);

function getPages($parent = 0, $level = 0){
	
	global $page_table, $db;
	$pages = $db->select_all("pages", "WHERE system_page = 0 AND parent = '".$parent."' ORDER BY `order`");
	if($pages){
		foreach($pages as $page){
			$check = "<input type='checkbox' value='".$page['id']."' id='page_".$page['id']."'>";
			$page_name = $page['name'];
			if(!empty($page['file_name'])) $page_name .= " <small>(".$page['file_name'].") ".$level."</small>";
			$prefix = str_repeat("-", $level);
			
			$page_name = $prefix.$page_name;
			$page_table .= "<tr>";
			$page_table .= "<td align='center'>".$check."</td>";
			$page_table .= "<td>".$page_name."</td>";
			$page_table .= "<td class='result'></td>";
			$page_table .= "</tr>\n";
			$next_level = $level + 1;
			getPages($page['id'], $next_level); // richiamo a prescindere, intanto se non trova nulla salta
		} // end foreach

	}// end if pages
	
	return true;
	
}

$page_table = "<table id='pages' class='table table-bordered table-sm'>\n<tbody>".$page_table."</tbody>\n</table>\n";

/*** TABELLA DATA DA ELIMINARE ***/
$data_tables = "";


?>


<!doctype html>
<html lang="it">
  <head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Create new clean installation of framework</title>
	<!-- Bootstrap 4.1.1 -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<!-- Font Awesome 5 -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

	<style type="text/css">

	/* ----------------------------------------------------------------
		MEDIA QUERIES
	-----------------------------------------------------------------*/

	/* < 1200px */
	@media (max-width: 1199px) {

	}

	/* > 991px < 1200px */
	@media (min-width: 992px) and (max-width: 1199px) {

	}

	/* < 992px */
	@media (max-width: 991px) {

	}

	/* > 767px < 992px */
	@media (min-width: 768px) and (max-width: 991px) {
	}

	/* < 768px */
	@media (max-width: 767px) {

	}

	/* > 479px < 768px */
	@media (min-width: 480px) and (max-width: 767px) {

	}

	/* < 480px */
	@media (max-width: 479px) {

	}
	  
  </style>
</head>

<body>

	<div class="container-fluid">
		
		<div class="row mt-2">
			<div class="col-md-<?php echo $colclass ?>">
				<h4>Pagine</h4>
				<?php echo $page_table; ?>
			</div>
			<div class="col-md-<?php echo $colclass ?>">
				<h4>Tabelle da svuotare</h4>
				<?php echo $truncate_table; ?>
			</div>
			<?php if(!empty($data_tables_table)){ ?>
			<div class="col-md-<?php echo $colclass ?>">
				<h4>Data Tables da cancellare</h4>
				<?php echo $data_tables_table; ?>
			</div>			
			<?php } ?>
			<div class="col-md-<?php echo $colclass ?>">
				<h4>Cartelle da svuotare</h4>
				<?php echo $dir_table; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12"><button class="btn btn-success"><i class="fa fa-check mr-2"></i>Install</button></div>
		</div>
		
	</div>	
	
	<!-- jQuery 3 -->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<!-- popper -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<!-- Bootstrap 4.1.1 -->
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

	<script type="text/javascript">
		
		$(document).ready(function() {
			
			$(document).on("click", "#data-tables input[type='checkbox']", function(){
				
				var t = $(this);
				var tr = t.closest("tr");
				var uncheck = (t.hasClass("dt_trunc")) ? "dt_drop" : "dt_trunc";
				tr.find("input."+uncheck).prop("checked", false);
				
			});
						   
			$(document).on("click", "button", function(){
				var t = $(this);
				var i = t.find("i");
				
				var checks = $('input:checkbox:checked');
				
				var nchecks = checks.length;
				
				if(nchecks){
					t.prop("disabled", true);
					i.removeClass("fa-check").addClass("fa-sync fa-spin");
					
					executeCommand(checks, 0);
					
					
				    // feedback visivo spinner al posto dei check
					checks.each(function(i, e){
						
						var td = $(e).closest("td");
						
						$(e).hide();
						var spinner = $("<i></i>");
						spinner.addClass("fa fa-sync fa-spin");
						spinner.appendTo(td);
												
					});
					
					
				}
				
			});
			
			/**
			 * 
			 */
			function executeCommand(checks, n){
				
				var check = $(checks[n]);
				var nchecks = checks.length
				
				var id = check.prop("id");
				
				var td = check.closest("td");
				var result = check.closest("tr").find("td.result");
				
				$.post(  
					"calls/install_script.php",  
					{id: id},  
					function(response){
						result.html(response.msg);
						if(response.result == true){
							td.html("<i class='fa fa-check text-success'></i>");
						}else{
							td.html("<i class='fa fa-times text-danger'></i>");
						}
						
						var next = n+1;
						if(next < nchecks){
							executeCommand(checks, next)						
						}else{
							var t = $("button");
							var i = t.find("i");
							t.prop("disabled", false);
							i.addClass("fa-check").removeClass("fa-sync fa-spin");
						}						
						
					},  
					"json"  
				);  	
				
			}
			
		});
		
	</script>
	
	
</body>
</html>
