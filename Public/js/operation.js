// JavaScript Document

$(document).ready(function(){ 



	$('#opsubmit').on('click', function(e){
		
		opsubmit();
	});
		
	$('#moneytransfer_submit').on('click', function(e){
		
		moneytransfer_submit();
	});
});

function opsubmit()
{
	if ($('#amount')[0].value == null || $('#amount')[0].value == '' || $('#amount')[0].value == 0)
    {
    	alert("please input amount");
    	return;
    }

	if ($('#price')[0].value == null || $('#price')[0].value == '' || $('#price')[0].value == 0)
    {
    	alert("please input price");
    	return;
    }
    var para = 'stock_id='+$('#stock')[0].value+'&amount='+$('#amount')[0].value+'&price='+$('#price')[0].value+'&reason='+$('#reason')[0].value;
    console.log(base_url+'/Index/submitOperation');
	$.ajax({
        type: 'post',
        url: base_url+'/Index/submitOperation',
        data: para,
        dataType: 'json',
    	success: function(data){
	    	if (data.status == 1)
	    	{
				//window.location = base_url+'Index/login_submit/?url='+redir_url;
				alert("success");
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
function moneytransfer_submit()
{
	if ($('#amount')[0].value == null || $('#amount')[0].value == '' || $('#amount')[0].value == 0)
    {
    	alert("please input amount");
    	return;
    }
    var type = $('input[name=type]:checked', '#body').val();
    var para = 'stock_id='+$('#currency')[0].value+'&amount='+$('#amount')[0].value+'&type='+type+'&reason='+$('#reason')[0].value;
	$.ajax({
        type: 'post',
        url: base_url+'/Index/submitMoneyTransfer',
        data: para,
        dataType: 'json',
    	success: function(data){
	    	if (data.status == 1)
	    	{
				//window.location = base_url+'Index/login_submit/?url='+redir_url;
				alert("success");
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


