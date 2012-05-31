<?php
/**
 * 用户集成相关操作
 *
 * @category QuickBug
 */
class User{

	/**
	 * 发送通知
	 *
	 * @param unknown_type $uid 接收者用户ID或用户名，多个ID用","连接，如:"501,yuanwei"
	 * @param unknown_type $msg 消息文本
	 * @param unknown_type $url 连接地址
	 * @param unknown_type $notifyType 通知类型 'rtx' | 'mail'
	 * @param unknown_type $title 标题,可以为空
	 */
	static public function notify($uids,$msg,$url,$notifyType,$title=''){
		// 得到 userid 得到 username
		$usernames = $spr = '';
		$uidArr = explode(',',$uids);
		foreach ($uidArr as $uid){
			$idtype = is_numeric($uid) ? 0 : 1;
			$uinfo = self::getInfo($uid,'', $idtype);
			$value = $notifyType=='rtx' ? $uinfo['username'] : $uinfo['email'];
			if($value){
				$usernames .= $spr.$value;
				$spr = ',';
			}
		}
		// 找不到接收者则退出
		if($usernames == ''){
			return;
		}
		// 发送对应的通知
		if($title == ''){
			$title = 'QuickBug Notify';
		}else{
			$title = 'QuickBug-'.$title;
		}
		if($notifyType == 'rtx'){
			Rtx::send($usernames,$msg,$url,$title);
		}else{
			$usernames = str_replace(',', ';' ,$usernames);
			$mailObj = new Qmail();

			$view = new QP_View();
			$contents = $view->render('/Common/Mail-tpl.html');

			$body = str_replace(array('{title}','{msg}','{url}'), array($title,$msg,$url), $contents);
			$mailObj->send($usernames,$title,$body);
		}
	}


	/**
	 * 得到用户信息
	 *
	 * @param unknown_type $userid
	 * @param unknown_type $key 属性，如果为空则返回所有
	 * @param unknown_type $idtype 0:根据用户ID查询 1:根据用户名查询
	 */
	static public function getInfo($userid,$key='',$idtype=0){
		static $model = null;
		if(null === $model){
			$model = new Model_User();
		}
		$info = $model->userinfo($userid,$idtype);
		return $key!='' ? (isset($info[$key]) ? $info[$key] : null) : $info;
	}
}
