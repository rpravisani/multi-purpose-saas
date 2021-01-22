$(document).ready(function() {
	
	// create backup
	$(document).on("click", ".backupbtn", function(){
		
		var t = $(".backupbtn");
		
		var i = t.find("i");
		i.removeClass("fa-database").addClass("fa-spin fa-refresh");
		t.prop("disabled", true);
		
		
		
		$.post(  
			"calls/backup-db.php",    
			{dummy: 1},  
			function(response){
				
				t.prop("disabled", false);
				i.addClass("fa-database").removeClass("fa-spin fa-refresh");
				
				if(response){					
					if(response.result){
						$("#table-backups").find("tbody").prepend(response.tr)
					}else{
					}
				} 
			}, "json"	

		);
		
		
		
	});
	
	$(document).on("click", ".delete-backup", function(){
		
		if(!confirm("Sei sicuro di voler eliminare questo file di backup?")) return false;
		
		var t = $(this);
		var tr = t.closest("tr");
		var filename = tr.find("td.backup-filename").text();
		
		// substitute icon with loader icon
		t.removeClass("fa-trash");
		t.addClass("loader");
		
		$.post(  
			"calls/delete-backup-db.php",    
			{filename: filename},  
			function(response){
				
				t.addClass("fa-trash");
				t.removeClass("loader");
				
				if(response){					
					if(response.result){
						tr.fadeOut("fast");
					}else{
						modal(response.msg, response.error, response.errorlevel);
					}
				} 
			}, "json"	

		);
		
	});
	
	$(document).on("click", ".download-backup", function(){
				
		var t = $(this);
		var tr = t.closest("tr");
		var filename = tr.find("td.backup-filename").text();
		
		window.location.href = 'backup/'+filename;
		
	});
	
	
});

