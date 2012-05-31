$(function(){
	// 发送类型
	$(":radio").click(function(){
		var val = parseInt($(this).val());
		switch(val){
			case 0:
			$('#userGroup,#userSpan').hide();
			break;
			
			case 1:
			$('#userGroup').show();
			$('#userSpan').hide();
			break;
			
			case 2:
			$('#userGroup').hide();
			$('#userSpan').show();
			break;
		}
	});
	// 弹出用户选择
	$('#selInviteUset').click(function(){
		selectUserBox(function(users){
			$('#sendUsers').val(users);
		},false,true);
		return false;
	});
	
	// 绑定发送
	$('#sendBtn').click(function(){
		// 是否选择了通知类型
		if(!$('input[name="notifyRtx"]').attr('checked') && !$('input[name="notifyEmail"]').attr('checked')){
			alert(L('sys.select_notify_type'));
			return false;
		}
		// 是否没有选择用户
		if($(":radio[checked]").val()==2 && $('#sendUsers').val()==''){
			alert(L('sys.select_user'));
			return false;
		}
		// 内容是否都有
		if($('#notMsg').val() == ''){
			alert(L('sys.notify_not_empty'));
			return false;
		}
		// 异步提交
		var btn = this;
		$('#loadimg').show();
		$(btn).hide();
		$.post(site_url('sys','notify'),$("#submitForm").serialize(),function(json){
			$('#loadimg').hide();
			$(btn).show();
			alert(L('sys.send_ok'));
		},'json');
	});
	
	// 初始化
	if(!G_rtx_notify){
		$('input[name="notifyRtx"]').attr('checked',false).attr('disabled',true);
	}
	if(!G_email_notify){
		$('input[name="notifyEmail"]').attr('checked',false).attr('disabled',true);
	}	
	$(":radio").eq(0).click();
});
