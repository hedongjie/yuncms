<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @copyright Copyright (c) 2003-2103 Jinan TintSoft development co., LTD
 * @license http://www.tintsoft.com/html/about/copyright/
 * @version $Id: admin.php 42 2013-02-26 02:55:12Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::session ();
define ( 'IN_ADMIN', true );
class admin extends Core_Controller {
	public $userid;
	public $username;

	public function __construct() {
		self::check_admin ();
		self::check_priv ();
		Loader::helper ( 'admin:global' );
		//if (! application_exists ( APP )) showmessage ( L ( 'application_not_exists' ) );
		self::manage_log ();
		self::check_ip ();
		self::lock_screen ();
		if (C ( 'system', 'admin_url' ) && $_SERVER ["SERVER_NAME"] != C ( 'system', 'admin_url' )) {
			Header ( "http/1.1 403 Forbidden" );
			exit ( 'No permission resources.' );
		}
	}

	/**
	 * 判断用户是否已经登陆
	 */
	final public function check_admin() {
		if (APP == 'admin' && CONTROLLER == 'Index' && in_array ( ACTION, array ('login' ) )) {
			return true;
		} else {
			if (! isset ( $_SESSION ['userid'] ) || ! isset ( $_SESSION ['roleid'] ) || ! $_SESSION ['userid'] || ! $_SESSION ['roleid']) {
				showmessage ( L ( 'admin_login' ), U ( 'admin/index/login' ) );
			}
		}
	}

	/**
	 * 权限判断
	 */
	final public function check_priv() {
		if (APP == 'admin' && CONTROLLER == 'Index' && in_array ( ACTION, array ('login','init' ) )) return true;
		if ($_SESSION ['roleid'] == 1) return true;
		$action = ACTION;
		$privdb = Loader::model ( 'admin_role_priv_model' );
		if (preg_match ( '/^public_/', ACTION )) return true;
		if (preg_match ( '/^ajax_([a-z]+)_/', ACTION, $_match )) $action = $_match [1];
		$r = $privdb->where ( array ('application' => APP,'controller' => CONTROLLER,'action' => $action,'roleid' => $_SESSION ['roleid'] ) )->find();
		if (! $r) showmessage ( '您没有权限操作该项!', 'blank' );
	}

	/**
	 * 记录日志
	 */
	final private function manage_log() {
		// 判断是否记录
		if (C ( 'system', 'admin_log' )) {
			$action = ACTION;
			if ($action == '' || strchr ( $action, 'public' ) || $action == 'init' || $action == 'public_current_pos' || ! isset ( $_SESSION ['userid'] )) {
				return false;
			} else {
				$log = Loader::model ( 'admin_log_model' );
				$username = cookie ( 'admin_username' );
				$userid = isset ( $_SESSION ['userid'] ) ? $_SESSION ['userid'] : '';
				$time = date ( 'Y-m-d H-i-s', TIME );
				$url = '?app=' . APP . '&controller=' . CONTROLLER . '&action=' . ACTION;
				$data = isset ( $_POST ) ? array2string ( $_POST ) : array2string ( $_GET );
				$log->insert ( array ('application' => APP,'controller' => CONTROLLER,'username' => $username,'userid' => $userid,'action' => ACTION,'querystring' => $url,'data' => $data,'time' => $time,'ip' => IP ) );
			}
		}
	}

	/**
	 * 后台IP禁止判断
	 */
	final private function check_ip() {
		$this->ipbanned = Loader::model ( 'ipbanned_model' );
		$this->ipbanned->check_ip ();
	}

