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
class AdminController extends admin {
	private $db, $role_db;

	public function __construct() {
		parent::__construct ();
		Loader::helper ( 'admin:admin' );
		$this->db = Loader::model ( 'admin_model' );
		$this->role_db = Loader::model ( 'admin_role_model' );
		$this->op = Loader::lib ( 'admin:admin_op' );
	}

	/**
	 * 管理员管理列表
	 */
	public function init() {
		$userid = $_SESSION ['userid'];
		$admin_username = cookie ( 'admin_username' );
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order ( 'userid DESC' )->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$roles = S ( 'common/role' );
		include $this->admin_tpl ( 'admin_list' );
	}

	/**
	 * 添加管理员
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$info = array ();
			if (! $this->op->checkname ( $_POST ['info'] ['username'] )) showmessage ( L ( 'admin_already_exists' ) );
			$info = checkuserinfo ( $_POST ['info'] );
			if (! checkpasswd ( $info ['password'] )) showmessage ( L ( 'pwd_incorrect' ) );
			$passwordinfo = password ( $info ['password'] );
			$info ['password'] = $passwordinfo ['password'];
			$info ['encrypt'] = $passwordinfo ['encrypt'];
			$admin_fields = array ('username','email','password','encrypt','roleid','realname','mobile' );
			foreach ( $info as $k => $value ) {
				if (! in_array ( $k, $admin_fields )) {
					unset ( $info [$k] );
				}
			}
			$this->db->insert ( $info );
			if ($this->db->insert_id ()) showmessage ( L ( 'operation_success' ), U ( 'admin/admin' ) );
		} else {
			$roles = $this->role_db->where ( array ('disabled' => '0' ) )->select ();
			include $this->admin_tpl ( 'admin_add' );
		}
	}

	/**
	 * 修改管理员
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$memberinfo = $info = array ();
			$info = checkuserinfo ( $_POST ['info'] );
			if (isset ( $info ['password'] ) && ! empty ( $info ['password'] )) $this->op->edit_password ( $info ['userid'], $info ['password'] );
			$userid = $info ['userid'];
			$admin_fields = array ('username','email','roleid','realname','mobile' );
			foreach ( $info as $k => $value ) {
				if (! in_array ( $k, $admin_fields )) unset ( $info [$k] );
			}
			$this->db->where ( array ('userid' => $userid ) )->update ( $info );
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$info = $this->db->where ( array ('userid' => $_GET ['userid'] ) )->find ();
			extract ( $info );
			$roles = $this->role_db->where ( array ('disabled' => '0' ) )->select ();
			$show_header = true;
			include $this->admin_tpl ( 'admin_edit' );
		}
	}

	/**
	 * 删除管理员
	 */
	public function delete() {
		$userid = intval ( $_GET ['userid'] );
		if ($userid == '1') showmessage ( L ( 'this_object_not_del' ), HTTP_REFERER );
		$this->db->where ( array ('userid' => $userid ) )->delete ();
		showmessage ( L ( 'admin_cancel_succ' ) );
	}

	/**
	 * 异步检测用户名
	 */
	public function public_checkname_ajx() {
		$username = isset ( $_GET ['username'] ) && trim ( $_GET ['username'] ) ? trim ( $_GET ['username'] ) : exit ( '0' );
		if ($this->db->field ( 'userid' )->where ( array ('username' => $username ) )->find ()) exit ( '0' );
		exit ( '1' );
	}

	/**
	 * 异步检测密码
	 */
	public function public_password_ajx() {
		$userid = $_SESSION ['userid'];
		$r = array ();
		$r = $this->db->field ( 'password,encrypt' )->where ( array ('userid' => $userid ) )->find ();
		if (password ( $_GET ['old_password'], $r ['encrypt'] ) == $r ['password']) exit ( '1' );
		exit ( '0' );
	}

	/**
	 * 异步检测emial合法性
	 */
	public function public_email_ajx() {
		$email = $_GET ['email'];
		$userid = $_SESSION ['userid'];
		$r = array ();
		$r = $this->db->field ( 'userid,email' )->where ( array ('email' => $email ) )->find ();
		if ($r) {
			if ($userid == $r ['userid'])
				exit ( '1' );
			else
				exit ( '0' );
		} else
			exit ( '1' );
	}
}