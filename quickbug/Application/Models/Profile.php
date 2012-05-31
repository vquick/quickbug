<?php
/**
 * 个人设置模型
 *
 */
class Model_Profile extends Model_base{
	
	// BUG模板表名
	private $bugtplTable ='';
	// 邀请表
	private $inviteTable;
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->bugtplTable = tabname('bug_tpls');
		$this->inviteTable = tabname('bug_invite');
	}
	
	/**
	 * BUG模板列表
	 *
	 * @param unknown_type $userid
	 */
	public function bugtplList($userid){
		return $this->db->select('*',$this->bugtplTable,array('userid'=>$userid));
	}
	
	/**
	 * 添加BUG模板
	 *
	 * @param unknown_type $userid
	 * @param unknown_type $tplname
	 * @param unknown_type $tplhtml
	 * @return unknown
	 */
	public function addBugtpl($userid,$tplname,$tplhtml){
		$sets = array(
		'userid'=>$userid,
		'tplname'=>$tplname,
		'tplhtml'=>$tplhtml,
		);
		$this->db->insert($this->bugtplTable,$sets);
		return $this->db->lastInsertId();
	}
	
	
	/**
	 * 更新BUG模板
	 *
	 * @param unknown_type $sets
	 * @param unknown_type $id
	 */
	public function updateBugtpl($sets,$id){
		return $this->db->update($this->bugtplTable,$sets,array('id'=>$id));
	}	
	
	/**
	 * 删除BUG模板
	 *
	 * @param unknown_type $id
	 */
	public function removeBugtpl($id){
		$this->db->delete($this->bugtplTable,array('id'=>$id));
	}	
	
	/**
	 * 用户组的详细信息
	 *
	 * @param unknown_type $id
	 */
	public function bugtplInfo($id){
		return $this->db->getRow($this->bugtplTable)->where(array('id'=>$id))->result();
	}
	
	/**
	 * 添加邀请
	 *
	 * @param unknown_type $bugid BUGid
	 * @param unknown_type $userid 当前操作用户ID
	 * @param unknown_type $ivtUsers 邀请用户名或用户ID,如:"name1,name2" 或 "123,456"
	 * @param unknown_type $opt 操作选择对应`quickbug_bug_invite`表中的 `opt` 字段 
	 * @param unknown_type $ivtUserType 参数 $ivtUsers 的类型 0:用户名 1:用户ID
	 */
	public function addInvite($bugid,$userid,$ivtUsers,$opt,$ivtUserType=0){
		$nameArr = explode(';',$ivtUsers);
		if($ivtUserType == 0){
			// 用户名要选查询对应的UID
			$user = new Model_User();
			foreach($nameArr as $name){
				$uinfo = $user->userinfo($name, 1);
				if($uinfo){
					// 插入到邀请表
					$sets = array(
						'bugid'=>$bugid,
						'userid'=>$userid,
						'ivtuserid'=>$uinfo['userid'],
						'opt'=>$opt,
						'dateline'=>time(),
					);
					$this->db->insert($this->inviteTable, $sets);
				}
			}
		}else{
			// id则直接插入
			foreach($nameArr as $ivtUid){
				// 插入到邀请表
				$sets = array(
					'bugid'=>$bugid,
					'userid'=>$userid,
					'ivtuserid'=>$ivtUid,
					'opt'=>$opt,
					'dateline'=>time(),
				);
				$this->db->insert($this->inviteTable, $sets);
			}			
		}
		return true;
	}
	
	/**
	 * 得到新的邀请通知数
	 *
	 * @param unknown_type $userid 被邀请的用户ID
	 */
	public function getNewInvite($userid){
		return $this->db->count($this->inviteTable,array('ivtuserid'=>$userid,'isread'=>0));
	}	
	
	/**
	 * 得到新的邀请通知数
	 *
	 * @param unknown_type $userid 被邀请的用户ID
	 * @param unknown_type $start
	 * @param unknown_type $length
	 * @param unknown_type $type
	 */
	public function inviteList($userid,$start=0,$length=0,$type='list'){
		$bug = new Model_Bug();
		$where = array('ivtuserid'=>$userid);
		// 得到记录数
		if($type == 'count'){
			return $this->db->count($this->inviteTable,$where);
		}
		// 取记录数
		$result = array();
		$lists = $this->db->select('*',$this->inviteTable,$where,'isread asc,inviteid desc', "$start,$length");
		foreach($lists as $row){
			$bugInfo = $bug->bugInfo($row['bugid']);
			$row['subject'] = $bugInfo['subject'];
			$result[] = $row;
		}
		return $result;
	}
	
	/**
	 * 删除邀请通知
	 *
	 * @param int|array $id 单个ID或数组
	 */
	public function removeInvite($id){
		$ids = is_array($id) ? implode(',',$id) : $id;
		if($ids == ''){
			return true;
		}
		return $this->db->delete($this->inviteTable,"inviteid in ($ids)");
	}	
	
	/**
	 * 更新邀请状态
	 *
	 * @param unknown_type $id 
	 * @param unknown_type $isread 0:新消息 1:已读消息
	 */
	public function updateInviteStat($id,$isread=1){
		return $this->db->update($this->inviteTable,array('isread'=>$isread),array('inviteid'=>$id));
	}	
	
}