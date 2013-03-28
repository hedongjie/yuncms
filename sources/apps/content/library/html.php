<?php
/**
 * 栏目、内容HTML处理类
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-3
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: html.php 479 2012-11-27 17:36:40Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class html {
	private $url, $html_root, $categorys;
	public function __construct() {
		define ( 'HTML', true );
		$this->categorys = S ( 'common/category_content' );
		$this->url = Loader::lib ( 'content:url' );
		$this->html_root = C ( 'system', 'html_root' );
	}

	/**
	 * 生成内容页
	 *
	 * @param $file 文件地址
	 * @param $data 数据
	 * @param $array_merge 是否合并
	 * @param $action 方法
	 * @param $upgrade 是否是升级数据
	 */
	public function show($file, $data = '', $array_merge = 1, $action = 'add', $upgrade = 0) {
		if ($upgrade) $file = '/' . ltrim ( $file, WEB_PATH );
		$allow_visitor = 1;
		$id = $data ['id'];
		if ($array_merge) {
			$data = new_stripslashes ( $data );
			$data = array_merge ( $data ['system'], $data ['model'] );
		}
		// 通过rs获取原始值
		$rs = $data;
		if (isset ( $data ['paginationtype'] )) {
			$paginationtype = $data ['paginationtype'];
			$maxcharperpage = $data ['maxcharperpage'];
		} else {
			$paginationtype = 0;
		}
		$catid = $data ['catid'];
		$CATEGORYS = $this->categorys;
		$CAT = $CATEGORYS [$catid];
		$CAT ['setting'] = unserialize ( $CAT ['setting'] );
		define ( 'STYLE', $CAT ['setting'] ['template_list'] );

		// 最顶级栏目ID
		$arrparentid = explode ( ',', $CAT ['arrparentid'] );
		$top_parentid = $arrparentid [1] ? $arrparentid [1] : $catid;

		$modelid = $CAT ['modelid'];
		require_once CACHE_MODEL_PATH . 'content_output.php';
		$content_output = new content_output ( $modelid, $catid, $CATEGORYS );
		$output_data = $content_output->get ( $data );
		extract ( $output_data );
		if (application_exists ( 'comment' )) {
			$allow_comment = isset ( $allow_comment ) ? $allow_comment : 1;
		} else {
			$allow_comment = 0;
		}
		$this->db = Loader::model ( 'content_model' );
		$this->db->set_model ( $modelid );
		// 上一页
		$previous_page = $this->db->order ( 'id DESC' )->where ( array ('status' => '99','id' => array ('lt',$id ) ) )->find ();
		// 下一页
		$next_page = $this->db->where ( array ('status' => '99','id' => array ('gt',$id ) ) )->find ();
		if (empty ( $previous_page )) {
			$previous_page = array ('title' => L ( 'first_page', '', 'content' ),'thumb' => IMG_PATH . 'nopic_small.gif','url' => 'javascript:alert(\'' . L ( 'first_page', '', 'content' ) . '\');' );
		}
		if (empty ( $next_page )) {
			$next_page = array ('title' => L ( 'last_page', '', 'content' ),'thumb' => IMG_PATH . 'nopic_small.gif','url' => 'javascript:alert(\'' . L ( 'last_page', '', 'content' ) . '\');' );
		}

		$title = strip_tags ( $title );
		// SEO
		$seo_keywords = '';
		if (! empty ( $keywords )) $seo_keywords = implode ( ',', $keywords );
		$SEO = seo ( $catid, $title, $description, $seo_keywords );

		$ishtml = 1;
		$template = $template ? $template : $CAT ['setting'] ['show_template'];

		// 分页处理
		$pages = $titles = '';
		if ($paginationtype == 1) {
			// 自动分页
			if ($maxcharperpage < 10) $maxcharperpage = 500;
			$contentpage = Loader::lib ( 'content:contentpage' );
			$content = $contentpage->get_data ( $content, $maxcharperpage );
		}

		if ($paginationtype != 0) {
			// 手动分页
			$CONTENT_POS = strpos ( $content, '[page]' );
			if ($CONTENT_POS !== false) {
				$this->url = Loader::lib ( 'content:url', 'content' );
				$contents = array_filter ( explode ( '[page]', $content ) );
				$pagenumber = count ( $contents );
				if (strpos ( $content, '[/page]' ) !== false && ($CONTENT_POS < 7)) {
					$pagenumber --;
				}
				for($i = 1; $i <= $pagenumber; $i ++) {
					$upgrade = $upgrade ? '/' . ltrim ( $file, WEB_PATH ) : '';
					$pageurls [$i] = $this->url->show ( $id, $i, $catid, $data ['inputtime'], $data ['prefix'], '', 'edit', $upgrade );
				}
				$END_POS = strpos ( $content, '[/page]' );
				if ($END_POS !== false) {
					if ($CONTENT_POS > 7) {
						$content = '[page]' . $title . '[/page]' . $content;
					}
					if (preg_match_all ( "|\[page\](.*)\[/page\]|U", $content, $m, PREG_PATTERN_ORDER )) {
						foreach ( $m [1] as $k => $v ) {
							$p = $k + 1;
							$titles [$p] ['title'] = strip_tags ( $v );
							$titles [$p] ['url'] = $pageurls [$p] [0];
						}
					}
				}
				// 生成分页
				foreach ( $pageurls as $page => $urls ) {
					$pages = content_pages ( $pagenumber, $page, $pageurls );
					// 判断[page]出现的位置是否在第一位
					if ($CONTENT_POS < 7) {
						$content = $contents [$page];
					} else {
						if ($page == 1 && ! empty ( $titles )) {
							$content = $title . '[/page]' . $contents [$page - 1];
						} else {
							$content = $contents [$page - 1];
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
					$pagefile = $urls [1];
					$pagefile = BASE_PATH . $pagefile;
					ob_start ();
					include template ( 'content', $template );
					$this->createhtml ( $pagefile );
				}
				return true;
			}
		}
		// 分页处理结束
		$file = BASE_PATH . $file;
		ob_start ();
		include template ( 'content', $template );
		return $this->createhtml ( $file );
	}

	/**
	 * 生成栏目列表
	 *
	 * @param $catid 栏目id
	 * @param $page 当前页数
	 */
	public function category($catid, $page = 0) {
		$CAT = $this->categorys [$catid];
		@extract ( $CAT );
		if (! $ishtml) return false;
		if (! $catid) showmessage ( L ( 'category_not_exists', 'content' ), 'blank' );
		$CATEGORYS = $this->categorys;
		if (! isset ( $CATEGORYS [$catid] )) showmessage ( L ( 'information_does_not_exist', 'content' ), 'blank' );
		$copyjs = '';
		$setting = string2array ( $setting );
		if (! $setting ['meta_title']) $setting ['meta_title'] = $catname;
		$SEO = seo ( '', $setting ['meta_title'], $setting ['meta_description'], $setting ['meta_keywords'] );
		define ( 'STYLE', $setting ['template_list'] );

		$page = intval ( $page );
		$parentdir = $CAT ['parentdir'];
		$catdir = $CAT ['catdir'];
		// 检查是否生成到根目录
		$create_to_html_root = $CAT ['sethtml'];
		// $base_file = $parentdir.$catdir.'/';
		// 生成地址
		if ($CAT ['create_to_html_root']) $parentdir = '';
		$base_file = $this->url->get_list_url ( $setting ['category_ruleid'], $parentdir, $catdir, $catid, $page );
		$base_file = '/' . $base_file;

		// 判断二级域名是否直接绑定到该栏目
		$root_domain = preg_match ( '/^((http|https):\/\/)([a-z0-9\-\.]+)\/$/', $CAT ['url'] ) ? 1 : 0;
		$count_number = substr_count ( $CAT ['url'], '/' );
		$urlrules = S ( 'common/urlrule' );
		$urlrules = explode ( '|', $urlrules [$category_ruleid] );

		if ($create_to_html_root) {
			$file = BASE_PATH . $base_file;
			// 评论跨站调用所需的JS文件
			if (substr ( $base_file, - 10 ) == 'index.html' && $count_number == 3) {
				$copyjs = 1;
			}
			// URLRULES
			foreach ( $urlrules as $_k => $_v ) {
				$urlrules [$_k] = '/' . $_v;
			}
		} else {
			$file = BASE_PATH . substr ( $this->html_root, 1 ) . $base_file;
			// 评论跨站调用所需的JS文件
			if (substr ( $base_file, - 10 ) == 'index.html' && $count_number == 3) {
				$copyjs = 1;
			}
			// URLRULES
			$htm_prefix = $root_domain ? '' : $this->html_root;
			$htm_prefix = rtrim ( WEB_PATH, '/' ) . $htm_prefix;
			if ($CAT ['isdomain']) {
				$second_domain = 1;
			} else {
				$second_domain = 0; // 判断该栏目是否绑定了二级域名或者上级栏目绑定了二级域名，存在的话，重新构造列表页url规则
				foreach ( $urlrules as $_k => $_v ) {
					$urlrules [$_k] = $htm_prefix . '/' . $_v;
				}
			}
		}

		if ($type == 0) {
			$template = $setting ['category_template'] ? $setting ['category_template'] : 'category';
			$template_list = $setting ['list_template'] ? $setting ['list_template'] : 'list';
			$template = $child ? $template : $template_list;
			$arrparentid = explode ( ',', $arrparentid );
			$top_parentid = $arrparentid [1] ? $arrparentid [1] : $catid;
			$array_child = array ();
			$self_array = explode ( ',', $arrchildid );
			foreach ( $self_array as $arr ) {
				if ($arr != $catid) $array_child [] = $arr;
			}
			$arrchildid = implode ( ',', $array_child );
			// URL规则
			$urlrules = implode ( '~', $urlrules );

			define ( 'URLRULE', $urlrules );
			// 绑定域名时，设置$catdir 为空
			if ($root_domain) $parentdir = $catdir = '';
			if ($second_domain) {
				$parentdir = '';
				$parentdir = str_replace ( $catdir . '/', '', $CAT ['url'] );
			}

			$GLOBALS ['URL_ARRAY'] = array ('categorydir' => $parentdir,'catdir' => $catdir,'catid' => $catid );
		} else {
			// 单网页
			$datas = $this->page ( $catid );
			if ($datas) extract ( $datas );
			$template = $setting ['page_template'] ? $setting ['page_template'] : 'page';
			$parentid = $CATEGORYS [$catid] ['parentid'];
			$arrchild_arr = $CATEGORYS [$parentid] ['arrchildid'];
			if ($arrchild_arr == '') $arrchild_arr = $CATEGORYS [$catid] ['arrchildid'];
			$arrchild_arr = explode ( ',', $arrchild_arr );
			array_shift ( $arrchild_arr );
			$keywords = $keywords ? $keywords : $setting ['meta_keywords'];
			$SEO = seo ( 0, $title, $setting ['meta_description'], $keywords );
		}
		ob_start ();
		include template ( 'content', $template );
		return $this->createhtml ( $file, $copyjs );
	}

	/**
	 * 更新首页
	 */
	public function index() {
		$file = BASE_PATH . 'index.html';
		$SEO = seo (); // SEO
		$CATEGORYS = $this->categorys;
		ob_start ();
		include template ( 'content', 'index', C ( 'template', 'name' ) );
		return $this->createhtml ( $file, 1 );
	}

	/**
	 * 单网页
	 *
	 * @param $catid
	 */
	public function page($catid) {
		$this->page_db = Loader::model ( 'page_model' );
		$data = $this->page_db->getby_catid ( $catid );
		return $data;
	}

	/**
	 * 写入文件
	 *
	 * @param $file 文件路径
	 * @param $copyjs 是否复制js，跨站调用评论时，需要该js
	 */
	private function createhtml($file, $copyjs = '') {
		$data = ob_get_contents ();
		ob_clean ();
		$dir = dirname ( $file );
		if (! is_dir ( $dir )) {
			mkdir ( $dir, 0777, 1 );
		}
		if ($copyjs && ! file_exists ( $dir . '/js.html' )) {
			@copy ( APPS_PATH . 'content/template/js.html', $dir . '/js.html' );
		}
		$strlen = file_put_contents ( $file, $data );
		@chmod ( $file, 0777 );
		if (! is_writable ( $file )) {
			$file = str_replace ( BASE_PATH, '', $file );
			showmessage ( L ( 'file' ) . '：' . $file . '<br>' . L ( 'not_writable' ) );
		}
		return $strlen;
	}

	/**
	 * 生成相关栏目列表、只生成前5页
	 *
	 * @param $catid
	 */
	public function create_relation_html($catid) {
		for($page = 1; $page < 6; $page ++) {
			$this->category ( $catid, $page );
		}
		// 检查当前栏目的父栏目，如果存在则生成
		$arrparentid = $this->categorys [$catid] ['arrparentid'];
		if ($arrparentid) {
			$arrparentid = explode ( ',', $arrparentid );
			foreach ( $arrparentid as $catid ) {
				if ($catid) $this->category ( $catid, 1 );
			}
		}
	}
}