<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
/**
 * 外部数据源
 *
 * @author Tongle Xu <xutongle@gmail.com> 2012-6-8
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: Dbsource_adminController.php 254 2012-11-08 01:00:18Z xutongle
 *          $
 */
class Dbsource_adminController extends admin {
	private $db;
	public function __construct() {
		$this->db = Loader::model ( 'dbsource_model' );
		parent::__construct ();
		Loader::helper ( 'dbsource:global' );
	}
	/**
	 * 外部数据源
	 */
	public function init() {
		$page = isset ( $_GET ['page'] ) && intval ( $_GET ['page'] ) ? intval ( $_GET ['page'] ) : 1;
		$list = $this->db->listinfo ( $page, 20 );
		$pages = $this->db->pages;
		$big_menu = big_menu ( U ( 'dbsource/dbsource_admin/add' ), 'add', L ( 'added_external_data_source' ), 700, 500 );
		include $this->admin_tpl ( 'dbsource_list' );
	}

	/**
	 * 添加外部数据源
	 */
	public function add() {
		if (isset ( $_POST ['dosubmit'] )) {
			$name = isset ( $_POST ['name'] ) && trim ( $_POST ['name'] ) ? trim ( $_POST ['name'] ) : showmessage ( L ( 'dbsource_name' ) . L ( 'empty' ) );
			$host = isset ( $_POST ['host'] ) && trim ( $_POST ['host'] ) ? trim ( $_POST ['host'] ) : showmessage ( L ( 'server_address' ) . L ( 'empty' ) );
			$port = isset ( $_POST ['port'] ) && intval ( $_POST ['port'] ) ? intval ( $_POST ['port'] ) : showmessage ( L ( 'server_port' ) . L ( 'empty' ) );
			$username = isset ( $_POST ['username'] ) && trim ( $_POST ['username'] ) ? trim ( $_POST ['username'] ) : showmessage ( L ( 'username' ) . L ( 'empty' ) );
			$password = isset ( $_POST ['password'] ) && trim ( $_POST ['password'] ) ? trim ( $_POST ['password'] ) : showmessage ( L ( 'password' ) . L ( 'empty' ) );
			$dbname = isset ( $_POST ['dbname'] ) && trim ( $_POST ['dbname'] ) ? trim ( $_POST ['dbname'] ) : showmessage ( L ( 'database' ) . L ( 'empty' ) );
			$dbtablepre = isset ( $_POST ['dbtablepre'] ) && trim ( $_POST ['dbtablepre'] ) ? trim ( $_POST ['dbtablepre'] ) : '';
			$charset = isset ( $_POST ['charset'] ) && in_array ( trim ( $_POST ['charset'] ), array ('gbk','utf8','gb2312','latin1' ) ) ? trim ( $_POST ['charset'] ) : showmessage ( L ( 'charset' ) . L ( 'illegal_parameters' ) );
			if (! preg_match ( '/^\\w+$/i', $name )) {
				showmessage ( L ( 'data_source_of_the_letters_and_figures' ) );
			}
			// 检察数据源名是否已经存在
			if ($this->db->where ( array ('name' => $name ) )->field ( 'id' )->find ()) {
				showmessage ( L ( 'dbsource_name' ) . L ( 'exists' ) );
			}

			if ($this->db->insert ( array ('name' => $name,'host' => $host,'port' => $port,'username' => $username,'password' => $password,'dbname' => $dbname,'dbtablepre' => $dbtablepre,'charset' => $charset ) )) {
				dbsource_cache ();
				showmessage ( L ( 'operation_success' ), '', '', 'add' );
			} else {
				showmessage ( L ( 'operation_failure' ) );
			}
		} else {
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'dbsource_add' );
		}
	}

	/**
	 * 修改外部数据源
	 */
	public function edit() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : showmessage ( 'ID' . L ( 'empty' ) );
		$data = $this->db->getby_id ($id );
		if (! $data) showmessage ( L ( 'notfound' ) );
		if (isset ( $_POST ['dosubmit'] )) {
			$host = isset ( $_POST ['host'] ) && trim ( $_POST ['host'] ) ? trim ( $_POST ['host'] ) : showmessage ( L ( 'server_address' ) . L ( 'empty' ) );
			$port = isset ( $_POST ['port'] ) && intval ( $_POST ['port'] ) ? intval ( $_POST ['port'] ) : showmessage ( L ( 'server_port' ) . L ( 'empty' ) );
			$username = isset ( $_POST ['username'] ) && trim ( $_POST ['username'] ) ? trim ( $_POST ['username'] ) : showmessage ( L ( 'username' ) . L ( 'empty' ) );
			$password = isset ( $_POST ['password'] ) && trim ( $_POST ['password'] ) ? trim ( $_POST ['password'] ) : showmessage ( L ( 'password' ) . L ( 'empty' ) );
			$dbname = isset ( $_POST ['dbname'] ) && trim ( $_POST ['dbname'] ) ? trim ( $_POST ['dbname'] ) : showmessage ( L ( 'database' ) . L ( 'empty' ) );
			$dbtablepre = isset ( $_POST ['dbtablepre'] ) && trim ( $_POST ['dbtablepre'] ) ? trim ( $_POST ['dbtablepre'] ) : '';
			$charset = isset ( $_POST ['charset'] ) && in_array ( trim ( $_POST ['charset'] ), array ('gbk','utf8','gb2312','latin1' ) ) ? trim ( $_POST ['charset'] ) : showmessage ( L ( 'charset' ) . L ( 'illegal_parameters' ) );
			$sql = array ('host' => $host,'port' => $port,'username' => $username,'password' => $password,'dbname' => $dbname,'dbtablepre' => $dbtablepre,'charset' => $charset );
			if ($this->db->where(array ('id' => $id ))->update ( $sql )) {
				dbsource_cache ();
				showmessage ( L ( 'operation_success' ), '', '', 'edit' );
			} else {
				showmessage ( L ( 'operation_failure' ) );
			}
		} else {
			$show_header = $show_validator = true;
			include $this->admin_tpl ( 'dbsource_edit' );
		}
	}

	/**
	 * 删除外部数据源
	 */
	public function del() {
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : '';
		if ($this->db->getby_id ( $id )) {
			if ($this->db->where(array ('id' => $id ))->delete (  )) {
				dbsource_cache ();
				showmessage ( L ( 'operation_success' ), U ( 'dbsource/dbsource_admin/init' ) );
			} else {
				showmessage ( L ( 'operation_failure' ), U ( 'dbsource/dbsource_admin/init' ) );
			}
		} else {
			showmessage ( L ( 'notfound' ), U ( 'dbsource/dbsource_admin/init' ) );
		}
	}

	/**
	 * 检查名称是否可用
	 */
	public function public_name() {
		$name = isset ( $_GET ['name'] ) && trim ( $_GET ['name'] ) ? (CHARSET == 'gbk' ? iconv ( 'utf-8', 'gbk', trim ( $_GET ['name'] ) ) : trim ( $_GET ['name'] )) : exit ( '0' );
		$id = isset ( $_GET ['id'] ) && intval ( $_GET ['id'] ) ? intval ( $_GET ['id'] ) : '';
		$data = array ();
		if ($id) {
			$data = $this->db->where ( array ('id' => $id ) )->field('name')->find();
			if (! empty ( $data ) && $data ['name'] == $name) exit ( '1' );
		}
		if ($this->db->where( array ('name' => $name ) )->field('id')->find())
			exit ( '0' );
		else
			exit ( '1' );
	}

	/**
	 * 检查数据库是否可用
	 */
	public function public_test_connect() {
		$host = isset ( $_GET ['host'] ) && trim ( $_GET ['host'] ) ? trim ( $_GET ['host'] ) : exit ( '0' );
		$password = isset ( $_GET ['password'] ) && trim ( $_GET ['password'] ) ? trim ( $_GET ['password'] ) : exit ( '0' );
		$port = isset ( $_GET ['port'] ) && intval ( $_GET ['port'] ) ? intval ( $_GET ['port'] ) : exit ( '0' );
		$username = isset ( $_GET ['username'] ) && trim ( $_GET ['username'] ) ? trim ( $_GET ['username'] ) : exit ( '0' );
		if (@mysql_connect ( $host . ':' . $port, $username, $password )) {
			exit ( '1' );
		} else {
			exit ( '0' );
		}
	}
}