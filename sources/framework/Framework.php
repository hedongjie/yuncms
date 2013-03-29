<?php
/**
 * 核心初始化
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Framework.php 2 2013-01-14 07:14:05Z xutongle $
 */
// Leaps Version
define ( 'LEAPS_VERSION', '1.0.1' );
// Leaps Release
define ( 'LEAPS_RELEASE', '20120216' );
define ( 'IS_CGI', substr ( PHP_SAPI, 0, 3 ) == 'cgi' ? true : false );
define ( 'IS_WIN', strstr ( PHP_OS, 'WIN' ) ? true : false );
define ( 'IS_CLI', PHP_SAPI == 'cli' ? true : false );
// Name of the "Framework folder"
define ( 'FW_PATH', dirname ( __FILE__ ) . DIRECTORY_SEPARATOR );
// Path to the system folder
defined ( 'BASE_PATH' ) or define ( 'BASE_PATH', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) . DIRECTORY_SEPARATOR );
// The path to the "data" folder
defined ( 'DATA_PATH' ) or define ( 'DATA_PATH', BASE_PATH . 'data' . DIRECTORY_SEPARATOR );
// The path to the "source" folder
defined ( 'SOURCE_PATH' ) or define ( 'SOURCE_PATH', BASE_PATH . 'sources' . DIRECTORY_SEPARATOR );
// The path to the "apps" folder
defined ( 'APPS_PATH' ) or define ( 'APPS_PATH', SOURCE_PATH . 'apps' . DIRECTORY_SEPARATOR );
if (function_exists ( 'spl_autoload_register' )) {
	spl_autoload_register ( array ('Core','autoload' ) );
} else {
	function __autoload($class) {
		return Core::autoload ( $class );
	}
}
Core::init ();
class Core {

	/**
	 * 常规入口
	 *
	 * @param string $app
	 * @param string $controller
	 * @param string $action
	 */
	public static function run($app = null, $controller = null, $action = null) {
		return Core_Application::execute ();
	}

	/**
	 * API入口点
	 *
	 * @param string $controller 控制器
	 * @param string $action 方法
	 */
	public static function runapi($controller = null, $action = null) {
		return Core_Application::execute_api ( $controller, $action );
	}

	/**
	 * 命令行入口点
	 *
	 * @param string $controller 控制器
	 * @param string $action 方法
	 */
	public static function runcli($controller = null, $action = null) {
		return Core_Application::execute_cli ( $controller, $action );
	}

	/**
	 * 系统初始化
	 */
	public static function init() {
		Core_Application::get_instance ();
	}

	/**
	 * 获取debug对象
	 * 可安全用于生产环境，在生产环境下将忽略所有debug信息
	 *
	 * @return Debug
	 */
	public static function debug() {
		static $debug = null;
		if (null === $debug) {
			if (! IS_CLI && IS_DEBUG && class_exists ( 'Core_Debug', true )) {
				$debug = Core_Debug::instance ();
			} else {
				$debug = new Core_NoDebug ();
			}
		}
		return $debug;
	}

	/**
	 * 获取Log对象
	 *
	 * @return log
	 */
	public static function log() {
		static $log = null;
		if (null === $log) $log = Log::get_instance ();
		return $log;
	}

	/**
	 * 自动装入
	 *
	 * 如果类名按照Core/Core_BASE 这种方式命名可自动加载
	 *
	 * @param string $class
	 */
	public static function autoload($class) {
		if (strpos ( $class, '_' ) !== false) {
			$file = str_replace ( '_', '.', $class );
		} else {
			$file = $class;
		}
		try {
			self::import ( $file, FW_PATH . 'library' . DIRECTORY_SEPARATOR );
		} catch ( Exception $exc ) {
			Helper::show_error ( $exc->getMessage () );
		}
	}

