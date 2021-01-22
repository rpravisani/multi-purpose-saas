$(document).ready(function() {
		
	$(document).on("click", ".fa-globe", function(){
		var url = $("#gotourl").val();
		if(url){
			window.open(url,'_blank');
			return true;
		}else{
			return false;
		}
	});

	
	
	
	
	$('.fancybox').fancybox();	
	
});

var myVar = setInterval(myTimer, 30000);

function myTimer() {
    var d = new Date();
	$("#time_update").val(d.toLocaleTimeString());
	$("#date_update").on("changeDate", function(e) {
		clearInterval(myVar);
    });	
	$("#time_update").on("changeTime.timepicker", function(e) {
		clearInterval(myVar);
    });	
}