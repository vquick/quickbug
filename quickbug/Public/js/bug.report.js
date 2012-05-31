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
	
	// 得到所有的项目
	var url = site_url('bug','gets','opt=project');
	$.get(url,function(json){
		var html = parse_template('options_tpl',json.data);
		$('#projectid').append(html);
		// 如果是查询则初始化
		$('#projectid').val(get_url_param('projectid')).change();
	},'json');
	
	// 统计查询
	$('#countBtn').click(function(){
		if($('#projectid').val() < 1){
			alert(L('bug.select_project'));
			return false;
		}
		var param = 'projectid='+$('#projectid').val();
		param += '&verid='+$('#verid').val();
		param += '&moduleid='+$('#moduleid').val();
		param += '&counttype='+$('input[name="counttype"]:checked').val();
		var url = site_url('bug','report',param);
		location.href = url;
	});
	
	// 初始化 
	init();
});
// 初始化过程
function init(){
	setTimeout(function(){
		$('#verid').val(get_url_param('verid'));
		$('#moduleid').val(get_url_param('moduleid'));
	},400);
	var t = get_url_param('counttype');
	$('input[name="counttype"][value="'+t+'"]').attr('checked',true);
	
	// 显示 flash报表
	var jsonurl = encodeURIComponent(no_cache_url(site_url('bug','flash','type=bar')));
	swfobject.embedSWF('chart/open-flash-chart.swf','flash_chart_bar','100%','100%','9.0.0',"expressInstall.swf",{"data-file":jsonurl});	
	
	jsonurl = encodeURIComponent(no_cache_url(site_url('bug','flash','type=pie')));
	swfobject.embedSWF('chart/open-flash-chart.swf','flash_chart_pie','100%','100%','9.0.0',"expressInstall.swf",{"data-file":jsonurl});	
}