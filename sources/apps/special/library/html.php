<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: html.php 883 2012-06-13 06:05:36Z 85825770@qq.com $
 */
class html {
	private $db, $type_db, $c_db, $data_db, $site, $queue, $html_root;
	public function __construct() {
		$this->db = Loader::model ( 'special_model' ); // 专题数据模型
		$this->type_db = Loader::model ( 'type_model' ); // 专题分类数据模型
		$this->c_db = Loader::model ( 'special_content_model' ); // 专题内容数据模型
		$this->data_db = Loader::model ( 'special_c_data_model' );
		$this->html_root = C ( 'system', 'html_root' );
		define ( 'HTML', true );
	}

	/**
	 * 生成文章静态页
	 *
	 * @param intval $contentid
	 *        	文章ID
	 * @return string
	 */
	public function _create_content($contentid = 0) {
		if (! $contentid) return false;
		Loader::helper ( 'special:global' );
		$r = $this->c_db->getby_id ( $contentid );
		$_special = $s_info = $this->db->getby_id ( $r ['specialid'] );
		if ($s_info ['ishtml'] == 0) return content_url ( $contentid, '1', 0, 'php' );
		unset ( $arr_content );
		$arr_content = $this->data_db->getby_id ( $contentid );
		@extract ( $r );
		$title = strip_tags ( $title );
		if ($arr_content ['paginationtype']) {
			// 文章使用分页时
			if ($arr_content ['paginationtype'] == 1) {
				if (strpos ( $arr_content ['content'], '[/page]' ) !== false) {
					$arr_content ['content'] = preg_replace ( "|\[page\](.*)\[/page\]|U", '', $arr_content ['content'] );
				}
				if (strpos ( $arr_content ['content'], '[page]' ) !== false) {
					$arr_content ['content'] = str_replace ( '[page]', '', $data ['content'] );
				}
				$contentpage = Loader::lib ( 'content:contentpage' ); // 调用自动分页类
				$arr_content ['content'] = $contentpage->get_data ( $arr_content ['content'], $arr_content ['maxcharperpage'] ); // 自动分页，自动添加上[page]
			}
		} else {
			if (strpos ( $arr_content ['content'], '[/page]' ) !== false) {
				$arr_content ['content'] = preg_replace ( "|\[page\](.*)\[/page\]|U", '', $arr_content ['content'] );
			}
			if (strpos ( $arr_content ['content'], '[page]' ) !== false) {
				$arr_content ['content'] = str_replace ( '[page]', '', $arr_content ['content'] );
			}
		}
		$template = $arr_content ['show_template'] ? $arr_content ['show_template'] : 'show'; // 调用模板
		$CONTENT_POS = strpos ( $arr_content ['content'], '[page]' );
		if ($CONTENT_POS !== false) {
			$contents = array_filter ( explode ( '[page]', $arr_content ['content'] ) );
			$pagenumber = count ( $contents );
			$END_POS = strpos ( $arr_content ['content'], '[/page]' );
			if ($END_POS !== false && ($CONTENT_POS < 7)) {
				$pagenumber --;
			}
			for($i = 1; $i <= $pagenumber; $i ++) {
				$pageurls [$i] = content_url ( $contentid, $i, $inputtime, 'html', $site_info );
			}
			if ($END_POS !== false) {
				if ($CONTENT_POS > 7) {
					$arr_content ['content'] = '[page]' . $title . '[/page]' . $arr_content ['content'];
				}
				if (preg_match_all ( "|\[page\](.*)\[/page\]|U", $arr_content ['content'], $m, PREG_PATTERN_ORDER )) {
					foreach ( $m [1] as $k => $v ) {
						$p = $k + 1;
						$titles [$p] ['title'] = strip_tags ( $v );
						$titles [$p] ['url'] = $pageurls [$p] [1];
					}
				}
			}
			$currentpage = $filesize = 0;
			for($i = 1; $i <= $pagenumber; $i ++) {
				$currentpage ++;
				// 判断[page]出现的位置是否在第一位
				if ($CONTENT_POS < 7) {
					$content = $contents [$currentpage];
				} else {
					if ($currentpage == 1 && ! empty ( $titles )) {
						$content = $title . '[/page]' . $contents [$currentpage - 1];
					} else {
						$content = $contents [$currentpage - 1];
					}
				}
				if ($titles) {
					list ( $title, $content ) = explode ( '[/page]', $content );
					$content = trim ( $content );
					if (strpos ( $content, '</p>' ) === 0) {
						$content = '<p>' . $content;
					}
					if (stripos ( $content, '<p>' ) === 0) {
						$content = $content . '</p>';
					}
				}
				$file_url = content_url ( $contentid, $currentpage, $inputtime, 'html', $site_info );
				if ($currentpage == 1) $urls = $file_url;
				Loader::helper ( 'content:util' );
				$title_pages = content_pages ( $pagenumber, $currentpage, $pageurls );
				$SEO = seo ( '', $title );
				$file = $file_url [1];
				$file = BASE_PATH . $file; // 生成文件的路径
				ob_start ();
				include template ( 'special', $template );
				$this->create_html ( $file );
			}
		} else {
			$page = 1;
			$title = strip_tags ( $title );
			$SEO = seo ( '', $title );
			$content = $arr_content ['content'];
			$urls = content_url ( $contentid, $page, $inputtime, 'html', $site_info );
			$file = $urls [1];
			$file = BASE_PATH . $file;
			ob_start ();
			include template ( 'special', $template );
			$this->create_html ( $file );
		}
		// $this->_index($specialid, 20, 5); //更新专题首页
		// $this->_list($typeid, 20, 5); //更新所在的分类页
		return $urls;
	}

