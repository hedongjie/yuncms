<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Loader.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Loader {
	private static $instances = array ();

	/**
	 * 获取存储实例
	 *
	 * @param $config
	 */
	public static function storage($config = '') {
		if (! empty ( $config ) && is_string ( $config )) { // 不为空并且是字符串
			$i_name = $config;
			$config = C ( 'storage', $config );
		} elseif (is_array ( $config )) { // 数组配置
			$config = array_change_key_case ( $config );
			$i_name = '.config_' . md5 ( serialize ( $config ) );
		} elseif (IS_SAE) { // 新浪云计算下的文件存储
			$i_name = 'sae';
			$config = array ('driver' => 'SAE' );
		} elseif (empty ( $config )) { // 如果配置为空，读取默认配置文件设置
			$config = C ( 'storage', 'default' );
			$i_name = 'default';
		}
		if (! isset ( self::$instances ['storage'] [$i_name] )) {
			if (! is_array ( $config ) || empty ( $config ['driver'] )) throw_exception ( 'No database configuration.' );
			$class = 'Storage_Driver_' . ucfirst ( strtolower ( $config ['driver'] ) );
			if (class_exists ( $class )) { // 检查驱动类
				self::$instances ['storage'] [$i_name] = new $class ( $config );
			} else {
				throw_exception ( 'No database driver' . ': ' . $class ); // 类没有定义
			}
		}
		return self::$instances ['storage'] [$i_name];
	}

	public static function db($db_config = '') {
		if (! empty ( $db_config ) && is_string ( $db_config )) { // 不为空并且是字符串
			$i_name = $db_config;
			$db_config = C ( 'database', $db_config );
		} elseif (is_array ( $db_config )) { // 数组配置
			$db_config = array_change_key_case ( $db_config );
			$i_name = '.config_' . md5 ( serialize ( $db_config ) );
		} elseif (empty ( $db_config )) { // 如果配置为空，读取默认配置文件设置
			$db_config = C ( 'database', 'default' );
			$i_name = 'default';
		}
		if (! isset ( self::$instances ['db'] [$i_name] )) {
			if (! is_array ( $db_config ) || empty ( $db_config ['driver'] )) throw_exception ( 'No database configuration.' );
			$class = 'Database_Driver_' . ucfirst ( strtolower ( $db_config ['driver'] ) );
			if (class_exists ( $class )) { // 检查驱动类
				self::$instances ['db'] [$i_name] = new $class ( $db_config );
				self::$instances ['db'] [$i_name]->open ();
			} else {
				throw_exception ( 'No database driver' . ': ' . $class ); // 类没有定义
			}
		}
		return self::$instances ['db'] [$i_name];
	}

	/**
	 * 加载缓存
	 *
	 * @param string $setting 配置项
	 */
	public static function cache($options = null) {
		if (! empty ( $options ) && is_string ( $options )) { // 不为空并且是字符串
			$i_name = $options;
			$options = C ( 'cache', $options );
		} elseif (is_array ( $options )) { // 数组配置
			$options = array_change_key_case ( $options );
			$i_name = '.config_' . md5 ( serialize ( $options ) );
		} elseif (IS_SAE) { // 新浪云计算环境
			$options = array ('driver' => 'SAE','expire' => '0' );
			$i_name = '.config_' . md5 ( serialize ( $options ) );
		} else { // 加载默认配置
			$options = C ( 'cache', 'default' );
			$i_name = 'default';
		}
		if (! isset ( self::$instances ['cache'] [$i_name] ) || ! is_object ( self::$instances ['cache'] [$i_name] )) {
			$class = 'Cache_Driver_' . $options ['driver'];
			self::$instances ['cache'] [$i_name] = new $class ( $options );
			self::$instances ['cache'] [$i_name]->set_config ( $options );
		}
		return self::$instances ['cache'] [$i_name];
	}

	/**
	 * 加载队列
	 *
	 * @param string $setting 配置
	 */
	public static function queue($setting = 'default') {
		if (! isset ( self::$instances ['queue'] [$setting] )) {
			$options = C ( 'queue', $setting );
			$class = 'Queue_Driver_' . ucfirst ( $options ['driver'] );
			self::$instances ['queue'] [$setting] = new $class ( $options );
		}
		return self::$instances ['queue'] [$setting];
	}

	/**
	 * 加载Session
	 */
	public static function session() {
		if (! isset ( self::$instances ['session'] )) {
			if (function_exists ( 'ini_set' )) @ini_set ( 'session.gc_maxlifetime', C ( 'session', 'maxlifetime' ) );
			session_cache_expire ( C ( 'session', 'cache_expire' ) );
			session_set_cookie_params ( C ( 'session', 'cookie_lifetime' ), C ( 'session', 'cookie_path' ), C ( 'session', 'cookie_domain' ) );
			Session_Abstract::get_instance ( C ( 'session' ) );
			if (isset ( $_GET ['sid'] ) && ! empty ( $_GET ['sid'] )) session_id ( trim ( $_GET ['sid'] ) );
			session_start ();
			define ( 'SID', session_id () );
			self::$instances ['session'] = true;
		}
		return self::$instances ['session'];
	}

	/**
	 * 加载模型
	 *
	 * @param $model
	 */
	public static function model($model, $initialize = true) {
		if (! isset ( self::$instances ['model'] [$model] )) {
			import ( $model, SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR );
			if ($initialize)
				self::$instances ['model'] [$model] = new $model ();
			else
				return true;
		}
		return self::$instances ['model'] [$model];
	}

	/**
	 * 加载控制器
	 *
	 * @param string $controller
	 * @param string $app
	 */
	public static function controller($controller = null, $app = null) {
		$app = ! is_null ( $app ) ? trim ( $app ) : APP;
		$controller = ! is_null ( $controller ) ? trim ( $controller ) : CONTROLLER;
		$classname = $controller . 'Controller';
		if (isset ( self::$instances ['controller'] [$classname] )) return self::$instances ['controller'] [$classname];
		import ( $app . ':' . $classname );
		if (class_exists ( $classname, false )) {
			self::$instances ['controller'] [$classname] = new $classname ();
		} else {
			throw_exception ( 'Unable to create instance for ' . $classname . ' , class is not exist.' );
		}
		return self::$instances ['controller'] [$classname];
	}

	/**
	 * 加载助手
	 *
	 * @param string $helper
	 */
	public static function helper($helper) {
		if (! isset ( self::$instances ['helper'] [$helper] )) {
			if (strpos ( $helper, ':' ) !== false) {
				list ( $app, $app_helper ) = explode ( ':', $helper );
				$import = $app . ':' . 'helper.' . $app_helper; // 构建加载应用类的相关字符
				import ( $import );
			} else {
				import ( 'helper.' . $helper );
			}
			self::$instances ['helper'] [$helper] = true;
		}
		return self::$instances ['helper'] [$helper];
	}

	/**
	 * 加载类库
	 *
	 * @param string $classname 类名
	 * @param bool $initialize 是否自动实例化
	 */
	public static function lib($classname, $initialize = true) {
		if (! $initialize) return self::get_instance ( $classname, false );
		return self::get_instance ( $classname );
	}

	/**
	 * 加载SMS网关接口
	 *
	 * @param string $setting 配置项
	 */
	public static function sms() {
		if (! isset ( self::$instances ['sms'] )) {
			$options = C ( 'sms' );
			$class = 'SMS_Driver_' . ucfirst ( $options ['driver'] );
			self::$instances ['sms'] = new $class ();
			self::$instances ['sms']->set ( $options );
		}
		return self::$instances ['sms'];
	}

	/**
	 * 取得对象实例 支持调用类的静态方法
	 *
	 * @param string $name 类名
	 * @param string $method 方法名，如果为空则返回实例化对象 如果定义$method为false则不实例化该类
	 * @param array $args 调用参数
	 * @return object
	 */
	public static function get_instance($classname, $method = '', $args = array()) {
		$key = empty ( $args ) ? $classname . $method : $classname . $method . to_guid_string ( $args );
		if (! isset ( self::$instances [$key] )) {
			if (strpos ( $classname, ':' ) !== false) { // 是否是应用内的类
				list ( $app, $classname ) = explode ( ':', $classname );
				import ( $app . ':' . 'library.' . $classname );
			} else {
				import ( 'library.' . $classname );
			}
			if ($method !== false) {
				$o = new $classname ();
				if (! empty ( $method ) && method_exists ( $o, $method )) {
					if (! empty ( $args )) {
						self::$instances [$key] = call_user_func_array ( array (&$o,$method ), $args );
					} else {
						self::$instances [$key] = $o->$method ();
					}
				} else {
					self::$instances [$key] = $o;
				}
			} else {
				return true;
			}
		}
		return self::$instances [$key];
	}
}