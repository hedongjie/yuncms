<?php
/**
 * Crypt 加密实现类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-29
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Crypt.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Crypt_Crypt {

	/**
	 * 加密字符串
	 *
	 * @param string $str 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function encrypt($str, $key, $toBase64 = false) {
		$r = md5 ( $key );
		$c = 0;
		$v = "";
		$len = strlen ( $str );
		$l = strlen ( $r );
		for($i = 0; $i < $len; $i ++) {
			if ($c == $l) $c = 0;
			$v .= substr ( $r, $c, 1 ) . (substr ( $str, $i, 1 ) ^ substr ( $r, $c, 1 ));
			$c ++;
		}
		if ($toBase64) {
			return base64_encode ( self::ed ( $v, $key ) );
		} else {
			return self::ed ( $v, $key );
		}

	}

	/**
	 * 解密字符串
	 *
	 * @param string $str 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function decrypt($str, $key, $toBase64 = false) {
		if ($toBase64) {
			$str = self::ed ( base64_decode ( $str ), $key );
		} else {
			$str = self::ed ( $str, $key );
		}
		$v = "";
		$len = strlen ( $str );
		for($i = 0; $i < $len; $i ++) {
			$md5 = substr ( $str, $i, 1 );
			$i ++;
			$v .= (substr ( $str, $i, 1 ) ^ $md5);
		}
		return $v;
	}

	private static function ed($str, $key) {
		$r = md5 ( $key );
		$c = 0;
		$v = "";
		$len = strlen ( $str );
		$l = strlen ( $r );
		for($i = 0; $i < $len; $i ++) {
			if ($c == $l) $c = 0;
			$v .= substr ( $str, $i, 1 ) ^ substr ( $r, $c, 1 );
			$c ++;
		}
		return $v;
	}
}