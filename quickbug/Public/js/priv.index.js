$(function(){
	$('#groupid').change(function(){
		location.href = site_url('priv','index','groupid='+$(this).val())						  
	});
	// 全选/返选
	$('input[name="privgroupname"]').click(function(){
		var pgid = $(this).val();
		var chk = $(this).attr('checked');
		$('input[privgroupid="'+pgid+'"]').attr('checked',chk);
	});
	
	// 提交
	$('#submitBtn').click(function(){
		poststr = $("#privform").serialize();
		var url = site_url('priv','submit');
		$.post(url,poststr,function(json){
			if(json.data){
				alert(L('priv.priv_submit_succ'));
			}
		},'json');
	});
	
	$('#groupid').val(get_url_param('groupid'));
});
