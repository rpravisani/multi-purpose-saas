$(document).ready(function() {
	
	// quando clicco su pulsante Cambia... mostra anche i disponibili non solo le squadre
	$(document).on("click", ".step .cambia", function(){
		$(this).closest("div.step").find(".step-option").each(function(){
			if(opzioni['tipo_uscita'] == '1'){
				// se urgenza mostro tutti...
				$(this).parent("div").show();				
			}else{
				// se no solo quelli non-squadra
				$(this).parent("div:not(.squadra)").show();
			}
		});
	});

	// quando clicco su un blocco nello step 2 (dettagli)
	$(document).on("click", ".step div.step-2", function(){
		if(opzioni['tipo_uscita'] == '2' || opzioni['tipo_uscita'] == '3'){ // servizi programmati e sociali
			// mostra solo veicoli per strade strette se il paziente ha in anagrafica spuntato strade_strette
			var ss = $(this).data("strada-stretta");
			if(ss == '1'){
				$('.step-3').closest("div.col-md-4").hide();
				$('.step-3.strette').closest("div.col-md-4").show();
			}else{
				// mostro tutti i veicoli (fossi tornato indietro)
				$('.step-3').closest("div.col-md-4").show();
				
			}
			var militi = $(this).data("militi"); // formato a|m1|m2
			var mostra_militi = militi.split("|"); // 1° autista - 2° milite1 - 3° milite2
			console.log(militi);
			// mostro tutti i militi e nascondo squadre (fossi tornato indietro)
			switchSquadra(false);

			// mostro solo autista preposto (se non a zero)
			if(mostra_militi[0] != '0'){
				$('.step-4').closest("div.col-md-4:not(.salta-holder)").hide();
				$('.step-4[data-value="'+mostra_militi[0]+'"]').closest("div.col-md-4").show();
			}
			// mostro solo milite preposto (se non a zero)
			if(mostra_militi[1] != '0'){
				$('.step-6').closest("div.col-md-4:not(.salta-holder)").hide();
				$('.step-6[data-value="'+mostra_militi[1]+'"]').closest("div.col-md-4").show();
			}
			// mostro solo milite preposto (se non a zero)
			if(mostra_militi[1] != '0'){
				$('.step-7').closest("div.col-md-4:not(.salta-holder)").hide();
				$('.step-7[data-value="'+mostra_militi[2]+'"]').closest("div.col-md-4").show();
			}
			
		}
	});
	
	// quando clicco su puls conferma nello step 2 (dettagli) - dimissioni
	$(document).on("click", "#conferma-dimissioni", function(){
		// prendo valori dei 4 campi e li inserisco in opzioni
		var paziente = $("#paziente-select").val();
		var struttura = $("#struttura-select").val();
		var reparto = $("#reparto-select").val();
		var destinazione = $("#destinazione-dimissione").val();
		if(paziente == '' || struttura == '' || destinazione == ''){ // reparto optional
			alert("Compila tutti i campi con l'asterisco (*)");
			return false;
		}
		// memorizza valori
		opzioni['paziente']  = paziente;
		opzioni['struttura'] = struttura;
		opzioni['reparto'] 	 = reparto;
		opzioni['prelevato'] = destinazione; // chiave opzioni deve corrispondere con vlaore in tab fono
		
		var nome_paziente = $("#paziente-select option:selected").text();
		var nome_struttura = $("#struttura-select option:selected").text();
		var nome_reparto = $("#reparto-select option:selected").text();

		// set text in option header
		var h1 = "<i class=\"fa fa-user\"></i>"+nome_paziente+" <i class=\"fa fa-map-marker\"></i>"+nome_struttura;
		if(reparto != '') h1 += " / "+nome_reparto;
		h1 += " <i class=\"fa fa-chevron-right\"></i>"+destinazione;
		$(steps[1]).find(".chosen-option").html(h1);								
		
		// store selected options in session as json string
		sessionStorage.opzioni = JSON.stringify(opzioni);
		
		// if next is defined go to next step
		$(activeStep).addClass("completed-step"); // mark this step as completed
		completed[2] = h1;
		sessionStorage.completed = JSON.stringify(completed);
		showStep(3); // show next step
		
	});
	
	// quando clicco su puls conferma nello step 2 (dettagli) - Uscita Extra
	$(document).on("click", "#conferma-uscita-extra", function(){
		var note = $("#motivo-uscita").val();
		if(note == ''){ 
			alert("Fornire una descrizione del motivo dell'uscita!");
			return false;
		}
		// memorizza valori
		opzioni['note'] = note;

		// set text in option header
		var h1 = note.substr(0, 120);
		if(h1 != note) h1 += "...";
		$(steps[1]).find(".chosen-option").html(h1);								
		
		// store selected options in session as json string
		sessionStorage.opzioni = JSON.stringify(opzioni);
		
		// if next is defined go to next step
		$(activeStep).addClass("completed-step"); // mark this step as completed
		completed[2] = h1;
		sessionStorage.completed = JSON.stringify(completed);
		showStep(3); // show next step
	});
	
	
	
});

