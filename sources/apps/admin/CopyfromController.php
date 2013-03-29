<?php
/**
 * 来源管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: CopyfromController.php 100 2013-03-24 09:49:11Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class CopyfromController extends admin {
	private $db;
	function __construct() {
		$this->db = Loader::model ( 'copyfrom_model' );
		parent::__construct ();
	}

	/**
	 * 来源管理列表
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$datas = $this->db->order('listorder ASC')->listinfo ( $page );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'admin/copyfrom/add' ), 'add', L ( 'add_copyfrom' ), 580, 240 );
		$this->public_cache ();
		include $this->admin_tpl ( 'copyfrom_list' );
	}

	/**
	 * 添加来源
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$_POST ['info'] = $this->check ( $_POST ['info'] );
			$this->db->insert ( $_POST ['info'] );
			showmessage ( L ( 'add_success' ), '', '', 'add' );
		} else {
			$show_header = $show_validator = '';
			include $this->admin_tpl ( 'copyfrom_add' );
		}
	}

	/**
	 * 修改来源
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$id = intval ( $_POST ['id'] );
			$_POST ['info'] = $this->check ( $_POST ['info'] );
			$this->db->where(array ('id' => $id ))->update ( $_POST ['info'] );
			showmessage ( L ( 'update_success' ), '', '', 'edit' );
		} else {
			$show_header = $show_validator = '';
			$id = intval ( $_GET ['id'] );
			if (! $id) showmessage ( L ( 'illegal_action' ) );
			$r = $this->db->getby_id ( $id );
			if (empty ( $r )) showmessage ( L ( 'illegal_action' ) );
			extract ( $r );
			include $this->admin_tpl ( 'copyfrom_edit' );
		}
	}

	/**
	 * 删除来源
	 */
	public function delete() {
		$_GET ['id'] = intval ( $_GET ['id'] );
		if (! $_GET ['id']) showmessage ( L ( 'illegal_action' ) );
		$this->db->where ( array ('id' => $_GET ['id'] ) )->delete();
		exit ( '1' );
	}

	/**
	 * 检查POST数据
	 *
	 * @param array $data
	 *        	前台POST数据
	 * @return array $data
	 */
	private function check($data = array()) {
		if (! is_array ( $data ) || empty ( $data )) return array ();
		if (! preg_match ( '/^((http|https):\/\/)?([^\/]+)/i', $data ['siteurl'] )) showmessage ( L ( 'input' ) . L ( 'copyfrom_url' ) );
		if (empty ( $data ['sitename'] )) showmessage ( L ( 'input' ) . L ( 'copyfrom_name' ) );
		if ($data ['thumb'] && ! preg_match ( '/^((http|https):\/\/)?([^\/]+)/i', $data ['thumb'] )) showmessage ( L ( 'copyfrom_logo' ) . L ( 'format_incorrect' ) );
		return $data;
	}

	/**
	 * 排序
	 */
	public function listorder() {
		if (isset ( $_POST ['dosubmit'] )) {
			foreach ( $_POST ['listorders'] as $id => $listorder ) {
				$this->db->where(array ('id' => $id ))->update ( array ('listorder' => $listorder ) );
			}
			showmessage ( L ( 'operation_success' ), HTTP_REFERER );
		} else {
			showmessage ( L ( 'operation_failure' ) );
		}
	}

	/**
	 * 生成缓存
	 */
	public function public_cache() {
		$infos = $this->db->order('listorder DESC')->key('id')->select (  );
		S ( 'admin/copyfrom', $infos );
		return true;
	}
}