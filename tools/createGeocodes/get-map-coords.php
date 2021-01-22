<?php
$joomla = false;
if($joomla){
	
	include_once '../../../configuration.php';
	include_once 'cc_mysqli.class.php';

	$variables = new JConfig();

	$db = new cc_dbconnect($variables->db, $variables->host, $variables->user, $variables->password);

	$table_prefix = $variables->dbprefix;
	
}else{
	
	include_once '../../required/variables.php';
	include_once '../../required/classes/cc_mysqli.class.php';

	$db = new cc_dbconnect(DB_NAME);

	$table_prefix = "qqdrv_";
	
}

$tab_segnaposti = $table_prefix."ecmaps_segnaposti_new";
$tab_geocodes   = $table_prefix."ecmaps_geocodes_new";

$where = (empty($_GET['all'])) ? "WHERE trovato = '0'" : "";
$checked = (empty($_GET['all'])) ? "checked='checked'" : "";
$checked_nazione = (!empty($_GET['nazione'])) ? "checked='checked'" : "";

// leggo indirizzi da tabella segnaposti
$segnaposti = $db->select_all($tab_segnaposti, $where." ORDER BY indirizzo, cap, localita, prov");
$num_segnaposti = count($segnaposti);
$trovati = 0;
$tab = "";

if($segnaposti){
	foreach($segnaposti as $segnaposto){
		
		$indirizzo = $segnaposto['indirizzo']." ".$segnaposto['cap']." ".$segnaposto['localita']." ".$segnaposto['prov'];
		if(!empty($_GET['nazione'])) $indirizzo .= " ".$segnaposto['nazione'];
		$tab .= "<tr data-id='".$segnaposto['id']."' data-status='todo' ><td>".$indirizzo."</td><td class='lng'>&nbsp;</td><td class='lat'>&nbsp;</td><td class='ok text-center'>&nbsp;</td></tr>";
		
	} // end foreach
	
} // end if segnaposti

$gotcurl = (function_exists('curl_init')) ? true : false;

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Map Geocodes</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<script
  src="https://code.jquery.com/jquery-2.2.4.js"
  integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI="
  crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container">
		<div class="row">
			<div class="md-col-8 md-col-offset-2 text-center">
			
				<h4>Map Google Coords 
				<?php echo ($gotcurl) ? "<small class=\"label label-success\">CURL</small>" : "<small class=\"label label-danger\">No CURL</small>"; ?>
				<?php echo ($joomla) ? " <small class=\"label label-warning\">Joomla! vers.</small>" : "<small class=\"label label-primary\">Normal vers.</small>"; ?>
				</h4>
				<button class='btn btn-success'>Start</button><br>
				<small>Time elapsed: <span class='time-elapsed'>0</span></small>
				<hr>
			</div>
		</div>
		<div class="row">
		
			<div class="md-col-8 md-col-offset-2 text-center">			
				<p>Completato al <span id='perc-completato'>0</span>%</p>
				<div class="progress">
					<div id="completato" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0"  aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
				</div> 																												
			</div>
	
			<div class="md-col-8 md-col-offset-2 text-center">			
				<p>Trovate <span id='num-trovato'>0</span> coordinate su <?php echo $num_segnaposti; ?> </p>
				<div class="progress">
					<div id="trovati" class="progress-bar" role="progressbar" aria-valuenow="0"  aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
				</div> 																												
			</div>
			
			<hr>
		</div>		
		<div class="row">
			<div class="md-col-10 md-col-offset-1">
			
				<div class="checkbox">
					<label><input id="switch" type="checkbox" <?php echo $checked; ?>>Mostra solo non trovati</label>&nbsp;&nbsp;
					<label><input id="nazione" type="checkbox" <?php echo $checked_nazione; ?>>Includi nazione</label>
				</div>			
				<table class="table table-bordered">
					<thead>
						<tr><th>INDIRIZZO</th><th>LNG</th><th>LAT</th><th>OK</th></tr>
					</thead>
					<tbody>
						<?php echo $tab; ?>
					</tbody>

				</table>			
			</div>
		</div>		
	</div>

