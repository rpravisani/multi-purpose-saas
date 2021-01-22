$(document).ready(function() {
	
	
	// prepare var which will hold all clicked options
	opzioni = {};
	completed = {};
	persistance = false;
	
	// get all the steps dom
	steps = $(".wizard").find(".step");
	activeStepIndex = 1; // default value;
	
	initialize();
		
	// open the first active step
	showStep(activeStepIndex);
	
	// click on a option inside the step
	$(document).on("click", ".step-option", function(){
		var k = $(this).data("option"); // key of the clicked option
		var v = $(this).data("value"); // value of the clicked option

		// functional data
		var current = parseInt($(this).data("step")); // current step (don't realy use it for now)
		var next = $(this).data("next"); // where to go next (number of step - not the index of global var steps)
		
		opzioni[k] = v; // add the key value pair to global var options (object)
		
		// let's see if there was an other option already selected, in that case remove the selected-option class
		var parent = $(this).closest(".step");
		
		$(parent).find("[data-option='" + k + "']").removeClass("selected-option");
		
		$(this).addClass("selected-option");
		
		var h1 = (v) ? $(this).find("h1").text() : "--";
		$(steps[current-1]).find(".chosen-option").text(h1);
								
		
		
		// stora selected options in session as json string
		sessionStorage.opzioni = JSON.stringify(opzioni);
		
		// if next is defined got to next step
		if(next != null){
			$(activeStep).addClass("completed-step"); // mark this step as completed
			completed[current] = h1;
			sessionStorage.completed = JSON.stringify(completed);
			showStep(next); // show next step
		}
		
		
	});
	
	// click on title of one of the previous steps...
	$(document).on("click", ".completed-step h3", function(){
		
		var parent = $(this).closest(".step"); // get the div which holds the whole step
		var step = parseInt(parent.data("step")); // get the number of the clicked step
		 
		showStep(step)
		
	});
	
});

function showStep(step){
	
	if(step == null) return false; // redondant but still...
	
	// set global var activeStepIndex and activeStep
	activeStepIndex = step-1; // index is 0 based, so subtract 1 from step value 
	sessionStorage.setItem("activeStepIndex", step);
	activeStep = steps[activeStepIndex];
	
	// remove class active-step from active-step item...
	$(".wizard .step.active-step").removeClass("active-step"); 
	$(activeStep).addClass("active-step"); // ...and add it to the next step to make it show
	
	// check if we reached the end of the wizard
	if(step > steps.length){ // was : current >= steps.length
		// if next step is bigger then the number of totale steps execute wizardEnd() function
		wizardEnd(); 
	}else{
		// if inbetween step execute page-custom function stepX (where X is current step +1) if one is defined
		
		$('html, body').animate({
			scrollTop: $(activeStep).offset().top
		}, 200);
		
		var fn = "step" + step;
		if (typeof window[fn] === 'function') { 
		  window[fn](); 
		}
	}
}

function wizardEnd(){
	sessionStorage.clear();
	var fn = "wizardEndCustom";
	if (typeof window[fn] === 'function') { 
	  window[fn](); 
	}else{
		$(".completed-step").removeClass("completed-step");
		$("#page-loader").show();
		setTimeout(function(){
			$(".wizard .step").hide();
			$("#fine-wizard").show();
			$("#page-loader").hide();
		}, 1000);
	}
	
}

function initialize(){
	if(!persistance) sessionStorage.clear();
	// set the active / open step
	activeStepIndex = ( sessionStorage.activeStepIndex ) ? sessionStorage.activeStepIndex : 1;
	
	// populate the global opzioni var
	opzioni = ( sessionStorage.opzioni ) ? JSON.parse(sessionStorage.opzioni) : opzioni;
	
	// set the global completed var with default values (false)
	for(var c=0; c<steps.length; c++){
		completed[c+1] = false;
	}
	
	// retieve completed values from session if any
	completed = ( sessionStorage.completed ) ? JSON.parse(sessionStorage.completed) : completed;
	$.each(completed, function(step, v){
		if(v){
			$(".wizard").find("[data-step='" + step + "']").addClass("completed-step");
			$(steps[step-1]).find(".chosen-option").text(v);
			var h1 = $(steps[step-1]).find("h1:contains('"+v+"')");
			h1.closest("div").addClass("selected-option");
		}
	});
	
}

// TODO: da spostare in javascript page-specific
function step4(){
	var k = opzioni['autista'];
	$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[4]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
}

function step5(){
	var k = opzioni['caposquadra'];
	$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[4]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
}

function step6(){
	var k = opzioni['milite1'];
	$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[5]).find("[data-value='" + k + "']").parent("div").hide();
}


function step7(){
	var k = opzioni['milite2'];
	$(steps[2]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[3]).find("[data-value='" + k + "']").parent("div").hide();
	$(steps[4]).find("[data-value='" + k + "']").parent("div").hide();

	var result = "";
	for(var k in opzioni){
		var v = opzioni[k];
		result += k+" : "+v+"<br>\n";
	}
	$("#result").html(result);
}

function wizardEndCustom(){
	// save to DB
	$("#page-loader").show();

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