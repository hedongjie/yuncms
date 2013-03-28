<?php
/**
 * DBA驱动
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: DBA.php 2 2013-01-14 07:14:05Z xutongle $
 */
class KVDB_Driver_DBA extends KVDB_Abstract {

	protected $id, $handler;

	public function __construct($handler = 'flatfile') {
		$this->handler = $handler;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::open()
	 */
	public function open($path, $mode = 'n') {
		$id = dba_open ( $path, $mode, $this->handler );
		if (! $id) {
			return false;
		}
		$this->id = $id;
		return $id;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::popen()
	 */
	public function popen($path, $mode = 'n') {
		$id = dba_popen ( $path, $mode, $this->handler );
		if (! $id) {
			return false;
		}
		$this->id = $id;
		return $id;
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::add()
	 */
	public function add($key, $value) {
		return dba_insert ( $key, $value, $this->id );
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::set()
	 */
	public function set($key, $value) {
		return dba_replace ( $key, $value, $this->id );
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::get()
	 */
	public function get($key) {
		return dba_fetch ( $key, $this->id );
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::rm()
	 */
	public function rm($key) {
		return dba_delete ( $key, $this->id );
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::exists()
	 */
	public function exists($key) {
		return dba_exists ( $key, $this->id );
	}

	/**
	 * (non-PHPdoc)
	 * @see KVDB_Abstract::close()
	 */
	public function close() {
		return dba_close ( $this->id );
	}
}