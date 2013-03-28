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
class PrivateController extends admin {
	private $db;

	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'admin_model' );
		$this->op = Loader::lib ( 'admin:admin_op' );
	}

	/**
	 * 编辑用户信息
	 */
	public function public_edit_info() {
		$userid = $_SESSION ['userid'];
		if (isset ( $_POST ['dosubmit'] )) {
			$admin_fields = array ('mobile','email','realname' );
			$info = array ();
			$info = $_POST ['info'];
			foreach ( $info as $k => $value ) {
				if (! in_array ( $k, $admin_fields )) {
					unset ( $info [$k] );
				}
			}
			$this->db->where ( array ('userid' => $userid ) )->update ( $info );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$info = $this->db->where ( array ('userid' => $userid ) )->find ();
			extract ( $info );
			include $this->admin_tpl ( 'admin_edit_info' );
		}
	}

	/**
	 * 管理员自助修改密码
	 */
	public function public_edit_pwd() {
		$userid = $_SESSION ['userid'];
		if (isset ( $_POST ['dosubmit'] )) {
			$r = $this->db->where ( array ('userid' => $userid ) )->field ( 'password,encrypt' )->find ();
			if (password ( $_POST ['old_password'], $r ['encrypt'] ) !== $r ['password']) showmessage ( L ( 'old_password_wrong' ), HTTP_REFERER );
			if (isset ( $_POST ['new_password'] ) && ! empty ( $_POST ['new_password'] )) {
				$this->op->edit_password ( $userid, $_POST ['new_password'] );
			}
			showmessage ( L ( 'password_edit_succ_logout' ), U ( 'admin/index/logout' ) );
		} else {
			$info = $this->db->where ( array ('userid' => $userid ) )->find ();
			extract ( $info );
			include $this->admin_tpl ( 'admin_edit_pwd' );
		}
	}
}