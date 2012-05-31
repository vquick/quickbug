<?php
/**
 * 权限操作台
 */
class Priv{
	
	/**
	 * 检测用户是否对URI有访问权限的
	 *
	 * @param unknown_type $userid
	 * @return boolean
	 */
	static public function check($userid,$controller,$action){
		// 非普通用户不查检权限
		if(QP_Session_Session::get('login_priv') != 1){
			return true;
		}
		// 得到配置
		$privcfg = QP_Sys::config('privconfig');
		// 如果不使用权限则永远返回 true
		if(! $privcfg['enable']){
			return true;
		}
		// 判断是否在全局访问的资源中
		$allRes = strtolower($controller.'_*');
		$currentRes = strtolower($controller.'_'.$action);
		if(in_array($currentRes,$privcfg['allow']) || in_array($allRes,$privcfg['allow'])){
			return true;
		}
		// 得到用户所在的组的所有权限
		$userModel = new Model_User();
		$userInfo = $userModel->userinfo($userid);
		$privModel = new Model_Priv();
		$resourceArr = $privModel->getResource($userInfo['groupid']);
		// 判断是否在权限组中
		return in_array($currentRes,$resourceArr) || in_array($allRes,$resourceArr);
	}
}
