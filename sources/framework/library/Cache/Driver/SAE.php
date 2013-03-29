<?php
/**
 * 新浪云计算缓存操作
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: SAE.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_SAE extends Cache {

	/**
	 * memcache缓存操作句柄
	 *
	 * @var Memcache
	 */
	protected $memcache = null;

	/**
	 * 是否对缓存采取压缩存储
	 *
	 * @var int
	 */
	protected $compress = 0;

	/**
	 * 构造函数
	 */
	public function __construct() {
		$this->memcache = memcache_init();
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return memcache_set($this->memcache,$key,$this->compress, ( int ) $expire);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return memcache_get( $this->memcache, $key, $this->compress);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return memcache_delete( $this->memcache , $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		return memcache_flush($this->memcache);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_config()
	 */
	public function set_config($options = array()) {

	}
}