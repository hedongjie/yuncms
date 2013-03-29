<?php
/**
 * 核心Debug类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Debug.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Debug extends FB {
	/**
	 *
	 * @var Debug
	 */
	protected static $instance = null;

	public static function &instance() {
		if (null === self::$instance) {
			self::$instance = new self ();
		}
		return self::$instance;
	}

	/**
	 * 开启Xhprof调试信息
	 */
	public function xhprof_start($type = null) {
		if (function_exists('saeAutoLoader')) {
			sae_xhprof_start();
			return;
		}
		$profiler = $this->profiler ( 'xhprof' );
		if (true === $profiler->is_open ()) {
			$xhprof_fun = 'xhprof_enable';
			if (function_exists ( $xhprof_fun )) {
				$xhprof_fun ( $type );
			}
			$profiler->start ( 'Xhprof', $type === null ? 'default' : 'Type:' . $type );
		}
	}

	/**
	 * 停止Xhprof调试信息
	 */
	public function xhprof_stop() {
		if (function_exists('saeAutoLoader')) {
			sae_xhprof_end();
			return;
		}
		$profiler = $this->profiler ( 'xhprof' );
		if (true === $profiler->is_open ()) {
			$xhprof_fun = 'xhprof_disable';
			if (function_exists ( $xhprof_fun )) {
				$data = $xhprof_fun ();
			} else {
				$data = null;
			}
			$profiler->stop ();
			return $data;
		}
	}

	public function __call($m, $v) {
		return $this;
	}
}