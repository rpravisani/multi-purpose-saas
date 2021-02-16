// JavaScript Document
var okayToLeave = true;
var indexCheckBoxes = -1;
var rowsChecked = new Array();

window.onbeforeunload = function () {
   if (!okayToLeave) {
	   return "Attenzione, tornando indietro o anullando andranno persi i dati!"; // su ff e chorme da messaggio standard al posto di questo
   }
}

window.addEventListener('resize', function(){
    $(".select2").select2();
});

 

$(document).ready(function() {
	
	// se un intpu ha la classe 'select-on-click' quando ci clicco dentro seleziona il contenuto
	$(document).on('click', "input.select-on-click", function(){
		$(this).select();
	});
	
	// spunta / seleziona tutti i record visibili nella pagina
	$(document).on('click', "#select-all", function(){
		checkRows();
	});
	
	// togli spunta / deseleziona tutti i record visibili nella pagina
	$(document).on('click', "#select-none", function(){
		uncheckRows(false);
	});	

	$(document).on('click', "#delete-rows", function(){
		if(rowsChecked.length == 0){
			alert("Please check at least one row!") // should not happen at all...
			return false;
		}
		var t = $(this);
		setLoadStatus(t);
		
		$.post(  
		 "calls/delrecord.php",  
		 {record: rowsChecked, pid: pid, disable: false, deldependencies: false },  
		 function(response){
			 restoreBtnStatus(t);
			 if(response.result){				
				 // remove rows
				 if(response.deleted.length > 0){
					 $.each(response.deleted, function(i,e){
						 $("#"+e).fadeOut("fast", function(){ dtable.row('#'+e).remove().draw( false ); });
					 });
				 }
				 if(response.error){
					 modal(response.msg, response.error, "warning", false);
					 // highlight checked rows of table
					 highlightCheckedRows();
					 // remove checks
					 uncheckRows();
				 }
				 
			 }else{
				 modal(response.msg, response.error, "default", true);
				 $("#disable-option-btn").remove();
				 $("#delete-option-btn").remove();
			 }
		 },  
		 "json"  
		); 	
		
	});
	
	// Simple/standard CSV export button - no params are passed, all is decided inside export2csv switch file
	$(document).on("click", "#export-csv", function(){
		
		$.post(  
			"calls/export2csv.php",    
			{pid: pid},  
			function(response){
				if(response){
					if(response.result){
						modal(response.msg, response.error, "success", false);
					}else{
						modal(response.msg, response.error, "warning", false);
					}
				} 
			}, "json"	

		);
		
	});
		


    $( ".media-thumbs" ).sortable({      
      handle: ".dz-error-mark", 
	  placeholder: "placeholder-thumb", 
	  update: function( event, ui ) {
		var neword = parseInt( $(ui.item).index() )+1;
		var id = $(ui.item).find(".dz-error-mark span").data("id");
		$.post(  
		 "calls/media_order.php",  
		 {id: id, ord: neword},  
		 function(response){
			 if (!response.result){					
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
	  }
    });
 	
	// quickfilter management - uses icheck jquery plugin - it has two different events: one for when the checkbox is being checked and one for when it's being unchecked
	$('.quickfilters input').on('ifChecked', function(event){
		var col = $(this).data("col")
		var tab = $("table");
		if(col != "_empty_"){
			tab.find("td."+col).each(function(){
				if($(this).html() != ""){
					var tr = $(this).closest("tr").show();
				}
			});
		}else{
			// get all the other quickfilter data-col
			var i = $(this).closest(".quickfilters").find("input").map(function() {
    			return $(this).data("col");
  			});
			tab.find("tbody tr").each(function(){
				var tr = $(this);
				var c = 0;
				tr.find("td").each(function(){
					var classe = $(this).prop("class");
					if(jQuery.inArray( classe, i ) != -1){
						if($(this).html() != ""){ c++ }
					}
				}); 
				if(c == 0) tr.show();
			});			
		}
	});

	
	$('.quickfilters input').on('ifUnchecked', function(event){
		var col = $(this).data("col")
		var tab = $("table");
		if(col != "_empty_"){
			tab.find("td."+col).each(function(){
				if($(this).html() != ""){
					var tr = $(this).closest("tr").hide();
				}
			});
		}else{
			// get all the other quickfilter data-col
			var i = $(this).closest(".quickfilters").find("input").map(function() {
    			return $(this).data("col");
  			});
			tab.find("tbody tr").each(function(){
				var tr = $(this);
				var c = 0;
				tr.find("td").each(function(){
					var classe = $(this).prop("class");
					if(jQuery.inArray( classe, i ) != -1){
						if($(this).html() != ""){ c++ }
					}
				}); 
				if(c == 0) tr.hide();
			});			
		}
	});
	
	
	
	// Do not send form automatically if it has the nosend class
	$("form.ajax").submit(function(e){
		//validateForm(e);
		console.log("no submit");
		return false;
	});
	
	/*** THE MAINTENANCE SWITCH ***/
	$(document).on("click", "#maintenance", function(){
		var t = $(this);
		var oldState = t.data("maintenance");
		console.log("oldState: "+oldState);
		var newState = (oldState === "on") ? "off" : "on";
		console.log("newState: "+newState);
		var q = "Are you sure you want to switch "+newState.toUpperCase()+" the maintenance mode?";
		if(!confirm(q)) return false;
		
		var i = t.find("i");
		i.removeClass();
		i.addClass("fa loader");
		
		$.post(  
		 "helpers/set_config.php",  
		 {param: "maintenance_mode", value: newState},  
		 function(response){
			i.removeClass("loader");
			i.addClass("fa-wrench maintenance-"+newState);
			 
			 if (response.result){					
				t.prop("title", "Maintenance mode "+newState.toUpperCase());
				t.data("maintenance", newState);
			 }else{
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
	});

	/*** TAKE A SCREENSHOT ***/
	$(document).on("click", "#screenshot", function(){
		var i = $(this).find("i");
		var c = i.prop("class");
		i.removeClass();
		i.addClass("fa loader");
		$("#screenshot-wia").fadeIn("fast");
		contact_helpdesk(c);
	});
	
	/*** SEND SCREENSHOT TO PHP SCRIPT ***/
	$(document).on("click", "#send_screenshot", function(){
		var msg = $("#helpdesk-message").val();
		var screenshot = $("#screenshot_file").val();
		$("#page-loader").fadeIn();
		$.post(  
		 "helpers/send_screenshot.php",  
		 {msg: msg, page: pid, screenshot: screenshot, url: window.location.href },  
		 function(response){
			 $("#page-loader").hide();
			 if (response.result){	
			 	modal(response.msg, "Segnalazione inviata allo sviluppatore", "success");			
			 }else{
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
	});
	
	/*** STANDARD BUTTONS IN MODULES ***/
	// save buttons
	$(document).on("click", ".saveBtn", function(){
		var afterwards = $(this).data("after");
		// call validate form
	});

	// cancel button
	$(document).on("click", "#cancelRecordBtn", function(event){
		event.preventDefault();
		goback(); 
	});
	
	// new button
	$(document).on("click", "#newRecordBtn", function(){
		var url = "cpanel.php?pid="+pid+"&v="+v+"&a=insert";
		window.location = url;
	});
	
	// copy button
	$(document).on("click", "#copyRecordBtn", function(){ copyrecord(); });
	
	
	// Not only for add / new button, but generally for to go to other page (magnify glass for example)
	$(document).on("click", ".goto", function(){
		var param = {};
		var qry = "";
		param.pid = $(this).data("pid");
		if($(this).data("view")) param.v = $(this).data("view");
		if($(this).data("action")) param.a = $(this).data("action");
		if($(this).data("record")) param.r = $(this).data("record");
		
		jQuery.each(param, function(i,e){
			if(e != ""){
				qry += i+"="+e+"&";
			}
		});
		if(qry != ""){
			qry = qry.substr(0, qry.length-1);
			qry = "?"+qry;
		}
		window.location = "cpanel.php"+qry;
	});


	/*** STANDARD BUTTONS FOR TABLES ***/
	// On/off or publish/unpublish button
	$(document).on("click", ".onoff", function(){
		var t = $(this);
		var onoff = t.data("onoff"); // current state
		var record = t.closest("tr").attr("id");
		
		// substitute icon with loader icon
		if(onoff == "on"){
			t.removeClass("fa-toggle-on");
		}else{
			t.removeClass("fa-toggle-off");
		}
		t.addClass("loader");
		
		$.post(  
		 "calls/onoff.php",  
		 {record: record, pid: pid, onoff: onoff },  
		 function(response){
			 t.removeClass("loader");
			 if (response.result){				
				if(onoff == "on"){
					// it's on: turn it off
					t.addClass("fa-toggle-off");
					t.data("onoff", "off")
				}else{
					// it's off turn it on
					t.addClass("fa-toggle-on");
					t.data("onoff", "on")
				}
			 }else{
				 // reset original icon
				if(onoff == "on"){
					t.addClass("fa-toggle-on");
				}else{
					t.addClass("fa-toggle-off");
				}
				modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	

	});
	
	// delete button - TODO: translation
	$(document).on("click", ".delete", function(){
		if(!confirm("Sicuro di voler eliminare questo record?")) return false;
		var t = $(this);
		var tr = t.closest("tr");
		var record = tr.attr("id");
		
		// substitute icon with loader icon
		t.removeClass("fa-trash");
		t.addClass("loader");
		
		$.post(  
		 "calls/delrecord.php",  
		 {record: record, pid: pid },  
		 function(response){
			 t.removeClass("loader"); // remove spinner
			 if (response.result){				
				 // Normal no problem cancel - remove row from table
				 tr.fadeOut("fast", function(){ dtable.row('#'+record).remove().draw( false ); }); // update table info
			 }else{
				 // stop or dependencies...
				 t.addClass("fa-trash"); // reset to original trash icon
				 
				 if(response.errornum == '1'){
					 // dependencies...
					modal(response.msg, response.error, response.errorlevel, true); // set message and color of modal
					$("#modal-save").hide(); // Hide the native save button from the modal window
					 if( !$("#disable-option-btn").length ){
						 // add the disable-instead-of-delete button
						 var btnOption1 = $('<button type="button" class="btn btn-sm pull-right btn-outline"></button>');
						 btnOption1.append(response.optionDisable); // text of disable button
						 btnOption1.attr("id", "disable-option-btn"); // set id
						 btnOption1.appendTo("#message-modal .modal-footer"); // add button to modal window
						 btnOption1.on("click", function(){
							 // on click disable the record
							 delrecord(record, t, true, false, false); // record id, btn, disable, deldependencies
						 });						 
					 }
					 if( !$("#delete-option-btn").length ){
						 // add the delete-dependencies button
						 var btnOption2 = $('<button type="button" class="btn btn-sm pull-right btn-outline"></button>');
						 btnOption2.append(response.optionDeleteDependencies); // text of delete dependencies
						 btnOption2.attr("id", "delete-option-btn"); // set id of button	
						 btnOption2.appendTo("#message-modal .modal-footer"); // add button to modal window
						 btnOption2.on("click", function(){
							 // on click delete also the dependencies of  the record
							 delrecord(record, t, false, true, false); // record id, btn, disable, deldependencies
						 });
					 }
					 
				 }else if(response.errornum == '2'){
					 // reassign...
					 modal(response.msg, response.error, response.errorlevel, true); // set message and color of modal
					 $("#modal-save").text("Conferma"); 
					 $(".select2").select2({ width: '90%' });
					 $("#modal-save").on("click", function(){
						 var newListino = $("#reassign-select").val();
						  delrecord(record, t, false, true, newListino); // record id, btn, disable, deldependencies
					 });
					
					
					 
				 }else{
					 // stop...
					modal(response.msg, response.error, response.errorlevel);					 
				 }
			 }
		 },  
		 "json"  
		);  	

	});

	// checkboxes
	$(document).on("click", ".rowcheck", function(e){
		var oldIndex = indexCheckBoxes; // 0 based
		indexCheckBoxes = $( ".rowcheck" ).index( this );
		var checked = ( $(this).is(':checked') ) ? true : false;
		var checks = $( ".rowcheck" ); // all checkboxes
		if (e.shiftKey && oldIndex > -1) {
			var diff = indexCheckBoxes - oldIndex;
			if(diff > 0){
				for(var c=oldIndex; c<indexCheckBoxes; c++){
					$(checks[c]).prop("checked", checked);
				}
			}else if(diff < 0){
				for(var c=indexCheckBoxes; c<oldIndex; c++){
					$(checks[c]).prop("checked", checked);
				}
			}
    	}
		
		var allChecked = $( ".rowcheck:checked" )
		rowsChecked = new Array();
		if(allChecked.length > 0){
			$(".top-table-btn").slideDown();
			$.each(allChecked, function(i,e){
				rowsChecked[i] = $(e).val();
			});
		}else{
			$(".top-table-btn").slideUp();
			
		}
	});
	
	
/*** START INLINE-EDIT FUNCTIONS ***/	
	
	// inline edit of row data
	$(document).on("click", ".inline-edit", function(){
		// get various objects
		var t = $(this);
		var td = $(this).closest("td");
		var tr = $(this).closest("tr");
		
		// get row / record id
		var trid = tr.attr("id");
		
		// If busy adding row don't do anything
		if(t.data("stop")) return false;

		// if row is "open" clicking on the icon will save the data, else it will "open" the row
		if(t.data("open")){
		
			// Collect data from fields
			var fields = {};
			tr.find("td.inline-edit-field").each(function(i, e) {
				var newval = $(e).find(".inline-input").val();
				var fieldname = $(e).find(".inline-input").prop("name");
				fields[fieldname] = newval;				
			});
			// send data to save function
			inlineSave("update", trid, fields);
			
		}else{
			// set switch to open
			t.data("open", true);
			// change icon from pencil to check
			t.removeClass("fa-pencil");
			t.addClass("fa-check");
			// hide delete icon if any
			td.find(".delete").hide();
			// add cancel button - function delegated to dedicated function 
			td.append("<i class=\"puls cancel-inline fa fa-fw fa-ban\"></i>");
			// disable new row buttons
			$(".new-row").prop("disabled", true);
			// inject input fields - loop al tabel cells with class inline-edit-field
			tr.find("td.inline-edit-field").each(function(i, e) {
				// get class of td
				var tdclass = $(e).attr("class");
				// get all class names and put them in an array
				var s = tdclass.split(" ");
				// the field name is the first class name
				var name = s[0];
				// get the old value
				var oldval = $(e).html();
				// get the raw value
				var rawval = $(e).find("span").data("raw");
				// get the type of field (text, numeric or other)
				var type = $(e).data("inline-type");
				// create input field DOM
				switch(type){
					case "colorpicker":
						var cp = $(oldval); // span color-swatch
						var color = cp.data("color");
						if(color === undefined){
							color = "";	
						}
						var input = "<div id=\"inline-colorpicker\" class=\"input-group colorpicker-component\"> <input data-old-val='"+oldval+"' type=\"text\" value=\""+color+"\" class=\"form-control\" name=\"color\" /> <span class=\"input-group-addon\"><i></i></span> </div>";
						
						break;
					case "percent":
						var input = "<input type='text' name='"+name+"' id='"+name+"' value='"+rawval+"' data-old-val='"+oldval+"' class='inline-input form-control'>";
						break;
					case "select":
						var input = "<select name='"+name+"' id='"+name+"' class='inline-input form-control'></select>";
						var funcname = "options_" + name;
						if (typeof window[funcname] == "function"){ 
							eval(funcname + "("+trid+")");
						}
						break;
					case "multiselect":
						var selected = new Array();
						var ov = $(oldval); 
						var mitab = ov.data("tab");
						var mikey = ov.data("key");
						var mivlaue  = ov.data("field");
						var l = ov.find("span.label");
						if(l){
							$.each(l, function(i, e){
								selected[i] = $(e).data("raw");
							});
						}
						console.log(mitab)
						
						break;
					default:
						var input = "<input type='"+type+"' name='"+name+"' id='"+name+"' value='"+oldval+"' data-old-val='"+oldval+"' class='inline-input form-control'>";
						break;
				}
				
				// insert input field in table cell
				$(e).html(input);
				// set focus on first field and select content
				if(i === 0){
					$("input#"+name).focus();
					$("input#"+name).select();
				}
				
			});
			//$(function() { $('#inline-colorpicker').colorpicker(); });
		}
	});

	// inline cancel in list / tab
	$(document).on("click", ".cancel-inline", function(){
		var t = $(this);
		var tr = $(this).closest("tr");
		var td = $(this).closest("td");
		var sb = td.find(".inline-edit");
		
		// set switch to closed
		sb.data("open", false);
		// reset icon to pencil
		sb.removeClass("fa-check");
		sb.addClass("fa-pencil");
		// reshow hidden cancel button (if any)
		td.find(".delete").show();
		// enable new row buttons
		$(".new-row").prop("disabled", false);
		// reset fields
		tr.find("td.inline-edit-field").each(function(i, e) {
			var oldval = $(e).find("input").data("old-val");
			$(e).html(oldval);
			
		});
		t.remove();

	});

	// When I click the cancel button on a new row
	$(document).on("click", ".inline-cancel-new", function(){
		var t = $(this);
		// remove tr
		t.closest("tr").remove();
		// re-enable edit buttons and new-row button
		$(".inline-edit").data("stop", false);
		$(".inline-edit").css("opacity", '1');
		$(".new-row").prop("disabled", false);

	});

	// When I click the save button save data to db
	$(document).on("click", ".inline-save-new", function(){
		var t = $(this);
		var tr = $(this).closest("tr");
		
		// save fields
		var fields = {};
		tr.find("td.inline-edit-field").each(function(i, e) {
			var newval = $(e).find(".inline-input").val();
			var fieldname = $(e).find(".inline-input").prop("name");
			fields[fieldname] = newval;				
		});
		inlineSave("insert", "0", fields);
		
	});
	
	
/*** END INLINE-EDIT FUNCTIONS ***/	
	
	// copy record in <table> uses the same script as copy record from module
	$(document).on("click", ".copy", function(){
		var t = $(this);
		var tr = t.closest("tr");
		var id = tr.attr("id");

		// substitute icon with loader icon
		t.removeClass("fa-files-o");
		t.addClass("loader");
		
		// call copy-inline script passing record- and page id
		$.post(  
		 "calls/copyrecord.php",  
		 {record: id, pid: pid},  
		 function(response){
			// reset original icon
			t.addClass("fa-files-o");
			t.removeClass("loader");

			 if(response.result){
				 // reload page
				 location.reload();
			 }else{
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
		
		
	});
	
	// Show in front-end - TODO
	$(document).on("click", ".eye", function(){
		alert("Da fare...");
	});

	
	// currency field formatting on blur 
	$(document).on("blur", ".currency", function(){
		var v = $(this).val(); // get value of currency input field
		var sep = ','; // set the separator TODO: embed data-sep in field and use that one
		var n = currencyFormat(v, sep);
		$(this).val(n); // set field to the formatted currency value
	});
	
	/*** MODAL BOX ***/
	// modal message dialog-box buttons
	$(document).on("click", "#message-modal .modal button", function(){
		if($(this).data("dismiss") == "modal") $("#message-modal .modal").fadeOut("fast");		
	});

	/*** FILTER HANDLE ***/
	$(document).on("change", "#tabfilter select, #tabfilter input", function(){
		var serial = $( "#tabfilter" ).serialize();
		var url = "cpanel.php?pid="+pid+"&f=1&"+serial;
		window.location = url;
	});
	
	$(document).on("click", ".multifield .input-group .input-group-addon", function(){
		var p = $(".multifield").find(".input-group").length;
		if(p == '1'){

			if( confirm("Vuoi cancellare questa riga?") ){
				var ii = $(this).closest(".input-group").find("input");
				$(ii).val("");
				$(ii).prop("required", false);
			}else{
				return false;
			}

		}else{
		
			if( confirm("Vuoi cancellare questa riga?") ){
				$(this).closest(".input-group").remove();
			}else{
				return false;
			}
			
		}
		
	});
	
/*** MULTIFIELD ***/
	// see crocerosa/views/gestione-struttura for dom structure
	$(document).on("click", ".addmulti", function(){
		// get the div with the multi-fields
		var mf = $(this).prev().closest(".form-group.multifield");
		// find all input groups (rows)
		var ig = mf.find(".input-group");
		// the number of rows
		var ncampi = parseInt(ig.length)+1;
		// first row...
		var lig = ig[0];
		// clone first row
		var copia = $(lig).clone();
		console.log(copia);
		// empty input field
		copia.find("input").val("");
		// attrib name and id based on the data-fieldname in this button
		var fieldName = $(this).data("fieldname");
		copia.find("input").prop("name", fieldName+"["+ncampi+"]");
		copia.find("input").prop("id", fieldName+"-"+ncampi);
		console.log(ncampi);
		// append the new row
		copia.appendTo(mf);
	});

	// icheck checkboxes
	$('.icheck input').iCheck({
	  checkboxClass: 'icheckbox_square-blue',
	  radioClass: 'iradio_square-blue',
	  increaseArea: '20%' // optional
	});
	
	$(document).on("dblclick", "input[type='email']", function(e){
		e.preventDefault();
		var t = $(this).val();
		window.location.href = "mailto:"+t;
	});
	
	// TODO: creare classe per email e legare funziona a tale classe
	$(document).on("focus", "#email", function(e){
		$(this).tooltip('destroy');
		$(this).select();
	});
	
	// TODO: creare classe per email e legare funziona a tale classe + traduzione
	$(document).on("blur", "#email", function(e){
		var g = $(this).closest(".form-group");
		var v = $(this).val();
		if(v == ''){
			if($(this).prop('required')){
				$(this).attr('title', 'Email non può essere vuota').tooltip('fixTitle').tooltip('show');
				if(g.length) g.addClass("has-error");
				$(".saveRecordBtn").prop("disabled", true);
				return false;
				
			}else{
				if(g.length) g.removeClass("has-error");
				$(".saveRecordBtn").prop("disabled", false);
				return false;
			}
		}
		if(!checkEmail(v)){
			$(this).attr('title', 'Email non valida').tooltip('fixTitle').tooltip('show');			
			$(this).addClass("form-control");
			if(g.length) g.addClass("has-error");
			$(".saveRecordBtn").prop("disabled", true);
		}else{			
			if(g.length) g.removeClass("has-error");
			$(".saveRecordBtn").prop("disabled", false);			
		}
		
	});

	/*** CHAT / TICKET REPLIES ***/
	$(document).on("click", "#send-reply", function(){
		var reply = $("#reply-message");
		var msg = reply.val();
		if(msg == '') return false;
		var ticket = reply.data("ticket");
		var user = reply.data("user");
		
		$.post(  
		 "calls/reply_ticket.php",  
		 {ticket: ticket, user: user, message: msg},  
		 function(response){
			 if (response.result){
				 $("#chat-instuctions").remove();
				 $(".direct-chat-messages").prepend(response.message);
				 reply.val("");
				 if( $("#ticket-box").length ){
					 var tr = $("#ticket_row_"+ticket);
					 var tile = tr.find(".get-chat");
					 var n = parseInt(tile.text());
					 n++;
					 tile.text(n);
				 }
			 }else{
				 modal(response.msg, response.error, response.errorlevel);				 
			 }
		 },  
		 "json"  
		);  	
	});

	$(document).on("click", ".get-chat", function(){
		var t = $(this);
		var tr = $(this).closest("tr");
		var ticket = tr.data("ticket");
		var user = tr.data("user");
		var tbody = $(this).closest("tbody");
		tbody.find("tr").each(function(){ $(this).removeClass("evidenziato")});
		$.post(  
		 "calls/get_chat.php",  
		 {ticket: ticket, user: user},  
		 function(response){
			 if (response.result){
				 $("#chat-room").html(response.html);
				 tr.addClass("evidenziato");
			 }else{
				 modal(response.msg, response.error, response.errorlevel);				 
			 }
		 },  
		 "json"  
		);  	
	});
	
	$(document).on("click", ".direct-chat button.btn", function(){
		if($(this).data("widget") == "remove" && $("#ticket-box").length){
			var tbody =  $("#ticket-box").find("tbody");
			tbody.find("tr").each(function(){ $(this).removeClass("evidenziato")});
		}
	});
	
	
});

function modal(content, title, level, savebutton, hidebuttons, width = false){
	// define default message title and warning level in case it's not send to the function
	if(content != undefined && content != ""){
		var message = content;
	}else{
		var message = "Something went wrong...";
	}
	if(title != undefined && title != ""){
		var t = title;
	}else{
		var t = "Attention!";
	}
	if(level != undefined && level != ""){
		var l = level;
	}else{
		var l = "warning";
	}
	if(savebutton == undefined || savebutton == "" || !savebutton){
		var b = false;
	}else{
		var b = true;
	}
	if(hidebuttons == undefined || hidebuttons == "" || !hidebuttons){
		var hb = false;
	}else{
		var hb = true;
	}
	
	// get dom
	var holder = $("#message-modal");
	var obscure = holder.find(".modal");
	var dialog = obscure.find(".modal-content");
	var bodysection = dialog.find(".modal-body");

	// set color of close button based on level
	if(level == "default"){
		$(".modal-footer .btn").removeClass("btn-outline");
		$("#modal-close").addClass("btn-danger");
		$("#modal-cancel").addClass("btn-danger");
		$("#modal-save").addClass("btn-success");
	}else{
		$(".modal-footer .btn").addClass("btn-outline");
		$("#modal-close").removeClass("btn-danger");
		$("#modal-cancel").removeClass("btn-danger");
		$("#modal-save").removeClass("btn-success");
	}

	// remove warning level	
	obscure.removeClass (function (index, css) {
		return ( css.match(/\bmodal-\S+/g) || []).join(' ');
	});
		
	// set warning level
	obscure.addClass("modal-"+l);
	// set title
	dialog.find(".modal-title").html(t);	
	// set message 
	bodysection.find("div").html(message);
	// activate / diactivate buttons in footer
	if(b){
		$("#modal-close").hide();
		$("#modal-save").show();
		$("#modal-cancel").show();
	}else{
		$("#modal-close").show();
		$("#modal-save").hide();
		$("#modal-cancel").hide();
	}
	if(hb){
		$(".modal-footer buttons").hide();
		$(".modal-footer").hide();
	}
	
	// set default text in buttons 
	$("#modal-close").text($("#modal-close").data("label")); 
	$("#modal-save").text($("#modal-save").data("label"));
	$("#modal-cancel").text($("#modal-cancel").data("label"));
		
	// let it show...
	obscure.fadeIn("fast");
	
	// set vertical position of box
	var sh = $(window).height();
	var dh = $(".modal-content").outerHeight(false);
	var center = (parseFloat(sh/2))-(parseFloat(dh/2)); 
	dialog.css("margin-top", center+"px");
	
	if(width){
		obscure.find(".modal-dialog").css("width", width);
	}
		
}

function pageAlert(title, content, level, closable, id){
	
	if(content != undefined && content != ""){
		var message = content;
	}else{
		var message = "Something went wrong...";
	}
	if(title != undefined && title != ""){
		var t = title;
	}else{
		var t = "Attention!";
	}
	if(level != undefined && level != ""){
		var l = level;
	}else{
		var l = "warning";
	}
	if(closable != undefined && level != ""){
		var c = closable;
	}else{
		var c = true;
	}
	if(id != undefined && id != ""){
		var i = " id=\""+id+"\"";
	}else{
		var i = "";
	}
	
	if(i != ""){
		if( $("#page_alerts #"+id).length ) $("#page_alerts #"+id).remove()
	}
	
	if(level != "success" && level != "danger" && level != "warning" && level != "info") level = "warning";
	
	switch(level){
		case "success":
			var icon = "check";
			break;
		case "info":
			var icon = "info";
			break;
		case "warning":
			var icon = "warning";
			break;
		case "danger":
			var icon = "ban";
			break;
	}
	
	var dismissable = (c) ? "alert-dismissable" : "";
	var button = (c) ? "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" : "";
	var titolo = "<h4><i class=\"icon fa fa-"+icon+"\"></i>"+t+"</h4>";
	var corpo = "<p>"+message+"</p>";
	
	var b = "<div"+i+" class=\"alert alert-"+level+" "+dismissable+"\">\n";
	b += button+"\n";
	b += titolo+"\n";
	b += corpo+"\n";
	b += "<div>\n";
	var block = $(b);
	block.hide();		
	
	// append block or inject
	if(!$("#page_alerts").length){
		var section = "<section class=\"content-header\" id=\"page_alerts\">\n</section>\n";
		$('.content-header + .content').before(section);
	}
	$("#page_alerts").append(block);
	block.slideDown("fast");
	
}

 function goback(){
	 // let's see if we've got a pid to go back to
	 if( isNaN(parseInt(gb))){ // gb is defined in cpanel.php
		 modal(gb, "", "warning");
	 }else{
		var url = "cpanel.php?pid="+gb+"&v="+v;
		window.location = url;		 
	 }
 }
 
  function copyrecord(){
	var i = $(this).find("i");
	i.removeClass("fa-copy");
	i.addClass("loader");  
	$.post(  
	 "calls/copyrecord.php",  
	 {record: r, pid: pid },  
	 function(response){
		 
		 if (response.result){				
			// reload page 
			var url = "cpanel.php?pid="+pid+"&v="+v+"&r="+response.record+"&a=update";
			window.location = url;		 
		 }else{
			 // reset original icon
			 i.removeClass("loader");
			 i.addClass("fa-copy");
			modal(response.msg, response.error, response.errorlevel);
		 }
	 },  
	 "json"  
	);  	
 }
 
 
function validateForm(form){
	alert("JS validation & ajax send : todo!");
}

function highlighField(field, fadeout, color){
	if(field != undefined && field != ""){
		field = "#"+field;
	}else{
		console.log("no field set");
		return false;
	}
	if(fadeout != undefined && fadeout != ""){
		var f = true;
	}else{
		var f = false;
	}
	if(color != undefined && color != ""){
		var c = color;
	}else{
		var c = '#ff6';
	}
	
	if( !$(field).length ) return false;
	
	var oldbgc = $(field).css("background-color");

	
	if(f){
		$(field).animate({
			backgroundColor: c
		}, 500);
		setTimeout(function(){ 
		
			$(field).animate({
				backgroundColor: oldbgc
			}, 800);
		
		
		}, 1100);
	}else{
		$(field).css("background-color", c);
	}
	
	
}

function take_screenshot(){
	html2canvas(document.body, {  
		onrendered: function(canvas){
			var img = canvas.toDataURL()
			$.post("helpers/save_screenshot.php", {data: img}, function (file){
				window.location.href =  "helpers/save_screenshot.php?file="+ file
			});
		}
	});
}

function contact_helpdesk(icon_class){
	html2canvas(document.body, {  
		onrendered: function(canvas){
			var img = canvas.toDataURL();
			$.post("helpers/save_screenshot.php", {data: img, pid: pid}, function (file){
				$("#screenshot-wia").fadeOut("fast");
				var i = $("#screenshot").find("i");
				i.removeClass();
				i.addClass(icon_class);
				
				// module
				html  = '<div>';
				html += '<div class="row"><div class="col-md-12"><div class="form-group">';
				html += '<textarea id="helpdesk-message" placeholder="Scrivi eventuali commenti..." rows="5" class="form-control"></textarea>';
				html += '</div></div></div>';
				html += '<div class="row"><div class="col-md-12"><div class="form-group">';
				html += '<button class="btn btn-success" id="send_screenshot"><i class="fa fa-check"></i>  Invia</button>';
				html += '<input type="hidden" id="screenshot_file" name="screenshot_file" value="'+file+'">';
				html += '</div></div></div>';
				html += '</div>';
				modal(html, "Invia segnalazione allo sviluppatore", "default");
			});
		}
	});
}

function inlineSave(action, trid, inlinedata){
	if(action != "update" && action != "insert") return false;
	if(action == "update"){
		// get tr object
		var tr = $("tr#"+trid);
		// get save button object
		var sb = tr.find(".inline-edit");
		// cancel button
		var cb = tr.find(".cancel-inline");
	}else{
		// get tr object
		var tr = $("tr#newrow");
		// get save button object
		var sb = tr.find(".inline-save-new");
		// cancel button
		var cb = tr.find(".inline-cancel-new");		
	}
	// change icon to loader
	sb.removeClass("fa-check");
	sb.addClass("fa-spin fa-circle-o-notch");
	sb.data("stop", true);
	// hide cancel button
	cb.hide();

	$.post(  
		"required/write2db.php",  
		{pid: pid, record: trid, action: action, _qta: "1", save: "ajax", inlinedata: inlinedata},  
		function(response){
			
			// change icon to loader
			sb.removeClass("fa-spin fa-circle-o-notch");
			sb.addClass("fa-check");
			sb.data("stop", false);
			
			
			if (response.result){
				
				if(action == 'insert'){
					location.reload();
					return false;
				}
				
				var returnValues = response.values;

				tr.find("td.inline-edit-field").each(function(i, e) {
					var k = $(e).find(".inline-input").attr("name");
					var v = returnValues[k];
					if(v === undefined) v = inlinedata[k];
					console.log(v);
					$(e).html(v);
				});
				
				// set switch to closed
				sb.data("open", false);
				// reset icon to pencil
				sb.removeClass("fa-check");
				sb.addClass("fa-pencil");
				// remove cancel button
				cb.remove();
				// reshow hidden cancel button (if any)
				tr.find(".delete").show();
				// enable new row buttons
				$(".new-row").prop("disabled", false);
				
			}else{
				// reshow cancel button
				cb.show();
				// output error
				modal(response.msg, response.error, response.errorlevel);
				/*
				for(var q=0; q<response.msg.length; q++){
					modal(response.msg[q], response.error[q], response.errorlevel[q]);
				}
				*/
			}
		},  
		"json"  
	);  	
	
}

// restituisce coordinate sinistra
function centerDiv(div){ // div = $() object
	var w = div.outerWidth();
	var sw = $(document).width();
	var sx = (sw/2)-(w/2);
	return sx;
}

// function called mainly when the user must choose between deleting dependencies of the record or only disable the record
function delrecord(record, t, disable, deldependencies, reassignvalue){
	setLoadStatus(t); // Add loader class to button (da cambiare, vedi funzione sotto)
	var tr = t.closest("tr"); // la riga della tabella dove risiede il record
	
	// richiama lo script di cancellazione passando le opzioni in base alla scelta fatta nel modal
	$.post(  
	 "calls/delrecord.php",  
	 {record: record, pid: pid, disable: disable, deldependencies: deldependencies, reassignvalue: reassignvalue },  
	 function(response){
		 restoreBtnStatus(t); // tolgo loader class da pulsante che ho cliccato
		 if(response.result){
			 
			 if(disable){
				 var swtch = tr.find("td.active-column-name").find("i");
				 if(swtch.length){
					 swtch.removeClass("fa-toggle-on").addClass("fa-toggle-off");
					 swtch.data("onoff", "off");
				 }
			 }else{
				 // ok remove row
				 tr.fadeOut("fast", function(){ $(this).remove(); });				 
			 }
			 // remove modal
			 $("#modal-cancel").trigger("click");
		 }else{
			 // something went wrong - display message
			 modal(response.msg, response.error, "default", true);
			 $("#disable-option-btn").remove();
			 $("#delete-option-btn").remove();
		 }
	 },  
	 "json"  
	); 	
	
}

// ADD LOADER CLASS TO A BUTTON
function setLoadStatus(btn){ // btn is dom object of button
	var i = btn.find("i");
	if(!i.length) return false;
	var c = i.attr("class");
	var si = c.match(/fa-[a-z-]+/); 
	if(si[0]){
		btn.data("ori-icon", si[0]);
		i.removeClass(si[0])
	}
	i.addClass("loader");
	btn.prop("disabled", true);
}

// REMOVE LOADER CLASS FROM BUTTON
function restoreBtnStatus(btn){
	var i = btn.find("i");
	if(!i.length) return false;
	i.removeClass("loader");
	var c = btn.data("ori-icon");
	if(c){
		i.addClass(c);
		btn.data("ori-icon", "");
	}
	btn.prop("disabled", false);
}

// get all the values of the checked checkbox in tables
function getCheckedRows(){
	var allChecked = $( ".rowcheck:checked" );
	rowsChecked = new Array();
	if(allChecked.length > 0){
		$.each(allChecked, function(i,e){
			rowsChecked[i] = $(e).val();
			console.log($(e).val())
		});

	}	
}

// uncheck all the checkboxes in tables
function uncheckRows(closeRow = true){
	var allChecked = $( ".rowcheck:checked" );
	rowsChecked = new Array();
	if(allChecked.length > 0){
		$.each(allChecked, function(i,e){
			$(e).prop("checked", false);
		});
		// close button tray
		if(closeRow) $(".top-table-btn").slideUp();
	}	
}

// check all the checkboxes in tables
function checkRows(){
	var allRows = $( ".rowcheck" );
	rowsChecked = new Array();
	if(allRows.length > 0){
		$.each(allRows, function(i,e){
			$(e).prop("checked", true);
			rowsChecked[i] = $(e).val();
		});
	}	
}

// uncheck all the checkboxes in tables
function highlightCheckedRows(){
	var allChecked = $( ".rowcheck:checked" );
	if(allChecked.length > 0){
		$.each(allChecked, function(i,e){
			var tr = $(e).closest("tr");
			tr.addClass("hilite");
		});
	}	
}

function currencyFormat(v, sep){
	v = (v + '').replace(/[^0-9., ]/g, ""); // remove anything that's not a number, a comma, a dot or a space
	v = v.trim(); // remove whitespaces at start and end
	v = v.replace(/[., ]/g, sep); // replace commas, dots and spaces (in between) with a separator sign		
	var p = v.split(sep); // split string by separator sign
	if(p.length > 1){
		var d = p.pop(); // get decimals
		d = myRound(d, 2); // round and truncate decimals to two (p.e. 2345 becomes 24)
		var i = p.join(""); // join all the remaining parts so that there are no other symbols other then numbers in the int part
		var n = i+"."+d; // create formatted value using sep var as dec separator		
	}else{
		var n = p[0]+"."+'00'; // whole number, add dec separtor and eros		
	}
	n = number_format(n, 2, sep, "");
	return n;

}

// performs a digit by digit analysis to return the correct rounded float
function realRound(num, length){
	var a = 0;
	num = parseFloat(num);
	var div = Math.pow(10, length);
	num 	= num*div;
	var s 	= num.toString().split(".");
	var int = parseInt(s[0]);
	var dec = parseInt(s[1]);  
	var l 	= dec.toString().length;
	for(p=l; p > 0; p--){
		// n is single digit already augmented by one if previous digit was 5 or higher
		var n = parseInt(dec.toString().substr(p-1, 1))+a;
		a = (n > 4) ? 1 : 0;
	}
	return (int + a) / div;
}

// serve per currency prenso un numero intero e lo tronco arrottondato a dimensione length (p.e. num = 2345, l = 2, da come output 24)
function myRound(num, length){
	var l = (num.toString().length)-length; // length of number minus the length param
	if(l < 0) l = 0;
	div = Math.pow(10, l); // calculate the divider by elevating 10 to the value of l 
	num = num / div; // divide the number the divider
	var real = realRound(num, 0); // pass the floating number optained to the realRound function with dec param set to 0
	
	if(real < div){
		// if the result in length is shorter than the length param add leading zeros
		var zeros =  Array(length).join("0")
		var slce = length*-1;
		real = String(zeros+ real).slice(slce); // returns 00123
	}
	return real;
	
}

// PHP-like number_format
function number_format (number, decimals, decPoint, thousandsSep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
  var n = !isFinite(+number) ? 0 : +number
  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
  var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
  var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
  var s = ''

  var toFixedFix = function (n, prec) {
    var k = Math.pow(10, prec)
    return '' + (Math.round(n * k) / k)
      .toFixed(prec)
  }

  // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || ''
    s[1] += new Array(prec - s[1].length + 1).join('0')
  }

  return s.join(dec)
}

function getNotifications(){
	$.post(  
	 "calls/notifications.php",    
	 {page: pid},  
	 function(response){
		 // notifications to show always (in menu tree)
		 if (response.menuNotifications){
			 // loop for every menu item that has to bear a notification
			 $.each(response.menuNotifications, function(id,value){
				 $("#"+id).find(".notification").remove();
				 $("#"+id).find("a").append(value);				 
			 });
		 }
		 // notifications to show only when on a specific page
		 if(response.pageNotifications){
			 // call function stored in page-specific file
			 window[response.pageFunction](response.pageNotifications);
		 }
		 if(response.force_logout){
			 // superadmin has ended this session, redirect to logout
			 window.location.href = 'logout.php';
		 }
	 },  
	 "json"  
	);  	
}

function checkEmail(v){
	v = v.trim();
	var rule = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	if(rule.test(v)){
		return true;
	}else{
		return false;
		
	}
	
}