<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'license','parentid' => 34,'application' => 'license','controller' => 'license','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'add_license','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_license','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_license','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'add_type','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'add_type','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'edit_type','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'edit_type','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_type','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'delete_type','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'list_type','parentid' => $parentid,'application' => 'license','controller' => 'license','action' => 'list_type','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'add_client','parentid' => $parentid,'application' => 'license','controller' => 'client','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_client','parentid' => $parentid,'application' => 'license','controller' => 'client','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_client','parentid' => $parentid,'application' => 'license','controller' => 'client','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'list_client','parentid' => $parentid,'application' => 'license','controller' => 'client','action' => 'init','data' => '','listorder' => 0,'display' => '1' ) );
$language = array ('license' => '软件授权','add_license' => '添加授权','edit_license' => '编辑授权','delete_license' => '删除授权','add_type' => '添加类别','edit_type' => '修改类别','delete_type' => '删除类别','list_type' => '分类管理','add_client' => '添加客户机','edit_client' => '修改客户机','delete_client' => '删除客户机','list_client' => '客户机管理' );
?>