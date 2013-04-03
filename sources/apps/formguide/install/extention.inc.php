<?php
error_reporting ( E_ALL );
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );

$parentid = $admin_menu_db->insert ( array ('name' => 'formguide','parentid' => 34,'application' => 'formguide','controller' => 'formguide','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'formguide_add','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'formguide_edit','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'form_info_list','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide_info','action' => 'init','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'formguide_disabled','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'disabled','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'formguide_delete','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'formguide_stat','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'stat','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'add_public_field','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide_field','action' => 'add','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'list_public_field','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide_field','action' => 'init','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'module_setting','parentid' => $parentid,'application' => 'formguide','controller' => 'formguide','action' => 'setting','data' => '','listorder' => 0,'display' => '0' ) );

$language = array ('formguide' => '表单向导','formguide_add' => '添加表单向导','formguide_edit' => '修改表单向导','form_info_list' => '信息列表','formguide_disabled' => '禁用表单','formguide_delete' => '删除表单','formguide_stat' => '表单统计','add_public_field' => '添加公共字段','list_public_field' => '管理公共字段','module_setting' => '模块配置' );
?>