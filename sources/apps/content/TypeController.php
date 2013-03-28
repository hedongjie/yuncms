<?php
/**
 * 类别管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * $Id: TypeController.php 135 2013-03-25 01:08:07Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
// error_reporting ( E_ERROR );
class TypeController extends admin {

	private $db, $category_db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'type_model' );
		$this->model = S ( 'common/model' );
		$this->category_db = Loader::model ( 'category_model' );
	}

	/**
	 * 类别管理
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = array ();
		$result_datas = $this->db->where(array ('application' => 'content' ))->order('listorder ASC,typeid DESC')->listinfo ($page );
		$pages = $this->db->pages;
		foreach ( $result_datas as $r ) {
			$r ['modelname'] = ($r ['modelid'] > 0) ? $this->model [$r ['modelid']] ['name'] : '';
			$datas [] = $r;
		}
		$big_menu = big_menu ( U ( 'content/type/add' ), 'add', L ( 'add_type' ), 780, 500 );
		$this->cache ();
		include $this->admin_tpl ( 'type_list' );
	}

	/**
	 * 添加类别
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['application'] = 'content';
			if (empty ( $_POST ['info'] ['name'] )) showmessage ( L ( "input" ) . L ( 'type_name' ) );
			$names = explode ( "\n", $_POST ['info'] ['name'] );
			$ids = isset($_POST ['ids']) ? $_POST ['ids'] : '';
			foreach ( $names as $name ) {
				$_POST ['info'] ['name'] = $name;
				$typeid = $this->db->insert ( $_POST ['info'], true );
				if (! empty ( $ids )) {
					foreach ( $ids as $catid ) {
						$r = $this->category_db->where ( array ('catid' => $catid ) )->field( 'usable_type')->find();
						if ($r ['usable_type']) $usable_type = $r ['usable_type'] . $typeid . ',';
						else $usable_type = ',' . $typeid . ',';
						$this->category_db->where(array ('catid' => $catid ))->update ( array ('usable_type' => $usable_type ) );
					}
				}
			}
			showmessage ( L ( 'add_success' ), '', '', 'add' );
		} else {
			$show_header = $show_validator = '';
			$categorys = $this->public_getsite_categorys ();
			include $this->admin_tpl ( 'type_add' );
		}
	}

	/**
	 * 修改类别
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$typeid = intval ( $_POST ['typeid'] );
			$this->db->where(array ('typeid' => $typeid ))->update ( $_POST ['info'] );
			$ids = $_POST ['ids'];
			if (! empty ( $ids )) {
				foreach ( $ids as $catid ) {
					$r = $this->category_db->where ( array ('catid' => $catid ) )->field('usable_type')->find();
					if ($r ['usable_type']) {
						$usable_type = array ();
						$usable_type_arr = explode ( ',', $r ['usable_type'] );
						foreach ( $usable_type_arr as $_usable_type_arr ) {
							if ($_usable_type_arr && $_usable_type_arr != $typeid) $usable_type [] = $_usable_type_arr;
						}
						$usable_type = ',' . implode ( ',', $usable_type ) . ',';
						$usable_type = $usable_type . $typeid . ',';
					} else {
						$usable_type = ',' . $typeid . ',';
					}
					$this->category_db->where(array ('catid' => $catid ))->update ( array ('usable_type' => $usable_type ) );
				}
			}
			// 删除取消的
			$catids_string = $_POST ['catids_string'];
			if ($catids_string) {
				$catids_string = explode ( ',', $catids_string );
				foreach ( $catids_string as $catid ) {
					$r = $this->category_db->where ( array ('catid' => $catid ))->field('usable_type')->find();
					$usable_type = array ();
					$usable_type_arr = explode ( ',', $r ['usable_type'] );
					foreach ( $usable_type_arr as $_usable_type_arr ) {
						if (! $_usable_type_arr || ! in_array ( $catid, $ids )) continue;
						$usable_type [] = $_usable_type_arr;
					}
					if (! empty ( $usable_type )) {
						$usable_type = ',' . implode ( ',', $usable_type ) . ',';
					} else {
						$usable_type = '';
					}
					$this->category_db->where(array ('catid' => $catid ))->update ( array ('usable_type' => $usable_type ) );
				}
			}
			$this->category_cache ();
			showmessage ( L ( 'update_success' ), '', '', 'edit' );
		} else {
			$show_header = $show_validator = '';
			$typeid = intval ( $_GET ['typeid'] );
			$r = $this->db->getby_typeid ($typeid );
			extract ( $r );
			$categorys = $this->public_getsite_categorys ( $typeid );
			$catids_string = empty ( $this->catids_string ) ? 0 : $this->catids_string = implode ( ',', $this->catids_string );
			include $this->admin_tpl ( 'type_edit' );
		}
	}

	/**
	 * 删除类别
	 */
	public function delete() {
		$_GET ['typeid'] = intval ( $_GET ['typeid'] );
		$this->db->where ( array ('typeid' => $_GET ['typeid'] ) )->delete();
		exit ( '1' );
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->where(array ('typeid' => $id ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 更新缓存
	 */
	public function cache() {
		$datas = $this->db->where(array ('application' => 'content' ))->order('listorder ASC,typeid ASC')->key('typeid')->select ();
		S ( 'common/type_content', $datas );
		$this->category_cache();
		return true;
	}

	/**
	 * 更新栏目缓存
	 */
	private function category_cache() {
		$categorys = array ();
		$category_arr = $this->category_db->where(array('application'=>'content'))->order('listorder ASC')->select (  );
		foreach ( $category_arr as $r ) {
			unset ( $r ['application'] );
			$setting = string2array ( $r ['setting'] );
			if ($r ['type'] == 0) { // 内容模型
				$r ['create_to_html_root'] = $setting ['create_to_html_root'];
				$r ['content_ishtml'] = $setting ['content_ishtml'];
				$r ['workflowid'] = $setting ['workflowid'];
			}
			$r ['ishtml'] = isset($setting ['ishtml']) ? $setting ['ishtml'] : 0;
			$r ['category_ruleid'] = isset($setting ['category_ruleid']) ? $setting ['category_ruleid'] : 0;
			$r ['show_ruleid'] = isset($setting ['show_ruleid']) ? $setting ['show_ruleid'] : 0;
			$r ['isdomain'] = '0';
			if (! preg_match ( '/^(http|https):\/\//', $r ['url'] )) {
				$r ['url'] = substr ( SITE_URL, 0, - 1 ) . $r ['url'];
			} elseif ($r ['ishtml'] == 1) {
				$r ['isdomain'] = '1';
			}
			$categorys [$r ['catid']] = $r;
		}
		S ( 'common/category_content', $categorys );
		return true;
	}

	/**
	 * 选择可用栏目
	 */
	public function public_getsite_categorys($typeid = 0) {
		$this->categorys = S ( 'common/category_content' );
		$tree = new Tree ();
		$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$categorys = array ();
		$this->catids_string = array ();
		foreach ( $this->categorys as $r ) {
			if ($r ['type'] != 0) continue;
			if ($r ['child']) {
				$r ['checkbox'] = '';
				$r ['style'] = 'color:#8A8A8A;';
			} else {
				$checked = '';
				if ($typeid && $r ['usable_type']) {
					$usable_type = explode ( ',', $r ['usable_type'] );
					if (in_array ( $typeid, $usable_type )) {
						$checked = 'checked';
						$this->catids_string [] = $r ['catid'];
					}
				}
				$r ['checkbox'] = "<input type='checkbox' name='ids[]' value='{$r['catid']}' {$checked}>";
				$r ['style'] = '';
			}
			$categorys [$r ['catid']] = $r;
		}
		$str = "<tr>
					<td align='center'>\$checkbox</td>
					<td style='\$style'>\$spacer\$catname</td>
				</tr>";
		$tree->init ( $categorys );
		$categorys = $tree->get_tree ( 0, $str );
		return $categorys;
	}

}