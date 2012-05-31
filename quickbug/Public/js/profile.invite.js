$(function(){
	// 绑定删除
	bind_ajax_remove(site_url('profile','removeinvite'),function(id){
		$('#tr_'+id).remove();
	});
	
	// 绑定全选
	$('#chkall').click(function(){
		$('input[type="checkbox"]').attr('checked', $(this).is(':checked'));
	});
});
