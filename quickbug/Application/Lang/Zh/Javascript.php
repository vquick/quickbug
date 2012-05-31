<?php
/**
 * javascript 中要用到的语言
 */
return array(

	// 公共JS函数
	'common'=>array(
		'notice'=>'通知',
		'remove_confirm'=>'您确认要删除 /1 吗？这将不可恢复',
		'select_user'=>'选择用户',
		'no_select_user'=>'没有选择用户!',
		'radio'=>'单选',
		'checkbox'=>'多选',
	),

	// 控制器
	'index'=>array(
		'about_title'=>'关于系统',
	),

	// 系统管理
	'sys'=>array(
		'db_backup_ok'=>'数据库备份成功',
		'select_notify_type'=>'请选择通知类型，如果全不可选请配置文件 Application/Configs/Sysconfig.php',
		'select_user'=>'请选择用户',
		'notify_not_empty'=>'通知内容不能为空',
		'send_ok'=>'消息发送成功',

		'reg_succ'=>'恭喜:自动注册成功,您现在可以 <a href="/1">自动登录</a> 了.',
		'reg_fail'=>'您没有安装RTX或不是用IE浏览器打开的这个地址',
		'save_ok'=>'保存成功',
		'email_suffix_empty'=>'邮箱后缀不能为空',
	),

	// 个人设置
	'project'=>array(
		'project_name_not_empty'=>'项目名称不能为空',
		'add_project_succ'=>'添加项目成功',
		'add_project'=>'添加项目',
		'project_edit_succ'=>'项目编辑成功',
		'edit_project'=>'编辑项目',
		'remove_project_tips'=>'[严重警告]:删除这个项目将会使该项目所有相关的信息丢失，并且不可恢复。你确认删除吗？',
		'remove_project_succ'=>'项目删除成功',
		'doc_upload_succ'=>'文档上传成功!',
	),

	// 权限设置
	'priv'=>array(
		'priv_submit_succ'=>'权限提交成功',
	),

	// 用户管理
	'user'=>array(
		'username_not_empty'=>'用户名不能为空',
		'truename_not_empty'=>'真实姓名不能为空',
		'passwd_not_empty'=>'密码不能为空',
		'email_format_error'=>'请输入正确的邮箱',
		'user_add_succ'=>'用户添加成功',
		'add_user'=>'添加用户',
		'select_user'=>'选择用户',
		'remove_admin_tips'=>'[严重警告]:删除管理员将会使管理员所创建的用户和项目等资料都会丢失，数据将不可恢复。你确认要删除这个管理员吗？',
		'edit_user_succ'=>'用户编辑成功',
		'edut_user'=>'编辑用户',

		'group_name_not_empty'=>'用户组名不能为空',
		'group_add_succ'=>'用户组添加成功',
		'add_group'=>'添加用户组',
		'remove_group_tips'=>'[严重警告]:如果删除这个用户组，则该组对应所有的用户信息都会丢失，并且数据不可恢复。你确认要删除这个用户组吗？',
		'group_edit_succ'=>'用户组编辑成功',
		'edit_group'=>'编辑用户组',

		'remove_user_tips'=>'[严重警告]:删除用户将会造成这个用户相关的记录不完整，并且数据不可恢复。你确认要删除这个用户吗？',
	),

	// 我的管理
	'profile'=>array(
		'submit_succ'=>'提交成功',
		'tpl_name_not_empty'=>'模板名不能为空',
		'tpl_content_not_empty'=>'模板内容不能为空',
		'add_tpl_succ'=>'添加模板成功',
		'add_bug_tpl'=>'添加缺陷模板',
		'edit_tpl_succ'=>'编辑模板成功',
		'edit_bug_tpl'=>'编辑缺陷模板',
	),

	// BUG管理
	'bug'=>array(
		'save_succ'=>'保存成功',
		'all_ver'=>'所有版本',
		'all_module'=>'所有模块',
		'simplify_condition'=>'精简条件',
		'more_condition'=>'更多条件',
		'export_excel'=>'导出为Excel',
		'select_user'=>'选择用户',
		'select_bugtype'=>'选择缺陷类型',
		'select_frequency'=>'选择重现规律',
		'select_severity'=>'选择严重程度',
		'bug_info_not_empty'=>'缺陷描述不能为空!',
		'subject_not_empty'=>'标题不能为空!',
		'select_module'=>'选择模块',
		'select_ver'=>'选择版本',
		'select_project'=>'请选择项目',
		'no_data_modify'=>'没有数据更改',
		'select_to_user'=>'请选择缺陷要重新指定的用户',
		'submit_succ'=>'提交处理成功!',
	),


);
