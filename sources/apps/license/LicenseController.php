<?php
/**
 * 授权管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: LicenseController.php 211 2013-03-29 23:40:31Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class LicenseController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->M = new_htmlspecialchars ( S ( 'common/license' ) );
		$this->db = Loader::model ( 'license_model' );
		$this->db2 = Loader::model ( 'type_model' );
	}

	/**
	 * 授权列表
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$where = '';
		if (isset ( $_GET ['typeid'] ) && ! empty ( $_GET ['typeid'] )) $where = array ('typeid' => $_GET ['typeid'] );
		$infos = $this->db->where($where)->order('listorder DESC,licenseid DESC')->listinfo ($page, $pages = 9 );
		$pages = $this->db->pages;
		$type_arr = new_htmlspecialchars ( S ( 'common/type_license' ) );
		$big_menu = big_menu ( U('license/license/add'), 'add', L ( 'license_add' ), 700, 550 );
		include $this->admin_tpl ( 'license_list' );
	}

	/**
	 * 添加授权
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['license'] ['addtime'] = TIME;
			if (empty ( $_POST ['license'] ['sitename'] )) showmessage ( L ( 'sitename_noempty' ), HTTP_REFERER );
			$_POST ['license'] ['licensekey'] = md5 ( md5 ( $_POST ['flexlm'] . 'newsteng' ) );
			$licenseid = $this->db->insert ( $_POST ['license'], true );
			if (! $licenseid) return FALSE;
			showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$types = S ( 'common/type_license');
			include $this->admin_tpl ( 'license_add' );
		}
	}

	/**
	 * 修改授权
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$licenseid = intval ( $_GET ['licenseid'] );
			if ($licenseid < 1) return false;
			if (! is_array ( $_POST ['license'] ) || empty ( $_POST ['license'] )) return false;
			$this->db->update ( $_POST ['license'], array ('licenseid' => $licenseid ) );
			showmessage ( L ( 'operation_success' ), U ( 'license/license/edit' ), '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$types = S ( 'common/type_license');
			$info = $this->db->get_one ( array ('licenseid' => $_GET ['licenseid'] ) );
			if (! $info) showmessage ( L ( 'license_exit' ) );
			extract ( $info );
			include $this->admin_tpl ( 'license_edit' );
		}
	}

	/**
	 * 查看授权
	 */
	public function look() {
		$show_validator = $show_scroll = $show_header = true;
		$type_arr = S ( 'common/type_license');
		$info = $this->db->get_one ( array ('licenseid' => $_GET ['licenseid'] ) );
		if (! $info) showmessage ( L ( 'license_exit' ) );
		extract ( $info );
		include $this->admin_tpl ( 'license_look' );
	}

	/**
	 * 删除授权
	 */
	public function delete() {
		if ((! isset ( $_GET ['licenseid'] ) || empty ( $_GET ['licenseid'] )) && (! isset ( $_POST ['licenseid'] ) || empty ( $_POST ['licenseid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (is_array ( $_POST ['licenseid'] )) {
				foreach ( $_POST ['licenseid'] as $licenseid_arr ) {
					$this->db->delete ( array ('licenseid' => $licenseid_arr ) );
				}
				showmessage ( L ( 'operation_success' ), U ( 'license/license' ) );
			} else {
				$licenseid = intval ( $_GET ['licenseid'] );
				if ($licenseid < 1) return false;
				$result = $this->db->delete ( array ('licenseid' => $licenseid ) );
				if ($result) exit ( '1' );
				exit ( '0' );
			}
		}
	}

	/**
	 * 授权排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $licenseid => $listorder ) {
				$this->db->update ( array ('listorder' => $listorder ), array ('licenseid' => $licenseid ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 类别列表
	 */
	public function list_type() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db2->listinfo ( array ('application' => ROUTE_APP ), $order = 'listorder DESC', $page, 10 );
		$big_menu = big_menu ( U('license/license/add'), 'add', L ( 'license_add' ), 700, 550 );
		include $this->admin_tpl ( 'license_list_type' );
	}

	/**
	 * 添加类别
	 */
	public function add_type() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (empty ( $_POST ['type'] ['name'] )) showmessage ( L ( 'typename_noempty' ), HTTP_REFERER );
			$_POST ['type'] ['application'] = ROUTE_APP;
			$this->db2 = Loader::model ( 'type_model' );
			$typeid = $this->db2->insert ( $_POST ['type'], true );
			if (! $typeid) return FALSE;
			$this->type_cache ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$show_validator = $show_scroll = true;
			$big_menu = big_menu ( U('license/license/add'), 'add', L ( 'license_add' ), 700, 550 );
			include $this->admin_tpl ( 'license_type_add' );
		}
	}

	/**
	 * 修改类别
	 */
	public function edit_type() {
		if (isset ( $_POST ['dosubmit'] )) {
			$typeid = intval ( $_GET ['typeid'] );
			if ($typeid < 1) return false;
			if (! is_array ( $_POST ['type'] ) || empty ( $_POST ['type'] )) return false;
			if ((! $_POST ['type'] ['name']) || empty ( $_POST ['type'] ['name'] )) return false;
			$this->db2->update ( $_POST ['type'], array ('typeid' => $typeid ) );
			$this->type_cache ();
			showmessage ( L ( 'operation_success' ), U ( 'license/license/list_type' ), '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$info = $this->db2->get_one ( array ('typeid' => $_GET ['typeid'] ) );
			if (! $info) showmessage ( L ( 'licensetype_exit' ) );
			extract ( $info );
			include $this->admin_tpl ( 'license_type_edit' );
		}
	}

	/**
	 * 删除类别
	 */
	public function delete_type() {
		if ((! isset ( $_GET ['typeid'] ) || empty ( $_GET ['typeid'] )) && (! isset ( $_POST ['typeid'] ) || empty ( $_POST ['typeid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (is_array ( $_POST ['typeid'] )) {
				foreach ( $_POST ['typeid'] as $typeid_arr ) {
					$this->db2->delete ( array ('typeid' => $typeid_arr ) );
				}
				$this->type_cache ();
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else {
				$typeid = intval ( $_GET ['typeid'] );
				if ($typeid < 1) return false;
				$result = $this->db2->delete ( array ('typeid' => $typeid ) );
				if ($result)
					showmessage ( L ( 'operation_success' ), HTTP_REFERER );
				else
					showmessage ( L ( "operation_failure" ), HTTP_REFERER );
			}
		}
	}

	/**
	 * 更新类别缓存
	 */
	private function type_cache() {
		$datas = array ();
		$result_datas = $this->db2->select ( array ('application' => 'license' ), '*', 1000, 'listorder ASC,typeid ASC' );
		foreach ( $result_datas as $typeid => $type ) {
			$datas [$type ['typeid']] = $type ['name'];
		}
		W ( 'common/type_license', $datas );
	}

	/**
	 * 检测域名是否可用
	 */
	public function public_check_domain() {
		$domain = isset ( $_GET ['domain'] ) && trim ( $_GET ['domain'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['domain'] ) ) : trim ( $_GET ['domain'] )) : exit ( '0' );
		$licenseid = isset ( $_GET ['licenseid'] ) && intval ( $_GET ['licenseid'] ) ? intval ( $_GET ['licenseid'] ) : '';
		$data = array ();
		if ($licenseid) {
			$data = $this->db->get_one ( array ('licenseid' => $licenseid ), 'domain' );
			if (! empty ( $data ) && $data ['domain'] == $domain) exit ( '1' );
		}
		if ($this->db->get_one ( array ('domain' => $domain ), 'licenseid' ))
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 检测类别是否可用
	 */
	public function public_check_name() {
		$type_name = isset ( $_GET ['type_name'] ) && trim ( $_GET ['type_name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['type_name'] ) ) : trim ( $_GET ['type_name'] )) : exit ( '0' );
		$typeid = isset ( $_GET ['typeid'] ) && intval ( $_GET ['typeid'] ) ? intval ( $_GET ['typeid'] ) : '';
		$data = array ();
		if ($typeid) {
			$data = $this->db2->get_one ( array ('typeid' => $typeid ), 'name' );
			if (! empty ( $data ) && $data ['name'] == $type_name) exit ( '1' );
		}
		if ($this->db2->get_one ( array ('name' => $type_name ), 'typeid' ))
			exit ( '0' );
		else
			exit ( '1' );
	}
}