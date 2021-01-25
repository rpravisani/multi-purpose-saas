jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "date-uk-pre": function ( a ) {
        if (a == null || a == "") {
            return 0;
        }
        var ukDatea = a.split('/');
        return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
    },
 
    "date-uk-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "date-uk-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }, 
	
    "datetime-uk-pre": function ( a ) {
        if (a == null || a == "") {
            return 0;
        }
        var ukDateTimeSegents = a.split(' ');
		var ukDateTimeDate = ukDateTimeSegents[0].split('/');
		var ukDateTimeTime = ukDateTimeSegents[1].split(':');
		if (ukDateTimeTime[2] == null || ukDateTimeTime[2] == "") ukDateTimeTime[2] = 0;
        console.log( (ukDateTimeDate[2] + ukDateTimeDate[1] + ukDateTimeDate[0] + ukDateTimeTime[0] + ukDateTimeTime[1] + ukDateTimeTime[2]) * 1);
        return (ukDateTimeDate[2] + ukDateTimeDate[1] + ukDateTimeDate[0] + ukDateTimeTime[0] + ukDateTimeTime[1] + ukDateTimeTime[2]) * 1;
    },
 
    "datetime-uk-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "datetime-uk-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );