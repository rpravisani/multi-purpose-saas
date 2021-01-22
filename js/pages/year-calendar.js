$(document).ready(function() {
	
	annoCal = new Date().getFullYear(); // tanto da dargli un valore iniziale
	datums = new Array();
	
	// init calendar
	$("#year-calendar").calendar({ 
		language: "it", 
		renderEnd: function(e) { annoCal = e.currentYear; } // get the current year the calendar is set to
	});
	
	// recupera assenze da DB passanda annoCal (preso da calendario) e id milite (da select)
	getAssenze();

	// Quando cambio il milite recupero le nuove assenze
	$('#milite').on('change', function(){ getAssenze(); });
	
	// selectRange non funziona su tablet... uso dunque var ausiliari e clickDay:  $('#year-calendar').on('selectRange', function(e){ 
	var startDate = null; var startElement = null;
	$('#year-calendar').on('clickDay', function(e){
		var milite = $("#milite").val();
		if(milite == ""){
			alert("Per impostare assenza seleziona prima un milite dall'elenco.");
			return false;
		}
		var datum = e.date;
		var toff = datum.getTimezoneOffset() * 60000; // timezone offset in seconds
		datum = new Date(datum-toff); // set date to my timezone
		if( startDate == null){ 
			e.element.addClass("selezionato");
			startElement = e.element;
			startDate = datum;
		}else{
			
			// invert startDate with datum if the later is smaller then startDate
			if(datum < startDate) datum = [startDate, startDate = datum][0]; 
			
			// invio start e end date insieme all'id milite allo script php che memorizza i dati all'interno del DB 
			$.post(  
			 "calls/save_assenze.php",  
			 {milite: milite, startdate: startDate.toISOString().slice(0, 10), enddate:  datum.toISOString().slice(0, 10)},   
			 function(response){
				 
				getAssenze();
				startDate = null; startElement = null;
				 
			 },  
			 "json"  
			); 
			
		}
	});
		
	
});

// funzione che richama script php con le assenze. Recupera id milite da select - annoCal è var globale
function getAssenze(){
	var milite = $("#milite").val();
	if(milite == "" || milite == undefined) return false; // se milite è vuoto non recupera nulla
	
	// load dates from php script and sets ranges in calendar
	$.post(  
	 "calls/get_assenze.php",  
	 {milite: milite, anno: annoCal},  
	 function(response){
		 datums = new Array();
		 // loop trough data
		 $.each(response.dates, function(i,row){
			 // create start and end date object of this range
			 var start = new Date(row.sYear, row.sMonth, row.sDay);
			 var fine = new Date(row.eYear, row.eMonth, row.eDay);
			 // Set array entering start and end date adn assigning a custom color to the range
			 datums[i] = { 
				 startDate: start,
				 endDate: fine, 
				 color: "#D73925"
			 }
		 });
		 
		 // pass data set of ranges to calendar
		 $('#year-calendar').data('calendar').setDataSource(datums);
	 },  
	 "json"  
	); 
	
}