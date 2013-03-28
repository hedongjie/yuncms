<?php
/**
 * 公告管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-4
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Admin_announceController.php 297 2012-11-09 17:06:31Z xutongle $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class Admin_announceController extends admin {

	private $db;
	public $username;
	public function __construct() {
		parent::__construct ();
		$this->username = cookie ( 'admin_username' );
		$this->db = Loader::model ( 'announce_model' );
	}

	public function init() {
		$where = array();
		$sql = '';
		$_GET ['status'] = isset ( $_GET ['status'] ) ? intval ( $_GET ['status'] ) : 1;
		switch ($_GET ['s']) {
			case '1' :
				$where['passed'] = '1';
				$where['endtime'] = array(array('gt',date ( 'Y-m-d' )),array('eq','0000-00-00'),'or') ;
				break;
			case '2' :
				$where['passed'] = '0';
				break;
			case '3' :
			$map = array();
				$where['passed'] = '1';
				$where['endtime'] = array(array('lt',date ( 'Y-m-d' )),array('neq','0000-00-00'),'and') ;
				break;
		}
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$data = $this->db->where($where)->order('aid DESC')->listinfo ($page );
		$big_menu = big_menu ( U('announce/admin_announce/add'), 'add', L ( 'announce_add' ), 850, 500 );
		include $this->admin_tpl ( 'announce_list' );
	}

	/**
	 * 添加公告
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['announce'] = $this->check ( $_POST ['announce'] );
			if ($this->db->insert ( $_POST ['announce'] )) showmessage ( L ( 'announcement_successful_added' ), HTTP_REFERER, '', 'add' );
		} else {
			Loader::helper ( 'admin:global' ); // 获取站点模板信息
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$show_header = $show_dialog = $show_validator = $show_scroll = 1;
			include $this->admin_tpl ( 'announce_add' );
		}
	}

	/**
	 * 修改公告
	 */
	public function edit() {
		$aid = isset ( $_GET ['aid'] ) ? intval ( $_GET ['aid'] ) : showmessage ( L ( 'illegal_operation' ) );
		;
		if (isset ( $_POST ['dosubmit'] )) {
			$info = $this->check ( $_POST ['announce'], 'edit' );
			if ($this->db->where(array ('aid' => $aid ))->update ( $info )) {
				showmessage ( L ( 'announced_a' ), HTTP_REFERER, '', 'edit' );
			} else {
				showmessage ( L ( 'operation_failure' ), HTTP_REFERER, '', 'edit' );
			}
		} else {
			$where = array ('aid' => $aid );
			$an_info = $this->db->where ( $where )->find();
			Loader::helper ( 'admin:global' ); // 获取站点模板信息
			$template_list = template_list ( 0 );
			foreach ( $template_list as $k => $v ) {
				$template_list [$v ['dirname']] = $v ['name'] ? $v ['name'] : $v ['dirname'];
				unset ( $template_list [$k] );
			}
			$show_header = $show_validator = $show_scroll = 1;
			include $this->admin_tpl ( 'announce_edit' );
		}
	}

	/**
	 * ajax检测公告标题是否重复
	 */
	public function public_check_title() {
		$title = isset ( $_GET ['title'] ) ? trim ( $_GET ['title'] ) : exit ( '0' );
		if (CHARSET == 'gbk') $title = iconv ( 'UTF-8', 'GBK', $title );
		if (isset ( $_GET ['aid'] )) {
			$r = $this->db->getby_aid ( $_GET ['aid'] );
			if ($r ['title'] == $title) exit ( '1' );
		}

		$r = $this->db->where ( array ('title' => $title ) )->field( 'aid')->find();
		if (isset ( $r ['aid'] ))
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 批量修改公告状态 使其成为审核、未审核状态
	 */
	public function public_approval($aid = 0) {
		if ((! isset ( $_POST ['aid'] ) || empty ( $_POST ['aid'] )) && ! $aid)
			showmessage ( L ( 'illegal_operation' ) );
		else {
			if (is_array ( $_POST ['aid'] ) && ! $aid) {
				array_map ( array ($this,'public_approval' ), $_POST ['aid'] );
				showmessage ( L ( 'announce_passed' ), HTTP_REFERER );
			} elseif ($aid) {
				$aid = intval ( $aid );
				$this->db->where(array ('aid' => $aid ))->update ( array ('passed' => $_GET ['passed'] ) );
				return true;
			}
		}
	}

	/**
	 * 批量删除公告
	 */
	public function delete($aid = 0) {
		if ((! isset ( $_POST ['aid'] ) || empty ( $_POST ['aid'] )) && ! $aid) {
			showmessage ( L ( 'illegal_operation' ) );
		} else {
			if (is_array ( $_POST ['aid'] ) && ! $aid) {
				array_map ( array ($this,'delete' ), $_POST ['aid'] );
				showmessage ( L ( 'announce_deleted' ), HTTP_REFERER );
			} elseif ($aid) {
				$aid = intval ( $aid );
				$this->db->where(array ('aid' => $aid ))->delete (  );
			}
		}
	}

	/**
	 * 验证表单数据
	 *
	 * @param array $data
	 *        	表单数组数据
	 * @param string $a
	 *        	当表单为添加数据时，自动补上缺失的数据。
	 * @return array 验证后的数据
	 */
	private function check($data = array(), $a = 'add') {
		if ($data ['title'] == '') showmessage ( L ( 'title_cannot_empty' ) );
		if ($data ['content'] == '') showmessage ( L ( 'announcements_cannot_be_empty' ) );
		$r = $this->db->where ( array ('title' => $data ['title'] ) )->find();
		if (strtotime ( $data ['endtime'] ) < strtotime ( $data ['starttime'] )) $data ['endtime'] = '0000-00-00';
		if ($a == 'add') {
			if (is_array ( $r ) && ! empty ( $r )) showmessage ( L ( 'announce_exist' ), HTTP_REFERER );
			$data ['addtime'] = TIME;
			$data ['username'] = $this->username;
			if ($data ['starttime'] == '') $announce ['starttime'] = date ( 'Y-m-d' );
		} else {
			if (isset ( $r ['aid'] ) && ($r ['aid'] != $_GET ['aid'])) showmessage ( L ( 'announce_exist' ), HTTP_REFERER );
		}
		return $data;
	}
}