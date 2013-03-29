<?php
/**
 * Xcache缓存驱动器
 * @author Tongle Xu <xutongle@gmail.com> 2012-10-31
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Xcache.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cache_Driver_Xcache extends Cache {

	/**
	 * 拥有删除数据的权限用户
	 *
	 * xcache清空缓存的时候需要获得有权限的用户
	 *
	 * @var string
	 */
	private $auth_user = '';

	/**
	 * 拥有删除数据的权限用户的密码
	 *
	 * xcache清空缓存的时候需要获得有权限的用户
	 *
	 * @var string
	 */
	private $auth_pwd = '';

	/**
	 * 构造函数
	 *
	 * 如果没有安装xcache扩展则抛出异常
	 *
	 * @throws cache_exception 如果没有安装xcache扩展
	 */
	public function __construct() {
		if (! extension_loaded ( 'xcache' )) {
			throw_exception ( 'The xcache extension must be loaded !' );
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_value()
	 */
	protected function set_value($key, $value, $expire = 0) {
		return xcache_set ( $key, $value, $expire );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::get_value()
	 */
	protected function get_value($key) {
		return xcache_get ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::delete_value()
	 */
	protected function delete_value($key) {
		return xcache_unset ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::clear()
	 */
	public function clear() {
		// xcache_clear_cache需要验证权限
		$tmp ['user'] = isset ( $_SERVER ['PHP_AUTH_USER'] ) ? null : $_SERVER ['PHP_AUTH_USER'];
		$tmp ['pwd'] = isset ( $_SERVER ['PHP_AUTH_PW'] ) ? null : $_SERVER ['PHP_AUTH_PW'];
		$_SERVER ['PHP_AUTH_USER'] = $this->auth_user;
		$_SERVER ['PHP_AUTH_PW'] = $this->auth_pwd;
		// 如果配置中xcache.var_count > 0 则不能用xcache_clear_cache(XC_TYPE_VAR, 0)的方式删除
		$max = xcache_count ( XC_TYPE_VAR );
		for($i = 0; $i < $max; $i ++) {
			xcache_clear_cache ( XC_TYPE_VAR, $i );
		}
		// 恢复之前的权限
		$_SERVER ['PHP_AUTH_USER'] = $tmp ['user'];
		$_SERVER ['PHP_AUTH_PW'] = $tmp ['pwd'];
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see Cache::set_config()
	 */
	public function set_config($options = array()) {
		if (! is_array ( $options )) return false;
		parent::set_config ( $options );
		$this->auth_user = $options ['user'];
		$this->auth_pwd = $options ['pwd'];
	}
}