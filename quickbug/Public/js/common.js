/**
 * 系统公共函数库
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */

/**
 * 得到URL查询的键值,如 ?id=10 中的ID的值
 * @param string key :GET参数
 */
function get_url_param(key)
{
	key = key .replace(/\[/g,"%5B");
	key = key .replace(/\]/g,"%5D");
	var query = location.search;
	var reg = "/^.*[\\?|\\&]" + key + "\\=([^\\&]*)/";  
	reg = eval(reg);  
	var ret = query.match(reg);
	if (ret != null) {  
		return decodeURIComponent(ret[1]);  
	} else {
		return "";  
	}   
}

/**
 * 给URL加随机数防止缓存
 * @param stirng  url :URL地址
 */
function no_cache_url(url)
{
	if(url.indexOf('?') == -1)
	{
		url += '?';
	}else{
		url += '&';
	}
	url += 'rand='+Math.random();
	return url;
}

/**
 * 得到COOKIE值
 * @param stirng  name :COOKIE名
 */
function get_cookie(name){
	try{
		return (document.cookie.match(new RegExp("(^"+name+"| "+name+")=([^;]*)"))==null)?"":decodeURIComponent(RegExp.$2);
	}
	catch(e){
		return (document.cookie.match(new RegExp("(^"+name+"| "+name+")=([^;]*)"))==null)?"":RegExp.$2;
	}
}

/**
 * 写COOKIE
 * @param stirng name   :COOKIE名
 * @param stirng value  :COOKIE值
 * @param int second    :有效期，单位:秒，不传时为当前会话
 * @param stirng domain :作用域,如果不定义则使用当前域
 */
function set_cookie(name,value,second,domain){
	if(!domain){
		domain = location.hostname;
	}
	if(arguments.length>2){
		var expireDate=new Date(new Date().getTime()+(second*3600*1000));
		document.cookie = name + "=" + encodeURIComponent(value) + "; path=/; domain="+domain+"; expires=" + expireDate.toGMTString() ;
	}else{
		document.cookie = name + "=" + encodeURIComponent(value) + "; path=/; domain="+domain;
	}
}

/**
 * 得到框架的实际URL地址
 * @param stirng controller :控制器名
 * @param stirng action     :要执行的动作
 * @param stirng params     :其它附加参数,如 "id=10&name=bug",可以不传
 */
function site_url(controller,action,params){
	var url = G_quickbug_base_url+'?c='+controller;
	if(action){
		url += '&a='+action;
	}
	if(params){
		url += '&'+params;
	}
	return url;
}

/**
 * 得到JS中对应的语言
 * @param stirng items     :键值,如 "index.about_title"
 * @param object vars      :要替换的值,如 ['title']
 */
function L(items,vars){
	var text = LANG;
	$.each(items.split('.'), function(i,key){
		text = text[key];
	});
	if(typeof(vars) == 'object'){
		$.each(vars,function(i,n){
			text = text.replace('/'+(i+1) , n);
		});
	}
	return text;
}

/**
 * 绑定异步编辑
 * @param string ajaxEditUrl   :异步编辑的URL地址
 * @param function callbackFun :删除成功后的回调函数,回调时会将返回的JSON数据传入,如 callbackFun(json);
 * @param string className     :类名,主要用于获取元素,默认为:"ajax_edit"
 *
 * 使用方法：
 * 1：必需要有以下这种DOM定义
 * <td editval="V哥" editfield="docname" editid="1" class="ajax_edit">V哥</td>
 *
 * 2:属性说明
 * ajax_edit :绑定时的class名称
 * editid    :编辑的主键ID
 * editval   :编辑时的初始值
 * editfield :编辑的字段名称
 */
function bind_ajax_edit(ajaxEditUrl,callbackFun,className){  
	if(! className){
		className = 'ajax_edit';
	}
	// 单元格式单击事件
	var tdclick = function(){
		var id = $(this).attr('editid');
		var field = $(this).attr('editfield');
		var val = $(this).attr('editval'); 
		var idname = 'edit_'+field+'_'+id;
		// 生成 input
		var html = '<input type="text" style="width:100px;" id="'+idname+'" value="'+val+'" />';
		$(this).html(html);
		// 绑定输入框
		$('#'+idname).blur(function(){
			// 重新设置TD内容
			var td = $(this).parent();
			td.attr('editval',this.value); 
			td.html(this.value);
			// 重新绑定TD
			$('.'+className).one('click',tdclick);	
			// 如果没有修改则直接退出
			if(this.value == val){
				return;
			}
			// 导步更新内容
			$.post(ajaxEditUrl,{editid:id,editfield:field,editval:this.value},function(json){
				if(callbackFun){
					callbackFun(json);
				}
			},'json');
		}).focus();
	}
	$('.'+className).one('click',tdclick).css({cursor:'pointer '});
}

/**
 * 异步更新单选
 * @param string ajaxEditUrl   :异步编辑的URL地址
 * @param function callbackFun :删除成功后的回调函数,回调时会将返回的JSON数据传入,如 callbackFun(json);
 */
function bind_ajax_radio(ajaxEditUrl,callbackFun){  
	$('.ajax_radio').change(function(){
		var id = $(this).attr('editid');
		var field = $(this).attr('editfield');
		var val = this.value;
		// 导步更新内容
		$.post(ajaxEditUrl,{editid:id,editfield:field,editval:val},function(json){
			if(callbackFun){
				callbackFun(json);
			}
		},'json');
	});
}

