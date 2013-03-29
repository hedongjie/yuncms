<?php
/**
 * 分页类
 * @author Tongle Xu <xutongle@gmail.com> 2012-12-26
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: Page.php 2 2013-01-14 07:14:05Z xutongle $
 */
class Page {

	/**
	 * URL路径解析，pages 函数的辅助函数
	 *
	 * @param string $par 传入需要解析的变量 默认为，page={$page}
	 * @param string $url URL地址
	 * @return URL
	 */
	public static function url_par($par, $url = '') {
		if ($url == '') $url = Core_Request::get_request_uri ();
		$pos = strpos ( $url, '?' );
		if ($pos === false) {
			$url .= '?' . $par;
		} else {
			$querystring = substr ( strstr ( $url, '?' ), 1 );
			parse_str ( $querystring, $pars );
			$query_array = array ();
			foreach ( $pars as $k => $v ) {
				if ($k == 'page') continue;
				$query_array [$k] = $v;
			}
			if ($query_array)
				$querystring = http_build_query ( $query_array ) . '&' . $par;
			else
				$querystring = $par;
			$url = substr ( $url, 0, $pos ) . '?' . $querystring;
		}
		return $url;
	}

	/**
	 * 返回分页路径
	 *
	 * @param string $urlrule 分页规则
	 * @param int $page 当前页
	 * @param array $array 需要传递的数组，用于增加额外的方法
	 * @return 完整的URL路径
	 */
	public static function pageurl($urlrule, $page, $array = array()) {
		if (strpos ( $urlrule, '~' )) {
			$urlrules = explode ( '~', $urlrule );
			$urlrule = $page < 2 ? $urlrules [0] : $urlrules [1];
		}
		$findme = array (
				'{$page}'
		);
		$replaceme = array (
				$page
		);
		if (is_array ( $array )) foreach ( $array as $k => $v ) {
			$findme [] = '{$' . $k . '}';
			$replaceme [] = $v;
		}
		$url = str_replace ( $findme, $replaceme, $urlrule );
		$url = str_replace ( array (
				'http://',
				'//',
				'~'
		), array (
				'~',
				'/',
				'http://'
		), $url );
		return $url;
	}

	/**
	 * 分页函数
	 *
	 * @param int $num 信息总数
	 * @param int $curr_page 当前分页
	 * @param int $perpage 每页显示数
	 * @param string $urlrule URL规则
	 * @param array $array 需要传递的数组，用于增加额外的方法
	 * @return 分页
	 */
	public static function pages($num, $curr_page, $perpage = 20, $urlrule = '', $array = array(), $setpages = 10) {
		if (defined ( 'URLRULE' ) && $urlrule == '') {
			$urlrule = URLRULE;
			$array = $GLOBALS ['URL_ARRAY'];
		} elseif ($urlrule == '') {
			$urlrule = self::url_par ( 'page={$page}' );
		}
		$multipage = '';
		if ($num > $perpage) {
			$page = $setpages + 1;
			$offset = ceil ( $setpages / 2 - 1 );
			$pages = ceil ( $num / $perpage );
			$from = $curr_page - $offset;
			$to = $curr_page + $offset;
			$more = 0;
			if ($page >= $pages) {
				$from = 2;
				$to = $pages - 1;
			} else {
				if ($from <= 1) {
					$to = $page - 1;
					$from = 2;
				} elseif ($to >= $pages) {
					$from = $pages - ($page - 2);
					$to = $pages - 1;
				}
				$more = 1;
			}
			$multipage .= '<a class="a1">' . $num . L ( 'page_item' ) . '</a>';
			if ($curr_page > 0) {
				$multipage .= ' <a href="' . self::pageurl ( $urlrule, $curr_page - 1, $array ) . '" class="a1">' . L ( 'previous' ) . '</a>';
				if ($curr_page == 1) {
					$multipage .= ' <span>1</span>';
				} elseif ($curr_page > 6 && $more) {
					$multipage .= ' <a href="' . self::pageurl ( $urlrule, 1, $array ) . '">1</a>..';
				} else {
					$multipage .= ' <a href="' . self::pageurl ( $urlrule, 1, $array ) . '">1</a>';
				}
			}
			for($i = $from; $i <= $to; $i ++) {
				if ($i != $curr_page) {
					$multipage .= ' <a href="' . self::pageurl ( $urlrule, $i, $array ) . '">' . $i . '</a>';
				} else {
					$multipage .= ' <span>' . $i . '</span>';
				}
			}
			if ($curr_page < $pages) {
				if ($curr_page < $pages - 5 && $more) {
					$multipage .= ' ..<a href="' . self::pageurl ( $urlrule, $pages, $array ) . '">' . $pages . '</a> <a href="' . self::pageurl ( $urlrule, $curr_page + 1, $array ) . '" class="a1">' . L ( 'next' ) . '</a>';
				} else {
					$multipage .= ' <a href="' . self::pageurl ( $urlrule, $pages, $array ) . '">' . $pages . '</a> <a href="' . self::pageurl ( $urlrule, $curr_page + 1, $array ) . '" class="a1">' . L ( 'next' ) . '</a>';
				}
			} elseif ($curr_page == $pages) {
				$multipage .= ' <span>' . $pages . '</span> <a href="' . self::pageurl ( $urlrule, $curr_page, $array ) . '" class="a1">' . L ( 'next' ) . '</a>';
			} else {
				$multipage .= ' <a href="' . self::pageurl ( $urlrule, $pages, $array ) . '">' . $pages . '</a> <a href="' . self::pageurl ( $urlrule, $curr_page + 1, $array ) . '" class="a1">' . L ( 'next' ) . '</a>';
			}
		}
		return $multipage;
	}
}