$(function(){
	
	// 刷新当前窗口
	var flushUrl = function(){
		location.reload(false);
	}
	
	// 用户添加与编辑时的检测过程
	var checkinput = function(isCheckPass){
		if($('#username').val()==''){
			alert(L('user.username_not_empty'));
			return false;
		}
		if($('#truename').val()==''){
			alert(L('user.truename_not_empty'));
			return false;
		}
		if(isCheckPass && $('#passwd').val()==''){
			alert(L('user.passwd_not_empty'));
			return false;
		}
		var email = $('#email').val();
		if(email=='' || !/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([_a-z0-9]+\.)+[a-z]{2,5}$/.test(email)){
			alert(L('user.email_format_error'));
			return false;
		}
		return true;
	}	
	
	// 添加用户时执行的操作
	var bind_add = function(){
		// 自动加上真实姓名
		$('#username').blur(function(){
			if($('#truename').val() == ''){
				$('#truename').val(this.value);
			}
		});
		// 注册提交
		$('#submitbtn').unbind().click(function(){
			// 测试输入
			if(! checkinput(true)){
				return false;
			}
			// 提交表单
			var poststr = $("#submitfrom").serialize();
			$.post(site_url('user','adduser'),poststr,function(json){
				if(json.result != 0){
					alert(json.message);
				}else{
					alert(L('user.user_add_succ'));
					$(':input[type=text],:password').val('');
				}
			},'json');
		});
	};
	
	// 弹出层方式添加用户
	$('#addbut').click(function(){
		pop_div(L('user.add_user'),'user_addedit_tpl',bind_add,flushUrl);	
	});	
	
	// 异步绑定编辑
	bind_ajax_edit(site_url('user','edituser'));
	bind_ajax_radio(site_url('user','edituser'));
	bind_ajax_remove(site_url('user','removeuser'),function(userid){
		$('#remove_tr_'+userid).remove();
	},null,function(name){
		var msg = L('user.remove_user_tips');
		return window.confirm(msg);
	});
	// 对话框的编辑
	$('.edit_user').click(function(){
		var userid = $(this).attr('editid');
		// 绑定编辑
		var bind_edit = function(){
			$.get(site_url('user','userinfo','userid='+userid),function(json){
				var info = json.data;
				$('#username').val(info.username);
				$('#truename').val(info.truename);
				$('#email').val(info.email);
				$(':radio[name="usertype"][value="'+info.usertype+'"]').attr('checked',true);
				$('#groupid').val(info.groupid);  
				// 注册提交
				$('#submitbtn').unbind().click(function(){
					// 测试输入
					if(! checkinput(false)){
						return false;
					}
					// 提交表单
					var poststr = $("#submitfrom").serialize();
					$.post(site_url('user','updateuser','userid='+userid),poststr,function(json){
						if(json.result != 0){
							alert(json.message);
						}else{
							alert(L('user.edit_user_succ'));
							flushUrl();
						}
					},'json');
				});				
			},'json');
		}
		pop_div(L('user.edut_user'),'user_addedit_tpl',bind_edit,flushUrl);	
		return false;
	});
	
	// 搜索过程
	$('#searchbut').click(function(){
		var params = $("#searchfrom").serialize();
		location.href = site_url('user','index',params);
	});
	
	// 显示所有
	$('#showall').click(function(){
		location.href = site_url('user','index');
		return false;
	});
	
	// 初始化搜索的参数
	$('#key').val(get_url_param('key'));
	$('#usertype').val(get_url_param('usertype'));
	$('#group').val(get_url_param('group'));
	$('#enable').val(get_url_param('enable'));	
});