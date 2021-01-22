$(document).ready(function() {
	
	var checked = {};

	$(document).on("click", ".delete-page", function(){
		if(!confirm("Sicuro di voler eliminare questa pagina?")) return false;
		var t = $(this);
		var tr = t.closest("tr");
		var pageid = tr.attr("id");
		
		// substitute icon with loader icon
		t.removeClass("fa-trash");
		t.addClass("loader");
		
		$.post(  
		 "calls/delpage.php",  
		 {pageid: pageid, delchildren: 0 },  
		 function(response){
			 t.removeClass("loader");
			 if (response.result){				
				 // remove row
				 tr.fadeOut("fast", function(){ $(this).remove(); resetOrder(response.neworder); });
				 
			 }else{
				 // reset original icon
				 t.addClass("fa-trash");
				modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	

	});
	
	$("td small i.fa").hover(
		function(){
			var tab = $(this).closest("table");
			var thisRow = $(this).closest("tr").attr("id");
			var linkedRow = $(this).data("link");
			
			
			tab.find("tr").css("opacity", 0.4);
			tab.find("tr#"+thisRow).css("opacity", 1);
			tab.find("tr#"+linkedRow).css("opacity", 1);
			tab.find("tr#"+linkedRow).addClass("linked");
		},
		function(){
			$("tr").removeClass("linked");
			$("tr").css("opacity", 1);;
		
		}
	);
	
	$(document).on("click", ".select-column", function(){
		var t = $(this);
		var classe = t.data("column");
		if(t.is(":checked")){
			$("."+classe).prop("checked", true);
		}else{
			$("."+classe).prop("checked", false);
			
		}
	});
	
	$(document).on("click", ".delete-children", function(){
		if(!confirm("Sicuro di voler eliminare questa pagina e sotto-pagine?")) return false;
		var t = $(this);
		var tr = t.closest("tr"); // row of clicked page
		var pageid = tr.attr("id"); // the id of the clicked page
		var childTr = tr.next();
		
		// substitute icon with loader icon
		t.removeClass("fa-trash");
		t.addClass("loader");
		
		
		// richiamo script basato su delrecord che prima estrapola i figli della pagina e cancella quelli e se 
		
		$.post(  
		 "calls/delpage.php",  
		 {pageid: pageid, delchildren: 1 },  
		 function(response){
			 t.removeClass("loader");
			 if (response.result){				
				 // remove rows
				 $.each(response.delrows, function(i,v){
					 var tr = $("#"+v);
					 tr.fadeOut("fast", function(){ $(this).remove(); });
				 });
				 if(response.delall){
					 childTr.fadeOut("fast", function(){ $(this).remove(); });
					 resetOrder(response.neworder);
				 }else{
					 t.addClass("fa-trash");
					 modal(response.msg, response.error, response.errorlevel);
				 }
			 }else{
				 // reset original icon
			 	t.addClass("fa-trash");
				modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	

	});
	
	$(document).on("click", "#clean-pages", function(){
		$.post(  
		 "calls/get_orphan_files.php",  
			{},
		 function(response){
			 
			 if (response.result){				
				modal(response.html, "Clean files", "default", null, true, '75%');
				
				
			 }else{
				 // reset original icon
			 	
				modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
		
	});
	
	$(document).on("click", "#orphan-files input[type=checkbox]", function(){
		$("#delete-pages").prop("disabled", false);
	});
	
	$(document).on("click", "#delete-pages", function(){
		var list = new Array();
		var ids = new Array();
		var i = 0;
		// get all checks
		$("#orphan-files").find("input.orphan:checked").each(function(){
			list[i] = $(this).val(); 
			ids[i] = $(this).attr("name"); 
			i++; 			
		});
		if(i == 0){
			return false;
		}
		if(confirm("Are you sure you want to delete all "+i+" files checked? ")){
			
		$.post(  
			"calls/delete_orphan_files.php",  
			{list: list},
			function(response){

				if (response.result){
					
					jQuery.each(ids, function(k, v){
						$("#"+v).addClass("deleted");
					});
					$("#orphan-files").find("input:checked").remove();
					$("#delete-pages").prop("disabled", true);
					$("#delmsg").text("Deleted "+i+" records");

				}else{

					modal(response.msg, response.error, response.errorlevel);
					
				}
			},  
			"json"  
		);  				
			
		}
		
	});
	
	
	
	// checkbuttons - show or hide copy pages button - per ora non mosto checkboxes
	$(document).on("click", ".pcheck", function(){
		var t = $(this);
		var id = t.prop('id');
		var v = t.data("page");
		if(t.prop("checked")){
			checked[id] = v;
		}else{			
			delete checked[id];
		}
		if(Object.keys(checked).length > 0 ){
			$("#copy-pages").slideDown("fast");
		}else{
			$("#copy-pages").slideUp("fast");
			
		}
	});

	$(document).on("click", "#import-pages", function(){
		$("#import-interface").slideDown("fast", function(){
			$("#import-interface .select2").select2();
			
		});
	});
	
	// CONNECT DB BUTTON
	$(document).on("click", "#connect-db", function(){
		// puls diabled
		var t = $(this);
		
		t.prop("disabled", true)
		var i = t.find("i");
		i.removeClass("fa-plug");
		i.addClass("fa-refresh fa-spin");
		
		// get vars
		var user = $("#user").val();
		var pwd = $("#pwd").val();
		var host = $("#host").val();
		$.post(  
			"calls/connect-other-database.php",  
			{user: user, pwd: pwd, host: host, table: "pages"},
			function(response){
				t.prop("disabled", false);
				i.removeClass("fa-refresh fa-spin");
				i.addClass("fa-plug");
				$("#db").html( response.dblist );
				$("#db").select2();
				if(response.result === true){
					t.removeClass("btn-danger");
					t.addClass("btn-success");
					t.html("<i class=\"fa fa-check\"></i> Connected");
					$("#user").prop("disabled",true);					
					$("#pwd").prop("disabled", true);					
					$("#host").prop("disabled", true);					
				}else{
					modal(response.msg, "Could not connect", "danger");
				}
			},  
			"json"  
		);		
	});	
	
	// CHANGE SELECT WITH DB IN MODAL - ACTIVATES GETPAGES BUTTON
	$(document).on("change", "#db", function(){
		var v = $(this).val();
		if(v == ''){
			$("#getpages").prop("disabled", true);						
		}else{
			$("#getpages").prop("disabled", false);			
		}
	});

	// CLICK GETPAGES BUTTON - GET LIST OF PAGES
	$(document).on("click", "#getpages", function(){
		var t = $(this);
		var db = $("#db").val();
		var systemonly = ( $("#systemonly").prop("checked") ) ? "1" : "0";
		var user = $("#user").val();
		var pwd = $("#pwd").val();
		var host = $("#host").val();
		// disable button
		t.prop("disabled", true)
		var i = t.find("i");
		i.removeClass("fa-download");
		i.addClass("fa-refresh fa-spin");
		
		$.post(  
			"calls/get-page-list.php",  
			{user: user, pwd: pwd, host: host, db: db, systemonly: systemonly},
			function(response){
				// reactivate button
				t.prop("disabled", false);
				i.removeClass("fa-refresh fa-spin");
				i.addClass("fa-download");
				
				if(response.result === true){
					// insert page list in modal
					modal(response.list, "Page List", "default", true);
				}else{
					modal(response.msg, "Could not connect", "danger");
				}
			},  
			"json"  
		);		
	});
	
	// in copy pages from other installations modal window - select children if clicked on parent
	$(document).on("click", ".parentpage input", function(){
		if($(this).prop("checked")){
			// get children (if any)
			$(this).parent("li").find("ul li").each(function(){
				$(this).find("input").prop("checked", true);
			});
			
		}else{			
			// get children (if any)
			$(this).parent("li").find("ul li").each(function(){
				$(this).find("input").prop("checked", false);
			});
		}
		
	})
	
	$(document).on("click", "#modal-save", function(){
		var t = $(this);
		// get all the checks
		var pages = new Array();
		var c = 0;
		$("#page-listing").find("input").each(function(){
			if($(this).prop("checked")){
				pages[c] = $(this).data("page");
				c++;
			}
		});
		if(c > 0){

			var db = $("#db").val();
			var user = $("#user").val();
			var pwd = $("#pwd").val();
			var host = $("#host").val();
			
			t.prop("disabled", true);
			
			$.post(  
				"calls/import-pages.php",  
				{user: user, pwd: pwd, host: host, db: db, pages: pages},
				function(response){
					// reactivate button
					t.prop("disabled", false);

					if(response.result === true){
						// reload window
						location.reload();
					}else{
						modal(response.msg, "Could not connect", "danger");
					}
				},  
				"json"  
			);		
			
			
		}else{
			alert("Select at least one page!");
		}
	});
	
	

});

function resetOrder(neworder){
	$.each(neworder, function(i, v){
		var tr = $("#"+i);
		tr.find("td.order").text(v);
	});
}