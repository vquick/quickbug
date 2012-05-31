<?php
/**
 * 权限控制器
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class PrivController extends BaseController
{
	private $userModel;
	private $privModel;
	
	/**
	 * 自动运行
	 */
	public function init(){
		parent::init();
		$this->privModel = new Model_Priv();
		$this->userModel = new Model_User();
	}

	/**
	 * 首页
	 */
	public function indexAction(){
		
		$get = $this->request->getGet();
		Priv::check($this->userid,$get['controller'],$get['action']);
		
		// 所有的用户组
		$groupList = $this->userModel->userGroupList(array('userid'=>$this->userid));
		// 得到组ID
		$groupid = $this->request->getGet('groupid',0);
		if($groupid < 1){
			$groupid = isset($groupList[0]['groupid']) ? $groupList[0]['groupid'] : 0;
		}
		// 得到群对应的所有权限
		$priv = $this->privModel->get($groupid);
		$allPriv = QP_Sys::config('privconfig.priv');
		
		$this->view->groups = $groupList;
		$this->view->privList = $allPriv;
		$this->view->priv = $priv;
	}
	
	/**
	 * 异步提交权限配置
	 *
	 */
	public function submitAction(){
		$post = $this->request->getPost();
		if(!isset($post['priv'])){
			$ret = false;
		}else{
			// 权限提交
			$ret = $this->privModel->submit($post['groupid'],$post['priv']);
		}
		$this->outputJson(0,'ok',$ret);
	}
}