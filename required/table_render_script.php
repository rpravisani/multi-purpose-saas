<?php /*** DA USARE CON INCLUDE ***/ ?>
<script type="text/javascript">
function renderDataTable(selector) {
	var out = [];
	var tables = jQuery(selector);
	var sorting;
	
	
	for ( var i=0, iLen=tables.length ; i<iLen ; i++ )
	{
		var defaultCol = $('th', tables[i]).index($(".sortme",tables[i]));
		if(defaultCol >= 0){
			var classecolonna = $('th.sortme', tables[i]).attr("class");
			if(classecolonna.indexOf("desc") >=0){
				var ordine = "desc";
			}else{
				var ordine = "asc";
			}
			sorting = [ defaultCol, ordine ];
		}else{
			sorting = [0,'asc'];
		}
		

		
		var oTable2 = $(tables[i]).DataTable({
			stateSave: true,
			"aaSorting": [ sorting ],
			"aoColumnDefs":[ { "bSortable": false, "aTargets": [ 'nosort' ] }, {"sType": 'euro', "aTargets": [ 'valuta' ]}, {"sType": 'date-eu', "aTargets": [ 'datum' ]}, {"sSortDataType": "bytesize",  "aTargets": [ 'bytesize' ], "sType": 'numeric'} ],
			"sPaginationType": "full_numbers",
			"sDom": '<"table-top"ipf<"clear">>rt<"table-bottom"lp>',
			"bLengthChange": false,
			"iDisplayLength": <?php echo NUMERO_DEFAULT_RIGHE_TABELLA; ?>,

			"oLanguage": {
				"sUrl": "<?php echo PATH_JS; ?>it_IT.txt"
			}
			<?php if($serverside){ ?>
			,
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "calls/server_processing.php?mod=<?php echo $modulo; ?>&f=<?php echo $nome_modulo; ?>&pf=<?php echo urlencode(serialize($pf)); ?>",
			
			"fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
		
				$('td',nRow).each(function(i,v){
					if (typeof aData[i]=='object'){
						if (typeof aData[i].attr != 'undefined'){
							var attribs = aData[i].attr;
							if(attribs){
								var as = attribs.split(" ");
								$.each(as, function(key, value){
									var a = value.split("=");
									var patt=/['"]/g;
									var val = a[1].replace(patt,""); 
									$(v).attr(a[0],val);
								});
							}
							//$(v).attr('fff',aData[i].attr);
						}
						if (typeof aData[i].cssclass!='undefined'){
							$(v).removeClass(aData[i].cssclass);
							$(v).addClass(aData[i].cssclass);
						}
						$(v).html('');
						if (typeof aData[i].data!='undefined'){
							$(v).html(aData[i].data);
						}
					}
				});
				return nRow;
				
			}
			<?php }else if($modulo == '1' and $sonoelenco){ ?>
			,"fnRowCallback": customFnRowCallback
			<?php } ?>
			
		} );
			//console.log(oTable2.columns());
		
		
		out.push( oTable2 );
	}
	return out;
}

</script>