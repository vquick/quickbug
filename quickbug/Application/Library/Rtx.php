<?php
/**
 * RTX相关操作接口
 *
 * @category QuickBug
 */
class Rtx{
	
	// 调度开关 true:只写日志 false:真正发通知
	static private $debug = false;
	
	
	/**
	 * 发送通知
	 *
	 * @param unknown_type $receiver 接收者ID，多个ID用","连接，如:"yuanwei1,guoyu"
	 * @param unknown_type $msg 消息内容
	 * @param unknown_type $url 点击消息所打开的URL
	 * @param unknown_type $title 标题
	 * @param unknown_type $delaytime 消息提醒框的停留时间（毫秒），0表示不自动消失。
	 */
	static public function send($receiver,$msg,$url,$title='QuickBug Notify',$delaytime=0){
		$rtxCfg = QP_Sys::config('sysconfig.rtx');
		// RTX网关只支持 GBK 的编码
		$msg = sprintf("[%s|%s]",$msg,$url);
		$msg = mb_convert_encoding($msg,'gbk','utf-8');
		$title = mb_convert_encoding($title,'gbk','utf-8');
		// 组合参数
		$params = implode('&',array(
			'title='.urlencode($title),
			'receiver='.$receiver,
			'msg='.urlencode($msg),
			'delaytime='.$delaytime,
		));
		$url = sprintf("http://%s:%d/sendnotify.cgi?%s",$rtxCfg['host'],$rtxCfg['port'],$params);
		if(!self::$debug){
			// 发送请求
			if(function_exists('curl_init')){
				QP_Sys::load('curl')->set(array('port'=>$rtxCfg['port'],'timeOut'=>5))->get($url);
			}else{
				@file_get_contents($url);
			}
		}else{
			// 写日志
			QP_Sys::log($url,'rtx');
		}
	}
}

