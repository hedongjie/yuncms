<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
/**
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-7
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: message_tag.php 39 2012-11-05 12:09:50Z xutongle $
 */
class message_tag {
	private $message_db;
	public function __construct() {
		$this->message_db = Loader::model ( 'message_model' );
		$this->message_group_db = Loader::model ( 'message_group_model' );
		$this->message_data_db = Loader::model ( 'message_data_model' );
		$this->_username = cookie ( '_username' );
		$this->_userid = cookie ( '_userid' );
		$this->_groupid = get_memberinfo ( $this->_userid, 'groupid' );
	}

	/**
	 * 检测是否有新邮件
	 *
	 * @param $typeid 分类ID
	 */
	public function check_new() {
		$where = array ('send_to_id' => $this->_username,'folder' => 'inbox','status' => '1' );
		$new_count = $this->message_db->where ( $where )->count ();
		// 检查是否有未查看的新系统短信
		// 检查该会员所在会员组 的系统公告,再查询message_data表. 是否有记录. 无则加入 未读NUM.
		$group_num = 0;
		$group_where = array ('typeid' => '1','groupid' => $this->_groupid,'status' => '1' );
		$group_arr = $this->message_group_db->where ( $group_where )->select ();
		foreach ( $group_arr as $groupid => $group ) {
			$group_message_id = $group ['id'];
			$where = array ('group_message_id' => $group_message_id,'userid' => $this->_userid );
			$result = $this->message_data_db->where ( $where )->select ();
			if (! $result) $group_num ++;
		}
		// 生成一个新数组,并返回此数组
		$new_arr = array ();
		$new_arr ['new_count'] = $new_count;
		$new_arr ['new_group_count'] = $group_num;
		return $new_arr;
	}
}