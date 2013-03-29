<?php
/**
 * 广告位管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: SpaceController.php 59 2012-11-05 12:48:20Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class SpaceController extends admin {
	private $M, $db;
	public function __construct() {
		parent::__construct ();
		$this->M = new_htmlspecialchars ( S ( 'common/poster' ) );
		$this->db = Loader::model ( 'poster_space_model' );
	}

	public function init() {
		$TYPES = $this->template_type ();
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order('spaceid')->listinfo ($page );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'poster/space/add' ), 'add', L ( 'add_space' ), 540, 320 );
		include $this->admin_tpl ( 'space_list' );
	}

	/**
	 * 添加广告版块
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$space = $this->check ( $_POST ['space'] );
			$space ['setting'] = array2string ( $_POST ['setting'] );
			$spaceid = $this->db->insert ( $space, true );
			if ($spaceid) {
				if ($space ['type'] == 'code') $path = '{show_ad(' . $spaceid . ')}';
				else $path = 'poster_js/' . $spaceid . '.js';
				$this->db->where(array ('spaceid' => $spaceid ))->update ( array ('path' => $path ) );
				showmessage ( L ( 'added_successful' ), U ( 'poster/space' ), '', 'add' );
			}
		} else {
			$TYPES = $this->template_type ();
			$poster_template = S ( 'common/poster_template' );
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'space_add' );
		}
	}

	/**
	 * 编辑广告版位
	 */
	public function edit() {
		if (! isset ( $_GET ['spaceid'] )) showmessage ( L ( 'illegal_operation' ), HTTP_REFERER );
		$_GET ['spaceid'] = intval ( $_GET ['spaceid'] );
		if (isset ( $_POST ['dosubmit'] )) {
			$space = $this->check ( $_POST ['space'] );
			$space ['setting'] = array2string ( $_POST ['setting'] );
			if ($space ['type'] == 'code') {
				$space ['path'] = '{show_ad(' . $_GET ['spaceid'] . ')}';
			} else {
				$space ['path'] = 'poster_js/' . $_GET ['spaceid'] . '.js';
			}
			if (isset ( $_POST ['old_type'] ) && $_POST ['old_type'] != $space ['type']) {
				$poster_db = Loader::model ( 'poster_model' );
				$poster_db->where ( array ('spaceid' => $_GET ['spaceid'] ) )->delete();
				$space ['items'] = 0;
			}
			if ($this->db->where(array ('spaceid' => $_GET ['spaceid'] ))->update ( $space )) {
				showmessage ( L ( 'edited_successful' ), U ( 'poster/space' ), '', 'testIframe' . $_GET ['spaceid'] );
			} else {
				showmessage ( L ( 'edited_successful' ), U ( 'poster/space' ), '', 'testIframe' . $_GET ['spaceid'] );
			}

		} else {
			$info = $this->db->getby_spaceid ( $_GET ['spaceid'] );
			$setting = string2array ( $info ['setting'] );
			$TYPES = $this->template_type ();
			$poster_template = S ( 'common/poster_template' );
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'space_edit' );
		}
	}

	/**
	 * 广告版位调用代码
	 */
	public function public_call() {
		$_GET ['sid'] = intval ( $_GET ['sid'] );
		if (! $_GET ['sid']) showmessage ( L ( 'illegal_action' ), HTTP_REFERER, '', 'call' );
		$r = $this->db->getby_spaceid ( $_GET ['sid'] );
		include $this->admin_tpl ( 'space_call' );
	}

	/**
	 * 广告预览
	 */
	public function public_preview() {
		if (is_numeric ( $_GET ['spaceid'] )) {
			$_GET ['spaceid'] = intval ( $_GET ['spaceid'] );
			$r = $this->db->getby_spaceid ( $_GET ['spaceid'] );
			$scheme = $_SERVER ['SERVER_PORT'] == '443' ? 'https://' : 'http://';
			if ($r ['type'] == 'code') {
				$db = Loader::model ( 'poster_model' );
				$rs = $db->where ( array ('spaceid' => $r ['spaceid'] ))->field('setting')->order('id ASC')->find();
				if ($rs ['setting']) {
					$d = string2array ( $rs ['setting'] );
					$data = $d ['code'];
				}
			} else {
				$path = SITE_URL . 'data/' . $r ['path'];
			}
			include $this->admin_tpl ( 'space_preview' );
		}
	}

	private function template_type() {
		Loader::helper ( 'poster:global' );
		return get_types ();
	}

	/**
	 * 删除广告版位
	 *
	 * @param intval $sid
	 */
	public function delete() {
		if ((! isset ( $_GET ['spaceid'] ) || empty ( $_GET ['spaceid'] )) && (! isset ( $_POST ['spaceid'] ) || empty ( $_POST ['spaceid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {
			if (is_array ( $_POST ['spaceid'] )) {
				array_map ( array ($this,_del ), $_POST ['spaceid'] ); // 如果是批量操作，则递归数组
			} elseif ($_GET ['spaceid']) {
				$_GET ['spaceid'] = intval ( $_GET ['spaceid'] );
				$db = Loader::model ( 'poster_model' );
				$db->delete ( array ('spaceid' => $_GET ['spaceid'] ) );
				$this->db->delete ( array ('spaceid' => $_GET ['spaceid'] ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 广告位删除
	 *
	 * @param intval $spaceid
	 *        	专题ID
	 */
	private function _del($spaceid = 0) {
		$spaceid = intval ( $spaceid );
		if (! $spaceid) return false;
		$db = Loader::model ( 'poster_model' );
		$db->where ( array ('spaceid' => $spaceid ) )->delete();
		$this->db->where ( array ('spaceid' => $spaceid ) )->delete();
		return true;
	}

	/**
	 * 广告模块配置
	 */
	public function setting() {
		if (isset ( $_POST ['dosubmit'] )) {
			$setting = $_POST ['setting'];
			S ( 'common/poster', $setting ); // 设置缓存
			Loader::model ( 'application_model' )->set_setting ( 'poster',$setting); // 将配置信息存入数据表中
			showmessage ( L ( 'setting_updates_successful' ), HTTP_REFERER, '', 'setting' );
		} else {
			@extract ( $this->M );
			include $this->admin_tpl ( 'setting' );
		}
	}

	/**
	 * 配置模板
	 */
	public function poster_template() {
		$templatedir = SOURCE_PATH . 'template' . DIRECTORY_SEPARATOR . C ( 'template', 'name' ) . DIRECTORY_SEPARATOR . 'poster' . DIRECTORY_SEPARATOR;
		$poster_template = S ( 'common/poster_template' );
		$templates = glob ( $templatedir . '*.html' );
		if (is_array ( $templates ) && ! empty ( $templates )) {
			foreach ( $templates as $k => $tem ) {
				$templates [$k] = basename ( $tem, ".html" );
			}
		}
		$big_menu = big_menu ( U ( 'poster/space/add' ), 'add', L ( 'add_space' ), 540, 320 );
		include $this->admin_tpl ( 'poster_template' );
	}

	/**
	 * 删除模板配置
	 */
	public function public_tempate_del() {
		if (! isset ( $_GET ['id'] )) showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$poster_template = S ( 'common/poster_template' );
		if ($poster_template [$_GET ['id']]) unset ( $poster_template [$_GET ['id']] );
		S ( 'common/poster_template', $poster_template );
		showmessage ( L ( 'operation_success' ), HTTP_REFERER );
	}

	/**
	 * 配置模板
	 */
	public function public_tempate_setting() {
		$poster_template = S ( 'common/poster_template' );
		if (isset ( $_POST ['dosubmit'] )) {
			if (isset($_POST ['info'] ['type']) && (is_array ( $_POST ['info'] ['type'] ) && ! empty ( $_POST ['info'] ['type'] ))) {
				$type2name = array ('images' => L ( 'photo' ),'flash' => L ( 'flash' ),'text' => L ( 'title' ),'code' => L ( 'code' ) );
				$type = array ();
				foreach ( $_POST ['info'] ['type'] as $t ) {
					if (in_array ( $t, array ('images','flash','text','code' ) )) $type [$t] = $type2name [$t];
					else continue;
				}
			}
			unset ( $_POST ['info'] ['type'] );
			$_POST ['info'] ['type'] = $type;
			$poster_template [$_POST ['template']] = $_POST ['info'];
			S ( 'common/poster_template', $poster_template );
			showmessage ( L ( 'setting_success' ), '', '', 'edit' );
		} else {
			if (! isset ( $_GET ['template'] )) showmessage ( L ( 'illegal_parameters' ) );
			else $template = $_GET ['template'];
			if (isset($poster_template [$template])) {
				$info = $poster_template [$template];
				if (is_array ( $info ['type'] ) && ! empty ( $info ['type'] )) {
					$type = array ();
					$type = array_keys ( $info ['type'] );
					unset ( $info ['type'] );
					$info ['type'] = $type;
				}
			}
			include $this->admin_tpl ( 'template_setting' );
		}
	}

	/**
	 * 更新js
	 */
	public function create_js($page = 0) {
		$pages = isset ( $_GET ['pages'] ) ? intval ( $_GET ['pages'] ) : 1;
		if ($page == 1) {
			$total = $this->db->where(array ('disabled' => 0 ))->count();
			$pages = ceil ( $total / 20 );
		} else
			$pages = isset ( $_GET ['pages'] ) ? intval ( $_GET ['pages'] ) : 0;
		$offset = ($page - 1) * 20;
		$data = $this->db->where(array ('disabled' => 0 ))->order('spaceid ASC')->listinfo ( $page );
		$html = Loader::lib ( 'poster:html' );
		foreach ( $data as $d ) {
			if ($d ['type'] != 'code') $html->create_js ( $d ['spaceid'] );
			else continue;
		}
		$page ++;
		if ($page > $pages) showmessage ( L ( 'update_js_success' ), U ( 'poster/space/init' ) );
		else showmessage ( L ( 'update_js' ) . '<font style="color:red">' . ($page - 1) . '/' . $pages . '</font>', U ( 'poster/space/create_js', array ('page' => $page,'pages' => $pages ) ) );
	}

	/**
	 * 检测版位名称是否存在
	 */
	public function public_check_space() {
		if (! $_GET ['name']) exit ( '0' );
		if (CHARSET == 'gbk') $_GET ['name'] = iconv ( 'UTF-8', 'GBK', $_GET ['name'] );
		$name = $_GET ['name'];
		if ($_GET ['spaceid']) {
			$spaceid = intval ( $_GET ['spaceid'] );
			$r = $this->db->getby_spaceid ( $spaceid );
			if ($r ['name'] == $name) exit ( '1' );
		}
		$r = $this->db->where ( array ('name' => $name ))->field( 'spaceid' )->find();
		if (isset($r ['spaceid'])) exit ( '0' );
		else exit ( '1' );
	}

	/**
	 * 检查表单数据
	 *
	 * @param Array $data
	 * @return Array
	 */
	private function check($data = array()) {
		if ($data ['name'] == '') showmessage ( L ( 'name_plates_not_empty' ) );
		$info = $this->db->where ( array ('name' => $data ['name'] ))->field( 'spaceid' )->find();
		if ((isset($info ['spaceid']) && (isset($_GET ['spaceid']) && $info ['spaceid'] != $_GET ['spaceid'])) || (isset($info ['spaceid']) && ! isset ( $_GET ['spaceid'] ))) {
			showmessage ( L ( 'space_exist' ), HTTP_REFERER );
		}
		if ((! isset ( $data ['width'] ) || $data ['width'] == 0) && in_array ( $data ['type'], array ('banner','fixure','float','couplet','imagechange','imagelist' ) )) {
			showmessage ( L ( 'plate_width_not_empty' ), HTTP_REFERER );
		} else
			$data ['width'] = intval ( $data ['width'] );
		if ((! isset ( $data ['height'] ) || $data ['height'] == 0) && in_array ( $data ['type'], array ('banner','fixure','float','couplet','imagechange','imagelist' ) )) {
			showmessage ( L ( 'plate_height_not_empty' ), HTTP_REFERER );
		} else
			$data ['height'] = intval ( $data ['height'] );
		$TYPES = $this->template_type ();
		return $data;
	}
}