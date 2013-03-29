<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );

$parentid = $admin_menu_db->insert ( array ('name' => 'tag','parentid' => 40,'application' => 'tag','controller' => 'tag','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'add_tag','parentid' => $parentid,'application' => 'tag','controller' => 'tag','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_tag','parentid' => $parentid,'application' => 'tag','controller' => 'tag','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_tag','parentid' => $parentid,'application' => 'tag','controller' => 'tag','action' => 'del','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'tag_lists','parentid' => $parentid,'application' => 'tag','controller' => 'tag','action' => 'lists','data' => '','listorder' => 0,'display' => '0' ) );

$language = array ('tag' => '标签向导','add_tag' => '添加标签向导','edit_tag' => '修改标签向导','delete_tag' => '删除标签向导','tag_lists' => '标签向导列表' );
?>