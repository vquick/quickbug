<?php
/**
 * 系统控制器
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class SysController extends BaseController
{
	// 用户模型
	private $userModel;
	/**
	 * 自动运行
	 */
	public function init(){
		parent::init();
		$this->userModel = new Model_User();
	}

	/**
	 * 导航
	 *
	 */
	public function navAction(){
		// 超级管理员则只有数据库备份功能
		if($this->priv == 3){
			$this->gotoUri('sys','database');
		}else{
			$this->gotoUri('sys','autoreg');
		}
	}

	/**
	 * 自动注册设置
	 *
	 */
	public function autoregAction(){
		// 邮箱后缀
		$this->view->emailAddr = $this->userModel->getSet('emailAddr');
		// 得到域名地址
		$this->view->domain = QP_Sys::config('sysconfig.domain');
		// 得到所有的用户组
		$this->view->groupList = $this->userModel->userGroupList(array('userid'=>$this->userid));
	}
	
	/**
	 * 数组备份
	 *
	 */
	public function databaseAction(){
		// 备份文件目录
		$path = SITEWEB_PATH.'/files/dbfiles/';
		// 异步删除文件操作
		$filename = $this->request->getGet('removeid');
		if($filename){
			@unlink($path.urldecode($filename));
			$this->outputJson(0);
		}
		// 下载文件
		$downfile = $this->request->getGet('downfile');
		if($downfile){
			$downfile = $path.urldecode($downfile);
			QP_Http_Http::download($downfile);
		}
		
		// 遍历得到所有的备份文件
		$fileList = array();
		foreach (glob($path."*.sql") as $filename) {
			$fileList[] = array(
				'filename'=>$filename,
				'basename'=>basename($filename),
			);
		}
		// 视图
		$this->view->fileList = $fileList;
	}
	
	/**
	 * 异步备份数据库
	 */
	public function savedbAction(){
		set_time_limit(0);
		// 得到所有表
		$db = QP_Db::factory();
		$tabList = $db->fetchCol('show tables');
		// 保存为文件
		$bakfile = SITEWEB_PATH.'/files/dbfiles/'.date('YmdHis').'.sql';
		$time = date('Y-m-d H:i:s');
		$conter   = '-- QuickBug Database Backup'.PHP_EOL;
		$conter  .= '-- http://www.vquickbug.com' . PHP_EOL;
		$conter  .= "-- Date Time:$time" . PHP_EOL.PHP_EOL;
		$conter  .= 'SET NAMES UTF8;'.PHP_EOL.PHP_EOL;
		file_put_contents($bakfile,$conter);
		// 备份所有表的数据
		foreach ($tabList as $tab){
			$conter  = '-- --------------------------------------------------------'.PHP_EOL;
			$conter .= '--'.PHP_EOL;
			$conter .= "-- Table Struct `$tab`".PHP_EOL;
			$conter .= '--'.PHP_EOL.PHP_EOL.PHP_EOL;
			// 得到表的结构
			$ret = $db->fetchRow("show create table $tab");
			// 过滤  AUTO_INCREMENT=%d  
			$conter .= preg_replace('/AUTO_INCREMENT=\d+/i','',$ret['Create Table']).';'.PHP_EOL.PHP_EOL;
			file_put_contents($bakfile,$conter,FILE_APPEND);
			// 得到表的数据
			$conter  = '--'.PHP_EOL;
			$conter .= "-- Table Data `$tab`".PHP_EOL;
			$conter .= '--'.PHP_EOL.PHP_EOL;
			file_put_contents($bakfile,$conter,FILE_APPEND);
			// 将表的数据每100行导一次
			$num = 100;
			$tableCount = $db->count($tab);
			$step = ceil($tableCount / $num);
			for($i=0; $i<$step; ++$i){
				$start = $i * $num;
				$result = $db->select('*',$tab,null,null,"$start,$num");
				// 输出数据
				foreach($result as $row){
					$spr = '';
					$conter = "INSERT INTO `$tab` SET ";
					foreach($row as $key=>$val){
						$conter .= $spr."`{$key}`=".$db->escape($val);
						$spr = ',';
					}
					$conter .= ';'.PHP_EOL;
					file_put_contents($bakfile,$conter,FILE_APPEND);
				}
			}
			$conter = PHP_EOL.PHP_EOL;
			file_put_contents($bakfile,$conter,FILE_APPEND);
		}
		$this->outputJson(0);
	}
	
	
	/**
	 * 异步保存邮箱后缀
	 */
	public function saveemailAction(){
		$email = $this->request->getGet('email');
		$this->userModel->saveSet('emailAddr',$email);
		$this->outputJson(0);
	}

	/**
	 * 自动RTX注册
	 * 注意:这个功能只支持用IE浏览器打开这个地址
	 */
	public function rtxRegAction(){
		// 提交注册
		if($this->request->isPost()){
			$groupid = intval($_POST['groupid']);
			$username = $_POST['username'];
			$truename = $_POST['truename'] ? $_POST['truename'] : $username;
			// 如果异常
			if($username == '' || $groupid<1){
				$this->outputJson(-1,'注册异常:您的RTX取不到用户信息，可以尝试重新安装以修复此问题！');
			}
			// 根据组ID得到组的邮箱的后缀
			$groupInfo = $this->userModel->usergroupInfo($groupid);
			$emailAddr = $this->userModel->getSet('emailAddr', $groupInfo['createuid']);
			if($emailAddr){
				$email = $username.$emailAddr;
			}else{
				$email = '';
			}
			// 注册用户
			$sets = array(
				'username'=>$username,
				'truename'=>$truename,
				'passwd'=>$username,
				'email'=>$email,
				'usertype'=>2,
				'priv'=>1,
			);
			$userid = $this->userModel->adduser($sets,$groupid);
			if($userid){
				// 设置为自动登录
				$this->userModel->saveSet('rtxAutoLogin',1,$userid);
			}
			// 输出
			$this->outputJson(0);
		}
	}
	
	/**
	 * 群发RTX/邮件通知
	 *
	 */
	public function notifyAction(){
		// 发通知提交
		if($this->request->isPost()){
			switch($_POST['toUser']){
				// 所有人
				case 0:
				$userList = $this->userModel->userinfoList(array('createuid'=>$this->userid));
				// 得到 UID
				$userids = $spr = '';
				foreach($userList as $row){
					$userids .= $spr.$row['userid'];
					$spr = ',';
				}
				break;
				
				// 用户组
				case 1:
				$userList = $this->userModel->userinfoList(array('groupid'=>$_POST['userGroup']));
				// 得到 UID
				$userids = $spr = '';
				foreach($userList as $row){
					$userids .= $spr.$row['userid'];
					$spr = ',';
				}
				break;
				
				// 指定用户
				case 2:
				$userids = $_POST['sendUsers'];
				break;
			}
			// 发通知
			if(isset($_POST['notifyEmail']) && $_POST['notifyEmail']){
				User::notify($userids,$_POST['notMsg'],$_POST['notUrl'],'mail',$_POST['notTitle']);
			}
			if(isset($_POST['notifyRtx']) && $_POST['notifyRtx']){
				User::notify($userids,$_POST['notMsg'],$_POST['notUrl'],'rtx',$_POST['notTitle']);
			}
			$this->outputJson(0);
		}
		// 得到所有的用户组
		$this->view->groupList = $this->userModel->userGroupList(array('userid'=>$this->userid));
		// 域名配置
		$conf = QP_Sys::config('sysconfig');
		$this->view->domain = $conf['domain'].$conf['path'];
	}
}