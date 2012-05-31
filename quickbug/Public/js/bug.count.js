$(function(){
	
	// 项目列表更改事件
	$('#projectid').change(function(){
		var url = site_url('bug','gets','projectid='+$(this).val());
		$.get(url,function(json){
			// 版本
			var html = parse_template('options_tpl',{list:json.data.ver});
			$('#verid').empty().append('<option value="0">'+L('bug.all_ver')+'</option>'+html);
			// 模块
			var html = parse_template('options_tpl',{list:json.data.module});
			$('#moduleid').empty().append('<option value="0">'+L('bug.all_module')+'</option>'+html);
		},'json');
	});
	
	// 统计
	$('#countBtn').click(function(){
		var param = 'count=1&projectid='+$('#projectid').val();
		param += '&verid='+$('#verid').val();
		param += '&moduleid='+$('#moduleid').val();
		var url = site_url('bug','morecount',param);
		location.href = url;
	});
	
	// 初始化 
	init();
});
// 初始化过程
function init(){

	// 得到所有的项目
	var url = site_url('bug','gets','opt=project');
	$.get(url,function(json){
		var html = parse_template('options_tpl',json.data);
		$('#projectid').append(html);
		// 如果是查询则初始化
		$('#projectid').val(get_url_param('projectid')).change();
	},'json');

	// 初始化版本和模块
	setTimeout(function(){
		$('#verid').val(get_url_param('verid'));
		$('#moduleid').val(get_url_param('moduleid'));
	},400);
}