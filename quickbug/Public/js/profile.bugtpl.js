$(function(){
	
	// 刷新当前窗口
	var flushUrl = function(){
		location.reload(false);
	}
	
	// 用户添加与编辑时的检测过程
	var checkinput = function(){
		if($('#tplname').val() == ''){
			alert(L('profile.tpl_name_not_empty'));
			return false;
		}
		if(Fckedit.get() == ''){
			alert(L('profile.tpl_content_not_empty'));
			return false;
		}
		return true;
	}	
	
	// 添加用户时执行的操作
	var bind_add = function(){
		// 注册提交
		$('#bugtplSubmitBtn').unbind().click(function(){
			// 测试输入
			if(! checkinput()){
				return false;
			}
			// 提交
			var tname = $('#tplname').val();
			var bultpl = Fckedit.get();
			$.post(site_url('profile','addbugtpl'),{tplname:tname,tplhtml:bultpl},function(json){
				if(json.result != 0){
					alert(json.message);
				}else{
					alert(L('profile.add_tpl_succ'));
				}
				flushUrl();
			},'json');
		});
	};
	
	// 添加模板
	$('#addBugtplBtn').click(function(){
		pop_div(L('profile.add_bug_tpl'),null,function(){
			$.get(site_url('profile','html'),function(html){
				$('#pop_box_centent').html(html);
				bind_add();
			});
		},flushUrl);	
	});	
	
	// 异步绑定编辑
	bind_ajax_edit(site_url('profile','updatebugtpl'));
	bind_ajax_remove(site_url('profile','removebugtpl'),function(groupid){
		$('#remove_tr_'+groupid).remove();
	});
	
	// 对话框的编辑
	$('.edit_bugtpl').click(function(){
		var id = $(this).attr('editid');
		// 绑定编辑
		var bind_edit = function(){
			// 编辑提交
			$('#bugtplSubmitBtn').unbind().click(function(){
				// 测试输入
				if(! checkinput()){
					return false;
				}
				// 提交
				var tname = $('#tplname').val();
				var bultpl = Fckedit.get();
				$.post(site_url('profile','editbugtpl'),{editid:id,tplname:tname,tplhtml:bultpl},function(json){
					if(json.result != 0){
						alert(json.message);
					}else{
						alert(L('profile.edit_tpl_succ'));
					}
					flushUrl();
				},'json');
			});
		}
		pop_div(L('profile.edit_bug_tpl'),null,function(){
			$.get(site_url('profile','html','id='+id),function(html){
				$('#pop_box_centent').html(html);
				bind_edit();
			});
		},flushUrl);		
		return false;
	});
	
});