<?php
error_reporting ( E_ALL );
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );

$parentid = $admin_menu_db->insert ( array ('name' => 'feedback','parentid' => 34,'application' => 'feedback','controller' => 'feedback','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'info_list','parentid' => $parentid,'application' => 'feedback','controller' => 'feedback_info','action' => 'init','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'feedback_stat','parentid' => $parentid,'application' => 'feedback','controller' => 'feedback','action' => 'stat','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'add_field','parentid' => $parentid,'application' => 'feedback','controller' => 'feedback_field','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'list_field','parentid' => $parentid,'application' => 'feedback','controller' => 'feedback_field','action' => 'init','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'module_setting','parentid' => $parentid,'application' => 'feedback','controller' => 'feedback','action' => 'setting','data' => '','listorder' => 0,'display' => '0' ) );

$language = array (
		'feedback' => '留言反馈',
		'info_list' => '信息列表',
		'feedback_stat' => '留言统计',
		'add_field' => '添加字段',
		'list_field' => '管理字段',
		'module_setting' => '模块配置'
);
?>