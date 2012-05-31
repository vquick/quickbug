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
	
	// 搜索按钮
	$('#searchBtn').click(function(){
		var url = site_url('bug','search',$('#searchform').serialize());
		location.href = url;
	});
	
	// 得到所有的项目
	var url = site_url('bug','gets','opt=project');
	$.get(url,function(json){
		var html = parse_template('options_tpl',json.data);
		$('#projectid').append(''+html);
		// 如果有参数则进行初始化
		var pid = get_url_param('projectid');
		if(pid != ''){
			$('#projectid').val(pid);
			$('#projectid').change();
		}
	},'json');

	// 选择用户
	$('#selToUser,#selCatUser').click(function(){
		var saveId = $(this).attr('id')=='selToUser' ? '#touserid' : '#createuid';
		selectUserBox(function(users){
			$(saveId).val(users);
		},true,true);
		return false;
	});
	
	// 初始化
	init();
});

// 初始化控件
function init(){
	// 初始化版本和模块
	setTimeout(function(){
		$('#verid').val(get_url_param('verid'));
		$('#moduleid').val(get_url_param('moduleid'));
	},400);
	$('input[type="radio"][value="'+get_url_param('opt')+'"]').attr('checked',true);
	$('select[name="select[bugid]"]').val(get_url_param('select[bugid]'));
	$('select[name="select[subject]"]').val(get_url_param('select[subject]'));
	$('select[name="select[touserid]"]').val(get_url_param('select[touserid]'));
	$('select[name="select[createuid]"]').val(get_url_param('select[createuid]'));
	$('#bugid').val(get_url_param('bugid'));
	$('#subject').val(get_url_param('subject'));
	$('#touserid').val(get_url_param('touserid'));
	$('#createuid').val(get_url_param('createuid'));
	$('#starttime').val(get_url_param('starttime'));
	$('#endtime').val(get_url_param('endtime'));
	// 初始化复选框
	for(var i=1; i<=10; ++i){
		if(get_url_param('severity['+i+']') != ''){
			$('input[name="severity['+i+']"]').attr('checked',true);
		}
		if(get_url_param('bugtype['+i+']') != ''){
			$('input[name="bugtype['+i+']"]').attr('checked',true);
		}
		if(get_url_param('status['+i+']') != ''){
			$('input[name="status['+i+']"]').attr('checked',true);
		}
		if(get_url_param('priority['+i+']') != ''){
			$('input[name="priority['+i+']"]').attr('checked',true);
		}
	}
}