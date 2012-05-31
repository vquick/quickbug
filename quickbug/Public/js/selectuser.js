/**
 * 弹出用户选择框选择用户
 * @param succCallback 确定后的回调函数,将会把用户所选择的所有用户名传值过来
 * @param isOneSelect 是否只能选择一个用户,默认下可以选择多个
 * @param inputOnlyRead 输入框是否只读,默认下可以编辑
 **/
function selectUserBox(succCallback,isOneSelect,inputOnlyRead){
	if(! isOneSelect){
		isOneSelect = false;
	}
	if(! inputOnlyRead){
		inputOnlyRead = false;
	}
	// 绑定DOM的事件
	var onLoad = function(){
		// 输入框是否是只读
		if(inputOnlyRead){
			$('#select_users').attr('readonly',true);
		}
		// 如果是单选则不要显示提示
		if(isOneSelect){
			$('#select_tips').hide();
		}
		// 用户组更改
		$('#select_usergroup').change(function(){
			var gid = $(this).val();
			// 清空用户ID
			$('#select_userid').empty().append('<option value="0">'+L('user.select_user')+'</option>');
			// 默认值
			if(gid == 0){
				return false;
			}
			// 得到所有用户
			$.get(site_url('user','getUsers','groupid='+gid),function(json){
				var html = '',list = json.data.userlist;
				for(var i=0; i<list.length; ++i){ 
					html += '<option value="'+list[i].userid+'">'+list[i].username+'</option>';
				}
				$('#select_userid').append(html);
			},'json');
		});
		// 用户更改
		$('#select_userid').change(function(){
			// 没有选择用户
			if(this.value < 1){
				return false;
			}
			
			var uname = $(this).find('option[selected]').text();
			// 如果只能选择一个用户
			if(isOneSelect){
				$('#select_users').val(uname);
				return false;
			}
			// 初始化输入框
			var userinput = $('#select_users')[0];
			// 查询是否已经输入框中了
			var spr = '';
			if(userinput.value != ''){
				if(userinput.value.indexOf(uname) != -1){
					return false;
				}
				spr = ',';
			}
			userinput.value += spr + uname;
		});
		// 确定按钮
		$('#select_btn').click(function(){
			var user = $('#select_users').val();
			if(user == ''){
				alert(L('common.no_select_user'));
				return false;
			}
			// 关闭层
			pop_div_close();
			// 回调用户的函数
			succCallback(user);
		});
	}
	
	// 得到选择框的HTML
	var htmlCallback = function(){
		$.get(site_url('user','selectUser'),function(html){
			$('#pop_box_centent').html(html);
			onLoad();
		});
	}
	
	// 弹出选择框
	var title = L('common.select_user') + (isOneSelect ? '('+L('common.radio')+')' : '('+L('common.checkbox')+')');
	pop_div(title,null,htmlCallback);	
}
