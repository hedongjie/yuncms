<?php
/**
 * 百度云计算缓存操作
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: BAE.php 2 2013-01-14 07:14:05Z xutongle $
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
	 *
	 * 判断是否有支持memcache,如果没有安装扩展库将会抛出异常,<br/>
	 * 首先尝试使用memcached扩展，如果然后尝试创建memcache
	 *
	 * @throws 如果没有安装memcache扩展则抛出异常
	 */
	public function __construct() {
		require_once ('BaeMemcache.class.php');
		$this->memcache = new BaeMemcache();
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return $this->memcache->set($key,$value,$this->compress,( int ) $expire);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return $this->memcache->get($key,$this->compress);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return $this->memcache->delete($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {

	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_config()
	 */
	public function set_config($options = array()) {

	}
}