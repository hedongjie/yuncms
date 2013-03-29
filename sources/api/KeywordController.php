<?php
/**
 * 关键词Api
 * @author Tongle Xu <xutongle@gmail.com>
 * @copyright Copyright (c) 2003-2103 Jinan TintSoft development co., LTD
 * @license http://www.tintsoft.com/html/about/copyright/
 * @version $Id: KeywordController.php 63 2013-02-27 01:02:27Z 85825770@qq.com $
 */
class KeywordController {

	public function __construct() {
		$this->charset = strtolower ( CHARSET );
	}

	public function get() {
		$number = isset ( $_GET ['number'] ) ? intval ( $_GET ['number'] ) : 3;
		$data = isset ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : exit ();
		//$result = $this->get_keywords ( $data, $number );
		$result = $this->get_sae ( $data, $number );
		exit ( $result );
	}

	/**
	 * 获取关键词
	 */
	private function get_sae($data, $number = 3) {
		$seg = new SaeSegment ();
		$ret = $seg->segment ( $data, 1 );
		if ($ret) {
			$return = '';
			$num = 1;
			foreach ( $ret as $val ) {
				if ($val ['word_tag'] == 95) {
					if ($num > 3) break;
					$return .= ' ' . $val ['word'];
					$num ++;
				}
			}
			return $return;
		} else {
			var_dump ( $seg->errno (), $seg->errmsg () );
		}
	}

	/**
	 * 获取关键词
	 *
	 * @param string $data 要分词的字符串
	 * @param int $number 数量
	 */
	private function get_keywords($data, $number = 3) {
		$data = trim ( strip_tags ( $data ) );
		if (empty ( $data )) return '';
		if ($this->charset != 'utf-8') {
			$data = iconv ( 'utf-8', CHARSET, $data );
		} else {
			$data = iconv ( 'utf-8', 'gbk', $data );
		}
		$result = Loader::lib ( 'HttpClient' )->post ( 'http://tool.phpcms.cn/api/get_keywords.php', array ('siteurl' => SITE_URL,'charset' => CHARSET,'data' => $data,'number' => $number ) );
		if ($result) {
			if ($this->charset != 'utf-8') {
				return $result;
			} else {
				return iconv ( 'gbk', 'utf-8', $result );
			}
		}
		return '';
	}
}