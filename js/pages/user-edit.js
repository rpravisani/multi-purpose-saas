$(document).ready(function() {

	// Scegliendo o cambiando targa recupero km da ultima scheda intervento
	$(document).on("change", "#subscription_type", function(){
		var subscription = $(this).val();
		var last_renew = $("#last_renew").val();
		
		$.post(  
		 "calls/get_expiry_date.php",  
		 {subscription: subscription, last_renew: last_renew},  
		 function(response){
			 if (response.result){
				 $("#expiry_date").val(response.expiry_date);
			 }else{
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
		
	});

	$(document).on("click", ".delrow", function(){
		var t = $(this).closest("table");
		$(this).closest("tr").remove();
		// change name and id's of fields (reorder from 1 to x)
		t.find("tr").each(function(index, element) {
            var c = parseInt(index)+1;
			var pnid = "preferences_name_"+c;
			var pvid = "preferences_value_"+c;
			var pnname = "preferences_name["+c+"]";
			var pvname = "preferences_value["+c+"]";
			$(this).find("td").each(function(index, element) {
                var f = $(this).find("input");
				if(index == 0){
					f.prop("id", pnid);
					f.prop("name", pnname);
				}else if(index == 1){
					f.prop("id", pvid);
					f.prop("name", pvname);
				}
            });
        });
	});

	$(document).on("click", "#addrow", function(){
		var nrows = $("#preferencestable").find("tr").length;
		nrows++;
		var tr = $("<tr></tr>");
		var inputName = $('<input type="text" value="" id="preferences_name_'+nrows+'" name="preferences_name['+nrows+']" class="form-control">');
		var inputVal = $('<input type="text" value="" id="preferences_value_'+nrows+'" name="preferences_value['+nrows+']" class="form-control">');
		var delbtn = $('<div class="btn btn-block btn-danger btn-xs delrow"><i class="fa fa-fw fa-times"></i></div>');
		var td = $("<td></td>");
		var td1 = td.clone();
		td1.append(inputName);
		td1.appendTo(tr);
		var td2 = td.clone();
		td2.append(inputVal);
		td2.appendTo(tr);
		var td3 = td.clone();
		td3.attr("width", "10");
		td3.append(delbtn);
		td3.appendTo(tr);
		$("#preferencestable").append(tr);
		
	});





});

