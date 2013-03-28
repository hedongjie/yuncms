<?php
/**
 * Eaccelerator缓存驱动器
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Eaccelerator.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_Eaccelerator extends Cache {

	/**
	 * 构造函数
	 *
	 * 判断是否有安装eaccelerator扩展,如果没有安装则会抛出cache_exception异常
	 *
	 * @throws cache_exception
	 */
	public function __construct() {
		if (! function_exists ( 'eaccelerator_get' )) {
			throw_exception ( 'The eaccelerator extension must be loaded !' );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return eaccelerator_put ( $key, $value, $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return eaccelerator_get ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return eaccelerator_rm ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		return eaccelerator_gc ();
	}

}
