<?php
/**
 * URL规则管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class UrlruleController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'urlrule_model' );
		$this->application_db = Loader::model ( 'application_model' );
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order ( 'urlruleid ASC' )->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'admin/urlrule/add' ), 'add', L ( 'add_urlrule' ), 800, 300 );
		$this->_cache ();
		include $this->admin_tpl ( 'urlrule_list' );
	}

	/**
	 * 添加URL规则
	 */
	function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$this->db->insert ( $_POST ['info'] );
			$this->_cache ();
			showmessage ( L ( 'add_success' ), '', '', 'add' );
		} else {
			$show_validator = $show_header = '';
			$applications_arr = $this->application_db->field('application,name')->select ();
			$applications = array ();
			foreach ( $applications_arr as $r ) {
				$applications [$r ['application']] = $r ['name'];
			}
			include $this->admin_tpl ( 'urlrule_add' );
		}
	}

	/**
	 * 删除URL规则
	 */
	function delete() {
		$_GET ['urlruleid'] = intval ( $_GET ['urlruleid'] );
		$this->db->where(array ('urlruleid' => $_GET ['urlruleid'] ))->delete (  );
		$this->_cache ();
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 修改URL规则
	 */
	function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$urlruleid = intval ( $_POST ['urlruleid'] );
			$this->db->where(array ('urlruleid' => $urlruleid ))->update ( $_POST ['info'] );
			$this->_cache();
			showmessage ( L ( 'update_success' ), '', '', 'edit' );
		} else {
			$show_validator = $show_header = '';
			$urlruleid = $_GET ['urlruleid'];
			$r = $this->db->where ( array ('urlruleid' => $urlruleid ) )->find();
			extract ( $r );
			$applications_arr = $this->application_db->field('application,name')->select ();
			$applications = array ();
			foreach ( $applications_arr as $r ) {
				$applications [$r ['application']] = $r ['name'];
			}
			include $this->admin_tpl ( 'urlrule_edit' );
		}
	}

	/**
	 * 生成URL规则缓存
	 */
	private function _cache(){
		$datas = $this->db->key('urlruleid')->select ( );
		$basic_data = array ();
		foreach ( $datas as $roleid => $r ) {
			$basic_data [$roleid] = $r ['urlrule'];
		}
		S ( 'common/urlrule_detail', $datas );
		S ( 'common/urlrule', $basic_data );
		return true;
	}
}