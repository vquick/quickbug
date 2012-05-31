$(function(){
	// 绑定事件
	init_bind();
	// 用户选择增加事件
	$('#userid').change(function(){
		var chk = $(this).val()!=bugInfo.touserid ? true : false;
		$(':checkbox[name="notifyEmail"]').attr('checked', chk);
	});
	
	// 初始化项目
	$('#projectid').val(bugInfo.projectid).change();
	// 初始化用户组
	$('#usergroup').val(bugInfo.groupid).change();
	
	// 初始化版本/模块/用户
	setTimeout(function(){
		$('#verid').val(bugInfo.verid);
		$('#moduleid').val(bugInfo.moduleid);
		$('#userid').val(bugInfo.touserid).change();
	},400);
	
	// 异步编辑文档名
	bind_ajax_edit(site_url('bug','updatebugdoc'),null,'ajax_edit_doc');
	// 异步删除文档
	bind_ajax_remove(site_url('bug','removebugdoc'),function(id){
		$('#remove_doc_tr_'+id).remove();
	},'ajax_remove_docs');	
	
	// 提交
	$('button').click(function(){
		var st = $(this).attr('savetype');
		$('#savetype').val(st);
		// 检测
		if(!check_submit()){
			return false;
		}
		// 提交
		this.form.submit();
	});	
});
