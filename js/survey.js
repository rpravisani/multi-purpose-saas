$(document).ready(function() {
	
	/*** TODO: Trasformare procedurale in plugin jQuery ***/
	
	// prepare var which will hold all clicked options
	opzioni = {};
	completed = {};
	persistance = false;
	
	// get all the steps dom
	steps = $(".wizard").find(".step"); // 0 index
	activeStepIndex = 0; // default value;
	
	// initialize the survey
	initialize();
		
	// open the first active step
	if(steps) showStep(activeStepIndex);
	
	// click on a option inside the step
	$(document).on("click", ".step-option", function(){
		var k = $(this).data("option"); // key of the clicked option
		var v = $(this).data("value"); // value of the clicked option

		// functional data
		var current = parseInt($(this).data("step")); // current step 
		var next = $(this).data("next"); // where to go next (number of step - not the index of global var steps)
		
		opzioni[k] = v; // add the key value pair to global var options (object)
		
		// let's see if there was an other option already selected, in that case remove the selected-option class...
		var parent = $(this).closest(".step");
		$(parent).find("[data-option='" + k + "']").removeClass("selected-option");
		// ...and add it to the selected option
		$(this).addClass("selected-option");
		
		// get label / text in block and ad it to the option header
		var h1 = (v) ? $(this).find("h1").text() : "--";
		$(steps[current-1]).find(".chosen-option").text(h1);								
		
		// store selected options in session as json string
		sessionStorage.opzioni = JSON.stringify(opzioni);
		
		// if next is defined go to next step
		if(next != null){
			$(activeStep).addClass("completed-step"); // mark this step as completed
			completed[current] = h1;
			sessionStorage.completed = JSON.stringify(completed);
			showStep(next); // show next step
		}
	});
	
	// when you click on title of one of the previous steps...
	$(document).on("click", ".completed-step h3", function(){	
		var parent = $(this).closest(".step"); // get the div which holds the whole step
		var step = parseInt(parent.data("step")); // get the number of the clicked step
		showStep(step)
	});
	
});

/* FUNCTIONS */

function initialize(){
	// memorize position and options clicked?
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


function showStep(step){
	if(step == null || step == 0 || steps.length == 0) return false; // don't show anything if no steps are found
	
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
		// if inbetween step execute page-custom function stepX (where X is current step +1) if one is defined (in page-specific js)
		
		var fn = "step" + step;
		if (typeof window[fn] === 'function') { 
		  window[fn](); 
		}
		
		$('html, body').animate({
			scrollTop: $(activeStep).offset().top
		}, 200);
		
	}
}

function wizardEnd(){
	sessionStorage.clear();
	// if  wizardEndCustom fucntion is defined in page-specific js execute it, else hide steps and show completed-step div
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

