$(function(){
	
	// 给数据表格 tr 加上移动效果
	$('#list_table > tr,.list_table > tr').hover(function(){
		$(this).css({'background-color':'rgb(224,230,235)'});
	},function(){
		$(this).css({'background-color':'rgb(255,255,255)'});
	});
	
	// 给图片加上预览效果
	$('a[isimg="1"]').hover(function(e){
		//var eve = e || window.event;
		//alert(eve.clientX+'='+eve.clientY);
		// 如果没有定义 "imgsrc" 属性则读取 "href" 属性
		var src = $(this).attr('imgsrc');
		if(src == undefined){
			src = $(this).attr('href');
		}
		$('#view_img_div > img').attr('src', src);
		$('#view_img_div > img').unbind().load(function(){
			// 自动调整
			resizeImage(this,400,400);
			// 设置图片层中位置
			var bodyObj = document.documentElement; //是否支持W3C标准的
			(document.compatMode == 'BackCompat') && (bodyObj = document.body);
			var _left = bodyObj.clientWidth/2 - this.width/2;
			var _top = bodyObj.scrollTop + 10;
			$('#view_img_div').css({left:_left,top:_top});
		});
		$('#view_img_div').fadeIn(200); 
	},function(){
		$('#view_img_div').hide();
	});
	
	// 给RTX用户添加对话框事件
	$('.rtx').click(function(){
		var username = $(this).text();
		RTX.openDiag(username);
		return false;
	});
	
	// 绑定“关于系统”菜单
	$('#about_sys').click(function(){
		pop_div(L('index.about_title'), null, function(){
			$.get(site_url('index','about'),function(html){
				$('#pop_box_centent').html(html);
			});
		});
		return false;
	});
	
	// 系统'心跳'
	(function sysTimer(){
		// 如果没有登录
		if(G_userid < 1){
			return;
		}
		var url = site_url('profile','timer');
		$.get(url,function(json){
			if(json.data > 0){
				$('#inviteNotifyA > span').text(L('common.notice')+'('+json.data+')');
				$('#inviteNotifyA').show();
			}else{
				$('#inviteNotifyA').hide();
			}
		},'json');
		setTimeout(sysTimer, 1000*30);
	})();
});
