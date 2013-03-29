<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * $Id: IndexController.php 479 2012-11-27 17:36:40Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
// 模型缓存路径
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::helper( 'content:util' );
error_reporting ( E_ERROR );
class IndexController {
	private $db;

	public function __construct() {
		$this->db = Loader::model ( 'content_model' );
		$this->_userid = cookie ( '_userid' );
		$this->_username = cookie ( '_username' );
		$this->_groupid = cookie ( '_groupid' );
	}

	/**
	 * 首页
	 */
	public function init() {
		$_userid = $this->_userid;
		$_username = $this->_username;
		$_groupid = $this->_groupid;
		// SEO
		$SEO = seo ();
		$default_style = C ( 'template', 'name' );
		$CATEGORYS = S ( 'common/category_content' );
		include template ( 'content', 'index', $default_style );
	}

	/**
	 * 内容页
	 */
	public function show() {
		$catid = intval ( $_GET ['catid'] );
		$id = intval ( $_GET ['id'] );

		if (! $catid || ! $id) showmessage ( L ( 'information_does_not_exist' ), 'blank' );
		$_userid = $this->_userid;
		$_username = $this->_username;
		$_groupid = $this->_groupid;

		$page = isset ( $_GET ['page'] ) ? max ( intval ( $_GET ['page'] ), 1 ) : 1;
		$CATEGORYS = S ( 'common/category_content' );

		if (! isset ( $CATEGORYS [$catid] ) || $CATEGORYS [$catid] ['type'] != 0) showmessage ( L ( 'information_does_not_exist' ), 'blank' );
		$this->category = $CAT = $CATEGORYS [$catid];
		$this->category_setting = $CAT ['setting'] = string2array ( $this->category ['setting'] );
		$MODEL = S ( 'common/model' );
		$modelid = $CAT ['modelid'];

		$tablename = $this->db->table_name = $this->db->get_prefix () . $MODEL [$modelid] ['tablename'];
		$r = $this->db->getby_id ($id );
		if (! $r || $r ['status'] != 99) showmessage ( L ( 'info_does_not_exists' ), 'blank' );

		$this->db->table_name = $tablename . '_data';
		$r2 = $this->db->getby_id ( $id );
		$rs = $r2 ? array_merge ( $r, $r2 ) : $r;

		// 再次重新赋值，以数据库为准
		$catid = $CATEGORYS [$r ['catid']] ['catid'];
		$modelid = $CATEGORYS [$catid] ['modelid'];

		require_once CACHE_MODEL_PATH . 'content_output.php';
		$content_output = new content_output ( $modelid, $catid, $CATEGORYS );
		$data = $content_output->get ( $rs );
		extract ( $data );

		// 检查文章会员组权限
		if ($groupids_view && is_array ( $groupids_view )) {
			$_groupid = cookie ( '_groupid' );
			$_groupid = intval ( $_groupid );
			if (! $_groupid) {
				$forward = urlencode ( Core_Request::get_url () );
				showmessage ( L ( 'login_website' ), U ( 'member/passport/login', array ('forward' => $forward ) ) );
			}
			if (! in_array ( $_groupid, $groupids_view )) showmessage ( L ( 'no_priv' ) );
		} else {
			// 根据栏目访问权限判断权限
			$_priv_data = $this->_category_priv ( $catid );
			if ($_priv_data == '-1') {
				$forward = urlencode ( Core_Request::get_url () );
				showmessage ( L ( 'login_website' ), U ( 'member/passport/login', array ('forward' => $forward ) ) );
			} elseif ($_priv_data == '-2') {
				showmessage ( L ( 'no_priv' ) );
			}
		}
		if (application_exists ( 'comment' )) {
			$allow_comment = isset ( $allow_comment ) ? $allow_comment : 1;
		} else {
			$allow_comment = 0;
		}
		// 阅读收费 类型
		$paytype = $rs ['paytype'];
		$readpoint = $rs ['readpoint'];
		$allow_visitor = 1;
		if ($readpoint || $this->category_setting ['defaultchargepoint']) {
			if (! $readpoint) {
				$readpoint = $this->category_setting ['defaultchargepoint'];
				$paytype = $this->category_setting ['paytype'];
			}

			// 检查是否支付过
			$allow_visitor = self::_check_payment ( $catid . '_' . $id, $paytype );
			if (! $allow_visitor) {
				$http_referer = urlencode ( Core_Request::get_url () );
				$allow_visitor = authcode ( $catid . '_' . $id . '|' . $readpoint . '|' . $paytype ) . '&http_referer=' . $http_referer;
			} else {
				$allow_visitor = 1;
			}
		}
		// 最顶级栏目ID
		$arrparentid = explode ( ',', $CAT ['arrparentid'] );
		$top_parentid = isset ( $arrparentid [1] ) ? $arrparentid [1] : $catid;

		$template = $template ? $template : $CAT ['setting'] ['show_template'];
		if (! $template) $template = 'show';
		// SEO
		$seo_keywords = '';
		if (! empty ( $keywords )) $seo_keywords = implode ( ',', $keywords );
		$SEO = seo ( $catid, $title, $description, $seo_keywords );

		define ( 'STYLE', $CAT ['setting'] ['template_list'] );
		if (isset ( $rs ['paginationtype'] )) {
			$paginationtype = $rs ['paginationtype'];
			$maxcharperpage = $rs ['maxcharperpage'];
		}
		$pages = $titles = '';

		if ($rs ['paginationtype'] == 1) { // 自动分页
			if ($maxcharperpage < 10) $maxcharperpage = 500;
			$contentpage = Loader::lib ( 'content:contentpage' );
			$content = $contentpage->get_data ( $content, $maxcharperpage );
		}
		if ($rs ['paginationtype'] != 0) { // 手动分页
			$CONTENT_POS = strpos ( $content, '[page]' );
			if ($CONTENT_POS !== false) {
				$this->url = Loader::lib ( 'content:url' );
				$contents = array_filter ( explode ( '[page]', $content ) );
				$pagenumber = count ( $contents );
				if (strpos ( $content, '[/page]' ) !== false && ($CONTENT_POS < 7)) {
					$pagenumber --;
				}
				for($i = 1; $i <= $pagenumber; $i ++) {
					$pageurls [$i] = $this->url->show ( $id, $i, $catid, $rs ['inputtime'] );
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
				// 当不存在 [/page]时，则使用下面分页
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
			}
		}
		$this->db->table_name = $tablename;
		// 上一页
		$previous_page = $this->db->where(array('status'=>99,'catid'=>$catid,'id'=>array('lt',$id)))->order('id DESC')->find (  );
		// 下一页
		$next_page = $this->db->where(array('status'=>99,'catid'=>$catid,'id'=>array('gt',$id)))->find();

		if (empty ( $previous_page )) {
			$previous_page = array ('title' => L ( 'first_page' ),'thumb' => IMG_PATH . 'nopic_small.gif','url' => 'javascript:alert(\'' . L ( 'first_page' ) . '\');' );
		}

		if (empty ( $next_page )) {
			$next_page = array ('title' => L ( 'last_page' ),'thumb' => IMG_PATH . 'nopic_small.gif','url' => 'javascript:alert(\'' . L ( 'last_page' ) . '\');' );
		}
		include template ( 'content', $template );
	}

	/**
	 * 栏目列表页
	 */
	public function lists() {
		$catid = intval ( $_GET ['catid'] );
		$_priv_data = $this->_category_priv ( $catid );
		if ($_priv_data == '-1') {
			$forward = urlencode ( Core_Request::get_url () );
			showmessage ( L ( 'login_website' ), SITE_URL . 'index.php?app=member&controller=index&action=login&forward=' . $forward );
		} elseif ($_priv_data == '-2') {
			showmessage ( L ( 'no_priv' ) );
		}
		$_userid = $this->_userid;
		$_username = $this->_username;
		$_groupid = $this->_groupid;

		if (! $catid) showmessage ( L ( 'category_not_exists' ), 'blank' );
		$CATEGORYS = S ( 'common/category_content' );
		if (! isset ( $CATEGORYS [$catid] )) showmessage ( L ( 'category_not_exists' ), 'blank' );
		$CAT = $CATEGORYS [$catid];
		extract ( $CAT );
		$setting = string2array ( $setting );
		// SEO
		if (! $setting ['meta_title']) $setting ['meta_title'] = $catname;
		$SEO = seo ( '', $setting ['meta_title'], $setting ['meta_description'], $setting ['meta_keywords'] );
		define ( 'STYLE', $setting ['template_list'] );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$template = isset ( $setting ['category_template'] ) ? $setting ['category_template'] : 'category';
		$template_list = isset ( $setting ['list_template'] ) ? $setting ['list_template'] : 'list';

		if ($type == 0) {
			$template = $child ? $template : $template_list;
			$arrparentid = explode ( ',', $arrparentid );
			$top_parentid = isset ( $arrparentid [1] ) ? $arrparentid [1] : $catid;
			$array_child = array ();
			$self_array = explode ( ',', $arrchildid );
			// 获取一级栏目ids
			foreach ( $self_array as $arr ) {
				if ($arr != $catid && $CATEGORYS [$arr] ['parentid'] == $catid) {
					$array_child [] = $arr;
				}
			}
			$arrchildid = implode ( ',', $array_child );
			// URL规则
			$urlrules = S ( 'common/urlrule' );
			$urlrules = str_replace ( '|', '~', $urlrules [$category_ruleid] );
			$tmp_urls = explode ( '~', $urlrules );
			$tmp_urls = isset ( $tmp_urls [1] ) ? $tmp_urls [1] : $tmp_urls [0];
			preg_match_all ( '/{\$([a-z0-9_]+)}/i', $tmp_urls, $_urls );
			if (! empty ( $_urls [1] )) {
				foreach ( $_urls [1] as $_v ) {
					$GLOBALS ['URL_ARRAY'] [$_v] = isset ( $_GET [$_v] ) ? $_GET [$_v] : '';
				}
			}
			define ( 'URLRULE', $urlrules );
			$GLOBALS ['URL_ARRAY'] ['categorydir'] = isset ( $categorydir ) ? $categorydir : '';
			$GLOBALS ['URL_ARRAY'] ['catdir'] = isset ( $catdir ) ? $catdir : '';
			$GLOBALS ['URL_ARRAY'] ['catid'] = isset ( $catid ) ? $catid : '';
			include template ( 'content', $template );
		} else {
			// 单网页
			$this->page_db = Loader::model ( 'page_model' );
			$r = $this->page_db->getby_catid ( $catid );
			if ($r) extract ( $r );
			$template = isset ( $setting ['page_template'] ) ? $setting ['page_template'] : 'page';
			$arrchild_arr = $CATEGORYS [$parentid] ['arrchildid'];
			if ($arrchild_arr == '') $arrchild_arr = $CATEGORYS [$catid] ['arrchildid'];
			$arrchild_arr = explode ( ',', $arrchild_arr );
			array_shift ( $arrchild_arr );
			$keywords = isset ( $keywords ) ? $keywords : $setting ['meta_keywords'];
			$SEO = seo ( 0, $title, $setting ['meta_description'], $keywords );
			include template ( 'content', $template );
		}
	}

	// JSON 输出
	public function json_list() {
		if ($_GET ['type'] == 'keyword' && $_GET ['modelid'] && $_GET ['keywords']) {
			// 根据关键字搜索
			$modelid = intval ( $_GET ['modelid'] );
			$id = intval ( $_GET ['id'] );
			$MODEL = S ( 'common/model' );
			if (isset ( $MODEL [$modelid] )) {
				$keywords = safe_replace ( htmlspecialchars ( $_GET ['keywords'] ) );
				$keywords = addslashes ( iconv ( 'utf-8', 'gbk', $keywords ) );
				$this->db->set_model ( $modelid );
				$result = $this->db->where(array('keywords'=>array('like',"%$keywords%")))->field('id,title,url')->limit(10)->select ();
				if (! empty ( $result )) {
					$data = array ();
					foreach ( $result as $rs ) {
						if ($rs ['id'] == $id) continue;
						if (CHARSET == 'gbk') {
							foreach ( $rs as $key => $r ) {
								$rs [$key] = iconv ( 'gbk', 'utf-8', $r );
							}
						}
						$data [] = $rs;
					}
					if (count ( $data ) == 0) exit ( '0' );
					echo json_encode ( $data );
				} else {
					// 没有数据
					exit ( '0' );
				}
			}
		}

	}

	/**
	 * 检查支付状态
	 */
	protected function _check_payment($flag, $paytype) {
		$_userid = $this->_userid;
		$_username = $this->_username;
		if (! $_userid) return false;
		Loader::lib ( 'pay:spend' );
		$setting = $this->category_setting;
		$repeatchargedays = intval ( $setting ['repeatchargedays'] );
		if ($repeatchargedays) {
			$fromtime = TIME - 86400 * $repeatchargedays;
			$r = spend::spend_time ( $_userid, $fromtime, $flag );
			if ($r ['id']) return true;
		}
		return false;
	}

	/**
	 * 检查阅读权限
	 */
	protected function _category_priv($catid) {
		$catid = intval ( $catid );
		if (! $catid) return '-2';
		$_groupid = $this->_groupid;
		$_groupid = intval ( $_groupid );
		if ($_groupid == 0) $_groupid = 8;
		$this->category_priv_db = Loader::model ( 'category_priv_model' );
		$result = $this->category_priv_db->where(array ('catid' => $catid,'is_admin' => 0,'action' => 'visit' ))->select (  );
		if ($result) {
			if (! $_groupid) return '-1';
			foreach ( $result as $r ) {
				if ($r ['roleid'] == $_groupid) return '1';
			}
			return '-2';
		} else {
			return '1';
		}
	}

}