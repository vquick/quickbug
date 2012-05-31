<?php
/**
 * 模型基类
 *
 */
abstract class Model_Base{

	// 数据库句柄
	protected $db = null;
	
	// 当前登录用户ID
	protected $userid;
	// 当前登录用户的身份
	protected $userpriv;
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		$this->db = QP_Db::factory();
		$this->userid = QP_Session_Session::get('login_userid');
		$this->userpriv = QP_Session_Session::get('login_priv');
	}
}