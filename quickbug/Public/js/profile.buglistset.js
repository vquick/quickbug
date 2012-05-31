$(function(){
	// 绑定提交
	$('#subBtn').click(function(){
		var data = $('form').serialize();
		var url = site_url('profile','buglistset');
		$.post(url,data,function(json){
			alert(L('profile.submit_succ'));
		},'json');
	});
	// 绑定全选
	$('#chkall').click(function(){
		$('input[name="fields[]"]').attr('checked', this.checked);
	});
});