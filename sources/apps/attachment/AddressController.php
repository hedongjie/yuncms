<?php
/**
 * 附件地址更新
 * @author Tongle Xu <xutongle@gmail.com> 2012-5-28
 * @copyright Copyright (c) 2003-2103 yuncms.net
 * @license http://leaps.yuncms.net
 * @version $Id: AddressController.php 95 2013-03-23 15:27:53Z 85825770@qq.com $
 */
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
Loader::lib ( 'admin:admin', false );
class AddressController extends admin {

	public function __construct() {
		parent::__construct ();
	}

	public function init() {
		include $this->admin_tpl ( 'address' );
	}

	public function update() {
		set_time_limit ( 120 );
		$old_attachment_path = isset ( $_POST ['old_attachment_path'] ) && trim ( $_POST ['old_attachment_path'] ) ? trim ( $_POST ['old_attachment_path'] ) : showmessage ( L ( 'old_attachment_address_empty' ) );
		$new_attachment_path = isset ( $_POST ['new_attachment_path'] ) && trim ( $_POST ['new_attachment_path'] ) ? trim ( $_POST ['new_attachment_path'] ) : showmessage ( L ( 'new_attachment_address_empty' ) );
		// 获取数据表列表
		$db = Loader::model('admin_model');
		$res = $db->query ( "show tables" );
		$r = $res->fetchAll ();
		$res = null;
		foreach ( $r as $k => $v ) {
			$v = array_pop ( $v );
			if (strpos ( $v, $db->get_prefix() ) === false)
				continue;
			$table_name = str_replace ( $db->get_prefix(), '', $v );
			// 获取每个表的数据表结构
			if (! file_exists ( SOURCE_PATH . 'model' . DIRECTORY_SEPARATOR . $table_name . '_model' . '.php' )) {
				$modle_table_db = $db;
			} else {
				$modle_table_db = Loader::model ( $table_name.'_model' );
			}
			$s = $modle_table_db->get_fields ( $table_name );
			if ($s) {
				$sql = '';
				foreach ( $s as $key => $val ) {
					// 对数据表进行过滤，只有CHAR、TEXT或mediumtext类型的字段才可以保存下附件的地址。
					if (preg_match ( '/(char|text|mediumtext)+/i', $val )) {
						$sql .= ! empty ( $sql ) ? ", `$key`=replace(`$key`, '$old_attachment_path', '$new_attachment_path')" : "`$key`=replace(`$key`, '$old_attachment_path', '$new_attachment_path')";
					}
				}
				if (! empty ( $sql ))
					$modle_table_db->query ( "UPDATE " . $db->get_prefix() . $table_name . " SET $sql" );
			}
		}
		showmessage ( L ( 'operation_success' ) );
	}
}