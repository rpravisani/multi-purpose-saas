$(document).ready(function() {
	var newRows = 0;
	var exclude = new Array();
	
	// for search hilite
	$("input.highlight").closest("tr").addClass("bg-warning");
	
	$(".filter").on("change", function(){
		var lang = $("#language").val();
		var sec = $("#section").val();
		var url = "cpanel.php?pid="+pid+"&lang="+lang+"&section="+sec;
		$("#page-loader").fadeIn();
		window.location = url;
	});

	$("#add-section").on("click", function(){
		var pos = $(this).position();
		var left = pos.left;
		var top = parseInt(pos.top) + parseInt($(this).outerHeight());
		var w = $(this).outerWidth();
		var p = $(this).parent();
		
		var label = $("<label for='newSection'>Name section</label>");

		var inputField = $("<input>");
		inputField.attr("id", "newSection");
		inputField.attr("name", "newSection");
		inputField.attr("type", "text");	
		inputField.css("width", "100%");	

		var btn = $("<button></button>");
		btn.addClass("btn btn-block btn-success");
		btn.css("margin-top", "4px");
		btn.html("<i class=\"fa fa-check\"></i> Save");
		
		var btn2 = $("<button></button>");
		btn2.addClass("btn btn-block btn-danger");
		btn2.css("margin-top", "4px");
		btn2.html("<i class=\"fa fa-close\"></i> Cancel");
		
		var div = $("<div></div>");
		div.css("position", "absolute");
		div.css("padding", "10px");
		div.css("background-color", "#f5f5f5");
		div.css("top", top+"px");
		div.css("left", left+"px");
		div.css("width", "200px");
		div.css("display", "none");
		div.append(label);
		div.append(inputField);
		div.append(btn);
		div.append(btn2);
		div.appendTo(p);
		div.slideDown("fast");
		
		btn2.on("click", function(){
			div.fadeOut();
		});

		btn.on("click", function(){
			var v = inputField.val();
			if(confirm("Are you sure you want to create a new section called "+v+"?")){
				$("#page-loader").fadeIn();				
				$.post(  
					"calls/new_language_section.php",  
					{name: v},
					function(response){
						$("#page-loader").fadeOut();
						if(response.result === true){
							// returns option tag with newly created section
							div.fadeOut("fast");
							$("#section").append(response.option); // append option to select
						}else{
							alert(response.msg);
						}
					},  
					"json"  
				);		
			}
		});

	});

	$("#add-row").on("click", function(){
		newRows++;
		var table = $("#translations");
		var row = "<tr>";
		row += "<td width='5%' align='center'>-</td>";
		row += "<td><input style='width: 100%' type='text' id='new-string-"+newRows+"' name='new-string-"+newRows+"' value='' class='newstring'></td>";
		row += "<td><input style='width: 100%' type='text' id='new-translation-"+newRows+"' name='new-translation-"+newRows+"' value=''></td>";
		row += "</tr>";
		table.append(row);
		$("#new-string-"+newRows).focus();
	});

	$("#copy-section").on("click", function(){
		var sec = $("#section").val();
		var lang = $("#language").val();
		var pos = $(this).position();
		var left = pos.left;
		var top = parseInt(pos.top) + parseInt($(this).outerHeight());
		var w = $(this).outerWidth();
		var p = $(this).parent();
		
		var lselect = $("#language").clone();
		lselect.attr("id", "copy-language");
		var option = lselect.find("option:selected");
		option.remove();
		
		var btn = $("<button></button>");
		btn.addClass("btn btn-block btn-success");
		btn.css("margin-top", "4px");
		btn.html("<i class=\"fa fa-check\"></i> Copy");
		
		var btn2 = $("<button></button>");
		btn2.addClass("btn btn-block btn-danger");
		btn2.css("margin-top", "4px");
		btn2.html("<i class=\"fa fa-close\"></i> Close");

		
		var div = $("<div></div>");
		div.css("position", "absolute");
		div.css("padding", "10px");
		div.css("background-color", "#f5f5f5");
		div.css("top", top+"px");
		div.css("left", left+"px");
		div.css("width", w+"px");
		div.css("display", "none");
		div.append(lselect);
		div.append(btn);
		div.append(btn2);
		div.appendTo(p);
		div.slideDown("fast");

		btn2.on("click", function(){
			div.fadeOut();
		});
		
		btn.on("click", function(){
			var l = lselect.val();
			var selectedLang = lselect.find("option:selected");
			if(confirm("Are you sure you want to copy al the translations of this sections to "+selectedLang.html()+"?")){
				$("#page-loader").fadeIn();				
				$.post(  
					"calls/copy-translations.php",  
					{fromlang: lang, tolang: l, section: sec},
					function(response){
						$("#page-loader").fadeOut();
						if(response.result === true){
							response.msg += selectedLang.html();
							div.fadeOut("fast");
							alert(response.msg);
							
							
						}else{
							alert(response.msg);
						}
					},  
					"json"  
				);		
			}
		});		
		
	});

	$("#search-translation").on("click", function(){
		if( $("#search").is(":visible")){
			$("#search").slideUp("fast");
		}else{
			$("#search").slideDown("fast");
		}
	});
	
	$("#search-field").on("change", function(){
		var l = $("#language").val();
		var s = $(this).val();
		searchfor( s, l );
	});
	
	$(document).on("dblclick", "input.newstring", function(){
		var i = $(this).prop("id");
		var lang = $("#language").val();
		var section = $("#section").val();
		
		$.post(  
			"calls/get-translations_lost.php",  
			{lang: lang, field: i, section: section, exclude: exclude},
			function(response){
				if(response.result === true){
					
					modal(response.html, response.title, 'default');
					
				}else{
					if(response.errorcode == "nolost") response.errorlevel = "default";
						
					modal(response.msg, response.error, response.errorlevel);
						
				}
			},  
			"json"  
		);		
		
	});
	
	$(document).on("click", ".addlost", function(){
		var field = $(this).data("field");
		var string = $(this).closest("tr").find("td.string").text();
		$("#"+field).val(string);
		exclude.push(string);
		$("#message-modal .modal").fadeOut("fast");		
	});


	$("#save").on("click", function(){
		var form = $("form").serializeArray(); // jquery function
		var lang = $("#language").val();
		var sec = $("#section").val();
		$("#page-loader").fadeIn();
		$.post(  
			"calls/save-translations.php",  
			{form: form, lang: lang, section: sec},
			function(response){
				if(response.result === true){
					location.reload();
					
				}else{
					$("#page-loader").fadeOut();
					alert(response.msg);
				}
			},  
			"json"  
		);		
	});

	$(".delrow").on("click", function(event){
		event.preventDefault();
		var id = $(this).data("id");
		if(confirm("Are you sure you want to delete this translation?")){
			$("#page-loader").fadeIn();
			$.post(  
				"calls/delete-translation.php",  
				{id: id},
				function(response){
					if(response.result === true){
						location.reload();
					}else{
						$("#page-loader").fadeOut();
						alert(response.msg);
					}
				},  
				"json"  
			);		
				
		}
	});
	
	/*** SYNC FUNCTIONS ***/
	$(document).on("click", "#sync-translations", function(){
		$("#sync-interface").slideDown("fast", function(){
			$("#sync-interface .select2").select2();
			
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
			{user: user, pwd: pwd, host: host, table: "translations"},
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
	
	// CHANGE SELECT WITH DB IN MODAL - ACTIVATES SYNC BUTTON
	$(document).on("change", "#db", function(){
		var v = $(this).val();
		if(v == ''){
			$("#syncnow").prop("disabled", true);						
		}else{
			$("#syncnow").prop("disabled", false);			
		}
	});

	// SYNC NOW BUTTON
	$(document).on("click", "#syncnow", function(){
		var t = $(this);
		var db = $("#db").val();
		if(! confirm("Are you sure you want to syncronize translations with "+db)) return false;
		var languages = $("#languages").val();
		var systemonly = ( $("#systemonly").prop("checked") ) ? "1" : "0";
		var clearnotfound = ( $("#clearnotfound").prop("checked") ) ? "1" : "0";		
		var user = $("#user").val();
		var pwd = $("#pwd").val();
		var host = $("#host").val();
		// disable button
		t.prop("disabled", true)
		var i = t.find("i");
		i.addClass("fa-spin");

		$.post(  
			"calls/sync-translations.php",  
			{user: user, pwd: pwd, host: host, db: db, languages: languages, systemonly: systemonly, clearnotfound: clearnotfound},
			function(response){
				t.prop("disabled", false);
				i.removeClass("fa-spin");

				if(response.result === true){
					var nins = response.inserted.length;
					var nups = response.updated.length;
					var message = "<strong>"+nins+"</strong> translations inserted<br><strong>"+nups+"</strong> translations updated<br><em>Check the file <strong>logsyncs.txt</strong> in calls/</em>";
					if(response.truncated) message += "<br><br>Translations Lost tabel has been emptied.";
					modal(message, "Translations synced", "success");
					
				}else{
					modal(response.msg, response.error, "danger");
				}
			},  
			"json"  
		);		
	});

	
	/*** CLEAN TRANSLATION TABLE ***/
	$(document).on("click", "#clean-translations", function(){
		if(!confirm("Delete alll the translation from sections that do not have a corrispondence in the pages table?")) return false;
		
		// Deactivate button
		var t = $(this);
		t.prop("disabled", true)
		var i = t.find("i");
		i.removeClass("fa-trash");
		i.addClass("fa-refresh fa-spin");
		
		// launch script non post variables needed...
		$.post(  
			"calls/clean-translations.php",  
			{dummy: 1},
			function(response){
				// reactive button
				t.prop("disabled", false);
				i.addClass("fa-trash");
				i.removeClass("fa-refresh fa-spin");

				if(response.result === true){
					modal(response.msg, "Cleaned up!", "success");
				}else{
					modal(response.msg, response.error, "danger");
				}
			},  
			"json"  
		);		
		
		
	});
	
	
}); // END ON DOCUMENT LOAD

function searchfor(s, lang){
	$("#page-loader").fadeIn();
	$.post(  
		"calls/search-translation.php",  
		{searchfor: s, language: lang},
		function(response){
			if(response.result === true){
				var ss = encodeURI(s);
				var url = "cpanel.php?pid=16&lang="+lang+"&section="+response.section+"&field="+response.field+"&s="+ss;
				window.location = url;
			}else{
				$("#page-loader").fadeOut();
				modal(response.msg, response.error, response.errorlevel);
			}
		},  
		"json"  
	);		
}
