// 绑定公共事件
function init_bind(initBugTpl){
	// BUG模板
	$('#bugTpl').change(function(){
		var id = $(this).val();
		// 添加模板
		if(id == -1){
			location.href = site_url('profile','bugtpl');
			return false;
		}
		// 改变模块内容
		$.get(site_url('bug','bugtpl','id='+id),function(json){
			Fckedit.set(json.data.tplhtml);
		},'json');
	});
	// 初始化模板
	if(initBugTpl){
		setTimeout(function(){
			$('#bugTpl').change();
		},400);
	}

	// 项目更改
	$('#projectid').change(function(){
		var proid = $(this).val();
		// 清空版本和模块
		$('#verid').empty().append('<option value="0">'+L('bug.select_ver')+'</option>');
		$('#moduleid').empty().append('<option value="0">'+L('bug.select_module')+'</option>');
		// 默认值
		if(proid == 0){
			return false;
		}
		// 得到项目所有的版本和模块
		$.get(site_url('bug','getVersModules','projectid='+proid),function(json){
			$('#verid').append(parse_template('ver_tpl', json.data));
			$('#moduleid').append(parse_template('module_tpl', json.data));
			// 如果版本/模块只有一个可选项的话则自动选项
			if($('#verid > option').size() == 2){
				$('#verid > option').eq(1).attr('selected',true);
			}
			if($('#moduleid > option').size() == 2){
				$('#moduleid > option').eq(1).attr('selected',true);
			}
		},'json');
	});

	// 用户组更改
	$('#usergroup').change(function(){
		var gid = $(this).val();
		// 清空用户ID
		$('#userid').empty().append('<option value="0">'+L('bug.select_user')+'</option>');
		// 默认值
		if(gid == 0){
			return false;
		}
		// 得到所有用户
		$.get(site_url('user','getUsers','groupid='+gid),function(json){
			$('#userid').append(parse_template('user_tpl', json.data));
		},'json');
	});
	// 用户更改
	$('#userid').change(function(){
		/*
		var sel = $(this);
		var txt = sel.val()==0 ? 'Ta' : sel.find('option[value="'+sel.val()+'"]').text();	
		$('.notuser').text(txt);
		*/
	});
	// 添加文档
	var G_n = 0;
	$('#addDocLink').click(function(){
		// 最多一次上传 5 个文档
		++G_n;
		if(G_n > 5){
			G_n = 5;
			return false;
		}
		var data = {n:G_n};
		var html = parse_template('add_docs_tpl',data);
		//alert(html);
		$('#uploadFiles').append(html);
		$('#uploadFiles .a_remove_doc').unbind('click').click(function(){
			--G_n; 
			var id = $(this).attr('divid');
			$('#adddoc_div_'+id).remove();
			return false;
		});
		return false;
	});
	
	// 通知是否可用
	if(!G_rtx_notify){
		$('input[name="notifyRtx"]').attr('checked',false).attr('disabled',true);
	}
	if(!G_email_notify){
		$('input[name="notifyEmail"]').attr('checked',false).attr('disabled',true);
	}
}

// 提交前的验证
function check_submit(){
	if($('#projectid').val() < 1){
		alert(L('bug.select_project'));
		return false;
	}
	if($('#verid').val() < 1){
		alert(L('bug.select_ver'));
		return false;
	}
	if($('#moduleid').val() < 1){
		alert(L('bug.select_module'));
		return false;
	}
	if($('#subject').val() == ''){
		alert(L('bug.subject_not_empty'));
		return false;
	}
	if(Fckedit.get() == ''){
		alert(L('bug.bug_info_not_empty'));
		return false;
	}
	if($('#userid').val() < 1){
		alert(L('bug.select_user'));
		return false;
	}
	if($('#severity').val() < 1){
		alert(L('bug.select_severity'));
		return false;
	}
	if($('#frequency').val() < 1){
		alert(L('bug.select_frequency'));
		return false;
	}
	if($('#bugtype').val() < 1){
		alert(L('bug.select_bugtype'));
		return false;
	}
	return true;	
}