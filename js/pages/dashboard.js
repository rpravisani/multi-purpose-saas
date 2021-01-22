$(document).ready(function() {


    //-------------
    //- PIE CHART -
    //-------------
    // Get context with jQuery - using jQuery's .get() method.
    /*
    var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
    var pieChart = new Chart(pieChartCanvas);
    
    var pieOptions = {
      //Boolean - Whether we should show a stroke on each segment
      segmentShowStroke: true,
      //String - The colour of each segment stroke
      segmentStrokeColor: "#fff",
      //Number - The width of each segment stroke
      segmentStrokeWidth: 2,
      //Number - The percentage of the chart that we cut out of the middle
      percentageInnerCutout: 50, // This is 0 for Pie charts
      //Number - Amount of animation steps
      animationSteps: 100,
      //String - Animation easing effect
      animationEasing: "easeOutBounce",
      //Boolean - Whether we animate the rotation of the Doughnut
      animateRotate: true,
      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale: false,
      //Boolean - whether to make the chart responsive to window resizing
      responsive: true,
      // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
      maintainAspectRatio: true,
      //String - A legend template
      legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style=\"background-color:<%=segments[i].fillColor%>\"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>"
    };
    //Create pie or douhnut chart
    // You can switch between pie and douhnut using the method below.
    pieChart.Doughnut(PieData, pieOptions);	
	
    
    
    */
});

/** DONUT CHART STATI REPORT **/
var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
var myPieChart = new Chart(pieChartCanvas, {
    type: 'doughnut',
    data: {
        labels: reportTypeLabels,
        datasets: [{
            
            data: reportTypeValues,
            backgroundColor: reportTypeColors,
            
            borderWidth: 0
        }]
    },
    options: {
       
        legend: { display: true }, 
        cutoutPercentage: 65,
        animation: {
					animateScale: true,
					animateRotate: true
				}
    }
});


/** BARCHART TASK PER TIPOLOGIA **/
var barChartCanvas = $("#barChart").get(0).getContext("2d");
var myChart = new Chart(barChartCanvas, {
    type: 'horizontalBar',
    data: {
        labels: taskLabels,
        datasets: [{
            
            data: taskValues,
            backgroundColor: taskColors,
            
            borderWidth: 0
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        },
        legend: { display: false }
    }
});

// set / update number of message in open tickets table
function updateTicketChat(result){
	 $.each(result, function(id,value){
		 $("#ticket_row_"+id).find(".replies").html(value);
	 });
}
