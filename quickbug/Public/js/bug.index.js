$(function(){
	// 得到查询URL
	var queryUrl = function(action){
		action || (action='index');
		// 常规参数
		var params = 'projectid='+$('#projectid').val();
		params += '&bugopt='+$('#bugopt').val();
		params += '&status='+$('#bugstatus').val();
		params += '&priority='+$('#priority').val();
		params += '&verid='+$('#verid').val();
		params += '&moduleid='+$('#moduleid').val();
		// 用户选择
		params += '&userid='+$('#userid').val();
		params += '&usergroup='+$('#usergroup').val();
		params += '&usertype='+$('#usertype').val();
		// 最后修改时间
		params += '&lastdate='+$('#lastdate').val();
		// 搜索关键字
		params += '&searchkey='+$('#search_text').val();
		
		return site_url('bug',action,params);
	}
	// 绑定下拉列表
	$('#list_title > select,#projectid').change(function(){
		location.href = queryUrl();
	});
	
	// 绑定查询按钮
	$('#search_btn').click(function(){
		location.href = queryUrl();
	});
		
	// 绑定排序的链接
	$('a[order]').click(function(){
		var url = queryUrl();
		url += "&order="+$(this).attr('order');
		url += "&by="+$(this).attr('by');
		location.href = url;
		return false;
	});
	
	// 初始化排序的A链接
	$('a[order]').each(function(){
		var orderkey = $(this).attr('order');
		// 如果有值，证明参考了查询排序，要切换排序类型
		if(get_url_param('order') == orderkey){
			var by = get_url_param('by');
			var byval = (by=='' || by=='1') ? 0 : 1;
			$(this).attr('by',byval);
			// 加上箭头
			var txt = $(this).text() + (byval==0 ? '↓' : '↑');
			$(this).text(txt);
		}
	});
	
	// 切换项目名称
	$('#proTitle').click(function(){
		$('#projectid').show(); //fadeIn(200);
		$('#proTitle').hide();
	});

	// 更多搜索条件
	$('#moreSearch').toggle(function(){
		$(this).html('&lt;&lt;'+L('bug.simplify_condition'));
		$('#moreSearchDiv').slideDown();
	},function(){
		$(this).html(L('bug.more_condition')+'&gt;&gt;');
		$('#moreSearchDiv').slideUp();
		// 如果是精简条件的话则要重置已经选择的更多条件
		$('#lastdate').val('');
		$('#usergroup').val(0).change();
	});
	
	// =================== 导出Excel ============================
	// 效果
	$('#exportExcel').toggle(function(){
		$(this).html('&lt;&lt;'+L('bug.export_excel'));
		$('#exportExcelDiv').slideDown();
		return false;
	},function(){
		$(this).html(L('bug.export_excel')+'&gt;&gt;');
		$('#exportExcelDiv').slideUp();
		return false;
	});	
	// 全选
	$('#chkall').click(function(){
		$('input[name="fields[]"]').attr('checked', this.checked);
	});
	// 导出提交
	$('#exportBtn').click(function(){
		var url = G_quickbug_base_url + location.search.replace('a=index','a=outexcel');
		url += '&'+$('#fieldsForm').serialize();
		window.open(url);
		return false;
	});	
	// =========================================================
	
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
	// 查询
	$('#searchBtn').click(function(){
		location.href = queryUrl();
	});
	// 恢复
	$('#retBtn').click(function(){
		$('#moreSearch').click();
	});
	
	// 绑定保存为默认的项目
	$('#saveDefaultBtn').click(function(){
		var msg = L('bug.save_succ');
		var id = $(this).attr('projectid');
		var val = location.search;
		if(val == '?c=bug&a=index'){
			alert(msg);
			return false;
		}
		var url = site_url('profile','savedefault','type=0&val='+encodeURIComponent(val));
		$.get(url,function(json){
			alert(msg);
		},'json');
		return false;
	});
	
	// 初始化搜索关键字
	$('#search_text').val(get_url_param('searchkey'));
	
	// 初始化下拉列表
	$('#projectid').val(get_url_param('projectid'));
	$('#bugopt').val(get_url_param('bugopt'));
	$('#bugstatus').val(get_url_param('status'));
	$('#priority').val(get_url_param('priority'));
	$('#verid').val(get_url_param('verid'));
	$('#moduleid').val(get_url_param('moduleid'));
	
	// 初始化用户选择
	$('#usertype').val(get_url_param('usertype'));
	$('#usergroup').val(get_url_param('usergroup')).change();
	setTimeout(function(){
		$('#userid').val(get_url_param('userid'));
	}, 300);
	
	// 初始化最后修改时间
	$('#lastdate').val(get_url_param('lastdate'));
	
	// 如果有选择更多的搜索条件
	if(get_url_param('userid') > 0 || get_url_param('lastdate')!=''){
		$('#moreSearch').click();
	}
	
	// 如果是第一次来到这个页面则智能自动选择
	if(location.search == '?c=bug&a=index'){
		// 如果有设置默认的查询条件
		if(G_defaultBugSearch != ''){
			location.href = G_quickbug_base_url+G_defaultBugSearch;
		}else{
			// 是开发组或测试组的用户
			if(G_groupType > 0 ){
				$('#bugopt').val(G_groupType).change();
			}
		}
	}
});
