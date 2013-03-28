<?php
/**
 * SAE云计算平台KVDB封装
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: SAE.php 2 2013-01-14 07:14:05Z xutongle $
 */
class KVDB_Driver_SAE extends KVDB_Abstract {

	protected $handler;

	public function __construct() {
		$this->handler = new SaeKV();
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::open()
	 */
	public function open($path, $mode = 'n') {
		$id  = $this->handler->init();
		if (! $id) {
			return false;
		}
		return $this->handler;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::popen()
	 */
	public function popen($path, $mode = 'n') {
		$id  = $this->handler->init();
		if (! $id) {
			return false;
		}
		return $this->handler;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::add()
	 */
	public function add($key, $value) {
		return $this->handler->add($key, $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::set()
	 */
	public function set($key, $value) {
		return $this->handler->set($key, $value);
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::get()
	 */
	public function get($key) {
		return $this->handler->get($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::rm()
	 */
	public function rm($key) {
		return $this->handler->delete($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::exists()
	 */
	public function exists($key) {
		$res = $this->handler->get($key);
		return $res ? true : false;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::close()
	 */
	public function close() {
		$this->handler = null;
		return;
	}
}