<?php
/**
 * Base64 加密实现类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-29
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Base64.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Crypt_Base64 {

	/**
	 * 加密字符串
	 *
	 * @param string $str 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function encrypt($data, $key) {
		$key = md5 ( $key );
		$data = base64_encode ( $data );
		$x = 0;
		$len = strlen ( $data );
		$l = strlen ( $key );
		for($i = 0; $i < $len; $i ++) {
			if ($x == $l) $x = 0;
			$char .= substr ( $key, $x, 1 );
			$x ++;
		}
		for($i = 0; $i < $len; $i ++) {
			$str .= chr ( ord ( substr ( $data, $i, 1 ) ) + (ord ( substr ( $char, $i, 1 ) )) % 256 );
		}
		return $str;
	}

	/**
	 * 解密字符串
	 *
	 * @param string $str 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function decrypt($data, $key) {
		$key = md5 ( $key );
		$x = 0;
		$len = strlen ( $data );
		$l = strlen ( $key );
		for($i = 0; $i < $len; $i ++) {
			if ($x == $l) $x = 0;
			$char .= substr ( $key, $x, 1 );
			$x ++;
		}
		for($i = 0; $i < $len; $i ++) {
			if (ord ( substr ( $data, $i, 1 ) ) < ord ( substr ( $char, $i, 1 ) )) {
				$str .= chr ( (ord ( substr ( $data, $i, 1 ) ) + 256) - ord ( substr ( $char, $i, 1 ) ) );
			} else {
				$str .= chr ( ord ( substr ( $data, $i, 1 ) ) - ord ( substr ( $char, $i, 1 ) ) );
			}
		}
		return base64_decode ( $str );
	}
}