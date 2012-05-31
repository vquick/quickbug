<?php
/**
 * 系统安装 控制器
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class setupController extends QP_Controller
{
	// 文件锁
	private $_lockFile;

	// 该系统是否已经安装过了
	private $_isLocked = false;

	/**
	 * 自动运行
	 */
	public function init(){
		restore_error_handler();
		// 文件锁
		$this->_lockFile = APPLICATION_PATH.'/Data/Logs/install.lock';
		$this->_isLocked = file_exists($this->_lockFile);
		// 使用视图
		QP_Layout::name('Setup.html');
	}

	/**
	 * 语言切换
	 */
	public function langAction(){
		$lang = $this->request->getGet('lang');
		// 记录 session
		QP_Session_Session::set('lang', $lang);
		// 跳转到上一个页面
		$url = $this->request->getGet('bgurl');
		($url=='') && ($url = QP_Sys::url('setup'));
		$this->location($url);
	}

	/**
	 * 安装第1步：检测环境
	 *
	 */
	public function indexAction(){
		// 操作系统
		$data['os']=array(
			'result'=>PHP_OS,
			'proposal'=>true,
		);
		$next = true;
		// PHP版本
		$verOK = version_compare(PHP_VERSION, "5.2.0", '>=');
		$data['php']=array(
			'result'=>PHP_VERSION,
			'proposal'=>$verOK,
		);
		if(! $verOK){
			$next = false;
		}
		// mysql 扩展
		$mysqlOK = function_exists('mysql_connect');
		$data['mysql']=array(
			'result'=>$mysqlOK ? 'PHP_MYSQL' : '',
			'proposal'=>$mysqlOK,
		);
		if(! $mysqlOK){
			$next = false;
		}

		// 目录是否可写
		$data['paths'] = array();
		$pathArr = array(
		SITEWEB_PATH.'/files/projectdocs',
		SITEWEB_PATH.'/files/bugdocs',
		SITEWEB_PATH.'/files/dbfiles',
		SITEWEB_PATH.'/files/export',
		APPLICATION_PATH.'/Data/Cache',
		APPLICATION_PATH.'/Data/Logs',
		APPLICATION_PATH.'/Data/Temp',
		APPLICATION_PATH.'/Configs/Database.php',
		APPLICATION_PATH.'/Configs/Sysconfig.php',
		);
		foreach ($pathArr as $path){
			$iswrite = is_writable($path);
			$row = array(
			'title'=>realpath($path),
			'result'=>$iswrite,
			);
			if(! $iswrite){
				$next = false;
			}
			$data['paths'][] = $row;
		}
		// 是否可以下一步
		$data['next'] = $next;
		// 是否锁定了
		$data['isLocked'] = $this->_isLocked;
		$data['lockFile'] = $this->_lockFile;
		// 视图变量
		$this->view->assign($data);
	}

	/**
	 * 安装第2步：输入参数
	 *
	 */
	public function inputAction(){
		// 是否锁定了
		$data['isLocked'] = $this->_isLocked;
		$data['lockFile'] = $this->_lockFile;
		// 视图变量
		$this->view->assign($data);
	}

	/**
	 * 安装第3步：安装库和修改配置文件
	 *
	 */
	public function installAction(){
		$this->_installDb();
		$this->_installConfig();
		$this->_lockInstall();
		$this->_initUser();
		$this->_initTestData();
		$this->_out(0);
	}

	/**
	 *  安装第4步：完成
	 *
	 */
	public function doneAction(){
		$domain = 'http://'.$_SERVER["HTTP_HOST"];
		$path = str_replace('index.php', '', $_SERVER["SCRIPT_NAME"]);
		$data['url'] = $domain.$path;
		// 视图变量
		$this->view->assign($data);
	}

	// 安装完后锁定标识
	private function _lockInstall(){
		$data = L('install_lock_contents');
		file_put_contents($this->_lockFile, $data);
	}

	// 安装库
	private function _installDb(){
		// 测试 mysql 连接
		$link = @mysql_connect($_POST['mysqlhost'].':'.$_POST['mysqlport'], $_POST['mysqluser'], $_POST['mysqlpass']);
		if(!$link){
			$this->_out(-1,L('db_connect_fail'));
		}
		// 测试数据库名
		$bool = @mysql_select_db($_POST['mysqldb'], $link);
		if(! $bool){
			// 尝试着创建库
			$sql = "CREATE DATABASE `{$_POST['mysqldb']}` DEFAULT CHARACTER SET utf8";
			if(! @mysql_query($sql,$link)){
				$this->_out(-2, L('db_create_fail', array($_POST['mysqldb'])));
			}
		}
		// 导入表结构和初始化
		mysql_select_db($_POST['mysqldb'], $link);
		mysql_query('set names utf8',$link);
		$fileData = file_get_contents(APPLICATION_PATH.'/Data/database.sql');
		$sqlArr = explode(';', $fileData);
		foreach ($sqlArr as $sql){
			$sql = trim($sql);
			if($sql){
				mysql_query($sql,$link);
			}
		}
	}

	// 安装配置文件
	private function _installConfig(){
		// 更新DB配置文件
		$dbCfgFile = realpath(APPLICATION_PATH.'/Configs/Database.php');
		$search = array(
		"/'host'\s*=>\s*'(.*)'/",
		"/'port'\s*=>\s*(\d+)/",
		"/'username'\s*=>\s*'(.*)'/",
		"/'password'\s*=>\s*'(.*)'/",
		"/'dbname'\s*=>\s*'(.*)'/",
		);
		$replace = array(
		"'host'=>'{$_POST['mysqlhost']}'",
		"'port'=>{$_POST['mysqlport']}",
		"'username'=>'{$_POST['mysqluser']}'",
		"'password'=>'{$_POST['mysqlpass']}'",
		"'dbname'=>'{$_POST['mysqldb']}'",
		);
		$this->_replaceFile($dbCfgFile,$search,$replace);

		// 更新SYS配置文件
		$sysCfgFile = realpath(APPLICATION_PATH.'/Configs/Sysconfig.php');
		$search = array(
		"/'domain'\s*=>\s*'(.*)'/",
		"/'path'\s*=>\s*'(.*)'/",
		);
		$domain = 'http://'.$_SERVER["HTTP_HOST"];
		$path = str_replace('index.php', '', $_SERVER["SCRIPT_NAME"]);
		$replace = array(
		"'domain'=>'{$domain}'",
		"'path'=>'{$path}'",
		);
		$this->_replaceFile($sysCfgFile,$search,$replace);

		// 如果有设置email
		if(isset($_POST['chkEmail']) && $_POST['chkEmail']){
			// 先取所 email 现有的配置
			$cfgData = file_get_contents($sysCfgFile);
			preg_match("/'mail'.*\([^\)]+\)/",$cfgData,$matchs);
			$mailSearch = $matchs[0];
			// 替换内容
			$search = array(
			"/'enable'\s*=>\s*.*,/",
			"/'host'\s*=>\s*'(.*)'/",
			"/'port'\s*=>\s*(\d+)/",
			"/'secure'\s*=>\s*'(.*)'/",
			"/'username'\s*=>\s*'(.*)'/",
			"/'password'\s*=>\s*'(.*)'/",
			"/'from'\s*=>\s*'(.*)'/",
			);
			$replace = array(
			"'enable'=>true,",
			"'host'=>'{$_POST['emailhost']}'",
			"'port'=>{$_POST['emailport']}",
			"'secure'=>'{$_POST['emailsecure']}'",
			"'username'=>'{$_POST['emailuser']}'",
			"'password'=>'{$_POST['emailpass']}'",
			"'from'=>'{$_POST['emailfrom']}'",
			);
			$mailReplace = preg_replace($search, $replace, $mailSearch);
			$cfgData = str_replace($mailSearch,$mailReplace,$cfgData);
			file_put_contents($sysCfgFile,$cfgData);
		}
		// 如果有设置rtx
		if(isset($_POST['chkRtx']) && $_POST['chkRtx']){
			// 先取所 RTX 现有的配置
			$cfgData = file_get_contents($sysCfgFile);
			preg_match("/'rtx'.*\([^\)]+\)/",$cfgData,$matchs);
			$rtxSearch = $matchs[0];
			// 替换内容
			$search = array(
			"/'enable'\s*=>\s*.*,/",
			"/'host'\s*=>\s*'(.*)'/",
			"/'port'\s*=>\s*(\d+)/",
			);
			$replace = array(
			"'enable'=>true,",
			"'host'=>'{$_POST['rtxhost']}'",
			"'port'=>{$_POST['rtxport']}",
			);
			$rtxReplace = preg_replace($search, $replace, $rtxSearch);
			$cfgData = str_replace($rtxSearch,$rtxReplace,$cfgData);
			file_put_contents($sysCfgFile,$cfgData);
		}
	}

	// 替换文件中的内容,即时存盘
	private function _replaceFile($cfgFile,$search=array(),$replace=array()){
		$fileData = file_get_contents($cfgFile);
		$fileData = preg_replace($search, $replace, $fileData);
		file_put_contents($cfgFile,$fileData);
	}

	// 初始化用户
	private function _initUser(){
		// 添加超级管理员帐号
		$user = new Model_User();
		$sets = array(
		'username'=>'admin',
		'truename'=>L('user.super_admin'),
		'passwd'=>'123456',
		'priv'=>3,
		);
		$user->adduser($sets);

		// 添加一个项目管理员
		$sets = array(
		'username'=>'pm',
		'truename'=>L('user.project_admin'),
		'passwd'=>'123456',
		'priv'=>2,
		);
		$user->adduser($sets);
	}

	// 自动安装测试数据
	private function _initTestData(){
		$db = QP_Db::factory();
		$time = time();
		// 安装两个用户，一个测试用户，一个开发用户
		$sql = "
INSERT INTO `quickbug_user` (`userid`, `username`, `truename`, `passwd`, `email`, `usertype`, `priv`, `enable`, `dateline`, `ext`) VALUES
(3, 'test', 'Test', 'e10adc3949ba59abbe56e057f20f883e', 'test@example.com', 1, 1, 1, $time, NULL),
(4, 'dev', 'Dev', 'e10adc3949ba59abbe56e057f20f883e', 'dev@example.com', 1, 1, 1, $time, NULL)
";
		$db->execute($sql);
		// 组与用户关系
		$sql = "
INSERT INTO `quickbug_groupuser` (`guid`, `groupid`, `userid`) VALUES
(1, 2, 3),
(2, 1, 4)
		";
		$db->execute($sql);
		// 添加一个测试BUG
		$condition = L('bug.condition');
		$step = L('bug.step');
		$case = L('bug.case');
		$interfix_log = L('bug.interfix_log');
		$antic_result = L('bug.antic_result');
		$sql = "
INSERT INTO `quickbug_bugs` (`bugid`, `projectid`, `verid`, `moduleid`, `subject`, `info`, `groupid`, `createuid`, `touserid`, `severity`, `frequency`, `priority`, `bugtype`, `status`, `savetype`, `dateline`, `lastuptime`) VALUES
(1, 1, 1, 1, 'Test Bug', '<p><strong>[{$condition}]</strong> <br />\r\n1: <br />\r\n2: <br />\r\n<br />\r\n<strong>[{$step}]</strong> <br />\r\n1: <br />\r\n2: <br />\r\n<br />\r\n<strong>[{$case}]</strong> <br />\r\n<br />\r\n<br />\r\n<strong>[{$interfix_log}]</strong> <br />\r\n<br />\r\n<br />\r\n<strong>[{$antic_result}]</strong></p>', 1, 3, 4, 3, 1, 3, 1, 1, 1, $time, $time)
		";
		$db->execute($sql);
		// BUG的文档
		$sql = "
INSERT INTO `quickbug_bug_docs` (`bugdocid`, `bugid`, `docname`, `docfile`, `dateline`) VALUES
(1, 1, 'Attachment1', 'attachment1.jpg', $time)
		";
		// BUG邀请处理记录
		$db->execute($sql);
		$sql = "
INSERT INTO `quickbug_bug_invite` (`inviteid`, `bugid`, `userid`, `ivtuserid`, `opt`, `isread`, `dateline`) VALUES
(1, 1, 3, 4, 0, 0, $time)
		";
		$db->execute($sql);
		// BUG操作历史记录
		$created_bug = L('bug.created_bug');
		$sql = "
INSERT INTO `quickbug_operate_history` (`id`, `bugid`, `userid`, `text`, `dateline`) VALUES
(1, 1, 3, '{$created_bug}', $time)
		";
		$db->execute($sql);
	}

	// 输出 json
	private function _out($result=0,$msg=''){
		$json = json_encode(array('result'=>$result,'message'=>$msg));
		exit($json);
	}
}