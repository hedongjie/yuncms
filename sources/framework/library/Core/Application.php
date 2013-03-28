<?php
/**
 * 应用程序创建类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Application.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Application {

	protected static $instance = null;

	public function __construct() {
		$this->_init_env ();
		$this->_init_input ();
	}

	/**
	 * 获取自身实例
	 */
	public static function &get_instance() {
		if (null === self::$instance) {
			self::$instance = new self ();
		}
		return self::$instance;
	}

	private function _init_env() {
		if (version_compare ( PHP_VERSION, '5.2.0', '<' )) die ( 'require PHP > 5.2.0 !' );
		if (version_compare ( PHP_VERSION, '5.4.0', '<' )) {
			@ini_set ( 'magic_quotes_runtime', 0 );
			define ( 'MAGIC_QUOTES_GPC', get_magic_quotes_gpc () ? true : false );
		} else {
			define ( 'MAGIC_QUOTES_GPC', false );
		}
		if (! defined ( 'CORE_FUNCTION' ) && ! @include (FW_PATH . 'library/Core/Function.php')) exit ( 'Function.php is missing' );
		if (function_exists ( "set_time_limit" ) == true and @ini_get ( "safe_mode" ) == 0) {
			@set_time_limit ( 300 );
		}
		if (function_exists ( 'ini_get' )) {
			$memorylimit = @ini_get ( 'memory_limit' );
			if ($memorylimit && format_byte ( $memorylimit ) < 33554432 && function_exists ( 'ini_set' )) @ini_set ( 'memory_limit', '128m' );
		}
		if (function_exists ( 'date_default_timezone_set' )) {
			@date_default_timezone_set ( C ( 'config', 'timezone', 'Etc/GMT-8' ) ); // 默认Etc/GMT-8
		}
		define ( 'TIME', time () );
		define ( 'CHARSET', C ( 'config', 'charset', 'UTF-8' ) );
		if (C ( 'config', 'debug', false )) {
			define ( 'IS_DEBUG', true );
			error_reporting ( E_ALL );
		} else {
			define ( 'IS_DEBUG', false );
			error_reporting ( 0 );
		}
		set_error_handler ( 'Core::_error_handle' );
		register_shutdown_function ( 'Core::_shutdown_handle' );
		set_exception_handler ( 'Core::_exception_handle' );
		// 访客IP
		define ( 'IP', Core_Request::get_client_ip () );
		// 前执行脚本的绝对路径
		define ( 'PHP_FILE', htmlspecialchars ( Core_Request::get_script_url () ) );
		/* 脚本名称 */
		define ( 'SCRIPT_NAME', Core_Request::get_script () );
		// 所在目录
		define ( 'WEB_PATH', substr ( PHP_FILE, 0, strrpos ( PHP_FILE, '/' ) ) . '/' );
		if (! IS_CLI) {
			/* 协议 */
			define ( 'SITE_PROTOCOL', Core_Request::is_ssl () ? 'https://' : 'http://' );
			/* 主机名 */
			define ( 'SITE_HOST', Core_Request::get_host () );
			/* 基础URL */
			define ( 'SITE_URL', htmlspecialchars ( Core_Request::get_base_url ( true ) ) . '/' );
			/* 设置来源 */
			define ( 'HTTP_REFERER', Core_Request::get_referer () );
		}
		/* 临时文件存储目录，临时文件的生存周期等同于PHP请求，也就是当该PHP请求完成执行时，所有写入TmpFS的临时文件都会被销毁 */
		define ( 'TMP_PATH', '' );
		/* 开始时间 */
		define ( 'START_TIME', microtime ( true ) );
		/* 开始占用内存 */
		define ( 'MEMORY_LIMIT_ON', function_exists ( 'memory_get_usage' ) );
		if (MEMORY_LIMIT_ON) define ( 'START_MEMORY', memory_get_usage () );
	}

	/**
	 * 初始化缓存流 暂不使用
	 */
	public static function _init_cache() {
		// 注册缓存流
		if (! in_array ( "cache", stream_get_wrappers () )) {
			stream_wrapper_register ( "cache", "Cache_Wrapper" );
		}
	}

	/**
	 * 处理用户输入
	 */
	private function _init_input() {
		if (! IS_CLI) { // 非命令行模式
			define ( 'REQUEST_METHOD', Core_Request::get_method () );
			define ( 'IS_GET', Core_Request::is_get () );
			define ( 'IS_POST', Core_Request::is_post () );
			define ( 'IS_PUT', Core_Request::is_put () );
			define ( 'IS_DELETE', Core_Request::is_delete () );
			define ( 'IS_AJAX', Core_Request::is_ajax () );
			define ( 'IS_SAE', function_exists ( 'saeAutoLoader' ) ? true : false );
			// define ( 'IS_BAE', true );
			// define ( 'IS_SDAE', true );
			// define ( 'IS_ALIAE', true );
			@header ( 'Content-Type: text/html; charset=' . CHARSET );
			@header ( 'X-Powered-By: PHP/' . PHP_VERSION . ' Leaps/' . LEAPS_VERSION );
			// 页面压缩输出支持
			if (C ( 'config', 'gzip', true ) && function_exists ( 'ob_gzhandler' )) {
				ob_start ( 'ob_gzhandler' );
			} else {
				ob_start ();
			}
		}
		Core_Filter::input ();
	}

	public static function execute($app = null, $controller = null, $action = null) {
		Core_Router::get_instance ( $app, $controller, $action );
		$app = ! is_null ( $app ) ? trim ( $app ) : APP;
		$controller = ! is_null ( $controller ) ? trim ( $controller ) : CONTROLLER;
		$action = ! is_null ( $action ) ? trim ( $action ) : ACTION;
		$controller = Loader::controller ( $controller, $app );
		if (method_exists ( $controller, $action ) && ! preg_match ( '/^[_]/i', $action )) {
			call_user_func ( array ($controller,$action ) );
		} else {
			throw_exception ( 'You are visiting the action is to protect the private action' );
		}
	}

	public static function execute_api($controller = null, $action = null) {
		Core_Router::get_instance ( null, $controller, $action );
		$controller = ! is_null ( $controller ) ? trim ( $controller ) : CONTROLLER;
		$action = ! is_null ( $action ) ? trim ( $action ) : ACTION;
		$classname = $controller . 'Controller';
		import ( $classname, SOURCE_PATH . 'api' . DIRECTORY_SEPARATOR );
		if (class_exists ( $classname, false )) {
			$controller_object = new $classname ();
		} else {
			throw_exception ( 'Unable to create instance for ' . $classname . ' , class is not exist.' );
		}
		if (method_exists ( $controller_object, $action ) && ! preg_match ( '/^[_]/i', $action )) {
			call_user_func ( array ($controller_object,$action ) );
		} else {
			throw_exception ( 'You are visiting the action is to protect the private action' );
		}
	}

	public static function execute_cli($controller = null, $action = null) {
		Core_Router::get_instance ( null, $controller, $action );
		$controller = ! is_null ( $controller ) ? trim ( $controller ) : CONTROLLER;
		$action = ! is_null ( $action ) ? trim ( $action ) : ACTION;
		$classname = $controller . 'Controller';
		import ( $classname, SOURCE_PATH . 'cli' . DIRECTORY_SEPARATOR );
		if (class_exists ( $classname, false )) {
			$controller_object = new $classname ();
		} else {
			throw_exception ( 'Unable to create instance for ' . $classname . ' , class is not exist.' );
		}
		if (method_exists ( $controller_object, $action ) && ! preg_match ( '/^[_]/i', $action )) {
			call_user_func ( array ($controller_object,$action ) );
		} else {
			throw_exception ( 'You are visiting the action is to protect the private action' );
		}
		if (C ( 'config', 'show_time' )) echo show_time ();
	}

}