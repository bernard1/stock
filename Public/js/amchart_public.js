// JavaScript Document



var _color = [ "#cc6600","#4169E1", "#c0c0c0","#00cccc"];


$(document).ready(function(){ 
	chartData = $.parseJSON(chartData);
	$(document.body).append("<div id='chartParent'></div>");
	$.each(chartData, function (i, value) {
		var divName = 'chartDiv'+i;
        var height = 350;
        console.log(value.chartHeight);
        if (typeof(value.chartHeight)!='undefined'){
            height = value.chartHeight;
        }
		$("#chartParent").append(value.html_name+"<div id='"+divName+"'></div>");
		$('#'+divName).css('height',height+'px');
		$('#'+divName).css('width','70%');
		$('#'+divName).css('font-size','11px');
        console.log('chart_type:'+value.chart_type);
		if (value.chart_type==0)
		{
            console.log('category:'+value.categoryField);
            console.log('value:'+value.valueFields[0]);
            console.log('datalen:'+value.data.length);
			serialChart(divName,value.data,value.categoryField,value.valueFields[0]);
		}
		if (value.chart_type==1)
		{
			console.log(value);
			pieChart(divName,value.data,value.categoryField,value.valueFields[0]);
		}
		if (value.chart_type==2)
			mutilMixedLineColumnGraph(divName,value.data,value.categoryField,value.valueFields);

	});
});
//最简单的饼图
function pieChart(div,data,titleField,valueField)
{
	// PIE CHART
    chart = new AmCharts.AmPieChart();
    chart.dataProvider = data;
    chart.titleField = titleField;
    chart.valueField = valueField;
    chart.outlineColor = "#FFFFFF";
    chart.outlineAlpha = 0.8;
    chart.outlineThickness = 2;
	
	var legend = new AmCharts.AmLegend();
	legend.position = "left";
	legend.textClickEnabled=true;
	legend.verticalGap=0;
	legend.valueWidth=100;
	legend.markerType="circle"
	legend.horizontalGap=100;
	chart.addLegend(legend);
	/*chart."legend": {
        "markerType": "circle",
        "position": "right",
		"marginRight": 80,		
		"autoMargins": false
    },*/

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
    graph.lineThickness = 2;
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


/*
*  非常牛B的一个画图函数！
*  需要有几个图，内容都在graphInfos里
*       .type:column|line
*       .valueField
*       .title
*       .axisPosition:left:right ,null is left
* 
*/
function mutilMixedLineColumnGraph(div,data,categoryField,graphInfos)
{
	console.dir(data);
	console.dir(graphInfos);

      // SERIAL CHART
    chart = new AmCharts.AmSerialChart();
    chart.dataProvider = data;
    chart.categoryField = categoryField;
    chart.startDuration = 1;
    chart.gridAboveGraphs =true;
    chart.type = "serial";
    chart.pathToImages = '../Public/img/charts/';
    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.labelRotation = 45;
    categoryAxis.gridPosition = "start";
        categoryAxis.axisColor = "#DADADA";
    categoryAxis.startOnAxis = true;
    categoryAxis.dateFormats = "MMM DD";


    var cnLeftPosition = 0;
    var cnRightPosition = 0;
    $.each(graphInfos, function (i, value) {
    	var axisPosition = "left";
    	if (value.position!=null){
    		axisPosition = value.position;
            if (value.position=='left')
                posX = (cnLeftPosition++)*80;
            if (value.position=='right')
                posX = (cnRightPosition++)*80;
        }


    	var axis = new_axis(_color[i],value.title,axisPosition,posX,null,null);
    	var graph = new_graph(_color[i],value.title,axis,value.valueField,value.type,0.5);
    	chart.addGraph(graph);
	});

    var legend = new AmCharts.AmLegend();
    legend.useGraphSettings=true;
    legend.align = "center";
    legend.valueAlign ="left";
    legend.switchable = false;
    legend.spacing=2;
    legend.valueWidth=0;
    legend.markerLabelGap=3;
    chart.addLegend(legend);




    // SCROLLBAR
    var chartScrollbar = new AmCharts.ChartScrollbar();
    chartScrollbar.graphType = "line";
    chartScrollbar.backgroundColor = "#bebebe";
    chartScrollbar.scrollbarHeight = 35;
    chartScrollbar.color = "#000000";
    chartScrollbar.gridColor = "#000000";
    chartScrollbar.gridAlpha = 1;
        //  chartScrollbar.scrollbarHeight = 50;
        //   chartScrollbar.gridCount = 50;
        // chartScrollbar.graphLineColor = pressure_color;
        //chartScrollbar.graphFillColor = pressure_color;
        //  chartScrollbar.graphType = 'line';
    chartScrollbar.selectedBackgroundColor = "#bebebe";
        //  chartScrollbar.selectedGraphFillColor = "#ff0000";
        //  chartScrollbar.selectedGraphLineColor = "#00ff00";
        //chartScrollbar.dragIconHeight = 100;
        //chartScrollbar.dragIconWidth = 15;
    chartScrollbar.autoGridCount = true;                
    chart.addChartScrollbar(chartScrollbar);


   // CURSOR
    chartCursor = new AmCharts.ChartCursor();
    chartCursor.cursorPosition = "mouse";
    chartCursor.categoryBalloonDateFormat = "DD MMMM";
    //chartCursor.pan = true;
    chartCursor.valueLineEnabled=true;
    chartCursor.valueLineBalloonEnabled=true;
    chart.addChartCursor(chartCursor);



    chart.creditsPosition = "top-right";

    chart.write(div);


}

function new_axis(color,title,position,offset,max,min)
{
    var newAxis = new AmCharts.ValueAxis();
     // this line makes the axis to appear detached from plot area
    newAxis.axisThickness = 3;
    newAxis.gridAlpha = 0;
    newAxis.title = title;
    if (max!=null)
    	newAxis.maximum  =max;
    if (min!=null)
    	newAxis.minimum  =min;
    if (color!=null){
        console.log(color);
		newAxis.axisColor = color;
    }
	if (offset!=null)
		newAxis.offset = offset;	
	if (position!=null)
    	newAxis.position = position;

	newAxis.reversed = false;
    chart.addValueAxis(newAxis);
    return newAxis;

}

function new_graph(color,title,axis,valueField,type,fillAlphas)
{
	var graph = new AmCharts.AmGraph();
	graph.bullet = "smoothedLine";
	graph.valueField = valueField;
	graph.title = title;
	graph.valueAxis = axis;
	graph.balloonText = "[[title]]:[[value]]";
	//graph.lineAlpha = 0;
	graph.lineThickness = 2;
	if (type!=null)
		graph.type = type;

    if (color!=null)
        graph.lineColor = color;

	if (fillAlphas!=null && type!='line')
		graph.fillAlphas = fillAlphas;

	return graph;
}

