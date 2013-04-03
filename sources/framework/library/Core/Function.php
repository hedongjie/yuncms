<?php
/**
 * 系统核心函数库
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Function.php 2 2013-01-14 07:14:05Z xutongle $
 */
define ( 'CORE_FUNCTION', true );

/**
 * 返回经addslashes处理过的字符串或数组
 *
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string) {
	if (! is_array ( $string )) return addslashes ( $string );
	foreach ( $string as $key => $val )
		$string [$key] = new_addslashes ( $val );
	return $string;
}

/**
 * 返回经stripslashes处理过的字符串或数组
 *
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if (empty ( $string )) return $string;
	if (! is_array ( $string )) {
		return stripslashes ( $string );
	} else {
		foreach ( $string as $key => $val ) {
			$string [$key] = new_stripslashes ( $val );
		}
	}
	return $string;
}

/**
 * 返回经htmlspecialchars处理过的字符串或数组
 *
 * @param $obj 需要处理的字符串或数组
 * @return mixed
 */
function new_htmlspecialchars($string) {
	if (! is_array ( $string )) return htmlspecialchars ( $string );
	foreach ( $string as $key => $val )
		$string [$key] = new_htmlspecialchars ( $val );
	return $string;
}

/**
 * 自定义异常处理
 *
 * @param string $msg
 *        	异常消息
 * @param string $type
 *        	异常类型 默认为Core_Exception
 * @param integer $code
 *        	异常代码 默认为0
 * @return void
 */
function throw_exception($msg, $type = 'Core_Exception', $code = 0) {
	if (class_exists ( $type )) {
		throw new $type ( $msg, $code, true );
	} else {
		Helper::halt ( $msg );
	}
}

/**
 * 载入文件或类
 *
 * @param string $name
 *        	文件名称 或带路径的文件名称
 * @param string $folder
 *        	文件夹默认为空
 */
function import($name, $folder = '') {
	return Core::import ( $name, $folder );
}

/**
 * 加载配置文件
 *
 * @param string $file
 *        	文件名
 * @param string $key
 *        	配置项
 * @param string/bool $default
 *        	默认值
 */
function C($file, $key = null, $default = false) {
	return Core_Config::get ( $file, $key, $default );
}

/**
 * 从格式话存储单位返回字节
 *
 * @param string $val
 *        	格式化存储单位
 */
