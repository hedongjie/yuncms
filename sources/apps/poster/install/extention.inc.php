<?php
error_reporting ( E_ALL );
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );

$parentid = $admin_menu_db->insert ( array ('name' => 'poster','parentid' => 34,'application' => 'poster','controller' => 'space','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'add_space','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_space','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'del_space','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'poster_list','parentid' => $parentid,'application' => 'poster','controller' => 'poster','action' => 'init','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'add_poster','parentid' => $parentid,'application' => 'poster','controller' => 'poster','action' => 'add','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'edit_poster','parentid' => $parentid,'application' => 'poster','controller' => 'poster','action' => 'edit','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'del_poster','parentid' => $parentid,'application' => 'poster','controller' => 'poster','action' => 'delete','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'poster_stat','parentid' => $parentid,'application' => 'poster','controller' => 'poster','action' => 'stat','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'poster_setting','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'setting','data' => '','listorder' => 0,'display' => '0' ) );
$admin_menu_db->insert ( array ('name' => 'create_poster_js','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'create_js','data' => '','listorder' => 0,'display' => '1' ) );
$admin_menu_db->insert ( array ('name' => 'poster_template','parentid' => $parentid,'application' => 'poster','controller' => 'space','action' => 'poster_template','data' => '','listorder' => 0,'display' => '1' ) );

$language = array ('poster' => '广告','add_space' => '添加版位','edit_space' => '编辑版位','del_space' => '删除版位','poster_list' => '广告列表','add_poster' => '添加广告','edit_poster' => '编辑广告','del_poster' => '删除广告','poster_stat' => '广告统计','poster_setting' => '模块配置','create_poster_js' => '重新生成js','poster_template' => '广告模板设置' );

$cache = array ('banner' => array ('name' => '矩形横幅','select' => '0','padding' => '0','size' => '1','option' => '0','num' => '1','iscore' => '1','type' => array ('images' => '图片','flash' => '动画' ) ),'fixure' => array ('name' => '固定位置','align' => 'align','select' => '1','padding' => '1','size' => '1','option' => '0','num' => '1','iscore' => '1','type' => array ('images' => '图片','flash' => '动画' ) ),'float' => array ('name' => '漂浮移动','select' => '0','padding' => '1','size' => '1','option' => '0','num' => '1','iscore' => '1','type' => array ('images' => '图片','flash' => '动画' ) ),'couplet' => array ('name' => '对联广告','align' => 'scroll','select' => '0','padding' => '1','size' => '1','option' => '0','num' => '2','iscore' => '1','type' => array ('images' => '图片','flash' => '动画' ) ),'imagechange' => array ('name' => '图片轮换广告','select' => '0','padding' => '0','size' => '1','option' => '1','num' => '1','iscore' => '1','type' => array ('images' => '图片' ) ),'imagelist' => array ('name' => '图片列表广告','select' => '0','padding' => '0','size' => '1','option' => '1','num' => '1','iscore' => '1','type' => array ('images' => '图片' ) ),'text' => array ('name' => '文字广告','select' => '0','padding' => '0','size' => '0','option' => '1','num' => '1','iscore' => '1','type' => array ('text' => '文字' ) ),'code' => array ('name' => '代码广告','type' => array ('text' => '代码' ),'num' => 1,'iscore' => 1,'option' => 0 ) );
S ( 'common/poster_template', $cache );
?>