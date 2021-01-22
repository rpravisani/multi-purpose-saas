$(document).ready(function() {
	var dettagliOpen = false;
	var dbgOpen = false;
	$("#dettagli").hide();
	$("#debug").hide();
	
	$("#open-dettagli").on("click", function(){
		if(dettagliOpen){
			$("#dettagli").slideUp("fast");
			dettagliOpen = false;
		}else{
			$("#dettagli").slideDown("fast");
			dettagliOpen = true;
		}
	});

	$("#open-dbg").on("click", function(){
		if(dbgOpen){
			$("#debug").slideUp("fast");
			dbgOpen = false;
		}else{
			$("#debug").slideDown("fast");
			dbgOpen = true;
		}
	});

});