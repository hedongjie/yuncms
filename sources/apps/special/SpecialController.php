<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
error_reporting ( E_ERROR );
class SpecialController extends admin {
	private $db, $special_api;
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'special_model' );
		$this->special_api = Loader::lib ( 'special:special_api' );
	}

	/**
	 * 专题列表
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order ( 'listorder DESC, id DESC' )->listinfo ( $page, 6 );
		include $this->admin_tpl ( 'special_list' );
	}

	/**
	 * 添加专题
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] ) && ! empty ( $_POST ['dosubmit'] )) {
			$special = $this->check ( $_POST ['special'] );
			$id = $this->db->insert ( $special, true );
			if ($id) {
				$this->special_api->_update_type ( $id, $_POST ['type'] );
				$url = $special ['ishtml'] ? SITE_URL . substr ( C ( 'system', 'html_root' ), 1 ) . '/special/' . $special ['filename'] . '/' : SITE_URL . 'index.php?app=special&controller=index&id=' . $id;
				$this->db->where ( array ('id' => $id ) )->update ( array ('url' => $url ) );
				if ($special ['ishtml']) {
					$html = Loader::lib ( 'special:html' );
					$html->_index ( $id, 20, 5 );
				}
				// 更新附件状态
				if (C ( 'attachment', 'stat' )) {
					$this->attachment_db = Loader::model ( 'attachment_model' );
					$this->attachment_db->api_update ( array ($special ['thumb'],$special ['banner'] ), 'special-' . $id, 1 );
				}
				$this->special_cache ();
			}
			showmessage ( L ( 'add_special_success' ), HTTP_REFERER );
		} else {
			// 获取站点模板信息
			Loader::helper ( 'admin:global' );
			$info = C ( 'template' );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			include $this->admin_tpl ( 'special_add' );
		}
	}

	/**
	 * 专题修改
	 */
	public function edit() {
		if (! isset ( $_GET ['specialid'] ) || empty ( $_GET ['specialid'] )) {
			showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		}
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		if (isset ( $_POST ['dosubmit'] ) && ! empty ( $_POST ['dosubmit'] )) {
			$special = $this->check ( $_POST ['special'], 'edit' );
			if ($special ['ishtml'] && $special ['filename']) {
				$special ['url'] = SITE_URL . substr ( C ( 'system', 'html_root' ), 1 ) . '/special/' . $special ['filename'] . '/';
			} elseif ($special ['ishtml'] == '0') {
				$special ['url'] = SITE_URL . 'index.php?app=special&controller=index&specialid=' . $_GET ['specialid'];
			}
			$this->db->update ( $special, array ('id' => $_GET ['specialid'] ) );
			$this->special_api->_update_type ( $_GET ['specialid'], $_POST ['type'], 'edit' );

			// 调用生成静态类
			if ($special ['ishtml']) {
				$html = Loader::lib ( 'special:html' );
				$html->_index ( $_GET ['specialid'], 20, 5 );
			}
			// 更新附件状态
			if (C ( 'attachment', 'stat' )) {
				$this->attachment_db = Loader::model ( 'attachment_model' );
				$this->attachment_db->api_update ( array ($special ['thumb'],$special ['banner'] ), 'special-' . $_GET ['specialid'], 1 );
			}
			$this->special_cache ();
			showmessage ( L ( 'edit_special_success' ), HTTP_REFERER );
		} else {
			$info = $this->db->getby_id ( intval ( $_GET ['specialid'] ) );
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			if ($info ['pics']) {
				$pics = explode ( '|', $info ['pics'] );
			}
			if ($info ['voteid']) {
				$vote_info = explode ( '|', $info ['voteid'] );
			}
			$type_db = Loader::model ( 'type_model' );
			$types = $type_db->where ( array ('application' => 'special','parentid' => $_GET ['specialid'] ) )->field ( 'typeid, name, listorder,typedir' )->order ( 'listorder ASC, typeid ASC' )->select ();
			include $this->admin_tpl ( 'special_edit' );
		}
	}

	/**
	 * 信息导入专题
	 */
	public function import() {
		if (isset ( $_POST ['dosubmit'] ) || isset ( $_GET ['dosubmit'] )) {
			if (! is_array ( $_POST ['ids'] ) || empty ( $_POST ['ids'] ) || ! $_GET ['modelid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
			if (! isset ( $_POST ['typeid'] ) || empty ( $_POST ['typeid'] )) showmessage ( L ( 'select_type' ), HTTP_REFERER );
			foreach ( $_POST ['ids'] as $id ) {
				$this->special_api->_import ( $_GET ['modelid'], $_GET ['specialid'], $id, $_POST ['typeid'], $_POST ['listorder'] [$id] );
			}
			$html = Loader::lib ( 'special:html' );
			$html->_index ( $_GET ['specialid'], 20, 5 );
			showmessage ( L ( 'import_success' ), 'blank', '', 'import' );
		} else {
			if (! $_GET ['specialid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
			$_GET ['modelid'] = $_GET ['modelid'] ? intval ( $_GET ['modelid'] ) : 0;
			$_GET ['catid'] = $_GET ['catid'] ? intval ( $_GET ['catid'] ) : 0;
			$_GET ['page'] = max ( intval ( $_GET ['page'] ), 1 );
			$where = array ();
			if (isset ( $_GET ['catid'] )) {
			$where ['catid'] = get_sql_catid ( 'category_content', intval ( $_GET ['catid'] ) );
			$where ['status'] = 99;
			} else
			$where ['status'] = 99;
			if ($_GET ['start_time']) {
				$where ['inputtime'] = array('egt',strtotime ( $_GET ['start_time'] ));
			}
			if ($_GET ['end_time']) {
				$where ['inputtime'] = array('elt',strtotime ( $_GET ['end_time'] ));
			}
			if ($_GET ['key']) {
				$map = array('title'=>array('like','%'.$_GET['key'].'%'),'keywords'=>array('like','%'.$_GET['key'].'%'),'_logic'=> 'or');
				$where['_complex'] = $map;
			}
			$data = $this->special_api->_get_import_data ( $_GET ['modelid'], $where, $_GET ['page'] );
			$pages = $this->special_api->pages;
			$models = S ( 'common/model' );
			$model_datas = array ();
			foreach ( $models as $_k => $_v ) {
				$model_datas [$_v ['modelid']] = $_v ['name'];
			}
			$model_form = Form::select ( $model_datas, $_GET ['modelid'], 'name="modelid" onchange="select_categorys(this.value)"', L ( 'select_model' ) );
			$types = $this->special_api->_get_types ( $_GET ['specialid'] );
			include $this->admin_tpl ( 'import_content' );
		}
	}
	public function public_get_pics() {
		$_GET ['modelid'] = $_GET ['modelid'] ? intval ( $_GET ['modelid'] ) : 0;
		$_GET ['catid'] = $_GET ['catid'] ? intval ( $_GET ['catid'] ) : 0;
		$_GET ['page'] = max ( intval ( $_GET ['page'] ), 1 );
		$where = '';
		if (isset ( $_GET ['catid'] )) {
			$where ['catid'] = get_sql_catid ( 'category_content', intval ( $_GET ['catid'] ) );
			$where ['status'] = 99;
		} else
			$where ['status'] = 99;
		if ($_GET ['title']) {
			$where['title'] = array('like','%'.$_GET ['title'].'%');
		}
		if ($_GET ['start_time']) {
			$where ['inputtime'] = array('egt',strtotime ( $_GET ['start_time'] ));
		}
		if ($_GET ['end_time']) {
			$where ['inputtime'] = array('elt',strtotime ( $_GET ['end_time'] ));
		}
		$data = $this->special_api->_get_import_data ( $_GET ['modelid'], $where, $_GET ['page'] );
		$pages = $this->special_api->pages;
		$models = S ( 'common/model' );
		$model_datas = array ();
		foreach ( $models as $_k => $_v ) {
			$model_datas [$_v ['modelid']] = $_v ['name'];
		}
		$model_form = Form::select ( $model_datas, $_GET ['modelid'], 'name="modelid" onchange="select_categorys(this.value)"', L ( 'select_model' ) );
		$types = $this->special_api->_get_types ( $_GET ['specialid'] );
		include $this->admin_tpl ( 'import_pics' );
	}
	public function html() {
		if ((! isset ( $_POST ['id'] ) || empty ( $_POST ['id'] ))) {
			$result = $this->db->where ( array ('disabled' => 0 ) )->field ( 'id' )->key ( 'id' )->select ();
			$id = array_keys ( $result );
		} else {
			$id = $_POST ['id'];
		}
		S ( 'common/create_specials', $id );
		$this->public_create_html ();
	}
	public function create_special_list() {
		$html = Loader::lib ( 'special:html' );
		$size = $html->create_list ();
		showmessage ( L ( 'index_create_finish', array ('size' => byte_format ( $size ) ) ) );
	}

	/**
	 * 专题排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorder'] as $id => $order ) {
				$id = intval ( $id );
				$order = intval ( $order );
				$this->db->where ( array ('id' => $id ) )->update ( array ('listorder' => $order ) );
			}
			$this->special_cache ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'please_in_admin' ), HTTP_REFERER );
		}
	}

	/**
	 * 生成专题首页控制中心
	 */
	public function public_create_html() {
		$specials = S ( 'common/create_specials' );
		if (is_array ( $specials ) && ! empty ( $specials )) {
			$specialid = array_shift ( $specials );
			S ( 'common/create_specials', $specials );
			$this->create_index ( $specialid );
		} else {
			S ( 'common/create_specials', '' );
			showmessage ( L ( 'update_special_success' ), '?app=special&controller=special&action=init' );
		}
	}

	/**
	 * 生成某专题首页
	 *
	 * @param unknown $specialid
	 */
	private function create_index($specialid) {
		$info = $this->db->getby_id ( $specialid );
		if (! $info ['ishtml']) {
			showmessage ( $info ['title'] . L ( 'update_success' ), '?app=special&controller=special&action=public_create_html' );
		}
		$html = Loader::lib ( 'special:html' );
		$html->_index ( $specialid );
		showmessage ( $info ['title'] . L ( 'index_update_success' ), '?app=special&controller=special&action=public_create_type&specialid=' . $specialid );
	}

	/**
	 * 生成专题里列表页
	 */
	public function public_create_type() {
		$specialid = $_GET ['specialid'] ? intval ( $_GET ['specialid'] ) : 0;
		if (! $specialid) showmessage ( L ( 'illegal_action' ) );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$pages = isset ( $_GET ['pages'] ) ? intval ( $_GET ['pages'] ) : 0;
		$types = S ( 'common/create_types' );
		if (is_array ( $types ) && ! empty ( $types ) || $pages) {
			if (! isset ( $page ) || $page == 1) {
				$typeids = array_keys ( $types );
				$typeid = array_shift ( $typeids );
				$typename = $types [$typeid];
				unset ( $types [$typeid] );
				S ( 'common/create_types', $types );
			}
			if (! $pages) {
				$c = Loader::model ( 'special_content_model' );
				$total = $c->where ( array ('typeid' => $typeid ) )->count ();
				$pages = ceil ( $total / 20 );
			}
			if ($_GET ['typeid']) {
				$typeid = intval ( $_GET ['typeid'] );
				$typename = $_GET ['typename'];
			}
			$maxpage = $page + 10;
			if ($maxpage > $pages) {
				$maxpage = $pages;
			}
			for($page; $page <= $maxpage; $page ++) {
				$html = Loader::lib ( 'special:html' );
				$html->create_type ( $typeid, $page );
			}
			if (empty ( $types ) && $pages == $maxpage) {
				S ( 'common/create_types', '' );
				showmessage ( $typename . L ( 'type_update_success' ), '?app=special&controller=special&action=public_create_content&specialid=' . $specialid );
			}
			if ($pages <= $maxpage) {
				showmessage ( $typename . L ( 'update_success' ), '?app=special&controller=special&action=public_create_type&specialid=' . $specialid );
			} else {
				showmessage ( $typename . L ( 'type_from' ) . (isset ( $_GET ['page'] ) ? $_GET ['page'] : 1) . L ( 'type_end' ) . $maxpage . '</font> ' . L ( 'update_success' ), '?app=special&controller=special&action=public_create_type&typeid=' . $typeid . '&typename=' . $typename . '&page=' . $page . '&pages=' . $pages . '&specialid=' . $specialid );
			}
		} else {
			$special_api = Loader::lib ( 'special:special_api' );
			$types = $special_api->_get_types ( $specialid );
			S ( 'common/create_types', $types );
			showmessage ( L ( 'start_update_type' ), '?app=special&controller=special&action=public_create_type&specialid=' . $specialid );
		}
	}

	/**
	 * 生成内容页
	 */
	public function public_create_content() {
		$specialid = isset ( $_GET ['specialid'] ) ? intval ( $_GET ['specialid'] ) : 0;
		if (! $specialid) showmessage ( L ( 'illegal_action' ) );
		$pages = isset ( $_GET ['pages'] ) ? intval ( $_GET ['pages'] ) : 0;
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$c = Loader::model ( 'special_content_model' );
		if (! $pages) {
			$total = $c->where ( array ('specialid' => $specialid,'isdata' => 1 ) )->count ();
			$pages = ceil ( $total / 10 );
		}
		$offset = ($page - 1) * 10;
		$result = $c->field ( 'id' )->where ( array ('specialid' => $specialid,'isdata' => 1 ) )->order ( 'listorder ASC, id ASC' )->limit ( $offset . ', 10' )->select ();
		foreach ( $result as $r ) {
			$html = Loader::lib ( 'special:html' );
			$urls = $html->_create_content ( $r ['id'] );
			$c->where ( array ('id' => $r ['id'] ) )->update ( array ('url' => $urls [0] ) );
		}
		if ($page >= $pages) {
			showmessage ( L ( 'content_update_success' ), '?app=special&controller=special&action=public_create_html&specialid=' . $specialid );
		} else {
			$page ++;
			showmessage ( L ( 'content_from' ) . ' <font color="red">' . intval ( $offset + 1 ) . L ( 'type_end' ) . intval ( $offset + 10 ) . '</font> ' . L ( 'update_success' ), '?app=special&controller=special&action=public_create_content&specialid=' . $specialid . '&page=' . $page . '&pages=' . $pages );
		}
	}

	/**
	 * 推荐专题
	 */
	public function elite() {
		if (! isset ( $_GET ['id'] ) || empty ( $_GET ['id'] )) {
			showmessage ( L ( 'illegal_action' ) );
		}
		$_GET ['value'] = isset ( $_GET ['value'] ) ? intval ( $_GET ['value'] ) : 0;
		$this->db->where ( array ('id' => $_GET ['id'] ) )->update ( array ('elite' => $_GET ['value'] ) );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 删除专题 未执行删除操作，仅进行递归循环
	 */
	public function delete($id = 0) {
		if ((! isset ( $_GET ['id'] ) || empty ( $_GET ['id'] )) && (! isset ( $_POST ['id'] ) || empty ( $_POST ['id'] )) && ! $id) {
			showmessage ( L ( 'illegal_action' ), HTTP_REFERER );
		}
		if (is_array ( $_POST ['id'] ) && ! $id) {
			array_map ( array ($this,delete ), $_POST ['id'] );
			$this->special_cache ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} elseif (is_numeric ( $id ) && $id) {
			$id = $_GET ['id'] ? intval ( $_GET ['id'] ) : intval ( $id );
			$this->special_api->_del_special ( $id );
			return true;
		} else {
			$id = $_GET ['id'] ? intval ( $_GET ['id'] ) : intval ( $id );
			$this->special_api->_del_special ( $id );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 专题缓存
	 */
	private function special_cache() {
		$specials = array ();
		$result = $this->db->where ( array ('disabled' => 0 ) )->order ( 'id, title, url, thumb, banner, ishtml' )->order ( 'listorder DESC, id DESC' )->select ();
		foreach ( $result as $r ) {
			$specials [$r ['id']] = $r;
		}
		S ( 'common/special', $specials );
		return true;
	}

	/**
	 * 获取专题的分类
	 *
	 * @param intval $specialid
	 *        	专题ID
	 * @return 返回此专题分类的下拉列表
	 */
	public function public_get_type() {
		$_GET ['specialid'] = intval ( $_GET ['specialid'] );
		if (! $_GET ['specialid']) return '';
		$datas = $this->special_api->_get_types ( $_GET ['specialid'] );
		echo Form::select ( $datas, 0, 'name="typeid" id="typeid" onchange="import_c(' . $_GET ['specialid'] . ', this.value)"', L ( 'please_select' ) );
	}

	/**
	 * 按模型ID列出模型下的栏目
	 */
	public function public_categorys_list() {
		if (! isset ( $_GET ['modelid'] ) || empty ( $_GET ['modelid'] )) exit ( '' );
		$modelid = intval ( $_GET ['modelid'] );
		exit ( Form::select_category ( '', $_GET ['catid'], 'name="catid" id="catid"', L ( 'please_select' ), $modelid, 0, 1 ) );
	}

	/**
	 * ajax验证专题是否已存在
	 */
	public function public_check_special() {
		if (! isset ( $_GET ['title'] )) exit ( '0' );
		if (CHARSET == 'gbk') {
			$_GET ['title'] = safe_replace ( iconv ( 'UTF-8', 'GBK', $_GET ['title'] ) );
		}
		$title = addslashes ( $_GET ['title'] );
		if (isset ( $_GET ['id'] )) {
			$id = intval ( $_GET ['id'] );
			$r = $this->db->getby_id ( $id );
			if ($r ['title'] == $title) {
				exit ( '1' );
			}
		}
		$r = $this->db->where ( array ('title' => $title ) )->field ( 'id' )->find ();
		if (isset ( $r ['id'] )) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * ajax检验专题静态文件名是否存在，避免专题页覆盖
	 */
	public function public_check_dir() {
		if (! isset ( $_GET ['filename'] )) exit ( '1' );
		if (isset ( $_GET ['id'] )) {
			$id = intval ( $_GET ['id'] );
			$r = $this->db->getby_id ( $id );
			if ($r ['filename'] = $_GET ['filename']) {
				exit ( '1' );
			}
		}
		$r = $this->db->where ( array ('filename' => $_GET ['filename'] ) )->field ( 'id' )->find ();
		if ($r ['id']) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * 表单验证
	 *
	 * @param array $data
	 *        	表单传递的值
	 * @param string $a
	 *        	add/edit添加操作时，自动加上默认值
	 */
	private function check($data, $a = 'add') {
		if (! $data ['title']) showmessage ( L ( 'title_cannot_empty' ), HTTP_REFERER );
		if (! $data ['banner']) showmessage ( L ( 'banner_no_empty' ), HTTP_REFERER );
		if (! $data ['thumb']) showmessage ( L ( 'thumb_no_empty' ), HTTP_REFERER );
		if (isset ( $data ['catids'] ) && (is_array ( $data ['catids'] ) && ! empty ( $data ['catids'] ))) {
			$data ['catids'] = ',' . implode ( ',', $data ['catids'] ) . ',';
		}
		if ($a == 'add') {
			if (! isset ( $data ['index_template'] )) $data ['index_template'] = 'index';
			$data ['createtime'] = TIME;
			$data ['username'] = cookie ( 'admin_username' );
			$data ['userid'] = $_SESSION ['userid'];
		}
		if ($data ['voteid']) {
			if (strpos ( $data ['voteid'], '|' ) === false) {
				$vote_db = Loader::model ( 'vote_subject_model' );
				$r = $vote_db->where ( array ('subject' => $data ['voteid'] ) )->field ( 'subjectid, subject' )->order ( 'addtime DESC' )->find ();
				if ($r) {
					$data ['voteid'] = 'vote|' . $r ['subjectid'] . '|' . $r ['subject'];
				}
			}
		}
		return $data;
	}
}