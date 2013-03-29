<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @copyright Copyright (c) 2003-2103 Jinan TintSoft development co., LTD
 * @license http://www.tintsoft.com/html/about/copyright/
 * @version $Id: IndexController.php 65 2013-02-28 01:34:17Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class IndexController extends admin {

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'admin_model' );
		$this->menu_db = Loader::model ( 'admin_menu_model' );
		$this->panel_db = Loader::model ( 'admin_panel_model' );
	}

	/**
	 * 后台首页
	 */
	public function init() {
		$userid = $_SESSION ['userid'];
		$admin_username = cookie ( 'admin_username' );
		$roles = S ( 'common/role' );
		$rolename = $roles [$_SESSION ['roleid']];
		$adminpanel = $this->panel_db->where ( array ('userid' => $userid ) )->order ( 'datetime' )->select ();
		include $this->admin_tpl ( 'index' );
	}

	/**
	 * 当前位置
	 */
	public function public_current_pos() {
		echo admin::current_pos ( $_GET ['menuid'] );
		exit ();
	}

	/**
	 * 用户登录
	 */
	public function login() {
		if (isset ( $_GET ['dosubmit'] )) {
			$username = isset ( $_POST ['username'] ) ? trim ( $_POST ['username'] ) : showmessage ( L ( 'nameerror' ), HTTP_REFERER, 301 );
			$checkcode = isset ( $_POST ['checkcode'] ) && trim ( $_POST ['checkcode'] ) ? trim ( $_POST ['checkcode'] ) : showmessage ( L ( 'input_checkcode' ), HTTP_REFERER, 301 );
			if (! checkcode ( $checkcode )) {
				showmessage ( L ( 'code_error' ), HTTP_REFERER, 301 );
			}
			// 密码错误剩余重试次数
			$this->times_db = Loader::model ( 'times_model' );
			$rtime = $this->times_db->where ( array ('username' => $username,'isadmin' => 1 ) )->find ();
			//$maxloginfailedtimes = S ( 'common/common' );
			//$maxloginfailedtimes = ( int ) $maxloginfailedtimes ['maxloginfailedtimes'];
			$maxloginfailedtimes = 10;
			if ($rtime && $rtime ['times'] > $maxloginfailedtimes) {
				$minute = 60 - floor ( (TIME - $rtime ['logintime']) / 60 );
				showmessage ( L ( 'wait_1_hour', array ('minute' => $minute ) ), HTTP_REFERER, 301 );
			}
			// 查询帐号
			$r = $this->db->where ( array ('username' => $username ) )->find ();
			if (! $r) showmessage ( L ( 'user_not_exist' ), U ( 'admin/index/login' ) );
			$password = md5 ( md5 ( trim ( $_POST ['password'] ) ) . $r ['encrypt'] );
			if ($r ['password'] != $password) {
				if ($rtime && $rtime ['times'] < $maxloginfailedtimes) {
					$times = $maxloginfailedtimes - intval ( $rtime ['times'] );
					$this->times_db->where ( array ('username' => $username ) )->update ( array ('ip' => IP,'isadmin' => 1,'times' => '+=1' ) );
				} else {
					$this->times_db->where ( array ('username' => $username,'isadmin' => 1 ) )->delete ();
					$this->times_db->insert ( array ('username' => $username,'ip' => IP,'isadmin' => 1,'logintime' => TIME,'times' => 1 ) );
					$times = $maxloginfailedtimes;
				}
				showmessage ( L ( 'password_error', array ('times' => $times ) ), HTTP_REFERER, 301 );
			}
			$this->times_db->where ( array ('username' => $username ) )->delete ();
			$this->db->where ( array ('userid' => $r ['userid'] ) )->update ( array ('lastloginip' => IP,'lastlogintime' => TIME ) );
			$_SESSION ['userid'] = $r ['userid'];
			$_SESSION ['roleid'] = $r ['roleid'];
			$_SESSION ['lock_screen'] = 0;
			$cookie_time = TIME + 86400 * 30;
			cookie ( 'admin_username', $username, $cookie_time );
			cookie ( 'userid', $r ['userid'], $cookie_time );
			cookie ( 'admin_email', $r ['email'], $cookie_time );
			showmessage ( L ( 'login_success' ), U ( 'admin/index' ) );
		} else {
			include $this->admin_tpl ( 'login' );
		}
	}

	/*
	 * 后台首页
	*/
	public function public_main() {
		Loader::helper ( 'admin:global' );
		Loader::helper ( 'admin:admin' );
		define ( 'YUNCMS_VERSION', C ( 'version', 'version' ) );
		define ( 'YUNCMS_RELEASE', C ( 'version', 'release' ) );
		$show_dialog = true;
		$admin_username = cookie ( 'admin_username' );
		$roles = S ( 'common/role' );
		$userid = $_SESSION ['userid'];
		$rolename = $roles [$_SESSION ['roleid']];
		$r = $this->db->where ( array ('userid' => $userid ) )->find();
		$logintime = $r ['lastlogintime'];
		$loginip = $r ['lastloginip'];
		$sysinfo = get_sysinfo ();
		$sysinfo ['mysqlv'] = $this->db->version ();
		$show_header = $show_hash = 1;
		/* 检测框架目录可写性 */
		$yun_writeable = is_writable ( SOURCE_PATH . 'init.php' );
		$maxfilesize = ( int ) C ( 'log', 'file_size' ) * 1024;
		$logsize_warning = errorlog_size () >= $maxfilesize ? '1' : '0';
		$adminpanel = $this->panel_db->select ( array ('userid' => $userid ), '*', 20, 'datetime' );
		$product_copyright = base64_decode ( '5rWO5Y2X5aSp5pm66L2v5Lu25byA5Y+R5pyJ6ZmQ5YWs5Y+4' );
		$architecture = base64_decode ( '5b6Q6LaF' );
		$programmer = base64_decode ( '5b6Q6LaF44CB6JGj5L+d6Iqz' );
		$designer = base64_decode ( '5aea5a2Q5ra1' );
		ob_start ();
		include $this->admin_tpl ( 'main' );
		$data = ob_get_contents ();
		ob_end_clean ();
		system_information ( $data );
	}

	/**
	 * 左侧菜单
	 */
	public function public_menu_left() {
		$menuid = intval ( $_GET ['menuid'] );
		$datas = admin::admin_menu ( $menuid );
		if (isset ( $_GET ['parentid'] ) && $parentid = intval ( $_GET ['parentid'] ) ? intval ( $_GET ['parentid'] ) : 1) {
			foreach ( $datas as $_value ) {
				if ($parentid == $_value ['id']) {
					echo '<li id="_M' . $_value ['id'] . '" class="on top_menu"><a href="javascript:_M(' . $_value ['id'] . ',\'?app=' . $_value ['application'] . '&controller=' . $_value ['controller'] . '&action=' . $_value ['action'] . '\')" hidefocus="true" style="outline:none;">' . L ( $_value ['name'] ) . '</a></li>';

				} else {
					echo '<li id="_M' . $_value ['id'] . '" class="top_menu"><a href="javascript:_M(' . $_value ['id'] . ',\'?app=' . $_value ['application'] . '&controller=' . $_value ['controller'] . '&action=' . $_value ['action'] . '\')"  hidefocus="true" style="outline:none;">' . L ( $_value ['name'] ) . '</a></li>';
				}
			}
		} else {
			include $this->admin_tpl ( 'left' );
		}
	}

	/**
	 * 添加常用菜单
	 */
	public function public_ajax_add_panel() {
		$menuid = isset ( $_POST ['menuid'] ) ? $_POST ['menuid'] : exit ( '0' );
		$menuarr = $this->menu_db->where ( array ('id' => $menuid ) )->find();
		$url = '?app=' . $menuarr ['application'] . '&controller=' . $menuarr ['controller'] . '&action=' . $menuarr ['action'] . '&' . $menuarr ['data'];
		$data = array ('menuid' => $menuid,'userid' => $_SESSION ['userid'],'name' => $menuarr ['name'],'url' => $url,'datetime' => TIME );
		$this->panel_db->insert ( $data, '', 1 );
		$panelarr = $this->panel_db->where(array ('userid' => $_SESSION ['userid'] ))->order("datetime")->listinfo ( );
		foreach ( $panelarr as $v ) {
			echo "<span><a onclick='paneladdclass(this);' target='right' href='" . $v ['url'] . '&menuid=' . $v ['menuid'] . "'>" . L ( $v ['name'] ) . "</a>  <a class='panel-delete' href='javascript:delete_panel(" . $v ['menuid'] . ");'></a></span>";
		}
		exit ();
	}

	/**
	 * 删除常用菜单
	 */
	public function public_ajax_delete_panel() {
		$menuid = isset ( $_POST ['menuid'] ) ? $_POST ['menuid'] : exit ( '0' );
		$this->panel_db->where ( array ('menuid' => $menuid,'userid' => $_SESSION ['userid'] ) )->delete();

		$panelarr = $this->panel_db->where(array ('userid' => $_SESSION ['userid'] ))->order("datetime")->listinfo ( );
		foreach ( $panelarr as $v ) {
			echo "<span><a onclick='paneladdclass(this);' target='right' href='" . $v ['url'] . "'>" . L ( $v ['name'] ) . "</a> <a class='panel-delete' href='javascript:delete_panel(" . $v ['menuid'] . ");'></a></span>";
		}
		exit ();
	}

	/**
	 * 维持 session 登陆状态
	 */
	public function public_session_life() {
		$userid = $_SESSION ['userid'];
		return true;
	}

	/**
	 * 锁屏
	 */
	public function public_lock_screen() {
		$_SESSION ['lock_screen'] = 1;
	}

	/**
	 * 解除锁屏
	 */
	public function public_login_screenlock() {
		if (empty ( $_GET ['lock_password'] )) showmessage ( L ( 'password_can_not_be_empty' ) );
		// 密码错误剩余重试次数
		$this->times_db = Loader::model ( 'times_model' );
		$username = cookie ( 'admin_username' );
		$maxloginfailedtimes = S ( 'common/common' );
		$maxloginfailedtimes = ( int ) $maxloginfailedtimes ['maxloginfailedtimes'];
		$rtime = $this->times_db->where ( array ('username' => $username,'isadmin' => 1 ) )->find();
		if ($rtime ['times'] > $maxloginfailedtimes - 1) {
			$minute = 60 - floor ( (TIME - $rtime ['logintime']) / 60 );
			exit ( '3' );
		}
		// 查询帐号
		$r = $this->db->where ( array ('userid' => $_SESSION ['userid'] ) )->find();
		$password = md5 ( md5 ( $_GET ['lock_password'] ) . $r ['encrypt'] );
		if ($r ['password'] != $password) {
			if ($rtime && $rtime ['times'] < $maxloginfailedtimes) {
				$times = $maxloginfailedtimes - intval ( $rtime ['times'] );
				$this->times_db->where ( array ('username' => $username ) )->update(array ('ip' => IP,'isadmin' => 1,'times' => '+=1' ));
			} else {
				$this->times_db->insert ( array ('username' => $username,'ip' => IP,'isadmin' => 1,'logintime' => TIME,'times' => 1 ) );
				$times = $maxloginfailedtimes;
			}
			exit ( '2|' . $times ); // 密码错误
		}
		$this->times_db->where ( array ('username' => $username ) )->delete();
		$_SESSION ['lock_screen'] = 0;
		exit ( '1' );
	}

	/**
	 * 后台站点地图
	 */
	public function public_map() {
		$array = admin::admin_menu ( 0 );
		$menu = array ();
		foreach ( $array as $k => $v ) {
			$menu [$v ['id']] = $v;
			$menu [$v ['id']] ['childmenus'] = admin::admin_menu ( $v ['id'] );
		}
		$show_header = true;
		include $this->admin_tpl ( 'map' );
	}

	/**
	 * 我们的
	 */
	public function public_our() {
		include $this->admin_tpl ( 'our' );
	}

	/**
	 * 退出登录
	 */
	public function public_logout() {
		$_SESSION ['userid'] = 0;
		$_SESSION ['roleid'] = 0;
		cookie ( 'admin_username', '' );
		cookie ( 'userid', '');
		showmessage ( L ( 'logout_success' ), '?app=admin&controller=index&action=login' );
	}
}