<script type="text/javascript"> 
	
	$(document).ready(function() {
		
	String.prototype.toHHMMSS = function () {
		var sec_num = parseInt(this, 10); // don't forget the second param
		var hours   = Math.floor(sec_num / 3600);
		var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
		var seconds = sec_num - (hours * 3600) - (minutes * 60);

		if (hours   < 10) {hours   = "0"+hours;}
		if (minutes < 10) {minutes = "0"+minutes;}
		if (seconds < 10) {seconds = "0"+seconds;}
		return hours+':'+minutes+':'+seconds;
	}	
	String.prototype.toMMSS = function () {
		var sec_num = parseInt(this, 10); // don't forget the second param
		var hours   = Math.floor(sec_num / 3600);
		var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
		var seconds = sec_num - (hours * 3600) - (minutes * 60);

		if (hours   < 10) {hours   = "0"+hours;}
		if (minutes < 10) {minutes = "0"+minutes;}
		if (seconds < 10) {seconds = "0"+seconds;}
		return minutes+':'+seconds;
	}	
		
		
		
		tab = $("table tbody").find("tr");
		i = trovati = timeElapsed = 0;
		proceed = false;
		started = false;
		max = tab.length;
		titolo = document.title;
		
		
		$(document).on("click", "button", function(){
			var t = $(this);
			
			if(started){
				started = false;
				proceed = false;
				t.removeClass("btn-danger").addClass("btn-success");
				t.text("Start");
			}else{
				started = true;
				proceed = true;
				t.removeClass("btn-success").addClass("btn-danger");
				t.html("Stop");
				getCoords();
			}
			
		});
		

		$(document).on("change", ".checkbox input[type=checkbox]", function(){
			var s = $("#switch").is(":checked") ? "" : "?all=true";
			var n = $("#nazione").is(":checked") ? "nazione=true" : "";
			if(n!="") n = (s=="") ? "?"+n : "&"+n;
			var suffix = s+n;
			var url = "get-map-coords.php"+suffix;
			window.location = url;			
		});
		
	});
	
	$.ajaxSetup({
		type: 'POST',
		timeout: 20000,
		error: function(xhr) {
			var elem = tab[i];
			var icon = "<i title=\""+xhr.statusText+"\" data-toggle=\"tooltip\" class=\"fa fa-exclamation-triangle text-danger\"></i>";
			$(elem).find("td.lng").text("-");
			$(elem).find("td.lat").text("-");
			$(elem).find("td.ok").html(icon);
			updatePercents();
			updateElapsedTime(20);
			proceed = false;
			i++;
				if(i < max){
					if(proceed){
						getCoords(); 						
					}else{
						// can not proceed
						if(started){
							started = false;
							var t = $("button");
							t.prop("disabled", true);
							t.html("Cannot proceed!");
							
						}
					}
				}else{ 
					started = false;
					var t = $("button");
					t.prop("disabled", true);
					t.removeClass("btn-danger").addClass("btn-default");
					t.html("Concluded!");
					
				}		 
			
		}
	})
	
	
	function getCoords(){
		var elem = tab[i];
		var id = $(elem).data("id");
		var status = $(elem).data("status");
		var nazione = <?php echo (empty($_GET['nazione'])) ? '0' : '1' ?>;
		
		$.post(  
			"getcoords.php",  
			{id: id, nazione: nazione },  
			function(response){

				if(response.result){
					trovati++;
					var icon = "<i title=\""+response.error+"\" data-toggle=\"tooltip\"  class='fa fa-check text-success'></i>";
					if(response.error == "doppione") icon += "<i title=\""+response.error+"\" data-toggle=\"tooltip\"  class='fa fa-check text-success'></i>";
				}else{
					if(!response.proceed){
						proceed = false;
						alert("stop");
					}
					var icon = "<i title=\""+response.error+"\" data-toggle=\"tooltip\" class=\"fa fa-times text-danger\"></i>";
					
				}

				$(elem).find("td.lng").text(response.lng);
				$(elem).find("td.lat").text(response.lat);
				$(elem).find("td.ok").html(icon);
				
				updatePercents();
				updateElapsedTime(response.elapsed_time);

				i++;
		
				if(i < max){
					if(proceed){
						getCoords(); 						
					}else{
						// can not proceed
						if(started){
							started = false;
							var t = $("button");
							t.prop("disabled", true);
							t.html("Cannot proceed!");
							
						}
					}
				}else{ 
					started = false;
					var t = $("button");
					t.prop("disabled", true);
					t.removeClass("btn-danger").addClass("btn-default");
					t.html("Concluded!");
					
				}		 
			 
		 },  
		 "json"
		);  
		

	}
	
	function updatePercents(){
		// update percents
		var num = i+1;
		var perc_completed = Math.round((num/max)*100);
		//console.log("("+num+"/"+max+")*100 = "+perc_completed);
		$("#completato").text(perc_completed+"%");
		$("#completato").prop("aria-valuenow", perc_completed);
		$("#completato").css("width", perc_completed+"%");
		$("title").text(perc_completed+"%");
		document.title = "("+perc_completed+"%) "+titolo;
		$("#perc-completato").text(perc_completed);


		$("#num-trovato").text(trovati);
		var perc_trovati = Math.round((trovati/max)*100);
		//console.log("("+trovati+"/"+max+")*100 = "+perc_trovati);
		$("#trovati").text(perc_trovati+"%");
		$("#trovati").prop("aria-valuenow", perc_trovati);
		$("#trovati").css("width", perc_trovati+"%");		
	}
	
	function updateElapsedTime(t){
		// elapsed time
		timeElapsed += parseFloat(t);
		timeElapsed = Math.round(timeElapsed * 100) / 100;
		
		var sec_num = parseInt(timeElapsed, 10); // don't forget the second param
		var ms = timeElapsed-sec_num
		var hours   = Math.floor(sec_num / 3600);
		var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
		var seconds = sec_num - (hours * 3600) - (minutes * 60);
		seconds +=ms;

		if (hours   < 10) {hours   = "0"+hours;}
		if (minutes < 10) {minutes = "0"+minutes;}
		if (seconds < 10) {seconds = "0"+seconds;}
		$(".time-elapsed").text(minutes+":"+seconds);

	 }
	
	
</script>

</body>
</html>