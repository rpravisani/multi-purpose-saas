$(document).ready(function() {
	
	// When the new-row button is clicked a new tr is appended to tbody with save button at the end
	$(document).on("click", ".new-row", function(){
		var tr = "<tr>";
		tr += '<td align="left" class="valore inline-edit-field"><input required type="number" class="inline-input" value="" id="valore" name="valore"></td>';
		tr += '<td align="left" class="denominazione inline-edit-field"><input required type="text" class="inline-input" value="" id="denominazione" name="denominazione"></td>';
		tr += "<td>&nbsp;</td>";
		tr += '<td align="center"><i class="puls inline-save-new fa fa-fw fa-check"></i>&nbsp;<i class="puls inline-cancel-new fa fa-fw fa-ban"></i></td>';
		tr += "</tr>";
		
		// get tbody
		var tbody = $("table#table-tabella-iva").find("tbody");
		// append row
		tbody.append(tr);
		// disable all edit buttons
		$(".inline-edit").data("stop", true);
		$(".inline-edit").css("opacity", '0.5');
		// ... end new-row buttons
		$(".new-row").prop("disabled", true);
		// set focus on first field
		$("#valore").focus();
	});


});