/**
 * 绑定异步删除，一般是A标记
 * @param string ajaxRemoveUrl     :异步删除的URL地址
 * @param function callbackFun     :删除成功后的回调函数,回调时会将id主键和后台返回的JSON数据传入,如 callbackFun(id,json);
 * @param string className         :类名,主要用于获取元素,默认为:"ajax_remove"
 * @param function confirmCallback :自定义对话提示,如果定义了则会自己将当前删除的名称传给这个函数，如果返回 false则终止删除操作,
 * 
 * 使用方法：
 * 1：必需要有以下这种DOM定义
 * <a href="javascript:;" removename="Ver 1.0" removeid="14" class="ajax_remove">删除</a>
 * 
 * 2:属性说明
 *  removename  :对话框架显示要删除的名称
 *  removeid    :删除的主健ID
 *  ajax_remove :绑定的类名
 */
function bind_ajax_remove(ajaxRemoveUrl,callbackFun,className,confirmCallback){
	if(!className){
		className = 'ajax_remove';
	}
	$('.'+className).unbind('click').click(function(){
		var id = $(this).attr('removeid');
		var name = $(this).attr('removename');
		var msg = L('common.remove_confirm',[name]);
		if(confirmCallback){
			if(! confirmCallback(name)){
				return false;
			}
		}
		if(window.confirm(msg)){
			$.get(ajaxRemoveUrl,{removeid:id},function(json){
				if(callbackFun){
					callbackFun(id,json);
				}
			},'json');
		}
		return false;
	});
}

/**
 * 显示公共的弹出层
 * @param string title           :弹出层的标题
 * @param string centenid        :弹出层的内容HTML容器ID
 * @param function loadcallback  :显示后的回调函数,没有则传 false
 * @param function closecallback :关闭层后的回调处理
 */
function pop_div(title,centenid,loadcallback,closecallback){
	// 弹出层内容
	if(centenid){
		$('#pop_box_centent').html($('#'+centenid).val());
	}
	// 标题
	$('#pop_box_title_text').html(title);
	// 关闭回调
	var closefun = function(){
		$('#pop_box_centent').html('');
		if(closecallback){
			closecallback();
		}
	}
	// 显示
	$('#pop_box_div').popShow({onclose:closefun});
	if(loadcallback){
		loadcallback();
	}
	// 如果是IE则要处理一下弹出层的宽度
	if($.browser.msie){
		setTimeout(function(){
			var _width = $('#pop_box_centent').children().eq(0).width();
			$('#pop_box_div').css({width:_width+2});
			$(window).scroll();
		},200);
	}
}

/**
 * 人为触发关闭弹出层
 *
 */
function pop_div_close(){
	$('#pop_box_div').popHide();
}

/**
 * 自动调整图片的高和宽
 * @param object imgObj :图片对象
 * @param int MaxW      :最大宽
 * @param int MaxH      :最大高
 */
function resizeImage(imgObj,MaxW,MaxH){
	var oldImage = new Image();
	oldImage.src = imgObj.src;
	var dW = oldImage.width,dH = oldImage.height;
	if(dW>MaxW || dH>MaxH) {
		var a = dW/MaxW, b = dH/MaxH;
		if(b > a){
			a = b;
		}
		dW = dW/a;
		dH = dH/a;
	}
	if(dW > 0 && dH > 0) {
		imgObj.width = dW;
		imgObj.height = dH;
	}	
}

/**
 * Fckeditor 编辑器的常用操作
 *
 */
var Fckedit = {
	// 设置编辑器的内容
	set:function(html){
		// 测试过程中发现 FCKeditorAPI 在 onload() 后不一定马上有的
		function _set(){
			if(typeof(FCKeditorAPI) != 'undefined'){
				FCKeditorAPI.GetInstance('fckEditInfo').SetHTML(html);
			}else{
				setTimeout(_set, 200);
			}
		}
		setTimeout(_set, 200);
	},
		
	// 得到当前编辑器中的内容
	get:function(){
		try{
			return FCKeditorAPI.GetInstance('fckEditInfo').GetXHTML(true);
		}catch(e){
			return '';
		}
	}
}


/**
 * RTX 操作
 *
 */
var RTX = (function(){
	var isIE = document.all ? true : false;
	if(isIE){
		try{
			var objKerRoot = RTXAX.GetObject("KernalRoot");
			var objBuddyManager = objKerRoot.RTXBuddyManager;
			var objApp = RTXAX.GetObject("AppRoot");
			var objHelper = objApp.GetAppObject("RTXHelper");
			var objIm = objApp.GetAppObject( "RTXPlugin.IM" );
			var _account = objKerRoot.Account;
			var _getName = function(){
				var name = objBuddyManager.Buddy(_account).name;
				var iPos = name.indexOf("-");			
				if(iPos <= 0){
					iPos = name.indexOf("－");
				}
				if(iPos <= 0){
					iPos = name.indexOf("_");
				}
				if(iPos <= 0){
					iPos = name.indexOf(" ");
				}	
				return iPos>0 ? name.substr(0, iPos) : name;
			};
			var user = {
				account : _account,// 帐号
				//email : _account+'@xunlei.com',// 邮箱
				username : _getName(),// 姓名
				dept : objHelper.GetBuddyDept(_account) // 部门
			}
		}catch(e){
			isIE = false;
		}
	}

	// 返回接口
	return {
		// 得到当前用户信息
		getUser:function(){
			return isIE ? user : false;
		},
		// 打开对话框
		openDiag:function(username){
			isIE && objIm.SendIM(username);
		}
	}
})();


