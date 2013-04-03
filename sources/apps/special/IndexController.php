<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::helper ( 'special:global' );
error_reporting ( E_ERROR );
class IndexController {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'special_model' );
	}

	/**
	 * 专题列表
	 */
	public function special() {
		$SEO = seo ();
		include template ( 'special', 'special_list' );
	}

	/**
	 * 专题首页
	 */
	public function init() {
		$specialid = $_GET ['id'] ? $_GET ['id'] : ($_GET ['specialid'] ? $_GET ['specialid'] : 0);
		if (! $specialid) showmessage ( L ( 'illegal_action' ) );
		$info = $this->db->where ( array ('id' => $specialid,'disabled' => 0 ) )->find();
		if (! $info) showmessage ( L ( 'special_not_exist' ), 'back' );
		extract ( $info );
		$css = get_css ( unserialize ( $css ) );
		if (! $ispage) {
			$type_db = Loader::model ( 'type_model' );
			$types = $type_db->where(array ('application' => 'special','parentid' => $specialid ))->order('listorder ASC, typeid ASC')->key('listorder')->select ();
		}
		if ($pics) {
			$pic_data = get_pic_content ( $pics );
			unset ( $pics );
		}
		if ($voteid) {
			$vote_info = explode ( '|', $voteid );
			$voteid = $vote_info [1];
		}
		$SEO = seo ( '', $title, $description );
		$commentid = id_encode ( 'special', $id );
		$template = $info ['index_template'] ? $info ['index_template'] : 'index';
		include template ( 'special', $template );
	}

	/**
	 * 专题分类
	 */
	public function type() {
		$typeid = intval ( $_GET ['typeid'] );
		$specialid = intval ( $_GET ['specialid'] );
		if (! $specialid || ! $typeid) showmessage ( L ( 'illegal_action' ) );
		$info = $this->db->where ( array ('id' => $specialid,'disabled' => 0 ) )->find();
		if (! $info) showmessage ( L ( 'special_not_exist' ), 'back' );
		$page = max ( intval ( $_GET ['page'] ), 1 );
		extract ( $info );
		$css = get_css ( unserialize ( $css ) );
		if (! $typeid) showmessage ( L ( 'illegal_action' ) );
		$type_db = Loader::model ( 'type_model' );
		$info = $type_db->getby_typeid ( intval($_GET ['typeid'] ) );
		$SEO = seo ( '', $info ['typename'], '' );
		$template = $list_template ? $list_template : 'list';
		include template ( 'special', $template );
	}

	/**
	 * 专题展示
	 */
	public function show() {
		$id = intval ( $_GET ['id'] );
		if (! $id) showmessage ( L ( 'content_not_exist' ), 'blank' );
		$page = max ( intval ( $_GET ['page'] ), 1 );
		$c_db = Loader::model ( 'special_content_model' );
		$c_data_db = Loader::model ( 'special_c_data_model' );
		$rs = $c_db->getby_id ( intval($_GET ['id'] ) );
		if (! $rs) showmessage ( L ( 'content_checking' ), 'blank' );
		extract ( $rs );
		if ($isdata) {
			$arr_content = $c_data_db->getby_id ( intval($_GET ['id'] ) );
			if (is_array ( $arr_content )) extract ( $arr_content );
		}
		if ($paginationtype) {
			// 文章使用分页时
			if ($paginationtype == 1) {
				if (strpos ( $content, '[/page]' ) !== false) {
					$content = preg_replace ( "|\[page\](.*)\[/page\]|U", '', $content );
				}
				if (strpos ( $content, '[page]' ) !== false) {
					$content = str_replace ( '[page]', '', $content );
				}
				$contentpage = Loader::lib ( 'content:contentpage' ); // 调用自动分页类
				$content = $contentpage->get_data ( $content, $maxcharperpage ); // 自动分页，自动添加上[page]
			}
		} else {
			if (strpos ( $content, '[/page]' ) !== false) {
				$content = preg_replace ( "|\[page\](.*)\[/page\]|U", '', $content );
			}
			if (strpos ( $content, '[page]' ) !== false) {
				$content = str_replace ( '[page]', '', $content );
			}
		}
		$template = $show_template ? $show_template : 'show'; // 调用模板
		$CONTENT_POS = strpos ( $content, '[page]' );
		if ($CONTENT_POS !== false) {
			$contents = array_filter ( explode ( '[page]', $content ) );
			$pagenumber = count ( $contents );
			$END_POS = strpos ( $content, '[/page]' );
			if ($END_POS !== false && ($CONTENT_POS < 7)) {
				$pagenumber --;
			}
			for($i = 1; $i <= $pagenumber; $i ++) {
				$pageurls [$i] = content_url ( $_GET ['id'], $i, $inputtime, 'php' );
			}
			if ($END_POS !== false) {
				if ($CONTENT_POS > 7) {
					$content = '[page]' . $title . '[/page]' . $content;
				}
				if (preg_match_all ( "|\[page\](.*)\[/page\]|U", $content, $m, PREG_PATTERN_ORDER )) {
					foreach ( $m [1] as $k => $v ) {
						$p = $k + 1;
						$titles [$p] ['title'] = strip_tags ( $v );
						$titles [$p] ['url'] = $pageurls [$p] [1];
					}
				}
			}
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
			Loader::helper ( 'content:util' );
			$title_pages = content_pages ( $pagenumber, $page, $pageurls );
		}
		$_special = $this->db->where ( array ('id' => $specialid ))->field( 'title, url' )->find();
		$inputtime = Format::date ( $inputtime );
		$SEO = seo ( '', $title );
		$template = isset ( $show_template ) && ! empty ( $show_template ) ? $show_template : 'show';
		$style = $style ? $style : 'default';
		include template ( 'special', $template, $style );
	}

	public function comment_show() {
		$commentid = isset ( $_GET ['commentid'] ) ? $_GET ['commentid'] : 0;
		$url = isset ( $_GET ['url'] ) ? $_GET ['url'] : HTTP_REFERER;
		$id = isset ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : 0;
		$userid = cookie ( '_userid' );
		include template ( 'special', 'comment_show' );
	}

	public function comment() {
		if (! $_GET ['id']) return '0';
		$id = intval ( $_GET ['id'] );
		$commentid = id_encode ( 'special', $id );
		$username = cookie ( '_username' );
		$userid = cookie ( '_userid' );
		if (! $userid) {
			showmessage ( L ( 'login_website' ), SITE_URL . 'index.php?app=member&controller=index' );
		}
		$date = date ( 'm-d H:i', TIME );
		if ($_POST ['dosubmit']) {
			$r = $this->db->where ( array ('id' => $_POST ['id'] ))->field('title, url' )->find();
			$comment = Loader::lib ( 'comment:comment' );
			if ($comment->add ( $commentid, array ('userid' => $userid,'username' => $username,'content' => $_POST ['content'] ), '', $r ['title'], $r ['url'] )) {
				exit ( $username . '|' . TIME . '|' . $_POST ['content'] );
			} else {
				exit ( 0 );
			}
		} else {
			include template ( 'special', 'comment' );
		}
	}
}