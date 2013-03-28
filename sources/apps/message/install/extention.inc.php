<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'message','parentid' => 34,'application' => 'message','controller' => 'message','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'send_one','parentid' => $parentid,'application' => 'message','controller' => 'message','action' => 'send_one','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'delete_message','parentid' => $parentid,'application' => 'message','controller' => 'message','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'message_send','parentid' => $parentid,'application' => 'message','controller' => 'message','action' => 'message_send','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'message_group_manage','parentid' => $parentid,'application' => 'message','controller' => 'message','action' => 'message_group_manage','data' => '','listorder' => 0,'display' => '1' ) );

$language = array ('message' => '短消息','send_one' => '发送消息','delete_message' => '删除短消息','message_send' => '群发短消息','message_group_manage' => '群发短消息管理' );
?>