	/**
	 * 生成静态文件
	 *
	 * @param string $file
	 *        	文件路径
	 * @return boolen/intval 成功返回生成文件的大小
	 */
	private function create_html($file) {
		$data = ob_get_contents ();
		ob_end_clean ();
		Folder::create ( dirname ( $file ) );
		$strlen = File::write ( $file, $data );
		return $strlen;
	}

	/**
	 * 生成专题首页
	 *
	 * @param intval $specialid
	 *        	专题ID
	 * @param intval $pagesize
	 *        	每页个数
	 * @param intval $pages_num
	 *        	最大更新页数
	 * @return boolen/intval 成功返回生成文件的大小
	 */
	public function _index($specialid = 0, $pagesize = 20, $pages_num = 0) {
		Loader::helper ( 'special:global' );
		$specialid = intval ( $specialid );
		if (! $specialid) return false;
		$r = $this->db->getby_id ( $specialid );
		if (! $r ['ishtml'] || $r ['disabled'] != 0) return true;
		if (! $specialid) showmessage ( L ( 'illegal_action' ) );
		$info = $this->db->getby_id ( $specialid );
		if (! $info) showmessage ( L ( 'special_not_exist' ), 'back' );
		extract ( $info );
		if ($pics) {
			$pic_data = get_pic_content ( $pics );
			unset ( $pics );
		}
		if ($voteid) {
			$vote_info = explode ( '|', $voteid );
			$voteid = $vote_info [1];
		}
		$commentid = id_encode ( 'special', $id );
		$file = $this->html_root . '/special/' . $filename . '/index.html';
		if (! $ispage) {
			$type_db = Loader::model ( 'type_model' );
			$types = $type_db->where(array ('application' => 'special','parentid' => $specialid ))->order('listorder ASC, typeid ASC')->key('listorder')->select ();
		}
		$css = get_css ( unserialize ( $css ) );
		$template = $index_template ? $index_template : 'index';
		$SEO = seo ( '', $title, $description );
		if ($ispage) {
			$total = $this->c_db->where(array ('specialid' => $specialid ))->count();
			$times = ceil ( $total / $pagesize );
			if ($pages_num)
				$pages_num = min ( $times, $pages_num );
			else
				$pages_num = $times;
			for($i = 1; $i <= $pages_num; $i ++) {
				if ($i == 1)
					$file_root = $file;
				else
					$file_root = str_replace ( 'index', 'index-' . $i, $file );
				$file_root = BASE_PATH . $file_root;
				ob_start ();
				include template ( 'special', $template );
				$this->create_html ( $file_root );
			}
			return true;
		} else {
			$file = BASE_PATH . $file;
			ob_start ();
			include template ( 'special', $template, $style );
			return $this->create_html ( $file );
		}
	}

	/**
	 * 生成列表页
	 */
	public function create_list() {
		$file = $this->html_root . '/special/index.html';
		$file = BASE_PATH . $file;
		ob_start ();
		include template ( 'special', 'special_list' );
		return $this->create_html ( $file );
	}

	/**
	 * 生成分类页
	 *
	 * @param intval $typeid
	 *        	分类ID
	 * @param intval $page
	 *        	页数
	 */
	public function create_type($typeid = 0, $page = 1) {
		if (! $typeid) return false;
		$info = $this->type_db->getby_typeid ( $typeid );
		$s_info = $this->db->getby_id ( $info ['parentid'] );
		extract ( $s_info );
		define ( 'URLRULE', SITE_URL . substr ( $this->html_root, 1 ) . '/special/{$specialdir}/{$typedir}/type-{$typeid}.html~' . SITE_URL . substr ( $this->html_root, 1 ) . '/special/{$specialdir}/{$typedir}/type-{$typeid}-{$page}.html' );
		$GLOBALS ['URL_ARRAY'] = array ('specialdir' => $filename,'typedir' => $info ['typedir'],'typeid' => $typeid );
		$SEO = seo ( '', $info ['typename'], '' );
		$template = $list_template ? $list_template : 'list';
		if ($page == 1)
			$file = $this->html_root . '/special/' . $filename . '/' . $info ['typedir'] . '/type-' . $typeid . '.html';
		else
			$file = $this->html_root . '/special/' . $filename . '/' . $info ['typedir'] . '/type-' . $typeid . '-' . $page . '.html';
		$file = BASE_PATH . $file;
		ob_start ();
		include template ( 'special', $template );
		$this->create_html ( $file );
	}

	/**
	 * 生成分类静态页
	 *
	 * @param intval $typeid
	 *        	分类ID
	 * @param intval $pagesize
	 *        	每页篇数
	 * @param intval $pages
	 *        	最大更新页数
	 */
	public function _list($typeid = 0, $pagesize = 20, $pages = 0) {
		if (! $typeid) return false;
		$total = $this->c_db->where(array ('typeid' => $typeid ))->count(  );
		$times = ceil ( $total / $pagesize );
		if ($pages)
			$pages = min ( $times, $pages );
		else
			$pages = $times;
		for($i = 1; $i <= $pages; $i ++) {
			$this->create_type ( $typeid, $i );
		}
		return true;
	}
}