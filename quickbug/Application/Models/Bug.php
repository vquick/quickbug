<?php
/**
 * BUG模型
 *
 */
class Model_Bug extends Model_base{
	// BUG表名
	private $bugTable;
	// BUG文档表名
	private $bugdocTable;
	// BUG变更历史表
	private $bugHistoryTable;
	// BUG评论处理意见表
	private $bugCommentTable;
	// BUG操作历史表
	private $operateHistoryTable;
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->bugTable = tabname('bugs');
		$this->bugdocTable = tabname('bug_docs');
		$this->bugHistoryTable = tabname('bug_history');
		$this->bugCommentTable = tabname('bug_comment');
		$this->operateHistoryTable = tabname('operate_history');
	}

	/**
	 * BUG数量统计
	 *
	 * @param unknown_type $where
	 * @param unknown_type $status
	 * @return int
	 */
	public function bugCount($where,$status){
		$_where = 'status='.$status;
		// 设置了项目ID
		if(isset($where['projectid']) && $where['projectid']){
			$_where .= " and projectid=".intval($where['projectid']);
		}else{
			// 否则显示所在用户组中所有的项目
			$projectArr = $this->_getProjectId();
			if($projectArr){
				$ids = implode(',',$projectArr);
				$_where .= " and projectid in ($ids)";
			}
		}
		// 版本ID
		if(isset($where['verid']) && $where['verid']){
			$_where .= " and verid=".$where['verid'];
		}
		// 模块ID
		if(isset($where['moduleid']) && $where['moduleid']){
			$_where .= " and moduleid=".$where['moduleid'];
		}
		return $this->db->count($this->bugTable,$_where);
	}
	
	/**
	 * BUG列表
	 *
	 * @param array $where
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 */
	public function bugList($where,$start=0,$length=0,$type='list'){
		$_where = '1';
		// 设置了项目ID
		if(isset($where['projectid']) && $where['projectid']){
			$_where .= " and b.projectid=".intval($where['projectid']);
		}else{
			// 否则显示所在用户组中所有的项目
			$projectArr = $this->_getProjectId();
			if($projectArr){
				$ids = implode(',',$projectArr);
				$_where .= " and b.projectid in ($ids)";
			}else{
				$_where .= " and b.projectid=0";	
			}
		}
		// 版本ID
		if(isset($where['verid']) && $where['verid']){
			$_where .= " and b.verid=".$where['verid'];
		}
		// 模块ID
		if(isset($where['moduleid']) && $where['moduleid']){
			$_where .= " and b.moduleid=".$where['moduleid'];
		}		
		// 搜索关键字
		if(isset($where['searchkey']) && $where['searchkey']){
			$key = trim($where['searchkey']);
			// 如果是数字则搜索 bugid,否则搜索标题
			if(is_numeric($key)){
				$_where .= " and b.bugid='$key'";
			}else{
				$_where .= " and b.subject like '%{$key}%'";
			}
		}		
		// BUG类型
		if(isset($where['bugopt']) && $where['bugopt']>0){
			// 所有分配给我的
			if($where['bugopt'] == 1){
				$_where .= " and b.touserid=".$this->userid;
			}else{
				// 我创建的
				$_where .= " and b.createuid=".$this->userid;
			}
		}
		// BUG状态
		if(isset($where['status']) && $where['status']>0){
			$_where .= " and b.status=".$where['status'];
		}
		// 优先级
		if(isset($where['priority']) && $where['priority']>0){
			$_where .= " and b.priority=".$where['priority'];
		}
		// 操作用户
		if(isset($where['userid']) && $where['userid']>0){
			$_where .= sprintf(" and b.%s=%d",$where['usertype'],$where['userid']);
		}
		// 最后的修改时间
		if(isset($where['lastdate']) && $where['lastdate']!=''){
			$_where .= " and b.lastuptime<=".strtotime($where['lastdate']);
		}
		
		// 得到记录数
		if($type == 'count'){
			return $this->db->count($this->bugTable.' as b',$_where);
		}
		// 得到记录
		$projectTable = tabname('project');
		$verTable = tabname('project_vers');
		$moduleTable = tabname('project_modules');
		$sql = "select b.*,p.projectname,v.vername,m.modulename from ".$this->bugTable." as b 
		left join $projectTable as p on b.projectid=p.projectid 
		left join $verTable as v on b.verid=v.verid 
		left join $moduleTable as m on b.moduleid=m.moduleid 
		where $_where order by ";
		// 是否设置了排序字段
		if(isset($where['order']) && $where['order']!=''){
			$byMap = array(0=>'asc',1=>'desc');
			$by = isset($where['by']) ? intval($where['by']) : 0;
			$sql .= sprintf("%s %s",$where['order'],$byMap[$by]);
		}else{
			$sql .= 'bugid desc';
		}
		if($length > 0){
			$sql .= " limit $start,$length";
		}
		return $this->db->fetchAll($sql);
	}	
	
	/**
	 * 高级搜索
	 * 
	 * @param array $param
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 * 
	 * @return array
	 */
	public function search($param,$start=0,$length=0,$type='list'){
		// 如果没有设置搜索标识
		if(!isset($param['issearch']) || !$param['issearch']){
			return $type=='count' ? 0 : array();
		}
		$where1 = '1';
		// 设置了项目ID
		if(isset($param['projectid']) && $param['projectid']>0){
			$where1 .= " and projectid=".intval($param['projectid']);
		}else{
			// 否则显示所在用户组中所有的项目
			$projectArr = $this->_getProjectId();
			if($projectArr){
				$ids = implode(',',$projectArr);
				$where1 .= " and projectid in ($ids)";
			}else{
				$where1 .= " and projectid=0";	
			}
		}
		// 版本ID
		if(isset($param['verid']) && $param['verid']>0){
			$where1 .= " and verid=".$where1['verid'];
		}
		// 模块ID
		if(isset($param['moduleid']) && $param['moduleid']>0){
			$where1 .= " and moduleid=".$where1['moduleid'];
		}
		$where1 = "({$where1})";
		// 以下的所有查询条件是 '与' 还是 '或'
		$condition = (!isset($param['opt']) || $param['opt']==1) ? ' and ' : ' or ';
		$where2 = '1';
		// BUG id
		if(isset($param['bugid']) && $param['bugid'] > 0){
			$where2 .= $condition.$this->_operator($param,'bugid',0);
		}
		// 标题
		if(isset($param['subject']) && $param['subject'] != ''){
			$where2 .= $condition.$this->_operator($param,'subject',1);
		}
		// 当前处理人
		if(isset($param['touserid']) && $param['touserid'] != ''){
			$where2 .= $condition.$this->_operator($param,'touserid',1);
		}
		// 创建人
		if(isset($param['createuid']) && $param['createuid'] != ''){
			$where2 .= $condition.$this->_operator($param,'createuid',1);
		}
		// 严重程度
		$w = $this->_options($param,'severity');
		if($w != ''){
			$where2 .= $condition.$w;
		}
		// 类型
		$w = $this->_options($param,'bugtype');
		if($w != ''){
			$where2 .= $condition.$w;
		}
		// 状态
		$w = $this->_options($param,'status');
		if($w != ''){
			$where2 .= $condition.$w;
		}
		// 优先级
		$w = $this->_options($param,'priority');
		if($w != ''){
			$where2 .= $condition.$w;
		}
		
		$where2 = "({$where2})";
		// 创建时间
		$where3 = '1';
		// 开始时间
		if(isset($param['starttime']) && $param['starttime'] != ''){
			$where3 .= ' and dateline>='.strtotime($param['starttime'].' 00:00:01');
		}
		// 结束时间
		if(isset($param['endtime']) && $param['endtime'] != ''){
			$where3 .= ' and dateline<='.strtotime($param['endtime'].' 23:59:59');
		}
		$where3 = "({$where3})";
		// 组合所有的查询条件
		$where = "{$where1} AND {$where2} AND {$where3}";
		// 得到记录数
		if($type == 'count'){
			return $this->db->count($this->bugTable,$where);
		}
		// 搜索得到所有的 BUG ID
		$bugidArr = $this->db->getCol($this->bugTable)->where($where)->result();
		$bugids = $bugidArr ? implode(',',$bugidArr) : '0';
		// 搜索所有结果集
		$projectTable = tabname('project');
		$verTable = tabname('project_vers');
		$moduleTable = tabname('project_modules');
		$sql = "select b.*,p.projectname,v.vername,m.modulename from ".$this->bugTable." as b 
		left join $projectTable as p on b.projectid=p.projectid 
		left join $verTable as v on b.verid=v.verid 
		left join $moduleTable as m on b.moduleid=m.moduleid 
		where b.bugid in ($bugids) order by bugid asc";
		if($length > 0){
			$sql .= " limit $start,$length";
		}
		return $this->db->fetchAll($sql);
	}
	
	/**
	 * 根据值和运算符得到表达式
	 * 
	 * @param array $param 查询参数
	 * @param string $field 字段名
	 * @param int $type 字段类型 0:数值 1:字符型
	 * 
	 * @return string
	 */
	private function _operator($param,$field,$type=0){
		$oper = $param['select'][$field];
		$value = addslashes($param[$field]);
		// 数值
		if($type == 0){
			switch($oper){
				case 0:
					$ret = " $field like '%{$value}%' ";
					break;
				case 1:
					$ret = " $field >= '{$value}' ";
					break;
				case 2:
					$ret = " $field <= '{$value}' ";
					break;
				case 3:
					$ret = " $field = '{$value}' ";
					break;
				case 4:
					$ret = " $field != '{$value}' ";
					break;
			}
		}else{
			// 字符型
			switch($oper){
				case 0:
					$ret = " $field like '%{$value}%' ";
					break;
				case 1:
					$ret = " $field like '{$value}%' ";
					break;
				case 2:
					$ret = " $field like '%{$value}' ";
					break;
				case 3:
					$ret = " $field = '{$value}' ";
					break;
				case 4:
					$ret = " $field != '{$value}' ";
					break;
			}
		}
		return $ret;
	}
	
	/**
	 * 得到指定字段的查询条件
	 * 
	 * @param array $params 参数
	 * @param string $field 字段名
	 * 
	 * @return string
	 */
	 private function _options($params,$field){
		// 如果没有定义查询
		if(!isset($params[$field])){
			return '';
		}
		$where = $spr = '';
		foreach($params[$field] as $key){
			$where .= sprintf(" %s %s=%d",$spr,$field,$key);
			$spr = 'or';
		}
		return "({$where})";
	}
	
	/**
	 * 得到当前有效的所有项目ID
	 * 
	 * @return array
	 */
	private function _getProjectId(){
		$userModel = new Model_User();
		$projectModel = new Model_Project();
		// 如果是项目管理员则直接查出他所创建的项目
		if($this->userpriv == 2){
			$projectList = $projectModel->projectList($this->userid);
		}else{
			// 普通用户则要先知道他所在的用户组，然后根据用户组可以得到项目管理员
			$pcuid = $userModel->projectCreateUid($this->userid);
			$projectList = $projectModel->projectList($pcuid);
		}
		$result = array();
		foreach ($projectList as $row){
			$result[] = $row['projectid'];
		}
		return $result;
	}
	
	/**
	 * 添加BUG
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $userid
	 * @return unknown
	 */
	public function addBug($sets,$userid){
		if(!isset($sets['dateline'])){
			$sets['dateline'] = time();
		}
		if(!isset($sets['lastuptime'])){
			$sets['lastuptime'] = $sets['dateline'];
		}
		$sets['createuid'] = $userid;
		// 过滤字符
		$sets['subject'] = h($sets['subject']);
		$this->db->insert($this->bugTable,$sets);
		$bugid = $this->db->lastInsertId();
		// 记录操作历史
		$this->addOperate($bugid,$userid,L('bug.created_bug'));
		return $bugid;
	}
	
	/**
	 * 添加BUG操作记录
	 *
	 * @param unknown_type $bugid
	 * @param unknown_type $userid
	 * @param unknown_type $text
	 */
	public function addOperate($bugid,$userid,$text){
		$sets = array(
		'bugid'=>$bugid,
		'userid'=>$userid,
		'text'=>$text,
		'dateline'=>time(),
		);
		$this->db->insert($this->operateHistoryTable,$sets);
		return true;	
	}
	
	/**
	 * 添加BUG文档
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $bugid
	 */
	public function addBugDoc($sets,$bugid){
		$sets['bugid'] = $bugid;
		$this->db->insert($this->bugdocTable,$sets);
		return $this->db->lastInsertId();
	}
	
	/**
	 * 得到BUG文档列表
	 *
	 * @param unknown_type $bugid
	 */
	public function bugDocList($bugid){
		return $this->db->select('*',$this->bugdocTable,array('bugid'=>$bugid));
	}
	
	/**
	 * BUG文档更新
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 * @return unknown
	 */
	public function updateBugDoc($sets,$id){
		return $this->db->update($this->bugdocTable,$sets,array('bugdocid'=>$id));
	}

	/**
	 * 删除BUG
	 *
	 * @param unknown_type $bugid
	 */
	public function removeBug($bugid){
		// 是否有删除权限
		$buginfo = $this->bugInfo($bugid);
		if($buginfo['createuid'] != $this->userid){
			return false;
		}
		// 得到所有的文档
		$doclist = $this->bugDocList($bugid);
		foreach ($doclist as $doc){
			// 删除文档
			$this->removeBugDoc($doc['bugdocid']);
		}
		// 删除BUG
		$this->db->delete($this->bugTable,array('bugid'=>$bugid));
		// 删除变更历史
		$this->db->delete($this->bugHistoryTable,array('bugid'=>$bugid));
		// 删除BUG评论
		$this->db->delete($this->bugCommentTable,array('bugid'=>$bugid));
		// 删除BUG操作历史
		$this->db->delete($this->operateHistoryTable,array('bugid'=>$bugid));
		return true;
	}
	
	/**
	 * 删除文档
	 *
	 * @param unknown_type $id
	 */
	public function removeBugDoc($id){
		// 删除文件
		$info = $this->db->getRow($this->bugdocTable)->where(array('bugdocid'=>$id))->result();
		@unlink(SITEWEB_PATH.'/files/bugdocs/'.$info['docfile']);
		// 删除记录
		$this->db->delete($this->bugdocTable,array('bugdocid'=>$id));
	}		
	
	/**
	 * 得到BUG的详细信息
	 *
	 * @param unknown_type $bugid
	 */
	public function bugInfo($bugid){
		$projectTable = tabname('project');
		$verTable = tabname('project_vers');
		$moduleTable = tabname('project_modules');
		$sql = "select b.*,p.projectname,v.vername,m.modulename from ".$this->bugTable." as b 
		left join $projectTable as p on b.projectid=p.projectid 
		left join $verTable as v on b.verid=v.verid 
		left join $moduleTable as m on b.moduleid=m.moduleid 
		where b.bugid = $bugid";
		return $this->db->fetchRow($sql);
	}
	
	/**
	 * 得到所有BUG的历史变更记录
	 *
	 * @param unknown_type $bugid
	 */
	public function bugHistory($bugid){
		$list = $this->db->getAll($this->bugHistoryTable)->where(array('bugid'=>$bugid))->result();
		$result = array();
		foreach ($list as $row){
			$info = unserialize($row['historydata']);
			$info['dateline'] = $row['dateline'];
			$result[] = $info;
		}
		return $result;
	}
	
	/**
	 * 更新BUG
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 */
	public function updateBug($sets,$bugid){
		$info = $this->bugInfo($bugid);
		// 如果修改了标题或内容则要保存所修改的历史记录
		if(isset($sets['subject']) || isset($sets['info'])){
			if($info){
				$historySet = array(
				'bugid'=>$bugid,
				'historydata'=>serialize($info),
				'dateline'=>time(),
				);
				$this->db->insert($this->bugHistoryTable,$historySet);
			}
			// 修改记录
			$this->addOperate($bugid,$this->userid, L('bug.modifyed_bug_content')); 
		}
		
		// 如果改变了状态则要记录操作记录
		if(isset($sets['status']) && $sets['status']!=$info['status']){
			$bugStatus = QP_Sys::config('bugconfig.status');
			$this->addOperate($bugid,$this->userid, $bugStatus[$sets['status']].L('bug.this_bug'));
			// 如果BUG的状态修改为了 "已接受" 则要把接受者改为当前的用户
			if($sets['status'] == 2){
				$sets['touserid'] = $this->userid;
			}
		}
		
		// 记录最后更新的时间
		if(! isset($sets['lastuptime'])){
			$sets['lastuptime'] = time();
		}
		
		return $this->db->update($this->bugTable,$sets,array('bugid'=>$bugid));
	}
	
	/**
	 * 添加评论
	 *
	 * @param unknown_type $sets
	 * @return unknown
	 */
	public function addComment($sets){
		$this->db->insert($this->bugCommentTable,$sets);
		// 记录操作历史
		$this->addOperate($sets['bugid'],$sets['userid'], L('bug.commented_bug'));
		return $this->db->lastInsertId();
	}
	
	/**
	 * 得到BUG所有的评论列表
	 *
	 * @param unknown_type $bugid
	 */
	public function commentList($bugid){
		return $this->db->getAll($this->bugCommentTable)->where(array('bugid'=>$bugid))->result();
	}
	
	/**
	 * 得到所有的操作历史
	 *
	 * @param unknown_type $bugid
	 */
	public function operateList($bugid){
		return $this->db->getAll($this->operateHistoryTable)->where(array('bugid'=>$bugid))->result();
	}
	
	/**
	 * 报表统计
	 *
	 * @param unknown_type $param
	 */
	public function reportCount($param){
		// 如果没有设置查询
		if(!isset($param['projectid']) || $param['projectid']<1){
			return array(
				'count'=>0,
				'list'=>array(),
				'type'=>'',
			);
		}
		$bugCfg = qp_sys::config('bugconfig');
		$where = 'projectid='.$param['projectid'];
		if(isset($param['verid']) && $param['verid']>0){
			$where .= ' and verid='.$param['verid'];
		}
		if(isset($param['moduleid']) && $param['moduleid']>0){
			$where .= ' and moduleid='.$param['moduleid'];
		}
		// 查询统计
		$list = array();
		$type = 'bugtype';
		$count = 0;
		switch($param['counttype']){
			// 缺陷类型
			case 1:
				foreach($bugCfg['bugtype'] as $bugtype=>$name){
					$list[$bugtype] = $this->db->count($this->bugTable,$where.' and bugtype='.$bugtype);
					$count += $list[$bugtype];
				}
				$type = 'bugtype';
				break;
			// 缺陷分配
			case 2:
				// 得到所分配的所有用户
				$sql = "select DISTINCT touserid from ".$this->bugTable." where $where";
				$touserArr = $this->db->fetchCol($sql);
				foreach($touserArr as $userid){
					$list[$userid] = $this->db->count($this->bugTable, $where.' and touserid='.$userid);
					$count += $list[$userid];
				}
				$type = 'user';
				break;
			// 缺陷创建
			case 3:
				// 得到所分配的所有用户
				$sql = "select DISTINCT createuid from ".$this->bugTable." where $where";
				$touserArr = $this->db->fetchCol($sql);
				foreach($touserArr as $userid){
					$list[$userid] = $this->db->count($this->bugTable, $where.' and createuid='.$userid);
					$count += $list[$userid];
				}
				$type = 'user';
				break;
			// 缺陷状态
			case 4:
				foreach($bugCfg['status'] as $status=>$name){
					$list[$status] = $this->db->count($this->bugTable,$where.' and status='.$status);
					$count += $list[$status];
				}
				$type = 'bugtype';
				break;
			// 缺陷严重程度
			case 5:
				foreach($bugCfg['severity'] as $severity=>$name){
					$list[$severity] = $this->db->count($this->bugTable,$where.' and severity='.$severity);
					$count += $list[$severity];
				}
				$type = 'bugtype';
				break;
			// 缺陷优先级
			case 6:
				foreach($bugCfg['priority'] as $priority=>$name){
					$list[$priority] = $this->db->count($this->bugTable,$where.' and priority='.$priority);
					$count += $list[$priority];
				}
				$type = 'bugtype';
				break;
		}
		return array(
			'count'=>$count,
			'list'=>$list,
			'type'=>$type,
		);
	}
	
	/**
	 * 检查 BUG 操作的合法性
	 *
	 * @param unknown_type $bugid BUGID
	 * @param unknown_type $opt 操作类型 1:浏览 2:编辑 3:删除
	 * @return boolean
	 */
	public function checkOpt($bugid,$opt=1){
		// 得到BUG的详细信息
		$bugInfo = $this->bugInfo($bugid);
		// 如果是浏览BUG
		if($opt == 1){
			// 如果是创建者本人或处理者本人则可以直接验证通过了
			if($bugInfo['touserid']==$this->userid || $bugInfo['createuid']==$this->userid){
				return true;
			}
			// 得到BUG处理人所在组对应的创建者
			$userModel = new Model_User();
			$ginfo = $userModel->usergroupInfo($bugInfo['groupid']);
			// 得到处理人所对应用户组的创建者
			if($this->userpriv == 1){
				$pcUid = $userModel->projectCreateUid($this->userid);
			}else{
				$pcUid = $this->userid;
			}
			// 判断对应的创建者是否为同一个人
			return $ginfo['createuid'] == $pcUid;
		}else{
			// 编辑/删除 则只有创建者自己才有权限
			return $bugInfo['createuid'] == $this->userid;
		}
	}
	
	/**
	 * BUG 详细报表统计
	 *
	 * @param unknown_type $pcUid 当前项目的管理员UID
	 * @param unknown_type $param 查询的参数
	 */
	public function moreCount($pcUid,$param){
		// 如果没有设置查询
		if(!isset($param['count']) || $param['count']!=1){
			return array();
		}
		$result = array();
		$pid = isset($param['projectid']) ? $param['projectid'] : 0;
		$verid = isset($param['verid']) ? $param['verid'] : 0;
		$moduleid = isset($param['moduleid']) ? $param['moduleid'] : 0;
		$projectModel = new Model_Project();
		// 得到所有的项目
		$projectList = $projectModel->projectList($pcUid, $pid); 
		foreach ($projectList as $project){
			// 得到所有版本
			$verList = $projectModel->versList($project['projectid'], $verid);
			$project['verscount'] = $this->_verOrModuleCount($project['projectid'],$verList,0);
			// 所有的模块
			$moduleList = $projectModel->modulesList($project['projectid'], $moduleid);
			$project['modulescount'] = $this->_verOrModuleCount($project['projectid'],$moduleList,1);
			// 组合
			$result[] = $project;
		}
		return $result;
	}
	// 统计所有版本或模块的详细报表
	private function _verOrModuleCount($projectid,$resultList,$type=0){
		$result = array();
		foreach($resultList as $row){
			if($type == 0){ // 版本
				$verid = $row['verid'];
				$moduleid = 0;
			}else{ // 模块
				$verid = 0;
				$moduleid = $row['moduleid'];
			}
			$row['countlist'] = $this->_count($projectid,$verid,$moduleid);
			$result[] = $row;
		}
		return $result;
	}
	
	// 根据项目，版本，模块综合统计
	private function _count($projectid,$verid=0,$moduleid=0){
		$bugCfg = qp_sys::config('bugconfig');
		$where = 'projectid='.$projectid;
		if($verid > 0){
			$where .= ' and verid='.$verid;
		}
		if($moduleid > 0){
			$where .= ' and moduleid='.$moduleid;
		}		
		// 统计类型
		$countType = array( 
			1=>array(
			'name'=>L('bug.bugtype'),//'缺陷类型',
			'item'=>'bugtype'),
			2=>array(
			'name'=>L('bug.to_user'),//'缺陷分配',
			'item'=>''),
			3=>array(
			'name'=>L('bug.creater'),//'缺陷创建',
			'item'=>''),
			4=>array(
			'name'=>L('bug.status'),//'缺陷状态',
			'item'=>'status'),
			5=>array(
			'name'=>L('bug.severity'),//'缺陷严重程度',
			'item'=>'severity'),
			6=>array(
			'name'=>L('bug.priority'),//'缺陷优先级',
			'item'=>'priority'),
		);
		// 查询所有的统计类型
		$result = array();
		foreach ($countType as $type=>$items){
			$row = array('title'=>$items['name'],'lists'=>array());
			// 如果是BUG的属性
			if($items['item'] != ''){
				$field = $items['item'];
				foreach($bugCfg[$field] as $key=>$name){
					$row['lists'][$name] = $this->db->count($this->bugTable,$where." and $field=".$key);
				}				
			}else{
				switch($type){
					// 缺陷分配
					case 2:
						// 得到所分配的所有用户
						$sql = "select DISTINCT touserid from ".$this->bugTable." where $where";
						$touserArr = $this->db->fetchCol($sql);
						foreach($touserArr as $userid){
							// 得到用户名
							$username = User::getInfo($userid,'username');
							$row['lists'][$username] = $this->db->count($this->bugTable, $where.' and touserid='.$userid);
						}
						break;
					// 缺陷创建
					case 3:
						// 得到所分配的所有用户
						$sql = "select DISTINCT createuid from ".$this->bugTable." where $where";
						$touserArr = $this->db->fetchCol($sql);
						foreach($touserArr as $userid){
							// 得到用户名
							$username = User::getInfo($userid,'username');
							$row['lists'][$username] = $this->db->count($this->bugTable, $where.' and createuid='.$userid);
						}
						break;
				} // end switch	
			}// end if 
			$result[] = $row;
		}// end foreach
		return $result;
	}
	
	
}

