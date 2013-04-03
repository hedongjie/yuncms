<?php
/**
 * 客户机管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-11-13
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: ClientController.php 211 2013-03-29 23:40:31Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class ClientController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->M = new_htmlspecialchars ( S ( 'common/license' ) );
		$this->license_client_model = Loader::model ( 'license_client_model' );
		$this->license_model = Loader::model ( 'license_model' );
		$this->type_model = Loader::model ( 'type_model' );
		$this->type = new_htmlspecialchars ( S ( 'common/type_license' ) );
	}

	/**
	 * 客户机列表
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$where = '';
		if (isset ( $_GET ['typeid'] ) && ! empty ( $_GET ['typeid'] )) $where = array ('typeid' => $_GET ['typeid'] );
		$infos = $this->license_client_model->where($where)->order('listorder DESC,clientid DESC')->listinfo ($page, $pages = '9' );
		$pages = $this->license_client_model->pages;
		$big_menu = big_menu ( U('license/license/add'), 'add', L ( 'license_add' ), 700, 550 );
		include $this->admin_tpl ( 'license_client_list' );
	}

	public function import(){
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['license'] ['addtime'] = TIME;
			if (empty ( $_POST ['license'] ['sitename'] )) showmessage ( L ( 'sitename_noempty' ), HTTP_REFERER );
			$licenseid = $this->license_model->insert ( $_POST ['license'], true );
			if (! $licenseid) return FALSE;
			showmessage ( L ( 'operation_success' ), U('license/license/init'), '', 'edit' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			$types = S ( 'common/type_license');
			$info = $this->license_client_model->getby_clientid ($_GET ['clientid'] );
			if (! $info) showmessage ( L ( 'client_exit' ) );
			extract ( $info );
			$domain = parse_url($siteurl);
			include $this->admin_tpl ( 'license_import' );
		}
	}

	/**
	 * 客户机排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $clientid => $listorder ) {
				$this->license_client_model->where(array ('clientid' => $clientid ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 查看授权
	 */
	public function look() {
		$show_validator = $show_scroll = $show_header = true;
		$type_arr = S ( 'common/type_license');
		$clientid = isset($_GET ['clientid']) ? intval($_GET ['clientid']) : showmessage ( L ( 'client_exit' ) );
		$info = $this->license_client_model->getby_clientid ($clientid );
		if (! $info) showmessage ( L ( 'client_exit' ) );
		extract ( $info );
		include $this->admin_tpl ( 'client_look' );
	}

	/**
	 * 删除授权
	 */
	public function delete() {
		if ((! isset ( $_GET ['clientid'] ) || empty ( $_GET ['clientid'] )) && (! isset ( $_POST ['clientid'] ) || empty ( $_POST ['clientid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (isset($_POST ['clientid']) && is_array ( $_POST ['clientid'] )) {
				foreach ( $_POST ['clientid'] as $clientid_arr ) {
					$this->license_client_model->where(array ('licenseid' => $clientid_arr ))->delete (  );
				}
				showmessage ( L ( 'operation_success' ), U ( 'license/license' ) );
			} else {
				$clientid = intval ( $_GET ['clientid'] );
				if ($clientid < 1) return false;
				$result = $this->license_client_model->where(array ('clientid' => $clientid ))->delete (  );
				if ($result) exit ( '1' );
				exit ( '0' );
			}
		}
	}

}