<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-27
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: KVDB.php 2 2013-01-14 07:14:05Z xutongle $
 */
class KVDB {

	private $handle;

	public function __construct($storage = 'DBA', $handler = 'flatfile') {
		$this->handle = & KVDB_Abstract::get_instance ( $storage, $handler );
	}

	public function open($path, $mode = 'n') {
		return $this->handle->open ( $path, $mode );
	}

	public function popen($path, $mode = 'n') {
		return $this->handle->popen ( $path, $mode );
	}

	public function add($key, $value) {
		return $this->handle->add ( $key, $value );
	}

	public function set($key, $value) {
		return $this->handle->set ( $key, $value );
	}

	public function get($key) {
		return $this->handle->get ( $key );
	}

	public function rm($key) {
		return $this->handle->rm ( $key );
	}

	public function exists($key) {
		return $this->handle->exists ( $key );
	}

	public function close() {
		return $this->handle->close ();
	}
}