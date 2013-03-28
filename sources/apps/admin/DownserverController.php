<?php
/**
 * 下载服务器管理
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-27
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: DownserverController.php 99 2013-03-24 09:45:52Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class DownserverController extends admin {
	private $db;
	function __construct() {
		parent::__construct ();
		$this->db = Loader::model ( 'downserver_model' );
	}

	/**
	 * 下载服务器管理
	 */
	public function init() {
		if (isset ( $_POST ['dosubmit'] )) {
			$info ['siteurl'] = trim ( $_POST ['info'] ['siteurl'] );
			$info ['sitename'] = trim ( $_POST ['info'] ['sitename'] );
			if (empty ( $info ['sitename'] )) showmessage ( L ( 'downserver_not_empty' ), HTTP_REFERER );
			if (empty ( $info ['siteurl'] ) || ! preg_match ( '/(\w+):\/\/(.+)[^\/]$/i', $info ['siteurl'] )) showmessage ( L ( 'downserver_error' ), HTTP_REFERER );
			$insert_id = $this->db->insert ( $info, true );
			if ($insert_id) {
				$this->_set_cache ();
				showmessage ( L ( 'operation_success' ), HTTP_REFERER );
			}
		} else {
			$infos = $sitelist = array ();
			$page = isset ( $_GET ['page'] ) ? $_GET ['page'] : '1';
			$infos = $this->db->order('listorder DESC,id DESC')->listinfo ($page, 20 );
			$pages = $this->db->pages;
			include $this->admin_tpl ( 'downserver_list' );
		}
	}

	/**
	 * 修改下载服务器
	 */
	public function edit() {
		if (isset ( $_POST ['dosubmit'] )) {
			$info ['siteurl'] = trim ( $_POST ['info'] ['siteurl'] );
			$info ['sitename'] = trim ( $_POST ['info'] ['sitename'] );
			if (empty ( $info ['sitename'] )) showmessage ( L ( 'downserver_not_empty' ), HTTP_REFERER );
			if (empty ( $info ['siteurl'] ) || ! preg_match ( '/(\w+):\/\/(.+)[^\/]$/i', $info ['siteurl'] )) showmessage ( L ( 'downserver_error' ), HTTP_REFERER );
			$id = intval ( trim ( $_POST ['id'] ) );
			$this->_set_cache ();
			$this->db->where(array ('id' => $id ))->update ( $info );
			showmessage ( L ( 'operation_success' ), '', '', 'edit' );
		} else {
			$info = $sitelist = array ();
			$info = $this->db->where ( array ('id' => $_GET ['id'] ) )->find();
			extract ( $info );
			$show_validator = true;
			$show_header = true;
			include $this->admin_tpl ( 'downserver_edit' );
		}
	}

	/**
	 * 删除下载服务器
	 */
	public function delete() {
		$id = intval ( $_GET ['id'] );
		$this->db->where(array ('id' => $id ))->delete (  );
		$this->_set_cache ();
		showmessage ( L ( 'downserver_del_success' ), HTTP_REFERER );
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
			showmessage ( L ( 'operation_failure' ), HTTP_REFERER );
		}
	}

	/**
	 * 设置缓存
	 */
	private function _set_cache() {
		$infos = $this->db->select ();
		$servers = array ();
		foreach ( $infos as $info ) {
			$servers [$info ['id']] = $info;
		}
		S ( 'common/downserver', $servers );
		return $infos;
	}

}