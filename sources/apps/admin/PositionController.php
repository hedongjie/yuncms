<?php
/**
 * 推荐位管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: PositionController.php 118 2013-03-24 12:54:17Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class PositionController extends admin {

	private $db, $db_data, $db_content;

	PUBLIC function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'position_model' );
		$this->db_data = Loader::model ( 'position_data_model' );
		$this->db_content = Loader::model ( 'content_model' );
	}

	/**
	 * 推荐位管理
	 */
	public function init() {
		$infos = array ();
		$category = S ( 'common/category_content' );
		$model = S ( 'common/model' );
		$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : 1;
		$infos = $this->db->order('listorder DESC,posid DESC')->listinfo ($page, 20 );
		$pages = $this->db->pages;
		$show_dialog = true;
		$big_menu = big_menu ( U ( 'admin/position/add' ), 'add', L ( 'posid_add' ), 500, 360 );
		include $this->admin_tpl ( 'position_list' );
	}

	/**
	 * 推荐位添加
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (! is_array ( $_POST ['info'] ) || empty ( $_POST ['info'] ['name'] )) {
				showmessage ( L ( 'operation_failure' ) );
			}
			$_POST ['info'] ['listorder'] = intval ( $_POST ['info'] ['listorder'] );
			$_POST ['info'] ['maxnum'] = intval ( $_POST ['info'] ['maxnum'] );
			$_POST ['info'] ['modelid'] = isset($_POST ['info'] ['modelid']) ? intval ( $_POST ['info'] ['modelid'] ) : 0;
			$insert_id = $this->db->insert ( $_POST ['info'], true );
			$this->_set_cache ();
			if ($insert_id) showmessage ( L ( 'operation_success' ), '', '', 'add' );
		} else {
			$this->sitemodel_db = Loader::model ( 'model_model' );
			$sitemodel = $sitemodel = array ();
			$sitemodel = S ( 'common/model' );
			foreach ( $sitemodel as $value ) {
				$modelinfo [$value ['modelid']] = $value ['name'];
			}
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'position_add' );
		}

	}

	/**
	 * 推荐位编辑
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['posid'] = intval ( $_POST ['posid'] );
			if (! is_array ( $_POST ['info'] ) || empty ( $_POST ['info'] ['name'] )) {
				showmessage ( L ( 'operation_failure' ) );
			}
			$_POST ['info'] ['listorder'] = intval ( $_POST ['info'] ['listorder'] );
			$_POST ['info'] ['maxnum'] = intval ( $_POST ['info'] ['maxnum'] );
			$_POST ['info'] ['modelid'] = isset($_POST ['info'] ['modelid']) ? intval ( $_POST ['info'] ['modelid'] ) : 0;
			$this->db->where(array ('posid' => $_POST ['posid'] ))->update ( $_POST ['info'] );
			$this->_set_cache ();
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$info = $this->db->getby_posid ( intval($_GET ['posid']) );
			extract ( $info );
			$this->sitemodel_db = Loader::model ( 'model_model' );
			$sitemodel = $sitemodel = array ();
			$sitemodel = S ( 'common/model' );
			foreach ( $sitemodel as $value ) {
				$modelinfo [$value ['modelid']] = $value ['name'];
			}
			$show_validator = $show_header = $show_scroll = true;
			include $this->admin_tpl ( 'position_edit' );
		}
	}

	/**
	 * 推荐位删除
	 */
	public function delete() {
		$posid = intval ( $_GET ['posid'] );
		$this->db->where ( array ('posid' => $posid ) )->delete();
		$this->_set_cache ();
		showmessage ( L ( 'posid_del_success' ), U ( 'admin/position' ) );
	}

	/**
	 * 推荐位排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $posid => $listorder ) {
				$this->db->where(array ('posid' => $posid ))->update ( array ('listorder' => $listorder ) );
			}
			$this->_set_cache ();
			showmessage ( L ( 'operation_success' ), U ( 'admin/position' ) );
		} else {
			showmessage ( L ( 'operation_failure' ), U ( 'admin/position' ) );
		}
	}

	/**
	 * 推荐位文章统计
	 *
	 * @param $posid 推荐位ID
	 */
	public function content_count($posid) {
		$posid = intval ( $posid );
		$where = array ('posid' => $posid );
		$infos = $this->db_data->where($where)->get_one ( $where, $data = 'count(*) as count' );
		return $infos ['count'];
	}

	/**
	 * 推荐位文章列表
	 */
	public function public_item() {
		if (isset ( $_POST ['dosubmit'] )) {
			$items = count ( $_POST ['items'] ) > 0 ? $_POST ['items'] : showmessage ( L ( 'posid_select_to_remove' ), HTTP_REFERER );
			if (is_array ( $items )) {
				$sql = array ();
				foreach ( $items as $item ) {
					$_v = explode ( '-', $item );
					$sql ['id'] = $_v [0];
					$sql ['modelid'] = $_v [1];
					$sql ['posid'] = intval ( $_POST ['posid'] );
					$this->db_data->where ( $sql )->delete();
					$this->content_pos ( $sql ['id'], $sql ['modelid'] );
				}
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$posid = intval ( $_GET ['posid'] );
			$MODEL = S ( 'common/model' );
			$CATEGORY = S ( 'common/category_content' );
			$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : 1;
			$pos_arr = $this->db_data->where(array ('posid' => $posid ))->order('listorder DESC')->listinfo ($page, 20 );
			$pages = $this->db_data->pages;
			$infos = array ();
			foreach ( $pos_arr as $_k => $_v ) {
				$r = string2array ( $_v ['data'] );
				$r ['catname'] = $CATEGORY [$_v ['catid']] ['catname'];
				$r ['modelid'] = $_v ['modelid'];
				$r ['posid'] = $_v ['posid'];
				$r ['id'] = $_v ['id'];
				$r ['listorder'] = $_v ['listorder'];
				$r ['catid'] = $_v ['catid'];
				$r ['url'] = go ( $_v ['catid'], $_v ['id'] );
				$key = $r ['modelid'] . '-' . $r ['id'];
				$infos [$key] = $r;
			}
			$big_menu = big_menu ( U ( 'admin/position/add' ), 'add', L ( 'posid_add' ), 500, 300 );
			include $this->admin_tpl ( 'position_items' );
		}
	}

	/**
	 * 推荐位文章管理
	 */
	public function public_item_manage() {
		if (isset ( $_POST ['dosubmit'] )) {
			$posid = intval ( $_POST ['posid'] );
			$modelid = intval ( $_POST ['modelid'] );
			$id = intval ( $_POST ['id'] );
			$pos_arr = $this->db_data->where ( array ('id' => $id,'posid' => $posid,'modelid' => $modelid ) )->find();
			$array = string2array ( $pos_arr ['data'] );
			$array ['inputtime'] = strtotime ( $_POST ['info'] ['inputtime'] );
			$array ['title'] = trim ( $_POST ['info'] ['title'] );
			$array ['thumb'] = trim ( $_POST ['info'] ['thumb'] );
			$array ['description'] = trim ( $_POST ['info'] ['description'] );
			$thumb = $_POST ['info'] ['thumb'] ? 1 : 0;
			$array = array ('data' => serialize ( $array ),'synedit' => intval ( $_POST ['synedit'] ),'thumb' => $thumb );
			$this->db_data->where(array ('id' => $id,'posid' => $posid,'modelid' => $modelid ))->update ( $array );
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$posid = intval ( $_GET ['posid'] );
			$modelid = intval ( $_GET ['modelid'] );
			$id = intval ( $_GET ['id'] );
			if ($posid == 0 || $modelid == 0) showmessage ( L ( 'linkage_parameter_error' ), HTTP_REFERER );
			$pos_arr = $this->db_data->where ( array ('id' => $id,'posid' => $posid,'modelid' => $modelid ) )->find();
			extract ( unserialize ( $pos_arr ['data'] ) );
			$synedit = $pos_arr ['synedit'];
			$show_validator = true;
			$show_header = true;
			include $this->admin_tpl ( 'position_item_manage' );
		}
	}

	/**
	 * 推荐位文章排序
	 */
	public function public_item_listorder() {
		if (isset ( $_POST ['posid'] )) {
			foreach ( $_POST ['listorders'] as $_k => $listorder ) {
				$pos = array ();
				$pos = explode ( '-', $_k );
				$this->db_data->where(array ('id' => $pos [1],'catid' => $pos [0],'posid' => $_POST ['posid'] ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}
	}

	/**
	 * 推荐位添加栏目加载
	 */
	public function public_category_load() {
		$modelid = intval ( $_GET ['modelid'] );
		$category = Form::select_category ( 'category_content', '', 'name="info[catid]"', L ( 'please_select_parent_category' ), $modelid );
		echo $category;
	}

	/**
	 * 设置缓存
	 */
	private function _set_cache() {
		$infos = $this->db->order('listorder DESC')->key('posid')->select ( );
		S ( 'common/position', $infos );
		return $infos;
	}

	private function content_pos($id, $modelid) {
		$id = intval ( $id );
		$modelid = intval ( $modelid );
		$MODEL = S ( 'common/model' );
		$this->db_content->table_name = $this->db_content->get_prefix () . $MODEL [$modelid] ['tablename'];
		$posids = $this->db_data->where ( array ('id' => $id,'modelid' => $modelid ) )->find() ? 1 : 0;
		return $this->db_content->where(array ('id' => $id ))->update ( array ('posids' => $posids ) );
	}
}