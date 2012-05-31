<?php
/**
 * 首页控制器
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class indexController extends BaseController
{
	/**
	 * 自动运行
	 */
	public function init(){
		// 检测系统是否正常安装了
		if(!file_exists(APPLICATION_PATH.'/Data/Logs/install.lock')){
			$this->gotoUri('setup');
		}
		// 初始化基类控制器
		parent::init();
	}

	/**
	 * 首页
	 */
	public function indexAction(){
		// 如果登录了
		if($this->userid > 0){
			// 根据不同的用户角色跳转到不同的页面
			// 1:普通用户 2:项目管理员 3:超级管理员
			switch (QP_Session_Session::get('login_priv')){
				case 3:
					$this->gotoUri('user','adminuser');
					break;
				case 2:
					$this->gotoUri('user','index');
					break;
				default:
					$this->gotoUri('bug','index');
					break;
			}
		}else{
			// 跳到登录
			$this->gotoUri('index','login');		
		}
	}
	
	/**
	 * 检测用户是否为 RTX 用户,并且设置了自动登录
	 *
	 */
	public function checkuserAction(){
		$username = $this->request->getGet('username');
		$userModel = new Model_User();
		$userInfo = $userModel->userinfo($username,1);
		$ret = -1; // 0:成功 其它值失败
		// 如果是RTX用户
		if($userInfo && $userInfo['usertype']==2){
			// 是否设置了自动登录
			$isAutoLogin = $userModel->getSet('rtxAutoLogin',$userInfo['userid']);
			if($isAutoLogin){
				$ret = $userModel->login($userInfo['username'],$userInfo['passwd'],1);
			}
		}
		$this->outputJson($ret);
	}
	
	
	/**
	 * 登录
	 *
	 */
	public function loginAction(){
		// 提交
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$model = new Model_User();
			$result = $model->login($post['username'],$post['passwd']);
			// 登录成功跳到首页
			if($result == 0){
				// 如果有跳转地址
				$bgurl = $this->request->getGet('bgurl');
				if($bgurl){
					$this->location($bgurl);
				}else{
					$this->gotoUri('index','index');
				}
			}else{
				$msgArr = array(
				'-1'=>L('account_error'),
				'-2'=>L('account_disabled'),
				);
				$this->msgbox($msgArr[$result]);
			}
		}
	}
	
	/**
	 * 退出
	 */
	public function logoutAction(){
		$model = new Model_User();
		$model->logout();
		$this->gotoUri('index','login');
	}
	
	/**
	 * 关于系统
	 */
	public function aboutAction(){
		// 不使用视图
		QP_Layout::stop();
	}
	
	/**
	 * 语言切换
	 */
	public function langAction(){
		$lang = $this->request->getGet('lang');
		// 记录 session
		QP_Session_Session::set('lang', $lang);
		// 如果登录了则记录用户的语言选择
		if($this->userid > 0){
			$userModel = new Model_User();
			$userModel->saveSet('lang', $lang);
		}
		$url = $this->request->getGet('bgurl');
		($url=='') && ($url = QP_Sys::url('index'));
		$this->location($url);
	}
	
}