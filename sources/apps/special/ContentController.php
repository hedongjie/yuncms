<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
/**
 *
 * @package XTCMS
 * @author NewsTeng Dev Team
 * @copyright Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license http://www.newsteng.com/about/license
 * @link http://www.newsteng.com
 *       $Id: ContentController.php 883 2012-06-13 06:05:36Z 85825770@qq.com $
 */
class ContentController extends admin {
	private $db, $data_db, $type_db;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'special_content_model' );
		$this->data_db = Loader::model ( 'special_c_data_model' );
		$this->type_db = Loader::model ( 'type_model' );
	}

	/**
	 * 信息列表
	 */
	public function init() {
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$types = $this->type_db->where(array ('application' => 'special','parentid' => $_GET ['specialid'] ))->field('name, typeid')->order('listorder ASC, typeid ASC')->key('typeid')->select ();
		$datas = $this->db->where(array ('specialid' => $_GET ['specialid'] ))->order('listorder ASC , id DESC')->listinfo ( $page );
		$pages = $this->db->pages;
		$big_menu = array (array ('javascript:openwinx(\'?app=special&controller=content&action=add&specialid=' . $_GET ['specialid'] . '\',\'\');void(0);',L ( 'add_content' ) ),
				big_menu ( '?app=special&controller=special&action=import&specialid=' . $_GET ['specialid'], 'import', L ( 'import_content' ), 700, 500 ) );
		include $this->admin_tpl ( 'content_list' );
	}

	/**
	 * 添加信息
	 */
	public function add() {
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		if (isset($_POST ['dosubmit']) || isset($_POST ['dosubmit_continue'])) {
			$info = $this->check ( $_POST ['info'], 'info', 'add', $_POST ['data'] ['content'] ); // 验证数据的合法性
			                                                                                      // 处理外部链接情况
			if ($info ['islink']) {
				$info ['url'] = $_POST ['linkurl'];
				$info ['isdata'] = 0;
			} else {
				$info ['isdata'] = 1;
			}
			$info ['specialid'] = $_GET ['specialid'];
			// 将基础数据添加到基础表，并返回ID
			$contentid = $this->db->insert ( $info, true );

			// 向数据统计表添加数据
			$count = Loader::model ( 'hits_model' );
			$hitsid = 'special-c-' . $info ['specialid'] . '-' . $contentid;
			$count->insert ( array ('hitsid' => $hitsid ) );
			// 如果不是外部链接，将内容加到data表中
			$html = Loader::lib ( 'special:html' );
			if ($info ['isdata']) {
				$data = $this->check ( $_POST ['data'], 'data' ); // 验证数据的合法性
				$data ['id'] = $contentid;
				$this->data_db->insert ( $data );
				$searchid = $this->search_api ( $contentid, $data, $info ['title'], 'update', $info ['inputtime'] );
				$url = $html->_create_content ( $contentid );
				$this->db->where(array ('id' => $contentid,'specialid' => $_GET ['specialid'] ))->update ( array ('url' => $url [0],'searchid' => $searchid ) );
			}
			$html->_index ( $_GET ['specialid'], 20, 5 );
			$html->_list ( $info ['typeid'], 20, 5 );
			// 更新附件状态
			if (C ( 'attachment', 'stat' )) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				if ($info ['thunb']) {
					$this->attachment_db->api_update ( $info ['thumb'], 'special-c-' . $contentid, 1 );
				}
				$this->attachment_db->api_update ( stripslashes ( $data ['content'] ), 'special-c-' . $contentid );
			}
			if ($_POST ['dosubmit'])
				showmessage ( L ( 'content_add_success' ), HTTP_REFERER, '', '', 'setTimeout("window.close()", 2000)' );
			elseif ($_POST ['dosubmit_continue'])
				showmessage ( L ( 'content_add_success' ), HTTP_REFERER );
		} else {
			$rs = $this->type_db->where(array ('parentid' => $_GET ['specialid'] ))->field('typeid, name')->select (  );
			$types = array ();
			foreach ( $rs as $r ) {
				$types [$r ['typeid']] = $r ['name'];
			}
			// 获取站点模板信息
			Loader::helper( 'admin:global' );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$special_db = Loader::model ( 'special_model' );
			$info = $special_db->getby_id ( intval($_GET ['specialid'] ) );
			@extract ( $info );
			include $this->admin_tpl ( 'content_add' );
		}
	}

	/**
	 * 信息修改
	 */
	public function edit() {
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		$_GET ['id'] = intval ( $_GET ['id'] );
		if (! $_GET ['specialid'] || ! $_GET ['id']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		if (isset ( $_POST ['dosubmit'] ) || isset ( $_POST ['dosubmit_continue'] )) {
			$info = $this->check ( $_POST ['info'], 'info', 'edit', $_POST ['data'] ['content'] ); // 验证数据的合法性
			                                                                                       // 处理外部链接更换情况
			$r = $this->db->where ( array ('id' => $_GET ['id'],'specialid' => $_GET ['specialid'] ) )->find();

			if ($r ['islink'] != $info ['islink']) {
				// 当外部链接和原来差别时进行操作
				// 向数据统计表添加数据
				$count = Loader::model ( 'hits_model' );
				$hitsid = 'special-c-' . $_GET ['specialid'] . '-' . $_GET ['id'];
				$count->delete ( array ('hitsid' => $hitsid ) );
				$this->data_db->where(array ('id' => $_GET ['id'] ))->delete (  );
				if ($info ['islink']) {
					$info ['url'] = $_POST ['linkurl'];
					$info ['isdata'] = 0;
				} else {
					$data = $this->check ( $_POST ['data'], 'data' );
					$data ['id'] = $_GET ['id'];
					$this->data_db->insert ( $data );
					$count->insert ( array ('hitsid' => $hitsid ) );
				}
			}
			// 处理外部链接情况
			if ($info ['islink']) {
				$info ['url'] = $_POST ['linkurl'];
				$info ['isdata'] = 0;
			} else {
				$info ['isdata'] = 1;
			}
			$html = Loader::lib ( 'special:html' );
			if ($info ['isdata']) {
				$data = $this->check ( $_POST ['data'], 'data' );
				$this->data_db->where(array ('id' => $_GET ['id'] ))->update ( $data );
				$url = $html->_create_content ( $_GET ['id'] );
				if ($url [0]) {
					$info ['url'] = $url [0];
					$searchid = $this->search_api ( $_GET ['id'], $data, $info ['title'], 'update', $info ['inputtime'] );
					$this->db->where(array ('id' => $_GET ['id'],'specialid' => $_GET ['specialid'] ))->update ( array ('url' => $url [0],'searchid' => $searchid ) );
				}
			} else {
				$this->db->where(array ('id' => $_GET ['id'],'specialid' => $_GET ['specialid'] ))->update ( array ('url' => $info ['url'] ) );
			}
			$this->db->where(array ('id' => $_GET ['id'],'specialid' => $_GET ['specialid'] ))->update ( $info );
			// 更新附件状态
			if (C ( 'attachment', 'stat' )) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				if ($info ['thumb']) {
					$this->attachment_db->api_update ( $info ['thumb'], 'special-c-' . $_GET ['id'], 1 );
				}
				$this->attachment_db->api_update ( stripslashes ( $data ['content'] ), 'special-c-' . $_GET ['id'] );
			}
			$html->_index ( $_GET ['specialid'], 20, 5 );
			$html->_list ( $info ['typeid'], 20, 5 );
			showmessage ( L ( 'content_edit_success' ), HTTP_REFERER, '', '', 'setTimeout("window.close()", 2000)' );
		} else {
			$info = $this->db->where ( array ('id' => $_GET ['id'],'specialid' => $_GET ['specialid'] ) )->find();
			if ($info ['isdata']) $data = $this->data_db->get_one ( array ('id' => $_GET ['id'] ) );
			$rs = $this->type_db->where(array ('parentid' => $_GET ['specialid'] ))->field('typeid, name')->select ( );
			$types = array ();
			foreach ( $rs as $r ) {
				$types [$r ['typeid']] = $r ['name'];
			}
			// 获取站点模板信息
			Loader::helper ( 'admin:global' );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			include $this->admin_tpl ( 'content_edit' );
		}
	}

	/**
	 * 检查表题是否重复
	 */
	public function public_check_title() {
		if ($_GET ['data'] == '' || (! $_GET ['specialid'])) return '';
		if (CHARSET == 'gbk') {
			$title = safe_replace ( iconv ( 'UTF-8', 'GBK', $_GET ['data'] ) );
		} else
			$title = $_GET ['data'];
		$specialid = intval ( $_GET ['specialid'] );
		$r = $this->db->where ( array ('title' => $title,'specialid' => $specialid ) )->find();
		if ($r) {
			exit ( '1' );
		} else {
			exit ( '0' );
		}
	}

	/**
	 * 信息排序 信息调用时按排序从小到大排列
	 */
	public function listorder() {
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		foreach ( $_POST ['listorders'] as $id => $v ) {
			$this->db->where(array ('id' => $id,'specialid' => $_GET ['specialid'] ))->update ( array ('listorder' => $v ) );
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 删除信息
	 */
	public function delete() {
		if (! isset ( $_POST ['id'] ) || empty ( $_POST ['id'] ) || ! $_GET ['specialid']) {
			showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		}
		$specialid = $_GET ['specialid'];
		$special = Loader::model ( 'special_model' );
		$info = $special->getby_id ( $specialid );
		$special_api = Loader::lib ( 'special:special_api' );
		if (is_array ( $_POST ['id'] )) {
			foreach ( $_POST ['id'] as $sid ) {
				$sid = intval ( $sid );
				$special_api->_delete_content ( $sid, $info ['ishtml'] );
				if (C ( 'attachment', 'stat' )) {
					$keyid = 'special-c-' . $sid;
					$this->attachment_db = Loader::model ( 'attachment_model' );
					$this->attachment_db->api_delete ( $keyid );
				}
			}
		} elseif (is_numeric ( $_POST ['id'] )) {
			$id = intval ( $_POST ['id'] );
			$special_api->_delete_content ( $id, $info ['ishtml'] );
			if (C ( 'attachment', 'stat' )) {
				$keyid = 'special-c-' . $id;
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$this->attachment_db->api_delete ( $keyid );
			}
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 添加到全站搜索
	 *
	 * @param intval $id 文章ID
	 * @param array $data 数组
	 * @param string $title 标题
	 * @param string $action 动作
	 */
	private function search_api($id = 0, $data = array(), $title, $action = 'update', $addtime) {
		$this->search_db = Loader::model ( 'search_model' );
		$type_arr = S ( 'search/type_application' );
		$typeid = $type_arr ['special'];
		if ($action == 'update') {
			$fulltextcontent = $data ['content'];
			return $this->search_db->update_search ( $typeid, $id, $fulltextcontent, $title, $addtime );
		} elseif ($action == 'delete') {
			$this->search_db->delete_search ( $typeid, $id );
		}
	}

	/**
	 * 表单验证
	 *
	 * @param array $data 表单数据
	 * @param string $type 按数据表数据判断
	 * @param string $action 在添加时会加上默认数据
	 * @return array 数据检验后返回的数组
	 */
	private function check($data = array(), $type = 'info', $action = 'add', $content = '') {
		if ($type == 'info') {
			if (! $data ['title']) showmessage ( L ( 'title_no_empty' ), HTTP_REFERER );
			if (! $data ['typeid']) showmessage ( L ( 'no_select_type' ), HTTP_REFERER );
			$data ['inputtime'] = $data ['inputtime'] ? strtotime ( $data ['inputtime'] ) : TIME;
			$data ['islink'] = $data ['islink'] ? intval ( $data ['islink'] ) : 0;
			$data ['style'] = '';
			if ($data ['style_color']) {
				$data ['style'] .= 'color:#00FF99;';
			}
			if ($data ['style_font_weight']) {
				$data ['style'] .= 'font-weight:bold;';
			}
			// 截取简介
			if ($_POST ['add_introduce'] && $data ['description'] == '' && ! empty ( $content )) {
				$content = stripslashes ( $content );
				$introcude_length = intval ( $_POST ['introcude_length'] );
				$data ['description'] = str_cut ( str_replace ( array ("\r\n","\t" ), '', strip_tags ( $content ) ), $introcude_length );
			}

			// 自动提取缩略图
			if (isset ( $_POST ['auto_thumb'] ) && $data ['thumb'] == '' && ! empty ( $content )) {
				$content = $content ? $content : stripslashes ( $content );
				$auto_thumb_no = intval ( $_POST ['auto_thumb_no'] ) * 3;
				if (preg_match_all ( "/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2/i", $content, $matches )) {
					$data ['thumb'] = $matches [$auto_thumb_no] [0];
				}
			}
			unset ( $data ['style_color'], $data ['style_font_weight'] );
			if ($action == 'add') {
				$data ['updatetime'] = TIME;
				$data ['username'] = cookie ( 'admin_username' );
				$data ['userid'] = $_SESSION ['userid'];
			}
		} elseif ($type == 'data') {
			if (! $data ['content']) showmessage ( L ( 'content_no_empty' ), HTTP_REFERER );
		}
		return $data;
	}
}