<?php
/**
 * 关闭Debug加载的类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: NoDebug.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_NoDebug {

	public function __call($m, $v) {
		return $this;
	}

	public function log($i = null) {
		return $this;
	}

	public function info($i = null) {
		return $this;
	}

	public function error($i = null) {
		return $this;
	}

	public function group($i = null) {
		return $this;
	}

	public function groupEnd($i = null) {
		return $this;
	}

	public function table($Label = null, $Table = null) {
		return $this;
	}

	public function profiler($i = null) {
		return $this;
	}

	public function is_open() {
		return false;
	}
}