<?php
/**
 * File缓存驱动器
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: File.php 2 2013-01-14 07:14:05Z xutongle $
 */
define ( 'CACHE_PATH', DATA_PATH . 'cache' . DIRECTORY_SEPARATOR );
class Cache_Driver_File extends Cache {

	/**
	 * 缓存过期时间
	 *
	 * @var int
	 */
	private $expire = '';

	private $suffix = '.cache.php';

	/**
	 * 标志存储时间
	 *
	 * @var string
	 */
	const STORETIME = 'store';

	/**
	 * 标志存储数据
	 *
	 * @var string
	 */
	const DATA = 'data';

	/**
	 * 配置文件中标志过期时间名称定义(也包含缓存元数据中过期时间的定义)
	 *
	 * @var string
	 */
	const EXPIRE = 'expires';

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expires = 0) {
		$file = $this->_path ( $key );
		return File::write ( $file, $value ) == strlen ( $value );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		$file = $this->_path ( $key );
		return File::read ( $file );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		$file = $this->_path ( $key );
		return File::delete ( $file );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		return File::clear ( CACHE_PATH );
	}

	/**
	 * 获取文本缓存要存放的路径
	 *
	 * @param string $key
	 *            缓存数据的唯一key
	 */
	private function _path($key) {
		if (strpos ( $key, '/' ) !== false) {
			$path = CACHE_PATH . dirname ( $key ) . DIRECTORY_SEPARATOR . basename ( $key ) . $this->suffix;
		} else {
			$path = CACHE_PATH . $key . $this->suffix;
		}
		$dir = dirname ( $path );
		if (! is_dir ( $dir )) Folder::create ( $dir, 0777 );
		return $path;
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_config()
	 */
	public function set_config($options = array()) {
		if (! is_array ( $options )) return false;
		parent::set_config ( $options );
		$this->suffix = $options ['suffix'];
	}
}