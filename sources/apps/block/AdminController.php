<?php
/**
 * 碎片管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: AdminController.php 71 2012-11-05 12:51:29Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class AdminController extends admin {

	private $db, $priv_db, $history_db, $roleid;

	public function __construct() {
		$this->db = Loader::model ( 'block_model' );
		$this->priv_db = Loader::model ( 'block_priv_model' );
		$this->history_db = Loader::model ( 'block_history_model' );
		$this->roleid = $_SESSION ['roleid'];
		parent::__construct ();
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		if ($_SESSION ['roleid'] != 1) {
			$offset = ($page - 1) * 20;
			$r = $this->priv_db->where(array ('roleid' => $this->roleid ))->select ( 'blockid', $offset . ',20' );
			$blockid_list = array ();
			foreach ( $r as $key => $v ) {
				$blockid_list [$key] = $v ['blockid'];
			}
			$sql = implode ( '\',\'', $blockid_list );
			$list = $this->db->listinfo ( "id in ('$sql')", '', $page, 20 );
		} else {
			$list = $this->db->listinfo ($page, 20 );
		}
		$pages = $this->db->pages;
		include $this->admin_tpl ( 'block_list' );
	}

	/**
	 * 添加碎片
	 */
	public function add() {
		$pos = isset ( $_GET ['pos'] ) && trim ( $_GET ['pos'] ) ? trim ( $_GET ['pos'] ) : showmessage ( L ( 'illegal_operation' ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
			$type = isset ( $_POST ['type'] ) && intval ( $_POST ['type'] ) ? intval ( $_POST ['type'] ) : 1;
			// 判断名称是否已经存在
			if ($this->db->getby_name ( $name )) showmessage ( L ( 'name' ) . L ( 'exists' ), HTTP_REFERER );
			if ($id = $this->db->insert ( array ('name' => $name,'pos' => $pos,'type' => $type ), true )) {
				// 设置权限
				$priv = isset ( $_POST ['priv'] ) ? $_POST ['priv'] : '';
				if (! empty ( $priv )) {
					if (is_array ( $priv )) foreach ( $priv as $v ) {
						if (empty ( $v )) continue;
						$this->priv_db->insert ( array ('roleid' => $v,'blockid' => $id ) );
					}
				}
				showmessage ( L ( 'operation_success' ), U ( 'block/admin/block_update', array ('id' => $id ) ) );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		} else {
			$show_header = $show_validator = true;
			$administrator = S ( 'common/role' );
			unset ( $administrator [1] );
			include $this->admin_tpl ( 'block_add_edit' );
		}
	}

	/**
	 * 修改碎片
	 */
	public function edit() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ) );
		if (! $data = $this->db->getby_id ( $id )) showmessage ( L ( 'nofound' ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
			if ($data ['name'] != $name) {
				if ($this->db->getby_name ( $name )) showmessage ( L ( 'name' ) . L ( 'exists' ), HTTP_REFERER );
			}
			if ($this->db->where(array ('id' => $id ))->update ( array ('name' => $name ) )) {
				// 设置权限
				$priv = isset ( $_POST ['priv'] ) ? $_POST ['priv'] : '';
				$this->priv_db->where(array ('blockid' => $id ))->delete (  );
				if (! empty ( $priv )) {
					if (is_array ( $priv )) foreach ( $priv as $v ) {
						if (empty ( $v )) continue;
						$this->priv_db->insert ( array ('roleid' => $v,'blockid' => $id ) );
					}
				}
				showmessage ( L ( 'operation_success' ), '', '', 'edit' );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		}
		$show_header = $show_validator = true;
		$administrator = S ( 'common/role' );
		unset ( $administrator [1] );
		$r = $this->priv_db->where(array ('blockid' => $id ))->field('roleid')->select ( );
		$priv_list = array ();
		foreach ( $r as $v ) {
			if ($v ['roleid']) $priv_list [] = $v ['roleid'];
		}
		include $this->admin_tpl ( 'block_add_edit' );
	}

	/**
	 * 删除碎片
	 */
	public function del() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ) );
		if (! $data = $this->db->getby_id ( $id )) showmessage ( L ( 'nofound' ) );
		if ($this->db->where(array ('id' => $id ))->delete (  ) && $this->history_db->delete ( array ('blockid' => $id ) ) && $this->priv_db->delete ( array ('blockid' => $id ) )) {
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}
	}

	/**
	 * 更新内容
	 */
	public function block_update() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		// 进行权限判断
		if ($this->roleid != 1) {
			if (! $this->priv_db->where(array ('blockid' => $id,'roleid' => $this->roleid ))->find (  )) showmessage ( L ( 'not_have_permissions' ) );
		}
		if (! $data = $this->db->getby_id ( $id )) showmessage ( L ( 'nofound' ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$sql = array ();
			if ($data ['type'] == 2) {
				$title = isset ( $_POST ['title'] ) ? $_POST ['title'] : '';
				$url = isset ( $_POST ['url'] ) ? $_POST ['url'] : '';
				$thumb = isset ( $_POST ['thumb'] ) ? $_POST ['thumb'] : '';
				$desc = isset ( $_POST ['desc'] ) ? $_POST ['desc'] : '';
				$template = isset ( $_POST ['template'] ) && trim ( $_POST ['template'] ) ? trim ( $_POST ['template'] ) : '';
				$datas = array ();
				foreach ( $title as $key => $v ) {
					if (empty ( $v ) || ! isset ( $url [$key] ) || empty ( $url [$key] )) continue;
					$datas [$key] = array ('title' => $v,'url' => $url [$key],'thumb' => $thumb [$key],'desc' => str_replace ( array (chr ( 13 ),chr ( 43 ) ), array ('<br />','&nbsp;' ), $desc [$key] ) );
				}
				if ($template) {
					$block = Loader::lib ( 'block:block_tag' );
					$block->template_url ( $id, $template );
				}
				$sql = array ('data' => array2string ( $datas ),'template' => $template );
			} elseif ($data ['type'] == 1) {
				$datas = isset ( $_POST ['data'] ) && trim ( $_POST ['data'] ) ? trim ( $_POST ['data'] ) : '';
				$sql = array ('data' => $datas );
			}
			if ($this->db->where(array ('id' => $id ))->update ( $sql )) {
				// 添加历史记录
				$this->history_db->insert ( array ('blockid' => $data ['id'],'data' => array2string ( $data ),'creat_at' => TIME,'userid' => cookie ( 'userid' ),'username' => cookie ( 'admin_username' ) ) );
				showmessage ( L ( 'operation_success' ), '', '', 'edit' );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
			}
		} else {
			if (! empty ( $data ['data'] )) {
				if ($data ['type'] == 2) $data ['data'] = string2array ( $data ['data'] );
				$total = count ( $data ['data'] );
			}
			$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$history_list = $this->history_db->where(array ('blockid' => $id ))->listinfo ($page, 10 );
			$pages = $this->history_db->pages;
			$show_header = $show_validator = $show_dialog = true;
			include $this->admin_tpl ( 'block_update' );
		}
	}

	/**
	 * 可视化碎片
	 */
	public function public_visualization() {
		error_reporting ( E_ERROR );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$catid = isset ( $_GET ['catid'] ) && intval ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : 0;
		$type = isset ( $_GET ['type'] ) && trim ( $_GET ['type'] ) ? trim ( $_GET ['type'] ) : 'list';
		if (! empty ( $catid )) {
			$CATEGORY = S ( 'common/category_content' );
			if (! isset ( $CATEGORY [$catid] )) showmessage ( L ( 'notfound' ) );
			$cat = $CATEGORY [$catid];
			$cat ['setting'] = string2array ( $cat ['setting'] );
		}
		if ($cat ['type'] == 2) showmessage ( L ( 'link_visualization_not_exists' ) );
		$file = '';
		$style = $cat ['setting'] ['template_list'];
		switch ($type) {
			case 'category' :
				if ($cat ['type'] == 1) {
					$file = $cat ['setting'] ['page_template'];
				} else {
					$file = $cat ['setting'] ['category_template'];
				}
				break;

			case 'list' :
				if ($cat ['type'] == 1) {
					$file = $cat ['setting'] ['page_template'];
				} else {
					$file = $cat ['setting'] ['list_template'];
				}
				break;

			case 'show' :
				$file = $cat ['setting'] ['show_template'];
				break;

			case 'index' :
				$file = 'index';
				$style = C ( 'template', 'name' );
				break;

			case 'page' :
				$file = $cat ['setting'] ['page_template'];
				break;
		}
		Loader::helper ( 'template:global' );
		ob_start ();
		include template ( 'content', $file, $style );
		$html = ob_get_contents ();
		ob_clean ();
		echo visualization ( $html, $style, 'content', $file . '.html' );
	}

	/**
	 * 预览碎片
	 */
	public function public_view() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : exit ( '0' );
		if (! $data = $this->db->getby_id ( $id )) showmessage ( L ( 'nofound' ) );
		if ($data ['type'] == 1) {
			exit ( '<script type="text/javascript">parent.showblock(' . $id . ', \'' . str_replace ( "\r\n", '', $_POST ['data'] ) . '\')</script>' );
		} elseif ($data ['type'] == 2) {
			extract ( $data );
			unset ( $data );
			$title = isset ( $_POST ['title'] ) ? $_POST ['title'] : '';
			$url = isset ( $_POST ['url'] ) ? $_POST ['url'] : '';
			$thumb = isset ( $_POST ['thumb'] ) ? $_POST ['thumb'] : '';
			$desc = isset ( $_POST ['desc'] ) ? $_POST ['desc'] : '';
			$template = isset ( $_POST ['template'] ) && trim ( $_POST ['template'] ) ? trim ( $_POST ['template'] ) : '';
			$data = array ();
			foreach ( $title as $key => $v ) {
				if (empty ( $v ) || ! isset ( $url [$key] ) || empty ( $url [$key] )) continue;
				$data [$key] = array ('title' => $v,'url' => $url [$key],'thumb' => $thumb [$key],'desc' => str_replace ( array (chr ( 13 ),chr ( 43 ) ), array ('<br />','&nbsp;' ), $desc [$key] ) );
			}
			$tpl = Loader::lib ( 'Template' );
			$str = $tpl->template_parse ( new_stripslashes ( $template ) );
			$filepath = DATA_PATH . 'compile' . DIRECTORY_SEPARATOR . 'block' . DIRECTORY_SEPARATOR . 'tmp_' . $id . '.php';
			$dir = dirname ( $filepath );
			if (! is_dir ( $dir )) {
				@mkdir ( $dir, 0777, true );
			}
			if (@file_put_contents ( $filepath, $str )) {
				ob_start ();
				include $filepath;
				$html = ob_get_contents ();
				ob_clean ();
				@unlink ( $filepath );
			}

			exit ( '<script type="text/javascript">parent.showblock(' . $id . ', \'' . str_replace ( "\r\n", '', $html ) . '\')</script>' );
		}
	}

	/**
	 * 历史记录还原
	 */
	public function history_restore() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if (! $data = $this->history_db->getby_id ( $id )) showmessage ( L ( 'nofound' ), HTTP_REFERER );
		$data ['data'] = string2array ( $data ['data'] );
		$this->db->where(array ('id' => $data ['blockid'] ))->update ( array ('data' => new_addslashes ( $data ['data'] ['data'] ),'template' => new_addslashes ( $data ['data'] ['template'] ) ) );
		if ($data ['data'] ['type'] == 2) {
			$block = Loader::lib ( 'block:block_tag' );
			$block->template_url ( $data ['blockid'], $data ['data'] ['template'] );
		}
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 历史记录删除
	 */
	public function history_del() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		if (! $data = $this->history_db->getby_id ( $id )) showmessage ( L ( 'nofound' ), HTTP_REFERER );
		$this->history_db->where(array ('id' => $id ))->delete (  );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 内容搜索
	 */
	public function public_search_content() {
		$catid = isset ( $_GET ['catid'] ) && intval ( $_GET ['catid'] ) ? intval ( $_GET ['catid'] ) : '';
		$posids = isset ( $_GET ['posids'] ) && intval ( $_GET ['posids'] ) ? intval ( $_GET ['posids'] ) : 0;
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$searchtype = isset ( $_GET ['searchtype'] ) && intval ( $_GET ['searchtype'] ) ? intval ( $_GET ['searchtype'] ) : 0;
		$end_time = isset ( $_GET ['end_time'] ) && trim ( $_GET ['end_time'] ) ? strtotime ( trim ( $_GET ['end_time'] ) ) : '';
		$start_time = isset ( $_GET ['start_time'] ) && trim ( $_GET ['start_time'] ) ? strtotime ( trim ( $_GET ['start_time'] ) ) : '';
		$keyword = isset ( $_GET ['keyword'] ) && trim ( $_GET ['keyword'] ) ? trim ( $_GET ['keyword'] ) : '';
		if (isset ( $_GET ['dosubmit'] ) && ! empty ( $catid )) {
			if (! empty ( $start_time ) && empty ( $end_time )) $end_time = TIME;
			if ($end_time < $start_time) showmessage ( L ( 'end_of_time_to_time_to_less_than' ) );
			if (! empty ( $end_time ) && empty ( $start_time )) showmessage ( L ( 'please_set_the_starting_time' ) );
			$where = array('catid'=>$catid,'posids'=>$posids);
			if (! empty ( $start_time ) && ! empty ( $end_time )) {
				$where['inputtime']  = array('between',array($start_time,$end_time));
			}
			if (! empty ( $searchtype ) && ! empty ( $keyword )) {
				switch ($searchtype) {
					case '1' : // 标题搜索
						$where['title'] = array('like',"%$keyword%");
						break;
					case '2' : // 简介搜索
						$where['description'] = array('like',"%$keyword%");
						break;
					case '3' : // 用户名
						$where['username'] = $keyword;
						break;
					case '4' : // ID搜索
						$where['id'] = $keyword;
						break;
				}
			}
			$content_db = Loader::model ( 'content_model' );
			$content_db->set_catid ( $catid );
			$data = $content_db->where($where)->order('id desc')->listinfo ($page );
			$pages = $content_db->pages;
		}
		$show_header = $show_validator = $show_dialog = true;
		include $this->admin_tpl ( 'search_content' );
	}

	/**
	 * 检查名称是否可用
	 */
	public function public_name() {
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : '';
		$name = safe_replace ( $name );
		$data = array ();
		if ($id) {
			$data = $this->db->where ( array ('id' => $id ) )->field('name')->find();
			if (! empty ( $data ) && $data ['name'] == $name) exit ( '1' );
		}
		if ($this->db->where ( array ('name' => $name ) )->field('id')->find())
			exit ( '0' );
		else
			exit ( '1' );
	}
}