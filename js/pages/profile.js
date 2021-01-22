$(document).ready(function() {
	
	$(document).on("click", "#change-user-data", function(){
		
		var t = $(this);
		if(t.data("action") == "edit"){
			
			$(".editable").prop("contenteditable", true);
			t.text("Salva");
			t.removeClass("btn-primary").addClass("btn-success");
			t.data("action", "save")
			
		}else if(t.data("action") == "save"){
			var values = {}
			$(".editable").each(function(){
				
				var field = $(this).data("name");
				values[field] = $(this).text();
				$(this).prop("contenteditable", false);
				
			});
			
			
			$.post(  
				"calls/saveprofile.php",    
				{values: values},  
				function(response){
					if(response){
						if(response.result){
							t.text("Modifica");
							t.removeClass("btn-success").addClass("btn-primary");
							t.data("action", "edit");
							
						}else{
							alert("azz");
						}
					} 
				}, "json"	

			);
			
			
			
			
			
			
		}
		
	});
	
	
	
});