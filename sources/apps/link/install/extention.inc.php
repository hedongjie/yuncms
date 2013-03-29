<?php
defined ( 'IN_YUNCMS' ) or exit ( 'No permission resources.' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'link','parentid' => 34,'application' => 'link','controller' => 'link','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'add_link','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_link','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_link','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'link_setting','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'setting','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'add_type','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'add_type','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'list_type','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'list_type','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'check_register','parentid' => $parentid,'application' => 'link','controller' => 'link','action' => 'check_register','data' => '','listorder' => 0,'display' => '1' ) );

$language = array ('link' => '友情链接','add_link' => '添加友情链接','edit_link' => '编辑友情链接','delete_link' => '删除友情链接','link_setting' => '模块配置','add_type' => '添加类别','list_type' => '分类管理','check_register' => '审核申请' );
