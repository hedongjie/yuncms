<?php
/**
 * HMAC 加密实现类
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-29
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Hmac.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Crypt_Hmac {

	/**
	 * SHA1加密
	 *
	 * @param string $key 加密key
	 * @param string $str 字符串
	 * @return string
	 */
	public static function sha1($key, $str) {
		$blocksize = 64;
		$hashfunc = 'sha1';
		if (strlen ( $key ) > $blocksize) $key = pack ( 'H*', $hashfunc ( $key ) );
		$key = str_pad ( $key, $blocksize, chr ( 0x00 ) );
		$ipad = str_repeat ( chr ( 0x36 ), $blocksize );
		$opad = str_repeat ( chr ( 0x5c ), $blocksize );
		$hmac = pack ( 'H*', $hashfunc ( ($key ^ $opad) . pack ( 'H*', $hashfunc ( ($key ^ $ipad) . $str ) ) ) );
		return $hmac;
	}

	/**
	 * MD5加密
	 *
	 * @access static
	 * @param string $key 加密key
	 * @param string $str 字符串
	 * @return string
	 */
	public static function md5($key, $str) {
		$b = 64;
		if (strlen ( $key ) > $b) {
			$key = pack ( "H*", md5 ( $key ) );
		}

		$key = str_pad ( $key, $b, chr ( 0x00 ) );
		$ipad = str_pad ( '', $b, chr ( 0x36 ) );
		$opad = str_pad ( '', $b, chr ( 0x5c ) );
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return md5 ( $k_opad . pack ( "H*", md5 ( $k_ipad . $str ) ) );
	}

}