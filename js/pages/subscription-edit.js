$(document).ready(function() {
	
	$(document).on("dblclick", ".selectrow", function(){
		var tr = $(this);
		var unchecked = tr.find("input.pcheck:not(:checked)");
		var n = unchecked.length;
		if(n > 0){
			unchecked.each(function(){
				$(this).prop("checked", true);
			});			
		}else{
			tr.find("input.pcheck").prop("checked", false);			
		}
		
	});

	$(document).on("click", ".selectall", function(){
		if( $(this).is(":checked") ){
			var checked = true;
		}else{
			var checked = false;
		}
		var pre = $(this).data("col");
		$("input.pcheck[name^='"+pre+"']").each(function(index, element) {
			$(this).prop("checked", checked);
		});
		
	});

	$(document).on("click", ".pcheck", function(){
		if( $(this).hasClass('gotchildren') ){
			if( $(this).is(":checked") ){
				var checked = true;
			}else{
				var checked = false;
			}
			var thisid = $(this).prop("id");
			var p = thisid.split("_");
			var pre = p[0];
			var tr = $(this).closest("tr");
			var ntr = tr.next("tr");
			ntr.find("input[name^='"+pre+"']").each(function(){
				$(this).prop("checked", checked);
				
			});
		}
	});

	$(document).on("click", ".delrow", function(){
		var t = $(this).closest("table");
		$(this).closest("tr").remove();
		// change name and id's of fields (reorder from 1 to x)
		t.find("tr").each(function(index, element) {
            var c = parseInt(index)+1;
			var pnid = "param_name_"+c;
			var pvid = "param_value_"+c;
			var pnname = "param_name["+c+"]";
			var pvname = "param_value["+c+"]";
			$(this).find("td").each(function(index, element) {
                var f = $(this).find("input");
				if(index == 0){
					f.prop("id", pnid);
					f.prop("name", pnname);
				}else if(index == 1){
					f.prop("id", pvid);
					f.prop("name", pvname);
				}
            });
        });
	});

	$(document).on("click", "#addrow", function(){
		var nrows = $("#paramtable").find("tr").length;
		nrows++;
		var tr = $("<tr></tr>");
		var inputName = $('<input type="text" value="" id="param_name_'+nrows+'" name="param_name['+nrows+']" class="form-control">');
		var inputVal = $('<input type="text" value="" id="param_value_'+nrows+'" name="param_value['+nrows+']" class="form-control">');
		var delbtn = $('<div class="btn btn-block btn-danger btn-xs delrow"><i class="fa fa-fw fa-times"></i></div>');
		var td = $("<td></td>");
		var td1 = td.clone();
		td1.append(inputName);
		td1.appendTo(tr);
		var td2 = td.clone();
		td2.append(inputVal);
		td2.appendTo(tr);
		var td3 = td.clone();
		td3.attr("width", "10");
		td3.append(delbtn);
		td3.appendTo(tr);
		$("#paramtable").append(tr);
		
	});

});

