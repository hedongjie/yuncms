<?php
/**
 * 应用管理
 * @author Tongle Xu <xutongle@gmail.com> 2013-2-26
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id$
 */
Loader::lib ( 'admin:admin', false );
class ApplicationController extends admin {
	private $db;

	public function __construct() {
		$this->db = Loader::model ( 'application_model' );
		parent::__construct ();
	}

	public function init() {
		$page = isset ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$dirs = $application = $dirs_arr = $directory = array ();
		$dirs = glob ( APPS_PATH . '*' );
		foreach ( $dirs as $d ) {
			if (is_dir ( $d )) {
				$d = basename ( $d );
				$dirs_arr [] = $d;
			}
		}
		//应用总数
		$total = count ( $dirs_arr );
		$dirs_arr = array_chunk ( $dirs_arr, 15, true );
		define ( 'INSTALL', true );
		$applications = $this->db->key('application')->select ( );
		$pages = Page::pages ( $total, $page, 15 );
		$directory = $dirs_arr [intval ( $page - 1 )];
		include $this->admin_tpl ( 'application_list' );
	}

	/**
	 * 应用安装
	 */
	public function install() {
		$this->application = isset ( $_POST ['application'] ) ? $_POST ['application'] : $_GET ['application'];
		$application_api = Loader::lib ( 'admin:application_api' );
		if (! $application_api->check ( $this->application )) showmessage ( $application_api->error_msg, 'blank' );
		if (isset ( $_POST ['dosubmit'] )) {
			if ($application_api->install ()) showmessage ( L ( 'success_application_install' ) . L ( 'update_cache' ), '?app=admin&controller=application&action=cache' );
			else showmessage ( $application_api->error_msg, HTTP_REFERER );
		} else {
			include APPS_PATH . $this->application . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'config.inc.php';
			include $this->admin_tpl ( 'application_config' );
		}
	}

	/**
	 * 应用卸载
	 */
	public function uninstall() {
		if (! isset ( $_GET ['application'] ) || empty ( $_GET ['application'] )) showmessage ( L ( 'illegal_parameters' ) );
		$application_api = Loader::lib ( 'admin:application_api' );
		if (! $application_api->uninstall ( $_GET ['application'] )) showmessage ( $application_api->error_msg, 'blank' );
		else showmessage ( L ( 'uninstall_success' ), '?app=admin&controller=application&action=cache' );
	}

	/**
	 * 更新应用缓存
	 */
	public function cache() {
		echo '<script type="text/javascript">parent.right.location.href = \'?app=admin&controller=cache_all&action=init\';window.top.art.dialog({id:\'install\'}).close();</script>';
	}
}