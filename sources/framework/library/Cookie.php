<?php
/**
 * Cookie Class
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Cookie.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Cookie {
	/**
	 * 判断Cookie是否存在
	 *
	 * @param string $var 变量名
	 * @return boolean 成功则返回true，否则返回 false
	 */
	public static function is_set($var) {
		return isset ( $_COOKIE [C ( 'cookie', 'prefix', 'yuncms_' ) . $var] );
	}

	/**
	 * 获取某个Cookie值
	 *
	 * @param string $var 变量名
	 * @param string $default 默认值
	 * @return mixed 成功则返回cookie 值，否则返回 false
	 */
	public static function get($var, $default = '') {
		$var = C ( 'cookie', 'prefix', 'yuncms_' ) . $var;
		return isset ( $_COOKIE [$var] ) ? authcode ( $_COOKIE [$var], 'DECODE' ) : $default;
	}

	/**
	 * 设置 cookie
	 *
	 * @param string $var 变量名
	 * @param string $value 变量值
	 * @param int $time 过期时间
	 */
	public static function set($var, $value = '', $time = 0) {
		if ($value == ''){
			$time = TIME - 3600;
		}elseif ($time > 0 && $time < 31536000){
			$time += TIME;
		}
		$s = $_SERVER ['SERVER_PORT'] == '443' ? 1 : 0;
		$var = C ( 'cookie', 'prefix', 'yuncms_' ) . $var;
		$_COOKIE [$var] = $value;
		setcookie ( $var, authcode ( $value, 'ENCODE' ), $time, C ( 'cookie', 'path', '/' ), C ( 'cookie', 'domain', '' ), $s );
	}

	/**
	 * 删除某个Cookie值
	 *
	 * @param string $var 变量名
	 */
	public static function delete($var) {
		self::set ( $var, '', TIME - 3600 );
		unset ( $_COOKIE [C ( 'cookie', 'prefix', 'yuncms_' ) . $var] );
	}

	/**
	 * 清空Cookie值
	 */
	public static function clear() {
		unset ( $_COOKIE );
	}
}