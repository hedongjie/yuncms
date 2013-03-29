<?php
/**
 * IP禁止
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class IpbannedController extends admin {

	public function __construct() {
		$this->db = Loader::model ( 'ipbanned_model' );
		parent::__construct ();
	}

	/**
	 * IP禁止
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : '1';
		$infos = array ();
		$infos = $this->db->order ( 'ipbannedid DESC' )->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'admin/ipbanned/add' ), 'add', L ( 'add_ipbanned' ), 450, 300 );
		include $this->admin_tpl ( 'ipbanned_list' );
	}

	/**
	 * 验证数据有效性
	 */
	public function public_name() {
		$ip = isset ( $_GET ['ip'] ) && trim ( $_GET ['ip'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['ip'] ) ) : trim ( $_GET ['ip'] )) : exit ( '0' );
		if ($this->db->where ( array ('ip' => $ip ), 'ipbannedid' )->find ()) {
			exit ( '0' );
		} else {
			exit ( '1' );
		}
	}

	/**
	 * IP添加
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] ['expires'] = strtotime ( $_POST ['info'] ['expires'] );
			$this->db->insert ( $_POST ['info'] );
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/ipbanned/add' ), '', 'add' );
		} else {
			$show_validator = $show_scroll = $show_header = true;
			include $this->admin_tpl ( 'ipbanned_add' );
		}
	}

	/**
	 * IP删除
	 */
	function delete() {
		if (isset ( $_POST ['ipbannedid'] ) && is_array ( $_POST ['ipbannedid'] )) {
			foreach ( $_POST ['ipbannedid'] as $ipbannedid_arr ) {
				$this->db->where ( array ('ipbannedid' => $ipbannedid_arr ) )->delete ();
			}
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/ipbanned/init' ) );
		} else {
			$ipbannedid = intval ( $_GET ['ipbannedid'] );
			if ($ipbannedid < 1) return false;
			$this->db->where ( array ('ipbannedid' => $ipbannedid ) )->delete ();
			$this->public_cache_file (); // 更新缓存
			showmessage ( L ( 'operation_success' ), U ( 'admin/ipbanned/init' ) );

		}
	}

	/**
	 * IP搜索
	 */
	public function search_ip() {
		$where = '';
		if ($_GET ['search']) extract ( $_GET ['search'] );
		if (isset ( $ip )) {
			$where .= $where ? " AND ip LIKE '%$ip%'" : " ip LIKE '%$ip%'";
		}
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$infos = $this->db->where ( $where )->order ( 'ipbannedid DESC' )->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'admin/ipbanned/add' ), 'add', L ( 'add_ipbanned' ), 450, 300 );
		include $this->admin_tpl ( 'ip_search_list' );
	}

	/**
	 * 生成缓存
	 */
	public function public_cache_file() {
		$infos = $this->db->field ( 'ip,expires' )->order ( 'ipbannedid desc' )->select ();
		S ( 'common/ipbanned', $infos );
		return true;
	}
}