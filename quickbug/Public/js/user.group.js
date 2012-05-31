$(function(){
	
	// 刷新当前窗口
	var flushUrl = function(){
		location.reload(false);
	}
	
	// 用户添加与编辑时的检测过程
	var checkinput = function(){
		if($('#groupname').val()==''){
			alert(L('user.group_name_not_empty'));
			return false;
		}
		return true;
	}	
	
	// 添加用户时执行的操作
	var bind_add = function(){
		// 用户组名
		$('#groupname').unbind().blur(function(){
			if($('#info').val() == ''){
				$('#info').val(this.value);
			}
		});
		// 注册提交
		$('#submitbtn').unbind().click(function(){
			// 测试输入
			if(! checkinput()){
				return false;
			}
			// 提交表单
			var poststr = $("#submitfrom").serialize();
			$.post(site_url('user','addusergroup'),poststr,function(json){
				if(json.result != 0){
					alert(json.message);
				}else{
					alert(L('user.group_add_succ'));
					$(':input[type=text]').val('');
				}
			},'json');
		});
	};
	
	// 弹出层方式添加用户
	$('#addbut').click(function(){
		pop_div(L('user.add_group'),'group_addedit_tpl',bind_add,flushUrl);	
	});	
	
	// 异步绑定编辑
	bind_ajax_edit(site_url('user','editusergroup'));
	bind_ajax_remove(site_url('user','removeusergroup'),function(groupid){
		$('#remove_tr_'+groupid).remove();
	},null,function(name){
		var msg = L('user.remove_group_tips');
		return window.confirm(msg);
	});
	
	// 对话框的编辑
	$('.edit_group').click(function(){
		var groupid = $(this).attr('editid');
		// 绑定编辑
		var bind_edit = function(){
			$.get(site_url('user','usergroupinfo','groupid='+groupid),function(json){
				var info = json.data;
				$('#groupname').val(info.groupname);
				$('#info').val(info.info);
				// 注册提交
				$('#submitbtn').unbind().click(function(){
					// 测试输入
					if(! checkinput()){
						return false;
					}
					// 提交表单
					var poststr = $("#submitfrom").serialize();
					$.post(site_url('user','updateusergroup','groupid='+groupid),poststr,function(json){
						if(json.result != 0){
							alert(json.message);
						}else{
							alert(L('user.group_edit_succ'));
							flushUrl();
						}
					},'json');
				});				
			},'json');
		}
		pop_div(L('user.edit_group'),'group_addedit_tpl',bind_edit,flushUrl);	
		return false;
	});
	
	// 搜索过程
	$('#searchbut').click(function(){
		var params = $("#searchfrom").serialize();
		location.href = site_url('user','group',params);
	});
	
	// 显示所有
	$('#showall').click(function(){
		location.href = site_url('user','group');
		return false;
	});
	
	// 初始化搜索的参数
	$('#key').val(get_url_param('key'));
});