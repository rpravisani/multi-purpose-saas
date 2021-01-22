$(document).ready(function() {
	
	// When the row of a table is clicked the content of the cell with the url is selected
	$(document).on("click", "tr", function(){
		var t = $(this).find("td.urltoken");
		var range = document.createRange();
    	var selection = window.getSelection();
    	range.selectNodeContents(t[0]);
    	selection.removeAllRanges();
    	selection.addRange(range);
	});
});