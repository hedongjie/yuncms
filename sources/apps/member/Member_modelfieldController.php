<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
define ( 'MODEL_PATH', APPS_PATH . 'member' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
/**
 * 会员模型字段管理
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Member_modelfieldController.php 855 2012-06-08 09:42:33Z
 *          85825770@qq.com $
 */

class Member_modelfieldController extends admin {

	function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_field_model' );
		$this->model_db = Loader::model ( 'model_model' );
	}

	public function manage() {
		$modelid = $_GET ['modelid'];
		$datas = $this->cache_field ( $modelid );
		$modelinfo = $this->model_db->get_one ( array ('modelid' => $modelid ) );
		$big_menu = big_menu ( '?app=member&controller=member_modelfield&action=add&modelid=' . $modelinfo ['modelid'], 'add', L ( 'member_modelfield_add' ), 700, 500 );
		include $this->admin_tpl ( 'member_modelfield_list' );
	}

	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$model_cache = S ( 'common/member_model' );
			$modelid = $_POST ['info'] ['modelid'] = intval ( $_POST ['info'] ['modelid'] );
			$model_table = $model_cache [$modelid] ['tablename'];
			$tablename = $this->db->get_prefix () . $model_table;
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] ? $_POST ['info'] ['minlength'] : 0;
			$maxlength = $_POST ['info'] ['maxlength'] ? $_POST ['info'] ['maxlength'] : 0;
			$field_type = $_POST ['info'] ['formtype'];
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			require MODEL_PATH . 'add.sql.php';
			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			$_POST ['info'] ['maxlength'] = isset ( $_POST ['info'] ['maxlength'] ) && ! empty ( $_POST ['info'] ['maxlength'] ) ? $_POST ['info'] ['maxlength'] : '0';
			$this->db->insert ( $_POST ['info'] );
			$this->cache_field ( $modelid );
			showmessage ( L ( 'operation_success' ), U ( 'member/member_model/manage' ), '', 'add' );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$modelid = $_GET ['modelid'];
			// 角色缓存
			$roles = S ( 'common/role' );
			// 会员组缓存
			$group_cache = S ( 'member/grouplist' );
			foreach ( $group_cache as $_key => $_value ) {
				$grouplist [$_key] = $_value ['name'];
			}
			header ( "Cache-control: private" );
			include $this->admin_tpl ( 'member_modelfield_add' );
		}
	}

	/**
	 * 修改
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$model_cache = S ( 'common/member_model' );
			$modelid = $_POST ['info'] ['modelid'] = intval ( $_POST ['info'] ['modelid'] );
			$model_table = $model_cache [$modelid] ['tablename'];
			$tablename = $this->db->get_prefix () . $model_table;
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] ? $_POST ['info'] ['minlength'] : 0;
			$maxlength = $_POST ['info'] ['maxlength'] ? $_POST ['info'] ['maxlength'] : 0;
			$field_type = $_POST ['info'] ['formtype'];
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			$oldfield = $_POST ['oldfield'];
			require MODEL_PATH . 'edit.sql.php';
			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$fieldid = intval ( $_POST ['fieldid'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			$this->db->update ( $_POST ['info'], array ('fieldid' => $fieldid ) );
			$this->cache_field ( $modelid );
			// 更新模型缓存
			Loader::lib ( 'member:member_cache', false );
			member_cache::update_cache_model ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'edit' );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$modelid = intval ( $_GET ['modelid'] );
			$fieldid = intval ( $_GET ['fieldid'] );
			$r = $this->db->get_one ( array ('fieldid' => $fieldid ) );
			extract ( $r );
			$setting = string2array ( $setting );
			ob_start ();
			include MODEL_PATH . $formtype . DIRECTORY_SEPARATOR . 'field_edit_form.inc.php';
			$form_data = ob_get_contents ();
			ob_end_clean ();
			// 角色缓存
			$roles = S ( 'common/role' );
			$grouplist = array ();
			// 会员组缓存
			$group_cache = S ( 'member/grouplist' );
			foreach ( $group_cache as $_key => $_value ) {
				$grouplist [$_key] = $_value ['name'];
			}
			header ( "Cache-control: private" );
			include $this->admin_tpl ( 'member_modelfield_edit' );
		}
	}

	public function delete() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$r = $this->db->get_one ( array ('fieldid' => $fieldid ) );
		// 删除模型字段
		$this->db->delete ( array ('fieldid' => $fieldid ) );
		// 删除表字段
		$model_cache = S ( 'common/member_model' );
		$model_table = $model_cache [$r ['modelid']] ['tablename'];
		$this->db->drop_field ( $model_table, $r ['field'] );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 禁用字段
	 */
	public function disable() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$disabled = intval ( $_GET ['disabled'] );
		$this->db->update ( array ('disabled' => $disabled ), array ('fieldid' => $fieldid ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 排序
	 */
	public function sort() {
		if (isset ( $_POST ['listorders'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->update ( array ('listorder' => $listorder ), array ('fieldid' => $id ) );
			}
			showmessage ( L ( 'operation_success' ) );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 检查字段是否存在
	 */
	public function public_checkfield() {
		$field = strtolower ( $_GET ['field'] );
		$oldfield = isset($_GET ['oldfield']) ? strtolower ( $_GET ['oldfield'] ) : null;
		if ($field == $oldfield) exit ( '1' );
		$modelid = intval ( $_GET ['modelid'] );
		$model_cache = S ( 'common/member_model' );
		$tablename = $model_cache [$modelid] ['tablename'];
		$this->db->table_name = $this->db->get_prefix () . $tablename;
		$fields = $this->db->get_fields ();
		if (array_key_exists ( $field, $fields )) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * 字段属性设置
	 */
	public function public_field_setting() {
		$fieldtype = $_GET ['fieldtype'];
		require MODEL_PATH . $fieldtype . DIRECTORY_SEPARATOR . 'config.inc.php';
		ob_start ();
		include MODEL_PATH . $fieldtype . DIRECTORY_SEPARATOR . 'field_add_form.inc.php';
		$data_setting = ob_get_contents ();
		ob_end_clean ();
		$settings = array ('field_basic_table' => $field_basic_table,'field_minlength' => $field_minlength,'field_maxlength' => $field_maxlength,'field_allow_search' => $field_allow_search,'field_allow_fulltext' => $field_allow_fulltext,'field_allow_isunique' => $field_allow_isunique,'setting' => $data_setting );
		echo json_encode ( $settings );
		return true;
	}

	/**
	 * 更新指定模型字段缓存
	 *
	 * @param $modelid 模型id
	 */
	public function cache_field($modelid = 0) {
		$field_array = array ();
		$fields = $this->db->select ( array ('modelid' => $modelid ), '*', 100, 'listorder ASC' );
		foreach ( $fields as $_value ) {
			$setting = string2array ( $_value ['setting'] );
			$_value = array_merge ( $_value, $setting );
			$field_array [$_value ['field']] = $_value;
		}
		W ( 'member/model_field_' . $modelid, $field_array );
		return $field_array;
	}

}