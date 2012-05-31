$(function(){
	// 刷新当前页面
	var flush = function(){
		location.reload(false);
	}
	// 删除文件后刷新当前页面
	bind_ajax_remove(site_url('sys','database'),function(){
		flush();
	});
	// 备份
	$('#saveBtn').click(function(){
		$(this).hide();
		$('#loadimg').show();
		var url = site_url('sys','savedb');
		$.get(url,function(json){
			alert(L('sys.db_backup_ok'));
			flush();
		},'json');
		return false;
	});
});
