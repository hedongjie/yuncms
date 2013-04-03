<?php
// 模型原型存储路径
define ( 'MODEL_PATH', APPS_PATH . 'formguide' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::lib ( 'admin:admin', false );
class Formguide_fieldController extends admin {
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_field_model' );
		$this->model_db = Loader::model ( 'model_model' );
	}
	public function init() {
		if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
			$formid = intval ( $_GET ['formid'] );
			$this->cache_field ( $formid );
			$datas = $this->db->where ( array ('modelid' => $formid ) )->order ( 'listorder ASC' )->select ();
			$r = $this->model_db->getby_modelid ( $formid );
		} else {
			$data = $datas = array ();
			$data = S ( 'model/form_public_field_array' );
			if (is_array ( $data )) {
				foreach ( $data as $_k => $_v ) {
					$datas [$_k] = $_v ['info'];
				}
			}
		}
		$show_header = $show_validator = $show_dialog = '';
		require MODEL_PATH . 'fields.inc.php';
		include $this->admin_tpl ( 'formguide_field_list' );
	}

	/**
	 * 添加字段，当没有formid时为添加公用字段
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] = isset ( $_POST ['info'] ['minlength'] ) && ! empty ( $_POST ['info'] ['minlength'] ) ? intval ( $_POST ['info'] ['minlength'] ) : 0;
			$maxlength = $_POST ['info'] ['maxlength'] = isset ( $_POST ['info'] ['maxlength'] ) && ! empty ( $_POST ['info'] ['maxlength'] ) ? intval ( $_POST ['info'] ['maxlength'] ) : 0;
			$field_type = $_POST ['info'] ['formtype'];
			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';

			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';

			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			if (isset ( $_POST ['info'] ['modelid'] ) && ! empty ( $_POST ['info'] ['modelid'] )) {
				$formid = intval ( $_POST ['info'] ['modelid'] );
				$forminfo = $this->model_db->where ( array ('modelid' => $formid ) )->field ( 'tablename' )->find ();
				$tablename = $this->db->get_prefix () . 'form_' . $forminfo ['tablename'];
				$unrunsql = false;
				require MODEL_PATH . 'add.sql.php';

				$this->db->insert ( $_POST ['info'] );
				$this->cache_field ( $formid );
			} else {
				$unrunsql = true;
				$tablename = 'formguide_table';
				require MODEL_PATH . 'add.sql.php';

				$form_public_field_array = S ( 'model/form_public_field_array' );
				if (is_array ( $form_public_field_array ) && array_key_exists ( $_POST ['info'] ['field'], $form_public_field_array )) {
					showmessage ( L ( 'fields' ) . L ( 'already_exist' ), HTTP_REFERER );
				} else {
					$form_public_field_array [$_POST ['info'] ['field']] = array ('info' => $_POST ['info'],'sql' => $sql );
					S ( 'model/form_public_field_array', $form_public_field_array );
				}
			}
			showmessage ( L ( 'add_success' ), U ( 'formguide/formguide_field/init', array ('formid' => isset ( $formid ) ? $formid : '' ) ) );
		} else {
			$show_header = $show_validator = $show_dialog = '';
			require MODEL_PATH . 'fields.inc.php';
			$formid = intval ( $_GET ['formid'] );
			$f_datas = $this->db->where ( array ('modelid' => $formid ) )->field ( 'field,name' )->order ( 'listorder ASC' )->select ();
			$m_r = $this->model_db->getby_modelid ( $formid );
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
			include $this->admin_tpl ( 'formguide_field_add' );
		}
	}
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$field = $_POST ['info'] ['field'];
			$minlength = $_POST ['info'] ['minlength'] = isset ( $_POST ['info'] ['minlength'] ) && ! empty ( $_POST ['info'] ['minlength'] ) ? intval ( $_POST ['info'] ['minlength'] ) : 0;
			$maxlength = $_POST ['info'] ['maxlength'] = isset ( $_POST ['info'] ['maxlength'] ) && ! empty ( $_POST ['info'] ['maxlength'] ) ? intval ( $_POST ['info'] ['maxlength'] ) : 0;
			$field_type = $_POST ['info'] ['formtype'];

			// 附加属性值
			$_POST ['info'] ['setting'] = array2string ( $_POST ['setting'] );
			$_POST ['info'] ['unsetgroupids'] = isset ( $_POST ['unsetgroupids'] ) ? implode ( ',', $_POST ['unsetgroupids'] ) : '';
			$_POST ['info'] ['unsetroleids'] = isset ( $_POST ['unsetroleids'] ) ? implode ( ',', $_POST ['unsetroleids'] ) : '';

			require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';

			if (isset ( $_POST ['setting'] ['fieldtype'] )) {
				$field_type = $_POST ['setting'] ['fieldtype'];
			}
			$oldfield = $_POST ['oldfield'];
			if (isset ( $_POST ['info'] ['modelid'] ) && ! empty ( $_POST ['info'] ['modelid'] )) {
				$formid = intval ( $_POST ['info'] ['modelid'] );
				$forminfo = $this->model_db->where ( array ('modelid' => $formid ) )->field ( 'tablename' )->find ();
				$tablename = $this->db->get_prefix () . 'form_' . $forminfo ['tablename'];

				$fieldid = intval ( $_POST ['fieldid'] );
				$unrunsql = false;
				require MODEL_PATH . 'edit.sql.php';
				$this->db->where(array ('fieldid' => $fieldid ))->update ( $_POST ['info'] );
			} else {
				$unrunsql = true;
				$tablename = 'formguide_table';
				require MODEL_PATH . 'add.sql.php';

				$form_public_field_array = S ( 'model/form_public_field_array' );
				if ($oldfield) {
					if (isset ( $form_public_field_array [$oldfield] ['info'] ['listorder'] )) {
						$_POST ['info'] ['listorder'] = $form_public_field_array [$oldfield] ['info'] ['listorder'];
					}
					if ($oldfield == $_POST ['info'] ['field']) {
						$form_public_field_array [$_POST ['info'] ['field']] = array ('info' => $_POST ['info'],'sql' => $sql );
					} else {
						if (is_array ( $form_public_field_array ) && array_key_exists ( $_POST ['info'] ['field'], $form_public_field_array )) {
							showmessage ( L ( 'fields' ) . L ( 'already_exist' ), HTTP_REFERER );
						}
						$new_form_field = $form_public_field_array;
						$form_public_field_array = array ();
						foreach ( $new_form_field as $name => $v ) {
							if ($name == $oldfield) {
								$form_public_field_array [$_POST ['info'] ['field']] = array ('info' => $_POST ['info'],'sql' => $sql );
							} else {
								$form_public_field_array [$name] = $v;
							}
						}
					}
				}
				S ( 'model/form_public_field_array', $form_public_field_array );
			}
			showmessage ( L ( 'update_success' ), U ( 'formguide/formguide_field/init', array ('formid' => isset ( $formid ) ? $formid : '' ) ) );
		} else {
			if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
				require MODEL_PATH . 'fields.inc.php';
				$formid = intval ( $_GET ['formid'] );
				$fieldid = intval ( $_GET ['fieldid'] );

				$m_r = $this->model_db->getby_modelid ( $formid );
				$r = $this->db->getby_fieldid ( $fieldid);
				extract ( $r );
				require MODEL_PATH . $formtype . DIRECTORY_SEPARATOR . 'config.inc.php';
			} else {
				if (! isset ( $_GET ['field'] ) || empty ( $_GET ['field'] )) {
					showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
				}

				$form_public_field_array = S ( 'model/form_public_field_array' );
				if (! array_key_exists ( $_GET ['field'], $form_public_field_array )) {
					showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
				}
				extract ( $form_public_field_array [$_GET ['field']] );
				extract ( $info );
				$setting = stripslashes ( $setting );
				$show_header = $show_validator = $show_dialog = '';
				require MODEL_PATH . 'fields.inc.php';
			}
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
			include $this->admin_tpl ( 'formguide_field_edit' );
		}
	}

	/**
	 * 禁用、开启字段
	 */
	public function disabled() {
		$fieldid = intval ( $_GET ['fieldid'] );
		$disabled = $_GET ['disabled'] ? 0 : 1;
		$this->db->where(array ('fieldid' => $fieldid ))->update ( array ('disabled' => $disabled ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 删除字段
	 */
	public function delete() {
		if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] ) && isset ( $_GET ['fieldid'] ) && ! empty ( $_GET ['fieldid'] )) {
			$formid = intval ( $_GET ['formid'] );
			$fieldid = intval ( $_GET ['fieldid'] );
			$r = $this->model_db->where ( array ('modelid' => $formid ))->field( 'tablename' )->find();
			$rs = $this->db->where( array ('fieldid' => $fieldid ))->field( 'field' )->find();
			$this->db->where(array ('fieldid' => $fieldid ))->delete (  );
			if ($r) {
				$field = $rs ['field'];
				$tablename = $this->db->get_prefix () . 'form_' . $r ['tablename'];
				require MODEL_PATH . 'delete.sql.php';
			}
		} else {
			if (! isset ( $_GET ['field'] ) || empty ( $_GET ['field'] )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
			$field = $_GET ['field'];
			$form_public_field_array = S ( 'model/form_public_field_array' );
			if (is_array ( $form_public_field_array ) && array_key_exists ( $field, $form_public_field_array )) {
				unset ( $form_public_field_array [$field] );
			}
			S ( 'model/form_public_field_array', $form_public_field_array );
		}
		showmessage ( L ( 'update_success' ), U ( 'formguide/formguide_field/init', array ('formid' => isset ( $formid ) ? $formid : '' ) ) );
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (isset ( $_GET ['formid'] ) && ! empty ( $_GET ['formid'] )) {
				foreach ( $_POST ['listorders'] as $id => $listorder ) {
					$this->db->where(array ('fieldid' => $id ))->update ( array ('listorder' => $listorder ) );
				}
			} else {
				$form_public_field_array = S ( 'model/form_public_field_array' );
				asort ( $_POST ['listorders'] );
				$new_form_field = array ();
				foreach ( $_POST ['listorders'] as $id => $listorder ) {
					$form_public_field_array [$id] ['info'] ['listorder'] = $listorder;
					$new_form_field [$id] = $form_public_field_array [$id];
				}
				unset ( $form_public_field_array );
				S ( 'model/form_public_field_array', $new_form_field );
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
		$oldfield = isset ( $_GET ['oldfield'] ) ? strtolower ( $_GET ['oldfield'] ) : '';
		if ($field == $oldfield) {
			exit ( '1' );
		}
		$modelid = isset ( $_GET ['modelid'] ) ? intval ( $_GET ['modelid'] ) : false;
		if (in_array ( $field, array ('dataid','userid','username','datetime','ip' ) )) {
			exit ( '0' );
		}
		if ($modelid) {
			$forminfo = $this->model_db->where ( array ('modelid' => $modelid ))->field( 'tablename' )->find();
			$fields = $this->db->get_fields ('form_' . $forminfo ['tablename']);
		} else {
			$fields = S ( 'model/form_public_field_array' );
		}
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

	/**
	 * 更新指定表单向导的字段缓存
	 *
	 * @param $formid 表单向导id
	 * @param $disabled 字段状态
	 */
	public function cache_field($formid = 0, $disabled = 0) {
		$field_array = array ();
		$fields = $this->db->where ( array ('modelid' => $formid,'disabled' => $disabled ) )->order ( 'listorder ASC' )->select ();
		foreach ( $fields as $_value ) {
			$setting = string2array ( $_value ['setting'] );
			$_value = array_merge ( $_value, $setting );
			$field_array [$_value ['field']] = $_value;
		}
		S ( 'model/formguide_field_' . $formid, $field_array );
		return true;
	}
}
?>