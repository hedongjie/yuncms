<?php
/**
 * 工作流
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class WorkflowController extends admin {

	private $db, $admin_db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'workflow_model' );
		$this->admin_db = Loader::model ( 'admin_model' );
	}

	/**
	 * 工作流管理
	 */
	public function init() {
		$datas = array ();
		$result_datas = $this->db->listinfo ();
		foreach ( $result_datas as $r ) {
			$datas [] = $r;
		}
		$this->_cache ();
		include $this->admin_tpl ( 'workflow_list' );
	}

	/**
	 * 添加工作流
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['workname'] = safe_replace ( $_POST ['info'] ['workname'] );
			$setting [1] = isset ( $_POST ['checkadmin1'] ) ? $_POST ['checkadmin1'] : '';
			$setting [2] = isset ( $_POST ['checkadmin2'] ) ? $_POST ['checkadmin2'] : '';
			$setting [3] = isset ( $_POST ['checkadmin3'] ) ? $_POST ['checkadmin3'] : '';
			$setting [4] = isset ( $_POST ['checkadmin4'] ) ? $_POST ['checkadmin4'] : '';
			$setting ['nocheck_users'] = isset ( $_POST ['nocheck_users'] ) ? $setting [1] : '';
			$setting = array2string ( $setting );
			$_POST ['info'] ['setting'] = $setting;
			$this->db->insert ( $_POST ['info'] );
			$this->_cache ();
			showmessage ( L ( 'add_success' ) );
		} else {
			$show_validator = '';
			$admin_data = array ();
			$result = $this->admin_db->select ();
			foreach ( $result as $_value ) {
				if ($_value ['roleid'] == 1) continue;
				$admin_data [$_value ['username']] = $_value ['username'];
			}
			include $this->admin_tpl ( 'workflow_add' );
		}
	}

	/**
	 * 修改工作流
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$workflowid = intval ( $_POST ['workflowid'] );
			$_POST ['info'] ['workname'] = safe_replace ( $_POST ['info'] ['workname'] );
			$setting [1] = isset ( $_POST ['checkadmin1'] ) ? $_POST ['checkadmin1'] : '';
			$setting [2] = isset ( $_POST ['checkadmin2'] ) ? $_POST ['checkadmin2'] : '';
			$setting [3] = isset ( $_POST ['checkadmin3'] ) ? $_POST ['checkadmin3'] : '';
			$setting [4] = isset ( $_POST ['checkadmin4'] ) ? $_POST ['checkadmin4'] : '';
			$setting ['nocheck_users'] = isset ( $_POST ['nocheck_users'] ) ? $setting [1] : '';
			$setting = array2string ( $setting );
			$_POST ['info'] ['setting'] = $setting;
			$this->db->where(array ('workflowid' => $workflowid ))->update ( $_POST ['info'] );
			$this->_cache ();
			showmessage ( L ( 'update_success' ), '', '', 'edit' );
		} else {
			$show_header = $show_validator = '';
			$workflowid = intval ( $_GET ['workflowid'] );
			$admin_data = array ();
			$result = $this->admin_db->select ();
			foreach ( $result as $_value ) {
				if ($_value ['roleid'] == 1) continue;
				$admin_data [$_value ['username']] = $_value ['username'];
			}
			$r = $this->db->where ( array ('workflowid' => $workflowid ) )->find();
			extract ( $r );
			$setting = string2array ( $setting );
			$checkadmin1 = $this->implode_ids ( $setting [1] );
			$checkadmin2 = $this->implode_ids ( $setting [2] );
			$checkadmin3 = $this->implode_ids ( $setting [3] );
			$checkadmin4 = $this->implode_ids ( $setting [4] );
			$nocheck_users = $this->implode_ids ( $setting ['nocheck_users'] );
			include $this->admin_tpl ( 'workflow_edit' );
		}
	}

	/**
	 * 查看工作流
	 */
	public function view() {
		$show_header = '';
		$workflowid = intval ( $_GET ['workflowid'] );
		$admin_data = array ();
		$result = $this->admin_db->select ();
		foreach ( $result as $_value ) {
			if ($_value ['roleid'] == 1) continue;
			$admin_data [$_value ['username']] = $_value ['username'];
		}
		$r = $this->db->where ( array ('workflowid' => $workflowid ) )->find();
		extract ( $r );
		$setting = string2array ( $setting );
		$checkadmin1 = is_array ( $setting [1] ) ? $this->implode_ids ( $setting [1], '、' ) : 'Null';
		$checkadmin2 = is_array ( $setting [2] ) ? $this->implode_ids ( $setting [2], '、' ) : 'Null';
		$checkadmin3 = is_array ( $setting [3] ) ? $this->implode_ids ( $setting [3], '、' ) : 'Null';
		$checkadmin4 = is_array ( $setting [4] ) ? $this->implode_ids ( $setting [4], '、' ) : 'Null';
		include $this->admin_tpl ( 'workflow_view' );
	}

	/**
	 * 删除工作流
	 */
	public function delete() {
		$_GET ['workflowid'] = intval ( $_GET ['workflowid'] );
		$this->db->where ( array ('workflowid' => $_GET ['workflowid'] ) )->find();
		$this->_cache ();
		exit ( '1' );
	}

	/**
	 * 用逗号分隔数组
	 */
	private function implode_ids($array, $flags = ',') {
		if (empty ( $array )) return true;
		$length = strlen ( $flags );
		$string = '';
		foreach ( $array as $_v )
			$string .= $_v . $flags;
		return substr ( $string, 0, - $length );
	}

	public function _cache(){
		$datas = array ();
		$workflow_datas = $this->db->select ();
		foreach ( $workflow_datas as $_k => $_v )
			$datas [$_v ['workflowid']] = $_v;
		S ( 'common/workflow', $datas );
		return true;
	}
}