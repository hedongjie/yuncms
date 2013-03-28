<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: ZendCache.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_ZendCache extends Cache {

	/**
	 * 构造函数
	 *
	 * 如果没有安装zend_cache扩展,则抛出异常
	 *
	 * @throws cache_exception 当没有安装zend_cache扩展的时候抛出异常
	 */
	public function __construct() {
		if (! function_exists ( 'zend_shm_cache_fetch' )) {
			throw_exception ( 'The zend cache extension must be loaded !' );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return zend_shm_cache_store ( $key, $value, $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return zend_shm_cache_fetch ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return zend_shm_cache_delete ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		return zend_shm_cache_clear ();
	}
}