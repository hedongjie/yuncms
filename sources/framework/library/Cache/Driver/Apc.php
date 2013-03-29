<?php
/**
 * Apc缓存驱动器
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Apc.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_Apc extends Cache {

	/**
	 * 构造函数
	 *
	 * 判断是否有安装apc扩展,如果没有安装将会抛出cache异常
	 *
	 * @throws Core_Exception 当没有安装apc扩展的时候
	 */
	public function __construct() {
		if (! extension_loaded ( 'apc' )) {
			throw_exception ( 'The apc extension must be loaded !' );
		}
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expires = 0) {
		return apc_store ( $key, $value, $expires );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return apc_fetch ( $key );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return apc_delete ( $key );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Cache::clear()
	 */
	public function clear() {
		return apc_clear_cache ( 'user' );
	}
}