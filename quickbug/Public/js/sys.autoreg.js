$(function(){
	// 保存邮箱后缀
	$('#saveBtn').click(function(){
		var emailAddr = $('#emailAddr').val();
		if(emailAddr == ''){
			alert(L('sys.email_suffix_empty'));
			return false;
		}
		var url = site_url('sys','saveemail','email='+emailAddr);
		$.get(url,function(json){
			alert(L('sys.save_ok'));
		},'json');
		return false;
	});
	// 自动选择内容
	$('.urlbox').focus(function(){
		$(this)[0].select();
	});

	// 如果文本框为空
	if($('#emailAddr').val() == ''){
		$('#emailAddr').val('@');
	}
});