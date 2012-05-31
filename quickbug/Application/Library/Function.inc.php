<?php
/**
 * 函数库,适用于喜欢采用函数式编程方法的开发者，引用请看 Bootstrap.php
 *
 * @category QuickBug
 * @copyright http://www.vquickbug.com
 * @version $Id: Bootstrap.php 905 2011-05-05 07:43:56Z yuanwei $
 */

/**
 * 得到表的完整名称
 *
 * @param string $name 表名
 * @return unknown
 */
function tabname($name){
	static $dbName = null;
	if(null === $dbName){
		$dbName = QP_Sys::config('database.default.dbname');
	}
	return $dbName.'.quickbug_'.$name;
}

// 成生URL [简写QP_Sys::url()方法，因为为大量用到这个方法]
function url($controller, $action=QP_Controller::DEFAULT_ACTION, $params=null){
	return QP_Sys::url($controller,$action,$params);
}

// 过滤 XSS
function xss($string){
	return QP_Func_Func::stripTag($string);
}

// 字符串转议
function h($str){
	return htmlspecialchars($str, ENT_QUOTES);
}

/**
 * 创建 FCKeditor 编辑器
 * @param $initInfo :初始化的值
 * @param $fckName :Fckeditor提交后的表单域名称
 * @param $width :Fckeditor编辑器的宽度
 * @param $height :Fckeditor编辑器的高度
 */
function getFckeditor($initInfo='',$fckName='fckEditInfo',$width='100%',$height='600')
{
	include_once(SITEWEB_PATH . "/fckeditor/fckeditor.php");
	$oFCKeditor = new FCKeditor($fckName) ;
	$oFCKeditor->BasePath = "fckeditor/";
	$oFCKeditor->Width = $width;
	$oFCKeditor->Height = $height;
	$oFCKeditor->Value = $initInfo;//默认的内容
	return $oFCKeditor->Create();
}

/**
 * 引入视图文件，一般用在视图中包含子视图之用
 *
 * @param unknown_type $tplfile
 * @param unknown_type $data
 */
function includeFile($tplfile,$data=array()){
	$view = new QP_View();
	$view->assign($data);
	return $view->render($tplfile);
}

/**
 * 将字符以HTML显示
 *
 * @param string $string 字符串
 */
function toHtml($string){
	return QP_Func_Func::toHtml($string);
}

/**
 * 显示文件大小
 *
 * @param unknown_type $size
 */
function showFileSize($size){
	$m = 1024 * 1024;
	$k = 1024;
	if($size > $m){
		return round($size/$m,2).'M';
	}else{
		return round($size/$k,2).'K';
	}
}

/**
 * 得到默认模板的内容
 *
 */
function getDefaultBugTpl(){
	$view = new QP_View();
	return $view->render('/Common/Default-bugtpl.html');
}

/**
 * 得到 BUG 配置项下拉列表
 *
 * @param unknown_type $item 配置选项
 * @param unknown_type $isSelect 是否返回 select
 * @param unknown_type $initVal 初始化的值
 * @return unknown
 */
function getBugItem($item,$isSelect=true,$initVal=''){
	$severity = QP_Sys::config('bugconfig.'.$item);
	if(!$isSelect){
		return $severity;
	}
	$html = '';
	foreach ($severity as $k=>$v){
		$selected = $k==$initVal ? 'selected="selected"' : '';
		$html .= "<option {$selected} value=\"{$k}\">{$v}</option>".PHP_EOL;
	}
	return $html;
}

/**
 * 得到BUG配置项目
 *
 * @param unknown_type $item
 * @param unknown_type $key
 */
function getBugCfgName($item,$key=''){
	$cfg = QP_Sys::config('bugconfig');
	if(!isset($cfg[$item])){
		return null;
	}
	if($key == ''){
		return $cfg[$item];
	}
	return isset($cfg[$item][$key]) ? $cfg[$item][$key] : null;
}

/**
 * 如果用户是RTX用户则返回带样式的真实姓名，否则只显示真实姓名
 *
 * @param unknown_type $userid
 */
function rtxUser($userid){
	$user = User::getInfo($userid);
	if($user['usertype'] == 2){
		return '<span class="rtx">'.$user['username'].'</span>';
	}else{
		return $user['username'];
	}
}

/**
 * 检测当前用户是否有对应的权限
 *
 * @param unknown_type $controller
 * @param unknown_type $action
 */
function privCheck($controller,$action){
	return Priv::check(QP_Session_Session::get('login_userid'), $controller, $action);
}

/**
 * 判断文件是否为图片
 *
 * @param unknown_type $file
 */
function isImage($file){
	// 得到扩展名
	$ext = strtolower(QP_Func_Func::fileExt($file));
	return in_array($ext,array('.jpg','.jpeg','.gif','.png','.bmp'));
}


/**
 * 判断字段是否设置显示了,这个函数一般用在BUG列表字段的控制中
 *
 * @param stirng $field :字段名,如果字段名为空则检测是否有设置显示字段的
 */
function fieldIsSet($field=''){
	$userid = QP_Session_Session::get('login_userid');
	static $userModel = null;
	static $fileds = null;
	if(null === $userModel){
		$userModel = new Model_User();
	}
	if(null === $fileds){
		$fileds = $userModel->getSet('bugListFields',$userid);
	}
	if($field == ''){
		return (null !== $fileds);
	}else{
		return (null === $fileds) ? false : in_array($field,$fileds);
	}
}

/**
 * 判断该字段在列表中是否显示,这个函数用在BUG列表视图中
 *
 * @param stirng $field :字段名
 */
function fieldShow($field=''){
	return !fieldIsSet() || fieldIsSet($field);
}

/**
 * 调试输出
 */
function dump($param){
	QP_Sys::dump($param);
}

/**
 * 根据KEY返回对应语言
 *
 * @param string $item : 如果没有指定控制器则用当前的控制器,如:'index.tabtitle' == 'tabtitle'
 * @param array $vars :替换的内容
 */
function L($item, $vars=array()){
	// 分解控制器和方法和KEY
	if(strpos($item, '.') === false){
		$request = QP_Request::getInstance();
		$get = $request->getGet();
		$parsm = $request->getParam();
		$controller = strtolower($get['controller'] ? $get['controller'] : $parsm['controller']);
		$key = $item;
	}else{
		list($controller, $key) = explode('.', $item);
	}
	// 得到语言包
	static $langArr = array();
	if(!isset($langArr[$controller])){
		$lang = getLang();
		$langArr[$controller] = include APPLICATION_PATH.'/Lang/'.$lang.'/'.ucfirst($controller).'.php';
	}
	// 如果有对应的语言设置
	if(isset($langArr[$controller][$key])){
		$text = $langArr[$controller][$key];
		// 是否要进行替换
		if($vars){
			foreach ($vars as $k => $v) {
				$rk = $k + 1;
				$text = str_replace('\\'.$rk, $v, $text);
			}
		}
	}else{
		$text = $key;
	}
	return $text;
}

/**
 * 得到当前用户所选择的语言
 *
 * @return unknown
 */
function getLang(){
	$lang = null;
	// 如果当前有用户登录了
	if(QP_Session_Session::get('login_userid') > 0){
		$userModel = new Model_User();
		$lang = $userModel->getSet('lang');	
	}
	// 如果用户没有设置则检查 session
	if($lang == null){
		$lang = QP_Session_Session::get('lang');
	}
	// 如果还是没有设置则取系统配置的默认值了
	if($lang == null){
		$lang = QP_Sys::config('Sysconfig.lang');
	}
	return ucfirst($lang);
}