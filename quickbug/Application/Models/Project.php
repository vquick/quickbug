<?php
/**
 * 项目管理模型
 *
 */
class Model_Project extends Model_base{
	
	// 项目表
	private $projectTable ='';
	// 模块表
	private $moduleTable = '';
	// 版本表
	private $verTable = '';
	// 项目文档表
	private $docTable = '';
	// BUG表
	private $bugTable = '';
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->projectTable = tabname('project');
		$this->moduleTable = tabname('project_modules');
		$this->verTable = tabname('project_vers');
		$this->docTable = tabname('project_docs');
		$this->bugTable = tabname('bugs');
	}
	
	/**
	 * 得到项目列表
	 *
	 * @param unknown_type $userid 项目创建者,默认得到所有项目
	 * @param unknown_type $proid 查询的项目ID，多个之间用","连接 
	 */
	public function projectList($userid=0,$proid=0){
		$where = '1';
		if($userid > 0){
			$where .= ' and userid='.$userid;
		}
		if($proid != 0){
			$where .= " and projectid in ($proid)";
		}
		return $this->db->getAll($this->projectTable)->where($where)->result();
	}
	
	/**
	 * 添加项目
	 *
	 * @param unknown_type $name
	 * @param unknown_type $info
	 * @param unknown_type $userid
	 */
	public function addProject($name,$info,$userid){
		$sets = array(
		'projectname'=>$name,
		'info'=>$info,
		'userid'=>$userid,
		'dateline'=>time(),
		);
		$this->db->insert($this->projectTable,$sets);
		$projectid = $this->db->lastInsertId();
		// 添加一个项目后自动为它添加版本的模块
		$this->addVers('Ver 1.0',$projectid);
		$this->addModules(L('project.default_module'),$projectid);
		return $projectid;
	}	
	
	/**
	 * 编辑项目信息
	 *
	 * @param unknown_type $name
	 * @param unknown_type $info
	 * @param unknown_type $projectid
	 */
	public function updateProject($name,$info,$projectid){
		$sets = array(
		'projectname'=>$name,
		'info'=>$info,
		);		
		return $this->db->update($this->projectTable,$sets,array('projectid'=>$projectid));
	}
	
	/**
	 * 得到项目的详细信息
	 * @param unknown_type $projectid
	 */
	public function projectInfo($projectid){
		// 基本信息
		$info = $this->db->getRow($this->projectTable)->where(array('projectid'=>$projectid))->result();
		if(!$info){
			$info['projectname'] = '';
			$info['info'] = '';
			$info['projectid'] = 0;
		}
		// 统计
		$info['modulesnum'] = $this->db->count($this->moduleTable,array('projectid'=>$projectid));
		$info['versnum'] = $this->db->count($this->verTable,array('projectid'=>$projectid));
		$info['docsnum'] = $this->db->count($this->docTable,array('projectid'=>$projectid));
		$info['bugsnum'] = $this->db->count($this->bugTable,array('projectid'=>$projectid));
		return $info;
	}
	
	/**
	 * 删除项目
	 *
	 * @param unknown_type $projectid
	 */
	public function removeProject($projectid){
		// 是否创建者本人
		$proInfo = $this->db->getRow($this->projectTable)->where(array('projectid'=>$projectid))->result();
		if($proInfo['userid'] != $this->userid){
			return false;
		}
		// 删除项目所有的文档
		$dosList = $this->docsList($projectid);
		foreach ($dosList as $doc){
			$this->removeDocs($doc['pjdocid']);
		}
		// 删除项目
		$this->db->delete($this->projectTable,array('projectid'=>$projectid));
		// 删除版本
		$this->db->delete($this->verTable,array('projectid'=>$projectid));
		// 删除模块
		$this->db->delete($this->moduleTable,array('projectid'=>$projectid));
	}

	/**
	 * 得到项目的所有文档列表
	 *
	 * @param unknown_type $projectid
	 */
	public function docsList($projectid){
		return $this->db->getAll($this->docTable)->where(array('projectid'=>$projectid))->result();
	}
	
	/**
	 * 添加项目文档
	 *
	 * @param unknown_type $sets
	 */
	public function addDocs($sets){
		$this->db->insert($this->docTable,$sets);
		return $this->db->lastInsertId();
	}
	
	/**
	 * 得到项目文档信息
	 *
	 * @param unknown_type $docid
	 */
	public function docsInfo($docid){
		return $this->db->getRow($this->docTable)->where(array('pjdocid'=>$docid))->result();
	}
	
	/**
	 * 项目文档更新
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function updateDocs($sets,$id){
		return $this->db->update($this->docTable,$sets,array('pjdocid'=>$id));
	}

	/**
	 * 删除文档
	 *
	 * @param unknown_type $id
	 */
	public function removeDocs($id){
		// 删除文件
		$info = $this->db->getRow($this->docTable)->where(array('pjdocid'=>$id))->result();
		@unlink(SITEWEB_PATH.'/files/projectdocs/'.$info['docfile']);
		// 删除记录
		$this->db->delete($this->docTable,array('pjdocid'=>$id));
	}	
	
	/**
	 * 得到项目的所有版本列表
	 *
	 * @param unknown_type $projectid
	 * @param unknown_type $verid 查询的版本ID，多个之间用","连接 
	 */
	public function versList($projectid, $verid=0){
		$where = 'projectid='.$projectid;
		if($verid != 0){
			$where .= " and verid in ($verid)";
		}
		return $this->db->getAll($this->verTable)->where($where)->result();
	}	
	
	/**
	 * 添加项目版本
	 *
	 * @param unknown_type $vername
	 * @param unknown_type $projectid
	 */
	public function addVers($vername,$projectid){
		$sets = array(
		'projectid'=>$projectid,
		'vername'=>$vername,
		'dateline'=>time(),
		);
		$this->db->insert($this->verTable,$sets);
		return $this->db->lastInsertId();
	}

	/**
	 * 得到版本名
	 *
	 * @param unknown_type $verid
	 */
	public function versInfo($verid){
		return $this->db->getRow($this->verTable)->where(array('verid'=>$verid))->result();
	}
	
	/**
	 * 项目版本更新
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function updateVers($sets,$id){
		return $this->db->update($this->verTable,$sets,array('verid'=>$id));
	}	
	
	/**
	 * 删除版本
	 *
	 * @param unknown_type $id
	 */
	public function removeVers($id){
		// 删除记录
		$this->db->delete($this->verTable,array('verid'=>$id));
	}		
	
	/**
	 * 得到项目的所有模块列表
	 *
	 * @param unknown_type $projectid
	 * @param unknown_type $moduleid 查询的模块ID，多个之间用","连接 
	 */
	public function modulesList($projectid,$moduleid=0){
		$where = 'projectid='.$projectid;
		if($moduleid != 0){
			$where .= " and moduleid in ($moduleid)";
		}		
		return $this->db->getAll($this->moduleTable)->where($where)->result();
	}	
	
	/**
	 * 添加项目模块
	 *
	 * @param unknown_type $modname
	 * @param unknown_type $projectid
	 */
	public function addModules($modname,$projectid){
		$sets = array(
		'projectid'=>$projectid,
		'modulename'=>$modname,
		'dateline'=>time(),
		);
		$this->db->insert($this->moduleTable,$sets);
		return $this->db->lastInsertId();
	}

	/**
	 * 模块详细信息
	 *
	 * @param unknown_type $moduleid
	 */
	public function modulesInfo($moduleid){
		return $this->db->getRow($this->moduleTable)->where(array('moduleid'=>$moduleid))->result();
	}

		

	/**
	 * 项目模块更新
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function updateModules($sets,$id){
		return $this->db->update($this->moduleTable,$sets,array('moduleid'=>$id));
	}	
	
	/**
	 * 删除模块
	 *
	 * @param unknown_type $id
	 */
	public function removeModules($id){
		// 删除记录
		$this->db->delete($this->moduleTable,array('moduleid'=>$id));
	}
	
	/**
	 * 检查操作的合法性
	 *
	 * @param unknown_type $idtype 检测的ID类型 1:项目 2:项目文档 3:版本 4:模块
	 * @return boolean
	 */
	public function checkOpt($id,$idtype=1){
		// 根据不同的类型分别得到对应的项目ID
		switch($idtype){
			case 1:
				$projectid = $id;
			break;
			case 2:
				$info = $this->docsInfo($id);
				$projectid = $info['projectid'];
			break;
			case 3:
				$info = $this->versInfo($id);
				$projectid = $info['projectid'];
			break;
			case 4:
				$info = $this->modulesInfo($id);
				$projectid = $info['projectid'];
			break;
		}
		// 根据用户的身份得到项目管理UID
		if($this->userpriv == 1){
			$userModel = new Model_User();
			$pcUid = $userModel->projectCreateUid($this->userid);
		}else{
			$pcUid = $this->userid;
		}
		// 判断项目的创建者是不是本人
		$proInfo = $this->projectInfo($projectid);
		return $proInfo['userid'] == $pcUid;
	}
	
}
