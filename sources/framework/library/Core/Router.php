<?php
/**
 * Router Class
 * 解析HTTP和Cli方式的路由访问
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Router.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Router {
	/**
	 *
	 * @var core_Router
	 */
	protected static $instance = null;

	/**
	 * 路由配置
	 *
	 * @var array
	 */
	protected $_config = array ();

	protected $params = array ();

	protected $_sep = '_array_';

	public static $app = null;
	public static $controller = null;
	public static $action = null;

	/**
	 * 获取自身实例
	 */
	public static function &get_instance($app = null, $controller = null, $action = null) {
		if(!is_null($app)){
			self::$app = $app;
			if(!is_null($controller)){
				self::$controller = $controller;
				if(!is_null($action)) self::$action = $action;
			}
		}
		if (null === self::$instance) {
			self::$instance = new self ();
		}
		return self::$instance;
	}

	public function __construct() {
		if (IS_CLI or defined ( 'STDIN' )) { // 加载命令行下的路由
			$this->_config = C ( 'router', 'cli' );
			$this->params = array (
					'action' => array (
							'map' => 2
					),
					'controller' => array (
							'map' => 1
					)
			);
		} else if (C ( 'router', SITE_HOST )) { // 加载基于域名的URL 路由配置
			$this->_config = C ( 'router', SITE_HOST );
			define ( 'SUB_DOMAIN', strtolower ( substr ( SITE_HOST, 0, strpos ( SITE_HOST, '.' ) ) ) ); // 二级域名定义
			$this->params = array (
					'action' => array (
							'map' => 2
					),
					'controller' => array (
							'map' => 1
					)
			);
		} else { // 使用默认路由配置
			$this->_config = C ( 'router', 'default' );
			$this->params = array (
					'action' => array (
							'map' => 3
					),
					'controller' => array (
							'map' => 2
					),
					'app' => array (
							'map' => 1
					)
			);
		}
		$this->match (); // 解析路由

		// 合并配置文件到变量
		if (isset ( $this->_config ['data'] ['POST'] ) && is_array ( $this->_config ['data'] ['POST'] )) {
			foreach ( $this->_config ['data'] ['POST'] as $_key => $_value ) {
				if (! isset ( $_POST [$_key] )) $_POST [$_key] = $_value;
			}
		}
		if (isset ( $this->_config ['data'] ['GET'] ) && is_array ( $this->_config ['data'] ['GET'] )) {
			foreach ( $this->_config ['data'] ['GET'] as $_key => $_value ) {
				if (! isset ( $_GET [$_key] )) $_GET [$_key] = $_value;
			}
		}
		if (isset ( $_GET ['page'] )) $_GET ['page'] = max ( intval ( $_GET ['page'] ), 1 );
		$this->set_params ();
		return;
	}

	/**
	 * 路由解析
	 *
	 * 匹配这个patten时，将试图去解析app、controller和action值，并解析二级域名。
	 */
	public function match() {
		if (IS_CLI || defined ( 'STDIN' )) {
			$_pathinfo = Core_Request::parse_cli_args ();
			if (! $_pathinfo || ! preg_match_all ( '/^(\/\w+)?(\/\w+)?.*$/i', trim ( $_pathinfo ), $matches )) return null;
		} else {
			$full_url = Core_Request::get_host_info () . Core_Request::get_request_uri ();
			$_pathinfo = trim ( str_replace ( Core_Request::get_base_url (), '', $full_url ), '/' );
			if (! $_pathinfo || ! preg_match_all ( '/^http[s]?:\/\/[^\/]+(\/\w+)?(\/\w+)?(\/\w+)?.*$/i', trim ( $_pathinfo, '/' ), $matches ) || strpos ( $_pathinfo, '.php' ) !== false || strpos ( $_pathinfo, Core_Request::get_host_info () . '/?' ) !== false) return null;
		}
		list ( , $_args ) = explode ( '?', $_pathinfo . '?', 2 );
		$_args = trim ( $_args, '?' );
		$_args = $this->url_to_args ( $_args, true, '&=' );
		$params = array ();
		foreach ( $this->params as $_n => $_p ) {
			if (isset ( $_p ['map'] ) && isset ( $matches [$_p ['map']] [0] )) {
				$_value = $matches [$_p ['map']] [0];
			} else {
				$_value = isset ( $_p ['default'] ) ? $_p ['default'] : '';
			}
			$this->params [$_n] ['value'] = $params [$_n] = trim ( $_value, '-/' );
			unset ( $_args [$_n] ); // 去掉参数中的app,controller,action
		}
		$_args && $params = array_merge ( $params, $_args );
		if (defined ( 'SUB_DOMAIN' )) $params ['app'] = isset ( $this->_config ['application'] ) ? $this->_config ['application'] : SUB_DOMAIN; // 设定应用名称;
		$_GET = array_merge ( $_GET, $params );
		return;
	}

	/**
	 * 将路由解析到的url参数信息保存到系统变量中
	 *
	 * @param string $params
	 * @return void
	 */
	protected function set_params() {
		$action = Core_Request::get_request ( 'action' ) ? Core_Request::get_request ( 'action' ) : (!is_null(self::$action) ? self::$action : $this->_config ['action']);
		$action && define ( 'ACTION', $action ); // 事件名称
		$controller = Core_Request::get_request ( 'controller' ) ? Core_Request::get_request ( 'controller' )  : (!is_null(self::$controller) ? self::$controller : $this->_config ['controller']);
		$controller && define ( 'CONTROLLER', ucfirst ( $controller ) ); // 控制器名称
		if (IS_CLI) return;
		$app = Core_Request::get_request ( 'app' ) ? Core_Request::get_request ( 'app' ) : (!is_null(self::$app) ? self::$app : $this->_config ['application']);
		$app && define ( 'APP', $app); // 应用名称
		return;
	}

	/**
	 * url字符串转化为数组格式
	 *
	 * 效果同'argstourl'相反
	 *
	 * @param string $url
	 * @param boolean $decode 是否需要进行url反编码处理
	 * @param string $separator url的分隔符
	 * @return array
	 */
	public function url_to_args($url, $decode = true, $separator = '&=') {
		! $separator && $separator = '&=';
		false !== ($pos = strpos ( $url, '?' )) && $url = substr ( $url, $pos + 1 );
		$_sep1 = substr ( $separator, 0, 1 );
		if ($_sep2 = substr ( $separator, 1, 1 )) {
			$__sep1 = preg_quote ( $_sep1, '/' );
			$url = preg_replace ( '/' . $__sep1 . '[\w+]' . $__sep1 . '/i', $_sep1, $url );
			$url = str_replace ( $_sep2, $_sep1, $url );
		}
		$url = explode ( $_sep1, trim ( $url, $_sep1 ) . $_sep1 );
		$args = array ();
		for($i = 0; $i < count ( $url ); $i = $i + 2) {
			if (! isset ( $url [$i] ) || ! isset ( $url [$i + 1] )) continue;
			$_v = $decode ? urldecode ( $url [$i + 1] ) : $url [$i + 1];
			$_k = $url [$i];
			if (strpos ( $_k, $this->_sep ) === 0) {
				$_k = substr ( $_k, strlen ( $this->_sep ) );
				$_v = unserialize ( $_v );
			}
			$args [$_k] = $_v;
		}
		return $args;
	}
}