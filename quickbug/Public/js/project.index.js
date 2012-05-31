// 全局变量
var global_projectid = 0;

// 刷新当前窗口
function flushUrl(){
	location.reload(false);
}

// 初始化过程
function init_run(){
	// 初始化
	if(get_url_param('projectid') != ''){
		var pid = get_url_param('projectid');
		$('#project').val(pid);
		global_projectid = pid;	
	}else{
		global_projectid = $('#project').val();
	}
}

// 项目管理过程
function init_project_manage(){
	// 用户添加与编辑时的检测过程
	var checkinput = function(){
		if($('#projectname').val()==''){
			alert(L('project.project_name_not_empty'));
			return false;
		}
		return true;
	}	
	
	// 添加项目时执行的操作
	var bind_add = function(){
		// 提交
		$('#projectSubmitBtn').unbind().click(function(){
			// 测试输入
			if(! checkinput()){
				return false;
			}
			// 提交表单
			var poststr = $("#projectfrom").serialize();
			$.post(site_url('project','addproject'),poststr,function(json){
				if(json.result != 0){
					alert(json.message);
				}else{
					alert(L('project.add_project_succ'));
					// 如果是第一次添加项目则直接刷新
					if($('#project > option').length < 1){ // 第一次添加项目时 '#project' 列表是不存在的
						flushUrl();
					}else{
						// 加到下拉列表中
						$('#project').append('<option value="'+json.data.projectid+'">'+json.data.projectname+'</option>');
						// 关闭弹出层
						pop_div_close();
					}
				}
			},'json');
		});
	};
	
	// 添加项目
	$('#addProjectBtn,#addOneProjectBtn').click(function(){
		pop_div(L('project.add_project'),'project_tpl',bind_add);	
	});
	
	// 更改项目
	$('#project').change(function(){
		var url = site_url('project','index','projectid='+$(this).val());
		location.href = url;
	});

	// 编辑项目
	$('#editProjectLink').click(function(){
		var proid = $(this).attr('projectid');
		// 绑定编辑
		var bind_edit = function(){
			$.get(site_url('project','projectinfo','projectid='+proid),function(json){
				var info = json.data;
				$('#projectname').val(info.projectname);
				$('#projectinfo').val(info.info);
				// 提交
				$('#projectSubmitBtn').unbind().click(function(){
					// 测试输入
					if(! checkinput()){
						return false;
					}
					// 提交表单
					var poststr = $("#projectfrom").serialize();
					$.post(site_url('project','updateproject','projectid='+proid),poststr,function(json){
						if(json.result != 0){
							alert(json.message);
						}else{
							alert(L('project.project_edit_succ'));
							flushUrl();
						}
					},'json');
				});				
			},'json');
		}
		pop_div(L('project.edit_project'),'project_tpl',bind_edit,flushUrl);	
		return false;

	});

	// 删除项目
	$('#removeProjectLink').click(function(){
		var proid = $(this).attr('projectid');
		if(window.confirm(L('project.remove_project_tips'))){
			$.get(site_url('project','removeproject','projectid='+proid),function(json){
				if(json.result != 0){
					alert(json.message);
				}else{
					alert(L('project.remove_project_succ'));
					location.href = site_url('project','index');
				}
			},'json');
		}
		return false;
	});
}


// 项目文档管理
function init_docs_manage(){
	// 异步编辑文档名
	bind_ajax_edit(site_url('project','updatedoc'),null,'ajax_edit_doc');
	// 异步删除文档
	bind_ajax_remove(site_url('project','removedoc'),function(id){
		$('#remove_doc_tr_'+id).remove();
	},'ajax_remove_docs');

	var G_n = 0;
	// 绑定添加文档
	$('#addDocLink').click(function(){
		$('#uploadBtnDiv').show();
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
			if(G_n < 1){
				$('#uploadBtnDiv').hide();
			}
			return false;
		});
		return false;
	});

	// 绑定上传按钮
	$('#uploadDocBtn').click(function(){
		if($('#submit_iframe').length < 1){
			$(document.body).append('<iframe width="0" height="0" src="" name="submit_iframe" id="submit_iframe" style="display:none;" />');
		}
		this.form.action = site_url('project','uploaddoc');
		this.form.target = 'submit_iframe';
		this.form.submit();
	});	

}

// 上传文件后的回调
function updocs_callback(json){
	if(json.result != 0){
		alert(json.message);
		return false;
	}
	alert(L('project.doc_upload_succ'));
	flushUrl();
}


// 初始化项目文档管理
function init_vers_manage(){
	// 添加版本
	$('#addVerBtn').click(function(){
		var vname = $('#vername').val();
		if(vname != ''){
			$.post(site_url('project','addvers'),{projectid:global_projectid,vername:vname},function(json){
				//alert('添加版本成功!');
				flushUrl();
			},'json');
		}
	});

	// 异步编辑文档名
	bind_ajax_edit(site_url('project','updatever'),null,'ajax_edit_vers');

	// 异步删除文档
	bind_ajax_remove(site_url('project','removever'),function(id){
		$('#remove_ver_tr_'+id).remove();
	},'ajax_remove_vers');
}

// 初始化项目模块管理
function init_module_manage(){
	// 添加模块
	$('#addModBtn').click(function(){
		var mname = $('#modname').val();
		if(mname != ''){
			$.post(site_url('project','addmodule'),{projectid:global_projectid,modname:mname},function(json){
				//alert('添加模块成功!');
				flushUrl();
			},'json');
		}
	});

	// 异步编辑文档名
	bind_ajax_edit(site_url('project','updatemodule'),null,'ajax_edit_module');

	// 异步删除文档
	bind_ajax_remove(site_url('project','removemodule'),function(id){
		$('#remove_module_tr_'+id).remove();
	},'ajax_remove_module');
}


// 自动运行
$(function(){
	// 项目管理
	init_project_manage();

	// 初始化项目文档管理
	init_docs_manage();

	// 初始化项目版本管理
	init_vers_manage();

	// 初始化项目模块管理
	init_module_manage();

	// 初始化
	init_run();
});
