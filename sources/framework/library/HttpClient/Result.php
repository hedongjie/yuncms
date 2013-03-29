<?php
/**
 * HttpClient返回对象
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Result.php 2 2013-01-14 07:14:05Z xutongle $
 */
class HttpClient_Result {

	protected $data;

	protected $code = 0;

	protected $headers = array ();

	protected $cookies = array ();

	protected $time = 0;

	public function __construct($data) {
		if (isset ( $data ['code'] )) $this->code = $data ['code'];
		if (isset ( $data ['time'] )) $this->time = $data ['time'];
		if (isset ( $data ['data'] )) $this->data = $data ['data'];

		if (isset ( $data ['header'] ) && is_array ( $data ['header'] )) foreach ( $data ['header'] as $item ) {
			if (preg_match ( '#^([a-zA-Z0-9\-]+): (.*)$#', $item, $m )) {
				if ($m [1] == 'Set-Cookie') {
					if (preg_match ( '#^([a-zA-Z0-9\-_]+)=(.*); Path=(.*)$#', $m [2], $m2 )) {
						$this->cookies [$m2 [1]] = $m2 [2];
					}
				} else {
					$this->headers [$m [1]] = $m [2];
				}
			}
		}
	}

	public function __toString() {
		return ( string ) $this->data ();
	}

	public function code() {
		return $this->code;
	}

	public function data() {
		return $this->data;
	}

	public function time() {
		return $this->time;
	}

	public function header($key = null) {
		if (null === $key) {
			return $this->headers;
		} else {
			return $this->headers [$key];
		}
	}

	public function cookie($key = null) {
		if (null === $key) {
			return $this->cookies;
		} else {
			return $this->cookies [$key];
		}
	}
}