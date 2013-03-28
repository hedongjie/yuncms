<?php
/**
 * 常用的正则表达式来验证信息.如:网址 邮箱 手机号等
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Validate.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Validate {

	/**
	 * 正则表达式验证email格式
	 *
	 * @param string $str
	 * @return boolean
	 */
	public static function is_email($str) {
		if (! $str) return false;
		return preg_match ( '#[a-z0-9&\-_.]+@[\w\-_]+([\w\-.]+)?\.[\w\-]+#is', $str ) ? true : false;
	}

	/**
	 * 正则表达式验证网址
	 *
	 * @param string $str
	 * @return boolean
	 */
	public static function is_url($str) {
		if (! $str) return false;
		return preg_match ( '#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str ) ? true : false;
	}

	/**
	 * 验证字符串中是否含有汉字
	 *
	 * @param integer $string
	 * @return boolean
	 */
	public static function is_chinese_character($string) {
		if (! $string) return false;
		return preg_match ( '~[\x{4e00}-\x{9fa5}]+~u', $string ) ? true : false;
	}

	/**
	 * 用正则表达式验证邮证编码
	 *
	 * @param integer $num
	 * @return boolean
	 */
	public static function is_post_num($num) {
		if (! $num) return false;
		return preg_match ( '#^[1-9][0-9]{5}$#', $num ) ? true : false;
	}

	/**
	 * 正则表达式验证身份证号码
	 *
	 * @param integer $num
	 * @return boolean
	 */
	public static function is_personal_card($num) {
		if (! $num) return false;
		return preg_match ( '#^[\d]{15}$|^[\d]{18}$#', $num ) ? true : false;
	}

	/**
	 * 正则表达式验证IP地址, 注:仅限IPv4
	 *
	 * @param string $str
	 * @return boolean
	 */
	public static function is_ipv4($str) {
		if (! $str) return false;
		if (! preg_match ( '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str )) {
			return false;
		}
		$ip_array = explode ( '.', $str );
		// 真实的ip地址每个数字不能大于255（0-255）
		return ($ip_array [0] <= 255 && $ip_array [1] <= 255 && $ip_array [2] <= 255 && $ip_array [3] <= 255) ? true : false;
	}

	/**
	 * 用正则表达式验证出版物的ISBN号
	 *
	 * @param integer $str
	 * @return boolean
	 */
	public static function is_book_isbn($str) {
		if (! $str) return false;
		return preg_match ( '#^978[\d]{10}$|^978-[\d]{10}$#', $str ) ? true : false;
	}

	/**
	 * 用正则表达式验证手机号码(中国大陆区)
	 *
	 * @param integer $num
	 * @return boolean
	 */
	public static function is_mobile($num) {
		if (! $num) return false;
		return preg_match ( '#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^18[0-9]\d{8}$#', $num ) ? true : false;
	}

	/**
	 * 检测输入中是否含有错误字符
	 *
	 * @param char $string
	 *        	要检查的字符串名称
	 * @return TRUE or FALSE
	 */
	public static function is_badword($string) {
		$badwords = array ("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n","#" );
		foreach ( $badwords as $value ) {
			if (strpos ( $string, $value ) !== FALSE) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * 检查密码长度是否符合规定
	 *
	 * @param STRING $password
	 * @return TRUE or FALSE
	 */
	public static function is_password($password) {
		$strlen = strlen ( $password );
		if ($strlen >= 6 && $strlen <= 20) return true;
		return false;
	}

	/**
	 * 检查用户名是否符合规定
	 *
	 * @param STRING $username
	 *        	要检查的用户名
	 * @return TRUE or FALSE
	 */
	public static function is_username($username) {
		$strlen = strlen ( $username );
		if (self::is_badword ( $username ) || ! preg_match ( "/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $username )) {
			return false;
		} elseif (20 < $strlen || $strlen < 2) {
			return false;
		}
		return true;
	}

	public static function is_utf8($string) {
		return preg_match('%^(?:
				[\x09\x0A\x0D\x20-\x7E]# ASCII
				| [\xC2-\xDF][\x80-\xBF]# non-overlong 2-byte
				|  \xE0[\xA0-\xBF][\x80-\xBF]# excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}# straight 3-byte
				|  \xED[\x80-\x9F][\x80-\xBF]# excluding surrogates
				|  \xF0[\x90-\xBF][\x80-\xBF]{2}# planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3}# planes 4-15
				|  \xF4[\x80-\x8F][\x80-\xBF]{2}# plane 16
		)*$%xs', $string);
	}

}