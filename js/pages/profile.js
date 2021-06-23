$(document).ready(function() {
    
    var empt = "Vuoto";
	
	$(document).on("click", "#modifica-password", function(){
       $('#pwd-modal').modal(); 
    });
                   
	$(document).on("click", "#change-user-data", function(){
		
		var t = $(this);
		if(t.data("action") == "edit"){
            
            $(".editable").each(function(){
                if($(this).text() == '') $(this).text("Vuoto");
                
            });
            $(".editable").prop("contenteditable", true);
            
			t.text("Salva");
			t.removeClass("btn-primary").addClass("btn-success");
			t.data("action", "save")
			
		}else if(t.data("action") == "save"){
            
			var values = {}
            
			$(".editable").each(function(){
				
				var field = $(this).data("name");
                var v = $(this).text();
                if(v == "Vuoto"){
                    v = '';
                    $(this).text('')
                }
				values[field] = v;
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