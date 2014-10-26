// JavaScript Document

$(document).ready(function(){ 



	$('#opsubmit').on('click', function(e){
		
		opsubmit();
	});

	$('#type').on('change', function(e){
		if ($('#type')[0].value == 0)
			$('#stock')[0].value = 0;

	});

	$('#stock,#year,#quarter').on('change', function(e){

		var url = base_url+'StockIndex/showFillIndexValue/defaultYear/'+$('#year')[0].value+'/defaultQuarter/'+$('#quarter')[0].value+'/defaultStock/'+$('#stock')[0].value;
	    window.location = url;

	});
		
	$('#value_delete').on('click', function(e){
		$(':checkbox:checked').each(function(i){
			var a = $(this)[0].id.split('_');
		  	if (a[1]!='')
        		deleteIndexValue(a[1]);  
        });
		
	});
});

function opsubmit()
{
	var para = 'stock='+$('#stock')[0].value+'&year='+$('#year')[0].value+'&quarter='+$('#quarter')[0].value+'&value='+$('#value')[0].value+'&index='+$('#index')[0].value;
    console.log(para);
	$.ajax({
        type: 'post',
        url: base_url+'/StockIndex/submitIndexValue',
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

function deleteIndexValue(deleId)
{
	var para = 'id='+deleId;
	$.ajax({
        type: 'post',
        url: base_url+'/StockIndex/deleteIndexValue',
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




