<?php
/**
 * 模型字段管理
 * @author		YUNCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, NewsTeng, Inc.
 * @license	http://www.yuncms.net/about/license
 * @link		http://www.yuncms.net
 * $Id: Model_fieldController.php 307 2012-11-11 11:24:56Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
define ( 'MODEL_PATH', APPS_PATH . 'content' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::lib ( 'admin:admin', false );
//error_reporting ( E_ERROR );
class Model_fieldController extends admin {

	private $db, $model_db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_field_model' );
		$this->model_db = Loader::model ( 'model_model' );
	}

	/**
	 * 模型字段管理
	 */
	public function init() {
		$show_header = '';
		$modelid = intval ( $_GET ['modelid'] );
		$this->cache_field ( $modelid );
		$datas = $this->db->where(array ('modelid' => $modelid ))->order('listorder ASC')->select (  );
		$r = $this->model_db->where ( array ('modelid' => $modelid ) )->find();
		require MODEL_PATH . 'fields.inc.php';
		include $this->admin_tpl ( 'model_field_manage' );
	}

	/**
	 * 添加字段
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$model_cache = S ( 'common/model' );
			$modelid = $_POST ['info'] ['modelid'] = intval ( $_POST ['info'] ['modelid'] );
			$model_table = $model_cache [$modelid] ['tablename'];
			$tablename = $_POST ['issystem'] ? $this->db->get_prefix () . $model_table : $this->db->get_prefix () . $model_table . '_data';
			$field = $_POST ['info'] ['field'];
			$minlength = isset ( $_POST ['info'] ['minlength'] ) ? $_POST ['info'] ['minlength'] : 0;
			$maxlength = isset ( $_POST ['info'] ['maxlength'] ) ? $_POST ['info'] ['maxlength'] : 0;
			$field_type = $_POST ['info'] ['formtype'];
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			require MODEL_PATH . 'add.sql.php';
			// 附加属性值
			$_POST ['info'] ['setting'] = isset ( $_POST ['setting'] ) ? array2string ( $_POST ['setting'] ) : '';
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			if (empty ( $_POST ['info'] ['maxlength'] )) $_POST ['info'] ['maxlength'] = '0';
			$this->db->insert ( $_POST ['info'] );
			$this->cache_field ( $modelid );
			showmessage ( L ( 'add_success' ), '?app=content&controller=model_field&action=init&modelid=' . $modelid . '&menuid=135' );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$modelid = $_GET ['modelid'];
			$f_datas = $this->db->where(array ('modelid' => $modelid ))->order('listorder ASC')->field('field,name')->select ();
			$m_r = $this->model_db->where ( array ('modelid' => $modelid ) )->find();
			foreach ( $f_datas as $_k => $_v ) {
				$exists_field [] = $_v ['field'];
			}
			$all_field = array ();
			foreach ( $fields as $_k => $_v ) {
				if (in_array ( $_k, $not_allow_fields ) || in_array ( $_k, $exists_field ) && in_array ( $_k, $unique_fields )) continue;
				$all_field [$_k] = $_v;
			}

			$modelid = $_GET ['modelid'];
			// 角色缓存
			$roles = S ( 'common/role' );
			$grouplist = array ();
			// 会员组缓存
			$group_cache = S ( 'member/grouplist' );
			foreach ( $group_cache as $_key => $_value ) {
				$grouplist [$_key] = $_value ['name'];
			}
			header ( "Cache-control: private" );
			include $this->admin_tpl ( 'model_field_add' );
		}
	}

	/**
	 * 字段修改
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$model_cache = S ( 'common/model' );
			$modelid = $_POST ['info'] ['modelid'] = intval ( $_POST ['info'] ['modelid'] );
			$model_table = $model_cache [$modelid] ['tablename'];
			$tablename = $_POST ['issystem'] ? $this->db->get_prefix () . $model_table : $this->db->get_prefix () . $model_table . '_data';
			$field = $_POST ['info'] ['field'];
			$minlength = isset ( $_POST ['info'] ['minlength'] ) ? $_POST ['info'] ['minlength'] : "0";
			$maxlength = isset ( $_POST ['info'] ['maxlength'] ) ? $_POST ['info'] ['maxlength'] : "0";
			$field_type = $_POST ['info'] ['formtype'];
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			$oldfield = $_POST ['oldfield'];
			require MODEL_PATH . 'edit.sql.php';
			// 附加属性值
			$_POST ['info'] ['setting'] = isset ( $_POST ['setting'] ) ? array2string ( $_POST ['setting'] ) : '';
			$fieldid = intval ( $_POST ['fieldid'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			$this->db->where(array ('fieldid' => $fieldid ))->update ( $_POST ['info'] );
			$this->cache_field ( $modelid );
			showmessage ( L ( 'update_success' ), '?app=content&controller=model_field&action=init&modelid=' . $modelid . '&menuid=93' );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$modelid = intval ( $_GET ['modelid'] );
			$fieldid = intval ( $_GET ['fieldid'] );
			$m_r = $this->model_db->where ( array ('modelid' => $modelid ) )->find();
			$r = $this->db->where ( array ('fieldid' => $fieldid ) )->find();
			extract ( $r );
			require MODEL_PATH . $formtype . DIRECTORY_SEPARATOR . 'config.inc.php';
			$setting = string2array( $setting );
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
			include $this->admin_tpl ( 'model_field_edit' );
		}
	}

	/**
	 * 禁用启用字段
	 */
	public function disabled() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$disabled = $_GET ['disabled'] ? 0 : 1;
		$this->db->where(array ('fieldid' => $fieldid ))->update ( array ('disabled' => $disabled ) );
		$modelid = $_GET ['modelid'];
		$this->cache_field ( $modelid );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 删除字段
	 */
	public function delete() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$r = $this->db->where ( array ('fieldid' => $_GET ['fieldid'] ) )->find();
		// 必须放在删除字段前、在删除字段部分，重置了 tablename
		$this->db->where ( array ('fieldid' => $_GET ['fieldid'] ) )->delete();
		$model_cache = S ( 'common/model' );
		$modelid = intval ( $_GET ['modelid'] );
		$model_table = $model_cache [$modelid] ['tablename'];
		$tablename = $r ['issystem'] ? $model_table : $model_table . '_data';
		$this->db->drop_field ( $tablename, $r ['field'] );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->where(array ('fieldid' => $id ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 检查字段是否存在
	 */
	public function public_checkfield() {
		$field = strtolower ( $_GET ['field'] );
		if (isset ( $_GET ['oldfield'] )) {
			$oldfield = strtolower ( $_GET ['oldfield'] );
			if ($field == $oldfield) exit ( '1' );
		}
		$modelid = intval ( $_GET ['modelid'] );
		$model_cache = S ( 'common/model' );
		$tablename = $model_cache [$modelid] ['tablename'];
		$issystem = intval ( $_GET ['issystem'] );
		if ($issystem) {
			$this->db->table_name = $this->db->get_prefix () . $tablename;
		} else {
			$this->db->table_name = $this->db->get_prefix () . $tablename . '_data';
		}
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
		$settings = array ('field_basic_table' => $field_basic_table,'field_minlength' => $field_minlength,'field_maxlength' => $field_maxlength,'field_allow_search' => $field_allow_search,'field_allow_fulltext' => $field_allow_fulltext,'field_allow_isunique' => $field_allow_isunique,
				'setting' => $data_setting );
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
		$fields = $this->db->where(array ('modelid' => $modelid,'disabled' => 0 ))->order('listorder ASC')->select ();
		foreach ( $fields as $_value ) {
			$setting = string2array ( $_value ['setting'] );
			$_value = array_merge ( $_value, $setting );
			$field_array [$_value ['field']] = $_value;
		}
		S ( 'model/model_field_' . $modelid, $field_array );
		return true;
	}

	/**
	 * 预览模型
	 */
	public function public_priview() {
		$show_header = $show_validator = $show_dialog = '';
		$modelid = intval ( $_GET ['modelid'] );
		require CACHE_MODEL_PATH . 'content_form.php';
		$content_form = new content_form ( $modelid );
		$r = $this->model_db->where ( array ('modelid' => $modelid ) )->find();
		$forminfos = $content_form->get ();
		include $this->admin_tpl ( 'model_priview' );
	}
}