<?php
/**
 * Apc缓存驱动器
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Memcache.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_Memcache extends Cache {
	/**
	 * memcache缓存操作句柄
	 *
	 * @var Memcache
	 */
	protected $memcache = null;

	/**
	 * 标志是否是memcached
	 *
	 * @var boolean
	 */
	private $is_memcached = false;

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
	 * @throws cache_exception 如果没有安装memcache扩展则抛出异常
	 */
	public function __construct() {
		if (extension_loaded ( 'Memcached' )) {
			$this->memcache = new Memcached ();
			$this->is_memcached = true;
		} elseif (extension_loaded ( 'Memcache' )) {
			$this->memcache = new Memcache ();
			$this->is_memcached = false;
		} else {
			throw_exception ( 'memcache requires PHP `Memcache` extension to be loaded !' );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return $this->is_memcached ? $this->memcache->set ( $key, $value, ( int ) $expire ) : $this->memcache->set ( $key, $value, $this->compress, ( int ) $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return $this->is_memcached ? $this->memcache->get ( $key ) : $this->memcache->get ( $key, $this->compress );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return $this->memcache->delete ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		return $this->memcache->flush ();
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_config()
	 */
	public function set_config($options = array()) {
		if (! is_array ( $options )) return false;
		parent::set_config ( $options );
		$servers = $options ['servers'];
		$default_server = array ('host' => '','port' => '','pconn' => true,'weight' => 1,'timeout' => 1,'retry' => 15,'status' => true,'fcallback' => null );
		foreach ( ( array ) $servers as $server ) {
			if (! is_array ( $server )) throw new Exception ( 'The memcache config is incorrect' );
			if (! isset ( $server ['host'] )) throw new Exception ( 'The memcache server ip address is not exist' );
			if (! isset ( $server ['port'] )) throw new Exception ( 'The memcache server port is not exist' );
			$args = array_merge ( $default_server, $server );
			$args = $this->is_memcached ? array ($args ['host'],$args ['port'],$args ['weight'] ) : array_values ( $args );
			call_user_func_array ( array ($this->memcache,'addServer' ), $args );
		}
	}
}