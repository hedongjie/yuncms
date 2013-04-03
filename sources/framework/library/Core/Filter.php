<?php
/**
 * 核心过滤器
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-24
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Filter.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Core_Filter {
	private static $_allowtags = 'p|br|b|strong|hr|a|img|object|param|form|input|label|dl|dt|dd|div|font', $_allowattrs = 'id|class|align|valign|src|border|href|target|width|height|title|alt|name|action|method|value|type', $_disallowattrvals = 'expression|javascript:|behaviour:|vbscript:|mocha:|livescript:';

	public function __construct($allowtags = null, $allowattrs = null, $disallowattrvals = null) {
		if ($allowtags) self::$_allowtags = $allowtags;
		if ($allowattrs) self::$_allowattrs = $allowattrs;
		if ($disallowattrvals) self::$_disallowattrvals = $disallowattrvals;
	}

	public static function input() {
		if (get_magic_quotes_gpc()){
			$_POST = new_stripslashes($_POST);
			$_GET = new_stripslashes($_GET);
			$_COOKIE = new_stripslashes($_COOKIE);
			$_REQUEST = new_stripslashes($_REQUEST);
		}
	}

	public static function remove_xss($cleanxss = 1) {
		if (! defined ( 'IN_ADMIN' ) && $cleanxss) {
			$_POST = self::xss ( $_POST );
			$_GET = self::xss ( $_GET );
			$_COOKIE = self::xss ( $_COOKIE );
			$_REQUEST = self::xss ( $_REQUEST );
		}
	}

	public static function xss($string) {
		if (is_array ( $string )) {
			$string = array_map ( array ('self','xss' ), $string );
		} else {
			if (strlen ( $string ) > 20) {
				$string = self::_strip_tags ( $string );
			}
		}
		return $string;
	}

	public static function _strip_tags($string) {
		return preg_replace_callback ( "|(<)(/?)(\w+)([^>]*)(>)|", array ('self','_strip_attrs' ), $string );
	}

	public static function _strip_attrs($matches) {
		if (preg_match ( "/^(" . self::$_allowtags . ")$/", $matches [3] )) {
			if ($matches [4]) {
				preg_match_all ( "/\s(" . self::$_allowattrs . ")\s*=\s*(['\"]?)(.*?)\\2/i", $matches [4], $m, PREG_SET_ORDER );
				$matches [4] = '';
				foreach ( $m as $k => $v ) {
					if (! preg_match ( "/(" . self::$_disallowattrvals . ")/", $v [3] )) {
						$matches [4] .= $v [0];
					}
				}
			}
		} else {
			$matches [1] = '&lt;';
			$matches [5] = '&gt;';
		}
		unset ( $matches [0] );
		return implode ( '', $matches );
	}
}