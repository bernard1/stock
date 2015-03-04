// JavaScript Document
var document = window.document;

var map;
var marker_focus = null;
var _circle = [];
var _map_init_bounds = null;
var _now_bounds = null;

var chart=null;
var chartData = [];
var chartCursor;

var finished_loading_date = '';
var despatched_from_plant_date = '';
var arrive_on_site_date=[];
var finished_discharging_date = [];
var left_site_date = [];
var load_start_date = '';
var start_discharging_date = [];

var pressureAxis;
var stop_report_trak = false;
var choose_tr = null;
var is_slump_data = false;
var axiss = [];
var graphs = [];
var RPM_zero_guide_line = null;
var data = null;



$(document).ready(function(){ 
      
    //register function of axis checkbox mouse click
    $('input[name=graph_cb]').on('click', function(e){
        var checkbox = this;
        // checked
        var axis;
        var graph;
        
        axis = axiss[checkbox.value-1];
        graph = graphs[checkbox.value-1];
        //console.log(checkbox.value);

        if( checkbox.checked ){
            axis.axisAlpha = graph.lineAlpha = 1;
        }
        else{
            axis.axisAlpha = 0.2;
            graph.lineAlpha = 0.0;
        }
        chart.validateNow();
    });
    /////
   /////

    $('#view').on('click', function() {
        update_graph();


    });

    update_graph();


});


function update_graph()
{    
    var url = '/stock/Index/ajaxGetMarketValue/';
    //var para = 'account_id='+$('#account-select').val()+'&from='+$('#from').val()+'&to='+$('#to').val();
    $.ajax({
        type: 'post',
        url: url,
        data:'',
        dataType: 'json',
        success: function(ret_data){
            if (ret_data.status == 1){
                data = ret_data.data;
                //draw graph
                draw_graph();
            }
            else{   
                alert(ret_data.data);
            }
        }, 
        error: function(ret_data){
            alert("read failed.");
        },
    }); // end reque
} 

/*
*   draw graph
*/
function draw_graph()
{
    remove_chart_all();

    var line_thinkness = 5;

    // generate data for special format.
    generateChartData();

    // SERIAL CHART    
    chart = new AmCharts.AmSerialChart();
    chart.pathToImages = '../Public/img/charts/';
    chart.dataProvider = chartData;
    chart.categoryField = "date";

    // listen for "dataUpdated" event (fired when chart is rendered) and call zoomChart method when it happens
    chart.addListener("dataUpdated", zoomChart);

    // AXES
    // category
    var categoryAxis = chart.categoryAxis;
    categoryAxis.parseDates = true;     // as our data is date-based, we set parseDates to true
    categoryAxis.minPeriod = "ss";      // our data is daily, so we set minPeriod to DD
    //categoryAxis.dashLength = 1;
    categoryAxis.gridAlpha = 0.15;
    categoryAxis.axisColor = "#DADADA";
    categoryAxis.startOnAxis = true;
    ///end category

	//guide line
 //   draw_all_guide_line(categoryAxis);

	             
    {
        var color1 = "#ff00ff";
        var color2 = "#faa61a";
        var color3 = "#D2232A";
        var color4 = "#007FFF";
        var color5 = "#8f8f8f";
        var axis1 = new_axis("total_value",color1,60+0*90,2600,1700);
        axiss.push(axis1);
        
        var axis2 = new_axis("stockPercent",color2,60+1*90,60,20);
        axiss.push(axis2);
        add_horizontal_guide_line(axis2,color2,60);
        add_horizontal_guide_line(axis2,color2,40);
        add_horizontal_guide_line(axis2,color2,30);

        var axis3 = new_axis("profitPercent",color3,60+2*90,30,10);
        axiss.push(axis3);
        add_horizontal_guide_line(axis3,color3,5);
        add_horizontal_guide_line(axis3,color3,10);
        add_horizontal_guide_line(axis3,color3,15);
        add_horizontal_guide_line(axis3,color3,20);

                // GRAPH lines
        var graph1 =  new_graph(axis1,color1,"total_value",
                    "totalValue:[[total_value]]\r\nstockPercent:[[stock_percent]]%\r\nprofitPercent:[[profit_percent]]%","#696969");
        graphs.push(graph1);
        var graph2 =  new_graph(axis2,color2,"stock_percent","","");
        graphs.push(graph2);
        var graph3 =  new_graph(axis3,color3,"profit_percent","","");
        graphs.push(graph3);
    }





    // CURSOR
    chartCursor = new AmCharts.ChartCursor();
    chartCursor.cursorPosition = "mouse";
    chartCursor.categoryBalloonDateFormat = "YYYY-MM-DD";
    chartCursor.pan = true;
    chart.addChartCursor(chartCursor);

    // SCROLLBAR
    var chartScrollbar = new AmCharts.ChartScrollbar();
    //chartScrollbar.graph = pressureGraph;
    chartScrollbar.graphType = "line";
    chartScrollbar.backgroundColor = "#bebebe";
    chartScrollbar.scrollbarHeight = 30;
    chartScrollbar.color = "#000000";
    chartScrollbar.gridColor = "#000000";
    chartScrollbar.gridAlpha = 1;
    chartScrollbar.selectedBackgroundColor = "#bebebe";
    chartScrollbar.autoGridCount = true;
                
    chart.addChartScrollbar(chartScrollbar);
    //chart.zoomToDates(finished_loading_date,finished_discharging_date);
	// WRITE to div
    chart.write("graph_main-div");
    chart.validateData();


    $('input[name=graph_cb]').each(function(i, checkbox){
            // checked
            var axis;
            var graph;
            axis = axiss[checkbox.value-1];
            graph = graphs[checkbox.value-1];

            if( checkbox.checked ){
                axis.axisAlpha = graph.lineAlpha = 1;
            }
            else{
                console.log("fade");
                axis.axisAlpha = 0.2;
                graph.lineAlpha = 0.0;
            }
            chart.validateNow();
    });


    //chart.validateNow();
            
} // end draw_graph

