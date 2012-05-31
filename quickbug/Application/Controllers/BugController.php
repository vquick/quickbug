<?php
/**
 * BUG 控制器
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class bugController extends BaseController
{
	private $bugModel;
	private $projectModel;
	private $userModel;
	private $profileModel;

	/**
	 * 自动运行
	 */
	public function init(){
		parent::init();
		$this->projectModel = new Model_Project();
		$this->userModel = new Model_User();
		$this->bugModel = new Model_Bug();
		$this->profileModel = new Model_Profile();

		// 当前登录UID,权限
		$this->view->currentUid = $this->userid;
		$this->view->currentPriv = $this->priv;
	}

	/**
	 * 导航菜单
	 */
	public function navAction(){
		// 如果是普通用户则要判断权限，其它用户则直接跳到列表页
		if($this->priv == 1){
			// 是否有列表权限
			if(privCheck('bug','index')){
				$this->gotoUri('bug','index');
			}elseif (privCheck('bug','add')){
				$this->gotoUri('bug','add');
			}elseif (privCheck('bug','search')){
				$this->gotoUri('bug','search');
			}elseif (privCheck('bug','report')){
				$this->gotoUri('bug','report');
			}
		}else{
			$this->gotoUri('bug','index');
		}
	}

	/**
	 * BUG列表
	 */
	public function indexAction(){
		// 得到当前用户组类型
		$groupType = 0;
		$userInfo = $this->userModel->userinfo($this->userid);
		if($userInfo['groupid']){
			$groupInfo = $this->userModel->usergroupInfo($userInfo['groupid']);
			$groupType = $groupInfo['grouptype'];
		}
		$this->view->groupType = $groupType;
		
		// 当前的项目管理员
		$pcuid = $this->getPCUid();
		// 项目列表
		$this->view->projectList = $this->projectModel->projectList($pcuid);

		// 得到项目对应的版本和模块
		$projectid = $this->request->getGet('projectid',0);
		$this->view->projectid = $projectid;
		$this->view->versList = $this->projectModel->versList($projectid);
		$this->view->modulesList = $this->projectModel->modulesList($projectid);
		// 项目详细信息
		$this->view->projectInfo = $this->projectModel->projectInfo($projectid);

		// BUG列表
		$where = $_GET;
		$perpage = 20;
		$count = $this->bugModel->bugList($where,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->bugCount = $count;
		$this->view->pageHtml = $page['html'];
		$this->view->bugList = $this->bugModel->bugList($where, ($page['curpage']-1)*$perpage, $perpage);

		// 新缺陷数
		$this->view->newbugCount = $this->bugModel->bugCount($where,1);
		// 延期/挂起的缺陷
		$this->view->sleepbugCount = $this->bugModel->bugCount($where,4);

		// 用户组列表
		$this->view->groupList = $this->userModel->userGroupList(array('userid'=>$pcuid));

		// 用户设置的默认查询条件
		$this->view->defaultBugSearch = $this->userModel->getSet('defaultBugSearch');

		// 导出Excel时可选择的BUG字段
		$this->view->bugFields = QP_Sys::config('bugconfig.listfields');
	}

	/**
	 * 导出为 EXCEL
	 *
	 */
	public function outexcelAction(){
		// 导出时比较耗系统资源
		@set_time_limit(0);
		@ini_set('memory_limit', '128M');
		// 解析XML模板
		$view = new QP_View();
		$view->setPath('Bug');
		// 得到BUG个数和列表
		$where = $_GET;
		$view->bugCount = $this->bugModel->bugList($where,null,null,'count');
		$view->bugList = $this->bugModel->bugList($where);
		// 得到要导出的字段
		$view->fields = isset($where['fields']) ? $where['fields'] : array();
		$view->allFields = QP_Sys::config('bugconfig.listfields');
		$content = $view->render('Outexcel.xml');
		// 下载
		$filename = 'buglist-'.date('Ymd').'.xls';
		$this->_downloadContent($content,$filename);
	}

	/**
	 * 高级搜索BUG
	 */
	public function searchAction(){
		// 分页显示
		$perpage = 20;
		$count = $this->bugModel->search($_GET,null,null,'count');
		$page = QP_sys::load('page')->set(array('sumcount'=>$count,'perpage'=>$perpage))->result();
		$this->view->bugCount = $count;
		$this->view->pageHtml = $page['html'];
		$this->view->bugList = $this->bugModel->search($_GET,($page['curpage']-1)*$perpage, $perpage);
	}

	/**
	 * 异步得到项目的版本的模块
	 *
	 */
	public function getsAction(){
		$opt = $this->request->getGet('opt');
		// 只取项目
		if($opt == 'project'){
			$pcuid = $this->getPCUid();
			$result = $this->projectModel->projectList($pcuid);
			$data['list'] = array();
			foreach ($result as $row){
				$data['list'][] = array(
				'key'=>$row['projectid'],
				'value'=>$row['projectname'],
				);
			}
		}else{
			// 取版本和模块
			$projectid = $this->request->getGet('projectid');
			$result = $this->projectModel->versList($projectid);
			$data['ver'] = array();
			foreach ($result as $row){
				$data['ver'][] = array(
				'key'=>$row['verid'],
				'value'=>$row['vername'],
				);
			}
			// 模块列表
			$result = $this->projectModel->modulesList($projectid);
			$data['module'] = array();
			foreach ($result as $row){
				$data['module'][] = array(
				'key'=>$row['moduleid'],
				'value'=>$row['modulename'],
				);
			}
		}
		$this->outputJson(0,'ok',$data);
	}

	/**
	 * 根据项目得到所有的版本和模块
	 */
	public function getVersModulesAction(){
		$date = array();
		$projectid = $this->request->getGet('projectid');
		// 版本列表
		$date['verlist'] = $this->projectModel->versList($projectid);
		// 模块列表
		$date['modulelist'] = $this->projectModel->modulesList($projectid);
		$this->outputJson(0,'ok',$date);
	}

	/**
	 * 浏览BUG
	 */
	public function viewAction(){
		$bugid = $this->request->getGet('bugid');
		// 验证合法性
		if(! $this->bugModel->checkOpt($bugid)){
			$this->msgbox(L('denied_view_bug')); // 非法查看缺陷！
		}
		$buginfo = $this->bugModel->bugInfo($bugid);
		// 判断当前用户和BUG的创建用户是否为同一个用户组的
		$cuInfo = User::getInfo($this->userid);
		$buInfo = User::getInfo($buginfo['createuid']);
		$this->view->isSomeGroup = $cuInfo['groupid']==$buInfo['groupid'];

		// 得到BUG详细信息
		$this->view->buginfo = $buginfo;
		// 设置 Title
		$this->view->title = sprintf("[#%s]%s",$buginfo['bugid'],$buginfo['subject']);
		// 得到变更历史
		$this->view->historyInfo = $this->bugModel->bugHistory($bugid);
		// 得到所有的附件
		$this->view->doclist = $this->bugModel->bugDocList($bugid);
		// 所有的评论
		$this->view->commentlist = $this->bugModel->commentList($bugid);
		// 所有的操作历史
		$this->view->operatelist = $this->bugModel->operateList($bugid);
	}

	/**
	 * 打印BUG
	 */
	public function printAction(){
		// 不使用Layout
		QP_Layout::stop();
		// 不自动解析视图
		$this->setViewAutoRender(false);
		// BUG信息
		$bugid = $this->request->getGet('bugid');
		echo $this->bugHtml($bugid);
	}

	/**
	 * 解析得到BUG的详细信息
	 */
	private function bugHtml($bugid){
		$view = new QP_View();
		$view->setPath('Bug');
		// 得到BUG详细信息
		$view->buginfo = $this->bugModel->bugInfo($bugid);
		// 得到所有的附件
		$view->doclist = $this->bugModel->bugDocList($bugid);
		// 所有的评论
		$view->commentlist = $this->bugModel->commentList($bugid);
		// 所有的操作历史
		$view->operatelist = $this->bugModel->operateList($bugid);
		return $view->render('Print.html');
	}

	/**
	 * 打印BUG
	 */
	public function exportWordAction(){
		$bugid = $this->request->getGet('bugid');
		$content = $this->bugHtml($bugid);
		// 下载
		$this->_downloadContent($content, 'bug-'.$bugid.'.doc');
	}

	/**
	 * 删除BUG
	 */
	public function removeAction(){
		$bugid = $this->request->getGet('bugid',0);
		if($bugid > 0){
			$this->bugModel->removeBug($bugid);
		}
		$this->location($_GET['bgurl']);
	}

	/**
	 * 处理状态流程
	 *
	 */
	public function processAction(){
		$post = $this->request->getPost();
		// 如果评论则提交评论
		$comment = trim($post['comment']);
		if($comment != ''){
			$sets = array(
			'bugid'=>$post['bugid'],
			'userid'=>$this->userid,
			'username'=>$this->username,
			'info'=>$post['comment'],
			'dateline'=>time(),
			);
			$id = $this->bugModel->addComment($sets);
		}
		// 如果修改了状态
		$ret = 1;
		if($post['status'] > 0){
			// 如果是重新指定用户
			if($post['status'] == 100){
				$touserid = User::getInfo($post['tobugUser'],'userid',1);
				$sets = array('touserid'=>$touserid);
				// "将缺陷重新指定给了 \\1"
				$this->bugModel->addOperate($post['bugid'],$this->userid, L('bug_reset_touser',array($post['tobugUser'])));
			}else{ 
				// 普通状态修改
				$sets = array('status'=>$post['status']);
			}
			$ret = $this->bugModel->updateBug($sets,$post['bugid']);
		}

		// 如果有邀请用户
		if($post['inviteUser'] != ''){
			// 操作选项 3:浏览BUG时的通知对方
			$opt = 4;
			$this->profileModel->addInvite($post['bugid'], $this->userid, $post['inviteUser'], $opt);
		}

		// 通知处理
		$this->processNotify($post);

		// 输出
		$this->outputJson(0,'ok',$ret);
	}
	// 通知处理
	private function processNotify($post){
		// 得到当前BUG信息
		$buginfo = $this->bugModel->bugInfo($post['bugid']);
		// 对谁进行消息通知
		$touid = $this->userid==$buginfo['createuid'] ? $buginfo['touserid'] : $buginfo['createuid'];

		// 如果设置了系统消息通知
		if($post['msgNotify']){
			// 操作选项 3:浏览BUG时的通知对方
			$opt = 3;
			$this->profileModel->addInvite($post['bugid'],$this->userid,$touid,$opt,1);
		}

		// 如果没有设置RTX或EMAIL通知提交就可以退出这个过程了
		if(!$post['rtxNotify'] && !$post['emailNotify']){
			return;
		}
		if(isset($post['rtxNotify']) || isset($post['emailNotify'])){
			$comment = trim($post['comment']);
			$bugStatus = QP_Sys::config('bugconfig.status');
			$url = $this->url('view','bugid='.$post['bugid']);
			// 如果有评论
			if($comment!='' && $post['status'] > 0){
				//$msg = sprintf("%s评论了“%s”这个缺陷:“%s”并将其状态修改为：%s",$this->username,$buginfo['subject'],$comment,$bugStatus[$post['status']]);
				$msg = L('commented_and_modifyed', array($this->username,$buginfo['subject'],$comment,$bugStatus[$post['status']]));
			}elseif ($comment!=''){
				//$msg = sprintf("%s评论了“%s”这个缺陷:“%s”",$this->username,$buginfo['subject'],$comment);
				$msg = L('commented_bug', array($this->username,$buginfo['subject'],$comment));
			}elseif ($post['status'] > 0){
				//$msg = sprintf("%s将“%s”这个缺陷的状态修改为：%s",$this->username,$buginfo['subject'],$bugStatus[$post['status']]);
				$msg = L('modifyed_bug', array($this->username,$buginfo['subject'],$bugStatus[$post['status']]));
			}
			// 如果有RTX通知
			if($post['rtxNotify'] && $post['rtxNotify']){
				User::notify($touid,$msg,$url,'rtx');
			}
			// 如果有邮件通知
			if($post['emailNotify'] && $post['emailNotify']){
				User::notify($touid,$msg,$url,'email');
			}
		}
	}

	/**
	 * 添加BUG
	 *
	 */
	public function addAction(){
		// 初始化数据
		$this->viewData();
		// 提交操作
		if($this->request->isPost()){
			// 添加BUG
			$sets = array(
			'projectid'=>$_POST['projectid'],
			'verid'=>$_POST['verid'],
			'moduleid'=>$_POST['moduleid'],
			'subject'=>$_POST['subject'],
			'info'=>$_POST['fckEditInfo'],
			'groupid'=>$_POST['usergroup'],
			'touserid'=>$_POST['userid'],
			'severity'=>$_POST['severity'],
			'frequency'=>$_POST['frequency'],
			'priority'=>$_POST['priority'],
			'bugtype'=>$_POST['bugtype'],
			'savetype'=>$_POST['savetype']>0 ? 1 : 0,
			);
			$bugid = $this->bugModel->addBug($sets,$this->userid);
			// 上传BUG文档
			$this->uploadDocs($bugid);
			// 发送通知
			$this->sendBugNotify($bugid);
			// 邀请
			if(isset($_POST['inviteBox'])){
				// 操作选项 1:创建BUG时的邀请Ta人
				$opt = 1;
				$this->profileModel->addInvite($bugid, $this->userid, $_POST['ivtuser'], $opt);
			}
			// 返回跳转
			$backArr = array(
			0=>url('this','index'),
			1=>url('this','view',array('bugid'=>$bugid)),
			2=>urldecode($_POST['fronturl'])
			);
			$this->location($backArr[$_POST['savetype']]);
			//$this->msgbox('添加Bug成功!',$backArr[$_POST['savetype']]);
		}

		// 用户设置的默认模板
		$this->view->defaultTplId = intval($this->userModel->getSet('defaultTplId'));
	}

	/**
	 * 添加BUG时通知发送
	 *
	 * @param unknown_type $bugid
	 * @param unknown_type $type 类型 0:添加BUG 1：修改BUG
	 */
	private function sendBugNotify($bugid,$type=0){
		$createUname = User::getInfo($this->userid,'username');
		if($type == 0){
			//$msg = sprintf('%s 给您分配了一个标题为“%s”的缺陷，请尽快处理一下！',$createUname,$_POST['subject']);
			$msg = L('notify_create_bug', array($createUname,$_POST['subject']));
		}else{
			// 处理人是否有改变了
			$buginfo = $this->bugModel->bugInfo($_GET['bugid']);
			// 没有修改处理人则提醒一下
			if($buginfo['touserid'] == $_POST['userid']){
				//$msg = sprintf('%s 修改了标题为“%s”的缺陷，请查看一下！',$createUname,$_POST['subject']);
				$msg = L('notify_modify_bug', array($createUname,$_POST['subject']));
			}else{
				//$msg = sprintf('%s 转交了一个标题为“%s”的缺陷，请尽快处理一下！',$createUname,$_POST['subject']);
				$msg = L('notify_reset_touser', array($createUname,$_POST['subject']));
			}
		}
		$url = $this->url('view','bugid='.$bugid);
		// 如果设置了邮件通知
		if(isset($_POST['notifyMsg']) && $_POST['notifyMsg']){
			// 操作选择: 0:创建BUG时的处理人通知 2:编辑BUG时的通知处理人
			$opt = $type==0 ? 0 : 2;
			$this->profileModel->addInvite($bugid, $this->userid, $_POST['userid'], $opt, 1);
		}
		// 如果设置了RTX通知
		if(isset($_POST['notifyRtx']) && $_POST['notifyRtx']){
			User::notify($_POST['userid'],$msg,$url,'rtx');
		}
		// 如果设置了邮件通知
		if(isset($_POST['notifyEmail']) && $_POST['notifyEmail']){
			User::notify($_POST['userid'],$msg,$url,'email');
		}
	}

	// 得到BUG地址
	private function url($action,$params=null){
		$domain = QP_Sys::config('sysconfig.domain');
		$path = url('bug', $action, $params);
		return $domain.$path;
	}

	/**
	 * 编辑BUG
	 *
	 */
	public function editAction(){
		// BUG详细信息
		$bugid = $this->request->getGet('bugid');
		// 验证合法性
		if(! $this->bugModel->checkOpt($bugid,3)){
			$this->msgbox(L('denied_edit_bug'));// 非法编辑缺陷！
		}
		// 初始化数据
		$this->viewData();
		$this->view->buginfo = $this->bugModel->bugInfo($bugid);
		// BUG的文档
		$this->view->bugdocList = $this->bugModel->bugDocList($bugid);

		// 提交操作
		if($this->request->isPost()){
			// 添加BUG
			$sets = array(
			'projectid'=>$_POST['projectid'],
			'verid'=>$_POST['verid'],
			'moduleid'=>$_POST['moduleid'],
			'subject'=>$_POST['subject'],
			'info'=>$_POST['fckEditInfo'],
			'touserid'=>$_POST['userid'],
			'severity'=>$_POST['severity'],
			'frequency'=>$_POST['frequency'],
			'priority'=>$_POST['priority'],
			'bugtype'=>$_POST['bugtype'],
			'savetype'=>$_POST['savetype']>0 ? 1 : 0,
			);
			$this->bugModel->updateBug($sets,$bugid);
			// 上传BUG文档
			$this->uploadDocs($bugid);
			// 发通知
			$this->sendBugNotify($bugid, 1);
			// 返回跳转
			$backArr = array(
			0=>url('this','index'),
			1=>url('this','view',array('bugid'=>$bugid)),
			2=>urldecode($_POST['fronturl'])
			);
			$this->location($backArr[$_POST['savetype']]);
			//$this->msgbox('添加Bug成功!',$backArr[$_POST['savetype']]);
		}
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
			$pcuid = $this->userModel->projectCreateUid($this->userid);
		}
		return $pcuid;
	}

	/**
	 * 添加和编辑BUG时的初始化数据
	 */
	private function viewData(){
		// 得到用户所属的项目管理员
		$pcUid = $this->getPCUid();
		// BUG模板列表
		$this->view->bugtplList = $this->profileModel->bugtplList($this->userid);
		// 项目列表
		$this->view->projectList = $this->projectModel->projectList($pcUid);
		// 用户组
		$this->view->groupList = $this->userModel->userGroupList(array('userid'=>$pcUid));
	}

	/**
	 * 上传BUG文档
	 */
	private function uploadDocs($bugid){
		$files = $this->request->files();
		// 上传文件
		$up = new QP_Upload_Upload();
		$up->set(array('type'=>'*','savePath'=>SITEWEB_PATH.'/files/bugdocs/'));
		// 一次最多上传 5 个
		for($i=1; $i<=5; ++$i){
			// 如果有文件上传
			$inputname = 'docfile_'.$i;
			if($up->setInputName($inputname)->hasUpload()){
				// 得到上传后的文件名
				$up->upload();
				$filename = $up->getUploadFile();
				// 如果没有设置文档名则用文件名
				$docname = $this->request->getPost('docname_'.$i,'');
				if($docname == ''){
					$docname = $files[$inputname]['name'];
				}
				// 插入表
				$sets = array(
				'docname'=>$docname,
				'docfile'=>$filename,
				'dateline'=>time(),
				);
				$this->bugModel->addBugDoc($sets,$bugid);
			}
		}
	}

	/**
	 * 得到BUG模板内容
	 */
	public function bugtplAction(){
		$id = $this->request->getGet('id',0);
		if($id == 0){
			$info['tplhtml'] = getDefaultBugTpl();
		}else{
			$info = $this->profileModel->bugtplInfo($id);
		}
		$this->outputJson(0,'ok',$info);
	}

	/**
	 * 更新BUG文档字段内容
	 *
	 */
	public function updatebugdocAction(){
		$this->bugModel->updateBugDoc(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0);
	}

	/**
	 * 删除BUG文档
	 */
	public function removebugdocAction(){
		$id = $this->request->getGet('removeid');
		$this->bugModel->removeBugDoc($id);
		$this->outputJson(0,'ok',$id);
	}

	/**
	 * BUG报表
	 *
	 */
	public function reportAction(){
		// 统计类型
		$countType = array(
			1=>array(
				'name'=>L('bugtype'),//'缺陷类型',
				'item'=>'bugtype'
			),
			2=>array(
				'name'=>L('to_user'),//'缺陷分配',
				'item'=>''
			),
			3=>array(
				'name'=>L('creater'),//'缺陷创建',
				'item'=>''
			),
			4=>array(
				'name'=>L('status'),//'缺陷状态',
				'item'=>'status'
			),
			5=>array(
				'name'=>L('severity'),//'缺陷严重程度',
				'item'=>'severity'
			),
			6=>array(
				'name'=>L('priority'),//'缺陷优先级',
				'item'=>'priority'
			),
		);
		// 统计
		$result = array();
		$param = $this->request->getGet();
		$result = $this->bugModel->reportCount($param);
		// 有统计操作
		if($result['count'] > 0){
			// 加工列表数据
			$list = array();
			foreach($result['list'] as $id=>$count){
				if($result['type'] == 'bugtype'){
					$title = getBugCfgName($countType[$param['counttype']]['item'], $id);
				}else{
					$title = rtxUser($id);
				}
				$list[] = array(
					'title'=>$title,
					'count'=>$count,
				);
			}

			// 统计结果
			$this->view->countName = $countType[$param['counttype']]['name'];
			$this->view->list = $list;

			// 保存数据给图形报表 flash 使用
			$projectInfo = $this->projectModel->projectInfo($param['projectid']);
			$subject = $projectInfo['projectname'];
			if(isset($param['verid']) && $param['verid']){
				$verInfo = $this->projectModel->versInfo($param['verid']);
				$subject .= '-'.$verInfo['vername'];
			}
			if(isset($param['moduleid']) && $param['moduleid']){
				$moduleInfo = $this->projectModel->modulesInfo($param['moduleid']);
				$subject .= '-'.$moduleInfo['vername'];
			}
			$data = array(
				// 标题
				'title'=>$countType[$param['counttype']]['name'],
				// 主题
				'subject'=>$subject,
				// 总数
				'count'=>$result['count'],
				// 统计结果
				'result'=>$list,
			);
			$this->_saveReport($data);
		}
		// 统计数目
		$this->view->reportCount = $result['count'];
	}
	// 将统计数据保存为文件
	private function _saveReport($data){
		$str = '<?php return '.var_export($data,true).';';
		$filename = APPLICATION_PATH.'/Data/Temp/flash-data-'.$this->userid.'.tmp';
		file_put_contents($filename, $str);
	}
	// 读取统计数据
	private function _readReport(){
		$filename = APPLICATION_PATH.'/Data/Temp/flash-data-'.$this->userid.'.tmp';
		return include $filename;
	}

	/**
	 * BUG报表所要的JSON数据
	 *
	 */
	public function flashAction(){
		$type = $this->request->getGet('type','bar');
		// 可用的颜色列表
		$colorArr = array('#9933CC','#FF0000','#FF00FF','#0000ff','#33CC00','#CC00FF','#FF6600');
		// 得到保存的数据
		$data = $this->_readReport();
		// 加工得到标签
		$labels = $spr = '';
		foreach($data['result'] as $row){
			$labels .= sprintf('%s"%s"',$spr,strip_tags($row['title']));
			$spr = ',';
		}
		// 加工得到要显示的值
		$values = $spr = $colors = '';
		foreach($data['result'] as $row){
			// 随机颜色
			$clr = $colorArr[mt_rand(0, count($colorArr)-1)];
			if($type == 'bar'){
				$format = '%s{"top":%d,"colour":"'.$clr.'", "tip": "%s<br>#val#"}';
			}else{
				// 圆型图中要指定颜色
				$colors .= $spr.'"'.$clr.'"';
				$format = '%s{"value":%d,"label":"%s"}';
			}
			$values .= sprintf($format, $spr,$row['count'],strip_tags($row['title']));
			$spr = ',';
		}

		// 得到JSON模板
		$file = SITEWEB_PATH.'/chart/'.$type.'.json';
		$json = file_get_contents($file);
		// 替换内容
		$search = array('<title>','<subject>','<count>','<labels>','<values>','<color>');
		$replace = array($data['title'],$data['subject'],$data['count'],$labels,$values,$colors);
		exit(str_replace($search,$replace,$json));
	}

	/**
	 * BUG 详细统计
	 *
	 */
	public function morecountAction(){
		// 统计
		$result = array();
		$param = $this->request->getGet();
		$pcUid = $this->getPCUid();
		$result = $this->bugModel->moreCount($pcUid,$param);
		$this->view->moreCountList = $result;
	}

	/**
	 * 将内容保存后下载
	 *
	 * @param string $content 内容
	 * @param string $filename 文件基本名
	 */
	private function _downloadContent($content,$filename){
		// 保存为临时文件
		$file = 'files/export/'.$filename;
		file_put_contents(SITEWEB_PATH.'/'.$file,$content);

		// 下载
		$sysCfg = QP_Sys::config('sysconfig');
		$url = $sysCfg['domain'].$sysCfg['path'].$file;
		header("Location: $url");
		exit;
	}
}