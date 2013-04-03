<?php
defined ( 'IN_YUNCMS' ) or exit ( 'Access Denied' );
defined ( 'INSTALL' ) or exit ( 'Access Denied' );
$parentid = $admin_menu_db->insert ( array ('name' => 'mood','parentid' => 34,'application' => 'mood','controller' => 'mood_admin','action' => 'init','data' => '','listorder' => 0,'display' => '1' ), true );
$admin_menu_db->insert ( array ('name' => 'mood_setting','parentid' => $parentid,'application' => 'mood','controller' => 'mood_admin','action' => 'setting','data' => '','listorder' => 0,'display' => '1' ) );

$language = array ('mood' => '新闻心情','mood_setting' => '心情配置' );
S ( 'common/mood_program', array (1 => array ('use' => '1','name' => '震惊','pic' => 'mood/zhenjing.gif' ),2 => array ('use' => '1','name' => '不解','pic' => 'mood/bujie.gif' ),3 => array ('use' => '1','name' => '愤怒','pic' => 'mood/fennu.gif' ),
		4 => array ('use' => '1','name' => '标题党','pic' => 'mood/biaotidang.gif' ),5 => array ('use' => '1','name' => '无聊','pic' => 'mood/wuliao.gif' ),6 => array ('use' => '1','name' => '高兴','pic' => 'mood/gaoxing.gif' ),7 => array ('use' => '1','name' => '无奈','pic' => 'mood/wunai.gif' ),
		8 => array ('use' => '1','name' => '支持','pic' => 'mood/zhichi.gif' ),9 => array ('use' => '1','name' => '谎言','pic' => 'mood/huangyan.gif' ),10 => array ('use' => '1','name' => '枪稿','pic' => 'mood/qianggao.gif' ) ) );
?>