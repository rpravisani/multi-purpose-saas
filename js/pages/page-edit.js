$(document).ready(function() {
	
	// update icon thumb when i change icon
	$(document).on("change", "#icon-select, #icon-input", function(){
		var icon = $(this).val();
		var icon_class = $("#icon_class").val();
		updateIconThumb(icon, icon_class);
	});

	// update icon thumb when i change icon_class
	$(document).on("change", "#icon_class", function(){
		var icon_class = $(this).val();
		if($("#icon-select").length){
			var icon = $("#icon-select").val();
		}else{
			var icon = $("#icon-input").val();
		}
		updateIconThumb(icon, icon_class);
	});

	// get max value of order for the menu section
	$(document).on("change", "#parent", function(){
		var p = $(this).val();
		$.post(  
		 "calls/get_max_order.php",  
		 {tab: 'pages', field: 'parent', value: p },  
		 function(response){
			 if (response.result){
				 highlighField("order", true);	
				 $("#order").val(response.value);
			 }else{
				modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	


	});

});

function updateIconThumb(icon, icon_class){
	$("#anteprima-icona").find("i").removeClass();
	$("#anteprima-icona").find("i").addClass("fa fa-2x fa-border fa-"+icon+" "+icon_class);
}