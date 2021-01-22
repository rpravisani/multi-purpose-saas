
$(document).ready(function() {
	
	// nascondi pulsanti sotto a listino
	$(".modify-column").hide();
	
	// initiate popover
	$(function () {
  		$('[data-toggle="popover"]').popover(
			{content: function(){ 
				
				var colname = $(this).data("colname");
				
				var content = "<input type='hidden' name='colname' id='colname' value='"+colname+"'>";
				content += "<p><label><input type='radio' name='tipo-modifica' id='tipo-modifica-perc' value='perc'> Incrementa / diminuisci i prezzi del</label> <input type='text' name='perc' id='perc' > %</p>";
				content += "<p><label><input type='radio' name='tipo-modifica' id='tipo-modifica-valore' value='valore'> Incrementa / diminuisci i prezzi di</label> <input type='text' name='valore' id='valore' > €</p>";
				content += "<p><label><input type='radio' name='tipo-modifica' id='tipo-modifica-imposta' value='imposta'> Imposta tutti i prezzi a</label> <input type='text' name='imposta' id='imposta' > €</p>";
				content += "<p><button  class='btn btn-sm btn-success' id='applica'>Applica</button> <button class='btn btn-sm btn-danger' id='close-popover'>Annulla</button></p>";
								
				return content;
				
			} 
		});
	});
	
	// quando clicco su campo testuale automaticamente spunta radiobox
	$(document).on("click", ".popover input[type='text']", function(){
		var id = $(this).prop("id");
		var chk = $("#tipo-modifica-"+id);
		chk.prop("checked", true);
	});
	
	// quando spunto radiobox attiva automaticamente campo testuale
	$(document).on("change", ".popover input[type='radio']", function(){
		var val = $(this).val();
		var inp = $("#"+val);		
		inp.focus();
	});	
	
	
	// Chiudo popover quando clicco su pulsante annulla in popover stesso
	$(document).on("click", "#close-popover", function(){
		$('[data-toggle="popover"]').popover("hide");
	});
	
	// applica aumento / diminuzione ai campi della colonna
	$(document).on("click", "#applica", function(){
		var selected = $(".popover input[name='tipo-modifica']:checked").val();
		if(selected === undefined) return false;
		var valore = $("#"+selected).val();
		if(valore === "") return false;
		var colname = $("#colname").val();
	
		valore = parseFloat(valore);
		if(isNaN(valore)) return false;
		
		switch(selected){
			case "perc":
				
				$("table td input[data-colname='"+colname+"']").each(function(){
					var oldval = parseFloat($(this).data("value"));
					var newval = oldval + ((oldval/100)*valore);
					$(this).val(newval);
					$(this).trigger("blur");
					$(this).addClass("grid-changed");
				})
				break;
				
			case "valore":
				
				$("table td input[data-colname='"+colname+"']").each(function(){
					var oldval = parseFloat($(this).data("value"));
					var newval = oldval+valore;
					$(this).val(newval);
					$(this).trigger("blur");
					$(this).addClass("grid-changed");
				})
				break;
				
			case "imposta":
				if(valore < 0) return false;
				$("table td input[data-colname='"+colname+"']").each(function(){
					$(this).val(valore);
					$(this).trigger("blur");
					$(this).addClass("grid-changed");
				})
				break;
		}
		$('[data-toggle="popover"]').popover("hide");
		okayToLeave = false;
		
	});
	
	
	// quando metto a fuoco su una colonna attiva seleziono contenuto
	$(document).on("focus", ".grid-active", function(){
		$(this).select();
	});

	// quando tolg fuoco da colonna attiva imposto classe
	$(document).on("change", ".grid-active", function(){
		$(this).removeClass("grid-saved").addClass("grid-changed");
		okayToLeave = false;
	});
	
	// Attivo modifica griglia
	$(document).on("click", "#activate-cells", function(){
		var t = $(this);
		t.hide();
		$("#save-grid").show();
		$("#reset-grid").show();
		$(".modify-column").show();
		$(".grid-cell").prop("disabled", false).removeClass("grid-saved").addClass("grid-active");
	});

	// Annullo e resetto valori
	$(document).on("click", "#reset-grid", function(){
		$("#save-grid").hide();
		$("#reset-grid").hide();
		$("#activate-cells").show();
		$(".modify-column").hide();
		
		$(".grid-changed").each(function(){
			$(this).val( $(this).data("value") );
			$(this).trigger( "blur" );
		});		
		$(".grid-cell").prop("disabled", true).removeClass("grid-saved").removeClass("grid-active").removeClass("grid-changed");
		okayToLeave = true;
	});
	
	// Annullo e resetto valori
	$(document).on("click", "#save-grid", function(){
		
		var t = $(this);
		var c = $("#reset-grid");
		
		if( $(".grid-changed").length = 0 ) return false;
		var kv = {}
		$(".grid-changed").each(function(i, e){
			kv[$(this).prop("id")] = $(this).val();
		});
		
		// disable save button and set loader icon
		var classSaveBtn = t.find("i").prop("class");
		t.find("i").removeClass().addClass("fa fa-refresh fa-spin");
		t.prop("disabled", true);
		
		c.prop("disabled", true);
		
		$.post(  
		 "calls/save_grid.php",  
		 {values: kv, pid: pid},  
		 function(response){
			 
			 // Reactivate save button
			t.find("i").removeClass().addClass(classSaveBtn);
			t.prop("disabled", false);
			 
			 // reactive cancel button
			c.prop("disabled", false);
			 
			 if (response.result){
				 
				 $.each(response.updated, function(i, status){
					 var iclass = (status == "KO") ? "grid-error" : "grid-saved";
					 $("#"+i).addClass(iclass);
					 $("#"+i).data("value", status);
				 });
				$(".grid-cell").prop("disabled", true).removeClass("grid-active").removeClass("grid-changed");
				$("#save-grid").hide();
				$("#reset-grid").hide();
				$("#activate-cells").show();
				$(".modify-column").hide();
				okayToLeave = true;
				 
				 var funzione = window[response.callfunc];
				 if (typeof funzione == 'function'){					
					 funzione();					
				 }
				 
			 }else{
				 modal(response.msg, response.error, response.errorlevel);
			 }
		 },  
		 "json"  
		);  	
		
		
		
	});
	
	
	
});

