<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-1-6
 * @copyright Copyright (c) 2003-2103 www.tintsoft.com
 * @version $Id: ContentPage.php 2 2013-01-14 07:14:05Z xutongle $
 */
class ContentPage {

	private $html_tag = array (); // HTML标记数组
	private $html_end_tag = array (); // HTML结束标记数组

	/**
	 * 临时内容存储器
	 *
	 * @var string
	 */
	public $content = '';

	/**
	 * 内容存储
	 *
	 * @var array
	 */
	private $data = array ();

	public function __construct() {
		// 定义HTML数组
		$this->html_tag = array ('p','div','h','span','strong','ul','ol','li','table','tr','tbody','dl','dt','dd' );
		$this->html_end_tag = array ('/p','/div','/h','/span','/strong','/ul','/ol','/li','/table','/tr','/tbody','/dl','/dt','/dd' );
		$this->content = '';
		$this->data = array ();
	}

	public function get($content = '', $size = 5) {
		if (! $content) return '';
		$size = ( int ) $size * 1024;
		$str_len_word = strlen ( $content ); // 获取使用strlen得到的字符总数
		if ($str_len_word < $size) { // 不足一页直接返回
			return $content;
		}
		if (strpos ( $content, '<' ) !== false) { // HTML自动分页
			$content = stripslashes ( $content );
			$this->data = explode ( '<', $content );

			$is_table = 0;
			$body = '';
			foreach ( $this->data as $i => $k ) {
				if ($i == 0) {
					$body .= $this->data [$i];
					continue;
				}
				$this->data [$i] = "<" . $this->data [$i];
				if (strlen ( $this->data [$i] ) > 6) {
					$tname = substr ( $this->data [$i], 1, 5 );
					if (strtolower ( $tname ) == 'table') {
						$is_table ++;
					} else if (strtolower ( $tname ) == '/tabl') {
						$is_table --;
					}
					if ($is_table > 0) {
						$body .= $this->data [$i];
						continue;
					} else {
						$body .= $this->data [$i];
					}
				} else {
					$body .= $this->data [$i];
				}
				if (strlen ( $body ) > $size) {
					$this->content .= $body . '[page]';
					print_r($this->content);

					//if ($this->data [$i]) {
					//	// 没有结尾跳过去
					//	continue;
					//} else {
						$this->content .= $body . '[page]';
					//	$body = '';
					//}
				}
			}
			if ($body != '') {
				$this->content .= $body;
			}
		} else {

		}exit;
		return $this->content;
	}

	/**
	 * 补全HTML标签
	 *
	 * @param string $html
	 * @return string
	 * @author Milian Wolff <mail@milianw.de>
	 */
	private function closetags($html) {
		// 不需要补全的标签
		$arr_single_tags = array ('meta','img','br','link','area' );
		// 匹配开始标签
		preg_match_all ( '#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result );
		$openedtags = $result [1];
		// 匹配关闭标签
		preg_match_all ( '#</([a-z]+)>#iU', $html, $result );
		$closedtags = $result [1];
		// 计算关闭标签数量相同返回html
		$len_opened = count ( $openedtags );
		if (count ( $closedtags ) == $len_opened) {
			return $html;
		}
		// 反排序数组，将最后一个开启的标签放到最前面
		$openedtags = array_reverse ( $openedtags );
		// 遍历开始标签数组
		for($i = 0; $i < $len_opened; $i ++) {
			// 如果标签不属于不需要补全的标签
			if (! in_array ( $openedtags [$i], $arr_single_tags )) {
				// 如果这个标签不在关闭的标签中
				if (! in_array ( $openedtags [$i], $closedtags )) {
					// 如果在这个标签之后还有开启的标签
					if ($next_tag = $openedtags [$i + 1]) {
						// 将当前标签放在下一个标签的关闭标签的前面
						$html = preg_replace ( '#</' . $next_tag . '#iU', '</' . $openedtags [$i] . '></' . $next_tag, $html );
					} else {
						// 直接补全闭合标签
						$html .= '</' . $openedtags [$i] . '>';
					}
				}
			}
		}
		return $html;
	}
}