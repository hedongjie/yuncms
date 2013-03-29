<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: MessageController.php 1112 2012-10-23 23:22:20Z 85825770@qq.com$
 */
class MessageController extends admin {
	public function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'message_model' );
		$this->group_db = Loader::model ( 'message_group_model' );
		$this->_username = cookie ( 'admin_username' );
		$this->_userid = cookie ( 'userid' );
		foreach ( L ( 'select' ) as $key => $value ) {
			$trade_status [$key] = $value;
		}
		$this->trade_status = $trade_status;
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->order('messageid DESC')->listinfo ($page, 12 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
		$trade_status = $this->trade_status;
		include $this->admin_tpl ( 'message_list' );
	}

	/**
	 * 群发消息管理 .
	 */
	public function message_group_manage() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->group_db->order('id DESC')->listinfo ($page, 12 );
		$pages = $this->group_db->pages;
		$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
		include $this->admin_tpl ( 'message_group_list' );
	}

	/**
	 * 判断用户名是否存在
	 */
	public function public_name() {
		$tousername = isset ( $_GET ['tousername'] ) && trim ( $_GET ['tousername'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['tousername'] ) ) : trim ( $_GET ['tousername'] )) : exit ( '0' );
		// 不能发给自己
		if ($tousername == $this->_username) {
			exit ( '0' );
		}
		// 判断用户名是否存在
		$member_interface = Loader::lib ( 'member:member_interface' );
		if ($tousername) {
			$data = $member_interface->get_member_info ( $tousername, 2 );
			if ($data != '-1') {
				exit ( '1' );
			} else {
				exit ( '0' );
			}
		} else {
			exit ( '0' );
		}
	}

	/**
	 * 删除短消息
	 *
	 * @param intval $sid
	 */
	public function delete() {
		if ((! isset ( $_GET ['messageid'] ) || empty ( $_GET ['messageid'] )) && (! isset ( $_POST ['messageid'] ) || empty ( $_POST ['messageid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {

			if (is_array ( $_POST ['messageid'] )) {
				foreach ( $_POST ['messageid'] as $messageid_arr ) {
					// 批量删除友情链接
					$this->db->where ( array ('messageid' => $messageid_arr ) )->delete();
				}
				showmessage ( L ( 'operation_success' ), '?app=message&controller=message' );
			} else {
				$messageid = intval ( $_GET ['messageid'] );
				if ($messageid < 1) return false;
				// 删除短消息
				$result = $this->db->where ( array ('messageid' => $messageid ) )->delete();
				if ($result) {
					showmessage ( L ( 'operation_success' ), '?app=message&controller=message' );
				} else {
					showmessage ( L ( "operation_failure" ), '?app=message&controller=message' );
				}
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 删除系统 短消息
	 *
	 * @param intval $sid
	 */
	public function delete_group() {
		if ((! isset ( $_GET ['message_group_id'] ) || empty ( $_GET ['message_group_id'] )) && (! isset ( $_POST ['message_group_id'] ) || empty ( $_POST ['message_group_id'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {

			if (is_array ( $_POST ['message_group_id'] )) {
				foreach ( $_POST ['message_group_id'] as $messageid_arr ) {
					// 批量删除系统消息
					$this->group_db->where ( array ('id' => $messageid_arr ) )->delete();
				}
				showmessage ( L ( 'operation_success' ), '?app=message&controller=message&action=message_group_manage' );
			} else {
				$group_id = intval ( $_GET ['message_group_id'] );
				if ($group_id < 1) return false;
				// 删除短消息
				$result = $this->group_db->where ( array ('id' => $group_id ) )->delete();
				if ($result) {
					showmessage ( L ( 'operation_success' ), '?app=message&controller=message&action=message_group_manage' );
				} else {
					showmessage ( L ( "operation_failure" ), '?app=message&controller=message&action=message_group_manage' );
				}
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		}
	}

	/**
	 * 管理按组或角色 群发消息
	 */
	public function message_send() {
		if (isset ( $_POST ['dosubmit'] )) {
			// 插入群发表
			$group_message = array ();
			if (empty ( $_POST ['info'] ['subject'] ) || empty ( $_POST ['info'] ['content'] )) return false;
			$group_message ['subject'] = $_POST ['info'] ['subject'];
			$group_message ['content'] = $_POST ['info'] ['content'];
			$group_message ['typeid'] = $_POST ['info'] ['type'];
			$group_message ['inputtime'] = TIME;
			if ($group_message ['typeid'] == 1) {
				$group_message ['groupid'] = $_POST ['info'] ['groupid'];
			} else {
				$group_message ['groupid'] = $_POST ['info'] ['roleid'];
			}
			$result_id = $this->group_db->insert ( $group_message, true );
			if (! $result_id) {
				showmessage ( L ( 'mass_failure' ), HTTP_REFERER );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER, '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			// LOAD 会员组模型
			$member_group = Loader::model ( 'member_group_model' );
			$member_group_infos = $member_group->order('groupid ASC')->select ( );
			// LOAD 管理员角色模型
			$role = Loader::model ( 'admin_role_model' );
			$role_infos = $role->order('roleid ASC')->select (  );
			include $this->admin_tpl ( 'message_send' );
		}
	}

	/**
	 * 发消息
	 */
	public function send_one() {
		if (isset ( $_POST ['dosubmit'] )) {
			$username = $this->_username;
			$tousername = $_POST ['info'] ['send_to_id'];
			$subject = $_POST ['info'] ['subject'];
			$content = $_POST ['info'] ['content'];
			$this->db->add_message ( $tousername, $username, $subject, $content, true );
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			$show_validator = $show_scroll = true;
			$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
			include $this->admin_tpl ( 'message_send_one' );
		}
	}

	/**
	 * 收件箱
	 */
	public function my_inbox() {
		$where = array ('send_to_id' => $this->_username,'folder' => 'inbox' );
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->where($where)->order('messageid DESC')->listinfo ($page, 12);
		$pages = $this->db->pages;
		$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
		$trade_status = $this->trade_status;
		include $this->admin_tpl ( 'message_inbox_list' );
	}

	/**
	 * 删除-收件箱短消息
	 *
	 * @param intval $sid
	 */
	public function delete_inbox() {
		if ((! isset ( $_GET ['messageid'] ) || empty ( $_GET ['messageid'] )) && (! isset ( $_POST ['messageid'] ) || empty ( $_POST ['messageid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {

			if (is_array ( $_POST ['messageid'] )) {
				foreach ( $_POST ['messageid'] as $messageid_arr ) {
					// 批量删除短消息
					$this->db->where(array ('messageid' => $messageid_arr,'send_to_id' => $this->_username ))->update ( array ('folder' => 'outbox' ) );
				}
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else {
				$messageid = intval ( $_GET ['messageid'] );
				if ($messageid < 1) return false;
				// 删除单个短消息
				$result = $this->db->where(array ('messageid' => $messageid,'send_to_id' => $this->_username ))->update ( array ('folder' => 'outbox' ) );
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			}
		}
	}

	/**
	 * 发件箱
	 */
	public function my_outbox() {
		$where = array ('send_from_id' => $this->_username,'del_type' => '0' );
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->where($where)->order('messageid DESC')->listinfo ( $page, 12 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
		$trade_status = $this->trade_status;
		include $this->admin_tpl ( 'message_outbox_list' );
	}

	/**
	 * 删除-发件箱短消息
	 *
	 * @param intval $sid
	 */
	public function delete_outbox() {
		if ((! isset ( $_GET ['messageid'] ) || empty ( $_GET ['messageid'] )) && (! isset ( $_POST ['messageid'] ) || empty ( $_POST ['messageid'] ))) {
			showmessage ( L ( 'illegal_parameters' ), HTTP_REFERER );
		} else {

			if (is_array ( $_POST ['messageid'] )) {
				foreach ( $_POST ['messageid'] as $messageid_arr ) {
					// 批量删除短消息
					$this->db->where(array ('messageid' => $messageid_arr,'send_from_id' => $this->_username ))->update ( array ('del_type' => '1' ) );
				}
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			} else {
				$messageid = intval ( $_GET ['messageid'] );
				if ($messageid < 1) return false;
				// 删除单个短消息
				$result = $this->db->where( array ('messageid' => $messageid,'send_from_id' => $this->_username ))->update ( array ('del_type' => '1' ) );
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			}
		}
	}

	/**
	 * 短消息搜索
	 */
	public function search_message() {
		if (isset ( $_POST ['dosubmit'] )) {
			$where = '';
			extract ( $_POST ['search'] );
			if (! $username && ! $start_time && ! $end_time) {
				$where = "";
			}
			if ($username) {
				// 判断是查询类型,收件还是发件记录
				if ($status == "") {
					$where .= $where ? " AND send_from_id='$username' or send_to_id='$username'" : " send_from_id='$username' or send_to_id='$username'";
				} else {
					$where .= $where ? " AND $status='$username'" : " $status='$username'";
				}
			}
			if ($start_time && $end_time) {
				$start = strtotime ( $start_time );
				$end = strtotime ( $end_time );
				$where .= $where ? "AND `message_time` >= '$start' AND `message_time` <= '$end' " : " `message_time` >= '$start' AND `message_time` <= '$end' ";
			}
		}
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->listinfo ( $where, $order = 'messageid DESC', $page, 12 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( '?app=message&controller=message&action=message_send', 'add', L ( 'all_send_message' ), 550, 300 );
		$trade_status = $this->trade_status;
		include $this->admin_tpl ( 'message_search_list' );
	}
}
?>