<?php
/**
 * 配置文件操作类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Config.php 2 2013-01-14 07:14:05Z xutongle $
 */
defined ( 'CONFIG_PATH' ) or define ( 'CONFIG_PATH', SOURCE_PATH . 'config' . DIRECTORY_SEPARATOR );
class Core_Config {
	private static $_file = null;
	private static $_config = array ();

	/**
	 * 设置配置文件
	 *
	 * @param string $file 文件名
	 */
	public static function set_file($file) {
		if (preg_match ( "/^[0-9a-z_\-]+$/i", $file )) {
			self::$_file = CONFIG_PATH . $file . '.php';
		} else {
			throw_exception ( 'Unable to load the file ' . $file . ' , file is not valid.' );
		}
	}

	/**
	 * 加载配置文件
	 *
	 * @param string $file 文件名称
	 * @return multitype:
	 */
	public static function load($file) {
		if (! isset ( self::$_config [$file] )) {
			self::set_file ( $file );
			if (file_exists ( self::$_file )) {
				self::$_config [$file] = include self::$_file;
			} else {
				return false;
			}
		}
		return self::$_config [$file];
	}

	/**
	 * 获取配置项
	 *
	 * @param string $file 配置文件名称
	 * @param string $key 配置项
	 * @param $default 默认值
	 */
	public static function get($file, $key = null, $default = false) {
		$config = self::load ( $file );
		return is_null ( $key ) ? $config : (isset ( $config [$key] ) ? $config [$key] : $default);
	}

	/**
	 * 保存配置文件
	 *
	 * @param string $file 文件名
	 * @param array $data 配置数组
	 */
	public static function set($file, $data = array()) {
		self::set_file ( $file );
		$config = self::load ( $file );
		$config = array_merge ( $config, $data );
		return self::_write ( self::$_file, $config );
	}

	/**
	 * 修改配置文件
	 *
	 * @param $file 文件名称
	 * @param $data 配置数组
	 */
	public static function modify($file, $data = array()) {
		self::set_file ( $file );
		if (! is_writable ( self::$_file )) {
			throw_exception ( 'Please chmod ' . self::$_file . ' to 0777 !' );
		}
		$pattern = $replacement = array ();
		foreach ( $data as $k => $v ) {
			$v = trim ( $v );
			$configs [$k] = $v;
			$pattern [$k] = "/'" . $k . "'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
			$replacement [$k] = "'" . $k . "' => \${1}" . $v . "\${2}\${3},";
		}
		$str = file_get_contents ( self::$_file );
		$str = preg_replace ( $pattern, $replacement, $str );
		return C ( 'config', 'lock_ex' ) ? file_put_contents ( self::$_file, $str, LOCK_EX ) : file_put_contents ( self::$_file, $str );
	}

	/**
	 * 创建配置文件
	 *
	 * @param string $file 文件名称
	 * @param array $data 配置数组
	 */
	public static function create($file, $data = array()) {
		self::set_file ( $file );
		return self::_write ( self::$_file, $data );
	}

	/**
	 * 写入配置文件
	 *
	 * @param string $file
	 * @param array $array
	 * @throws yuncms_exception
	 */
	private static function _write($file, $array = array()) {
		$data = "<?php\nreturn " . var_export ( $array, true ) . ';';
		if (! @file_put_contents ( $file, $data )) {
			throw_exception ( "$file is not exists or not writable" );
		}
		return true;
	}
}