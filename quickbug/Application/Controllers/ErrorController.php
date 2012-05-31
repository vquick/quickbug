<?php
/**
 * 系统 错误/异常 处理控制器
 * 
 * [注意]
 * 
 * 1:当框架生产严重错误或异常时将会自动调用该控制器,开发者可适当的修改以适合自己项目需求
 * 2:不可删除该控制器，否则会显示所有的异常错误
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class ErrorController extends QP_Controller
{

	/**
	 * 框架自动执行该方法
	 *
	 * @param object $exceptionObj 异常消息
	 */
	public function errorAction($exceptionObj)
	{
		// 根据不同的异常类型分别处理
		switch($exceptionObj->getCode()){
			
			// 找不到控制器
			case QP_Exception::EXCEPTION_NO_CONTROLLER:
				// 这里一般可以跳到自定义的 404 页面
				$this->msgbox('Page Not Found！');
				break;
				
			// 找不到动作
			case QP_Exception::EXCEPTION_NO_ACTION:
				// 这里一般可以跳到当前控制器的默认动作
				$this->gotoUri($this->request->getParam('controller'), QP_Controller::DEFAULT_ACTION);
				break;
				
			// 其它情况都默认显示出来
			default:
				$this->view->exception = $exceptionObj;
				break;
		}
	}
}