<?php
/**
 * 项目管理控制器
 * 
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */
class ProjectController extends BaseController
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
		$this->model = new Model_Project();
		// XSS过滤
		if($this->request->isPost()){
			$_POST = array_map('xss', $_POST);
		}		
	}

	/**
	 * 首页
	 */
	public function indexAction(){
		// 项目列表
		$proList = $this->model->projectList($this->userid);
		// 得到项目详细信息
		$proid = $this->request->getGet('projectid',0);
		if($proid < 1){
			if(count($proList) > 0){
				$proid = $proList[0]['projectid'];
			}
		}
		// 验证合法性
		if(! $this->model->checkOpt($proid)){
			$this->msgbox(L('denied_options'));
		}
		$proInfo = $this->model->projectInfo($proid);
		$this->view->projectInfo = $proInfo;
		$this->view->projectList = $proList;
		// 文档列表
		$this->view->docsList = $this->model->docsList($proid);
		// 版本列表
		$this->view->versList = $this->model->versList($proid);
		// 模块列表
		$this->view->modulesList = $this->model->modulesList($proid);
	}
	
	/**
	 * 浏览项目详细信息,这个操作用在BUG列表时对项目的查看
	 *
	 */
	public function viewAction(){
		$projectid = $this->request->getGet('projectid',0);
		if($projectid < 1){
			$this->msgbox(L('project_not_exist'),url('bug','index'));
		}
		// 验证合法性
		if(! $this->model->checkOpt($projectid)){
			$this->msgbox(L('denied_options'));
		}
		// 详细信息
		$this->view->projectInfo = $this->model->projectInfo($projectid);
		// 文档列表
		$this->view->docsList = $this->model->docsList($projectid);
		// 版本列表
		$this->view->versList = $this->model->versList($projectid);
		// 模块列表
		$this->view->modulesList = $this->model->modulesList($projectid);
	}
	
	/**
	 * 异步添加项目
	 *
	 */
	public function addprojectAction(){
		$projectid = $this->model->addProject($_POST['projectname'],$_POST['projectinfo'],$this->userid);
		$data = array(
		'projectid'=>$projectid,
		'projectname'=>$_POST['projectname'],
		);
		$this->outputJson(0,'ok',$data);
	}
	
	/**
	 * 异步得到项目的详细信息
	 */
	public function projectinfoAction(){
		$projectid = $this->request->getGet('projectid');
		$info = $this->model->projectInfo($projectid);
		$this->outputJson(0,'ok',$info);
	}	
	
	/**
	 * 更新项目信息
	 *
	 */
	public function updateprojectAction(){
		$projectid = $this->request->getGet('projectid');
		$this->model->updateProject($_POST['projectname'],$_POST['projectinfo'],$projectid);
		$this->outputJson(0,'ok');
	}	
	
	/**
	 * 异步删除项目
	 */
	public function removeprojectAction(){
		$projectid = $this->request->getGet('projectid');
		$info = $this->model->removeProject($projectid);
		$this->outputJson(0,'ok',$info);
	}	
	
	/**
	 * 上传项目文档
	 *
	 */
	public function uploaddocAction(){
		$projectid = $this->request->getGet('projectid');
		$files = $this->request->files();
		// 上传文件
		$up = new QP_Upload_Upload();
		$up->set(array('type'=>'*','savePath'=>SITEWEB_PATH.'/files/projectdocs/'));
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
				'projectid'=>$_POST['projectid'],
				'docname'=>$docname,
				'docfile'=>$filename,
				'docsize'=>$files[$inputname]['size'],
				'dateline'=>time(),
				);
				$this->model->addDocs($sets);
			}
		}
		$this->jsCallBack('updocs_callback',0);
	}
	
	/**
	 * 更新项目文档字段内容
	 *
	 */
	public function updatedocAction(){
		$this->model->updateDocs(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0);
	}
	
	/**
	 * 删除项目文档
	 */
	public function removedocAction(){
		$id = $this->request->getGet('removeid');
		// 验证合法性
		if(! $this->model->checkOpt($id,2)){
			$this->outputJson(-1,L('denied_options'));
		}
		$this->model->removeDocs($id);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 添加项目版本
	 *
	 */
	public function addversAction(){
		$id = $this->model->addVers($_POST['vername'],$_POST['projectid']);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 更新项目版本字段内容
	 *
	 */
	public function updateverAction(){
		$this->model->updateVers(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0);
	}	
	
	/**
	 * 删除项目版本
	 */
	public function removeverAction(){
		$id = $this->request->getGet('removeid');
		// 验证合法性
		if(! $this->model->checkOpt($id,3)){
			$this->outputJson(-1,L('denied_options'));
		}
		$this->model->removeVers($id);
		$this->outputJson(0,'ok',$id);
	}	
	
	////////////////////////////////////////////////////
	/**
	 * 添加项目模块
	 *
	 */
	public function addmoduleAction(){
		$id = $this->model->addModules($_POST['modname'],$_POST['projectid']);
		$this->outputJson(0,'ok',$id);
	}
	
	/**
	 * 更新项目模块字段内容
	 *
	 */
	public function updatemoduleAction(){
		$this->model->updateModules(array($_POST['editfield']=>$_POST['editval']),$_POST['editid']);
		$this->outputJson(0);
	}	
	
	/**
	 * 删除项目模块
	 */
	public function removemoduleAction(){
		$id = $this->request->getGet('removeid');
		// 验证合法性
		if(! $this->model->checkOpt($id,4)){
			$this->outputJson(-1,L('denied_options'));
		}
		$this->model->removeModules($id);
		$this->outputJson(0,'ok',$id);
	}		
	
}