function add_horizontal_guide_line(axis, color,value)
{
    var guide = new AmCharts.Guide();

    guide.lineAlpha = 1;
    guide.lineColor = color;
    guide.toValue = value;        // at 0 value
    guide.lineAlpha = 1;
    guide.lineThickness = 2;
    guide.inside = true;
    guide.dashLength = 4;
    axis.addGuide(guide);

    return guide;

}
function remove_chart_all()
{
    //console.log('chart.valueAxes.length'+chart.valueAxes.length);

    for(var i=axiss.length-1;i>=0;i--)
    {
        axis = axiss[i];
        //console.log(i+' '+axis);
        chart.removeValueAxis(axis);
        axiss.pop();
    }
    //console.log('chart.valueAxes.length'+chart.valueAxes.length);

    for(var i=graphs.length-1;i>=0;i--)
    {
        graph = graphs[i];
        chart.removeGraph(graph);
        graphs.pop();
    }
    if (chart!=null)
    {
        chart.removeChartCursor();
        chart.removeChartScrollbar();
        chart.clear();
        chart = null;
    }
    for(var i=chartData.length-1;i>=0;i--)
    {
        chartData.pop();
    }
}

   

/*
* generate data for chart
*/
function generateChartData() {
	$.each(data, function(i,item){
            chartData.push({
                date: item.date,
                total_value: item.total_value,
                profit_percent: item.profit_percent,
                stock_percent: item.stock_percent,
                //stock_value: item.stock_value,
            });
            console.log(item.date);
    });
} 

function new_chart_guide(cataAxis,guide_date,lable_name)
{

    var guide = new AmCharts.Guide();
    guide.date = guide_date
    guide.lineColor = "#220011";
    guide.lineAlpha = 1;
    guide.lineThickness = 1;
    guide.dashLength = 4;
    guide.above = true;
    guide.inside = true;
    guide.labelRotation = 90;
    guide.label = lable_name;
    guide.position = "left";
    guide.fontSize = 15;
    cataAxis.addGuide(guide);
    return guide;
   
}
function new_graph(axis,lineColor,valueField,ballonText,ballonColor)
{
    newGraph = new AmCharts.AmGraph();
    newGraph.title = "red line";
    newGraph.valueAxis = axis;
    newGraph.valueField = valueField;
    newGraph.bullet = "smoothedLine";
    newGraph.bulletBorderColor = "#FFFFFF";
    newGraph.bulletBorderThickness = 2;
    newGraph.lineThickness = 5;
    if (ballonText!="")
        newGraph.balloonText = ballonText;
    else
        newGraph.showBalloon =false;

    if (ballonColor!="")
        newGraph.balloonColor = ballonColor;
    newGraph.lineColor = lineColor;
    newGraph.type = "line";
    newGraph.hideBulletsCount = 50; // this makes the chart to hide bullets when there are more than 50 series in selection
    
    chart.addGraph(newGraph);
    return newGraph;
}


function new_axis(title,color,offset,max,min)
{
    var newAxis = new AmCharts.ValueAxis();
    newAxis.position = "left"; // this line makes the axis to appear on the right
    newAxis.offset = offset; // this line makes the axis to appear detached from plot area
    newAxis.axisColor = color;
    newAxis.axisThickness = 5;
    newAxis.gridAlpha = 0;
    newAxis.title = title;
    newAxis.maximum  =max;
    newAxis.minimum  =min;
    newAxis.reversed = false;
    chart.addValueAxis(newAxis);
    return newAxis;

}


//=======================================
//	amChart Event function
//=======================================

// changes cursor mode from pan to select
function setPanSelect() {
    if (document.getElementById("rb1").checked) {
        chartCursor.pan = false;
        chartCursor.zoomable = true;
                
    } else {
        chartCursor.pan = true;
    }
    chart.validateNow();
}

// this method is called when chart is first inited as we listen for "dataUpdated" event
function zoomChart() {
	// different zoom methods can be used - zoomToIndexes, zoomToDates, zoomToCategoryValues
    //chart.zoomToIndexes(0, chartData.length );

    chart.zoomToIndexes(0, chartData.length );  
   
}
//	End amChart Event function



function parseDate(str) {
    var a=str.split(" ");

    var d=a[0].split("-");
    var t=a[1].split(":");
    return new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);

} 

function buildOptions(items)
{
    var options = '<option value="0">All</option>';
    $.each(items, function(i,item){
        options += '<option value="' + item.account_id + '">' + item.account_name + '</option>';        
    });
    $('#account-selection').html('<select id="account-select">' + options + '</select>');
                        
            
}


