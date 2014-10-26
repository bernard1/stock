// JavaScript Document

$(document).ready(function(){ 



	$('#opsubmit').on('click', function(e){
		
		opsubmit();
	});

	$('#type').on('change', function(e){
		if ($('#type')[0].value == 0)
			$('#stock')[0].value = 0;

	});

	$('#stock').on('change', function(e){
		if ($('#stock')[0].value != 0){
			$('#type')[0].value = 1;
		}
		else{
			$('#type')[0].value = 0;
		}

		
		$.ajax({
		    type: 'post',
        	url: base_url+'/StockIndex/ajaxGetIndexs/type/'+$('#type')[0].value+'/stock/'+$('#stock')[0].value,
        	dataType: 'json',
	    	success: function(data){
		    	if (data.status == 1)
	    		{
	    			$('#haveIndexs').empty();
	    			if (data.data==null || data.data=='' ||data.data.length<=0) return;
	    			$.each(data.data, function (index, value) {
    					$('#haveIndexs').append($('<option/>', { 
        					value: value.id,
        					text : value.title 
    					}));
					});   
	    		}
	    	}
	    });	

	});


	//only forshowindex page
	$('#stock_showindex').on('change', function(e){
		var url = base_url+'StockIndex/showIndex/stockid/'+$('#stock_showindex')[0].value;
		window.location = url;
	});
		
	$('#opdelete').on('click', function(e){
		if ($('#haveIndexs')[0].value =='')
			alert("please select index.");

		opdelete();
	});
});

function opsubmit()
{
	var para = 'stock_id='+$('#stock')[0].value+'&type='+$('#type')[0].value+'&title='+$('#title')[0].value;
    console.log(para);
	$.ajax({
        type: 'post',
        url: base_url+'/StockIndex/submitIndex',
        data: para,
        dataType: 'json',
    	success: function(data){
	    	if (data.status == 1)
	    	{

				window.location = data.data;
				//window.location = base_url+'Index/showOperation';
			}
			else
			{
				alert("fail");
			}
    	},
    	error: function(data){
			alert("Read user data error.");	
    	},
	}); // end request
}

function opdelete()
{
	var para = 'stock_id='+$('#stock')[0].value+'&type='+$('#type')[0].value+'&index_id='+$('#haveIndexs')[0].value;
    console.log(para);
	$.ajax({
        type: 'post',
        url: base_url+'/StockIndex/deleteIndex',
        data: para,
        dataType: 'json',
    	success: function(data){
	    	if (data.status == 1)
	    	{

				window.location = data.data;
				//window.location = base_url+'Index/showOperation';
			}
			else
			{
				alert("fail");
			}
    	},
    	error: function(data){
			alert("Read user data error.");	
    	},
	}); // end request
}