	/**
	 * 载入文件
	 *
	 * @param string $name 文件名或带路径的文件名
	 * @param string $folder 文件夹默认为空
	 * @throws Exception
	 * @return boolean
	 */
	public static function import($filepath, $folder = '') {
		if (! $filepath) return;
		static $_imports = array ();
		if (isset ( $_imports [$filepath] )) return $_imports [$filepath];
		if (strpos ( $filepath, ':' ) !== false) { // 如果是：分割则导入应用下的文件
			list ( $app, $filename ) = explode ( ':', $filepath );
			$base = ! empty ( $folder ) ? $folder : APPS_PATH . $app . DIRECTORY_SEPARATOR;
		} else {
			$base = ! empty ( $folder ) ? $folder : FW_PATH;
			$filename = $filepath;
		}
		// 如果是.分割则组合路径
		if (strpos ( $filename, '.' ) !== false) {
			$path = $base . str_replace ( '.', DIRECTORY_SEPARATOR, $filename ) . '.php';
		} else {
			$path = $base . $filename . '.php';
		}
		if (self::file_exists ( $path ) && is_file ( $path )) {
			$_imports [$filepath] = include $path;
		} else {
			throw_exception ( 'Unable to load the file ' . $path . ' , file is not exist.' );
		}
		return $_imports [$filepath];
	}

	/**
	 * 区分大小写的文件存在判断
	 *
	 * @param string $filename 文件地址
	 * @return boolean
	 */
	public static function file_exists($filename) {
		if (is_file ( $filename )) {
			if (IS_WIN) {
				if (basename ( realpath ( $filename ) ) != basename ( $filename )) return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * 自定义错误处理
	 *
	 * @param int $errno 错误类型
	 * @param string $errstr 错误信息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行数
	 * @return void
	 */
	public static function _error_handle($errno, $errstr, $errfile, $errline) {
		if ($errno == E_STRICT) return; // 忽略运行时
		if (($errno & error_reporting ()) == $errno && $errno != E_ERROR) { // 致命错误直接写日志
			restore_error_handler ();
			restore_exception_handler ();
			$trace = debug_backtrace ();
			unset ( $trace [0] ["function"], $trace [0] ["args"] );
			if (IS_CLI) {
				printf ( Helper::get_errorname ( $errno ) . ': ' . $errstr . ' in ' . $errfile . ' on line ' . $errline );
			} else {
				Core::debug ()->error ( Helper::get_errorname ( $errno ) . ': ' . $errstr . ' in ' . $errfile . ' on line ' . $errline );
				if (! IS_AJAX) { // Ajax请求不显示错误页面
					Helper::halt ( Helper::get_errorname ( $errno ) . ':' . $errstr, $errfile, $errline, $trace );
				}
			}
		}
		if (C ( 'log', 'log_threshold' ) != 0) { // 记录 错误 日志
			log_message ( 'error', 'Severity: ' . Helper::get_errorname ( $errno ) . '  --> ' . $errstr . ' ' . $errfile . ' ' . $errline, TRUE );
		}
	}

	/**
	 * 异常处理
	 *
	 * @param obj $exception
	 * @param string $message
	 * @param string $filepath
	 * @param int $line
	 */
	public static function _exception_handle($exception) {
		restore_error_handler ();
		restore_exception_handler ();
		$trace = $exception->getTrace ();
		if (@$trace [0] ['file'] == '') {
			unset ( $trace [0] );
			$trace = array_values ( $trace );
		}
		$file = @$trace [0] ['file'];
		$line = @$trace [0] ['line'];
		if (IS_CLI) {
			printf ( Helper::get_errorname ( $exception ) . ': ' . $exception->getMessage () . ' in ' . $file . ' on line ' . $line );
		} else {
			if (C ( 'config', 'firephp', false ) && class_exists ( 'FB' )) { // FirePHP调试
				fb ( $exception );
			}
			if (! IS_AJAX) { // Ajax请求不显示错误页面
				Helper::halt ( $exception->getMessage (), $file, $line, $trace, $exception->getCode () );
			}
		}
	}

	/**
	 * 致命错误捕捉
	 */
	public static function _shutdown_handle() {
		if (($errno = error_get_last ()) && $errno ['type'] & IS_DEBUG) {
			restore_error_handler ();
			restore_exception_handler ();
			$trace = debug_backtrace ();
			unset ( $trace [0] ["function"], $trace [0] ["args"] );
			Core::_error_handle ( $errno, $errno ['message'], $errno ['file'], $errno ['line'] );
		}
	}

	public static function logo() {
		return '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';
	}
}
Loader::helper ( 'Global' );