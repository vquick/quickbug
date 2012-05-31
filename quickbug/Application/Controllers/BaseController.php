<?php
/**
 * 控制器基类
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
abstract class BaseController extends QP_Controller
{
	// 当前控制器
	protected $controller;
	
	// 当前方法
	protected $action;
	
	// 当前登录 userid
	protected $userid;
	// 当前登录 username
	protected $username;
	// 当前角色 1:普通用户 2:项目管理员 3:超级管理员
	protected $priv;
	
	/**
	 * 自动运行
	 */
	public function init(){
		// 得到用户信息
		$this->userid = intval(QP_Session_Session::get('login_userid'));
		$this->username = QP_Session_Session::get('login_username');
		$this->priv = QP_Session_Session::get('login_priv');
		
		// 自动得到当前的控制器和方法,适应各种URL模式 
		$get = $this->request->getGet();
		$parsm = $this->request->getParam();
		$this->controller = strtolower($get['controller'] ? $get['controller'] : $parsm['controller']);
		$this->action = strtolower($get['action'] ? $get['action'] : $parsm['action']);
		
		// 判断是否登录了,除了可以直接访问的行为
		$allowRes = QP_Sys::config('privconfig.allow');
		$res = strtolower($this->controller.'_'.$this->action);
		$resAll = strtolower($this->controller.'_*');
		if((!in_array($res,$allowRes)) && (!in_array($resAll,$allowRes))){
			// 没有登录后台则跳转到登录去
			if(! $this->userid){
				$url = $this->request->currentUrl();
				$this->gotoUri('index','login',array('bgurl'=>$url));
			}
		}
	}
	
	/**
	 * 输出 JSON 供 AJAX 调用
	 *
	 * @param int $result 状态码
	 * @param string $message 消息
	 * @param unknown_type $data 数据
	 */
	protected function outputJson($result,$message='ok',$data=array()){
		echo json_encode(array('result'=>$result,'message'=>$message,'data'=>$data));
		exit;
	}
	
	/**
	 * JS函数的回调，一般是用在 iframe 中提交所使用到
	 *
	 * @param unknown_type $jsname
	 * @param unknown_type $result
	 * @param unknown_type $message
	 * @param unknown_type $data
	 */
	protected function jsCallBack($jsname,$result=0,$message='ok',$data=array()){
		$json = json_encode(array('result'=>$result,'message'=>$message,'data'=>$data));
		$js = '<script type="text/javascript">';
		$js .= "parent.{$jsname}({$json});";
		$js .= '</script>';
		echo $js;
		exit;
	}	
}