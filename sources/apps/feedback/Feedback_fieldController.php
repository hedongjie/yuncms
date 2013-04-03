<?php
// 模型原型存储路径
define ( 'MODEL_PATH', APPS_PATH . 'feedback' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::lib ( 'admin:admin', false );
class Feedback_fieldController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_field_model' );
		$this->model_db = Loader::model ( 'model_model' );
		$this->feedback_db = Loader::model('feedback_model');
		$mode_info = $this->model_db->get_one(array('type'=>4),'*');
		$this->modelid = $mode_info['modelid'];
	}

	public function init() {
		$datas = $this->db->select ( array ('modelid' => $this->modelid ), '*', 100, 'listorder ASC' );
		$r = $this->model_db->get_one ( array ('modelid' => $this->modelid ) );
		$show_header = $show_validator = $show_dialog = '';
		require MODEL_PATH . 'fields.inc.php';
		include $this->admin_tpl ( 'feedback_field_list' );
	}

	/**
	 * 添加字段，当没有formid时为添加公用字段
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] = isset($_POST ['info'] ['minlength']) && !empty($_POST ['info'] ['minlength']) ? intval($_POST ['info'] ['minlength']) : 0;
			$maxlength = $_POST ['info'] ['maxlength'] = isset($_POST ['info'] ['maxlength']) && !empty($_POST ['info'] ['maxlength']) ? intval($_POST ['info'] ['maxlength']) : 0;
			$field_type = $_POST ['info'] ['formtype'];
			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			$_POST ['info'] ['modelid'] = $this->modelid;
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			$tablename = $this->feedback_db->table_name;
			require MODEL_PATH . 'add.sql.php';
			$this->db->insert ( $_POST ['info'] );
			$this->cache_field ( $this->modelid );
			showmessage ( L ( 'add_success' ), U ( 'feedback/feedback_field/init') );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$f_datas = $this->db->select ( array ('modelid' => $this->modelid ), 'field,name', 100, 'listorder ASC' );
			$m_r = $this->model_db->get_one ( array ('modelid' => $this->modelid ) );
			foreach ( $f_datas as $_k => $_v ) {
				$exists_field [] = $_v ['field'];
			}
			$all_field = array ();
			foreach ( $fields as $_k => $_v ) {
				$all_field [$_k] = $_v;
			}

			$grouplist = array ();
			// 会员组缓存
			$group_cache = S ( 'member/grouplist' );
			foreach ( $group_cache as $_key => $_value ) {
				$grouplist [$_key] = $_value ['name'];
			}
			header ( "Cache-control: private" );
			include $this->admin_tpl ( 'feedback_field_add' );
		}
	}

	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] = isset($_POST ['info'] ['minlength']) && !empty($_POST ['info'] ['minlength']) ? intval($_POST ['info'] ['minlength']) : 0;
			$maxlength = $_POST ['info'] ['maxlength'] = isset($_POST ['info'] ['maxlength']) && !empty($_POST ['info'] ['maxlength']) ? intval($_POST ['info'] ['maxlength']) : 0;
			$field_type = $_POST ['info'] ['formtype'];

			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';
			$_POST ['info'] ['modelid'] = $this->modelid;
			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';

			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			$oldfield = $_POST ['oldfield'];
			$tablename = $this->feedback_db->table_name;
			$fieldid = intval ( $_POST ['fieldid'] );
			$unrunsql = false;
			require MODEL_PATH . 'edit.sql.php';
			$this->db->update ( $_POST ['info'], array ('fieldid' => $fieldid ) );
			showmessage ( L ( 'update_success' ), U ( 'feedback/feedback_field/init' ) );
		} else {
			$fieldid = intval ( $_GET ['fieldid'] );
			$m_r = $this->model_db->get_one ( array ('modelid' => $this->modelid ) );
			$r = $this->db->get_one ( array ('fieldid' => $fieldid ) );
			extract ( $r );
			require MODEL_PATH . $formtype . DIRECTORY_SEPARATOR . 'config.inc.php';
			$setting = string2array ( $setting );
			ob_start ();
			include MODEL_PATH . $formtype . DIRECTORY_SEPARATOR . 'field_edit_form.inc.php';
			$form_data = ob_get_contents ();
			ob_end_clean ();
			// 会员组缓存
			$group_cache = S ( 'member/grouplist' );
			foreach ( $group_cache as $_key => $_value ) {
				$grouplist [$_key] = $_value ['name'];
			}
			header ( "Cache-control: private" );
			include $this->admin_tpl ( 'feedback_field_edit' );
		}
	}

	/**
	 * 禁用、开启字段
	 */
	public function disabled() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$disabled = $_GET ['disabled'] ? 0 : 1;
		$this->db->update ( array ('disabled' => $disabled ), array ('fieldid' => $fieldid ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 删除字段
	 */
	public function delete() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$r = $this->model_db->get_one ( array ('modelid' => $this->modelid ), 'tablename' );
		$rs = $this->db->get_one ( array ('fieldid' => $fieldid ), 'field' );
		$this->db->delete ( array ('fieldid' => $fieldid ) );
		if ($r) {
			$field = $rs ['field'];
			$tablename = $this->feedback_db->table_name;
			require MODEL_PATH . 'delete.sql.php';
		}
		showmessage ( L ( 'update_success' ), U ( 'feedback/feedback_field/init' ) );
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->update ( array ('listorder' => $listorder ), array ('fieldid' => $id ) );
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
		$oldfield = isset ( $_GET ['oldfield'] ) ? strtolower ( $_GET ['oldfield'] ) : 0;
		if ($field == $oldfield) exit ( '1' );
		if (in_array ( $field, array ('fid','userid','username','datetime','ip' ) )) {
			exit ( '0' );
		}
		$fields = $this->feedback_db->get_fields ();
		if (is_array ( $fields ) && array_key_exists ( $field, $fields )) {
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
		$settings = @array ('field_basic_table' => $field_basic_table,'field_minlength' => $field_minlength,'field_maxlength' => $field_maxlength,'field_allow_search' => $field_allow_search,'field_allow_fulltext' => $field_allow_fulltext,'field_allow_isunique' => $field_allow_isunique,
				'setting' => $data_setting );
		echo json_encode ( $settings );
		return true;
	}

	public function cache_field() {
		$field_array = array ();
		$fields = $this->db->get_one ( array ('modelid' => $this->modelid));
		$setting = string2array ( $fields ['setting'] );
		$_value = array_merge ( $fields, $setting );
		$field_array [$fields ['field']] = $_value;
		W ( 'model/feedback_field', $field_array );
		return true;
	}
}
?>