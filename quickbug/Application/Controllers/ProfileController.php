<?php
/**
 * 个人设置 控制器
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class profileController extends BaseController
{
	private $model;
	
	/**
	 * 自动运行
	 */
	public function init(){
		parent::init();
		$this->model = new Model_Profile();
	}

	/**
	 * 导航菜单
	 */
	public function navAction(){
		// 如果是普通用户则要判断权限，其它用户则直接跳到列表页
		if($this->priv == 1){
			// 是否有列表权限
			if(privCheck('profile','index')){
				$this->gotoUri('profile','index');
			}elseif (privCheck('profile','bugtpl')){
				$this->gotoUri('profile','bugtpl');
			}else{
				$this->gotoUri('profile','invite');
			}
		}else{
			$this->gotoUri('profile','index');
		}
	}	
	
	/**
	 * 我的资料设置
	 */
	public function indexAction(){
		$userModel = new Model_User();
		if($this->request->isPost()){
			// 更新资料
			$post = $this->request->getPost();
			$sets = array(
				'passwd'=>xss($post['password']),
				'truename'=>xss($post['truename']),
				'email'=>xss($post['email']),
			);
			$userModel->updateuser($sets,$this->userid);
			$userModel->saveSet('rtxAutoLogin',$post['rtxAutoLogin']);
			$this->outputJson(0);
		}
		// 得到用户信息
		$this->view->userInfo = $userModel->userinfo($this->userid);
		$this->view->rtxAutoLogin = $userModel->getSet('rtxAutoLogin');
	}
	
	/**
	 * BUG模板
	 */
	public function bugtplAction(){
		$this->view->bugtplList = $this->model->bugtplList($this->userid);
	}
	
	/**
	 * 异步得到输入编辑器HTML
	 *
	 */
	public function htmlAction(){
		// 不使用视图
		QP_Layout::stop();
		$id = $this->request->getGet('id',0);
		if($id > 0){
			// 得到要编辑的BUG模板内容	
			$info = $this->model->bugtplInfo($id);
			$tplname = $info['tplname'];	
			$defaultTpl = $info['tplhtml'];
		}else{
			// 默认的模板内容
			$tplname = '';
			$defaultTpl = getDefaultBugTpl();
		}
		$this->view->tplname = $tplname;
		$this->view->tplhtml = $defaultTpl;
	}
	
	/**
	 * 添加BUG模板
	 *
	 */
	public function addbugtplAction(){
		$id = $this->model->addBugtpl($this->userid,xss($_POST['tplname']),$_POST['tplhtml']);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 编辑BUG模板名
	 */
	public function updatebugtplAction(){
		$this->model->updateBugtpl(array($_POST['editfield']=>xss($_POST['editval'])),$_POST['editid']);
		$this->outputJson(0,'ok',$_POST['editid']);
	}
	
	/**
	 * 对话框式编辑BUG模板
	 */
	public function editbugtplAction(){
		$sets = array(
		'tplname'=>xss($_POST['tplname']),
		'tplhtml'=>$_POST['tplhtml'],
		);
		$this->model->updateBugtpl($sets,$_POST['editid']);
		$this->outputJson(0,'ok',$_POST['editid']);
	}
	
	/**
	 * 异步得到BUG模板内容
	 */
	public function bugtplinfoAction(){
		$id = $this->request->getGet('id');
		$info = $this->model->bugtplInfo($id);
		$this->outputJson(0,'ok',$info);
	}
	
	/**
	 * 删除BUG模板
	 */
	public function removebugtplAction(){
		$id = $this->request->getGet('removeid');
		$this->model->removeBugtpl($id);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 系统'心跳'由前端定时触发
	 */
	public function timerAction(){
		$ret = $this->model->getNewInvite($this->userid);
		$this->outputJson(0,'ok',$ret);
	}	
	
	/**
	 * 保存 BUG列表查询条件/默认模块 的默认值
	 * 
	 * type=[0:BUG列表查询条件 1:默认模块]&val=<查询条件或模块的ID>
	 */
	public function savedefaultAction(){
		$get = $this->request->getGet();
		$key = $get['type']==0 ? 'defaultBugSearch' : 'defaultTplId';
		$user = new Model_User();
		$ret = $user->saveSet($key,$get['val']);
		$this->outputJson(0,'ok',$ret);
	}
	
	/**
	 * 删除邀请通知
	 */
	public function removeinviteAction(){
		$id = $this->request->getGet('removeid');
		$this->model->removeInvite($id);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 查看邀请
	 */
	public function viewinviteAction(){
		$id = $this->request->getGet('id');
		$bugid = $this->request->getGet('bugid');
		$this->model->updateInviteStat($id);
		$this->gotoUri('bug','view',array('bugid'=>$bugid));
	}
	
	/**
	 * 邀请通知列表
	 */
	public function inviteAction(){
		// 如果有删除提交
		if($this->request->isPost() && isset($_POST['ids']) && $_POST['ids']){
			$this->model->removeInvite($_POST['ids']);
		}
		// 分页显示
		$perpage = 20;
		$count = $this->model->inviteList($this->userid,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->pageHtml = $page['html'];
		$this->view->inviteList = $this->model->inviteList($this->userid, ($page['curpage']-1)*$perpage, $perpage);
	}
	
	/**
	 * BUG列表字段设置
	 */
	public function buglistsetAction(){
		$userModel = new Model_User();
		//dump($userModel->getSet('bugListFields'));
		// 提交数据
		if($this->request->isPost()){
			$userModel->saveSet('bugListFields', isset($_POST['fields']) ? $_POST['fields'] : array());
			$this->outputJson(0);
		}
		$fields = QP_Sys::config('bugconfig.listfields');
		$this->view->fields = $fields;
		$this->view->noSet = ! fieldIsSet();
	}
}