<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
//error_reporting ( E_ERROR );
class RoleController extends admin {

	private $db, $priv_db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'admin_role_model' );
		$this->priv_db = Loader::model ( 'admin_role_priv_model' );
		$this->op = Loader::lib ( 'admin:role_op' );
	}

	/**
	 * 角色管理列表
	 */
	public function init() {
		$infos = $this->db->order('listorder DESC, roleid DESC')->select ( );
		include $this->admin_tpl ( 'role_list' );
	}

	/**
	 * 添加角色
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			if (! is_array ( $_POST ['info'] ) || empty ( $_POST ['info'] ['rolename'] )) {
				showmessage ( L ( 'operation_failure' ) );
			}
			if ($this->op->checkname ( $_POST ['info'] ['rolename'] )) {
				showmessage ( L ( 'role_duplicate' ) );
			}
			$insert_id = $this->db->insert ( $_POST ['info']);
			$this->_cache ();
			if ($insert_id) {
				showmessage ( L ( 'operation_success' ), U ( 'admin/role/init' ) );
			}
		} else {
			include $this->admin_tpl ( 'role_add' );
		}

	}

	/**
	 * 编辑角色
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['roleid'] = intval ( $_POST ['roleid'] );
			if (! is_array ( $_POST ['info'] ) || empty ( $_POST ['info'] ['rolename'] )) {
				showmessage ( L ( 'operation_failure' ) );
			}
			$this->db->where( array ('roleid' => $_POST ['roleid'] ))->update ( $_POST ['info'] );
			$this->_cache ();
			showmessage ( L ( 'operation_success' ), U ( 'admin/role/init' ) );
		} else {
			$info = $this->db->where ( array ('roleid' => $_GET ['roleid'] ) )->find();
			extract ( $info );
			include $this->admin_tpl ( 'role_edit' );
		}
	}

	/**
	 * 删除角色
	 */
	public function delete() {
		$roleid = intval ( $_GET ['roleid'] );
		if ($roleid == '1') showmessage ( L ( 'this_object_not_del' ), HTTP_REFERER );
		$this->db->where ( array ('roleid' => $roleid ) )->delete();
		$this->priv_db->where ( array ('roleid' => $roleid ) )->delete();
		$this->_cache ();
		showmessage ( L ( 'role_del_success' ) );
	}

	/**
	 * 更新角色排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $roleid => $listorder ) {
				$this->db->where(array ('roleid' => $roleid ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ) );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 角色权限设置
	 */
	public function role_priv() {
		$this->menu_db = Loader::model ( 'admin_menu_model' );
		if (isset ( $_POST ['dosubmit'] )) {
			if (is_array ( $_POST ['menuid'] ) && count ( $_POST ['menuid'] ) > 0) {
				$this->priv_db->where ( array ('roleid' => $_POST ['roleid'] ) )->delete();
				$menuinfo = $this->menu_db->field ('id,application,controller,action,data' )->delete();
				foreach ( $menuinfo as $_v )
					$menu_info [$_v ['id']] = $_v;
				foreach ( $_POST ['menuid'] as $menuid ) {
					$info = array ();
					$info = $this->op->get_menuinfo ( intval ( $menuid ), $menu_info );
					$info ['roleid'] = $_POST ['roleid'];
					$this->priv_db->insert ( $info );
				}
			} else {
				$this->priv_db->where ( array ('roleid' => $_POST ['roleid'] ) )->delete();
			}
			$this->_cache ();
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$roleid = intval ( $_GET ['roleid'] );
			$menu = Loader::lib ( 'Tree' );
			$menu->icon = array ('│ ','├─ ','└─ ' );
			$menu->nbsp = '&nbsp;&nbsp;&nbsp;';
			$result = $this->menu_db->select ();
			$priv_data = $this->priv_db->select (); // 获取权限表数据
			foreach ( $result as $n => $t ) {
				$result [$n] ['cname'] = L ( $t ['name'] );
				$result [$n] ['checked'] = ($this->op->is_checked ( $t, $_GET ['roleid'], $priv_data )) ? ' checked' : '';
				$result [$n] ['level'] = $t ['level'];
				$result [$n] ['parentid_node'] = ($t ['parentid']) ? ' class="child-of-node-' . $t ['parentid'] . '"' : '';
			}
			$str = "<tr id='node-\$id' \$parentid_node>
			<td style='padding-left:30px;'>\$spacer<input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$cname</td>
			</tr>";
			$menu->init ( $result );
			$categorys = $menu->get_tree ( 0, $str );
			$show_header = true;
			$show_scroll = true;
			include $this->admin_tpl ( 'role_priv' );
		}
	}

	/**
	 * 更新角色状态
	 */
	public function change_status() {
		$roleid = intval ( $_GET ['roleid'] );
		$disabled = intval ( $_GET ['disabled'] );
		$this->db->where(array ('roleid' => $roleid ))->update ( array ('disabled' => $disabled ) );
		$this->_cache ();
		showmessage ( L ( 'operation_success' ), U ( 'admin/role/init' ) );
	}

	/**
	 * 成员管理
	 */
	public function member_manage() {
		$this->admin_db = Loader::model ( 'admin_model' );
		$roleid = intval ( $_GET ['roleid'] );
		$roles = S ( 'common/role' );
		$infos = $this->admin_db->where(array ('roleid' => $roleid ))->select ( );
		include $this->admin_tpl ( 'admin_list' );
	}

	/**
	 * 设置栏目权限
	 */
	public function setting_cat_priv() {
		$roleid = isset ( $_GET ['roleid'] ) && intval ( $_GET ['roleid'] ) ? intval ( $_GET ['roleid'] ) : showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		$op = isset ( $_GET ['op'] ) && intval ( $_GET ['op'] ) ? intval ( $_GET ['op'] ) : '';
		switch ($op) {
			case 1 :
				Loader::lib ( 'admin:role_cat', false );
				role_cat::updata_priv ( $roleid, $_POST ['priv'] );
				showmessage ( L ( 'operation_success' ), U ( 'admin/role/init' ), '', 'edit' );
				break;
			default :
				Loader::lib ( 'admin:role_cat', false );
				$category = role_cat::get_category ();
				// 获取角色当前权限设置
				$priv = role_cat::get_roleid ( $roleid );
				// 加载tree
				$tree = Loader::lib ( 'Tree' );
				$categorys = array ();
				foreach ( $category as $k => $v ) {
					if ($v ['type'] == 1) {
						$v ['disabled'] = 'disabled';
						$v ['init_check'] = '';
						$v ['add_check'] = '';
						$v ['delete_check'] = '';
						$v ['listorder_check'] = '';
						$v ['push_check'] = '';
						$v ['move_check'] = '';
					} else {
						$v ['disabled'] = '';
						$v ['add_check'] = isset ( $priv [$v ['catid']] ['add'] ) ? 'checked' : '';
						$v ['delete_check'] = isset ( $priv [$v ['catid']] ['delete'] ) ? 'checked' : '';
						$v ['listorder_check'] = isset ( $priv [$v ['catid']] ['listorder'] ) ? 'checked' : '';
						$v ['push_check'] = isset ( $priv [$v ['catid']] ['push'] ) ? 'checked' : '';
						$v ['move_check'] = isset ( $priv [$v ['catid']] ['remove'] ) ? 'checked' : '';
						$v ['edit_check'] = isset ( $priv [$v ['catid']] ['edit'] ) ? 'checked' : '';
					}
					$v ['init_check'] = isset ( $priv [$v ['catid']] ['init'] ) ? 'checked' : '';
					$category [$k] = $v;
				}
				$show_header = true;
				$str = "<tr>
				<td align='center'><input type='checkbox'  value='1' onclick='select_all(\$catid, this)' ></td>
				<td>\$spacer\$catname</td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$init_check  value='init' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$add_check value='add' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$edit_check value='edit' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$delete_check  value='delete' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$listorder_check value='listorder' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$push_check value='push' ></td>
				<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$move_check value='remove' ></td>
				</tr>";
				$tree->init ( $category );
				$categorys = $tree->get_tree ( 0, $str );
				include $this->admin_tpl ( 'role_cat_priv' );
				break;
		}
	}

	/**
	 * 角色缓存
	 */
	private function _cache() {
		$infos = $this->db->field('roleid,rolename')->where(array ('disabled' => '0' ))->order('roleid ASC')->select ();
		$role = array ();
		foreach ( $infos as $info ) {
			$role [$info ['roleid']] = $info ['rolename'];
		}
		S ( 'common/role', $role );
		return $infos;
	}
}