<?php
/**
 * 权限模型
 *
 */
class Model_Priv extends Model_base{
	
	// 权限表名
	private $privTable;
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->privTable = tabname('grouppriv');
	}

	/**
	 * 得到群的权限配置
	 *
	 * @param unknown_type $groupid
	 * @return array
	 */
	public function get($groupid){
		$ret = $this->db->getOne($this->privTable)->fields('priv')->where(array('groupid'=>$groupid))->result();
		$priv = $ret ? unserialize($ret) : array();
		return $priv;
	}
	
	/**
	 * 得到群的所有权限资料
	 *
	 * @param unknown_type $groupid
	 * @return array
	 */
	public function getResource($groupid){
		$resource = array();
		$privArr = $this->get($groupid);
		$privcfg = QP_Sys::config('privconfig');
		// example : $privArr = array("1|2","2|1")
		foreach($privArr as $priv){
			list($gid,$resid) = explode('|',$priv);
			if( isset($privcfg['priv'][$gid]) && isset($privcfg['priv'][$gid]['resgroup'][$resid]) ){
				$resource = array_merge($resource,$privcfg['priv'][$gid]['resgroup'][$resid]['resource']);
			}
		}
		return $resource;
	}
	
	/**
	 * 提交权限
	 *
	 * @param unknown_type $priv
	 * @param unknown_type $groupid
	 * @param unknown_type $privType 0:数组 1:字符串
	 * 
	 * @return unknown
	 */
	public function submit($groupid,$priv,$privType=0){
		$sets = array(
		'groupid'=>$groupid,
		'priv'=>$privType==0 ? serialize($priv) : $priv,
		);
		$this->db->insert($this->privTable,$sets,true);
		return true;
	}
	
	/**
	 * 为用户组添加默认权限(所有的权限)
	 *
	 * @param unknown_type $groupid
	 */
	public function addDefaultPriv($groupid){
		// 得到权限的配置
		$privcfg = QP_Sys::config('privconfig');
		$priv = array();
		foreach ($privcfg['priv'] as $gid=>$res){
			foreach ($res['resgroup'] as $resid=>$row){
				$priv[] = $gid.'|'.$resid;
			}
		}
		return $this->submit($groupid,$priv);		
	}
}