/*** Le funzioni stepx vengono eseguite su entrata dello step, ovvero quando questi vengono mostrati ***/
function step2(){
	if(opzioni['tipo_uscita'] == '1'){ // Se urgenza...
		switchSquadra(true); // mostro solo membri della squadra - vedi func in fondo
		showStep(3); // se urgenza salto lo step 2 (dettagli) e vado direttamente a step 3 (veicolo)
	}else{
		// mostra il div appropriato
		var opzione = $(steps[0]).find(".chosen-option").text().toLowerCase();
		opzione = opzione.replace(" ", "-");
		var div = "dettagli-"+opzione;
		$("#"+div).show();
		if(opzioni['tipo_uscita'] == '4') {

			$(".select2").select2({
				"language": { 
					"noResults": function(){ 
						return "Nessun risultato trovato <div class='btn btn-primary btn-xs pull-right add-select2-value'><i class=\"fa fa-plus\"></i>&nbsp;&nbsp;Inserisci.</div>";
					}
				}, 
				escapeMarkup: function (markup) { 
					return markup;
				}			
			});			
			
			
		}
		
	}
}


// step del capo squadra
function step5(){
	if(opzioni['tipo_uscita'] == '2' || opzioni['tipo_uscita'] == '3'){ showStep(6); } // se servizi prog o soc salto capo squadra
	/* tolto perché autista è assestante, non appare tra i militi
	var k = opzioni['autista'];
	$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[4]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
	*/
}
// step del milite 1
function step6(){
	/* tolto perché autista è assestante, non appare tra i militi
	var k = opzioni['caposquadra'];
	$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[4]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
	*/
}
// step del milite 2
function step7(){
	var k = opzioni['milite1'];
	//$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	//$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[6]).find("[data-value='" + k + "']").parent("div").hide();
}
// step di conferma
function step8(){
	var k = opzioni['milite2'];
	//$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	//$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
	
	// mostra risultati
	var t = $(".wizard > div.step h3 .chosen-option");
	var result = "<table class='table table-condensed'>";
	
	var classe_int = $(t[0]).text().toLowerCase();
	result += "<tr><th>Tipo intervento</th><td><span class=\"label "+classe_int+"\">"+$(t[0]).text()+"</span></td></tr>";
	if( $(t[1]).text() != "" ) result += "<tr><th>Servizio</th><td><span class=\"label bg-teal\">"+$(t[1]).html()+"</span></td></tr>";
	result += "<tr><th>Automezzo</th><td><span class=\"label bg-gray\">"+$(t[2]).text()+"</span></td></tr>";
	result += "<tr><th>Autista</th><td><span class=\"label bg-red\">"+$(t[3]).text()+"</span></td></tr>";
	if( $(t[4]).text() != "" ) result += "<tr><th>Caposquadra</th><td><span class=\"label bg-yellow\">"+$(t[4]).text()+"</span></td></tr>";
	result += "<tr><th>Militi</th><td><span class=\"label bg-blue\">"+$(t[5]).text()+"</span> <span class=\"label bg-blue\">"+$(t[6]).text()+"</span></td></tr>";
	result += "</table>";
	$("#result").html(result);
}


function wizardEndCustom(){
	
	$("#page-loader").show();

	// save to DB
	$.post(  
		"required/write2db.php",  
		{pid: pid, record: '', action: "insert", _qta: "1", save: "ajax", inlinedata: opzioni},  
		function(response){
			
			$("#page-loader").hide();
			
			if (response.result){

				$(".completed-step").removeClass("completed-step");
				$(".wizard .step").hide();
				$("#fine-wizard").find("h1").html(response.fono);
				$("#fine-wizard").show();

				$('html, body').animate({
					scrollTop: $("#fine-wizard").offset().top
				}, 200);
				
				
			}else{
				// output error
				modal(response.msg, response.error, response.errorlevel);
			}
		},  
		"json"  
	);  		
}

function switchSquadra(mostra){
	var steps = new Array( 4,5,6,7 );
	$.each(steps, function(i,e){
		if($('.step-'+e).closest("div.col-md-4.squadra").length){
			if(mostra){
				// mostro squadra - nascondo resto
				$('.step-'+e).closest("div.col-md-4").hide();
				$('.step-'+e).closest("div.col-md-4.squadra").show();
				$('.step-'+e).closest("div.col-md-4.salta-holder").show();				
			}else{
				// nascondo squadra - mostro il resto
				$('.step-'+e).closest("div.col-md-4").show();
				$('.step-'+e).closest("div.col-md-4.squadra").hide();											
				$('.step-'+e).closest("div.col-md-4.salta-holder").show();				
			}
		}
	})
	
	
}