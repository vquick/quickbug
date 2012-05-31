$(function(){
	$('#submitBtn').click(function(){
		// RTX是否自动登录
		var rtxAutoLogin = 1;
		if($('#rtxAutoLogin').attr('disabled') == true){
			rtxAutoLogin = 0;
		}else{
			rtxAutoLogin = $('#rtxAutoLogin').attr('checked') ? 1 : 0;
		}
		// 导步更新
		var data = {
			'password':$('#password').val(),
			'truename':$('#truename').val(),
			'email':$('#email').val(),
			'rtxAutoLogin':rtxAutoLogin
		};
		var url = site_url('profile','index');
		$.post(url,data,function(json){
			alert(L('profile.submit_succ'));
		},'json');
	});
});
