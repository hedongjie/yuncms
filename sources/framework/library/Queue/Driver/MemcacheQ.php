<?php
/**
 * MemcacheQ队列驱动器
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-14
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: MemcacheQ.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Queue_Driver_MemcacheQ extends Queue_Abstract {

	protected $memcache = NULL;

	public function __construct($options) {
		$this->memcache = new Memcache ();
		$port = isset ( $options ['port'] ) ? $options ['port'] : 22201;
		try {
			$this->memcache->connect ( $options ['host'], $port );
		} catch ( Exception $e ) {
			throw_exception ( 'MemcacheQ connection failed!' );
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Queue::put()
	 */
	public function put($name, $data) {
		return $this->memcache->set ( $name, $data );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Queue::get()
	 */
	public function get($name) {
		return $this->memcache->get ( $name );
	}

	/**
	 * 获取队列状态
	 *
	 * @param string $name 队列名称
	 */
	public function status($name) {
		return;
	}

	/**
	 *
	 * @param unknown_type $name
	 */
	public function reset($name) {
		return $this->memcache->delete ( $name );
	}

	/**
	 * 析构函数
	 */
	public function __destruct() {
		$this->memcache->close ();
	}
}