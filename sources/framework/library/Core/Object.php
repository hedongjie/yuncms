<?php
/**
 * 对象类
 * @author Tongle Xu <xutongle@gmail.com> 2013-1-8
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Object.php 2 2013-01-14 07:14:05Z xutongle $
 */
class object {
	protected $errno, $error;

	public function __construct() {

	}

	public function __get($name) {
		return isset ( $this->$name ) ? $this->$name : null;
	}

	public function __set($name, $value) {
		$this->$name = $value;
	}

	public function __isset($name) {
		return isset ( $this->$name );
	}

	public function __unset($name) {
		unset ( $this->$name );
	}

	public function __toString() {
		return get_class ( $this );
	}

	function errno() {
		return $this->errno;
	}

	function error() {
		return $this->error;
	}
}