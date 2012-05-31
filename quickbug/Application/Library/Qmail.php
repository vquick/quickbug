<?php
/**
 * 发送邮件，引用的 phpMailer 的功能
 * 
 * @category QuickBug
 */

include_once dirname(__FILE__).'/phpmailer/class.phpmailer.php';
class Qmail{
	
	// 调度开关 true:只写日志 false:真正发邮件
	private $debug = false;
	
	/**
	 * 邮件服务器配置
	 *
	 * @var unknown_type
	 */
	private $_config = array();
	
	/**
	 * 构造函数
	 *
	 */
	public function __construct(){
		$this->_config = QP_Sys::config('sysconfig.mail');
	}
	
	/**
	 * 发送邮件
	 *
	 * @param string $address 收件人,多个用 ";" 分开
	 * @param string $subject 主题
	 * @param string $body 内容
	 * @param string $attachment 附件文件，多个用 ";" 分开,要用绝对路径，如 "/tmp/a.zip;/tmp/b.zip"
	 * @return unknown
	 */
	public function send($address,$subject,$body,$attachment=''){
		$mail = new PHPMailer();
		$mail->CharSet = 'UTF-8';
		$mail->SetLanguage('zh_cn');
		$mail->IsSMTP();
		$mail->SMTPDebug = 1;
		$mail->SMTPAuth = $this->_config['auth'];
		$mail->Host = $this->_config['host'];
		$mail->Port = $this->_config['port'];
		$mail->Username = $this->_config['username']; 
		$mail->Password = $this->_config['password'];
		$mail->SetFrom($this->_config['from'], $this->_config['fromname']);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		$mail->SMTPSecure = $this->_config['secure'];
		
		// 收件人
		$addrArr = explode(';',$address);
		foreach($addrArr as $addr){
			$mail->AddAddress($addr);
		}
		// 附件
		if($attachment != ''){
			$attaArr = explode(';',$attachment);
			foreach($attaArr as $file){
				$mail->AddAttachment($file);
			}
		}
		if(! $this->debug){
			// 发送
			if(!$mail->Send()) {
				$ret = $mail->ErrorInfo;
			} else {
				$ret = true;
			}
		}else{
			// 写日志
			$message = $subject.PHP_EOL.$body;
			QP_Sys::log($message,'email');
			$ret = true;
		}
		return $ret;
	}
	
}