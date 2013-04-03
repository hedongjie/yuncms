<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'comment','parentid' => 34,'application' => 'comment','controller' => 'comment_admin','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );

$mid = $admin_menu_db->insert ( array ('name' => 'comment_manage','parentid' => 36,'application' => 'comment','controller' => 'comment_admin','action' => 'listinfo','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'comment_check','parentid' => $mid,'application' => 'comment','controller' => 'check','action' => 'checks','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'comment_list','parentid' => $parentid,'application' => 'comment','controller' => 'comment_admin','action' => 'lists','data' => '','listorder' => 0,'display' => '0' ) );

$language = array ('comment' => '评论','comment_manage' => '评论管理','comment_check' => '评论审核','comment_list' => '评论列表' );
?>