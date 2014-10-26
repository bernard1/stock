// JavaScript Document






$(document).ready(function(){ 
	chartData = $.parseJSON(chartData);
	$(document.body).append("<div id='chartParent'></div>");
	$.each(chartData, function (i, value) {
		var divName = 'chartDiv'+i;
		$("#chartParent").append(value.html_name+"<div id='"+divName+"'></div>");
		$('#'+divName).css('height','500px');
		$('#'+divName).css('width','70%');
		$('#'+divName).css('font-size','11px');
		if (value.chart_type==0)
			serialChart(divName,value.data,value.categoryField,value.valueField);
		if (value.chart_type==1)
			pieChart(divName,value.data,value.categoryField,value.valueField);
	});
});
//最简单的饼图
function pieChart(div,data,titleField,valueField)
{
	console.log(titleField);
	console.log(valueField);
	console.dir(data);
	// PIE CHART
    chart = new AmCharts.AmPieChart();
    chart.dataProvider = data;
    chart.titleField = titleField;
    chart.valueField = valueField;
    chart.outlineColor = "#FFFFFF";
    chart.outlineAlpha = 0.8;
    chart.outlineThickness = 2;
    // WRITE
    chart.write(div);
}


function serialChart(div,data,categoryField,valueField){
	      // SERIAL CHART
    chart = new AmCharts.AmSerialChart();
    chart.dataProvider = data;
    chart.categoryField = categoryField;
    chart.startDuration = 1;
    chart.gridAboveGraphs =true;
    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.labelRotation = 0;
    categoryAxis.gridPosition = "start";

    // value
    // in case you don't want to change default settings of value axis,
    // you don't need to create it, as one value axis is created automatically.

    // GRAPH
    var graph = new AmCharts.AmGraph();
    graph.valueField = valueField;
    graph.balloonText = "[[category]]:[[value]]";
    graph.type = "column";
    graph.lineAlpha = 0;
    graph.fillAlphas = 0.8;
    chart.addGraph(graph);


    // CURSOR
    var chartCursor = new AmCharts.ChartCursor();
    chartCursor.cursorAlpha = 0;
    chartCursor.zoomable = false;
    chartCursor.categoryBalloonEnabled = false;
    chart.addChartCursor(chartCursor);

    chart.creditsPosition = "top-right";

    chart.write(div);
}


function mutilSerialChart()
{
	//多列的曲线图
	var chart = AmCharts.makeChart("chartdiv", {
	    "type": "serial",
	    "theme": "none",
	    "pathToImages": "http://www.amcharts.com/lib/3/images/",
	    "legend": {
	        "useGraphSettings": true
	    },
	    "dataProvider": chartData,
	    "valueAxes": [{
	        "id":"v1",
	        "axisColor": "#FF6600",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "left"
	    }, {
	        "id":"v2",
	        "axisColor": "#FCD202",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "axisAlpha": 1,
	        "position": "right"
	    }, {
	        "id":"v3",
	        "axisColor": "#B0DE09",
	        "axisThickness": 2,
	        "gridAlpha": 0,
	        "offset": 50,
	        "axisAlpha": 1,
	        "position": "left"
	    }],
	    "graphs": [{
	        "valueAxis": "v1",
	        "lineColor": "#FF6600",
	        "bullet": "round",
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "red line",
	        "valueField": "visits",
			"fillAlphas": 0
	    }, {
	        "valueAxis": "v2",
	        "lineColor": "#FCD202",
	        "bullet": "square",
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "yellow line",
	        "valueField": "hits",
			"fillAlphas": 0
	    }, {
	        "valueAxis": "v3",
	        "lineColor": "#B0DE09",
	        "bullet": "triangleUp",
	        "bulletBorderThickness": 1,
	        "hideBulletsCount": 30,
	        "title": "green line",
	        "valueField": "views",
			"fillAlphas": 0
	    }],
	    "chartScrollbar": {},
	    "chartCursor": {
	        "cursorPosition": "mouse"
	    },
	    "categoryField": "date",
	    "categoryAxis": {
	        "parseDates": true,
	        "axisColor": "#DADADA",
	        "minorGridEnabled": true
	    }
	});
}
