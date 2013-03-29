<?php
/**
 * 请求处理类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Request.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Request {

	/**
	 * 主机信息，包含协议信息，主机名，访问端口信息
	 *
	 * @var string
	 */
	private static $_host_info = null;

	/**
	 * 基础URL
	 *
	 * @var string
	 */
	private static $_base_url = null;

	/**
	 * 请求的资源标识符
	 *
	 * @var string
	 */
	private static $_request_uri = null;

	/**
	 * 获取主机名
	 */
	public static function get_host() {
		$host = self::get_env ( 'HTTP_X_FORWARDED_HOST' );
		return $host ? $host : self::get_env ( 'HTTP_HOST' );
	}

	/**
	 * 获取基础URL
	 *
	 * 这里是去除了脚本文件以及访问参数信息的URL地址信息:
	 *
	 * <pre>Example:
	 * 请求: http://www.yuncms.net/example/index.php?a=test
	 * 1]如果: $absolute = false：
	 * 返回： example
	 * 2]如果: $absolute = true:
	 * 返回： http://www.yuncms.net/example
	 * </pre>
	 *
	 * @param boolean $absolute 是否返回主机信息
	 * @return string
	 */
	public static function get_base_url($absolute = false) {
		if (! is_null ( self::$_base_url )) return self::$_base_url;
		self::$_base_url = rtrim ( dirname ( self::get_script_url () ), '\\/.' );
		return $absolute ? self::get_host_info () . self::$_base_url : self::$_base_url;
	}

	/**
	 * 获得主机信息，包含协议信息，主机名，访问端口信息
	 *
	 * <pre>Example:
	 * 请求: http://www.yuncms.net/example/index.php?a=test
	 * 返回：http://www.yuncms.net/
	 * </pre>
	 *
	 * @return string
	 */
	public static function get_host_info() {
		if (! is_null ( self::$_host_info )) return self::$_host_info;
		$base = self::is_ssl () ? 'https://' : 'http://';
		$base .= self::get_host ();
		self::$_host_info = $base;
		return $base;
	}

	/**
	 * 获取访客浏览器编码
	 */
	public static function get_charset() {
		return $_SERVER ['HTTP_ACCEPT_CHARSET'];
	}

	/**
	 * 语言
	 *
	 * @var string
	 */
	private static $_language = null;

	/**
	 * 返回客户端程序期望服务器返回哪个国家的语言文档
	 */
	public static function get_language() {
		if (! is_null ( self::$_language )) return self::$_language;
		self::$_language = self::get_env ( 'HTTP_ACCEPT_LANGUAGE' );
		return self::$_language;
	}

	/**
	 * 是否SSL
	 *
	 * @return boolean
	 */
	public static function is_ssl() {
		return (strtolower ( self::get_env ( 'HTTPS' ) ) === 'on' || strtolower ( self::get_env ( 'HTTP_SSL_HTTPS' ) ) === 'on' || self::get_env ( 'HTTP_X_FORWARDED_PROTO' ) == 'https');
	}

	/**
	 * 访客IP
	 *
	 * @var string
	 */
	private static $_client_ip;

	/**
	 * 当前执行脚本的绝对路径
	 *
	 * @var string
	 */
	private static $_script_url;

	/**
	 * 返回执行脚本名称
	 *
	 * <pre>Example:
	 * 请求: http://www.yuncms.net/example/index.php?a=test
	 * 返回: index.php
	 * </pre>
	 *
	 * @return string
	 */
	public static function get_script() {
		return basename ( self::get_script_url () );
	}

	public static function get_url(){
		return self::get_host_info().self::get_request_uri();
	}

	/**
	 * 返回当前执行脚本的绝对路径
	 *
	 * <pre>Example:
	 * 请求: http://www.yuncms.net/example/index.php?a=test
	 * 返回: /example/index.php
	 * </pre>
	 *
	 * @return string 当前执行脚本的绝对路径
	 */
	public static function get_script_url() {
		if (! is_null ( self::$_script_url )) return self::$_script_url;
		$scriptName = basename ( $_SERVER ['SCRIPT_FILENAME'] );
		if (basename ( $_SERVER ['SCRIPT_NAME'] ) === $scriptName) {
			self::$_script_url = $_SERVER ['SCRIPT_NAME'];
		} else if (basename ( $_SERVER ['PHP_SELF'] ) === $scriptName) {
			self::$_script_url = $_SERVER ['PHP_SELF'];
		} else if (isset ( $_SERVER ['ORIG_SCRIPT_NAME'] ) && basename ( $_SERVER ['ORIG_SCRIPT_NAME'] ) === $scriptName) {
			self::$_script_url = $_SERVER ['ORIG_SCRIPT_NAME'];
		} else if (($pos = strpos ( $_SERVER ['PHP_SELF'], '/' . $scriptName )) !== false) {
			self::$_script_url = substr ( $_SERVER ['SCRIPT_NAME'], 0, $pos ) . '/' . $scriptName;
		} else if (isset ( $_SERVER ['DOCUMENT_ROOT'] ) && strpos ( $_SERVER ['SCRIPT_FILENAME'], $_SERVER ['DOCUMENT_ROOT'] ) === 0) {
			self::$_script_url = str_replace ( '\\', '/', str_replace ( $_SERVER ['DOCUMENT_ROOT'], '', $_SERVER ['SCRIPT_FILENAME'] ) );
			self::$_script_url != '/' && self::$_script_url = '/' . self::$_script_url;
		} else {
			throw new Exception ( 'Request tainting, Please try again.' );
		}
		return htmlspecialchars ( self::$_script_url );
	}

	/**
	 * 获取访客IP
	 */
	public static function get_client_ip() {
		if (! is_null ( self::$_client_ip )) return self::$_client_ip;
		if (getenv ( 'HTTP_CLIENT_IP' ) && strcasecmp ( getenv ( 'HTTP_CLIENT_IP' ), 'unknown' )) {
			$ip = getenv ( 'HTTP_CLIENT_IP' );
		} elseif (getenv ( 'HTTP_X_FORWARDED_FOR' ) && strcasecmp ( getenv ( 'HTTP_X_FORWARDED_FOR' ), 'unknown' )) {
			$ip = getenv ( 'HTTP_X_FORWARDED_FOR' );
		} elseif (getenv ( 'REMOTE_ADDR' ) && strcasecmp ( getenv ( 'REMOTE_ADDR' ), 'unknown' )) {
			$ip = getenv ( 'REMOTE_ADDR' );
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], 'unknown' )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		}
		self::$_client_ip = (isset ( $ip ) && preg_match ( "/[\d\.]{7,15}/", $ip, $matches )) ? $matches [0] : '0.0.0.0';
		return self::$_client_ip;
	}

	/**
	 * 获取客户端浏览器信息
	 *
	 * @access public
	 * @return string
	 */
	public function get_user_browser() {
		$user_agent_info = htmlspecialchars ( $_SERVER ['HTTP_USER_AGENT'] );
		if (strpos ( $user_agent_info, 'MSIE 9.0' )) {
			return 'IE9';
		} else if (strpos ( $user_agent_info, 'MSIE 8.0' )) {
			return 'IE8';
		} else if (strpos ( $user_agent_info, 'MSIE 7.0' )) {
			return 'IE7';
		} else if (strpos ( $user_agent_info, 'MSIE 6.0' )) {
			return 'IE6';
		} else if (strpos ( $user_agent_info, 'Firefox' )) {
			return 'Firfox';
		} else if (strpos ( $user_agent_info, 'Chrome' )) {
			return 'Chrome';
		} else if (strpos ( $user_agent_info, 'Opera' )) {
			return 'Opera';
		} else if (strpos ( $user_agent_info, 'Safari' )) {
			return 'Safari';
		} else if (strpos ( $user_agent_info, 'Elinks' )) {
			return 'Elinks';
		} else if (strpos ( $user_agent_info, 'OmniWeb' )) {
			return 'OmniWeb';
		} else if (strpos ( $user_agent_info, 'Links' )) {
			return 'Links';
		} else if (strpos ( $user_agent_info, 'Lynx' )) {
			return 'Lynx';
		} else if (strpos ( $user_agent_info, 'Arora' )) {
			return 'Arora';
		} else if (strpos ( $user_agent_info, 'Epiphany' )) {
			return 'Epiphany';
		} else if (strpos ( $user_agent_info, 'Konqueror' )) {
			return 'Konqueror';
		} else if (strpos ( $user_agent_info, 'EudoraWeb' )) {
			return 'EudoraWeb';
		} else if (strpos ( $user_agent_info, 'Minimo' )) {
			return 'Minimo';
		} else if (strpos ( $user_agent_info, 'NetFront' )) {
			return 'NetFront';
		} else if (strpos ( $user_agent_info, 'POLARIS' )) {
			return 'Polaris';
		} else if (strpos ( $user_agent_info, 'BlackBerry' )) {
			return 'BlackBerry';
		} else if (strpos ( $user_agent_info, 'Nokia' )) {
			return 'Nokia';
		} else {
			return 'Others';
		}
	}

	/**
	 * 获取客户端操作系统信息
	 *
	 * @access public
	 * @return string
	 */
	public function get_user_os() {
		$user_agent_info = htmlspecialchars ( $_SERVER ['HTTP_USER_AGENT'] );
		if (strpos ( $user_agent_info, 'Windows NT 6.1' )) {
			return 'Windows 7';
		} else if (strpos ( $user_agent_info, 'Windows NT 6.0' )) {
			return 'Windows Vista';
		} else if (strpos ( $user_agent_info, 'Windows NT 5.2' )) {
			return 'Windows 2003';
		} else if (strpos ( $user_agent_info, 'Windows NT 5.1' )) {
			return 'Windows XP';
		} else if (strpos ( $user_agent_info, 'Windows NT 5.0' )) {
			return 'Windows 2000';
		} else if (strpos ( $user_agent_info, 'Windows ME' )) {
			return 'Windows ME';
		} else if (strpos ( $user_agent_info, 'PPC Mac OS X' )) {
			return 'OS X PPC';
		} else if (strpos ( $user_agent_info, 'Intel Mac OS X' )) {
			return 'OS X Intel';
		} else if (strpos ( $user_agent_info, 'Win98' )) {
			return 'Windows 98';
		} else if (strpos ( $user_agent_info, 'Win95' )) {
			return 'Windows 95';
		} else if (strpos ( $user_agent_info, 'WinNT4.0' )) {
			return 'Windows NT4.0';
		} else if (strpos ( $user_agent_info, 'Mac OS X Mach-O' )) {
			return 'OS X Mach';
		} else if (strpos ( $user_agent_info, 'Ubuntu' )) {
			return 'Ubuntu';
		} else if (strpos ( $user_agent_info, 'Debian' )) {
			return 'Debian';
		} else if (strpos ( $user_agent_info, 'AppleWebKit' )) {
			return 'WebKit';
		} else if (strpos ( $user_agent_info, 'Mint/8' )) {
			return 'Mint 8';
		} else if (strpos ( $user_agent_info, 'Minefield' )) {
			return 'Minefield Alpha';
		} else if (strpos ( $user_agent_info, 'gentoo' )) {
			return 'Gentoo';
		} else if (strpos ( $user_agent_info, 'Kubuntu' )) {
			return 'Kubuntu';
		} else if (strpos ( $user_agent_info, 'Slackware/13.0' )) {
			return 'Slackware 13';
		} else if (strpos ( $user_agent_info, 'Fedora' )) {
			return 'Fedora';
		} else if (strpos ( $user_agent_info, 'FreeBSD' )) {
			return 'FreeBSD';
		} else if (strpos ( $user_agent_info, 'SunOS' )) {
			return 'SunOS';
		} else if (strpos ( $user_agent_info, 'OpenBSD' )) {
			return 'OpenBSD';
		} else if (strpos ( $user_agent_info, 'NetBSD' )) {
			return 'NetBSD';
		} else if (strpos ( $user_agent_info, 'DragonFly' )) {
			return 'DragonFly';
		} else if (strpos ( $user_agent_info, 'IRIX' )) {
			return 'IRIX';
		} else if (strpos ( $user_agent_info, 'Windows CE' )) {
			return 'Windows CE';
		} else if (strpos ( $user_agent_info, 'PalmOS' )) {
			return 'PalmOS';
		} else if (strpos ( $user_agent_info, 'Linux' )) {
			return 'Linux';
		} else if (strpos ( $user_agent_info, 'DragonFly' )) {
			return 'DragonFly';
		} else if (strpos ( $user_agent_info, 'Android' )) {
			return 'Android';
		} else if (strpos ( $user_agent_info, 'Mac OS X' )) {
			return 'Mac OS X';
		} else if (strpos ( $user_agent_info, 'iPhone' )) {
			return 'iPhone OS';
		} else if (strpos ( $user_agent_info, 'Symbian OS' )) {
			return 'Symbian';
		} else if (strpos ( $user_agent_info, 'Symbian OS' )) {
			return 'Symbian';
		} else if (strpos ( $user_agent_info, 'SymbianOS' )) {
			return 'SymbianOS';
		} else if (strpos ( $user_agent_info, 'webOS' )) {
			return 'webOS';
		} else if (strpos ( $user_agent_info, 'PalmSource' )) {
			return 'PalmSource';
		} else {
			return 'Others';
		}
	}

	/**
	 * 获取来路URL
	 */
	public static function get_referer() {
		return self::get_env ( 'HTTP_REFERER' );
	}

	/**
	 * 获取系统变量
	 *
	 * @param string $key
	 * @return Ambigous <unknown, boolean>
	 */
	public static function get_env($key) {
		return isset ( $_SERVER [$key] ) ? $_SERVER [$key] : (isset ( $_ENV [$key] ) ? $_ENV [$key] : false);
	}

	/**
	 * 是否是Ajax
	 */
	public static function is_ajax() {
		return (self::get_env ( 'HTTP_X_REQUESTED_WITH' ) == 'XMLHttpRequest');
	}
	/**
	 * 是否是POST
	 */
	public static function is_post() {
		return $_SERVER ['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * 是否是GET
	 */
	public static function is_get() {
		return $_SERVER ['REQUEST_METHOD'] === 'GET';
	}

	/**
	 * 是否是PUT
	 */
	public static function is_put() {
		return $_SERVER ['REQUEST_METHOD'] === 'PUT';
	}

	/**
	 * 是否是DELETE
	 */
	public static function is_delete() {
		return $_SERVER ['REQUEST_METHOD'] === 'DELETE';
	}

	/**
	 * 判断是否是IE浏览器
	 *
	 * @return boolean
	 */
	public static function is_ie() {
		return strpos ( self::get_env ( 'HTTP_USER_AGENT' ), 'MSIE' ) ? true : false;
	}

	/**
	 * 获取请求方式
	 */
	public static function get_method() {
		return $_SERVER ['REQUEST_METHOD'];
	}

	/**
	 * 初始化请求的资源标识符
	 *
	 * 这里的uri是去除协议名、主机名的
	 * <pre>Example:
	 * 请求： http://www.yuncms.net/example/index.php?a=test
	 * 则返回: /example/index.php?a=test
	 * </pre>
	 *
	 * @return string
	 */
	public static function get_request_uri() {
		if (! is_null ( self::$_request_uri )) return self::$_request_uri;
		if (isset ( $_SERVER ['HTTP_X_REWRITE_URL'] )) {
			$uri = $_SERVER ['HTTP_X_REWRITE_URL'];
		} elseif (isset ( $_SERVER ['REQUEST_URI'] )) {
			$uri = $_SERVER ['REQUEST_URI'];
		} elseif (isset ( $_SERVER ['ORIG_PATH_INFO'] )) {
			$uri = $_SERVER ['ORIG_PATH_INFO'];
			if (! empty ( $_SERVER ['QUERY_STRING'] )) {
				$uri .= '?' . $_SERVER ['QUERY_STRING'];
			}
		} else {
			$uri = '';
		}
		self::$_request_uri = $uri;
		return $uri;
	}

	/**
	 * 获得用户请求的数据
	 *
	 * 返回$_GET,$_POST的值,未设置则返回$defaultValue
	 *
	 * @param string $key 获取的参数name,默认为null将获得$_GET和$_POST两个数组的所有值
	 * @param mixed $defaultValue 当获取值失败的时候返回缺省值,默认值为null
	 * @return mixed
	 */
	public function get_request($key = null, $default = null) {
		if (is_null ( $key )) return array_merge ( $_POST, $_GET );
		if (isset ( $_GET [$key] )) return $_GET [$key];
		if (isset ( $_POST [$key] )) return $_POST [$key];
		return $default;
	}

	/**
	 * 解析cli参数
	 *
	 * @return string
	 */
	public static function parse_cli_args() {
		$args = array_slice ( $_SERVER ['argv'], 1 );
		return $args ? '/' . implode ( '/', $args ) : '';
	}
}