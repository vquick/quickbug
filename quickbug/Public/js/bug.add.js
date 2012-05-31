$(function(){
	// 绑定公共事件
	init_bind(true);

	// 提交
	$('button').click(function(){
		var st = $(this).attr('savetype');
		$('#savetype').val(st);
		// 检测
		if(!check_submit()){
			return false;
		}
		// 邀请相关
		if($('#ivtuser').val() == $('#ivtuser')[0].defaultValue){
			$('#ivtuser').val('');
		}
		// 提交
		this.form.submit();
	});	

	// 如果有指定项目ID则自动初始化
	var projectid = get_url_param('projectid');
	if(projectid != ''){
		$('#projectid').val(projectid).change();
	}
	/* ================= 以下代码是邀请相关 ================== */
	// 弹出选择框
	$('#selInviteUset').click(function(){
		selectUserBox(function(users){
			$('#ivtuser').val(users);
		});
		return false;
	});	

	// 邀请的用户选择
	$('#inviteBox').click(function(){
		if($(this).is(':checked')){
			$('#inviteSpan').show();
		}else{
			$('#inviteSpan').hide();
		}
	});
	// 邀请者输入框
	$('#ivtuser').focus(function(){
		if(this.value == this.defaultValue){
			this.value = '';
		}
	});	
	/* ===================================================== */
	
	// 绑定保存为默认的模板
	$('#saveDefaultBtn').click(function(){
		var id = $('#bugTpl').val();
		var url = site_url('profile','savedefault','type=1&val='+id);
		$.get(url,function(json){
			alert(L('bug.save_succ'));
		},'json');
		return false;
	});

	// 初始化 默认的模板
	if(G_default_tplid > 0){
		$('#bugTpl').val(G_default_tplid);
	}	
});
