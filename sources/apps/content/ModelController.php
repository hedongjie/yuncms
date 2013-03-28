<?php
/**
 * 内容模型管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
define ( 'MODEL_PATH', APPS_PATH . 'content' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
Loader::lib ( 'admin:admin', false );
// error_reporting ( E_ERROR );
class ModelController extends admin {

	private $db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_model' );
	}

	/**
	 * 模型管理
	 */
	public function init() {
		$categorys = S ( 'common/category_content' );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = $this->db->where ( array ('type' => 0 ) )->listinfo ( $page, 30 );
		// 模型文章数array('模型id'=>数量);
		$items = array ();
		foreach ( $datas as $k => $r ) {
			$items [$r ['modelid']] = 0;
			foreach ( $categorys as $catid => $cat ) {
				if ($cat ['modelid'] == 0) continue;
				if (intval ( $cat ['modelid'] ) == intval ( $r ['modelid'] )) {
					$items [$r ['modelid']] += intval ( $cat ['items'] );
				} else {
					$items [$r ['modelid']] += 0;
				}
			}
			$datas [$k] ['items'] = $items [$r ['modelid']];
		}
		$pages = $this->db->pages;
		$this->public_cache ();
		$big_menu = big_menu ( U ( 'content/model/add' ), 'add', L ( 'add_model' ), 580, 420 );
		include $this->admin_tpl ( 'model_manage' );
	}

	/**
	 * 添加模型
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['category_template'] = $_POST ['setting'] ['category_template'];
			$_POST ['info'] ['list_template'] = $_POST ['setting'] ['list_template'];
			$_POST ['info'] ['show_template'] = $_POST ['setting'] ['show_template'];
			$modelid = $this->db->insert ( $_POST ['info'] );
			$model_sql = file_get_contents ( MODEL_PATH . 'model.sql' );
			$tablepre = $this->db->get_prefix ();
			$tablename = $_POST ['info'] ['tablename'];
			$model_sql = str_replace ( '$basic_table', $tablepre . $tablename, $model_sql );
			$model_sql = str_replace ( '$table_data', $tablepre . $tablename . '_data', $model_sql );
			$model_sql = str_replace ( '$table_model_field', $tablepre . 'model_field', $model_sql );
			$model_sql = str_replace ( '$modelid', $modelid, $model_sql );
			$this->db->sql_execute ( $model_sql );
			$this->cache_field ( $modelid );
			// 调用全站搜索类别接口
			$this->type_db = Loader::model ( 'type_model' );
			$this->type_db->insert ( array ('name' => $_POST ['info'] ['name'],'application' => 'search','modelid' => $modelid ) );
			$cache_api = Loader::lib ( 'admin:cache_api' );
			$cache_api->cache ( 'type' );
			$cache_api->search_type ();
			showmessage ( L ( 'add_success' ), '', '', 'add' );
		} else {
			$show_header = $show_validator = '';
			$style_list = template_list ( 0 );
			foreach ( $style_list as $k => $v ) {
				$style_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $style_list [$k] );
			}
			include $this->admin_tpl ( 'model_add' );
		}
	}

	/**
	 * 修改模型
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$modelid = intval ( $_POST ['modelid'] );
			$_POST ['info'] ['category_template'] = $_POST ['setting'] ['category_template'];
			$_POST ['info'] ['list_template'] = $_POST ['setting'] ['list_template'];
			$_POST ['info'] ['show_template'] = $_POST ['setting'] ['show_template'];
			$this->db->where ( array ('modelid' => $modelid ) )->update ( $_POST ['info'] );
			showmessage ( L ( 'update_success' ), '', '', 'edit' );
		} else {
			$show_header = $show_validator = '';
			$style_list = template_list ( 0 );
			foreach ( $style_list as $k => $v ) {
				$style_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $style_list [$k] );
			}
			$modelid = intval ( $_GET ['modelid'] );
			$r = $this->db->where ( array ('modelid' => $modelid ) )->find ();
			extract ( $r );
			include $this->admin_tpl ( 'model_edit' );
		}
	}

	/**
	 * 删除模型
	 */
	public function delete() {
		$this->model_field_db = Loader::model ( 'model_field_model' );
		$modelid = intval ( $_GET ['modelid'] );
		$model_cache = S ( 'common/model' );
		$model_table = $model_cache [$modelid] ['tablename'];
		$this->model_field_db->where ( array ('modelid' => $modelid ) )->delete ();
		$this->db->drop_table ( $model_table );
		$this->db->drop_table ( $model_table . '_data' );
		$this->db->where ( array ('modelid' => $modelid ) )->delete ();
		// 删除全站搜索接口数据
		$this->type_db = Loader::model ( 'type_model' );
		$this->type_db->where ( array ('application' => 'search','modelid' => $modelid ) )->delete ();
		$cache_api = Loader::lib ( 'admin:cache_api' );
		$cache_api->cache ( 'type' );
		$cache_api->search_type ();
		exit ( '1' );
	}

	/**
	 * 开启禁用模型
	 */
	public function disabled() {
		$modelid = intval ( $_GET ['modelid'] );
		$r = $this->db->getby_modelid ( $modelid );
		$status = $r ['disabled'] == '1' ? '0' : '1';
		$this->db->where ( array ('modelid' => $modelid ) )->update ( array ('disabled' => $status ) );
		showmessage ( L ( 'update_success' ), HTTP_REFERER );
	}

	/**
	 * 更新模型缓存
	 */
	public function public_cache() {
		require MODEL_PATH . 'fields.inc.php';
		// 更新内容模型类：表单生成、入库、更新、输出
		$classtypes = array ('form','input','update','output' );
		foreach ( $classtypes as $classtype ) {
			$cache_data = file_get_contents ( MODEL_PATH . 'content_' . $classtype . '.php' );
			$cache_data = str_replace ( '}?>', '', $cache_data );
			foreach ( $fields as $field => $fieldvalue ) {
				if (file_exists ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' )) {
					$cache_data_info = file_get_contents ( MODEL_PATH . $field . DIRECTORY_SEPARATOR . $classtype . '.inc.php' );
					$cache_data .= substr_between($cache_data_info,'<?php','?>');
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			file_put_contents ( CACHE_MODEL_PATH . 'content_' . $classtype . '.php', $cache_data );
			@chmod ( CACHE_MODEL_PATH . 'content_' . $classtype . '.php', 0777 );
		}
		// 更新模型数据缓存
		$model_array = array ();
		$datas = $this->db->where ( array ('type' => 0 ) )->select ();
		if ($datas) {
			foreach ( $datas as $r ) {
				if (! $r ['disabled']) $model_array [$r ['modelid']] = $r;
			}
			S ( 'common/model', $model_array );
		}
		return true;
	}

	/**
	 * 导出模型
	 */
	public function export() {
		$modelid = isset ( $_GET ['modelid'] ) ? $_GET ['modelid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$modelarr = S ( 'common/model' );
		// 定义系统字段排除
		$this->model_field_db = Loader::model ( 'model_field_model' );
		$modelinfo = $this->model_field_db->where ( array ('modelid' => $modelid ) )->select ();
		foreach ( $modelinfo as $k => $v ) {
			$modelinfoarr [$k] = $v;
			$modelinfoarr [$k] ['setting'] = string2array ( $v ['setting'] );
		}
		$res = var_export ( $modelinfoarr, TRUE );
		header ( 'Content-Disposition: attachment; filename="' . $modelarr [$modelid] ['tablename'] . '.model"' );
		echo $res;
		exit ();
	}

	/**
	 * 导入模型
	 */
	public function import() {
		if (isset ( $_POST ['dosubmit'] )) {
			$info = array ();
			$info ['name'] = $_POST ['info'] ['modelname'];
			// 主表表名
			$basic_table = $info ['tablename'] = $_POST ['info'] ['tablename'];
			// 从表表名
			$table_data = $basic_table . '_data';
			$info ['description'] = $_POST ['info'] ['description'];
			$info ['type'] = 0;
			$info ['default_style'] = $_POST ['default_style'];
			$info ['category_template'] = $_POST ['setting'] ['category_template'];
			$info ['list_template'] = $_POST ['setting'] ['list_template'];
			$info ['show_template'] = $_POST ['setting'] ['show_template'];

			if (! empty ( $_FILES ['model_import'] ['tmp_name'] )) {
				$model_import = @file_get_contents ( $_FILES ['model_import'] ['tmp_name'] );
				if (! empty ( $model_import )) {
					$model_import_data = string2array ( $model_import );
				}
			}
			$is_exists = $this->db->table_exists ( $basic_table );
			if ($is_exists) showmessage ( L ( 'operation_failure' ), U ( 'content/model/init' ) );
			$modelid = $this->db->add ( $info, 1 );
			if ($modelid) {
				$tablepre = $this->db->get_prefix ();
				// 建立数据表
				$model_sql = file_get_contents ( MODEL_PATH . 'model.sql' );
				$model_sql = str_replace ( '$basic_table', $tablepre . $basic_table, $model_sql );
				$model_sql = str_replace ( '$table_data', $tablepre . $table_data, $model_sql );
				$model_sql = str_replace ( '$table_model_field', $tablepre . 'model_field', $model_sql );
				$model_sql = str_replace ( '$modelid', $modelid, $model_sql );
				$this->db->sql_execute ( $model_sql );
				if (! empty ( $model_import_data )) {
					$this->model_field_db = Loader::model ( 'model_field_model' );
					$system_field = array ('title','style','catid','url','listorder','status','userid','username','inputtime','updatetime','pages','readpoint','template','groupids_view','posids','content','keywords','description','thumb','typeid','relation','islink','allow_comment' );
					foreach ( $model_import_data as $v ) {
						$field = $v ['field'];
						if (in_array ( $field, $system_field )) {
							unset ( $v ['fieldid'], $v ['modelid'], $v ['field'] );
							$v = new_addslashes ( $v );
							$v ['setting'] = serialize ( $v ['setting'] );
							$this->model_field_db->where(array ('modelid' => $modelid,'field' => $field ))->update ( $v );
						} else {
							$tablename = $v ['issystem'] ? $tablepre . $basic_table : $tablepre . $table_data;
							// 重组模型表字段属性
							$minlength = $v ['minlength'] ? $v ['minlength'] : 0;
							$maxlength = $v ['maxlength'] ? $v ['maxlength'] : 0;
							$field_type = $v ['formtype'];
							require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
							if (isset ( $v ['setting'] ['fieldtype'] )) {
								$field_type = $v ['setting'] ['fieldtype'];
							}
							require MODEL_PATH . 'add.sql.php';
							$v ['tips'] = addslashes ( $v ['tips'] );
							$v ['setting'] = serialize ( $v ['setting'] );
							$v ['modelid'] = $modelid;
							unset ( $v ['fieldid'] );
							$this->model_field_db->insert ( $v );
						}
					}
				}
				$this->public_cache ();
				showmessage ( L ( 'operation_success' ), U ( 'content/model/init' ) );
			}
		} else {
			$show_validator = '';
			$style_list = template_list ( 0 );
			foreach ( $style_list as $k => $v ) {
				$style_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $style_list [$k] );
			}
			$big_menu = big_menu ( U ( 'content/model/add' ), 'add', L ( 'add_model' ), 580, 400 );
			include $this->admin_tpl ( 'model_import' );
		}
	}

	/**
	 * 检查表是否存在
	 */
	public function public_check_tablename() {
		$r = $this->db->table_exists ( strip_tags ( $_GET ['tablename'] ) );
		if (! $r) exit ( '1' );
		exit ( '0' );
	}

	/**
	 * 更新指定模型字段缓存
	 *
	 * @param $modelid 模型id
	 */
	public function cache_field($modelid = 0) {
		$this->field_db = Loader::model ( 'model_field_model' );
		$field_array = array ();
		$fields = $this->field_db->where ( array ('modelid' => $modelid,'disabled' => 0 ) )->order ( 'listorder ASC' )->select ();
		foreach ( $fields as $_value ) {
			$setting = ! empty ( $_value ['setting'] ) ? string2array ( $_value ['setting'] ) : array();
			$_value = array_merge ( $_value, $setting );
			$field_array [$_value ['field']] = $_value;
		}
		S ( 'model/model_field_' . $modelid, $field_array );
		return true;
	}
}
?>