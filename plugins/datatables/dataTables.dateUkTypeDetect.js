jQuery.fn.dataTableExt.aTypes.unshift(
    function ( sData ){
		// date in format dd/mm/yyyy
        if (sData !== null && sData.match(/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/(19|20|21)\d\d$/)){
            return 'date-uk';
		// date time in format dd/mm/yyyy hh:ii(:ss)
        }else  if (sData !== null && sData.match(/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/(19|20|21)\d\d[ ](0[0-9]|1[0-9]|2[01234]):([0-5][0-9])(:[0-5][0-9])?$/)){
            return 'datetime-uk';
		}
        return null;
    }
);