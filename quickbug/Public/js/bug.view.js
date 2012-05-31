$(function(){
	// 评论框
	$('#comment').focus(function(){
		if($(this).val() == this.defaultValue){
			$(this).val('');
		}
	}).focusout(function(){
		if($(this).val() == ''){
			$(this).val(this.defaultValue);
		}
	});
	
	// 提交
	$('#submitBtn').click(function(){
		var _bugid = get_url_param('bugid');
		var _comment = $('#comment').val(); 
		var _status = $('input[type="radio"][name="status"]:checked').val();
		var _msg = $('#notifyMsg').is(':checked') ? 1 : 0;
		var _rtx = $('#notifyRtx').is(':checked') ? 1 : 0;
		var _email = $('#notifyEmail').is(':checked') ? 1 : 0;
		var url = site_url('bug','process');
		(_comment == $('#comment')[0].defaultValue) && (_comment = '');
		// 如果没有数据提交
		if(_comment=='' && _status==bugInfo.status){
			alert(L('bug.no_data_modify'));
			return false;
		}
		// 状态没有修改
		if(_status == bugInfo.status){
			_status = 0;
		}
		// 是否邀请讨论
		var _ivtuser = '';
		if($('#inviteBox').is(':checked') && $('#ivtuser').val()!=$('#ivtuser')[0].defaultValue){
			_ivtuser = $('#ivtuser').val();
		}
		// 如果是BUG重新指定则要判断是否选择了用户
		var _tobugUser = '';
		if(_status == 100){
			_tobugUser = $('#bugToUser').val();
			if(_tobugUser == ''){
				alert(L('bug.select_to_user'));
				return false;
			}
		}
		
		// 数据提交
		var postData = {
			bugid:_bugid,
			comment:_comment,
			status:_status,
			msgNotify:_msg,	
			rtxNotify:_rtx,
			emailNotify:_email,
			inviteUser:_ivtuser,
			tobugUser:_tobugUser
		};
		$.post(url,postData,function(json){
			alert(L('bug.submit_succ'));
			location.reload(false);
		},'json');
	});

	// UL菜单切换
	$('#ulmenu > li').click(function(){
		$('#ulmenu > li').removeClass();
		$(this).addClass('on');
		$('.infodiv').hide();
		$('#'+$(this).attr('item')).show();
	});
	$('#ulmenu > li').eq(0).click();
	
	// 邀请的用户选择
	$('#inviteBox').click(function(){
		if($(this).is(':checked')){
			$('#inviteSpan').show();
		}else{
			$('#inviteSpan').hide();
		}
	});
	// 激活者输入框
	$('#ivtuser').focus(function(){
		if(this.value == this.defaultValue){
			this.value = '';
		}
	});
	// 选择邀请用户
	$('#selInviteUset').click(function(){
		selectUserBox(function(users){
			$('#ivtuser').val(users);
		});
		return false;
	});
	// 选择BUG重新指定的用户
	$('#selToUset').click(function(){
		selectUserBox(function(users){
			$('#bugToUser').val(users);
		},true,true);
		return false;
	});
	
	// 通知是否可用
	if(!G_rtx_notify){
		$('#notifyRtx').attr('checked',false).attr('disabled',true);
	}
	if(!G_email_notify){
		$('#notifyEmail').attr('checked',false).attr('disabled',true);
	}	
});
