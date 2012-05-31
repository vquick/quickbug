<?php
/**
 * Setup 控制器中文语言
 */
return array(
	'system_check_fail'=>'System environment checked failure, please adjust your system and refresh this page to try again',// '系统环境有严重不符合.请调整后再刷新该页面'
	'step1_text'=>'QuickBug Install: Step 1, system check',// 'QuickBug 安装第一步:系统检查'
	'os'=>'Operate System',// '操作系统'
	'php_ver'=>'PHP Version',// 'PHP版本'
	'php_ver_error'=>'Error: PHP Version need >= 5.2.0, upgrage your php version first',// '错误:PHP的版本必需是 5.2.0 及以上版本.请先升级您的PHP'
	'php_mysql'=>'PHP Mysql extension',// 'PHP Mysql扩展'
	'php_mysql_error'=>'Error: PHP Mysql extension checked failure, please install PHP Mysql extension first',// '错误:没有安装PHP_MYSQL的扩展.请先为PHP加入该扩展的支持'
	'write_enable'=>'Write',// '可写'
	'write_disable'=>'Readonly',// '不可写'
	'path_write_disable'=>'Error: Destionation direcory readonly, you may use "chmod" command to change your directory privilege if your OS is linux',// '错误:该目录不可写.如果是Linux的系统则可以使用chmod命令来打开目录的写权限'
	'setup_notice'=>'Warning: QuickBug aleady exists, if you need to reinstall, please remove \\1 and refresh this page',// '警告:该系统已经安装过了.如果需要重新安装.请先把 \\1 删除后再刷新该页面.',
	'next'=>'Next',// '下一步'

	'step2_text'=>'QuickBug Install: Step 2, finish install',// 'QuickBug 安装第二步:完成安装'
	'install_db'=>'Install database',// '安装数据库'
	'mysql_host'=>'Mysql Host/IP',// 'MYSQL 主机名/IP'
	'mysql_port'=>'Mysql Port',// 'MYSQL 端口'
	'mysql_port_tips'=>'default port is 3306',// '如果没有特别设置的话.则默认是 3306'
	'mysql_user'=>'Mysql User',// 'MYSQL 用户名'
	'mysql_passwd'=>'Mysql Password',// 'MYSQL 密码'
	'mysql_db'=>'Database Name',// 'MYSQL 数据库'
	'mysql_db_tips'=>'database will be created if do not exists',// '如果该库不存在.则会自动尝试创建这个库.'
	'is_use_email'=>'Use Email Notice',// '是否使用邮件通知'
	'smtp_host'=>'SMTP HOST/IP',// 'SMTP  主机或IP地址'
	'smtp_host_tips'=>'EX: smtp.163.com',// '类似像 smtp.163.com 这种'
	'smtp_port'=>'SMTP Port',// 'SMTP 端口'
	'smtp_port_tips'=>'default port is 25',// '默认是 25'
	'protocol'=>'Communicate protocol',// '通信协议'
	'default'=>'Default',// '默认'
	'smtp_user'=>'SMTP User',// 'SMTP 用户名'
	'smtp_passwd'=>'SMTP Password',// 'SMTP 密码'
	'from_user'=>'SMTP sender email address',// 'SMTP 发送者邮箱地址'
	'from_user_tips'=>'This name will show as sender in email',// '即在邮件中显示是谁发的'
	'is_use_rtx'=>'Use RTX(If your company used RTX software)',// '是否结合RTX(公司必需有安装腾讯的RTX软件)'
	'rtx_host'=>'RTX HOST/IP',// 'RTX 主机或IP地址'
	'rtx_web_port'=>'RTX Web Gateway Port',// 'RTX WEB网关端口'
	'rtx_web_port_tips'=>'default port is 8012',// '默认是 8012'
	'finish'=>'finish install',// '完成安装'

	'step_ok'=>'QuickBug install successful',// 'QuickBug 安装成功'
	'file_modify_tips'=>'If you want to edit options manual or system environment changed, please edit below files directly',// '如果以后系统配置有变化或想手工修改更多参数的话.请直接修改以下文件'
	'db_config_file'=>'databse config files',// '数据库配置文件'
	'sys_config_file'=>'system config files',// '系统配置文件'
	'sys_auto_register_tips'=>'System was created account for you',// '系统已自动为您创建了二个帐号'
	'admin_user'=>'Admin',// '超级管理员'
	'pm_user'=>'PM',// '项目管理员'
	
	'test_user'=>'Test',
	'dev_user'=>'Dev',
		
	'new_use_sys'=>'Start to using QuickBug',// '现在开始使用 QuickBug 系统'

	'install_lock_contents'=>'This file exist means your system intall successful, pay attention to delete this file only if you want to reinstall system.',// '这个文件是安装标识.代表这个系统已经安装了.请不要随便删除该文件.如果要重新安装请先删除这个文件'
	'db_connect_fail'=>'Database connected failure, please checked host/port/user/password is correct',// '数据库连接失败.请确认 主机/端口/用户名/密码 是否都正确'
	'db_create_fail'=>'Database \\1 do not exist and created failure, please create it manaul',// '数据库 \\1 不存在.系统尝试自动创建不成功.请手工创建好该数据库.',
);
