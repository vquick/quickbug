<?php
/**
 * 用户控制器
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class userController extends BaseController
{
	/**
	 * 模型
	 *
	 * @var unknown_type
	 */
	private $model;
	
	/**
	 * 自动运行
	 */
	public function init(){
		parent::init();
		$this->model = new Model_User();
		// XSS过滤
		if($this->request->isPost()){
			$_POST = array_map('xss', $_POST);
		}
	}

	/**
	 * 项目管理员管理用户
	 */
	public function indexAction(){
		// 如果是管理员登录则直接输到管理员用户管理
		if($this->priv == 3){
			$this->gotoUri('this','adminuser');
		}
		// 得到所有创建的用户
		$where = array();
		$where['createuid'] = $this->userid;
		// 查询
		$key = $this->request->getGet('key');
		if($key != ''){
			$where['key'] = $key;
		}
		$usertype = $this->request->getGet('usertype',0);
		if($usertype > 0){
			$where['usertype'] = $usertype;
		}
		$enable = $this->request->getGet('enable',-1);
		if($enable > -1){
			$where['enable'] = $enable;
		}
		$group = $this->request->getGet('group',0);
		if($group > 0){
			$where['groupid'] = $group;
		}
		// 列表
		$perpage = 20;
		$count = $this->model->userinfoList($where,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->pageHtml = $page['html'];
		$this->view->lists = $this->model->userinfoList($where, ($page['curpage']-1)*$perpage, $perpage);
		// 用户组列表
		$this->view->groups = $this->model->userGroupList(array('userid'=>$this->userid));
	}

	/**
	 * 用户组的管理
	 */
	public function groupAction(){
		// 得到所有创建的用户组
		$params = array('userid'=>$this->userid);
		// 查询
		$key = $this->request->getGet('key');
		if($key != ''){
			$params['key'] = $key;
		}
		$perpage = 10;
		$count = $this->model->userGroupList($params,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->pageHtml = $page['html'];
		$this->view->lists = $this->model->userGroupList($params,($page['curpage']-1)*$perpage, $perpage);
	}

	/**
	 * 添加用户组
	 */
	public function addusergroupAction(){
		$sets = $_POST;
		$groupid = $this->model->adduserGroup($sets);
		if(!$groupid){
			$this->outputJson(-1,L('group_exist'));
		}else{
			$this->outputJson(0,'ok',$groupid);
		}
	}

	/**
	 * 异步编译用户组的属性
	 */
	public function editusergroupAction(){
		$this->model->updateuserGroup(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0,'ok');
	}

	/**
	 * 删除用户组
	 */
	public function removeusergroupAction(){
		$groupid = $this->request->getGet('removeid');
		$this->model->removeuserGroup($groupid);
		$this->outputJson(0,'ok');
	}

	/**
	 * 异步编译用户的属性
	 */
	public function edituserAction(){
		$this->model->updateuser(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0,'ok');
	}

	/**
	 * 异步得到用户组的详细信息
	 */
	public function usergroupinfoAction(){
		$groupid = $this->request->getGet('groupid');
		$info = $this->model->usergroupInfo($groupid);
		$this->outputJson(0,'ok',$info);
	}

	/**
	 * 更新用户组信息
	 *
	 */
	public function updateusergroupAction(){
		$groupid = $this->request->getGet('groupid');
		$sets = $_POST;
		$this->model->updateuserGroup($sets,$groupid);
		$this->outputJson(0,'ok');
	}

	/**
	 * 更新用户信息
	 *
	 */
	public function updateuserAction(){
		$userid = $this->request->getGet('userid');
		$sets = $_POST;
		$groupid = 0;
		if(isset($sets['groupid'])){
			$groupid = $sets['groupid'];
			unset($sets['groupid']);
		}
		$this->model->updateuser($sets,$userid,$groupid);
		$this->outputJson(0,'ok');
	}

	/**
	 * 异步得到用户的详细信息
	 */
	public function userinfoAction(){
		$userid = $this->request->getGet('userid');
		$info = $this->model->userinfo($userid);
		$this->outputJson(0,'ok',$info);
	}

	/**
	 * 删除用户
	 */
	public function removeuserAction(){
		$userid = $this->request->getGet('removeid');
		// 验证合法性
		if(! $this->model->checkOpt($userid)){
			$this->outputJson(-1,L('denied_options'));
		}
		$this->model->removeuser($userid);
		$this->outputJson(0,'ok');
	}

	/**
	 * 添加用户
	 */
	public function adduserAction(){
		$groupid = 0;
		$sets = $_POST;
		if(isset($sets['groupid'])){
			$groupid = $sets['groupid'];
			unset($sets['groupid']);
		}
		$userid = $this->model->adduser($sets,$groupid);
		if(!$userid){
			$this->outputJson(-1, L('user_exist'));
		}else{
			$this->outputJson(0,'ok',$userid);
		}
	}

	/**
	 * 管理员管理用户
	 */
	public function adminuserAction(){
		$params = array();
		// 查询
		$key = $this->request->getGet('key');
		if($key != ''){
			$params['name'] = $key;
		}
		$usertype = $this->request->getGet('usertype',0);
		if($usertype > 0){
			$params['usertype'] = $usertype;
		}
		$priv = $this->request->getGet('priv',0);
		if($priv > 0){
			$params['priv'] = $priv;
		}
		$enable = $this->request->getGet('enable',-1);
		if($enable > -1){
			$params['enable'] = $enable;
		}
		// 分页
		$perpage = 20;
		$count = $this->model->adminUserList($params,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->pageHtml = $page['html'];
		$this->view->lists = $this->model->adminUserList($params, ($page['curpage']-1)*$perpage, $perpage);
	}

	/**
	 * 显示选择用户框
	 */
	public function selectUserAction(){
		// 不使用视图
		QP_Layout::stop();
		// 用户组
		$pcUid = $this->getPCUid();
		$this->view->groupList = $this->model->userGroupList(array('userid'=>$pcUid));
	}

	/**
	 * 得到当前的项目管理员UID
	 */
	private function getPCUid(){
		// 如果当前是项目管理员
		if($this->priv != 1){
			$pcuid = $this->userid;
		}else{
			// 普通用户要得到项目管理员
			$pcuid = $this->model->projectCreateUid($this->userid);
		}
		return $pcuid;
	}

	/**
	 * 根据用户组得到所有的用户
	 */
	public function getUsersAction(){
		$date = array();
		$groupid = $this->request->getGet('groupid');
		// 版本列表
		$date['userlist'] = $this->model->userinfoList(array('groupid'=>$groupid));
		$this->outputJson(0,'ok',$date);
	}

}