<?php
/**
 * APP 启动后自动运行，在这里可以做一些自定义操作，如自动登录等。
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 * @version $Id: Bootstrap.php 905 2011-05-05 07:43:56Z yuanwei $
 */
class Bootstrap
{
	/**
	 * 框架只会自动调用该方法，用户自己的代码可加在里面.
	 *
	 */
	public function init(){
		// 使用 session
		@session_start();

		// 使用 Layout
		QP_Layout::start();

		// 引用公共函数库(Application/Library已经在搜索路径中了)
		require 'Function.inc.php';
		
		// 得到当前语言
		$lang = getLang();

		// 读出JS语言包设置到视图中
		$jsLang = json_encode(include APPLICATION_PATH.'/Lang/'.$lang.'/Javascript.php');
		QP_Layout::set('jsLang',$jsLang);
		
		// 用户访问权限判断
		$this->_checkpriv();
	}

	/**
	 * 检测用户的权限
	 *
	 */
	private function _checkpriv(){
		$controller = isset($_GET['c']) ? $_GET['c'] : QP_Controller::DEFAULT_CONTROLLER;
		$action = isset($_GET['a']) ? $_GET['a'] : QP_Controller::DEFAULT_ACTION;
		// 如果有权限则返回
		$ret = Priv::check(QP_Session_Session::get('login_userid'),$controller,$action);
		if($ret){
			return;
		}
		// 如果是异步访问则直接输出错误
		if(QP_Request::getInstance()->isAJAX()){
			die('Priv Access denied');
		}else{
			// 其它方式则直接提示后跳转
			QP_Sys::msgbox('Priv Access denied！',url('index','index'),10);
		}
	}
	
}