function format_byte($val) {
	$val = trim ( $val );
	$last = strtolower ( $val {strlen ( $val ) - 1} );
	switch ($last) {
		case 'g' :
			$val *= 1024;
		case 'm' :
			$val *= 1024;
		case 'k' :
			$val *= 1024;
	}
	return $val;
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 *
 * @param mixed $mix
 *        	变量
 * @return string
 */
function to_guid_string($mix) {
	if (is_object ( $mix ) && function_exists ( 'spl_object_hash' )) {
		return spl_object_hash ( $mix );
	} elseif (is_resource ( $mix )) {
		$mix = get_resource_type ( $mix ) . strval ( $mix );
	} else {
		$mix = serialize ( $mix );
	}
	return md5 ( $mix );
}

/**
 * 错误日志接口
 *
 * @param string $level
 *        	日志级别
 * @param string $message
 *        	日志信息
 * @param boolean $php_error
 *        	是否是PHP错误
 */
function log_message($level = 'error', $message, $php_error = FALSE) {
	if (C ( 'log', 'log_threshold' ) == 0) return;
	Core::log ()->write ( $level, $message, $php_error );
}

/**
 * 字符串加密、解密函数
 *
 * @param string $txt
 * @param string $operation
 * @param string $key
 * @return string
 */
function authcode($txt, $operation = 'ENCODE', $key = '') {
	$key = $key ? $key : C ( 'config', 'auth_key' );
	$txt = $operation == 'ENCODE' ? ( string ) $txt : base64_decode ( $txt );
	$len = strlen ( $key );
	$code = '';
	for($i = 0; $i < strlen ( $txt ); $i ++) {
		$k = $i % $len;
		$code .= $txt [$i] ^ $key [$k];
	}
	$code = $operation == 'DECODE' ? $code : base64_encode ( $code );
	return $code;
}

/**
 * 记录加载和运行时间
 *
 * @param string $start
 * @param string $end
 * @param int $dec
 */
function G($start, $end = '', $dec = 3) {
	static $_info = array ();
	if (! empty ( $end )) { // 统计时间
		if (! isset ( $_info [$end] )) $_info [$end] = microtime ( TRUE );
		return number_format ( ($_info [$end] - $_info [$start]), $dec );
	} else { // 记录时间
		$_info [$start] = microtime ( TRUE );
	}
}

/**
 * 加载视图
 *
 * @param string $template
 * @param string $$application
 * @param string $$application
 */
function V($template = 'index', $application = null, $style = null) {
	if ($style == null) $style = C ( 'template', 'name' );
	$compiledtplfile = View::instance ()->compile ( $template, $application, $style );
	return $compiledtplfile;
}

/**
 * Cookie设置、获取、删除
 *
 * @param string $var
 *        	Cookie名称
 * @param string $value
 *        	Cookie值
 * @param int $time
 *        	Cookie有效期
 * @return Ambigous <mixed, string, unknown>
 */
function cookie($var, $value = null, $time = 0) {
	if (is_null ( $value )) {
		return Cookie::get ( $var );
	} else if ($value == '') {
		return Cookie::delete ( $var );
	} else {
		return Cookie::set ( $var, $value, $time );
	}
}

/**
 * 操作KVdb的快速方法
 */
function KV($key, $value = '') {
	static $kvdb = null;
	if (is_null ( $kvdb )) $kvdb = new KVDB ( 'SAE' );
	if ($value == '') {
		return $kvdb->get ( $key );
	}
	if ($kvdb->get ( $key )) {
		return $kvdb->set ( $key, $value );
	}
	$kvdb->add ( $key, $value );
}

/**
 * 设置和获取统计数据
 *
 * @param string $key
 *        	要统计的项
 * @param int $step
 *        	递加的值
 * @return int 如果递加的值为空返回目前该项统计到的次数
 */
function N($key, $step = 0) {
	static $_num = array ();
	if (! isset ( $_num [$key] )) {
		$_num [$key] = 0;
	}
	if (empty ( $step ))
		return $_num [$key];
	else
		$_num [$key] = $_num [$key] + ( int ) $step;
}

/**
 * 全局缓存读取、设置、删除，默认为文件缓存。
 *
 * @param string $key
 *        	缓存名称
 * @param string $value
 *        	缓存内容
 * @param int $expires
 *        	缓存有效期
 * @param string $options
 *        	缓存配置
 */
function S($key, $value = null, $expires = 0, $options = null) {
	if (is_null ( $value )) { // 获取缓存
		return Loader::cache ( $options )->get ( $key );
	} elseif ($value === '') { // 删除缓存
		return Loader::cache ( $options )->delete ( $key );
	} else {
		return Loader::cache ( $options )->set ( $key, $value, $expires );
	}
}

/**
 * 全局缓存信息获取
 *
 * @param string $name
 *        	缓存名称
 * @param string $setting
 *        	配置名称
 */
function I($key, $setting = 'default') {
	return Loader::cache ( $setting )->info ( $key );
}

/**
 * 无模型文件实例化模型并设置到当前表
 */
function M($table) {
	static $model = array ();
	if (! isset ( $model [$table] )) {
		Loader::model ( 'get_model', false );
		$model [$table] = new get_model ( null, $table );
	}
	return $model [$table];
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * @param string $name
 *        	文件名称
 * @param mixed $value
 *        	文件内容
 * @param string $path
 *        	存储路径
 * @return mixed
 */
function F($name, $value = '', $path = DATA_PATH) {
	$filename = $path . $name;
	if ($value == '') { // 获取文件
	} elseif (is_null ( $value )) { // 删除文件
	} else { // 写入文件
	}
	if ($content == '') {
		return Loader::storage ( $config )->read ( $domain, $filename );
	} else {
		return Loader::storage ( $config )->write ( $domain, $filename );
	}
}

/**
 * 程序执行时间
 *
 * @return int
 */
function execute_time() {
	$etime = microtime ( true );
	return number_format ( ($etime - START_TIME), 6 );
}
function set_status_header($code = 200, $text = '') {
	$stati = array (200 => 'OK',201 => 'Created',202 => 'Accepted',203 => 'Non-Authoritative Information',204 => 'No Content',205 => 'Reset Content',206 => 'Partial Content',

	300 => 'Multiple Choices',301 => 'Moved Permanently',302 => 'Found',304 => 'Not Modified',305 => 'Use Proxy',307 => 'Temporary Redirect',

	400 => 'Bad Request',401 => 'Unauthorized',403 => 'Forbidden',404 => 'Not Found',405 => 'Method Not Allowed',406 => 'Not Acceptable',407 => 'Proxy Authentication Required',408 => 'Request Timeout',409 => 'Conflict',410 => 'Gone',411 => 'Length Required',412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',414 => 'Request-URI Too Long',415 => 'Unsupported Media Type',416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed',

			500 => 'Internal Server Error',501 => 'Not Implemented',502 => 'Bad Gateway',503 => 'Service Unavailable',504 => 'Gateway Timeout',505 => 'HTTP Version Not Supported' );

	if ($code == '' or ! is_numeric ( $code )) {
		Helper::show_error ( 'Status codes must be numeric', 500 );
	}

	if (isset ( $stati [$code] ) and $text == '') {
		$text = $stati [$code];
	}

	if ($text == '') {
		Helper::show_error ( 'No status text available.  Please check your status code number or supply your own message text.', 500 );
	}
	$server_protocol = (isset ( $_SERVER ['SERVER_PROTOCOL'] )) ? $_SERVER ['SERVER_PROTOCOL'] : FALSE;
	if (IS_CGI) {
		header ( "Status: {$code} {$text}", TRUE );
	} elseif ($server_protocol == 'HTTP/1.1' or $server_protocol == 'HTTP/1.0') {
		header ( $server_protocol . " {$code} {$text}", TRUE, $code );
	} else {
		header ( "HTTP/1.1 {$code} {$text}", TRUE, $code );
	}
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 *
 * @param int $len
 *        	长度
 * @param int $type
 *        	字串类型 0 字母 1 数字 2 大写字母 3 小写字母 其它 混合
 * @param string $add_chars
 *        	额外字符
 * @return string
 */
function random($len = 6, $type = '', $add_chars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $add_chars;
			break;
		case 1 :
			$chars = str_repeat ( '0123456789', 3 );
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $add_chars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $add_chars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $add_chars;
			break;
	}
	if ($len > 10) {
		// 位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );
	}
	$chars = str_shuffle ( $chars );
	$str = substr ( $chars, 0, $len );
	return $str;
}

/**
 * 字符截取 支持UTF8/GBK
 *
 * @param
 *        	$string
 * @param
 *        	$length
 * @param
 *        	$dot
 */
function str_cut($string, $length, $dot = '...') {
	$strlen = strlen ( $string );
	if ($strlen <= $length) return $string;
	$string = str_replace ( array (' ','&nbsp;','&amp;','&quot;','&#039;','&ldquo;','&rdquo;','&mdash;','&lt;','&gt;','&middot;','&hellip;' ), array ('∵',' ','&','"',"'",'“','”','—','<','>','·','…' ), $string );
	$strcut = '';
	if (strtolower ( CHARSET ) == 'utf-8') {
		$length = intval ( $length - strlen ( $dot ) - $length / 3 );
		$n = $tn = $noc = 0;
		while ( $n < strlen ( $string ) ) {
			$t = ord ( $string [$n] );
			if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1;
				$n ++;
				$noc ++;
			} elseif (194 <= $t && $t <= 223) {
				$tn = 2;
				$n += 2;
				$noc += 2;
			} elseif (224 <= $t && $t <= 239) {
				$tn = 3;
				$n += 3;
				$noc += 2;
			} elseif (240 <= $t && $t <= 247) {
				$tn = 4;
				$n += 4;
				$noc += 2;
			} elseif (248 <= $t && $t <= 251) {
				$tn = 5;
				$n += 5;
				$noc += 2;
			} elseif ($t == 252 || $t == 253) {
				$tn = 6;
				$n += 6;
				$noc += 2;
			} else {
				$n ++;
			}
			if ($noc >= $length) {
				break;
			}
		}
		if ($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr ( $string, 0, $n );
		$strcut = str_replace ( array ('∵','&','"',"'",'“','”','—','<','>','·','…' ), array (' ','&amp;','&quot;','&#039;','&ldquo;','&rdquo;','&mdash;','&lt;','&gt;','&middot;','&hellip;' ), $strcut );
	} else {
		$dotlen = strlen ( $dot );
		$maxi = $length - $dotlen - 1;
		$current_str = '';
		$search_arr = array ('&',' ','"',"'",'“','”','—','<','>','·','…','∵' );
		$replace_arr = array ('&amp;','&nbsp;','&quot;','&#039;','&ldquo;','&rdquo;','&mdash;','&lt;','&gt;','&middot;','&hellip;',' ' );
		$search_flip = array_flip ( $search_arr );
		for($i = 0; $i < $maxi; $i ++) {
			$current_str = ord ( $string [$i] ) > 127 ? $string [$i] . $string [++ $i] : $string [$i];
			if (in_array ( $current_str, $search_arr )) {
				$key = $search_flip [$current_str];
				$current_str = str_replace ( $search_arr [$key], $replace_arr [$key], $current_str );
			}
			$strcut .= $current_str;
		}
	}
	return $strcut . $dot;
}

/**
 * 浏览器友好的变量输出
 *
 * @param mixed $var
 *        	变量
 * @param boolean $echo
 *        	是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label
 *        	标签 默认为空
 * @param boolean $strict
 *        	是否严谨 默认为true
 * @return void string
 */
function dump($var, $echo = true, $label = null, $strict = true) {
	$label = ($label === null) ? '' : rtrim ( $label ) . ' ';
	if (! $strict) {
		if (ini_get ( 'html_errors' )) {
			$output = print_r ( $var, true );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		} else {
			$output = $label . print_r ( $var, true );
		}
	} else {
		ob_start ();
		var_dump ( $var );
		$output = ob_get_clean ();
		if (! extension_loaded ( 'xdebug' )) {
			$output = preg_replace ( '/\]\=\>\n(\s+)/m', '] => ', $output );
			$output = '<pre>' . $label . htmlspecialchars ( $output, ENT_QUOTES ) . '</pre>';
		}
	}
	if ($echo) {
		echo ($output);
		return null;
	} else
		return $output;
}

/**
 * URL重定向
 *
 * @param string $url
 *        	重定向的URL地址
 * @param integer $time
 *        	重定向的等待时间（秒）
 * @param string $msg
 *        	重定向前的提示信息
 * @return void
 */
function redirect($url, $time = 0, $msg = '') {
	// 多行URL地址支持
	$url = str_replace ( array ("\n","\r" ), '', $url );
	if (empty ( $msg )) $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
	if (! headers_sent ()) { // redirect
		if (0 === $time) {
			header ( 'Location: ' . $url );
		} else {
			header ( "refresh:{$time};url={$url}" );
			echo ($msg);
		}
		exit ();
	} else {
		$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
		if ($time != 0) $str .= $msg;
		exit ( $str );
	}
}

/**
 * 语言文件处理
 *
 * @param string $language
 * @param array $pars
 * @param string $applications
 * @return string
 */
function L($language = 'NO_LANG', $pars = array(), $applications = '') {
	static $lang = null;
	if (is_null ( $lang )) $lang = Core_Lang::instance ();
	return $lang->load ( $language, $pars, $applications );
}

/**
 * 队列操作
 *
 * @param string $name
 * @param array $data
 * @param string $setting
 */
function Q($name, $data = '', $setting = 'default') {
	$queue = Loader::queue ( $setting );
	if (empty ( $data )) {
		return $queue->get ( $name );
	}
	return $queue->put ( $name, $data );
}

/**
 * URL组装 支持不同URL模式
 *
 * @param string $url
 *        	URL表达式，格式：'[应用/模块/操作]?参数1=值1&参数2=值2...'
 * @param string|array $vars
 *        	传入的参数，支持数组和字符串
 * @param boolean $redirect
 *        	是否跳转，如果设置为true则表示跳转到该URL地址
 * @param boolean $domain
 *        	是否显示域名
 * @return string
 */
function U($url = '', $vars = '', $redirect = false, $domain = false) {
	// 解析URL
	$info = parse_url ( $url );
	$url = ! empty ( $info ['path'] ) ? $info ['path'] : ACTION;
	if (isset ( $info ['fragment'] )) { // 解析锚点
		$anchor = $info ['fragment'];
		if (false !== strpos ( $anchor, '?' )) { // 解析参数
			list ( $anchor, $info ['query'] ) = explode ( '?', $anchor, 2 );
		}
	}
	// 解析参数
	if (is_string ( $vars )) { // aaa=1&bbb=2 转换成数组
		parse_str ( $vars, $vars );
	} elseif (! is_array ( $vars )) {
		$vars = array ();
	}
	if (isset ( $info ['query'] )) { // 解析地址里面参数 合并到vars
		parse_str ( $info ['query'], $params );
		$vars = array_merge ( $params, $vars );
	}
	// URL组装
	if ($url) {
		$url = trim ( $url, '/' );
		$path = explode ( '/', $url );
		$var = array ();
		if (isset ( $path [2] )) $var ['action'] = $path [2];
		if (isset ( $path [1] )) $var ['controller'] = $path [1];
		$var ['app'] = isset ( $path [0] ) ? $path [0] : APP;
	}
	if (C ( 'config', 'url_model' ) == 0) { // 普通模式URL转换
		$url = PHP_FILE . '?' . http_build_query ( array_reverse ( $var ) );
		if (! empty ( $vars )) {
			$vars = urldecode ( http_build_query ( $vars ) );
			$url .= '&' . $vars;
		}
	} else if (C ( 'config', 'url_model' ) != 0) {
		$url = WEB_PATH . implode ( '/', array_reverse ( $var ) );
		if (! empty ( $vars )) { // 添加参数
			$params = http_build_query ( $vars );
			$url = $url . '?' . $params;
		}
	}
	if ($domain) {
		$url = SITE_PROTOCOL . SITE_HOST . $url;
	}
	if ($redirect) // 直接跳转URL
		redirect ( $url );
	else
		return $url;
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 *
 * @param string $name
 *        	字符串
 * @param integer $type
 *        	转换类型
 * @return string
 */
function parse_name($name, $type = 0) {
	if ($type) {
		return ucfirst ( preg_replace ( "/_([a-zA-Z])/e", "strtoupper('\\1')", $name ) );
	} else {
		return strtolower ( trim ( preg_replace ( "/[A-Z]/", "_\\0", $name ), "_" ) );
	}
}

/**
 * 对数据进行编码转换
 *
 * @param array/string $data
 *        	数组
 * @param string $input
 *        	需要转换的编码
 * @param string $output
 *        	转换后的编码
 */
function array_iconv($data, $input = 'gbk', $output = 'utf-8') {
	if (! is_array ( $data )) {
		return iconv ( $input, $output, $data );
	} else {
		foreach ( $data as $key => $val ) {
			if (is_array ( $val )) {
				$data [$key] = array_iconv ( $val, $input, $output );
			} else {
				$data [$key] = iconv ( $input, $output, $val );
			}
		}
		return $data;
	}
}

/**
 * 使用phpqrcode生成二维码
 *
 * @param string $value
 *        	二维码数据
 * @param string $level
 *        	纠错级别：L、M、Q、H
 * @param int $size
 *        	点的大小：1到10,用于手机端4就可以了
 */
function qrcode($value, $level = 'L', $size = 4) {
	Loader::lib ( 'QRcode.QRcode', false );
	return QRcode::png ( $value, false, $level, $size );
}

/**
 * 显示运行时间、数据库操作、缓存次数、内存使用信息
 *
 * @return string
 */
function show_time() {
	if (! C ( 'config', 'show_time' )) return;
	$show_time = '';
	// 显示运行时间
	$show_time = 'Process: ' . execute_time () . ' seconds ';
	if (class_exists ( 'Core_DB', false )) $show_time .= ' | DB :' . N ( 'db_query' ) . ' queries ';
	$show_time .= ' | Cache :' . N ( 'cache_read' ) . ' gets ' . N ( 'cache_write' ) . ' writes ';
	// 显示内存开销
	$startMem = array_sum ( explode ( ' ', START_MEMORY ) );
	$endMem = array_sum ( explode ( ' ', memory_get_usage () ) );
	$show_time .= ' | UseMem:' . number_format ( ($endMem - $startMem) / 1024 ) . ' kb';
	if (IS_CLI) return "\r\n" . $show_time . "\r\n";
	return $show_time;
}

/**
 * 添加和获取页面Trace记录
 *
 * @param string $value
 *        	变量
 * @param string $label
 *        	标签
 * @param string $level
 *        	日志级别
 * @param boolean $record
 *        	是否记录日志
 * @return void
 */
function trace($value = '[leaps]', $label = '', $level = 'DEBUG', $record = false) {
	static $_trace = array ();
	if ('[leaps]' === $value) { // 获取trace信息
		return $_trace;
	} else {
		$info = ($label ? $label . ':' : '') . print_r ( $value, true );
		if ('ERR' == $level && C ( 'config', 'trace_exception' )) { // 抛出异常
			throw_exception ( $info );
		}
		$level = strtoupper ( $level );
		if (! isset ( $_trace [$level] )) {
			$_trace [$level] = array ();
		}
		$_trace [$level] [] = $info;
	}
}

/**
 * 判断验证码是否正确
 *
 * @param string $checkcode
 */
function checkcode($checkcode) {
	Loader::session (); // 加载Session
	if (! empty ( $checkcode ) && $_SESSION ['code'] == strtolower ( $checkcode )) return true;
	return false;
}
function show_trace() {
	if (! IS_AJAX && C ( 'config', 'show_trace' )) {
		$trace_page_tabs = array ('BASE' => '基本','FILE' => '文件','INFO' => '流程','ERR|NOTIC' => '错误','SQL' => 'SQL','DEBUG' => '调试' ); // 页面Trace可定制的选项卡

		// 系统默认显示信息
		$files = get_included_files ();
		$info = array ();
		foreach ( $files as $key => $file ) {
			$info [] = $file . ' ( ' . number_format ( filesize ( $file ) / 1024, 2 ) . ' KB )';
		}
		$trace = array ();
		$base = array ('请求信息' => date ( 'Y-m-d H:i:s', $_SERVER ['REQUEST_TIME'] ) . ' ' . $_SERVER ['SERVER_PROTOCOL'] . ' ' . $_SERVER ['REQUEST_METHOD'] . ' : ' . $_SERVER ['REQUEST_URI'],'运行时间' => show_time (),
				'内存开销' => MEMORY_LIMIT_ON ? number_format ( (memory_get_usage () - START_MEMORY) / 1024, 2 ) . ' kb' : '不支持','查询信息' => N ( 'db_query' ) . ' queries ' . N ( 'db_write' ) . ' writes ','文件加载' => count ( get_included_files () ),
				'缓存信息' => N ( 'cache_read' ) . ' gets ' . N ( 'cache_write' ) . ' writes ','会话信息' => 'SESSION_ID=' . session_id () );
		$debug = trace ();
		foreach ( $trace_page_tabs as $name => $title ) {
			switch (strtoupper ( $name )) {
				case 'BASE' : // 基本信息
					$trace [$title] = $base;
					break;
				case 'FILE' : // 文件信息
					$trace [$title] = $info;
					break;
				default : // 调试信息
					$name = strtoupper ( $name );
					if (strpos ( $name, '|' )) { // 多组信息
						$array = explode ( '|', $name );
						$result = array ();
						foreach ( $array as $name ) {
							$result += isset ( $debug [$name] ) ? $debug [$name] : array ();
						}
						$trace [$title] = $result;
					} else {
						$trace [$title] = isset ( $debug [$name] ) ? $debug [$name] : '';
					}
			}
		}
	}
	include FW_PATH . 'errors' . DIRECTORY_SEPARATOR . 'trace.php';
}

/**
 * 将字符串转换为数组
 *
 * @param string $data
 * @return array
 */
function string2array($data) {
	$array = array ();
	if ($data == '') return $array;
	@eval ( "\$array = $data;" );
	return $array;
}

/**
 * 将数组转换为字符串
 *
 * @param array $data
 * @param bool $isformdata
 * @return string
 *
 */
function array2string($data, $isformdata = 1) {
	if ($data == '') return '';
	if ($isformdata) $data = new_stripslashes ( $data );
	return var_export ( $data, TRUE );
}

/**
 * 表格转数组
 *
 * @param string $table
 *        	表格的html代码
 * @return array 数组
 */
function table2array($table) {
	$table = preg_replace ( "'<table[^>]*?>'si", "", $table );
	$table = preg_replace ( "'<tr[^>]*?>'si", "", $table );
	$table = preg_replace ( "'<td[^>]*?>'si", "", $table );
	$table = str_replace ( "</tr>", "{tr}", $table );
	$table = str_replace ( "</td>", "{td}", $table );
	// 去掉 HTML 标记
	$table = preg_replace ( "'<[/!]*?[^<>]*?>'si", "", $table );
	// 去掉空白字符
	$table = preg_replace ( "'([rn])[s]+'", "", $table );
	$table = str_replace ( " ", "", $table );
	$table = str_replace ( " ", "", $table );
	$table = explode ( '{tr}', $table );
	array_pop ( $table );
	$tb_array = array ();
	foreach ( $table as $key => $tr ) {
		$td = explode ( '{td}', $tr );
		array_pop ( $td );
		$tb_array [] = $td;
	}
	return $tb_array;
}

/**
 * 提取两个字符串之间的值，不包括分隔符
 *
 * @param string $string
 *        	待提取的只付出
 * @param string $start
 *        	开始字符串
 * @param string|null $end
 *        	结束字符串，省略将返回所有的。
 * @return bool string substring between $start and $end or false if either
 *         string is not found
 *
 */
function substr_between($string, $start, $end = null) {
	if (($start_pos = strpos ( $string, $start )) !== false) {
		if ($end) {
			if (($end_pos = strpos ( $string, $end, $start_pos + strlen ( $start ) )) !== false) {
				return substr ( $string, $start_pos + strlen ( $start ), $end_pos - ($start_pos + strlen ( $start )) );
			}
		} else {
			return substr ( $string, $start_pos );
		}
	}
	return false;
}

/**
 * 通过节点路径返回html的某个节点值
 *
 * @param string $html
 *        	待解析的html文档
 * @param string $node
 *        	返回节点参数
 * @return bool string 正常返回结果，失败返回false
 */
function get_data_for_html($html, $xpath) {
	if ($html != '' && $xpath != '') {
		$dom = new DOMDocument ();
		if ($dom->loadHTML ( $html )) {
			$xml = simplexml_import_dom ( $dom );
			$result = $xml->xpath ( $xpath );
			while ( list ( , $xpath ) = each ( $result ) ) {
				return ( array ) $xpath;
			}
		}
	}
	return false;
}

/**
 * 发送电子邮件
 *
 * @param srting $toemail
 *        	要发送到的邮箱多个逗号隔开
 * @param srting $subject
 *        	邮件标题
 * @param srting $message
 *        	邮件内容
 * @param srting $from
 */
function sendmail($toemail, $subject, $message, $from = '') {
	static $mail = null;
	if (null === $mail) $mail = Loader::lib ( 'Mail' );
	return $mail->send ( $toemail, $subject, $message, $from );
}

/**
 * 发送手机短信
 *
 * @param int $mobile
 *        	手机号
 * @param string $content
 *        	内容
 */
function sendsms($mobile, $content) {
	static $sms = null;
	if ($sms === null) $sms = Loader::sms ();
	if ($sms->send ( $mobile, $content )) return true;
	return false;
}

if (! function_exists ( 'json_encode' )) {
	function json_encode($value, $options = 0) {
		static $json = null;
		if ($json === null) {
			$json = new Services_JSON ();
		}
		return $json->encode ( $value );
	}
}

if (! function_exists ( 'json_decode' )) {
	function json_decode($value) {
		static $json = null;
		if ($json === null) {
			$json = new Services_JSON ();
		}
		return $json->decode ( $value );
	}
}

/**
 * 中文字符转拼音
 *
 * @param string $str
 * @param string $utf8
 */
function string_to_pinyin($str, $utf8 = true) {
	static $obj = null;
	if ($obj === null) $obj = new Pinyin ();
	return $obj->output ( $str, $utf8 );
}

function uuid($prefix = '') {
	$chars = md5 ( uniqid ( mt_rand (), true ) );
	$uuid = substr ( $chars, 0, 8 ) . '-';
	$uuid .= substr ( $chars, 8, 4 ) . '-';
	$uuid .= substr ( $chars, 12, 4 ) . '-';
	$uuid .= substr ( $chars, 16, 4 ) . '-';
	$uuid .= substr ( $chars, 20, 12 );
	return $prefix . $uuid;
}