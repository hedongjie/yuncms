<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
define ( 'MODEL_PATH', APPS_PATH . 'member' . DIRECTORY_SEPARATOR . 'fields' . DIRECTORY_SEPARATOR );
define ( 'CACHE_MODEL_PATH', DATA_PATH . 'model' . DIRECTORY_SEPARATOR );
//error_reporting ( E_ERROR );
/**
 * 管理员后台会员模型操作
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Member_modelController.php 870 2012-06-11 02:09:24Z
 *          85825770@qq.com $
 */
class Member_modelController extends admin {

	private $db;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'model_model' );
	}

	/**
	 * 会员模型列表
	 */
	public function manage() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$member_model_list = $this->db->where(array ('type' => 2 ))->order('sort ASC')->listinfo ( $page, 10 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'member/member_model/add' ), 'add', L ( 'add_model' ), 700, 500 );
		include $this->admin_tpl ( 'member_model_list' );
	}

	/**
	 * 添加会员模型
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$info = array ();
			$info ['name'] = $_POST ['info'] ['modelname'];
			$info ['tablename'] = 'member_' . $_POST ['info'] ['tablename'];
			$info ['description'] = $_POST ['info'] ['description'];
			$info['type'] = 2;
			if (! empty ( $_FILES ['model_import'] ['tmp_name'] )) {
				$model_import = @file_get_contents ( $_FILES ['model_import'] ['tmp_name'] );
				if (! empty ( $model_import )) {
					$model_import_data = string2array ( $model_import );
				}
			}
			$is_exists = $this->db->table_exists ( $info ['tablename'] );
			if ($is_exists) showmessage ( L ( 'operation_failure' ), U ( 'member/member_model/manage' ), '', 'add' );
			$modelid = $this->db->insert ( $info, 1 );
			if ($modelid) {
				$model_sql = file_get_contents ( MODEL_PATH . 'model.sql' );
				$tablepre = $this->db->get_prefix ();
				$tablename = $info ['tablename'];
				$model_sql = str_replace ( '$tablename', $tablepre . $tablename, $model_sql );
				$this->db->sql_execute ( $model_sql );
				if (! empty ( $model_import_data )) {
					$this->model_field_db = Loader::model ( 'model_field_model' );
					$tablename = $tablepre . $tablename;
					foreach ( $model_import_data as $v ) {
						// 修改模型表字段
						$field = $v ['field'];
						$minlength = $v ['minlength'] ? $v ['minlength'] : 0;
						$maxlength = $v ['maxlength'] ? $v ['maxlength'] : 0;
						$field_type = $v ['formtype'];
						require MODEL_PATH . $field_type . DIRECTORY_SEPARATOR . 'config.inc.php';
						if (isset ( $v ['setting'] ['fieldtype'] )) {
							$field_type = $v ['setting'] ['fieldtype'];
						}
						require MODEL_PATH . 'add.sql.php';
						$v ['setting'] = array2string ( $v ['setting'] );
						$v ['modelid'] = $modelid;
						unset ( $v ['fieldid'] );
						$fieldid = $this->model_field_db->insert ( $v, 1 );
					}
				}
				// 更新模型缓存
				Loader::lib ( 'member:member_cache', false );
				member_cache::update_cache_model ();
				showmessage ( L ( 'operation_success' ), U ( 'member/member_model/manage' ), '', 'add' );
			} else {
				showmessage ( L ( 'operation_failue' ), U ( 'member/member_model/manage' ), '', 'add' );
			}
		} else {
			$show_header = $show_scroll = true;
			include $this->admin_tpl ( 'member_model_add' );
		}
	}

	/**
	 * 修改会员模型
	 */
	function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$modelid = isset ( $_POST ['info'] ['modelid'] ) ? $_POST ['info'] ['modelid'] : showmessage ( L ( 'operation_success' ), '?app=member&controller=member_model&action=manage', '', 'edit' );
			$info ['name'] = $_POST ['info'] ['modelname'];
			$info ['disabled'] = $_POST ['info'] ['disabled'] ? 1 : 0;
			$info ['description'] = $_POST ['info'] ['description'];
			$this->db->where(array ('modelid' => $modelid ))->update ( $info );
			// 更新模型缓存
			Loader::lib ( 'member:member_cache', false );
			member_cache::update_cache_model ();
			showmessage ( L ( 'operation_success' ), U ( 'member/member_model/manage' ), '', 'edit' );
		} else {
			$show_header = $show_scroll = true;
			$modelinfo = $this->db->getby_modelid ( $_GET ['modelid']);
			include $this->admin_tpl ( 'member_model_edit' );
		}
	}

	/**
	 * 删除会员模型
	 */
	function delete() {
		$modelidarr = isset ( $_POST ['modelid'] ) ? $_POST ['modelid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$where = to_sqls ( $modelidarr, '', 'modelid' );
		$modelinfo = $this->db->where($where)->select (  );
		foreach ( $modelinfo as $v ) {
			$this->db->drop_table ( $v ['tablename'] );
		}
		if ($this->db->delete ( $where )) {
			// 删除模型字段
			$this->model_field_db = Loader::model ( 'model_field_model' );
			$this->model_field_db->delete ( $where );
			// 修改用户模型组为普通会员
			$this->member_db = Loader::model ( 'member_model' );
			$this->member_db->update ( array ('modelid' => 1 ), $where );
			// 更新模型缓存
			Loader::lib ( 'member:member_cache', false );
			member_cache::update_cache_model ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}
	}

	/**
	 * 导出会员模型
	 */
	function export() {
		$modelid = isset ( $_GET ['modelid'] ) ? $_GET ['modelid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$modelarr = S ( 'common/member_model' );
		$this->model_field_db = Loader::model ( 'model_field_model' );
		$modelinfo = $this->model_field_db->select ( array ('modelid' => $modelid ) );
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
	 * 移动模型会员
	 */
	function move() {
		if (isset ( $_POST ['dosubmit'] )) {
			$from_modelid = isset ( $_POST ['from_modelid'] ) ? $_POST ['from_modelid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			$to_modelid = ! empty ( $_POST ['to_modelid'] ) && $_POST ['to_modelid'] != $from_modelid ? $_POST ['to_modelid'] : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
			// 更新会员表modelid
			$this->db->change_member_modelid ( $from_modelid, $to_modelid );
			showmessage ( L ( 'member_move' ) . L ( 'operation_success' ), HTTP_REFERER, '', 'move' );
		} else {
			$show_header = $show_scroll = true;
			$modelarr = $this->db->select (array ('type' => 2 ) );
			foreach ( $modelarr as $v ) {
				$modellist [$v ['modelid']] = $v ['name'];
			}
			include $this->admin_tpl ( 'member_model_move' );
		}
	}

	/**
	 * 排序会员模型
	 */
	function sort() {
		if (isset ( $_POST ['sort'] )) {
			foreach ( $_POST ['sort'] as $k => $v ) {
				$this->db->update ( array ('sort' => $v ), array ('modelid' => $k ) );
			}
			// 更新模型缓存
			Loader::lib ( 'member:member_cache', false );
			member_cache::update_cache_model ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}
	}

	/**
	 * 检查模型名称
	 *
	 * @param string $username
	 * @return $status {0:模型名已经存在 ;1:成功}
	 */
	public function public_checkmodelname_ajax() {
		$modelname = isset ( $_GET ['modelname'] ) ? trim ( $_GET ['modelname'] ) : exit ( '0' );
		if (CHARSET != 'utf-8') {
			$modelname = iconv ( 'utf-8', CHARSET, $modelname );
			$modelname = addslashes ( $modelname );
		}
		$oldmodelname = isset ( $_GET ['oldmodelname'] ) ? trim ( $_GET ['oldmodelname'] ) : null;
		if ($modelname == $oldmodelname) exit ( '1' );
		$status = $this->db->get_one ( array ('name' => $modelname ) );
		if ($status) exit ( '0' );
		else exit ( '1' );
	}

	/**
	 * 检查模型表是否存在
	 *
	 * @param string $username
	 * @return $status {0:模型表名已经存在 ;1:成功}
	 */
	public function public_checktablename_ajax() {
		$tablename = isset ( $_GET ['tablename'] ) ? trim ( $_GET ['tablename'] ) : exit ( '0' );
		$status = $this->db->table_exists ( 'member_' . $tablename );
		if ($status) exit ( '0' );
		else exit ( '1' );
	}
}