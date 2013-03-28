<?php
/**
 * 日期处理类
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-1
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Format.php 126 2013-03-24 15:16:26Z 85825770@qq.com $
 */
class Format {
	/**
	 * 日期格式化
	 *
	 * @param
	 *        	$timestamp
	 * @param
	 *        	$showtime
	 */
	public static function date($timestamp, $showtime = 0) {
		$times = intval ( $timestamp );
		if (! $times) return true;
		$lang = C ( 'system', 'lang' );
		if ($lang == 'zh-cn') {
			$str = $showtime ? date ( 'Y-m-d H:i:s', $times ) : date ( 'Y-m-d', $times );
		} else {
			$str = $showtime ? date ( 'm/d/Y H:i:s', $times ) : date ( 'm/d/Y', $times );
		}
		return $str;
	}

	/**
	 * 获取当前星期
	 *
	 * @param
	 *        	$timestamp
	 */
	public static function week($timestamp) {
		$times = intval ( $timestamp );
		if (! $times) return true;
		$weekarray = array (L ( 'Sunday' ),L ( 'Monday' ),L ( 'Tuesday' ),L ( 'Wednesday' ),L ( 'Thursday' ),L ( 'Friday' ),L ( 'Saturday' ) );
		return $weekarray [date ( "w", $timestamp )];
	}
}