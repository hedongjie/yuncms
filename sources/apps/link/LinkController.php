<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-6
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: LinkController.php 19 2012-11-05 10:09:53Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class LinkController extends admin {
	public function __construct() {
		parent::__construct ();
		$this->M = new_htmlspecialchars ( S ( 'common/link' ) );
		$this->db = Loader::model ( 'link_model' );
		$this->db2 = Loader::model ( 'type_model' );
	}
	public function init() {
		if (isset ( $_GET ['typeid'] )) {
			$where = array ('typeid' => intval ( $_GET ['typeid'] ) );
		} else {
			$where = array ();
		}
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->where ( $where )->order ( 'listorder DESC,linkid DESC' )->listinfo ( $page, 15 );
		$pages = $this->db->pages;
		$types = $this->db2->where ( array ('application' => APP ) )->order ( 'typeid DESC' )->listinfo ();
		$types = new_htmlspecialchars ( $types );
		$type_arr = array ();
		foreach ( $types as $typeid => $type ) {
			$type_arr [$type ['typeid']] = $type ['name'];
		}
		$big_menu = big_menu ( '?app=link&controller=link&action=add', 'add', L ( 'link_add' ), 700, 450 );
		include $this->admin_tpl ( 'link_list' );
	}

	/**
	 * 添加友情链接
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['link'] ['addtime'] = TIME;
			if (empty ( $_POST ['link'] ['name'] )) showmessage ( L ( 'sitename_noempty' ), HTTP_REFERER );
			$linkid = $this->db->insert ( $_POST ['link'], true );
			if (! $linkid) return FALSE;
			// 更新附件状态
			if (C ( 'attachment', 'stat' ) & $_POST ['link'] ['logo']) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$this->attachment_db->api_update ( $_POST ['link'] ['logo'], 'link-' . $linkid, 1 );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$types = $this->db2->get_types ();
			include $this->admin_tpl ( 'link_add' );
		}
	}

	/**
	 * 更新排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $linkid => $listorder ) {
				$this->db->where ( array ('linkid' => $linkid ) )->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$linkid = intval ( $_GET ['linkid'] );
			if ($linkid < 1) return false;
			if (! is_array ( $_POST ['link'] ) || empty ( $_POST ['link'] )) return false;
			if ((! $_POST ['link'] ['name']) || empty ( $_POST ['link'] ['name'] )) return false;
			$this->db->where(array ('linkid' => $linkid ))->update ( $_POST ['link'] );
			// 更新附件状态
			if (C ( 'attachment', 'stat' ) & $_POST ['link'] ['logo']) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$this->attachment_db->api_update ( $_POST ['link'] ['logo'], 'link-' . $linkid, 1 );
			}
			showmessage ( L ( 'operation_success' ), '?app=link&controller=link&action=edit', '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$types = $this->db2->order('typeid DESC')->where(array ('application' => APP ))->key('typeid')->select();
			// 解出链接内容
			$info = $this->db->getby_linkid ( $_GET ['linkid'] );
			if (! $info) showmessage ( L ( 'link_exit' ) );
			extract ( $info );
			include $this->admin_tpl ( 'link_edit' );
		}
	}

	/**
	 * 删除友情链接
	 *
	 * @param intval $sid
	 */
	public function delete() {
		if ((! isset ( $_GET ['linkid'] ) || empty ( $_GET ['linkid'] )) && (! isset ( $_POST ['linkid'] ) || empty ( $_POST ['linkid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (isset ( $_POST ['linkid'] ) && is_array ( $_POST ['linkid'] )) {
				foreach ( $_POST ['linkid'] as $linkid_arr ) {
					// 批量删除友情链接
					$this->db->where ( array ('linkid' => $linkid_arr ) )->delete();
					// 更新附件状态
					if (C ( 'attachment', 'stat' )) {
						$this->attachment_db = Loader::model ( 'attachment_model' );
						$this->attachment_db->api_delete ( 'link-' . $linkid_arr );
					}
				}
				showmessage ( L ( 'operation_success' ), '?app=link&controller=link' );
			} else {
				$linkid = intval ( $_GET ['linkid'] );
				if ($linkid < 1) return false;
				// 删除友情链接
				$result = $this->db->where ( array ('linkid' => $linkid ) )->delete();
				// 更新附件状态
				if (C ( 'attachment', 'stat' )) {
					$this->attachment_db = Loader::model ( 'attachment_model' );
					$this->attachment_db->api_delete ( 'link-' . $linkid );
				}
				if ($result) {
					showmessage ( L ( 'operation_success' ), '?app=link&controller=link' );
				} else {
					showmessage ( L ( "operation_failure" ), '?app=link&controller=link' );
				}
			}
		}
	}

	// 批量审核申请 ...
	public function check_register() {
		if (isset ( $_POST ['dosubmit'] )) {
			if ((! isset ( $_GET ['linkid'] ) || empty ( $_GET ['linkid'] )) && (! isset ( $_POST ['linkid'] ) || empty ( $_POST ['linkid'] ))) {
				showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			} else {
				if (isset ( $_POST ['linkid'] ) && is_array ( $_POST ['linkid'] )) { // 批量审核
					foreach ( $_POST ['linkid'] as $linkid_arr ) {
						$this->db->where(array ('linkid' => $linkid_arr ))->update ( array ('passed' => 1 ) );
					}
					showmessage ( L ( 'operation_success' ), '?app=link&controller=link' );
				} else { // 单个审核
					$linkid = intval ( $_GET ['linkid'] );
					if ($linkid < 1) return false;
					$result = $this->db->where(array ('linkid' => $linkid ))->update ( array ('passed' => 1 ) );
					if ($result) {
						showmessage ( L ( 'operation_success' ), '?app=link&controller=link' );
					} else {
						showmessage ( L ( "operation_failure" ), '?app=link&controller=link' );
					}
				}
			}
		} else { // 读取未审核列表
			$where = array ('passed' => 0 );
			$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
			$infos = $this->db->where($where)->order('linkid DESC')->listinfo ( $page, 9 );
			$pages = $this->db->pages;
			$big_menu = big_menu ( '?app=link&controller=link&action=add', 'add', L ( 'link_add' ), 700, 450 );
			include $this->admin_tpl ( 'check_register_list' );
		}
	}

	// 单个审核申请
	public function check() {
		if ((! isset ( $_GET ['linkid'] ) || empty ( $_GET ['linkid'] )) && (! isset ( $_POST ['linkid'] ) || empty ( $_POST ['linkid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			$linkid = intval ( $_GET ['linkid'] );
			if ($linkid < 1) return false;
			$result = $this->db->where(array ('linkid' => $linkid ))->update ( array ('passed' => 1 ) );
			if ($result) {
				showmessage ( L ( 'operation_success' ), '?app=link&controller=link' );
			} else {
				showmessage ( L ( "operation_failure" ), '?app=link&controller=link' );
			}
		}
	}

	/**
	 * 添加友情链接分类
	 */
	public function add_type() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (empty ( $_POST ['type'] ['name'] )) showmessage ( L ( 'typename_noempty' ), HTTP_REFERER );
			$_POST ['type'] ['application'] = APP;
			$typeid = $this->db2->insert ( $_POST ['type'], true );
			if (! $typeid) return FALSE;
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$show_validator = $show_scroll = true;
			$big_menu = big_menu ( '?app=link&controller=link&action=add', 'add', L ( 'link_add' ), 700, 450 );
			include $this->admin_tpl ( 'link_type_add' );
		}
	}

	/**
	 * 修改友情链接 分类
	 */
	public function edit_type() {
		if (isset ( $_POST ['dosubmit'] )) {
			$typeid = intval ( $_GET ['typeid'] );
			if ($typeid < 1) return false;
			if (! is_array ( $_POST ['type'] ) || empty ( $_POST ['type'] )) return false;
			if ((! $_POST ['type'] ['name']) || empty ( $_POST ['type'] ['name'] )) return false;
			$this->db2->where(array ('typeid' => $typeid ))->update ( $_POST ['type'] );
			showmessage ( L ( 'operation_success' ), '?app=link&controller=link&action=list_type', '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			// 解出分类内容
			$info = $this->db2->getby_typeid ( $_GET ['typeid'] );
			if (! $info) showmessage ( L ( 'linktype_exit' ) );
			extract ( $info );
			include $this->admin_tpl ( 'link_type_edit' );
		}
	}

	/**
	 * 分类管理
	 */
	public function list_type() {
		$infos = $this->db2->where( array ('application' => APP ))->order('listorder DESC')->select ( );
		$big_menu = big_menu ( '?app=link&controller=link&action=add', 'add', L ( 'link_add' ), 700, 450 );
		include $this->admin_tpl ( 'link_list_type' );
	}

	/**
	 * 删除分类
	 */
	public function delete_type() {
		if ((! isset ( $_GET ['typeid'] ) || empty ( $_GET ['typeid'] )) && (! isset ( $_POST ['typeid'] ) || empty ( $_POST ['typeid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (isset ( $_POST ['typeid'] ) && is_array ( $_POST ['typeid'] )) {
				foreach ( $_POST ['typeid'] as $typeid_arr ) {
					$this->db2->where ( array ('typeid' => $typeid_arr ) )->delete();
				}
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else {
				$typeid = intval ( $_GET ['typeid'] );
				if ($typeid < 1) return false;
				$result = $this->db2->where ( array ('typeid' => $typeid ) )->delete();
				if ($result) {
					showmessage ( L ( 'operation_success' ), HTTP_REFERER );
				} else {
					showmessage ( L ( "operation_failure" ), HTTP_REFERER );
				}
			}
		}
	}

	/**
	 * 添加分类时，验证分类名是否已存在
	 */
	public function public_check_name() {
		$type_name = isset ( $_GET ['type_name'] ) && trim ( $_GET ['type_name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['type_name'] ) ) : trim ( $_GET ['type_name'] )) : exit ( '0' );
		$typeid = isset ( $_GET ['typeid'] ) && intval ( $_GET ['typeid'] ) ? intval ( $_GET ['typeid'] ) : '';
		$data = array ();
		if ($typeid) {
			$data = $this->db2->where ( array ('typeid' => $typeid ) )->field ( 'name' )->find ();
			if (! empty ( $data ) && $data ['name'] == $type_name) exit ( '1' );
		}
		if ($this->db2->where ( array ('name' => $type_name ) )->field ( 'typeid' )->find ())
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 判断标题重复和验证
	 */
	public function public_name() {
		$link_title = isset ( $_GET ['link_name'] ) && trim ( $_GET ['link_name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['link_name'] ) ) : trim ( $_GET ['link_name'] )) : exit ( '0' );
		$linkid = isset ( $_GET ['linkid'] ) && intval ( $_GET ['linkid'] ) ? intval ( $_GET ['linkid'] ) : '';
		$data = array ();
		if ($linkid) {
			$data = $this->db->where ( array ('linkid' => $linkid ) )->field ( 'name' )->find ();
			if (! empty ( $data ) && $data ['name'] == $link_title) exit ( '1' );
		}
		if ($this->db->where ( array ('name' => $link_title ) )->field ( 'linkid' )->find ())
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 模块配置
	 */
	public function setting() {
		// 更新模型数据库,重设setting 数据.
		$seting = Loader::model ( 'application_model' )->get_setting ( 'link' );
		if (isset ( $_POST ['dosubmit'] )) {
			// 多站点存储配置文件
			S ( 'common/link', $_POST ['setting'] );
			Loader::model ( 'application_model' )->set_setting ( 'link', $_POST ['setting'] );
			showmessage ( L ( 'setting_updates_successful' ), '?app=link&controller=link&action=init' );
		} else {
			@extract ( $seting );
			$big_menu = big_menu ( '?app=link&controller=link&action=add', 'add', L ( 'link_add' ), 700, 450 );
			include $this->admin_tpl ( 'setting' );
		}
	}
}