	/**
	 * 按父ID查找菜单子项
	 *
	 * @param integer $parentid 父菜单ID
	 * @param integer $with_self 是否包括他自己
	 */
	final public static function admin_menu($parentid, $with_self = 0) {
		$parentid = intval ( $parentid );
		$menudb = Loader::model ( 'admin_menu_model' );
		$result = $menudb->where ( array ('parentid' => $parentid,'display' => 1 ) )->order ( 'listorder ASC' )->select ();
		if ($with_self) {
			$result2 [] = $menudb->where ( array ('id' => $parentid ) )->find ();
			$result = array_merge ( $result2, $result );
		}
		// 权限检查
		if ($_SESSION ['roleid'] == 1) return $result;
		$array = array ();
		$privdb = Loader::model ( 'admin_role_priv_model' );
		foreach ( $result as $v ) {
			$action = $v ['action'];
			if (preg_match ( '/^public_/', $action )) {
				$array [] = $v;
			} else {
				if (preg_match ( '/^ajax_([a-z]+)_/', $action, $_match )) $action = $_match [1];
				$r = $privdb->where ( array ('application' => $v ['application'],'controller' => $v ['controller'],'action' => $action,'roleid' => $_SESSION ['roleid'] ) )->find ();
				if ($r) $array [] = $v;
			}
		}
		return $array;
	}

	/**
	 * 获取菜单 头部菜单导航
	 *
	 * @param $parentid 菜单id
	 */
	final public static function submenu($parentid = '', $big_menu = false) {
		if (empty ( $parentid )) {
			$menudb = Loader::model ( 'admin_menu_model' );
			$r = $menudb->where ( array ('application' => APP,'controller' => CONTROLLER,'action' => ACTION ) )->find ();
			$parentid = $_GET ['menuid'] = $r ['id'];
		}
		$array = self::admin_menu ( $parentid, 1 );
		$numbers = count ( $array );
		if ($numbers == 1 && ! $big_menu) return '';
		$string = '';
		foreach ( $array as $_value ) {
			if (! isset ( $_GET ['s'] )) {
				$classname = APP == $_value ['application'] && CONTROLLER == $_value ['controller'] && ACTION == $_value ['action'] ? 'class="on"' : '';
			} else {
				$_s = ! empty ( $_value ['data'] ) ? str_replace ( '=', '', strstr ( $_value ['data'], '=' ) ) : '';
				$classname = APP == $_value ['application'] && CONTROLLER == $_value ['controller'] && ACTION == $_value ['action'] && $_GET ['s'] == $_s ? 'class="on"' : '';
			}
			if ($_value ['parentid'] == 0 || $_value ['application'] == '') continue;
			if ($classname) {
				$string .= "<a href='javascript:;' $classname><em>" . L ( $_value ['name'] ) . "</em></a><span>|</span>";
			} else {
				$string .= "<a href='?app=" . $_value ['application'] . "&controller=" . $_value ['controller'] . "&action=" . $_value ['action'] . "&menuid=$parentid" . '&' . $_value ['data'] . "' $classname><em>" . L ( $_value ['name'] ) . "</em></a><span>|</span>";
			}
		}
		$string = substr ( $string, 0, - 14 );
		return $string;
	}

	/**
	 * 当前位置
	 *
	 * @param $id 菜单id
	 */
	final public static function current_pos($id) {
		$menudb = Loader::model ( 'admin_menu_model' );
		$r = $menudb->where ( array ('id' => $id ))->field( 'id,name,parentid' )->find();
		$str = '';
		if ($r ['parentid']) {
			$str = self::current_pos ( $r ['parentid'] );
		}
		return $str . L ( $r ['name'] ) . ' > ';
	}

	/**
	 * 检查锁屏状态
	 */
	final private function lock_screen() {
		if (isset ( $_SESSION ['lock_screen'] ) && $_SESSION ['lock_screen'] == 1) {
			if (preg_match ( '/^public_/', ACTION ) || (APP == 'content' && CONTROLLER == 'Create_html') || (ACTION == 'login') || (APP == 'search' && CONTROLLER == 'Search_admin' && ACTION == 'createindex')) return true;
			showmessage ( L ( 'admin_login' ), U ( 'admin/index/login' ) );
		}
	}

	/**
	 * 加载后台模板
	 *
	 * @param string $file 文件名
	 * @param string $application 模型名
	 */
	final public static function admin_tpl($file, $application = '') {
		$application = empty ( $application ) ? APP : $application;
		if (empty ( $application )) return false;
		return APPS_PATH . $application . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file . '.tpl.php';
	}
}