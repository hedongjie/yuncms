<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
class message_model extends model {
	public function __construct() {
		$this->setting = 'default';
		$this->table_name = 'message';
		$this->_username = cookie ( '_username' );
		$this->_userid = cookie ( '_userid' );
		parent::__construct ();
	}

	/**
	 *
	 *
	 *
	 * 检查当前用户短消息相关权限
	 *
	 * @param $userid 用户ID
	 */
	public function messagecheck($userid) {
		$member_arr = get_memberinfo ( $this->_userid );
		$groups = S ( 'member/grouplist' );
		if ($groups [$member_arr ['groupid']] ['allowsendmessage'] == 0) {
			showmessage ( '对不起你没有权限发短消息', HTTP_REFERER );
		} else {
			// 判断是否到限定条数
			$num = $this->get_membermessage ( $this->_username );
			if ($num >= $groups [$member_arr ['groupid']] ['allowmessage']) {
				showmessage ( '你的短消息条数已达最大值!', HTTP_REFERER );
			}
		}
	}

	/**
	 * 获取用户发消息信息 .
	 * ..
	 */
	public function get_membermessage($username) {
		$arr = $this->where(array ('send_from_id' => $username ))->select (  );
		return count ( $arr );
	}

	public function add_message($tousername, $username, $subject, $content) {
		$message = array ();
		$message ['send_from_id'] = $username;
		$message ['send_to_id'] = $tousername;
		$message ['subject'] = $subject;
		$message ['content'] = $content;
		$message ['message_time'] = TIME;
		$message ['status'] = '1';
		$message ['folder'] = 'inbox';

		if ($message ['send_from_id'] == "") {
			$message ['send_from_id'] = $this->_username;
		}
		if (empty ( $message ['content'] )) {
			showmessage ( '发信内空不能为空！', HTTP_REFERER );
		}

		$messageid = $this->insert ( $message, true );
		if (! $messageid) {
			return FALSE;
		} else {
			return true;
		}
	}
}
?>