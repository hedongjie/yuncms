<?php
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class admin_op {

	public function __construct() {
		$this->db = Loader::model ( 'admin_model' );
	}

	/**
	 * 修改密码
	 */
	public function edit_password($userid, $password) {
		$userid = intval ( $userid );
		if ($userid < 1) return false;
		if (! Validate::is_password ( $password )) {
			showmessage ( L ( 'pwd_incorrect' ) );
			return false;
		}
		$passwordinfo = password ( $password );
		return $this->db->where ( array ('userid' => $userid ) )->update ( $passwordinfo );
	}

	/**
	 * 检查用户名重名
	 */
	public function checkname($username) {
		$username = trim ( $username );
		if ($this->db->where ( array ('username' => $username ) )->field ( 'userid' )->find ()) {
			return false;
		}
		return true;
	}
}