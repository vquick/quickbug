<?php
/**
 * javascript 中要用到的语言
 */
return array(

	// 公共JS函数
	'common'=>array(
		'notice'=>'Notice',// '通知',
		'remove_confirm'=>'Confirm remove "/1" It\'s unrecoverable',// '您确认要删除 /1 吗？这将不可恢复',
		'select_user'=>'Choose User',// '选择用户',
		'no_select_user'=>'You haven\'t choose any users',// '没有选择用户!',
		'radio'=>'Single Choice',// '单选',
		'checkbox'=>'Multiple Choice',// '多选',
	),

	// 控制器
	'index'=>array(
		'about_title'=>'About System',// '关于系统',
	),

	// 系统管理
	'sys'=>array(
		'db_backup_ok'=>'Database backup successful',// '数据库备份成功',
		'select_notify_type'=>'Please choose the type of notice, if there is no any option, you can edit the config file "Application/Configs/Sysconfig.php"',// '请选择通知类型.如果全不可选请配置文件 Application/Configs/Sysconfig.php',
		'select_user'=>'Please choose the user',// '请选择用户',
		'notify_not_empty'=>'Content is empty',// '通知内容不能为空',
		'send_ok'=>'Message send successful',// '消息发送成功',

		'reg_succ'=>'Auto Register Successful, Now you can <a href="/1">Auto Login</a>',// '恭喜:自动注册成功,您现在可以 <a href="/1">自动登录</a> 了.',
		'reg_fail'=>'You do not install RTX client or you do not open this page with InternetExplorer',// '您没有安装RTX或不是用IE浏览器打开的这个地址',
		'save_ok'=>'Save OK',
		'email_suffix_empty'=>'Email suffix is empty',
	),

	// 个人设置
	'project'=>array(
		'project_name_not_empty'=>'Project name couldn\'t be empty',// '项目名称不能为空',
		'add_project_succ'=>'Add Project Successful',// '添加项目成功',
		'add_project'=>'Add Project',// '添加项目',
		'project_edit_succ'=>'Project Edit Successful',// '项目编辑成功',
		'edit_project'=>'Edit Project',// '编辑项目',
		'remove_project_tips'=>'[Warning]:remove this project will delete all data of this project, the record is unrecoverable.Do you confirm to remove?',// '[严重警告]:删除这个项目将会使该项目所有相关的信息丢失.并且不可恢复.你确认删除吗？',
		'remove_project_succ'=>'Remove Project Successful',// '项目删除成功',
		'doc_upload_succ'=>'Upload Doc Successful',// '文档上传成功!',
	),

	// 权限设置
	'priv'=>array(
		'priv_submit_succ'=>'Privilege Submit Successful',// '权限提交成功',
	),

	// 用户管理
	'user'=>array(
		'username_not_empty'=>'Username couldn\'t be empty',// '用户名不能为空',
		'truename_not_empty'=>'Nickname couldn\'t be empty',// '真实姓名不能为空',
		'passwd_not_empty'=>'Password couldn\'t be empty',// '密码不能为空',
		'email_format_error'=>'Email Format Incorrect',// '请输入正确的邮箱',
		'user_add_succ'=>'User Add Successful',// '用户添加成功',
		'add_user'=>'Add User',// '添加用户',
		'select_user'=>'Choose User',// '选择用户',
		'remove_admin_tips'=>'[Warning]:remove this admin will delete all data of this admin, the record is unrecoverable and related record will incomplete.Do you confirm to remove?',// '[严重警告]:删除管理员将会使管理员所创建的用户和项目等资料都会丢失.数据将不可恢复.你确认要删除这个管理员吗？',
		'edit_user_succ'=>'Edit User Successful',// '用户编辑成功',
		'edut_user'=>'Edit User',// '编辑用户',

		'group_name_not_empty'=>'Group name couldn\'t be empty',// '用户组名不能为空',
		'group_add_succ'=>'Group Add Successful',// '用户组添加成功',
		'add_group'=>'Add Group',// '添加用户组',
		'remove_group_tips'=>'[Warning]:remove this group will delete all data of this group, the group\'s record is unrecoverable and related record will incomplete.Do you confirm to remove?',// '[严重警告]:如果删除这个用户组.则该组对应所有的用户信息都会丢失.并且数据不可恢复.你确认要删除这个用户组吗？',
		'group_edit_succ'=>'Edit Group Successful',// '用户组编辑成功',
		'edit_group'=>'Edit Group',// '编辑用户组',

		'remove_user_tips'=>'[Warning]:remove this user will delete all data of this user, the user\'s record is unrecoverable and related record will incomplete.Do you confirm to remove?',// '[严重警告]:删除用户将会造成这个用户相关的记录不完整.并且数据不可恢复.你确认要删除这个用户吗？',
	),

	// 我的管理
	'profile'=>array(
		'submit_succ'=>'Submit Successful',// '提交成功',
		'tpl_name_not_empty'=>'Template name is empty',// '模板名不能为空',
		'tpl_content_not_empty'=>'Template content is empty',// '模板内容不能为空',
		'add_tpl_succ'=>'Add template Successful',// '添加模板成功',
		'add_bug_tpl'=>'Add template',// '添加缺陷模板',
		'edit_tpl_succ'=>'Edit template Successful',// '编辑模板成功',
		'edit_bug_tpl'=>'Edit template',// '编辑缺陷模板',
	),

	// BUG管理
	'bug'=>array(
		'save_succ'=>'Save Successful',// '保存成功',
		'all_ver'=>'All Version',// '所有版本',
		'all_module'=>'All Module',// '所有模块',
		'simplify_condition'=>'Simple Condition',// '精简条件',
		'more_condition'=>'More Condition',// '更多条件',
		'export_excel'=>'Export Excel',// '导出为Excel',
		'select_user'=>'Choose User',// '选择用户',
		'select_bugtype'=>'Choose Bug type',// '选择缺陷类型',
		'select_frequency'=>'Choose Frequency',// '选择重现规律',
		'select_severity'=>'Choose Severity',// '选择严重程度',
		'bug_info_not_empty'=>'Bug content is empty',// '缺陷描述不能为空!',
		'subject_not_empty'=>'Subject is empty',// '标题不能为空!',
		'select_module'=>'Choose Module',// '选择模块',
		'select_ver'=>'Choose Version',// '选择版本',
		'select_project'=>'Choose project',// '请选择项目',
		'no_data_modify'=>'You haven\'t change data',// '没有数据更改',
		'select_to_user'=>'Please choose the user that you want to relocated of this bug',// '请选择缺陷要重新指定的用户',
		'submit_succ'=>'Submit Successful',// '提交处理成功!',
	),


);
