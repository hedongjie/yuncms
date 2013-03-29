<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'vote','parentid' => 34,'application' => 'vote','controller' => 'vote','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'add_vote','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_vote','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'delete_vote','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'vote_setting','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'setting','data' => '','listorder' => 0,'display' => '1' ) );

$admin_menu_db->insert ( array ('name' => 'statistics_vote','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'statistics','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'statistics_userlist','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'statistics_userlist','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'create_js','parentid' => $parentid,'application' => 'vote','controller' => 'vote','action' => 'create_js','data' => '','listorder' => 0,'display' => '1' ) );

$language = array ('vote' => '投票','add_vote' => '添加投票','edit_vote' => '编辑投票','delete_vote' => '删除投票','vote_setting' => '投票配置','statistics_vote' => '查看统计','statistics_userlist' => '会员统计','create_js' => '更新JS' );
?>