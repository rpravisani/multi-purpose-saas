$(document).ready(function() {
	
	// When the new-row button is clicked a new tr is appended to tbody with save button at the end
	$(document).on("change", "#subscription_type", function(){
		
		var t = $(this);
		$("#didascalia_tipo_utente").html(t.find("option:selected").data('description'))

	});


});



