<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );

$parentid = $admin_menu_db->insert ( array ('name' => 'announce','parentid' => 34,'application' => 'announce','controller' => 'admin_announce','action' => 'init','data' => 's=1','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'announce_add','parentid' => $parentid,'application' => 'announce','controller' => 'admin_announce','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_announce','parentid' => $parentid,'application' => 'announce','controller' => 'admin_announce','action' => 'edit','data' => 's=1','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'check_announce','parentid' => $parentid,'application' => 'announce','controller' => 'admin_announce','action' => 'init','data' => 's=2','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'overdue','parentid' => $parentid,'application' => 'announce','controller' => 'admin_announce','action' => 'init','data' => 's=3','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'del_announce','parentid' => $parentid,'application' => 'announce','controller' => 'admin_announce','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );

$language = array ('announce' => '公告','announce_add' => '添加公告','edit_announce' => '编辑公告','check_announce' => '审核公告','overdue' => '过期公告','del_announce' => '删除公告' );
?>