<?php
/**
 * 后台菜单管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
error_reporting ( E_ERROR );
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class MenuController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'admin_menu_model' );
	}

	/**
	 * 菜单管理
	 */
	public function init() {
		$tree = new Tree ();
		$tree->icon = array ('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ' );
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$result = $this->db->order ( 'listorder ASC,id DESC' )->select ();
		$array = array ();
		foreach ( $result as $r ) {
			$r ['cname'] = L ( $r ['name'] );
			$r ['str_manage'] = '<a href="?app=admin&controller=menu&action=add&parentid=' . $r ['id'] . '&menuid=' . $_GET ['menuid'] . '">' . L ( 'add_submenu' ) . '</a> | <a href="?app=admin&controller=menu&action=edit&id=' . $r ['id'] . '&menuid=' . $_GET ['menuid'] . '">' . L ( 'modify' ) . '</a> | <a href="javascript:window.top.art.dialog.confirm(\'' . L ( 'confirm', array (
					'message' => $r ['cname'] ) ) . '\',function(topWin){redirect(\'?app=admin&controller=menu&action=delete&id=' . $r ['id'] . '&menuid=' . $_GET ['menuid'] . '\');},function(){});void(0);">' . L ( 'delete' ) . '</a> ';
			$array [] = $r;
		}
		$str = "<tr>
		<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input-text-c'></td>
		<td align='center'>\$id</td>
		<td >\$spacer\$cname</td>
		<td align='center'>\$str_manage</td>
		</tr>";
		$tree->init ( $array );
		$menus = $tree->get_tree ( 0, $str );
		include $this->admin_tpl ( 'menu' );
	}

	/**
	 * 添加菜单
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			if ($_POST ['info'] ['parentid'] != 0) {
				//获取上级的层级深度
				$parentinfo = $this->db->where(array('id'=>intval($_POST ['info'] ['parentid'])))->find();
				$_POST ['info'] ['level'] = $parentinfo['level'] + 1;
			}else{
				$_POST ['info'] ['level'] = 0;
			}
			$this->db->insert ( $_POST ['info'] );
			// 开发过程中用于自动创建语言包
			$file = SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . 'admin_menu.php';
			if (file_exists ( $file )) {
				$content = file_get_contents ( $file );
				$content = substr ( $content, 0, - 2 );
				$key = $_POST ['info'] ['name'];
				$data = $content . "\$LANG['$key'] = '$_POST[language]';\r\n?>";
				file_put_contents ( $file, $data );
			} else {
				$key = $_POST ['info'] ['name'];
				$data = "<?php\r\n\$LANG['$key'] = '$_POST[language]';\r\n?>";
				file_put_contents ( $file, $data );
			}
			// 结束
			showmessage ( L ( 'add_success' ) );
		} else {
			$show_validator = '';
			$array = array ();
			$tree = new Tree ();
			$result = $this->db->select ();
			foreach ( $result as $r ) {
				$r ['cname'] = L ( $r ['name'] );
				if (! empty ( $_GET ['parentid'] )) $r ['selected'] = $r ['id'] == $_GET ['parentid'] ? 'selected' : '';
				$array [] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$cname</option>";
			$tree->init ( $array );
			$select_menus = $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'menu' );
		}
	}

	/**
	 * 修改菜单
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$id = intval ( $_POST ['id'] );
			if ($_POST ['info'] ['parentid'] != 0) {
				//获取上级的层级深度
				$parentinfo = $this->db->where(array('id'=>intval($_POST ['info'] ['parentid'])))->find();
				$_POST ['info'] ['level'] = $parentinfo['level'] + 1;
			}else{
				$_POST ['info'] ['level'] = 0;
			}
			$this->db->where ( array ('id' => $id ) )->update ( $_POST ['info'] );
			// 修改语言文件
			$LANG = array ();
			$file = SOURCE_PATH . 'languages' . DIRECTORY_SEPARATOR . C ( 'config', 'lang' ) . DIRECTORY_SEPARATOR . 'admin_menu.php';
			require $file;
			$key = $_POST ['info'] ['name'];
			if (! isset ( $LANG [$key] )) {
				$content = file_get_contents ( $file );
				$content = substr ( $content, 0, - 2 );
				$data = $content . "\$LANG['$key'] = '$_POST[language]';\r\n?>";
				file_put_contents ( $file, $data );
			} elseif (isset ( $LANG [$key] ) && $LANG [$key] != $_POST ['language']) {
				$content = file_get_contents ( $file );
				$content = str_replace ( $LANG [$key], $_POST ['language'], $content );
				file_put_contents ( $file, $content );
			}
			showmessage ( L ( 'operation_success' ) );
		} else {
			$show_validator = $array = $r = '';
			$tree = new Tree ();
			$id = intval ( $_GET ['id'] );
			$r = $this->db->where ( array ('id' => $id ) )->find ();
			if ($r) extract ( $r );
			$result = $this->db->select ();
			foreach ( $result as $r ) {
				$r ['cname'] = L ( $r ['name'] );
				$r ['selected'] = $r ['id'] == $parentid ? 'selected' : '';
				$array [] = $r;
			}
			$str = "<option value='\$id' \$selected>\$spacer \$cname</option>";
			$tree->init ( $array );
			$select_menus = $tree->get_tree ( 0, $str );
			include $this->admin_tpl ( 'menu' );
		}
	}

	/**
	 * 删除菜单
	 */
	public function delete() {
		$_GET ['id'] = intval ( $_GET ['id'] );
		$this->db->where ( array ('id' => $_GET ['id'] ) )->delete ();
		showmessage ( L ( 'operation_success' ) );
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->where ( array ('id' => $id ) )->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ) );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}
}