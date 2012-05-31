<?php
/**
 * 用户模型
 *
 */
class Model_User extends Model_base{
	
	// 用户表名
	private $userTable ='';
	
	// 用户组表名
	private $groupTable ='';
	
	// 组与用户关系表
	private $groupUserTable = '';
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->userTable = tabname('user');
		$this->groupTable = tabname('group');
		$this->groupUserTable = tabname('groupuser');
	}
	
	/**
	 * 用户登录
	 *
	 * @param string $username :用户名
	 * @param string $passwd :密码
	 * @param string $pwdType :密码类型 0:明文 1:密文
	 * @return int 0:成功 -1:用户名和密码不对 -2:帐号被禁用
	 */
	public function login($username,$passwd,$pwdType=0){
		$passwd = $pwdType==0 ? md5($passwd) : $passwd;
		// 得到用户数据
		$row = $this->db->getRow($this->userTable)->where(array('username'=>$username,'passwd'=>$passwd))->result();
		// 用户名或密码不对
		if(!$row){
			return -1;
		}
		// 是否禁用
		if($row['enable'] == 0){
			return -2;
		}
		// 写SESSION
		QP_Session_Session::set(array(
		'login_userid'=>$row['userid'],
		'login_username'=>$row['username'],
		'login_truename'=>$row['truename'],
		'login_priv'=>$row['priv'],
		));
		return 0;
	}
	
	/**
	 * 用户登出
	 *
	 * @return boolean
	 */
	public function logout(){
		// 写SESSION
		QP_Session_Session::set(array(
		'login_userid'=>0,
		'login_username'=>'',
		'login_truename'=>'',
		'login_priv'=>0,
		'lang'=>'',
		));
		return true;
	}

	
	/**
	 * 用户列表
	 *
	 * @param unknown_type $params
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 */
	public function adminUserList($params,$start=0,$length=0,$type='list'){
		$where = 'userid >1 and priv>1';
		// 用户名
		if(isset($params['name'])){
			$key = addslashes($params['name']);
			$where .= " and (username like '%{$key}%' or truename like '%{$key}%')";
		}
		// 身份
		if(isset($params['priv'])){
			$where .= " and priv=".$params['priv'];
		}
		// 类型
		if(isset($params['usertype'])){
			$where .= " and usertype=".$params['usertype'];
		}		
		// 状态
		if(isset($params['enable'])){
			$where .= " and enable=".$params['enable'];
		}		
		// 统计
		if($type == 'count'){
			return $this->db->count($this->userTable,$where);
		}
		// 列表
		$sql = "select * from ".$this->userTable." where $where order by userid asc";
		if($length > 0){
			$sql .= " limit $start,$length";
		}
		return $this->db->fetchAll($sql);
	}
	
	/**
	 * 用户详细信息列表
	 *
	 * @param array $whereArr 
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 */
	public function userinfoList($whereArr=array(),$start=0,$length=0,$type='list'){
		// 不显示超级管理员和自己
		$where = '1';
		
		if(isset($whereArr['key'])){
			$whereArr['key'] = addslashes($whereArr['key']);
			$where .= " and (u.username like '%{$whereArr['key']}%' or u.truename like '%{$whereArr['key']}%')";
		}
		if(isset($whereArr['usertype'])){
			$where .= " and u.usertype={$whereArr['usertype']}";
		}
		if(isset($whereArr['enable'])){
			$where .= " and u.enable={$whereArr['enable']}";
		}	
		if(isset($whereArr['groupid'])){
			$where .= " and g.groupid={$whereArr['groupid']}";
		}	
		if(isset($whereArr['createuid'])){
			$where .= " and g.createuid={$whereArr['createuid']}";
		}	
		// TABLE
		$tables = $this->userTable." as u left join ";
		$tables .= $this->groupUserTable." as gu on u.userid=gu.userid left join ";
		$tables .= $this->groupTable." as g on g.groupid=gu.groupid ";
		// 返回统计数
		if($type == 'count'){
			return $this->db->count($tables,$where);
		}
		// 返回结果集		
		$fields = "u.*,g.groupname";
		$limit = $length > 0 ? "$start,$length" : '';
		return $this->db->select($fields,$tables,$where,'userid asc',$limit);
	}	
	
	/**
	 * 添加用户
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $groupid 如果设置了这个值则表示把用户加到对应的组中
	 */
	public function adduser($sets,$groupid=0){
		// 判断用户名是否已存在
		if($this->db->count($this->userTable,array('username'=>$sets['username']))){
			return false;
		}
		if(!isset($sets['dateline'])){
			$sets['dateline'] = time();
		}
		// 密码加密
		$sets['passwd'] = md5($sets['passwd']);
		$this->db->insert($this->userTable,$sets);
		$userid = $this->db->lastInsertId();
		// 加到用户组
		if($groupid > 0){
			$this->addGroupUser($groupid,$userid);
		}
		// 如果是添加的"项目管理员"则自动为他创建两个用户组和一个默认的项目
		if(isset($sets['priv']) && $sets['priv']==2){
			$groupset = array('createuid'=>$userid);
			// 创建开发组
			$groupset['groupname'] = $groupset['info'] = L('user.dev_group');
			$groupset['grouptype'] = 1;
			$this->adduserGroup($groupset);
			// 创建测试组
			$groupset['groupname'] = $groupset['info'] = L('user.test_group');
			$groupset['grouptype'] = 2;
			$this->adduserGroup($groupset);
			// 创建项目
			$project = new Model_Project();
			$project->addProject(L('user.default_project'),L('user.default_project_info'),$userid);
		}
		// 如果是添加的普通用户并且是RTX用户的话则设置为自动登录
		if( (!isset($sets['priv']) || $sets['priv']==1) && $sets['usertype']==2){
			$this->saveSet('rtxAutoLogin',1,$userid);
		}
		return $userid;
	}
	
	/**
	 * 添加组用户
	 *
	 * @param unknown_type $groupid
	 * @param unknown_type $userid
	 */
	private function addGroupUser($groupid,$userid){
		$this->db->insert($this->groupUserTable,array(
		'groupid'=>$groupid,
		'userid'=>$userid,
		));	
	}
	
	/**
	 * 更新用户属性
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $userid
	 * @param unknown_type $groupid
	 */
	public function updateuser($sets,$userid,$groupid=0){
		if(isset($sets['passwd'])){
			if($sets['passwd'] != ''){
				$sets['passwd'] = md5($sets['passwd']);
			}else{
				unset($sets['passwd']);
			}
		}
		$ret = $this->db->update($this->userTable,$sets,array('userid'=>$userid));
		// 用户组修改
		if($groupid > 0){
			$this->db->update($this->groupUserTable,array('groupid'=>$groupid),array('userid'=>$userid));
		}
		return $ret;
	}
	
	/**
	 * 用户的详细信息
	 *
	 * @param unknown_type $id ID
	 * @param unknown_type $idtype 0:根据用户ID查询 1:根据用户名查询
	 */
	public function userinfo($id,$idtype=0){
		$id = addslashes($id);
		$format = "select u.*,g.groupid from %s as u left join %s as g on u.userid=g.userid where ";
		$format .= $idtype==0 ? "u.userid=%d" : "u.username='%s'";
		$sql = sprintf($format,$this->userTable,$this->groupUserTable,$id);
		return $this->db->fetchRow($sql);
	}
	
	/**
	 * 删除用户
	 *
	 * @param unknown_type $userid
	 */
	public function removeuser($userid){
		// 删除用户资料
		$this->db->delete($this->userTable,array('userid'=>$userid));
		// 删除用户与组的关系
		$this->removeGroupUser($userid);
		// 删除用户所创建的组
		$this->db->delete($this->groupTable,array('createuid'=>$userid));
		// 还有其它操作
	}
	
	/**
	 * 删除组用户
	 *
	 * @param unknown_type $userid
	 */
	private function removeGroupUser($userid){
		// 删除记录
		$this->db->delete($this->groupUserTable,array('userid'=>$userid));
	}
	
	
	/**
	 * 用户组列表
	 *
	 * @param unknown_type $params
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 */
	public function userGroupList($params,$start=0,$length=0,$type='list'){
		$where = "1";
		// 如果设置了创建人的UID
		if(isset($params['userid'])){
			$where .= sprintf(" and createuid=%d",$params['userid']);
		}
		// 如果设置的查询KEY
		if(isset($params['key']) && $params['key']){
			$key = addslashes($params['key']);
			$where .= " and (groupname like '%{$key}%')";
		}
		// 统计记录数
		if($type == 'count'){
			return $this->db->count($this->groupTable,$where);
		}
		$sql = "select * from ".$this->groupTable." where $where order by groupid asc";
		if($length > 0){
			$sql .= " limit $start,$length";
		}
		$lists = $this->db->fetchAll($sql);
		// 得到用户数
		foreach ($lists as $key=>$row){
			$row['usernum'] = $this->db->count($this->groupUserTable,array('groupid'=>$row['groupid']));
			$lists[$key] = $row;
		}
		return $lists;
	}	
	
	/**
	 * 添加用户组
	 *
	 * @param unknown_type $sets
	 */
	public function adduserGroup($sets){
		// 如果没有设置创建者则自己为当前用户
		if(!isset($sets['createuid'])){
			$sets['createuid'] = $this->userid;
		}
		// 判断是否已存在
		if($this->db->count($this->groupTable,array('groupname'=>$sets['groupname'],'createuid'=>$sets['createuid']))){
			return false;
		}
		if(!isset($sets['dateline'])){
			$sets['dateline'] = time();
		}
		$this->db->insert($this->groupTable,$sets);
		$groupid = $this->db->lastInsertId();
		// 为组添加默认权限
		$priv = new Model_Priv();
		$priv->addDefaultPriv($groupid);
		return $groupid;
	}	
	
	/**
	 * 更新用户组属性
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $groupid
	 */
	public function updateuserGroup($sets,$groupid){
		return $this->db->update($this->groupTable,$sets,array('groupid'=>$groupid));
	}	
	
	/**
	 * 删除用户组
	 *
	 * @param unknown_type $groupid
	 */
	public function removeuserGroup($groupid){
		// 判断是否是创建者
		$info = $this->usergroupInfo($groupid);
		if($info['createuid'] != $this->userid){
			return false;
		}
		// 删除用户组
		$this->db->delete($this->groupTable,array('groupid'=>$groupid));
		// 删除组对应的所有用户关系
		$this->db->delete($this->groupUserTable,array('groupid'=>$groupid));
		// 考虑到其它数据的重要性，不删除。
	}	
	
	/**
	 * 用户组的详细信息
	 *
	 * @param unknown_type $groupid
	 */
	public function usergroupInfo($groupid){
		return $this->db->getRow($this->groupTable)->where(array('groupid'=>$groupid))->result();
	}
	
	/**
	 * 根据普通用户的UID得到项目管理员的UID
	 *
	 * @param unknown_type $userid
	 */
	public function projectCreateUid($userid){
		// 得到用户所在的组
		$uinfo = $this->userinfo($userid);
		// 根据组得到创建者,就是项目管理员了,因为只有项目管理员才可以创建组
		$ginfo = $this->usergroupInfo($uinfo['groupid']);
		return $ginfo['createuid'];
	}
	
	/**
	 * 得到用户的设置
	 *
	 * @param string $key :配置项键值,为空时返回所有的配置
	 * @param int $userid :用户ID
	 * @return array || string || int  注意：不存在时返回 null
	 */
	public function getSet($key=null,$userid=0){
		$userid = $userid ? $userid : $this->userid;
		$info = $this->userinfo($userid);
		$setStr = $info['ext'];
		if(! $setStr){
			return null;
		}
		$data = unserialize($setStr);
		if($key == null){
			return $data;
		}
		return isset($data[$key]) ? $data[$key] : null;
	}

	/**
	 * 更新用户的设置
	 *
	 * @param array || string  $key :可以是MAP关系数组如 array('clubWrited'=>1) 或 'clubWrited' 代码的键名
	 * @param unkone $value :任意值
	 * @param int $userid :用户ID
	 */
	public function saveSet($key,$value='',$userid=0){
		$userid = $userid ? $userid : $this->userid;
		$data = $this->getSet(null,$userid);
		if($data === null){
			$data = array();
		}
		if(is_array($key)){
			$data = array_merge($data,$key);
		}else{
			$data[$key] = $value;
		}
		$setStr = serialize($data);
		return $this->db->update($this->userTable,array('ext'=>$setStr),array('userid'=>$userid));
	}
	
	/**
	 * 检查操作的合法性
	 *
	 * @param unknown_type $idtype 检测的ID类型 1:用户ID 2:用户组ID
	 * @return boolean
	 */
	public function checkOpt($id,$idtype=1){
		// 用户就要得到对应的项目管理
		if($idtype == 1){
			$pcUid = $this->projectCreateUid($id);
		}else{
			// 用户组就得到对应的创建者
			$ginfo = $this->usergroupInfo($id);
			$pcUid = $ginfo['createuid'];
		}
		return $pcUid == $this->userid;
	}
	
}