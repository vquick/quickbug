<?php
/**
 * 公共视图助手
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 */

class Helper_Common extends QP_View_Helper
{

	/**
	 * 自动初始化
	 *
	 */
	public function init(){}

	/**
	 * 得到用户角色名
	 *
	 * @param unknown_type $priv
	 */
	public function getUserPrivName($priv){
		$privName = array(
			1=>L('user.normal_user'),//'普通用户',
			2=>L('user.project_admin'),//'项目管理员',
			3=>L('user.super_admin'),//'超级管理员'
		);
		return $privName[$priv];
	}

	/**
	 * 得到邀请通知 Title 说明
	 *
	 * @param int $opt :字段名
	 */
	public function getInviteInfo($opt){
		// 0:创建BUG时的处理人通知 1:创建BUG时的邀请Ta人 2:编辑BUG时的通知处理人 3:浏览BUG时的通知对方 4:浏览BUG时的邀请参与者
		$info = array(
			0=>L('profile.bug_to_user'),//'分配了一个缺陷给您',
			1=>L('profile.invite_to_bug'),//'邀请您关注这个缺陷',
			2=>L('profile.edit_this_bug'),//'编辑了这个缺陷',
			3=>L('profile.process_this_bug'),//'处理了这个缺陷',
			4=>L('profile.invite_comment_bug'),//'邀请您参与这个缺陷',
		);
		return $info[$opt];
	}

	/**
	 * 以友好方式返回文件大小
	 *
	 * @param unknown_type $filename
	 */
	public function getFileSize($filename){
		$filesize = filesize($filename);
		$G = 1024*1024*1024;
		$M = 1024*1024;
		$K = 1024;
		if($filesize >= $G){
			return round($filesize/$G, 2).'G';
		}elseif($filesize >= $M){
			return round($filesize/$M, 2).'M';
		}elseif($filesize >= $K){
			return round($filesize/$K, 2).'K';
		}else{
			return $filesize;
		}
	}

}